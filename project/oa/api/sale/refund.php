<?php

/**
 * 退款记录
 *
 * @author QI
 * @copyright 2015 星密码
 * @version 2015/5/14
 */
use Models\Base\Model;
use Models\P_Refund;
use Models\P_Salecount;
use Models\Base\SqlOperator;
use Models\Base\SqlSortType;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../Common/http.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {

    if (!isset($action)) $action = -1;
    if ($action == 1) {
        $refund = new P_Refund();
        $sort = request_string('sort');
        $sortname = request_string('sortname');
        $searchName = request_string('searchName');
        if (isset($searchName)) {
            $refund->set_custom_where(" AND ( presale like '%" . $searchName . "%' "
                    . "OR name LIKE '%" . $searchName . "%' "
                    . "OR customer LIKE '%" . $searchName . "%' "
                    . "OR ww LIKE '%" . $searchName . "%' "
                    . "OR status LIKE '%" . $searchName . "%' "
                    . "OR duty LIKE '%" . $searchName . "%') ");
        }
        if (isset($sort) && isset($sortname)) {
            $refund->set_order_by($refund->get_field_by_name($sortname), $sort);
        } else {
            $refund->set_order_by(P_Refund::$field_id, 'desc');
        }
        $refund->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $refund, NULL, true);
        if (!$result[0]) {
            die_error(USER_ERROR, '获取统计资料失败，请重试');
        }
        $models = Model::list_to_array($result['models']);
        echo_list_result($result, $models);
    }
    if ($action == 5) {
        $sql = "SELECT IFNULL(SUM(r.refund_rate),0) refund_rate,IFNULL(SUM(r.ind_refund_rate),0) ind_refund_rate FROM P_Refund r WHERE r.date = DATE_FORMAT(NOW(),'%Y-%m-%d');";
        $db = create_pdo();
        $result = Model::execute_custom_sql($db, $sql);
        if ($result[0]) echo_result($result['results'][0]);
    }
    if ($action == 11) {
        $startTime = request_datetime("start_time");
        $endTime = request_datetime("end_time");
        $export = new ExportData2Excel();
        $sql = "select a.money refundmoney,a.date,a.totalmoney,a.record_amount,a.refund_type,a.refund_rate,a.retrieve,a.status,a.duty,a.reason,a.name,a.presale,a.customer,a.controlman,a.is_logoff_dbb,a.ind_refund_rate,";
        $sql .= "b.addtime,b.ww,b.qq,b.mobile,b.arrears,b.setmeal,b.payment,b.channel,b.customer2,b.nick_name,b.nick_name2,b.address,b.isQQTeach,b.isTimely from P_Refund as a RIGHT JOIN P_Salecount as b ON a.s_id = b.id ";
        $sql .="where 1=1 ";
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
        array_walk($models, function(&$d) {
            $d['isQQTeach'] = $d['isQQTeach'] === 0 ? "否" : '是';
            $d['isTimely'] = $d['isTimely'] === 0 ? "否" : '是';
        });
        $title_array = array('退款日期', '退款类型', '售后/未收货', '套餐金额', '记录金额', '退款金额', '挽回金额', '实际退款笔数', '个人退款笔数', '是否注销店宝宝', '收款方式', '退款原因', '实际责任', '购买日期', '昵称', '旺旺', '手机号', 'QQ', '欠款', '售前', '售后', '套餐类型', '地址', '是否及时', 'QQ教学', '接入渠道', '真实姓名');
        $field = array('date', 'refund_type', 'status', 'totalmoney', 'record_amount', 'refundmoney', 'retrieve', 'refund_rate', 'ind_refund_rate', 'is_logoff_dbb', 'payment', 'reason', 'duty', 'addtime', 'nick_name', 'ww', 'mobile', 'qq', 'arrears', 'presale', 'customer', 'setmeal', 'address', 'isTimely', 'isQQTeach', 'channel', 'name');
        $export->set_field($field);
        $export->create($title_array, $models, "退款记录数据导出", "退款记录");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $refundData = request_object();
    //添加退款
    if ($action == 1) {
        $refund = new P_Refund();
        $refund->set_field_from_array($refundData);
        $refundarray = (array) $refundData;
        if ($refundarray['date'] == "") {
            $refund->set_date("now");
        }
        $db = create_pdo();
        if (isset($refundData->s_id)) {
            $salecount = new P_Salecount($refundData->s_id);
            $result = $salecount->load($db, $salecount);
            if (!$result[0]) die_error(USER_ERROR, '添加退款失败。');
            $refund->set_ww($salecount->get_ww());
            $refund->set_name($salecount->get_name());
            $refund->set_presale($salecount->get_presales());
            $refund->set_customer($salecount->get_customer());
        } else {
            die_error(USER_ERROR, '添加退款失败。');
        }
        $result = $refund->insert($db);
        if (!$result[0]) die_error(USER_ERROR, '添加退款失败。');
        echo_msg('添加成功');
    }
    //修改退款
    if ($action == 2) {
        $refund = new P_Refund();
        $refund->set_field_from_array($refundData);
        $db = create_pdo();
        $result = $refund->update($db, true);
        if (!$result[0]) die_error(USER_ERROR, '保存退款资料失败');
        echo_msg('保存成功');
    }
    //删除退款
    if ($action == 3) {
        $refund = new P_Refund();
        $refund->set_field_from_array($refundData);
        $db = create_pdo();
        $result = $refund->delete($db, true);
        if (!$result[0]) die_error(USER_ERROR, '删除失败');
        echo_msg('删除成功');
    }
});
