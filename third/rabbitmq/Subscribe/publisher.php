<?php

/**
 * 用于发布=>订阅模式
 *
 * 订阅者在上线时，会创建一个随机的队列
 * 发布者把消息发送给交换器（exchange）
 * 交换器把消息发送给每个队列
 * 订阅者下线时，会删除随机队列
 *
 * 适用于给在线用户推送消息
 *
*/
require_once  '../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('logs', 'fanout', false, false, false);

$data = implode(' ', array_slice($argv, 1));
if(empty($data)) $data = "info: Hello World!";
$msg = new AMQPMessage($data);

$channel->basic_publish($msg, 'logs');

echo " [x] Sent ", $data, "\n";

$channel->close();
$connection->close();

?>