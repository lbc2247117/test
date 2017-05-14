<?php

/**
 * 售前统计
 *
 * @author QI
 * @copyright 2015 星密码
 * @version 2015/5/07
 */
use Models\Base\Model;
use Models\P_SaleStatistics;
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
        $manager_ids = array(0, 701, 702, 703, 704, 709, 710);
        $employee = get_employees()[$userid];
        $role_id = $employee['role_id'];
        $is_manager = in_array($role_id, $manager_ids);
        if (!isset($searchTime)) {
            $searchTime = date('Y-m');
        }
        $saleStatistics = new P_SaleStatistics();
        $sql = "SELECT s.userid,s.username,s.channel,u.salecount_lv lv,SUM(s.into_count) AS into_count,SUM(s.accept_count) AS accept_count,SUM(s.deal_count) AS deal_count,";
        $sql.="SUM(s.timely_count) AS timely_count,SUM(s.amount) AS amount,DATE_FORMAT(s.addtime,'%Y-%m') AS addtime FROM p_salestatistics s ";
        $sql.="INNER JOIN m_user u ON s.userid = u.userid WHERE 1=1 ";
        if (isset($searchName)) {
            $sql .= "AND s.username like '%" . $searchName . "%' ";
        }
        if (isset($searchChannel)) {
            $sql.=" AND s.channel = '" . $searchChannel . "' ";
        }
        $sql .= "AND DATE_FORMAT(s.addtime, '%Y-%m') = '" . $searchTime . "' GROUP BY s.userid ";
        if (isset($sort) && isset($sortname)) {
            $sql .= "ORDER BY SUM(s." . $sortname . ") " . $sort . " ";
        } else {
            $sql .= "ORDER BY s.addtime desc ";
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
        array_walk($models, function(&$d) {
            $loss_number = (int) $d['into_count'] - (int) $d['accept_count'];
            $d['commission'] = get_commission($d['amount'], $d['lv']); //提成/日
            $d['loss_number'] = $loss_number; //流失数/日
            $d['loss_rate'] = sprintf("%.2f", ($loss_number / $d['into_count']) * 100); //流失率/日 %
            $d['timely_rate'] = sprintf("%.2f", ($d['timely_count'] / $d['accept_count']) * 100); //及时率/日 %
            $d['timely_turnover_ratio'] = sprintf("%.2f", ($d['timely_count'] / $d['deal_count']) * 100); //及时成交占比 %
            $d['conversion_rate'] = sprintf("%.2f", ($d['deal_count'] / $d['accept_count']) * 100); //转化率/日 %
            $d['average_price'] = sprintf("%.2f", ($d['amount'] / $d['deal_count'])); //均价/日
        });
        array_sort_by_field($models, 'conversion_rate', SORT_DESC);

        $month_count_sql = "SELECT IFNULL(SUM(ps.into_count),0) AS into_count,IFNULL(SUM(ps.accept_count),0) AS accept_count,IFNULL(SUM(ps.deal_count),0) AS deal_count,IFNULL(SUM(ps.timely_count),0) AS timely_count,IFNULL(SUM(ps.amount),0) AS amount,IFNULL(DATE_FORMAT(ps.addtime,'%Y-%m'),'" . $searchTime . "') AS addtime from P_SaleStatistics ps ";
        $month_count_sql .="WHERE 1=1 AND DATE_FORMAT(ps.addtime, '%Y-%m') = '" . $searchTime . "' ";
        if (isset($searchChannel)) {
            $month_count_sql.=" AND ps.channel = '" . $searchChannel . "' ";
        }
        $month_count_result = Model::execute_custom_sql($db, $month_count_sql);
        if (!$month_count_result[0]) {
            die_error(USER_ERROR, '获取统计资料失败，请重试');
        }
        $d = $month_count_result['results'][0];
        $loss_number = (int) $d['into_count'] - (int) $d['accept_count'];
        $d['commission'] = "/"; //get_commission($d['amount'],$d['lv']); //提成/日
        $d['loss_number'] = $loss_number; //流失数/日
        $d['loss_rate'] = sprintf("%.2f", ($loss_number / $d['into_count']) * 100) . "%"; //流失率/日 %
        $d['timely_rate'] = sprintf("%.2f", ($d['timely_count'] / $d['accept_count']) * 100) . "%"; //及时率/日 %
        $d['timely_turnover_ratio'] = sprintf("%.2f", ($d['timely_count'] / $d['deal_count']) * 100) . "%"; //及时成交占比 %
        $d['conversion_rate'] = sprintf("%.2f", ($d['deal_count'] / $d['accept_count']) * 100) . "%"; //转化率/日 %
        $d['average_price'] = sprintf("%.2f", ($d['amount'] / $d['deal_count'])); //均价/日
        echo_result(array('is_manager' => $is_manager, 'total_month_count' => $d, 'total_count' => $result_total_count, 'start_year' => START_YEAR, 'current_year' => date('Y'), 'list' => $models, 'page_no' => request_pageno(), 'max_page_no' => ceil($result_total_count / request_pagesize())));
    }
    if ($action == 11) {
        $startTime = request_string("start_time");
        $endTime = request_string("end_time");
        $channel = request_string("channel");
        $export = new ExportData2Excel();
        $sql = "SELECT s.id,s.channel,u.salecount_lv as lv,s.userid,s.username,SUM(s.into_count) AS into_count,SUM(s.accept_count) AS accept_count,SUM(s.deal_count) AS deal_count,SUM(s.timely_count) AS timely_count,SUM(s.amount) AS amount,DATE_FORMAT(s.addtime, '%Y-%m') AS addtime FROM P_SaleStatistics s ";
        $sql .= "INNER JOIN m_user u ON s.userid = u.userid WHERE 1=1 ";
        if (isset($startTime)) {
            $sql .= "AND DATE_FORMAT(s.addtime, '%Y-%m') >= '" . $startTime . "' ";
        }
        if (isset($endTime)) {
            $sql.="AND DATE_FORMAT(s.addtime, '%Y-%m') <= '" . $endTime . "' ";
        }
        if (isset($channel)) {
            $sql .="AND s.channel = '" . $channel . "' ";
        }
        $sql.="GROUP BY s.userid ORDER BY s.channel DESC";
        $db = create_pdo();
        $result = Model::execute_custom_sql($db, $sql);
        if (!$result[0]) {
            $export->create(array('导出错误'), array(array('售前统计(月)数据导出失败,请稍后重试!')), "售前统计(月)数据导出", "售前统计(月)");
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
        $title_array = array('姓名', '渠道', '转入数/月', '接入数/月', '成交数/月', '及时数/月', '金额/月', '提成/月', '流失数/月', '流失率/月', '及时率/月', '及时成交占比', '转化率/月', '均价/月', '日期');
        $field = array('username', 'channel', 'into_count', 'accept_count', 'deal_count', 'timely_count', 'amount', 'commission', 'loss_number', 'loss_rate', 'timely_rate', 'timely_turnover_ratio', 'conversion_rate', 'average_price', 'addtime');
        $export->set_field($field);
        $export->set_field_width(array(12, 15, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 17));
        $export->create($title_array, $models, "售前统计(月)数据导出", "售前统计(月)");
    }
});
