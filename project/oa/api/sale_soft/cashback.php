<?php

/**
 * 返现记录(软件)
 *
 * @author QI
 * @copyright 2015 星密码
 * @version 2015/6/4
 */
use Models\Base\Model;
use Models\P_Cashback_soft;
use Models\P_Salecount_soft;
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
        $cashback = new P_Cashback_soft();
        $sort = request_string('sort');
        $sortname = request_string('sortname');
        $searchName = request_string('searchName');
        $searchTime = request_string('searchTime');
        $login_userid = request_login_userid();
        $is_manager = is_manager($login_userid, 2);
        if (isset($searchName)) {
            $cashback->set_custom_where(" AND ( presale like '%" . $searchName . "%' OR name LIKE '%" . $searchName . "%' OR customer like'%" . $searchName . "%'"
                    . " OR cashback_shop like'%" . $searchName . "%' OR cashback_reason like'%" . $searchName . "%'"
                    . " OR ww like'%" . $searchName . "%' OR duty like'%" . $searchName . "%') ");
        }
        if (isset($searchTime)) {
            $cashback->set_custom_where(" AND DATE_FORMAT(date, '%Y-%m-%d') = '" . $searchTime . "'");
        }
        if (!$is_manager) {
            $cashback->set_custom_where(" and (presale_id = " . $login_userid . " or customer_id = " . $login_userid . ") ");
        }
        if (isset($sort) && isset($sortname)) {
            $cashback->set_order_by($cashback->get_field_by_name($sortname), $sort);
        } else {
            $cashback->set_order_by(P_Cashback_soft::$field_id, 'desc');
        }
        $cashback->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $cashback, NULL, true);
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
        $cashback = new P_Cashback_soft();
        if (isset($startTime)) {
            $cashback->set_custom_where(" and DATE_FORMAT(date, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $cashback->set_custom_where(" and DATE_FORMAT(date, '%Y-%m-%d') <= '" . $endTime . "' ");
        }
        $field = array('buydate', 'date', 'cashback', 'cashback_shop', 'cashback_reason', 'name', 'ww', 'channel', 'money', 'presale', 'customer', 'duty');
        $cashback->set_query_fields($field);
        $db = create_pdo();
        $result = Model::query_list($db, $cashback, NULL, true);
        if (!$result[0]) {
            $export->create(array('导出错误'), array(array('返现记录数据导出失败,请稍后重试!')), "返现记录数据导出", "返现记录");
        }
        $models = Model::list_to_array($result['models'], array(), function(&$d) {
                    $d['isTimely'] = $d['isTimely'] === 0 ? "否" : '是';
                });
        $title_array = array('购买日期', '返现日期', '返现金额', '返现店铺', '返现原因', '姓名', '旺旺名', '接入渠道', '套餐金额', '售前', '售后', '实际责任');
        $export->set_field($field);
        $export->create($title_array, $models, "返现记录数据导出", "返现记录");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $cashbackData = request_object();
    //添加返现
    if ($action == 1) {
        $cashback = new P_Cashback_soft();
        $cashback->set_field_from_array($cashbackData);
        if (!isset($cashbackData->date) || str_equals(($cashbackData->date), "")) {
            $cashback->set_date("now");
        }
        $db = create_pdo();
        $salecount = new P_Salecount_soft($cashbackData->s_id);
        $result_salecount = $salecount->load($db, $salecount);
        if (!$result_salecount[0]) die_error(USER_ERROR, '保存返现信息失败~');
        $cashback->set_presale($salecount->get_presales());
        $cashback->set_presale_id($salecount->get_presales_id());
        if (!str_equals(($salecount->get_customer2()), "")) {
            $cashback->set_customer($salecount->get_customer2());
            $cashback->set_customer_id($salecount->get_customer_id2());
        } else {
            $cashback->set_customer($salecount->get_customer());
            $cashback->set_customer_id($salecount->get_customer_id());
        }
        $cashback->set_name($salecount->get_name());
        $buydate = $salecount->get_addtime();
        $cashback->set_buydate($buydate->format('Y-m-d H:i:s'));
        $cashback->set_ww($salecount->get_ww());
        $cashback->set_channel($salecount->get_channel());
        $cashback->set_money($salecount->get_money());
        $result = $cashback->insert($db);
        if (!$result[0]) die_error(USER_ERROR, '添加返现失败。');
        echo_msg('添加成功');
    }
    //修改返现
    if ($action == 2) {
        $cashback = new P_Cashback_soft();
        $cashback->set_field_from_array($cashbackData);
        $db = create_pdo();
        $result = $cashback->update($db, true);
        if (!$result[0]) die_error(USER_ERROR, '保存返现资料失败');
        echo_msg('保存成功');
    }
    //删除返现
    if ($action == 3) {
        $cashback = new P_Cashback_soft();
        $cashback->set_field_from_array($cashbackData);
        $db = create_pdo();
        $result = $cashback->delete($db, true);
        if (!$result[0]) die_error(USER_ERROR, '删除失败');
        echo_msg('删除成功');
    }
});
