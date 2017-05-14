<?php
/**
 * Created by PhpStorm.
 * User: liubocheng
 * Date: 17-5-7
 * Time: 下午6:00
 */
require_once '../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('myrabbit', false, false, false, false);

$msg = new AMQPMessage('Hello liubocheng!');
$channel->basic_publish($msg, '', 'myrabbit');

echo " [x] Sent 'Hello World!'\n";

$channel->close();
$connection->close();