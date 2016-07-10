#!/usr/bin/php
<?php

require_once("vendor/autoload.php");

use Clue\React\Redis\Client;
use Clue\React\Redis\Factory;

$worker = new \CarTraq\GPSWorker();

$loop = React\EventLoop\Factory::create();
$factory = new Factory($loop);

$factory
    ->createClient()
    ->then(function (Client $client) use ($loop, $worker) {
        $client->subscribe('location');
        $client->on('message', function($channel, $payload) use ($worker, $client){
            if(json_decode($payload) !== null){
                $payload = json_decode($payload);
            }
            if ($channel == 'location'){
                \Kint::dump($payload);
            }
        });
    })->then(function() use ($worker){
        while(true){

            $worker->checkForUpdate();
        }

    });

$loop->run();