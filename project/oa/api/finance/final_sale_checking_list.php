<?php

/*
 * 财务对账列表
 * @author TianYu
 * @copyright 2016 非时序
 * @version 2016/2/24
 */

use Models\Base\Model;
use Models\Base\SqlOperator;
use Models\st_seller_front_agent;
use Models\st_final_sale;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';

execute_request(HttpRequestMethod::Get, function() {
    $currentDate = request_string("currentDate");
    $action = request_action();
    if ($action == 1) {
        //$sellerFront = new st_seller_front_agent();
        $sql = "select gen_id,addtime,rcept_account,pay_user,seller_front,qq,customer_type,customer,headmaster,money,remark from st_final_sale where addtime like '$currentDate%'";
        $sql_total = "select sum(money) totalTransferMoney from st_final_sale where addtime like '$currentDate%'";
        $resultArray = array('total' => getResult($sql_total), 'list' => getResult($sql));
        exit(get_response($resultArray));
    }
    //导出
    if ($action == 10) {
        $startTime = request_string("start_time");
        $endTime = request_string("end_time");
        $expolt = new ExportData2Excel();
        $generationOperation = new st_final_sale();
        if (isset($startTime)) {
            $generationOperation->set_custom_where(" and DATE_FORMAT(addtime, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $generationOperation->set_custom_where(" and DATE_FORMAT(addtime, '%Y-%m-%d') <= '" . $endTime . "' ");
        }
        $generationOperation->set_query_fields(array('addtime', 'qq', 'money', 'seller_front', 'rcept_account', 'pay_user'));
        $db = create_pdo();
        $result = Model::query_list($db, $generationOperation, NULL, true);
        if (!$result[0]) {
            $expolt->create(array('导出错误'), array(array('补款对账列表数据导出失败,请稍后重试!')), "补款对账列表数据导出", "补款对账列表");
        }
        $models = Model::list_to_array($result['models'], array(), function(&$d) {
                    
                });
        $title_array = array('时间', 'qq', '金额','销售','收款账号', '付款人');
        $expolt->create($title_array, $models, "补款对账列表数据导出", "补款对账列表");
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
