<?php

/**
 * 装修业绩
 *
 * @author B.Maru
 * @copyright 2015 星密码
 * @version 2015/7/13
 */
use Models\Base\Model;
use Models\P_Decoration;
use Models\Base\SqlOperator;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../common/http.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {

    $login_userid = request_login_userid();
    $manager_role_ids = array(0, 101, 102, 103, 401, 402, 403, 404, 601, 602, 701, 702, 713, 714, 715, 801, 802, 901, 902, 1102);
    $manager_userids = array(43, 187, 39, 48, 42, 52, 291, 19, 24, 25, 26, 27, 30, 31, 32, 263, 316);
    $is_manager = in_array(get_role_id(), $manager_role_ids) || in_array($login_userid, $manager_userids);

    $sort = request_string('sort');
    $sortname = request_string('sortname');
    $workName = request_string("workName");
    $wwName = request_string("wwName");
    $searchTime = request_string("searchTime");
    $searchStartTime = request_string("searchStartTime");
    $searchEndTime = request_string("searchEndTime");
    $qq = request_string("qq");
    if ($action == 1) {
        $decoration = new P_Decoration();
        if (isset($workName)) {
            $decoration->set_custom_where(" AND (customer LIKE '%" . $workName . "%' OR rception LIKE '%" . $workName . "%') ");
        }
        if (isset($wwName)) {
            $decoration->set_where_and(P_Decoration::$field_ww, SqlOperator::Like, "%" . $wwName . "%");
        }
        if (isset($searchTime)) {
            $decoration->set_custom_where(" AND DATE_FORMAT(add_time,'%Y-%m-%d') = '" . $searchTime . "' ");
        }
        if (isset($searchStartTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchStartTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $decoration->set_custom_where(" and DATE_FORMAT(add_time, '" . $formatStr . "') >= '" . $searchStartTime . "' ");
        }
        if (isset($searchEndTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchEndTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $decoration->set_custom_where(" and DATE_FORMAT(add_time, '" . $formatStr . "') <= '" . $searchEndTime . "' ");
        }
        if (isset($sort) && isset($sortname)) {
            $decoration->set_order_by($decoration->get_field_by_name($sortname), $sort);
        } else {
            $decoration->set_order_by(P_Decoration::$field_add_time, 'DESC');
        }
        $decoration->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $decoration, NULL, true);
        if (!$result[0]) die_error(USER_ERROR, '获取统计资料失败，请重试');
        $models = Model::list_to_array($result['models'], array(), function(&$d) use($is_manager, $login_userid) {
                    if (!$is_manager && !str_equals($d['rception_id'], $login_userid) && !str_equals($d['customer_id'], $login_userid)) {
                        $d['ww'] = '****';
                        $d['name'] = '***';
                        $d['qq'] = '********';
                        $d['phone'] = '***********';
                        $d['alipay_account'] = '***********';
                        $d['decoration_packages'] = '***';
                        $d['decoration_price'] = '***';
                    }
                });
        echo_list_result($result, $models);
    }
    if ($action == 5) {
        $decoration = new P_Decoration();
        $decoration->set_custom_where(" AND ( qq like '%" . $qq . "%' OR ww like '%" . $qq . "%' ) ");
        $db = create_pdo();
        $decoration_result = Model::query_list($db, $decoration);
        $decoration_list = Model::list_to_array($decoration_result['models']);
        echo_result($decoration_list);
    }
    if ($action == 10) {
        $startTime = request_string("start_time");
        $endTime = request_string("end_time");
        $expolt = new ExportData2Excel();
        $decoration = new P_Decoration();
        if (isset($startTime)) {
            $decoration->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $decoration->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') <= '" . $endTime . "' ");
        }
        $decoration->set_query_fields(array('add_time', 'ww', 'qq', 'name', 'phone', 'decoration_packages', 'decoration_price', 'isArrears', 'alipay_account', 'payment_method', 'customer', 'rception'));
        $db = create_pdo();
        $result = Model::query_list($db, $decoration, NULL, true);
        if (!$result[0]) {
            $expolt->create(array('导出错误'), array(array('装修业绩数据导出失败,请稍后重试!')), "平台业绩数据导出", "平台业绩");
        }
        $models = Model::list_to_array($result['models'], array(), function(&$d) {
                    $d['isArrears'] = $d['isArrears'] === 0 ? "否" : '是';
                });
        $title_array = array('添加时间', '旺旺', 'QQ', '真实姓名', '手机号码', '装修套餐', '装修金额', '是否欠款', '支付宝账号', '支付方式', '售后名称', '接待人员');
        $expolt->create($title_array, $models, "装修业绩数据导出", "装修业绩");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $decorationData = request_object();
    //添加
    if ($action == 1) {
        $decoration = new P_Decoration();
        $decoration->set_field_from_array($decorationData);
        $decoration->set_add_time('now');
        $db = create_pdo();
        $result = $decoration->insert($db);
        if (!$result[0]) die_error(USER_ERROR, "添加装修业绩信息失败~");
        echo_msg("添加装修业绩信息成功~");
    }
    //删除
    if ($action == 2) {
        $decoration = new P_Decoration($decorationData->id);
        $db = create_pdo();
        $result = $decoration->delete($db);
        if (!$result[0]) die_error(USER_ERROR, '删除装修业绩信息失败');
        echo_msg('删除装修业绩信息成功~');
    }
    //修改信息
    if ($action == 3) {
        $decoration = new P_Decoration($decorationData->id);
        $fuck_array = array(35, 37);
        $login_user_id = request_login_userid();
        $db = create_pdo();
        if (!in_array($login_user_id, $fuck_array)) {
            $result = $decoration->load($db, $decoration);
            if (!$result[0]) die_error(USER_ERROR, '系统错误,请稍后重试~');
            if ($decoration->get_is_edit() == 1) {
                die_error(USER_ERROR, '该数据修改次数已上限,暂不能修改~');
            }
        }
        $decoration->reset();
        $decoration->set_field_from_array($decorationData);
        $decoration->set_id($decorationData->id);
        $decoration->set_is_edit(1);
        $result = $decoration->update($db, true);
        if (!$result[0]) die_error(USER_ERROR, '保存统计资料失败');
        echo_msg('保存成功');
    }
});
