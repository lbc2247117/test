<?php

/**
 * 平台业绩
 *
 * @author B.Maru
 * @copyright 2015 星密码
 * @version 2015/7/13
 */
use Models\Base\Model;
use Models\P_Platform_soft;
use Models\Base\SqlOperator;
use Models\P_Customerrecord_second_soft;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../common/http.php';


$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    $login_userid = request_login_userid();
    $manager_role_ids = array(0, 101, 102, 103, 401, 402, 403, 404, 601, 602, 701, 702, 801, 802, 901, 902, 1101, 1102, 1103, 1112);
    $manager_userids = array(187);
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
        $platform = new P_Platform_soft();
        if (isset($workName)) {
            $platform->set_custom_where(" AND (customer LIKE '%" . $workName . "%' OR rception_staff LIKE '%" . $workName . "%') ");
        }
        if (isset($wwName)) {
            $platform->set_where_and(P_Platform_soft::$field_ww, SqlOperator::Like, "%" . $wwName . "%");
        }
        if (isset($searchTime)) {
            $platform->set_custom_where(" AND DATE_FORMAT(add_time,'%Y-%m-%d') = '" . $searchTime . "' ");
        }
        if (isset($searchStartTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchStartTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $platform->set_custom_where(" and DATE_FORMAT(add_time, '" . $formatStr . "') >= '" . $searchStartTime . "' ");
        }
        if (isset($searchEndTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchEndTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $platform->set_custom_where(" and DATE_FORMAT(add_time, '" . $formatStr . "') <= '" . $searchEndTime . "' ");
        }
        if (!in_array($login_userid, array(1, 16, 161, 163))) {
            $platform->set_where_and(P_Platform_soft::$field_customer_id, SqlOperator::Equals, $login_userid);
        }
        if (isset($sort) && isset($sortname)) {
            $platform->set_order_by($platform->get_field_by_name($sortname), $sort);
        } else {
            $platform->set_order_by(P_Platform_soft::$field_add_time, 'DESC');
        }
        $platform->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $platform, NULL, true);
        if (!$result[0]) die_error(USER_ERROR, '获取统计资料失败，请重试');
        $models = Model::list_to_array($result['models']);
        echo_list_result($result, $models);
    }
    if ($action == 2) {
        $customer = new P_Customerrecord_second_soft();
        $customer->set_status(1);
        $customer->set_query_fields(array('userid', 'username', 'nickname'));
        $db = create_pdo();
        $customer_result = Model::query_list($db, $customer);
        $customer_list = Model::list_to_array($customer_result['models'], array(), function(&$d) {
                    $d['id'] = $d['userid'];
                    $d['text'] = $d['username'] . "(" . $d['nickname'] . ")";
                    unset($d['userid']);
                    unset($d['username']);
                    unset($d['nickname']);
                });
        echo_result($customer_list);
    }
    if ($action == 5) {
        $platform = new P_Platform_soft();
        $platform->set_custom_where(" AND ( qq like '%" . $qq . "%' OR ww like '%" . $qq . "%' ) ");
        $db = create_pdo();
        $platform_result = Model::query_list($db, $platform);
        $platform_list = Model::list_to_array($platform_result['models']);
        echo_result($platform_list);
    }
    if ($action == 6) {
        $search_name = request_string("searchName");
        if (isset($search_name)) {
            $platform = new P_Platform_soft();
            $platform->set_custom_where(" AND ( ww = '" . $search_name . "' OR alipay_account = '" . $search_name . "' OR name = '" . $search_name . "' OR phone = '" . $search_name . "' OR qq = '" . $search_name . "' ) ");
            $platform->set_query_fields(array('ww', 'alipay_account', 'name', 'phone', 'qq', 'payment_method'));
            $db = create_pdo();
            $result = Model::query_list($db, $platform);
            if (!$result[0]) die_error(USER_ERROR, "查询失败,请稍后重试或手动录入~");
            $model = Model::list_to_array($result['models']);
            echo_result(array('code' => 0, 'model' => $model[0]));
        } else {
            echo_result(array('code' => 0, 'model' => array()));
        }
    }
    if ($action == 10) {
        $startTime = request_string("start_time");
        $endTime = request_string("end_time");
        $expolt = new ExportData2Excel();
        $platform = new P_Platform_soft();
        if (isset($startTime)) {
            $platform->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $platform->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') <= '" . $endTime . "' ");
        }
        $platform->set_query_fields(array('add_time', 'ww', 'qq', 'name', 'money', 'diamond_card', 'p_arrears', 'alipay_account', 'payment_method', 'customer', 'rception_staff', 'isTeach', 'md_arrears'));
        $db = create_pdo();
        $result = Model::query_list($db, $platform, NULL, true);
        if (!$result[0]) {
            $expolt->create(array('导出错误'), array(array('平台业绩数据导出失败,请稍后重试!')), "平台业绩数据导出", "平台业绩");
        }
        $models = Model::list_to_array($result['models'], array(), function(&$d) {
                    $d['isTeach'] = $d['isTeach'] === 0 ? "否" : '是';
                });
        $title_array = array('添加时间', '旺旺', 'QQ', '真实姓名', '付款金额', '钻卡', '平台欠款', '支付宝账号', '支付方式', '售后名称', '平台接待人员', '是否教学', '实物/装修欠款');
        $expolt->create($title_array, $models, "平台业绩数据导出", "平台业绩");
    }
    if ($action == 11) {
        $time_unit = request_int('time_unit', 1, 3);
        $condition_mapping = array(
            1 => 'TO_DAYS(add_time) = TO_DAYS(NOW())',
            2 => 'add_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)',
            3 => "DATE_FORMAT(add_time,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m')"
        );
        $sql = "SELECT a.customer,a.customer_id,SUM(a.count) count  FROM(";
        $sql .="SELECT * FROM (";
        $sql .="SELECT customer customer,customer_id customer_id,SUM(decoration_price) count FROM P_Decoration_soft WHERE " . $condition_mapping[$time_unit] . " GROUP BY customer ORDER BY COUNT(customer) DESC";
        $sql .=") d ";
        $sql .="UNION ALL ";
        $sql .="SELECT * FROM (";
        $sql .="SELECT customer customer,customer_id customer_id,SUM(money) count FROM P_Platform_soft WHERE " . $condition_mapping[$time_unit] . " GROUP BY customer ORDER BY COUNT(customer) DESC";
        $sql .=") p ";
        $sql .="UNION ALL ";
        $sql .="SELECT * FROM (";
        $sql .="SELECT customer customer,customer_id customer_id,SUM(all_price) count FROM P_Physica_soft h WHERE " . $condition_mapping[$time_unit] . " GROUP BY customer ORDER BY COUNT(customer) DESC";
        $sql .=") h ";
        $sql .=")a GROUP BY a.customer_id ORDER BY SUM(a.count) desc";
        $db = create_pdo();
        $result = Model::execute_custom_sql($db, $sql);
        if (!$result[0]) die_error(USER_ERROR, '获取排名数据失败，请重试');
        $result = $result['results'];
        echo_result($result);
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $platformData = request_object();
    //添加
    if ($action == 1) {
        $platform = new P_Platform_soft();
        $platform->set_field_from_array($platformData);
        $platform->set_add_time('now');
        $db = create_pdo();
        $result = $platform->insert($db);
        if (!$result[0]) die_error(USER_ERROR, "添加平台业绩信息失败~");
        echo_msg("添加平台业绩信息成功~");
    }
    //删除
    if ($action == 2) {
        $platform = new P_Platform_soft($platformData->id);
        $db = create_pdo();
        $result = $platform->delete($db);
        if (!$result[0]) die_error(USER_ERROR, '删除平台业绩信息失败');
        echo_msg('删除平台业绩信息成功~');
    }
    //修改信息
    if ($action == 3) {
        $platform = new P_Platform_soft($platformData->id);
        $fuck_array = array(1, 161, 163, 178);
        $login_user_id = request_login_userid();
        $db = create_pdo();
        if (!in_array($login_user_id, $fuck_array)) {
            $result = $platform->load($db, $platform);
            if (!$result[0]) die_error(USER_ERROR, '系统错误,请稍后重试~');
            if ($platform->get_is_edit() == 1) {
                die_error(USER_ERROR, '该数据修改次数已上限,暂不能修改~');
            }
        }
        $platform->reset();
        $platform->set_field_from_array($platformData);
        $platform->set_id($platformData->id);
        $platform->set_is_edit(1);
        $result = $platform->update($db, true);
        if (!$result[0]) die_error(USER_ERROR, '保存统计资料失败');
        echo_msg('保存成功');
    }
});
