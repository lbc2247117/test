<?php


/**
 * 消息推送-监听
 *
 * @author ChenHao
 * @version 2015/4/10
 */
require '../../application.php';


    $time=$_REQUEST['time'];
    $action=$_REQUEST['action'];
    $client_code=$_REQUEST['client_code'];
    $sign=$_REQUEST['sign'];
    $sign_str = $time . $action . $client_code . PUBLIC_KEY;
    
    
    
    if (!$sign===md5($sign_str)) echo "错误";
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
        echo "1";
    }
    //停止监听
    if ($action == 2) {
        $result = $redis->sRemove(YC_OA_CLIENT_LIST_KEY, $client_code);
        //if (!$result) die_error(USER_ERROR, 'Failed to remove client from queue');
        $redis->close();
        echo "00";
    }
