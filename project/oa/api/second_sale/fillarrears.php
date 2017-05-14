<?php

/**
 * 代运营补款
 *
 * @author QI
 * @copyright 2015 星密码
 * @version 2015/3/16
 */
use Models\Base\Model;
use Models\P_Salecount;
use Models\Base\SqlOperator;
use Models\P_Customerrecord;
use Models\P_Fills_second;
use Models\P_GenerationOperation;

//use Common\PHPExcel\PHPExcel;

require '../../application.php';
require '../../loader-api.php';
require '../../common/http.php';
require '../../Models/postMsgClass.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    $db = create_pdo();
    if (!isset($action))
        $action = -1;
    $fillarrears = new P_Fills_second();
    $sort = request_string('sort');
    $sortname = request_string('sortname');
    $searchName = request_string('searchName');
    if (isset($searchName)) {
        $fillarrears->set_custom_where(" AND ( ww like '%" . $searchName . "%' OR name LIKE '%" . $searchName . "%' OR add_name like'%" . $searchName . "%' ) ");
    }
    if (isset($sort) && isset($sortname)) {
        $fillarrears->set_order_by($fillarrears->get_field_by_name($sortname), $sort);
    } else {
        $fillarrears->set_order_by(P_Salecount::$field_id, 'desc');
    }
    $fillarrears->set_limit_paged(request_pageno(), request_pagesize());
    $result = Model::query_list($db, $fillarrears, NULL, true);
    if (!$result[0])
        die_error(USER_ERROR, '获取补欠款资料失败，请重试');
    $models = Model::list_to_array($result['models']);

    $page_no = request_pageno();
    $total = $result['total_count'];
    $max_page_no = ceil($total / request_pagesize());

    $customer = new P_Customerrecord();
    $customer->set_status(1);
    $customer->set_query_fields(array('userid', 'username', 'nickname'));
    $customer_result = Model::query_list($db, $customer);
    $customer_list = Model::list_to_array($customer_result['models'], array(), function(&$d) {
                $d['id'] = $d['userid'];
                $d['text'] = $d['username'] . "(" . $d['nickname'] . ")";
                unset($d['userid']);
                unset($d['username']);
                unset($d['nickname']);
            });
    echo_list_result($result, $models, array('customer_list' => $customer_list, 'current_date' => date('Y-m-d')));
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $fillarrearsData = request_object();
    //添加补欠款
    if ($action == 1) {
        if (empty($fillarrearsData->headmaster))
            die_error(USER_ERROR, '班主任不能为空~');
        if (empty($fillarrearsData->pay_user))
            die_error(USER_ERROR, '付款人不能为空~');
        if (empty($fillarrearsData->rcept_account))
            die_error(USER_ERROR, '收款账号不能为空~');
        $fillarrears = new P_Fills_second();
        $fillarrears->set_field_from_array($fillarrearsData);
        $add_time = $fillarrearsData->add_time;
        $fillarrears->set_fill_sum('');
        $fillarrears->set_add_time($add_time);
        $fillarrears->set_second_type('代运营补欠款');
        $fillarrears->set_add_name(request_userid());
        $fillarrears->set_play_price($fillarrearsData->fill_sum*(1-$fillarrearsData->pay_rate));
        $fillarrears->set_st_is_approve(1);
        $db = create_pdo();
        $gen = new P_GenerationOperation();
        $gen->set_add_time($add_time);
        $gen->set_payType(1);
        $gen->set_customer_type($fillarrearsData->customer_type);
        $gen->set_customer($fillarrearsData->customer);
        $gen->set_customer_id($fillarrearsData->customer_id);
        $gen->set_platform_sales($fillarrearsData->platform_rception);
        $gen->set_platform_sales_id($fillarrearsData->platform_rception_id);
        $gen->set_payment_amount($fillarrearsData->fill_sum*(1-$fillarrearsData->pay_rate));
        $gen->set_headmaster($fillarrearsData->headmaster);
        $gen->set_headmaster_id($fillarrearsData->headmaster_id);
        $gen->set_qq($fillarrearsData->qq);
        $gen->set_platform_num($fillarrearsData->platform_num);
        $gen->set_remark($fillarrearsData->remark);
        $gen->set_rception_money($fillarrearsData->rception_money);
        $gen->set_st_is_approve(1);
        $gen->set_pay_user($fillarrearsData->pay_user);
        $gen->set_rcept_account($fillarrearsData->rcept_account);
        $gen->set_payment_method($fillarrearsData->payment_method);
        pdo_transaction($db, function($db)use($gen, $fillarrears) {
            $result_fillarrears = $fillarrears->insert($db);
            if (!$result_fillarrears[0])
                die_error(USER_ERROR, "添加补欠款失败");
            $result_gen = $gen->insert($db);
            if (!$result_gen[0])
                die_error(USER_ERROR, "添加补欠款失败");
        });
        $username = $fillarrearsData->headmaster;
        $ordermsg = new postMsgClass();
        $ordermsg->sellerorder = $fillarrearsData->fill_sum;
        $ordermsg->userid = $fillarrearsData->platform_rception_id; //售后所有人都弹，售前平台接待这个人要弹
        $ordermsg->username = $username;
        $strSql = "select sum(play_price) from P_Fills_second where headmaster='$username' and  DATE_FORMAT(add_time,'%Y-%m')= DATE_FORMAT(SYSDATE(),'%Y-%m')";
        $result = $db->query($strSql);
        $allorderArr = $result->fetch(PDO::FETCH_NUM);
        $allorder = $allorderArr[0];

        $strSql = "select sum(play_price) from P_Fills_second where   DATE_FORMAT(add_time,'%Y-%m-%d')= DATE_FORMAT('$add_time','%Y-%m-%d')";
        $result = $db->query($strSql);
        $dayorderArr = $result->fetch(PDO::FETCH_NUM);
        $dayorder = $dayorderArr[0];
        $strSql = "select headmaster,sum(play_price) from P_Fills_second where  DATE_FORMAT(add_time,'%Y-%m-%d')= DATE_FORMAT('$add_time','%Y-%m-%d') GROUP BY headmaster order by sum(play_price) desc LIMIT 3";
        $result = $db->query($strSql);
        $arr = $result->fetchAll(PDO::FETCH_NUM);
        $count = count($arr);
        $ordermsg->first = $arr[0][1] . '|' . $arr[0][0];
        if ($count == 2) {
            $ordermsg->second = $arr[1][1] . '|' . $arr[1][0];
        } else if ($count == 3) {
            $ordermsg->second = $arr[1][1] . '|' . $arr[1][0];
            $ordermsg->third = $arr[2][1] . '|' . $arr[2][0];
        }
        $ordermsg->allorder = $allorder;
        $ordermsg->dayorder = $dayorder;
        $ordermsg->msgtype = msgType::bumoney;
        $ordermsg->rewardType = 2;
        $ordermsg->createtime = date('Y-m-d');
        $omsg = json_encode($ordermsg);
        $result = curl_http_post(PUSH_MESSAGE_URL, $omsg);
        if ($result != 'True')
            die_error(USER_ERROR, "添加二销业绩成功，推送消息失败~");
        echo_msg("添加二销业绩信息成功~");
    }
    //修改销售统计信息
    if ($action == 2) {
        $fillarrears = new P_Fills_second();
        $fillarrears->set_field_from_array($fillarrearsData);

        $db = create_pdo();
        $result = $fillarrears->update($db, true);
        if (!$result[0])
            die_error(USER_ERROR, '保存统计资料失败');
        echo_msg('保存成功');
    }
    //删除
    if ($action == 3) {
        $fillarrears = new P_Fills_second();
        $fillarrears->set_field_from_array($fillarrearsData);
        $db = create_pdo();
        $result = $fillarrears->delete($db, true);
        if (!$result[0])
            die_error(USER_ERROR, '删除失败');
        echo_msg('删除成功');
    }
});

