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

//第三个参数表示是否开启持久化，false表示不开启，true表示开启
$channel->queue_declare('task_queue', false, true, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
//$callback = function ($msg) {
//    $db = mysqlPDO::create();
//    $content = $msg->body;
//    $sql = 'insert into rabbit (`key`,`value`)VALUES ("testkey","' . $content . '")';
//    $db->exec($sql);
//};
//
//$channel->basic_consume('myrabbit', '', false, true, false, false, $callback);
$callback = function ($msg) {
    echo " [x] Received ", $msg->body, "\n";
    sleep(substr_count($msg->body, '.'));
    echo " [x] Done", "\n";
    //给队列发送确认信息，队列收到确认信息后，才会删除相应消息
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);

//第四个参数为false，表示队列需要确认才删除消息
//第四个参数为true，表示队列不需要确认，发送消息之后，直接删除消息
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);


while (count($channel->callbacks)) {
    $channel->wait();
}


