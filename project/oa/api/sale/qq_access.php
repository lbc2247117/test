<?php

/**
 * QQ接入
 *
 * @author QI
 * @copyright 2015 星密码
 * @version 2015/3/16
 */
use Models\Base\Model;
use Models\P_QQAccess;
use Models\P_QQReception;
use Models\M_User;
use Models\Base\SqlSortType;
use Models\Base\SqlOperator;

require '../../application.php';
require '../../loader-api.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    if (!isset($action)) $action = -1;
    $sort = request_string('sort');
    $sortname = request_string('sortname');
    $searchName = request_string('searchName');
    $qq_access = new P_QQAccess();
    $user_id = request_login_userid();
    if (isset($searchName)) {
        $qq_access->set_custom_where(" AND (add_username LIKE '%" . $searchName . "%' OR qq_num LIKE '%" . $searchName . "%' OR customer_num LIKE '%" . $searchName . "%' OR presales LIKE '%" . $searchName . "%' )");
    }
    $qq_access->set_custom_where(" AND (add_userid = " . $user_id . " OR  presales_id = " . $user_id . " ) ");
    if (isset($sort) && isset($sortname)) {
        $qq_access->set_order_by($qq_access->get_field_by_name($sortname), $sort);
    } else {
        $qq_access->set_order_by(P_QQAccess::$field_addtime, SqlSortType::Desc);
    }
    $qq_access->set_limit_paged(request_pageno(), request_pagesize());

    $db = create_pdo();
    $result = Model::query_list($db, $qq_access, NULL, TRUE);
    if (!$result[0]) die_error(USER_ERROR, '获取售后资料失败，请重试');
    $models = Model::list_to_array($result['models']);

    $retArray = array('TodayBaiduPCTotals' => 0, 'TodayBaiduMTotals' => 0, 'Today360Totals' => 0, 'TodaySogouTotals' => 0);
    $groupSumCountSql = "select u.* from P_QQAccess qq LEFT JOIN M_User u ON qq.add_userid = u.userid WHERE " . getWhereSql("qq");
    $user = new M_User();
    $groupSumCountResult = Model::query_list($db, $user, $groupSumCountSql);
    $sumCountModel = Model::list_to_array($groupSumCountResult['models']);
    foreach ($sumCountModel as $model) {
        $grout_id = $model['role_id'];
        if (str_equals(get_group($grout_id), "百度(PC)")) {
            $retArray['TodayBaiduPCTotals'] += 1;
        } else if (str_equals(get_group($grout_id), "百度(YD)")) {
            $retArray['TodayBaiduMTotals'] += 1;
        } else if (str_equals(get_group($grout_id), "360")) {
            $retArray['Today360Totals'] += 1;
        } else if (str_equals(get_group($grout_id), "搜狗")) {
            $retArray['TodaySogouTotals'] += 1;
        }
    }
    echo_list_result($result, $models, array('TodayTotals' => $retArray));
});

function get_group($group_id) {
    $group_array = array(
        701 => '百度(PC)',
        703 => '百度(PC)',
        705 => '百度(PC)',
        707 => '百度(PC)',
        713 => '百度(PC)',
        716 => '百度(YD)',
        702 => '360',
        704 => '360',
        706 => '360',
        708 => '360',
        714 => '360',
        709 => '搜狗',
        710 => '搜狗',
        711 => '搜狗',
        712 => '搜狗',
        715 => '搜狗',
    );
    return $group_array[$group_id];
}

execute_request(HttpRequestMethod::Post, function() use($action) {
    $qq_accessData = request_object();
    if ($action == 1) {
        $request_user_id = request_login_userid();
        $user = get_employees()[$request_user_id];
        $where_id = -1;
        switch ($user['role_id']) {
            case 707:
                $where_id = 705;
                break;
            case 717:
                $where_id = 716;
                break;
            case 708:
                $where_id = 706;
                break;
            case 712:
                $where_id = 711;
                break;
        }
        $sql = "SELECT pr.id,pr.`status`,pr.addtime,pr.presales,pr.presales_id,pr.toplimit,IFNULL(pa.finish,0) AS finish,pr.starttime,pr.endtime,pr.lastDistribution ";
        $sql .="FROM P_QQReception pr LEFT JOIN (";
        $sql .="SELECT COUNT(pa.presales_id) AS finish,pa.presales_id FROM P_QQAccess pa ";
        $sql .="WHERE " . getWhereSql('pa') . " GROUP BY pa.presales_id ) AS pa ON pr.presales_id = pa.presales_id ";
        if ($where_id != -1) {
            $sql .= "INNER JOIN M_User u ON pr.presales_id = u.userid WHERE pr.toplimit > IFNULL(pa.finish,0) AND pr.`status` = 1 AND u.role_id = " . $where_id . " ORDER BY pr.lastDistribution ASC LIMIT 1;";
        } else {
            $sql .="WHERE pr.toplimit > IFNULL(pa.finish,0) AND pr.`status` = 1 ORDER BY pr.lastDistribution ASC LIMIT 1; ";
        }
        $qq_reception = new P_QQReception();
        $db = create_pdo();
        $result = $qq_reception->load($db, $qq_reception, $sql);
        if (!$result[0]) die_error(USER_ERROR, '暂无QQ接待,请稍后重试~');
        $qq_reception->set_lastDistribution((microtime(TRUE) * 10000));
        $qq_access = new P_QQAccess();
        $qq_access->set_field_from_array($qq_accessData);
        $qq_access->set_add_userid(request_login_userid());
        $qq_access->set_add_username(request_login_username());
        $qq_access->set_addtime('now');
        $qq_access->set_presales($qq_reception->get_presales());
        $qq_access->set_presales_id($qq_reception->get_presales_id());
        pdo_transaction($db, function($db) use($qq_access, $qq_reception, $qq_accessData) {
            $access_result = $qq_access->insert($db);
            if (!$access_result[0]) throw new TransactionException(PDO_ERROR_CODE, '添加QQ接入信息失败~' . $access_result['detail_cn'], $access_result);
            $reception_result = $qq_reception->update($db);
            if (!$reception_result[0]) throw new TransactionException(PDO_ERROR_CODE, '添加QQ接入信息失败~' . $reception_result['detail_cn'], $reception_result);
            add_data_add_log($db, $qq_accessData, new P_QQAccess($qq_access->get_id()), 10);
        });
        echo_msg('添加QQ接入信息成功~');
    }
    //修改销售售后信息
    if ($action == 2) {
        $qq_access = new P_QQAccess();
        $qq_access->set_field_from_array($qq_accessData);
        $db = create_pdo();
        if (isset($qq_accessData->do_access)) {
            $qq_access->set_access_time('now');
            add_data_change_log($db, $qq_accessData, new P_QQAccess($qq_accessData->id), 10, '确认通过');
        } else {
            add_data_change_log($db, $qq_accessData, new P_QQAccess($qq_accessData->id), 10);
        }
        $result = $qq_access->update($db, true);
        if (!$result[0]) die_error(USER_ERROR, '保存售后资料失败');
        echo_msg('保存成功');
    }
    //删除
    if ($action == 3) {
        $qq_access = new P_QQAccess();
        $qq_access->set_field_from_array($qq_accessData);
        $db = create_pdo();
        $result = $qq_access->delete($db, true);
        if (!$result[0]) die_error(USER_ERROR, '删除失败');
        echo_msg('删除成功');
    }
});

