#!/usr/bin/php
<?php

require_once("vendor/autoload.php");

$worker = new \CarTraq\GPSWorker();

$worker->run();