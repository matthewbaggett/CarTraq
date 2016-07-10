<?php
namespace CarTraq;

use Nykopol\GpsdClient\Client as GPSClient;
use Predis\Client as RedisClient;

class UpdateWorker {

    /** @var RedisClient */
    private $redis;

    public function __construct()
    {
        $this->redis = new RedisClient();
    }

    private function cycle(){
        $infos = $this->gpsClient->getNext('TPV'); // Get the next message of class TPV ()
        $infos = json_decode($infos);
        $locationRecord = [];
        $locationRecord['time'] = date("Y-m-d H:i:s");
        $locationRecord['latitude'] = $infos->lat;
        $locationRecord['longitude'] = $infos->lon;
        $locationRecord['altitude'] = $infos->alt;
        $locationRecord['speed'] = $infos->speed;
        $locationRecord['track'] = $infos->track;
        $locationRecord['climb'] = $infos->climb;
        $this->redis->publish("location", json_encode($locationRecord));
        $this->redis->hmset("current_location", $locationRecord);
        $this->redis->lpush("locations", $locationRecord);
        echo "Location: ({$locationRecord['latitude']},{$locationRecord['longitude']}) @ {$locationRecord['speed']}mph\n";
    }

    public function run()
    {
        while(true){
            $this->cycle();
            usleep(500);
        }
    }
}
