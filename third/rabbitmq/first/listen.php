<?php
/**
 * Created by PhpStorm.
 * User: liubocheng
 * Date: 17-5-7
 * Time: 下午6:00
 */
require '../mysql.php';
require_once '../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;


$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('myrabbit', false, false, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

$callback = function ($msg) {
    $db = mysqlPDO::create();
    $content = $msg->body;
    $sql = 'insert into rabbit (`key`,`value`)VALUES ("testkey","' . $content . '")';
    $db->exec($sql);
};

$channel->basic_consume('myrabbit', '', false, true, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}

