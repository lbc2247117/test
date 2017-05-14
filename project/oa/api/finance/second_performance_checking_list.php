<?php

/*
 * 二销对账列表
 * @author TianYu
 * @copyright 2016 非时序
 * @version 2016/2/24
 */

use Models\Base\Model;
use Models\Base\SqlOperator;
use Models\st_fills_second;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';

execute_request(HttpRequestMethod::Get, function() {
    $currentDate = request_string("currentDate");
    $action = request_action();
    if ($action == 1) {
        //$sellerFront = new st_seller_front_agent();
        $sql = "select id,se_id,rcept_account,qq,headmaster,addtime,customer,customer_type,platform_rception,money,pay_user,compare_user,remark from st_fills_second where addtime like '$currentDate%' order by se_id,addtime";
        $sql_total = "select COALESCE(SUM(money),0) totalSubmitMoney from st_fills_second where addtime like '$currentDate%'";
        $resultArray = array('total' => getResult($sql_total), 'list' => getResult($sql));
        exit(get_response($resultArray));
    }
    //导出
    if ($action == 10) {
        $startTime = request_string("start_time");
        $endTime = request_string("end_time");
        $expolt = new ExportData2Excel();
        $generationOperation = new st_fills_second();
        if (isset($startTime)) {
            $generationOperation->set_custom_where(" and DATE_FORMAT(addtime, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $generationOperation->set_custom_where(" and DATE_FORMAT(addtime, '%Y-%m-%d') <= '" . $endTime . "' ");
        }
        $generationOperation->set_query_fields(array('addtime', 'qq', 'money', 'headmaster', 'rcept_account', 'pay_user'));
        $db = create_pdo();
        $result = Model::query_list($db, $generationOperation, NULL, true);
        if (!$result[0]) {
            $expolt->create(array('导出错误'), array(array('对账列表数据导出失败,请稍后重试!')), "二销对账结果", "对账列表");
        }
        $models = Model::list_to_array($result['models'], array(), function(&$d) {
                    
                });
        $title_array = array('时间', 'qq', '金额', '收款账号', '付款人', '班主任');
        $expolt->create($title_array, $models, "二销对账结果", "对账列表");
    }
});

/*
 * 根据传入的SQL语句
 * 获取数据集合
 */

function getResult($sql) {
    $db = create_pdo();
    $result = $db->query($sql);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

;
