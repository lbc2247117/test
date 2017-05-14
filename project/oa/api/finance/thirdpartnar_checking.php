<?php

/**
 * 财务对账
 *
 * @author bocheng
 * @copyright 2015 非时序
 * @version 2016/2/22
 */
use Models\Base\Model;
use Models\P_GenerationOperation;
use Models\st_seller_front_agent;
use Models\Base\SqlOperator;
use Models\Base\SqlSortType;
use Models\receiver_payaccount;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../common/http.php';
require '../../Helper/CommonDal.php';
require '../../Models/postMsgClass.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {

    $login_userid = request_login_userid();
    $login_username = request_login_username();
    $curTime = request_string("curTime");
    $inner = request_string('inner');
    $review = request_string('review');
    $searchTime = request_string('searchTime');
    $workName = request_string('workName');
    $payType = request_string('payType');
    $account = request_string('account');
    $db = create_pdo();

    if ($action == 1) {
        $sql_pre = "SELECT b.add_time as maintime,a.id, a.transfer_time as add_time,b.qq,b.sales_numbers,b.customer_type,b.customer,a.platform_sales,a.pay_money as payment_amount,a.payment_method,a.pay_account_type as rcept_account,a.pay_username as pay_user,a.approve_status as st_is_approve from receiver_payaccount as a left JOIN p_generationoperation as b ON a.general_operal_id=b.id where 1=1";
        $sql_desc = ' ORDER BY a.approve_status DESC,a.transfer_time ASC';

        if (isset($inner)) {
            if (!isset($curTime) || $curTime == "1")
                $curTime = date('Y-m-d');
            if ($inner == 1) {
                $curTime = date('Y-m-d', strtotime("$curTime -1 day"));
            } else if ($inner == 2) {
                $curTime = date('Y-m-d', strtotime("$curTime +1 day"));
            }
        }
        if (isset($review)) {
            $sql_pre.=" and a.approve_status=$review";
        }
        if (!isset($curTime))
            $curTime = "1";
        if (isset($searchTime))
            $curTime = $searchTime;
        if ($curTime != "1") {
            $sql_pre.=" and  DATE_FORMAT(b.add_time, '%Y-%m-%d')='$curTime'";
        }
        if (isset($payType)) {
            if ($payType != '全部')
                $sql_pre.=" and  a.payment_method='$payType'";
        }
        if (isset($account)) {
            if ($account != '全部')
                $sql_pre.=" and  a.pay_account_type='$account'";
        }
        if (isset($workName)) {
            $sql_pre.=" AND (a.pay_money LIKE '%" . $workName . "%' OR b.qq LIKE '%" . $workName . "%' OR b.platform_num LIKE '%" . $workName . "%' OR b.platform_sales LIKE '%" . $workName . "%' OR b.customer LIKE '%" . $workName . "%' OR b.headmaster LIKE '%" . $workName . "%' OR a.pay_account_type LIKE '%" . $workName . "%' OR  a.pay_username LIKE '%" . $workName . "%') ";
        }
        $sql_pre.=$sql_desc;
        $result = $db->query($sql_pre);
        $total = $result->rowCount();
        $sql_pre.=' limit ' . request_pagesize() * (request_pageno() - 1) . ',' . request_pagesize();


        $result = $db->query($sql_pre);
        $models = $result->fetchAll(PDO::FETCH_ASSOC);
        $totalmoney = 0;
        foreach ($models as $row) {
            $totalmoney+=$row['payment_amount'];
        }
        $page_no = request_pageno();

        $max_page_no = ceil($total / request_pagesize());
        $result = array('curTime' => $curTime, 'totalmoney' => $totalmoney, 'total_count' => $total, "list" => $models, 'page_no' => $page_no, 'max_page_no' => $max_page_no, 'code' => 0);
        exit(get_response($result));
    }
    //导出excel
    if ($action == 10) {
        $startTime = request_string("start_time");
        $endTime = request_string("end_time");
        $expolt = new ExportData2Excel();

        $sql_pre = "SELECT  b.add_time as maintime,a.transfer_time as add_time,b.qq,b.sales_numbers,a.pay_money as payment_amount,a.platform_sales,a.payment_method,b.customer_type,b.customer,a.approve_status,a.pay_account_type as rcept_account,a.pay_username as pay_user from receiver_payaccount as a left JOIN p_generationoperation as b ON a.general_operal_id=b.id where 1=1";
        if (isset($startTime)) {
            $sql_pre.=" and DATE_FORMAT(b.add_time, '%Y-%m-%d') >=DATE_FORMAT('$startTime', '%Y-%m-%d')";
        }
        if (isset($endTime)) {
            $sql_pre.=" and DATE_FORMAT(b.add_time, '%Y-%m-%d') <=DATE_FORMAT('$endTime', '%Y-%m-%d')";
        }
        $sql_pre.=' and payType in (0,2) order by a.transfer_time asc';
        $db = create_pdo();
        $result = $db->query($sql_pre);
        $models = $result->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($models); $i++) {
            if ($models[$i]['approve_status'] == 0)
                $models[$i]['approve_status'] = '已审核';
            if ($models[$i]['approve_status'] == 1)
                $models[$i]['approve_status'] = '未审核';
            if ($models[$i]['approve_status'] == 2)
                $models[$i]['approve_status'] = '已驳回';
        }

        $title_array = array('公布日期','到账日期', 'qq', '套餐类型', '金额', '销售人员', '支付方式', '客户来源', '售后', '是否审核', '收款账号', '付款人');
        $expolt->create($title_array, $models, "自助对账数据导出", "自助对账");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $generationOperationData = request_object();
    $id_arr = $generationOperationData->id;
    $db = create_pdo();
    if ($action == 6) { //取消驳回
        $re = new receiver_payaccount($id_arr);
        $result = $re->load($db, $re);
        if (!$result[0])
            die_error(USER_ERROR, '获取数据失败~');
        $gen_id = $re->get_general_operal_id();
        $sql_gen = 'select count(*) from receiver_payaccount where general_operal_id=' . $gen_id . ' and approve_status=2';
        $result = $db->query($sql_gen);
        $result = $result->fetch(PDO::FETCH_ASSOC);
        $count = $result[0]['count(*)'];
        $re->reset();
        $re->set_id($id_arr);
        $re->set_approve_status(1);
        pdo_transaction($db, function($db)use($db, $gen_id, $re, $count) {
            if ($count == 0) {
                $gen = new P_GenerationOperation($gen_id);
                $gen->set_st_is_approve(1);
                $result = $gen->update($db);
                if (!$result[0])
                    die_error(USER_ERROR, '保存数据失败~');
            }
            $result = $re->update($db);
            if (!$result[0])
                die_error(USER_ERROR, '保存数据失败~~');
        });
        echo_msg('操作成功');
    }
    $username = request_login_username();
    $gen = new P_GenerationOperation();
    $searchDate = $generationOperationData->add_time;
    //财务审核
    if ($action == 1) {
        for ($i = 0; $i < count($id_arr); $i++) {
            $id = $id_arr[$i];
            $re = new receiver_payaccount($id);
            $result = $re->load($db, $re);
            if (!$result[0])
                die_error(USER_ERROR, '获取数据失败,请刷新页面重新尝试~');
            if ($re->get_approve_status() == 0)
                die_error(USER_ERROR, '该条数据已经审核，请刷新页面查看');
            $gen_id = $re->get_general_operal_id();
            $gen = new P_GenerationOperation($gen_id);
            $result = $gen->load($db, $gen);
            if (!$result[0])
                die_error(USER_ERROR, '获取主表信息失败~');
            $add_time = $re->get_transfer_time();
            $add_time = $add_time->format('Y-m-d');
            $st = new st_seller_front_agent();
            $st->set_addtime($add_time);
            $st->set_compare_time('now');
            $st->set_compare_user($username);
            $st->set_qq($gen->get_qq());
            $st->set_seller_front($gen->get_platform_sales());
            $st->set_submit_money($re->get_pay_money());
            $st->set_transfer_time($add_time);
            $st->set_transfer_money($re->get_pay_money());
            $st->set_remark('财务已审核');
            $st->set_alipay_account($re->get_pay_username());
            $st->set_rcept_account($re->get_pay_account_type());
            $st->set_gen_id($id);
            $st->set_customer_type($gen->get_customer_type());
            $st->set_customer($gen->get_customer());
            $st->set_customer_id($gen->get_customer_id());
            $re->reset();
            $re->set_id($id);
            $re->set_approve_status(0);
            pdo_transaction($db, function($db)use($db, $st, $re, $gen_id) {
                $result = $st->insert($db);
                if (!$result[0])
                    die_error(USER_ERROR, '审核数据失败');
                $result = $re->update($db);
                if (!$result[0])
                    die_error(USER_ERROR, '审核数据失败~');
                $sql_gen_id = 'select count(*) from receiver_payaccount where general_operal_id=' . $gen_id . ' and approve_status>0';
                $result = $db->query($sql_gen_id);
                $result = $result->fetchAll(PDO::FETCH_ASSOC);
                if ($result[0]['count(*)'] == 0) {
                    $gen = new P_GenerationOperation($gen_id);
                    $gen->set_st_is_approve(2);
                    $result = $gen->update($db);
                    if (!$result[0])
                        die_error(USER_ERROR, '审核数据失败~~');
                }
            });
        }
        $gen = new P_GenerationOperation();
        $gen->set_custom_where(' AND (st_is_approve=1 or st_is_approve=3)');
        $gen->set_custom_where(' AND (payType = 0 or payType = 2)');
        $gen->set_custom_where(" AND  DATE_FORMAT(add_time, '%Y-%m-%d')= DATE_FORMAT('$searchDate', '%Y-%m-%d')");
        $result = Model::query_list($db, $gen, NULL, true);
        if (!$result[0])
            echo_msg('审核成功~');
        $models = Model::list_to_array($result['models']);
        if (count($models) > 0)
            echo_msg('审核成功~');
        //需要弹窗
        $post = new postMsgClass();
        $post->msgtype = msgType::caiwu_result;
        $post->createtime = $searchDate;
        $post->backtime = date('Y-m-d');
        $post->username = request_username();
        $post->userid = Depart::D7;
        $post->receiveruser = '王杰';
        $msg = json_encode($post);
        $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
        if ($result == 'True')
            echo_msg('审核成功~');
        else
            die_error(USER_ERROR, "审核成功，发送审核通知失败~");
    }
    //反审核
    if ($action == 2) {
        $re = new receiver_payaccount($id_arr[0]);
        $result = $re->load($db, $re);
        $status = $re->get_approve_status();
        $gen_id = $re->get_general_operal_id();
        if ($status == 1)
            die_error(USER_ERROR, '该数据已经处于未审核状态，请不要重复反审');
        $re->reset();
        $re->set_id($id_arr[0]);
        $re->set_approve_status(1);

        $gen = new P_GenerationOperation($gen_id);
        $result = $gen->load($db, $gen);
        if (!$result[0])
            die_error(USER_ERROR, '获取主表信息失败~');
        $main_status = $gen->get_st_is_approve();
        $gen->reset();
        $gen->set_id($gen_id);
        $sql_del = 'delete from st_seller_front_agent where gen_id=' . $id_arr[0];
        pdo_transaction($db, function($db)use($db, $gen, $main_status, $re, $sql_del) {
            if ($main_status == 2) {
                $gen->set_st_is_approve(1);
                $result = $gen->update($db);
                if (!$result[0])
                    die_error(USER_ERROR, '反审核失败~');
            }
            $result = $re->update($db);
            if (!$result[0])
                die_error(USER_ERROR, '反审核失败~~');
            $result = $db->exec($sql_del);
            if ($result === FALSE)
                die_error(USER_ERROR, '反审核失败！~');
        });
        echo_msg('反审成功~');
    }

    //驳回
    if ($action == 3) {
        $remark = $generationOperationData->content;
        $re = new receiver_payaccount($id_arr[0]);
        $result = $re->load($db, $re);
        if (!$result[0])
            die_error(USER_ERROR, '获取数据失败~');
        $gen_id = $re->get_general_operal_id();
        $gen = new P_GenerationOperation($gen_id);
        $result = $gen->load($db, $gen);
        if (!$result[0])
            die_error(USER_ERROR, '获取数据失败!');
        $rmk = $gen->get_remark();
        $gen->reset();
        $gen->set_id($gen_id);
        if (!empty($rmk))
            $remark = $rmk . '|' . $remark;
        $gen->set_remark($remark);
        $gen->set_st_is_approve(3);
        $re->reset();
        $re->set_approve_status(2);
        pdo_transaction($db, function($db)use($db, $gen, $re) {
            $result = $re->update($db);
            if (!$result[0])
                die_error(USER_ERROR, '驳回失败~');
            $result = $gen->update($db);
            if (!$result[0])
                die_error(USER_ERROR, '驳回失败~~');
        });
        echo_msg('驳回成功');
    }
});

