<?php

/**
 * QQ接待名单
 *
 * @author QI
 * @copyright 2015 星密码
 * @version 2015/3/16
 */
use Models\Base\Model;
use Models\P_QQReception;
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

    $searchName = request_string('searchName');
    $sql = "SELECT pr.id,pr.`status`,pr.addtime,pr.presales,pr.presales_id,pr.toplimit,pa.finish,pr.starttime,pr.endtime ";
    $sql .= "FROM P_QQReception pr LEFT JOIN ( ";
    $sql .="SELECT COUNT(pa.presales_id) AS finish,pa.presales_id FROM P_QQAccess pa WHERE " . getWhereSql('pa') . " GROUP BY pa.presales_id ";
    $sql .=") AS pa ON pr.presales_id = pa.presales_id ";
    if (isset($searchName)) {
        $sql .= " AND pr.presales like '%" . $searchName . "%' ";
    }
    if (isset($sort) && isset($sortname)) {
        if (str_equals($sortname, 'finish')) {
            $sql .= "ORDER BY pa." . $sortname . " " . $sort;
        } else {
            $sql .= "ORDER BY pr." . $sortname . " " . $sort;
        }
    } else {
        $sql .= "ORDER BY pr.id ASC";
    }

    $qq_reception = new P_QQReception();
    $db = create_pdo();
    $result_total_count = $qq_reception->count($db);
    $qq_reception->reset();
    $sql .= " LIMIT " . request_pagesize() * (request_pageno() - 1) . "," . request_pagesize();
    $result = Model::query_list($db, $qq_reception, $sql);
    if (!$result[0]) die_error(USER_ERROR, '获取QQ接待名单失败，请重试');
    $models = Model::list_to_array($result['models']);
    echo_result(array('total_count' => $result_total_count, 'list' => $models, 'page_no' => request_pageno(), 'max_page_no' => ceil($result_total_count / request_pagesize())));
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $qq_receptionData = request_object();
    if ($action == 1) {
        $qq_reception = new P_QQReception();
        $qq_reception->set_field_from_array($qq_receptionData);
        $qq_reception->set_addtime('now');
        $db = create_pdo();
        $result = $qq_reception->insert($db);
        if (!$result[0]) die_error(USER_ERROR, '添加失败~');
        echo_msg('添加成功~');
    }
    //修改销售售后信息
    if ($action == 2) {
        $qq_reception = new P_QQReception();
        $qq_reception->set_field_from_array($qq_receptionData);
        $db = create_pdo();
        $result = $qq_reception->update($db, true);
        if (!$result[0]) die_error(USER_ERROR, '保存售后资料失败');
        echo_msg('保存成功');
    }
    if ($action == 5) {
        $qq_reception = new P_QQReception($qq_receptionDate->id);
        $qq_reception->set_field_from_array($qq_receptionData);
        $toplimit = $qq_receptionData->toplimit;
        $finish = $qq_receptionData->finish;
        if ($toplimit <= $finish) {
            die_error(USER_ERROR, '用户到达接单上限');
            exit;
        }
        $db = create_pdo();
        $result = $qq_reception->update($db, true);
        if (!$result[0]) die_error(USER_ERROR, '保存售后资料失败');
        echo_msg('保存成功');
    }
    //删除
    if ($action == 3) {
        $qq_reception = new P_QQReception();
        $qq_reception->set_field_from_array($qq_receptionData);
        $db = create_pdo();
        $result = $qq_reception->delete($db, true);
        if (!$result[0]) die_error(USER_ERROR, '删除失败');
        echo_msg('删除成功');
    }
    //启用
    if ($action == 4) {
        $db = create_pdo();
        $sql = "update P_QQReception SET status = 1";
        pdo_transaction($db, function($db) use($sql) {
            $result = Model::execute_custom_sql($db, $sql);
            if (!$result[0]) throw new TransactionException(PDO_ERROR_CODE, '启动失败~' . $result['detail_cn'], $result);
        });
        echo_msg('启用成功');
    }

    //启用/停用
    if ($action == 40) {
        $qq_reception = new P_QQReception($qq_receptionData->id);
        $qq_reception->set_status($qq_receptionData->status);
        $db = create_pdo();
        $result = $qq_reception->update($db, true);
        if (!$result[0]) die_error(USER_ERROR, '保存售后资料失败');
        echo_msg('保存成功');
    }

    //全部暂停
    if ($action == 41) {
        $db = create_pdo();
        $sql = "update P_QQReception SET status = 0";
        pdo_transaction($db, function($db) use($sql) {
            $result = Model::execute_custom_sql($db, $sql);
            if (!$result[0]) throw new TransactionException(PDO_ERROR_CODE, '暂停失败~' . $result['detail_cn'], $result);
        });
        echo_msg('启用成功');
    }
});

