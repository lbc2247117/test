<?php

/**
 * 销售业绩统计表
 *
 * @author QI
 * @copyright 2015 星密码
 * @version 2015/3/16
 */
use Models\Base\Model;
use Models\P_Salecount;
use Models\Base\SqlOperator;
use Models\P_Customerrecord_second_soft;
use Models\P_Fills_second_soft;

//use Common\PHPExcel\PHPExcel;

require '../../application.php';
require '../../loader-api.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    $db = create_pdo();
    if (!isset($action)) $action = -1;
    $fillarrears = new P_Fills_second_soft();
    $sort = request_string('sort');
    $sortname = request_string('sortname');
    $searchName = request_string('searchName');
    if (isset($searchName)) {
        $fillarrears->set_custom_where(" AND ( ww like '%" . $searchName . "%' OR name LIKE '%" . $searchName . "%' OR add_name like'%" . $searchName . "%' ) ");
    }
    if (isset($sort) && isset($sortname)) {
        $fillarrears->set_order_by($fillarrears->get_field_by_name($sortname), $sort);
    } else {
        $fillarrears->set_order_by(P_Salecount::$field_id, 'desc');
    }
    $fillarrears->set_limit_paged(request_pageno(), request_pagesize());
    $result = Model::query_list($db, $fillarrears, NULL, true);
    if (!$result[0]) die_error(USER_ERROR, '获取补欠款资料失败，请重试');
    $models = Model::list_to_array($result['models']);

    $page_no = request_pageno();
    $total = $result['total_count'];
    $max_page_no = ceil($total / request_pagesize());

    $customer = new P_Customerrecord_second_soft();
    $customer->set_status(1);
    $customer->set_query_fields(array('userid', 'username', 'nickname'));
    $customer_result = Model::query_list($db, $customer);
    $customer_list = Model::list_to_array($customer_result['models'], array(), function(&$d) {
                $d['id'] = $d['userid'];
                $d['text'] = $d['username'] . "(" . $d['nickname'] . ")";
                unset($d['userid']);
                unset($d['username']);
                unset($d['nickname']);
            });
    echo_list_result($result, $models, array('customer_list' => $customer_list, 'current_date' => date('Y-m-d')));
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $fillarrearsData = request_object();
    //添加补欠款
    if ($action == 1) {
        $fillarrears = new P_Fills_second_soft();
        $fillarrears->set_field_from_array($fillarrearsData);
        $fillarrears->set_add_time("now");
        $fillarrears->set_add_name(request_userid());
        $db = create_pdo();
        $result = $fillarrears->insert($db);
        if (!$result[0]) die_error(USER_ERROR, '添加补欠款失败。');
        echo_msg('添加成功');
    }
    //修改销售统计信息
    if ($action == 2) {
        $fillarrears = new P_Fills_second_soft();
//        if (isset($fillarrearsData->customer_id)) {
//            if ($fillarrearsData->customer_id2 == $fillarrearsData->customer_id) {
//                die_error(USER_ERROR, '请不要重复选择老师');
//            }
//        }
        $fillarrears->set_field_from_array($fillarrearsData);
        $fillarrears->set_attachment($fillarrearsData->editorValue);

        $db = create_pdo();
        $result = $fillarrears->update($db, true);
        if (!$result[0]) die_error(USER_ERROR, '保存统计资料失败');
        echo_msg('保存成功');
    }
    //删除
    if ($action == 3) {
        $fillarrears = new P_Fills_second_soft();
        $fillarrears->set_field_from_array($fillarrearsData);
        $db = create_pdo();
        $result = $fillarrears->delete($db, true);
        if (!$result[0]) die_error(USER_ERROR, '删除失败');
        echo_msg('删除成功');
    }
});

