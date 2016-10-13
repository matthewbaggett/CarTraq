<?php
namespace CarTraq;

use Nykopol\GpsdClient\Client as GPSClient;
use Predis\Client as RedisClient;

class GPSWorker {

    /** @var RedisClient */
    private $redis;

    /** @var GPSClient */
    private $gpsClient;

    public function __construct()
    {
        $this->redis = new RedisClient();
        $this->gpsClient = new GPSClient(); // new client for localhost on port 2947
        $this->gpsClient->connect(); // Initiate socket with the service
        $this->gpsClient->watch();   // Tell the service to start report event
    }

    private function cycle(){
        $infos = $this->gpsClient->getNext('TPV'); // Get the next message of class TPV ()
        $infos = json_decode($infos);
        $locationRecord = [];
        $locationRecord['time'] = date("Y-m-d H:i:s");

        $locationRecord['latitude'] = isset($infos->lat) ? $infos->lat : null;
        $locationRecord['longitude'] = isset($infos->lon) ? $infos->lon : null;
        $locationRecord['altitude'] = isset($infos->alt) ? $infos->alt : null;
        $locationRecord['speed'] = isset($infos->speed) ? $infos->speed : null;
        $locationRecord['track'] = isset($infos->track) ? $infos->track : null;
        $locationRecord['climb'] = isset($infos->climb) ? $infos->climb : null;
        $this->redis->publish("location", json_encode($locationRecord));
        $this->redis->hmset("current_location", $locationRecord);
        $this->redis->lpush("locations", $locationRecord);
	if($locationRecord['latitude']){
	        echo "Location: ({$locationRecord['latitude']},{$locationRecord['longitude']}) @ {$locationRecord['speed']}mph\n";
	}
    }

    public function run()
    {
        while(true){
            $this->cycle();
            usleep(500);
        }
    }

    public function hasConnection(){
        $connected = @fsockopen("nope.thru.io", 80);
        //website, port  (try 80 or 443)
        if ($connected){
            fclose($connected);
            return true;
        }else{
            return false;
        }
    }

    public function checkForUpdate(){
        if($this->hasConnection()){
            $this->sendUpdate();
        }else{
            echo "No connectivity\n";
        }
        sleep(30);
    }

    public function sendUpdate(){
        echo "Sending update\n";
    }
}
