<?php

/**
 * 二销财务业绩排行
 *
 * @author TianYu
 * @copyright 2016 非时序
 * @version 2016/4/14
 */
use Models\st_fills_second;

require '../../application.php';
require '../../loader-api.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    if (!isset($action))
        $action = -1;
    $sellerFront = new st_fills_second();
    $pre = request_int('pre');
    $next = request_string('next');
    $currDate = request_string('currDate');
    $sql = "";
    switch ($action) {
        case 1:
            if (empty($currDate)) {
                $sql .= "select @rownum :=@rownum + 1 AS rownum,headmaster,submit_money,transfer_money,addtime from (SELECT @rownum := 0) r,(SELECT	headmaster,SUM(money) submit_money,SUM(money) transfer_money,addtime FROM	st_fills_second  GROUP BY headmaster ORDER BY money DESC) test order by rownum";
            } else {
                $sql .= "select @rownum :=@rownum + 1 AS rownum,headmaster,submit_money,transfer_money,addtime from (SELECT @rownum := 0) r,(SELECT headmaster,SUM(money) submit_money,SUM(money) transfer_money,addtime FROM st_fills_second where  addtime like '$currDate%' GROUP BY headmaster ORDER BY money DESC) test order by rownum";
            }
            break;
    }
    $datetime = date('Y-m-1');
    $sql_month = "select sum(money) from st_fills_second where addtime>='$datetime'";
    $db = create_pdo();
    $result = $db->query($sql);
//    $result = Model::query_list($db, $sellerFront, NULL, true);
    $result1 = $result->fetchAll(PDO::FETCH_ASSOC);

//    $models = Model::list_to_array($result['models'], array(), "id_2_text");
//    $page_no = request_pageno();
    $total = count($result1);
    $result = $db->query($sql_month);
    $result = $result->fetchAll(PDO::FETCH_ASSOC);
    $totalmoney = $result[0]['sum(money)'];
    if (empty($totalmoney))
        $totalmoney = 0;
    //$max_page_no = ceil($total / request_pagesize());
    $result2 = array('total_count' => $total, "list" => $result1, 'code' => 0, 'totalmoney' => $totalmoney);
//    $result = array_merge($result, $result);
    exit(get_response($result2));

//    echo_list_result($result, $models);
});
