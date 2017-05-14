<?php

/**
 * 补欠款
 *
 * @author QI
 * @copyright 2015 星密码
 * @version 2015/3/16
 */
use Models\Base\Model;
use Models\P_Salecount;
use Models\Base\SqlOperator;
use Models\P_Customerrecord;
use Models\P_Fills;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    $db = create_pdo();
    if (!isset($action)) $action = -1;
    if ($action == 1) {
        $fillarrears = new P_Fills();
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
        $customer = new P_Customerrecord();
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
        $login_userid = request_login_userid();
        echo_list_result($result, $models, array('customer_list' => $customer_list, 'current_date' => date('Y-m-d'), 'is_manager' => is_manager($login_userid)));
    }

    if ($action == 11) {
        $startTime = request_datetime("start_time");
        $endTime = request_datetime("end_time");
        $export = new ExportData2Excel();
        $fills = new P_Fills();
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
        $fillarrears = new P_Fills();
        $fillarrears->set_field_from_array($fillarrearsData);
        if (!isset($fillarrearsData->add_time)) {
            $fillarrears->set_add_time("now");
        }
        if (!isset($fillarrearsData->add_name)) {
            $fillarrears->set_add_name(request_username());
        }
        $db = create_pdo();
        $salecount = new P_Salecount($fillarrearsData->sale_id);
        $salecount_result = $salecount->load($db, $salecount);
        if (!$salecount_result[0]) {
            die_error(USER_ERROR, "系统错误,请稍后重试~");
        } else {
            if ($fillarrearsData->customer_id == 0) {// && $salecount->get_scheduledPackage() == 1
                $customer = new P_Customerrecord();
                $sql = "SELECT pc.id,pc.userid,pc.username,pc.nickname,pc.toplimit,IFNULL(ps.finish,0) AS finish ,pc.`status`,pc.lastDistribution,pc.qqReception,pc.starttime,pc.endtime ";
                $sql .= "FROM P_Customerrecord pc ";
                $sql .= "LEFT JOIN ( ";
                $sql .= "SELECT sa.customer,sa.customer_id, IFNULL(COUNT(sa.customer_id),0) AS finish FROM P_Salecount sa WHERE " . getWhereSql("sa") . " AND sa.customer_id != 0 GROUP BY sa.customer_id ";
                $sql .= ") AS ps ON pc.userid = ps.customer_id ";
                $sql .= "WHERE pc.toplimit > IFNULL(ps.finish,0) AND pc.`status` = 1 ";
                if ($salecount->get_isQQTeach() == 1) {//QQ教学 指定分配
                    $sql .= " AND pc.qqReception = 1 ";
                }
                if ($salecount->get_isTmallTeach_qj() == 1) {//旗舰 指定分配
                    $sql .= " AND pc.tmallReception_qj = 1 ";
                }
                if ($salecount->get_isTmallTeach_zy() == 1) {//专营店 指定分配
                    $sql .= " AND pc.tmallReception_zy = 1 ";
                }
                $sql .= " ORDER BY pc.lastDistribution ASC ";
                $customerres = Model::query_list($db, $customer, $sql);
                $models = Model::list_to_array($customerres['models']);
                if ($customerres['count'] != 0) {
                    $user = $models[0];
                    $customer = $user['username'];
                    $customer_id = $user['userid'];
                    $nick_name = $user['nickname'];
                    $salecount->set_customer($customer);
                    $salecount->set_customer_id($customer_id);
                    $salecount->set_nick_name($nick_name);
                    $fillarrears->set_customer($customer);
                    $fillarrears->set_customer_id($customer_id);
                    $fillarrears->set_nick_name($nick_name);
                } else {
                    die_error(USER_ERROR, '暂无售后,请稍后重试~');
                }
                pdo_transaction($db, function($db) use($fillarrears, $salecount) {
                    $result_fillarrears = $fillarrears->insert($db);
                    if (!$result_fillarrears[0]) throw new TransactionException(PDO_ERROR_CODE, '添加补欠款失败。' . $result_fillarrears['detail_cn'], $result_fillarrears);
                    $result_salecount = $salecount->update($db, true);
                    if (!$result_salecount[0]) throw new TransactionException(PDO_ERROR_CODE, '添加补欠款失败。' . $result_salecount['detail_cn'], $result_salecount);
                });
            } else {
                if ($salecount->get_customer_id() !== $fillarrears->get_customer_id()) {
                    $salecount->set_customer($fillarrears->get_customer());
                    $salecount->set_customer_id($fillarrears->get_customer_id());
                    $salecount->set_nick_name($fillarrears->get_nick_name());
                    pdo_transaction($db, function($db) use($fillarrears, $salecount) {
                        $result_fillarrears = $fillarrears->insert($db);
                        if (!$result_fillarrears[0]) throw new TransactionException(PDO_ERROR_CODE, '添加补欠款失败。' . $result_fillarrears['detail_cn'], $result_fillarrears);
                        $result_salecount = $salecount->update($db, TRUE);
                        if (!$result_salecount[0]) throw new TransactionException(PDO_ERROR_CODE, '添加补欠款失败。' . $result_salecount['detail_cn'], $result_salecount);
                    });
                } else {
                    $result = $fillarrears->insert($db);
                    if (!$result[0]) die_error(USER_ERROR, '添加补欠款失败。');
                }
            }
            echo_msg('添加成功');
        }
    }
    //修改销售统计信息
    if ($action == 2) {
        $fillarrears = new P_Fills();
//        if (isset($fillarrearsData->customer_id)) {
//            if ($fillarrearsData->customer_id2 == $fillarrearsData->customer_id) {
//                die_error(USER_ERROR, '请不要重复选择老师');
//            }
//        }
        $fillarrears->set_field_from_array($fillarrearsData);

        $db = create_pdo();
        $result = $fillarrears->update($db, true);
        if (!$result[0]) die_error(USER_ERROR, '保存统计资料失败');
        echo_msg('保存成功');
    }
    //删除
    if ($action == 3) {
        $fillarrears = new P_Fills();
        $fillarrears->set_field_from_array($fillarrearsData);
        $db = create_pdo();
        $result = $fillarrears->delete($db, true);
        if (!$result[0]) die_error(USER_ERROR, '删除失败');
        echo_msg('删除成功');
    }
});

