<?php

/**
 * 实物业绩
 *
 * @author B.Maru
 * @copyright 2015 星密码
 * @version 2015/7/13
 */
use Models\Base\Model;
use Models\P_Physica_soft;
use Models\Base\SqlOperator;

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
    $keyWord = request_string("keyWord");
    $searchTime = request_string("searchTime");
    $searchStartTime = request_string("searchStartTime");
    $searchEndTime = request_string("searchEndTime");
    $qq = request_string("qq");
    if ($action == 1) {
        $Physical = new P_Physica_soft();
        if (isset($keyWord)) {
            $Physical->set_custom_where(" AND (customer LIKE '%" . $keyWord . "%' OR rception LIKE '%" . $keyWord . "%' OR ww LIKE '%" . $keyWord . "%' OR qq LIKE '%" . $keyWord . "%' OR name LIKE '%" . $keyWord . "%' OR phone LIKE '%" . $keyWord . "%' OR agent_category LIKE '%" . $keyWord . "%' OR agent_price LIKE '%" . $keyWord . "%') ");
        }
        if (isset($searchTime)) {
            $Physical->set_custom_where(" AND DATE_FORMAT(add_time,'%Y-%m-%d') = '" . $searchTime . "' ");
        }
        if (isset($searchStartTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchStartTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $Physical->set_custom_where(" and DATE_FORMAT(add_time, '" . $formatStr . "') >= '" . $searchStartTime . "' ");
        }
        if (isset($searchEndTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchEndTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $Physical->set_custom_where(" and DATE_FORMAT(add_time, '" . $formatStr . "') <= '" . $searchEndTime . "' ");
        }

        if (!in_array($login_userid, array(1, 16, 161, 163))) {
            $Physical->set_where_and(P_Physica_soft::$field_customer_id, SqlOperator::Equals, $login_userid);
        }

        if (isset($sort) && isset($sortname)) {
            $Physical->set_order_by($Physical->get_field_by_name($sortname), $sort);
        } else {
            $Physical->set_order_by(P_Physica_soft::$field_add_time, 'DESC');
        }
        $Physical->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $Physical, NULL, true);
        if (!$result[0]) die_error(USER_ERROR, '获取统计资料失败，请重试');
        $models = Model::list_to_array($result['models']);
        echo_list_result($result, $models);
    }

    if ($action == 5) {
        $physica = new P_Physica_soft();
        $physica->set_custom_where(" AND ( qq like '%" . $qq . "%' OR ww like '%" . $qq . "%' ) ");
        if (!in_array($login_userid, array(1, 161, 163))) {
            $Physical->set_where_and(P_Physica_soft::$field_customer_id, SqlOperator::Equals, $login_userid);
        }
        $db = create_pdo();
        $physica_result = Model::query_list($db, $physica);
        $physica_list = Model::list_to_array($physica_result['models']);
        echo_result($physica_list);
    }
    if ($action == 10) {
        $startTime = request_string("start_time");
        $endTime = request_string("end_time");
        $expolt = new ExportData2Excel();
        $Physical = new P_Physica_soft();
        if (isset($startTime)) {
            $Physical->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $Physical->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') <= '" . $endTime . "' ");
        }
        $Physical->set_query_fields(array('add_time', 'ww', 'qq', 'name', 'phone', 'agent_category', 'agent_price', 'free_price', 'all_price', 'free_decoration', 'isArrears', 'isTeaching', 'alipay_account', 'payment_method', 'customer', 'rception'));
        $db = create_pdo();
        $result = Model::query_list($db, $Physical, NULL, true);
        if (!$result[0]) {
            $expolt->create(array('导出错误'), array(array('实物业绩数据导出失败,请稍后重试!')), "平台业绩数据导出", "平台业绩");
        }
        $models = Model::list_to_array($result['models'], array(), function(&$d) {
                    $d['isArrears'] = $d['isArrears'] === 0 ? "否" : '是';
                    $d['isTeaching'] = $d['isTeaching'] === 0 ? "否" : '是';
                });
        $title_array = array('添加时间', '旺旺', 'QQ', '真实姓名', '手机号码', '代理类目', '代理金额', '免费装修金额', '总金额', '免费装修次数', '是否欠款', '是否立即教学', '支付宝账号', '支付方式', '售后名称', '接待人员');
        $expolt->create($title_array, $models, "实物业绩数据导出", "实物业绩");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $PhysicalData = request_object();
    //添加
    if ($action == 1) {
        $Physical = new P_Physica_soft();
        $Physical->set_field_from_array($PhysicalData);
        if ($PhysicalData->free_decoration) {
            $Physical->set_free_decoration(get_num($PhysicalData->free_decoration));
            if (isset($PhysicalData->all_price)) {
                $agent_price = round(($PhysicalData->all_price) * 0.8, 2);
                $Physical->set_agent_price($agent_price);
                $free_price = round(($PhysicalData->all_price) * 0.2, 2);
                $Physical->set_free_price($free_price);
            }
        } else {
            if (isset($PhysicalData->all_price)) {
                $Physical->set_agent_price($PhysicalData->all_price);
                $Physical->set_all_price(0);
            }
        }
        $Physical->set_add_time('now');
        $db = create_pdo();
        $result = $Physical->insert($db);
        if (!$result[0]) die_error(USER_ERROR, "添加实物业绩信息失败~");
        echo_msg("添加实物业绩信息成功~");
    }
    //删除
    if ($action == 2) {
        $Physical = new P_Physica_soft($PhysicalData->id);
        $db = create_pdo();
        $result = $Physical->delete($db);
        if (!$result[0]) die_error(USER_ERROR, '删除实物业绩信息失败');
        echo_msg('删除实物业绩信息成功~');
    }
    //修改信息
    if ($action == 3) {
        $Physical = new P_Physica_soft($PhysicalData->id);
        $fuck_array = array(1, 161, 163, 178);
        $login_user_id = request_login_userid();
        $db = create_pdo();
        if (!in_array($login_user_id, $fuck_array)) {
            $result = $Physical->load($db, $Physical);
            if (!$result[0]) die_error(USER_ERROR, '系统错误,请稍后重试~');
            if ($Physical->get_is_edit() == 1) {
                die_error(USER_ERROR, '该数据修改次数已上限,暂不能修改~');
            }
        }
        $Physical->reset();
        $Physical->set_field_from_array($PhysicalData);
        $Physical->set_id($PhysicalData->id);
        if ($PhysicalData->free_decoration) {
            $Physical->set_free_decoration(get_num($PhysicalData->free_decoration));
            if (isset($PhysicalData->all_price)) {
                $agent_price = round(($PhysicalData->all_price) * 0.8, 2);
                $Physical->set_agent_price($agent_price);
                $free_price = round(($PhysicalData->all_price) * 0.2, 2);
                $Physical->set_free_price($free_price);
            }
        } else {
            if (isset($PhysicalData->all_price)) {
                $Physical->set_agent_price($PhysicalData->all_price);
                $Physical->set_all_price(0);
                $Physical->set_free_price(0);
            }
        }


        $Physical->set_is_edit(1);
        if ($PhysicalData->free_decoration) {
            $Physical->set_free_decoration(get_num($PhysicalData->free_decoration));
        }
        $result = $Physical->update($db, true);
        if (!$result[0]) die_error(USER_ERROR, '保存统计资料失败');
        echo_msg('保存成功');
    }
});

function get_num($str) {
    $str_array = array(
        '一次' => 1,
        '两次' => 2,
        '三次' => 3,
        '四次' => 4,
        '五次' => 5
    );
    return $str_array[$str];
}

/**
 * 售后,,当前登陆
 * 
 * 
 * 
 */