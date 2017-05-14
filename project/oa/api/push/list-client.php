<?php

/**
 * 消息推送-当前在线客户端列表
 *
 * @author ChenHao
 * @version 2015/4/10
 */
require '../../application.php';
require '../../loader-api.php';

list( $key) = filter_request(array(
    request_md5_32('key')));
$caller = request_int('caller', 0, 1);
if (!isset($caller)) $caller = 0;
if (!str_equals($key, PUBLIC_KEY)) die_error(USER_ERROR, 'Invalid PublicKey');

$redis_host = REDIS_HOST;
$redis_port = REDIS_PORT;
if ($caller == 1) {
    $redis_port = REDIS_PORT_ZH;
}
$redis = new Redis();
$redis->connect($redis_host, $redis_port);
$result = $redis->sMembers(YC_OA_CLIENT_LIST_KEY);
$total_count = $redis->sSize(YC_OA_CLIENT_LIST_KEY);
$redis->close();
echo_list_result(array('total_count' => $total_count), $result);
