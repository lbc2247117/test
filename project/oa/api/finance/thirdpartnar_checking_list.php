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

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';

execute_request(HttpRequestMethod::Get, function() {
    $currentDate = request_string("currentDate");
    $action = request_action();
    if ($action == 1) {
        //$sellerFront = new st_seller_front_agent();
        $sql = "select a.*,c.flag from (select id,gen_id,rcept_account,alipay_account,seller_front,qq,submit_money,addtime,transfer_time,compare_time,customer,customer_type,transfer_money,is_question,remark from st_seller_front_agent where addtime like '$currentDate%' order by gen_id,addtime) as a,(select gen_id,is_question as flag from st_seller_front_agent as b where not exists(select 1 from st_seller_front_agent where gen_id= b.gen_id and b.addtime<addtime)) as c where a.gen_id=c.gen_id";
        $sql_total = "select COALESCE(SUM(submit_money),0) totalSubmitMoney,COALESCE(sum( transfer_money),0) totalTransferMoney,COALESCE(SUM(submit_money)-sum( transfer_money),0) differMoney,(select count(*) from st_seller_front_agent as b where not exists(select 1 from st_seller_front_agent where gen_id=b.gen_id and b.addtime<addtime) and b.is_question=1 and addtime like '$currentDate%') questionCount from st_seller_front_agent where addtime like '$currentDate%'";
        $resultArray = array('total' => getResult($sql_total), 'list' => getResult($sql));
        exit(get_response($resultArray));
    }
    //导出
    if ($action == 10) {
        $startTime = request_string("start_time");
        $endTime = request_string("end_time");
        $expolt = new ExportData2Excel();
        $generationOperation = new st_seller_front_agent();
        if (isset($startTime)) {
            $generationOperation->set_custom_where(" and DATE_FORMAT(addtime, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $generationOperation->set_custom_where(" and DATE_FORMAT(addtime, '%Y-%m-%d') <= '" . $endTime . "' ");
        }
        $generationOperation->set_query_fields(array('addtime', 'qq', 'submit_money', 'seller_front', 'rcept_account', 'alipay_account', 'customer_type', 'customer'));
        $db = create_pdo();
        $result = Model::query_list($db, $generationOperation, NULL, true);
        if (!$result[0]) {
            $expolt->create(array('导出错误'), array(array('对账列表数据导出失败,请稍后重试!')), "对账列表数据导出", "对账列表");
        }
        $models = Model::list_to_array($result['models'], array(), function(&$d) {
                    
                });
        $title_array = array('时间', 'qq', '平台接待', '金额', '收款账号', '付款人', '客户来源', '售后');
        $expolt->create($title_array, $models, "对账列表数据导出", "对账列表");
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
