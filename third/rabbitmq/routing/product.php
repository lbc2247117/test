<?php

/**
 * 路由模式
 *
 * 和发布者/订阅模式类似
 * 只是路由模式增加了一个路由参数
 * 订阅者只接收相应路由的消息
 *
 * 适用于推送在线用户，可根据不同的条件（如地区）推送不同的消息
*/

require_once '../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('direct_logs', 'direct', false, false, false);

$severity = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'info';

$data = implode(' ', array_slice($argv, 2));
if(empty($data)) $data = "Hello World!";

$msg = new AMQPMessage($data);

$channel->basic_publish($msg, 'direct_logs', $severity);

echo " [x] Sent ",$severity,':',$data," \n";

$channel->close();
$connection->close();

?>