<?php

/*
 * 代运营认领
 *
 * @author bocheng
 * @copyright 2015 非时序
 * @version 2015/12/23
 */

use Models\Base\Model;
use Models\P_GenerationOperation;
use Models\Base\SqlOperator;
use Models\wait_msg;
use Models\P_Customerrecord_second;
use Models\receiver_payaccount;
use Models\M_Dept;

//use Common\PHPExcel\PHPExcel;
require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../common/http.php';
require '../../api/excel/PHPExcel/IOFactory.php';
require '../../Helper/CommonDal.php';
require '../../Models/postMsgClass.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    if ($action == 1) {
        $gen = new P_GenerationOperation();
        $gen->set_custom_where(' and status>0');
        $gen->set_query_fields(array('id', 'add_time', 'payment_amount', 'payment_method', 'pay_user', 'rcept_account', 'status', 'tradeNo'));
        $gen->set_limit_paged(request_pageno(), request_pagesize());
        $gen->set_custom_where(' order by add_time desc,status asc');
        $db = create_pdo();
        $result = Model::query_list($db, $gen, NULL, true);
        if (!$result[0])
            die_error(USER_ERROR, '获取统计资料失败，请重试');
        $models = Model::list_to_array($result['models']);
        $totalmoney = 0;
        foreach ($models as $row) {
            $totalmoney+=$row['payment_amount'];
        }
        $page_no = request_pageno();
        $total = $result['total_count'];
        $max_page_no = ceil($total / request_pagesize());
        $result = array('totalmoney' => $totalmoney, 'total_count' => $total, "list" => $models, 'page_no' => $page_no, 'max_page_no' => $max_page_no, 'code' => 0);
        exit(get_response($result));
    }
});


execute_request(HttpRequestMethod::Post, function() use($action) {
    $requestData = request_object();
    //添加
    if ($action == 1) {
        $db = create_pdo();
        $generationOperation = new P_GenerationOperation();
        $generationOperation->set_add_time($requestData->add_time);
        $generationOperation->set_payment_amount($requestData->payment_amount);
        $generationOperation->set_pay_user($requestData->pay_user);
        $generationOperation->set_rcept_account($requestData->rcept_account_id);
        $generationOperation->set_payment_method($requestData->payment_method);
        $generationOperation->set_tradeNo($requestData->tradeNo);
        $generationOperation->set_rcept_account($requestData->rcept_account);
        $generationOperation->set_status(1);
        $result = $generationOperation->insert($db);
        if (!$result[0])
            die_error(USER_ERROR, "添加代运营认领失败~");
        echo_msg("添加代运营认领成功");
    }
    //认领
    if ($action == 2) {
        $db = create_pdo();
        $generationOperation = new P_GenerationOperation($requestData->id); //待认领的数据
        $result = $generationOperation->load($db, $generationOperation);
        if (!$result[0])
            die_error(USER_ERROR, '获取数据失败，请稍后尝试~');
        $rcept_account = $generationOperation->get_rcept_account();
        $payment_amount = $generationOperation->get_payment_amount();
        $add_time = $generationOperation->get_add_time();
        $add_time = $add_time->format('Y-m-d H:i:s');
        $pay_user = $generationOperation->get_pay_user();
        $add_user = request_username();
        $sqlCustomer = "select pay_rate from p_customerrecord_second where username='$rcept_account'";
        $result = $db->query($sqlCustomer);
        $result = $result->fetchAll(PDO::FETCH_ASSOC);
        $pay_rate = $result[0]['pay_rate']; //税率
        $qq = $requestData->qq;
        //判断这个客户QQ是否有历史数据
        $sqlgen = "select rception_money,final_money,id from p_generationoperation where payType=0 and status=0 and qq='$qq' and rception_money>0";
        $result = $db->query($sqlgen);
        $result = $result->fetchAll(pdo::FETCH_ASSOC);
        $rception_money = 0; //数据库中的
        $gen_id = 0;
        $gen_final_money = 0;

        if (count($result) > 0) {
            $rception_money = $result[0]['rception_money'];
            $gen_id = $result[0]['id'];
            $gen_final_money = $result[0]['final_money'];
        }
        $generationOperation_id = $requestData->id;
        $rcept_money_post = $requestData->rception_money;
        $generationOperation->reset();
        $generationOperation->set_field_from_array($requestData);
        $generationOperation->set_id($requestData->id);
        $generationOperation->set_status(0);
        $generationOperation->set_st_is_approve(1);
        if ($requestData->customer_type == 1) {
            $generationOperation->set_customer_type('优程');
        } else {
            $generationOperation->set_customer_type('领远');
        }
        $re = new receiver_payaccount();
        $re->set_general_operal_id($generationOperation_id);
        $re->set_add_time('now');
        $re->set_transfer_time($add_time);
        $re->set_add_user($add_user);
        $re->set_pay_money($payment_amount);
        $re->set_pay_rate($pay_rate);
        $re->set_pay_username($pay_user);
        $re->set_pay_account_type($rcept_account);
        $re->set_platform_sales($requestData->platform_sales);
        $re->set_platform_sales_id($requestData->platform_sales_id);
        if ($rception_money == 0) {//表示没有新数据
            //1.保存代运营数据
            //2.推送业绩排行弹窗
            //3.添加消息处理中心数据
            //4.给售后弹出弹窗
            //5.保存数据到从表
            $payment_amount_pre = $payment_amount;
            if ($pay_rate > 0) {
                $payment_amount_pre = $payment_amount / (1 - $pay_rate);
            }
            $generationOperation->set_final_money($rcept_money_post - $payment_amount_pre);
            $wait = new wait_msg();
            $wait->set_add_time('now');
            $wait->set_request_id(request_login_userid());
            $wait->set_request_name(request_login_username());
            $wait->set_dept_id(get_dept_id());
            $wait->set_msgtype(1);
            $wait->set_status(1);
            $wait->set_task_id($generationOperation_id);
            pdo_transaction($db, function($db)use($db, $re, $wait, $generationOperation) {
                $result = $generationOperation->update($db);
                if (!$result[0])
                    die_error(USER_ERROR, '保存数据失败');
                $result = $wait->insert($db);
                if (!$result[0])
                    die_error(USER_ERROR, '保存数据失败~');
                $result = $re->insert($db);
                if (!$result[0])
                    die_error(USER_ERROR, '保存数据失败~~');
            });
            //推送消息
            $dept = new M_Dept(get_dept_id());
            $result = $dept->load($db, $dept);
            if (!$result[0])
                die_error(USER_ERROR, '系统错误,请稍后重试~');
            $dept_name = $dept->get_text();
            $postmsg = new postMsgClass();
            $postmsg->username = request_username();
            $postmsg->userid = request_userid();
            $postmsg->department = $dept_name;
            $postmsg->msgtype = msgType::sendbacksale;
            $msg = json_encode($postmsg);
            $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
            if ($result == 'True')
                echo_msg('认领业绩成功~');
            else
                die_error(USER_ERROR, "认领业绩成功，发送广播失败~");
        } else {//表示有数据
            //1.更新之前数据的欠款金额
            //2.把该条数据的欠款金额和接待金额清零
            //3.把数据保存在从表中
            $generationOperation->set_rception_money(0);
            $generationOperation->set_final_money(0);


            if ($pay_rate > 0) {
                $payment_amount = $payment_amount / (1 - $pay_rate);
            }
            $gen = new P_GenerationOperation($gen_id);
            $gen->set_final_money($gen_final_money - $payment_amount);

            pdo_transaction($db, function($db)use($db, $generationOperation, $gen, $re) {
                $result = $generationOperation->update($db);
                if (!$result[0])
                    die_error(USER_ERROR, '保存数据失败');
                $result = $gen->update($db);
                if (!$result[0])
                    die_error(USER_ERROR, '保存数据失败~');
                $result = $re->insert($db);
                if (!$result[0])
                    die_error(USER_ERROR, '保存数据失败~~');
            });
            echo_msg('认领业绩成功');
        }
    }
    //充公
    if ($action == 3) {
        $generationOperation = new P_GenerationOperation($requestData->id);
        $generationOperation->set_id($requestData->id);
        $generationOperation->set_status(2);
        $db = create_pdo();
        $result = $generationOperation->update($db, true);
        if (!$result[0])
            die_error(USER_ERROR, '冻结代运营失败');
        echo_msg('冻结代运营成功~');
    }
    //修改
    if ($action == 4) {
        $db = create_pdo();
        $generationOperation = new P_GenerationOperation($requestData->id);
        $generationOperation->set_field_from_array($requestData);
        $generationOperation->set_id($requestData->id);
        $result = $generationOperation->update($db, true);
        if (!$result[0])
            die_error(USER_ERROR, '修改失败');
        echo_msg('修改成功');
    }
    //删除
    if ($action == 5) {
        $generationOperation = new P_GenerationOperation($requestData->id);
        $db = create_pdo();
        $result = $generationOperation->delete($db);
        if (!$result[0])
            die_error(USER_ERROR, '删除流量业绩信息失败');
        echo_msg('删除流量业绩信息成功~');
    }
    //批量删除
    if ($action == 10) {
        $id = $_POST['id'];
        $sql = "delete from p_fills_second where id in($id)";
        $db = create_pdo();
        $result = $db->exec($sql);
        if (!$result)
            die_error(USER_ERROR, '删除数据失败，请稍后重新!');
        echo_msg('删除数据成功');
    }
});

/*
 * 根据传入的SQL语句
 * 获取数据集合
 */

function getResult($sql) {
    $db = create_pdo();
    $result = $db->query($sql);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}
