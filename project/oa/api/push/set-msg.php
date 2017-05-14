<?php

/**
 * 消息推送-发送消息
 *
 * @author ChenHao
 * @version 2015/4/10
 */
require '../../application.php';
require '../../loader-api.php';

execute_request(HttpRequestMethod::Post, function() {
    list($time, $msg, $action, $sign) = filter_request(array(
        request_datetime('time'),
        request_string('msg'),
        request_action(),
        request_md5_32('sign')));
    $time = urldecode($time);
    $msg = urldecode($msg);
    $users = request_string('users');

    $sign_str = $time . $msg . $action . PRIVATE_KEY;
    $part_users = isset($users) && strlen($users) > 0;
    if ($part_users) {
        $users = urldecode($users);
        $sign_str .= $users;
        $users = explode(',', $users);
        $part_users = $part_users && count($users) > 0;
    }
    $caller = request_int('caller', 0, 1);
    if (!isset($caller)) $caller = 0;

    if (!str_equals($sign, md5($sign_str))) die_error(USER_ERROR, 'Error Signature');
    if (abs(time() - strtotime($time)) > 300) die_error(USER_ERROR, 'Invalid Timestamp');

    $redis_host = REDIS_HOST;
    $redis_port = REDIS_PORT;
    if ($caller == 1) {
        $redis_port = REDIS_PORT_ZH;
    }
    if ($action == 1) {
        $redis = new Redis();
        $redis->connect($redis_host, $redis_port);
        $client_code_array = $redis->sMembers(YC_OA_CLIENT_LIST_KEY);
        //如果指定了接收用户，则只向指定的用户发送消息
        if ($part_users) {
            $client_code_array = array_intersect($users, $client_code_array);
        }
        //循环为每个客户端创建一个消息队列，并在队列尾部添加消息
        foreach ($client_code_array as $client_code) {
            $redis->rPush(YC_OA_NOTIFY_MSG_KEY_PREFIX . $client_code, $msg);
        }

        $redis->close();
        echo_code(0);
    }
});
