<?php

/**
 * 退款记录
 *
 * @author QI
 * @copyright 2015 星密码
 * @version 2015/5/14
 */
use Models\Base\Model;
use Models\P_Refund_soft;
use Models\P_Salecount_soft;
use Models\Base\SqlOperator;
use Models\Base\SqlSortType;
use Models\P_Customerdetails_soft;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../Common/http.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    $db = create_pdo();
    if (!isset($action)) $action = -1;
    if ($action == 1) {
        $refund = new P_Refund_soft();
        $sort = request_string('sort');
        $sortname = request_string('sortname');
        $searchName = request_string('searchName');
        $searchTime = request_string('searchTime');
        $refund_type = request_string("refund_type");
        $login_userid = request_login_userid();
        $is_manager = is_manager($login_userid, 2);
        if (isset($searchName)) {
            $refund->set_custom_where(" AND ( presale like '%" . $searchName . "%' "
                    . "OR name LIKE '%" . $searchName . "%' "
                    . "OR customer like'%" . $searchName . "%' "
                    . "OR ww LIKE '%" . $searchName . "%' "
                    . "OR status LIKE '%" . $searchName . "%' "
                    . "OR duty LIKE '%" . $searchName . "%' "
                    . "OR controlman LIKE '%" . $searchName . "%' ) ");
        }
        if (isset($searchTime)) {
            $refund->set_custom_where(" AND DATE_FORMAT(date, '%Y-%m-%d') = '" . $searchTime . "'");
        }
        if (isset($refund_type)) {
            $refund->set_where_and(P_Refund_soft::$field_refund_type, SqlOperator::Equals, $refund_type);
        }
        if (!$is_manager) {
            $refund->set_custom_where(" and ( customer_id = " . $login_userid . " or presale_id = " . $login_userid . " ) ");
        }
        if (isset($sort) && isset($sortname)) {
            $refund->set_order_by($refund->get_field_by_name($sortname), $sort);
        } else {
            $refund->set_order_by(P_Refund_soft::$field_id, 'desc');
        }
        $refund->set_limit_paged(request_pageno(), request_pagesize());
        $result = Model::query_list($db, $refund, NULL, true);
        if (!$result[0]) {
            die_error(USER_ERROR, '获取统计资料失败，请重试');
        }
        $models = Model::list_to_array($result['models']);
        echo_list_result($result, $models);
    }
    if ($action == 11) {
        $startTime = request_datetime("start_time");
        $endTime = request_datetime("end_time");
        $export = new ExportData2Excel();
        $sql = "select a.date,a.ww,b.mobile,a.setmeal,a.refund_type,a.`status`,b.money,a.money AS tk_money,a.recordMoney,a.retrieve,a.refund_rate,a.presale,a.customer,a.duty,a.refund_shop,a.remark ";
        $sql .=" from P_Refund_soft as a right join P_Salecount_soft as b on a.s_id = b.id where 1=1 ";
        if (isset($startTime)) {
            $sql.=" and DATE_FORMAT(a.date, '%Y-%m-%d') >= '" . $startTime . "'";
        }
        if (isset($endTime)) {
            $sql .=" and DATE_FORMAT(a.date, '%Y-%m-%d') <= '" . $endTime . "' ";
        }
        $sql .= "order by a.id desc";
        $db = create_pdo();
        $result = Model::execute_custom_sql($db, $sql);
        if (!$result[0]) {
            $export->create(array('导出错误'), array(array('退款记录数据导出失败,请稍后重试!')), "退款记录数据导出", "退款记录");
        }
        $models = $result['results'];
        array_walk($models, function (&$d) {
            $d['customer'] = $d['customer2'] ? $d['customer2'] : $d['customer'];
        });
        $title_array = array('日期', '旺旺', '手机号', '版本', '退款类型', '退款方式', '套餐金额', '退款金额', '记录金额', '挽回金额', '退款率', '售前', '售后', '实际责任', '退款店铺', '退款原因', '备注');
        $field = array('date', 'ww', 'mobile', 'setmeal', 'refund_type', 'status', 'money', 'tk_money', 'recordMoney', 'retrieve', 'refund_rate', 'presale', 'customer', 'duty', 'refund_shop', 'remark');
        $export->set_field($field);
        $export->set_field_width(array(15, 20, 15, 15, 15, 15, 15, 15, 15, 15, 15, 12, 12, 15, 15, 15, 30));
        $export->create($title_array, $models, "退款记录数据导出", "退款记录");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $refundData = request_object();
    //添加退款
    if ($action == 1) {
        $refund = new P_Refund_soft();
        $refund->set_field_from_array($refundData);
        if (!isset($refundData->date) || str_equals(($refundData->date), "")) {
            $refund->set_date("now");
        }
        $db = create_pdo();
        $salecount = new P_Salecount_soft($refundData->s_id);
        $result_salecount = $salecount->load($db, $salecount);
        if (!$result_salecount[0]) die_error(USER_ERROR, '保存退款信息失败~');
        $refund->set_presale($salecount->get_presales());
        $refund->set_presale_id($salecount->get_presales_id());
        if (!str_equals(($salecount->get_customer2()), "")) {
            $refund->set_customer($salecount->get_customer2());
            $refund->set_customer_id($salecount->get_customer_id2());
        } else {
            $refund->set_customer($salecount->get_customer());
            $refund->set_customer_id($salecount->get_customer_id());
        }
        $refund->set_name($salecount->get_name());
        $refund->set_ww($salecount->get_ww());
        $refund->set_totalmoney($salecount->get_money());
        $refund->set_setmeal($salecount->get_setmeal());
        $refund->set_refund_shop($salecount->get_c_shop());
        pdo_transaction($db, function($db) use($refund,$refundData) {
            $result = $refund->insert($db);
            if (!$result[0]) throw new TransactionException(PDO_ERROR_CODE, '保存退款信息失败~' . $result['detail_cn'], $result);
            add_data_add_log($db, $refundData, new P_Refund_soft($refund->get_id()), 10);
            $refund_id = $refund->get_s_id();
            $customerdetails = new P_Customerdetails_soft();
            $customerdetails->set_where_and(P_Customerdetails_soft::$field_sale_id, SqlOperator::Equals, $refund_id);
            $result_customerdetails = $customerdetails->load($db, $customerdetails);
            if (!$result_customerdetails[0]) throw new TransactionException(PDO_ERROR_CODE, '保存退款信息失败~' . $result_customerdetails['detail_cn'], $result_customerdetails);
            $customerdetails->set_is_refund(1);
            $customerdetails_update_result = $customerdetails->update($db, true);
            if (!$customerdetails_update_result[0]) throw new TransactionException(PDO_ERROR_CODE, '保存退款信息失败~' . $customerdetails_update_result['detail_cn'], $customerdetails_update_result);
        });
        echo_msg('添加成功');
    }
    //修改退款
    if ($action == 2) {
        $refund = new P_Refund_soft($refundData->id);
        $db = create_pdo();
        $refund->load($db, $refund);
        $refund->set_field_from_array($refundData);
        $totalmoney = $refund->get_totalmoney();
        $money = $refund->get_money();
        $refund->set_retrieve($totalmoney - $money);
        add_data_change_log($db, $refundData, new P_Refund_soft($refund->get_id()), 10);
        $result = $refund->update($db, true);
        if (!$result[0]) die_error(USER_ERROR, '保存退款资料失败');
        echo_msg('保存成功');
    }
    //删除退款
    if ($action == 3) {
        $refund = new P_Refund_soft();
        $refund->set_field_from_array($refundData);
        $db = create_pdo();
        $result = $refund->delete($db, true);
        if (!$result[0]) die_error(USER_ERROR, '删除失败');
        echo_msg('删除成功');
    }
});
