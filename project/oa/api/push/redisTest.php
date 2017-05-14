<?php
//Connecting to Redis server on localhost

require '../../application.php';

$redis_host = REDIS_HOST;
$redis_port = REDIS_PORT;
$redis = new Redis();
$redis->connect($redis_host, $redis_port);
echo "Connection to server sucessfully";
//set the data in redis string
$redis->set("tutorial-name", "Redis tutorial");
// Get the stored data and print it
echo "Stored string in redis:: " + $redis->get("tutorial-name");

