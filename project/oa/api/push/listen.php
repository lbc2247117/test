<?php

 echo $_REQUEST['sign'];
 exit();

/**
 * 消息推送-监听
 *
 * @author ChenHao
 * @version 2015/4/10
 */
require '../../application.php';
require '../../loader-api.php';


echo '5555555555555';
exit();
execute_request("POST", function() {
    
    
    list($time, $client_code, $sign, $action) = filter_request(array(
        request_datetime('time'),
        request_int('client_code'),
        request_md5_32('sign'),
        request_action()));
    $caller = request_int('caller', 0, 1);
    if (!isset($caller)) $caller = 0;
    $sign_str = $time . $action . $client_code . PUBLIC_KEY;
    
    
    
   // if (!str_equals($sign, md5($sign_str))) die_error(USER_ERROR, 'Error Signature');
    //if (abs(time() - strtotime($time)) > 300) die_error(USER_ERROR, 'Invalid Timestamp');

    $redis_host = REDIS_HOST;
    $redis_port = REDIS_PORT;
    if ($caller == 1) {
        $redis_port = REDIS_PORT_ZH;
    }
    $redis = new Redis();
    $redis->connect($redis_host, $redis_port);
    //启动监听
    if ($action == 1) {
        $result = $redis->sRemove(YC_OA_CLIENT_LIST_KEY, $client_code);
        //移除之前未收到的消息
        while (true) {
            $result = $redis->lPop(YC_OA_NOTIFY_MSG_KEY_PREFIX . $client_code);
            if (!$result) break;
        }
        $result = $redis->sAdd(YC_OA_CLIENT_LIST_KEY, $client_code);
        //客户端连接成功后发送一个心跳包
        $redis->rPush(YC_OA_NOTIFY_MSG_KEY_PREFIX . $client_code, HEARTBEAT_PACKET);
        $redis->rPush(YC_OA_NOTIFY_MSG_KEY_PREFIX . $client_code, HEARTBEAT_PACKET);
        //if (!$result) die_error(USER_ERROR, 'Failed to add client to queue');
        $redis->close();
        echo_code(0);
    }
    //停止监听
    if ($action == 2) {
        $result = $redis->sRemove(YC_OA_CLIENT_LIST_KEY, $client_code);
        //if (!$result) die_error(USER_ERROR, 'Failed to remove client from queue');
        $redis->close();
        echo_code(0);
    }
});
