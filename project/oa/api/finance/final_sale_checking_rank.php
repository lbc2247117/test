<?php

/**
 * 员工列表/员工增删改操作
 *
 * @author ChenHao
 * @copyright 2015 星密码
 * @version 2015/1/27
 */
use Models\st_seller_front_agent;
use Models\st_final_sale;

require '../../application.php';
require '../../loader-api.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    if (!isset($action))
        $action = -1;
    $currDate = request_string('currDate');
    $sql = "";
    switch ($action) {
        case 1:
            if (empty($currDate)) {
                $sql .= "select @rownum :=@rownum + 1 AS rownum,seller_front,money,addtime from (SELECT @rownum := 0) r,(select seller_front,sum(money) money,addtime from st_final_sale  GROUP BY seller_front ORDER BY money DESC) test ORDER BY rownum";
            } else {
                $sql .= "select @rownum :=@rownum + 1 AS rownum,seller_front,money,addtime from (SELECT @rownum := 0) r,(select seller_front,sum(money) money,addtime from st_final_sale where addtime like '$currDate%' GROUP BY seller_front ORDER BY money DESC) test ORDER BY rownum";
            }
            break;
    }
    $sql_month = "select sum(money) from st_final_sale where DATE_FORMAT(addtime, '%Y-%m') = DATE_FORMAT('$currDate', '%Y-%m')";
    $db = create_pdo();
    $result = $db->query($sql);
    
    $result1 = $result->fetchAll(PDO::FETCH_ASSOC);
    
    $total = count($result1);
    
    $result2 = $db->query($sql_month);
    $result3 = $result2->fetchAll(PDO::FETCH_ASSOC);
    $totalmoney = $result3[0]['sum(money)'];
    if (empty($totalmoney))
        $totalmoney = 0;
    $result4 = array('total_count' => $total, "list" => $result1, 'code' => 0, 'totalmoney' => $totalmoney, 'currentDate'=>$currDate);

    exit(get_response($result4));
});
