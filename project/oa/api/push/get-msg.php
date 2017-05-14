<?php

/**
 * 消息推送-接收消息（长连接）
 *
 * @author ChenHao
 * @version 2015/4/10
 */
require '../../application.php';
require '../../loader-api.php';

list($time, $client_code, $sign, $action) = filter_request(array(
    request_datetime('time'),
    request_int('client_code'),
    request_md5_32('sign'),
    request_action()));
$caller = request_int('caller', 0, 1);
if (!isset($caller)) $caller = 0;
$sign_str = $time . $action . $client_code . PUBLIC_KEY;
if (!str_equals($sign, md5($sign_str))) die_error(USER_ERROR, 'Error Signature');

$redis_host = REDIS_HOST;
$redis_port = REDIS_PORT;
if ($caller == 1) {
    $redis_port = REDIS_PORT_ZH;
}
if ($action == 1) {
    set_time_limit(0);
    ini_set('default_socket_timeout', -1); //不超时

    $redis = new Redis();
    $redis->pconnect($redis_host, $redis_port);

    $last_msg_time = time();
    while (true) {
        $msg = $redis->lPop(YC_OA_NOTIFY_MSG_KEY_PREFIX . $client_code);
        if ($msg !== false && str_length($msg) >= 0) {
            $last_msg_time = time();
            $redis->close();
            echo $msg;
            break;
        }
        usleep(1000000); //休眠1秒钟，usleep单位微妙，sleep单位毫秒
        //每隔10分钟发送一个心跳包
        if (time() - $last_msg_time >= 600) {
            $last_msg_time = time();
            $redis->close();
            echo HEARTBEAT_PACKET;
            break;
        }
    }
}
