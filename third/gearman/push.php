<?php
/**
 * Created by PhpStorm.
 * User: liubocheng
 * Date: 17-5-19
 * Time: 上午9:54
 */
$client = new GearmanClient();
$client->addServer();
print $client->do("reverse", "Hello World!");