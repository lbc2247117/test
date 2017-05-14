<?php

/**
 * 售前统计
 *
 * @author QI
 * @copyright 2015 星密码
 * @version 2015/5/07
 */
use Models\Base\Model;
use Models\M_User;
use Models\p_salestatistics_soft;
use Models\Base\SqlOperator;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    if ($action == 1) {
        $sort = request_string('sort');
        $sortname = request_string('sortname');
        $searchName = request_string('searchName');
        $searchTime = request_string('searchTime');
        $searchChannel = request_string("searchChannel");
        $userid = request_userid();
        $manager_ids = array(0, 1101, 1103);
        $employee = get_employees()[$userid];
        $role_id = $employee['role_id'];
        $is_manager = in_array($role_id, $manager_ids);
        $sql = "SELECT s.id,s.channel,u.salecount_lv lv,s.userid,s.username,s.into_count,s.accept_count,s.deal_count,s.elderly_deal_count,s.timely_count,s.amount,s.addtime ";
        $sql .= "FROM p_salestatistics_soft s ";
        $sql .="INNER JOIN m_user u ON s.userid = u.userid WHERE 1=1 ";
        if (isset($searchName)) {
            $sql.=" AND s.username like '%" . $searchName . "%' ";
        }
        if (isset($searchTime)) {
            $sql.=" AND '" . $searchTime . " 03:00:00' <=  DATE_FORMAT(addtime,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(addtime,'%Y-%m-%d %H:%i:%s') <= '" . date('Y-m-d', strtotime("+1 day", strtotime($searchTime))) . " 02:59:59' ";
        } else {
            $sql.=" AND '" . date("Y-m-d") . " 03:00:00' <=  DATE_FORMAT(addtime,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(addtime,'%Y-%m-%d %H:%i:%s') <= '" . date('Y-m-d', strtotime("+1 day")) . " 02:59:59' ";
        }
        if (!$is_manager) {
            $sql.=" AND s.userid = " . request_login_userid() . " ";
        }
        if (isset($searchChannel)) {
            $sql.=" AND s.channel = '" . $searchChannel . "' ";
        }
        if (isset($sort) && isset($sortname)) {
            $sql.="ORDER BY s." . $sortname . " " . $sort . " ";
        } else {
            $sql.= "ORDER BY s.addtime DESC ";
        }
        $db = create_pdo();
        $sql_res = Model::execute_custom_sql($db, $sql);
        $result_total_count = $sql_res['count'];
        $sql .= "LIMIT " . request_pagesize() * (request_pageno() - 1) . "," . request_pagesize();
        $result = Model::execute_custom_sql($db, $sql);
        if (!$result[0]) {
            die_error(USER_ERROR, '获取统计资料失败，请重试');
        }
        $models = $result['results'];
        array_walk($models, function(&$d)use ($userid, $is_manager) {
            $loss_number = (int) $d['into_count'] - (int) $d['accept_count'];
            $d['commission'] = get_soft_commission($d['amount'], $d['lv']); //提成/日
            $d['loss_number'] = $loss_number; //流失数/日
            $d['loss_rate'] = sprintf("%.2f", ($loss_number / $d['into_count']) * 100) . "%"; //流失率/日 %
            $d['timely_rate'] = sprintf("%.2f", ($d['timely_count'] / $d['accept_count']) * 100) . "%"; //及时率/日 %
            $d['timely_turnover_ratio'] = sprintf("%.2f", ($d['timely_count'] / $d['deal_count']) * 100) . "%"; //及时成交占比 %
            $d['conversion_rate'] = sprintf("%.2f", ($d['deal_count'] / $d['accept_count']) * 100) . "%"; //转化率/日 %
            $d['average_price'] = sprintf("%.2f", ($d['amount'] / $d['deal_count'])); //均价/日
            if ($is_manager) {
                $d['edit'] = true;
                $d['dele'] = true;
            } else {
                $d['edit'] = ($userid == $d['userid']) && str_equals(date('Y-m-d', strtotime($d['addtime'])), date("Y-m-d"));
                $d['dele'] = false; //($userid == $d['userid']) && str_equals(date('Y-m-d', strtotime($d['addtime'])), date("Y-m-d"));
            }
        });
        array_sort_by_field($models, 'conversion_rate', SORT_DESC);
        $d = array('into_count' => 0, 'accept_count' => 0, 'deal_count' => 0, 'timely_count' => 0, 'amount' => 0.00, 'commission' => '/', 'loss_number' => 0, 'loss_rate' => 0, 'timely_rate' => 0, 'timely_turnover_ratio' => 0, 'conversion_rate' => 0, 'average_price' => 0, 'addtime' => (isset($searchTime) ? $searchTime : adjust_time('Y-m-d', '03:00:00')));
        if ($is_manager) {
            $month_count_sql = "SELECT IFNULL(SUM(ps.into_count),0) AS into_count,IFNULL(SUM(ps.accept_count),0) AS accept_count,IFNULL(SUM(ps.deal_count),0) AS deal_count,IFNULL(SUM(ps.elderly_deal_count),0) AS elderly_deal_count,";
            $month_count_sql.="IFNULL(SUM(ps.timely_count),0) AS timely_count,IFNULL(SUM(ps.amount),0) AS amount,'" . (isset($searchTime) ? $searchTime : date("Y-m-d")) . "' AS addtime from p_salestatistics_soft ps WHERE 1=1 ";
            if (isset($searchTime)) {
                $month_count_sql.=" AND '" . $searchTime . " 03:00:00' <=  DATE_FORMAT(ps.addtime,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(ps.addtime,'%Y-%m-%d %H:%i:%s') <= '" . date('Y-m-d', strtotime("+1 day", strtotime($searchTime))) . " 02:59:59' ";
            } else {
                $month_count_sql.=" AND '" . date("Y-m-d") . " 03:00:00' <=  DATE_FORMAT(ps.addtime,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(ps.addtime,'%Y-%m-%d %H:%i:%s') <= '" . date('Y-m-d', strtotime("+1 day")) . " 02:59:59' ";
            }
            if (isset($searchChannel)) {
                $month_count_sql.=" AND ps.channel ='" . $searchChannel . "' ";
            }
            $month_count_result = Model::execute_custom_sql($db, $month_count_sql);
            if (!$month_count_result[0]) {
                die_error(USER_ERROR, '获取统计资料失败，请重试');
            }
            $d = $month_count_result['results'][0];
            $loss_number = (int) $d['into_count'] - (int) $d['accept_count'];
            $d['commission'] = "/"; //get_commission($d['amount'], $d['lv']); //提成/日
            $d['loss_number'] = $loss_number; //流失数/日
            $d['loss_rate'] = sprintf("%.2f", ($loss_number / $d['into_count']) * 100) . "%"; //流失率/日 %
            $d['timely_rate'] = sprintf("%.2f", ($d['timely_count'] / $d['accept_count']) * 100) . "%"; //及时率/日 %
            $d['timely_turnover_ratio'] = sprintf("%.2f", ($d['timely_count'] / $d['deal_count']) * 100) . "%"; //及时成交占比 %
            $d['conversion_rate'] = sprintf("%.2f", ($d['deal_count'] / $d['accept_count']) * 100) . "%"; //转化率/日 %
            $d['average_price'] = sprintf("%.2f", ($d['amount'] / $d['deal_count'])); //均价/日
        }
        echo_result(array('is_manager' => $is_manager, 'total_month_count' => $d, 'total_count' => $result_total_count, 'list' => $models, 'page_no' => request_pageno(), 'max_page_no' => ceil($result_total_count / request_pagesize())));
    }
    /**
     * 获取销售部门提成等级
     */
    if ($action == 2) {
        $sort = request_string('sort');
        $sortname = request_string('sortname');
        $searchUserName = request_string('searchUserName');
        $employee = new M_User();
        $employee->set_where_and(M_User::$field_dept1_id, SqlOperator::Equals, 11);
        $employee->set_where_and(M_User::$field_status, SqlOperator::In, array(1, 2));
        if (isset($searchUserName)) {
            $employee->set_custom_where(" AND username like '%" . $searchUserName . "%' ");
        }
        $employee->set_query_fields(array('userid', 'username', 'salecount_lv'));
        if (isset($sort) && isset($sortname)) {
            $employee->set_order_by($employee->get_field_by_name($sortname), $sort);
        } else {
            $employee->set_order_by(M_User::$field_userid, 'ASC');
        }
        $employee->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $employee, NULL, true);
        if (!$result[0]) {
            die_error(USER_ERROR, '获取统计资料失败，请重试');
        }
        $models = Model::list_to_array($result['models']);
        echo_list_result($result, $models);
    }
    if ($action == 11) {
        $startTime = request_datetime("start_time");
        $endTime = request_datetime("end_time");
        $channel = request_string("channel");
        $export = new ExportData2Excel();
        $sql = "select s.id,s.channel,u.salecount_lv as lv,s.userid,s.username,s.into_count,s.accept_count,s.deal_count,s.elderly_deal_count,s.timely_count,s.amount,s.addtime FROM p_salestatistics_soft s ";
        $sql .="INNER JOIN m_user u ON s.userid = u.userid WHERE 1=1 ";
        if (isset($startTime)) {
            $sql .="AND DATE_FORMAT(s.addtime, '%Y-%m-%d') >= '" . $startTime . "' ";
        }
        if (isset($endTime)) {
            $sql .="AND DATE_FORMAT(s.addtime, '%Y-%m-%d') <= '" . $endTime . "' ";
        }
        if (isset($channel)) {
            $sql .="AND s.channel = '" . $channel . "' ";
        }
        $sql.="ORDER BY s.channel DESC";
        $db = create_pdo();
        $result = Model::execute_custom_sql($db, $sql);
        if (!$result[0]) {
            $export->create(array('导出错误'), array(array('售前统计(日)数据导出失败,请稍后重试!')), "售前统计(日)数据导出", "售前统计(日)");
        }
        $models = $result['results'];
        array_walk($models, function(&$d) {
            $loss_number = (int) $d['into_count'] - (int) $d['accept_count'];
            $d['commission'] = get_commission($d['amount'], $d['lv']); //提成/日
            $d['loss_number'] = $loss_number; //流失数/日
            $d['loss_rate'] = sprintf("%.2f", ($loss_number / $d['into_count']) * 100) . "%"; //流失率/日 %
            $d['timely_rate'] = sprintf("%.2f", ($d['timely_count'] / $d['accept_count']) * 100) . "%"; //及时率/日 %
            $d['timely_turnover_ratio'] = sprintf("%.2f", ($d['timely_count'] / $d['deal_count']) * 100) . "%"; //及时成交占比 %
            $d['conversion_rate'] = sprintf("%.2f", ($d['deal_count'] / $d['accept_count']) * 100) . "%"; //转化率/日 %
            $d['average_price'] = sprintf("%.2f", ($d['amount'] / $d['deal_count'])); //均价/日
        });
        $title_array = array('姓名', '提成等级', '渠道', '转入数/日', '接入数/日', '成交数/日', '老人成交数/日', '及时数/日', '金额/日', '提成/日', '流失数/日', '流失率/日', '及时率/日', '及时成交占比', '转化率/日', '均价/日', '日期');
        $field = array('username', 'lv', 'channel', 'into_count', 'accept_count', 'deal_count', 'elderly_deal_count', 'timely_count', 'amount', 'commission', 'loss_number', 'loss_rate', 'timely_rate', 'timely_turnover_ratio', 'conversion_rate', 'average_price', 'addtime');
        $export->set_field($field);
        $export->set_field_width(array(12, 8, 15, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 17));
        $export->create($title_array, $models, "售前统计(日)数据导出", "售前统计(日)");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $saleStatisticsData = request_object();
    if ($action == 1) {
        $saleStatistics = new p_salestatistics_soft();
        $h = (int) date("H");
        if ($h < 3) {
            $where_sql = " '" . date('Y-m-d', strtotime("-1 day")) . " 03:00:00' <=  DATE_FORMAT(addtime,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(addtime,'%Y-%m-%d %H:%i:%s') <= '" . date('Y-m-d') . " 02:59:59' ";
        } else {
            $where_sql = " '" . date('Y-m-d') . " 03:00:00' <=  DATE_FORMAT(addtime,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(addtime,'%Y-%m-%d %H:%i:%s') <= '" . date('Y-m-d', strtotime("+1 day")) . " 02:59:59' ";
        }
        $saleStatistics->set_custom_where(" AND " . $where_sql . " AND userid = " . $saleStatisticsData->userid);
        $db = create_pdo();
        $count = $saleStatistics->count($db);
        if ($count == 0) {
            $saleStatistics->reset();
            $saleStatistics->set_field_from_array($saleStatisticsData);
            $saleStatistics->set_addtime('now');
            $employee = get_employees()[$saleStatisticsData->userid];
            $result = $saleStatistics->insert($db);
            if (!$result[0]) die_error(USER_ERROR, '添加统计失败~');
            echo_msg('添加成功');
        }else {
            die_error(USER_ERROR, '添加统计失败，当天统计资料已录入~');
        }
    }
    if ($action == 2) {
        $saleStatistics = new p_salestatistics_soft($saleStatisticsData->id);
        $db = create_pdo();
        $result = $saleStatistics->delete($db);
        if (!$result[0]) die_error(USER_ERROR, '删除失败。');
        echo_msg('删除成功');
    }
    if ($action == 3) {
        $saleStatistics = new p_salestatistics_soft($saleStatisticsData->id);
        $saleStatistics->set_field_from_array($saleStatisticsData);
        $db = create_pdo();
        $result = $saleStatistics->update($db, true);
        if (!$result[0]) die_error(USER_ERROR, '修改失败。');
        echo_msg('修改成功');
    }
    /**
     * 修改销售等级
     */
    if ($action == 4) {
        $employee = new M_User($saleStatisticsData->userid);
        $employee->set_salecount_lv($saleStatisticsData->salecount_lv);
        $db = create_pdo();
        $result = $employee->update($db, true);
        if (!$result[0]) die_error(USER_ERROR, '修改失败。');
        echo_msg('修改成功');
    }
});
