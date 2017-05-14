<?php

/**
 * 销售业绩统计表
 *
 * @author QI
 * @copyright 2015 星密码
 * @version 2015/3/16
 */
use Models\Base\Model;
use Models\P_Salecount_soft;
use Models\Base\SqlOperator;
use Models\P_Customerrecord_soft;
use Models\P_Fills_soft;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    if (!isset($action)) $action = -1;
    if ($action == 1) {
        $fillarrears = new P_Fills_soft();
        $sort = request_string('sort');
        $sortname = request_string('sortname');
        $searchName = request_string('searchName');
        $login_userid = request_login_userid();
        $is_manager = is_manager($login_userid, 2);
        if (isset($searchName)) {
            $fillarrears->set_custom_where(" AND ( ww like '%" . $searchName . "%' OR name LIKE '%" . $searchName . "%' OR add_name like'%" . $searchName . "%' ) ");
        }
        if (!$is_manager) {
            $fillarrears->set_custom_where(" and (customer_id=" . $login_userid . " or customer_id2 = " . $login_userid . "  )");
        }
        if (isset($sort) && isset($sortname)) {
            $fillarrears->set_order_by($fillarrears->get_field_by_name($sortname), $sort);
        } else {
            $fillarrears->set_order_by(P_Salecount_soft::$field_id, 'desc');
        }
        $fillarrears->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $fillarrears, NULL, true);
        if (!$result[0]) die_error(USER_ERROR, '获取补欠款资料失败，请重试');
        $models = Model::list_to_array($result['models']);

        $customer = new P_Customerrecord_soft();
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
    }
    if ($action == 11) {
        $startTime = request_datetime("start_time");
        $endTime = request_datetime("end_time");
        $export = new ExportData2Excel();
        $fills = new P_Fills_soft();
        if (isset($startTime)) {
            $fills->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $fills->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') <= '" . $endTime . "' ");
        }
        $field = array('add_time', 'ww', 'name', 'mobile', 'fill_sum', 'payment', 'channel', 'customer', 'customer2', 'add_name');
        $fills->set_query_fields($field);
        $db = create_pdo();
        $result = Model::query_list($db, $fills, NULL, true);
        if (!$result[0]) {
            $export->create(array('导出错误'), array(array('补欠款数据导出失败,请稍后重试!')), "补欠款数据导出", "补欠款");
        }
        $models = Model::list_to_array($result['models']);
        $title_array = array('日期', '旺旺号', '真实姓名', '手机号', '补欠金额', '收款方式', '接入渠道', '售后老师', '更换老师', '添加人');
        $export->set_field($field);
        $export->create($title_array, $models, "补欠款数据导出", "补欠款");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $fillarrearsData = request_object();
    //添加补欠款
    if ($action == 1) {
        $fillarrears = new P_Fills_soft();
        $fillarrears->set_field_from_array($fillarrearsData);
        if (isset($fillarrearsData->add_time)) $fillarrears->set_add_time("now");
        if (isset($fillarrearsData->add_name)) $fillarrears->set_add_name(request_username());
        $db = create_pdo();
        $result = $fillarrears->insert($db);
        if (!$result[0]) die_error(USER_ERROR, '添加补欠款失败。');
        echo_msg('添加成功');
    }
    //修改销售统计信息
    if ($action == 2) {
        $fillarrears = new P_Fills_soft();
        if (isset($fillarrearsData->customer_id)) {
            if ($fillarrearsData->customer_id != $fillarrearsData->customer_id2) {
                $fillarrearsData->customer_id2 = $fillarrearsData->customer_id;
                $fillarrears->set_customer_id2($fillarrearsData->customer_id2);
                $fillarrears->set_customer2($fillarrearsData->customer);
                $fillarrears->set_nick_name2($fillarrearsData->nick_name);
                unset($fillarrearsData->customer_id);
                unset($fillarrearsData->customer);
                unset($fillarrearsData->nick_name);
            } else {
                die_error(USER_ERROR, '怎么还是TA？售后没选择对吧？~');
            }
        }
        $fillarrears->set_field_from_array($fillarrearsData);

        $db = create_pdo();
        $result = $fillarrears->update($db, true);
        if (!$result[0]) die_error(USER_ERROR, '保存统计资料失败');
        echo_msg('保存成功');
    }
    //删除
    if ($action == 3) {
        $fillarrears = new P_Fills_soft();
        $fillarrears->set_field_from_array($fillarrearsData);
        $db = create_pdo();
        $result = $fillarrears->delete($db, true);
        if (!$result[0]) die_error(USER_ERROR, '删除失败');
        echo_msg('删除成功');
    }
});

