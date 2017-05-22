<?php
/**
 * 工作模式
 *
 * 生产者给指定的队列发送任务
 * 队列可对消息进行持久化
 * 消费者在线时，队列给消费者推送任务
 * 消费者完成任务时，给队列发送确认命令（可省略）
 * 队列删除相应消息
 *
 * 适用于分布式实现最终一致性
 */
require_once '../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->set_ack_handler(
    function (AMQPMessage $message) {
        echo "Message acked with content " . $message->body . PHP_EOL;
    }
);

$channel->set_nack_handler(
    function (AMQPMessage $message) {
        echo "Message nacked with content " . $message->body . PHP_EOL;
    }
);
$channel->queue_declare('task_queue', false, true, false, false);
$channel->confirm_select();
$data = implode('', array_slice($argv, 1));
if (empty($data))
    $data = "Hello World . . .！";
$msg = new AMQPMessage($data, array('delivery_mode' => AMQPMessage :: DELIVERY_MODE_PERSISTENT)
);

$channel->basic_publish($msg, '', 'task_queue');
$channel->wait_for_pending_acks();

echo "[x] Sent" . $data . "\n";

//$msg = new AMQPMessage('Hello liubocheng!');
//$channel->basic_publish($msg, '', 'myrabbit');
//
//echo " [x] Sent 'Hello World!'\n";

$channel->close();
$connection->close();