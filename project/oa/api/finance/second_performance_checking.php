<?php

/**
 * 二销财务对账
 *
 * @author bocheng
 * @copyright 2015 非时序
 * @version 2016/2/22
 */
use Models\Base\Model;
use Models\P_GenerationOperation;
use Models\P_Fills_second;
use Models\st_fills_second;
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
        $sql_pre = "select id,qq,type,headmaster,add_time,qq,rception_money,play_price,fill_sum,customer,customer_id,platform_rception,platform_rception_id,customer_type,payment_method,rcept_account,pay_user,st_is_approve,transfer_time,second_type from p_fills_second where st_is_approve>0 and second_type!='代运营补欠款'";
        $sql_desc = " ORDER BY st_is_approve=1 DESC,DATE_FORMAT(transfer_time,'%Y-%m-%d') ASC";

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
            $sql_pre.=" and st_is_approve=$review";
        }
        if (!isset($curTime))
            $curTime = "1";
        if (isset($searchTime))
            $curTime = $searchTime;
        if ($curTime != "1") {
            $sql_pre.=" and  DATE_FORMAT(transfer_time, '%Y-%m-%d')='$curTime'";
        }
        if (isset($payType)) {
            if ($payType != '全部')
                $sql_pre.=" and  payment_method='$payType'";
        }
        if (isset($account)) {
            $sql_pre.=" and  rcept_account='$account'";
        }
        if (isset($workName)) {
            $sql_pre.=" AND (qq LIKE '%" . $workName . "%' OR platform_num LIKE '%" . $workName . "%' OR customer LIKE '%" . $workName . "%' OR headmaster LIKE '%" . $workName . "%' OR rcept_account LIKE '%" . $workName . "%' OR pay_user LIKE '%" . $workName . "%') ";
        }
        $sql_pre.=$sql_desc;
        $result = $db->query($sql_pre);
        $total = $result->rowCount();
        $sql_pre.=' limit ' . request_pagesize() * (request_pageno() - 1) . ',' . request_pagesize();


        $result = $db->query($sql_pre);
        $models = $result->fetchAll(PDO::FETCH_ASSOC);
        $totalmoney = 0;
        foreach ($models as $row) {
            $totalmoney+=$row['play_price'];
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

        $sql_pre = "SELECT add_time as time,qq as q,second_type as stp,play_price as money,platform_rception as plat,headmaster as hm,payment_method as pmt,customer_type as ct,st_is_approve as st,rcept_account as rat,pay_user as pu  from p_fills_second where second_type!='代运营补欠款' and st_is_approve>0";
        if (isset($startTime)) {
            $sql_pre.=" and DATE_FORMAT(add_time, '%Y-%m-%d') >=DATE_FORMAT('$startTime', '%Y-%m-%d')";
        }
        if (isset($endTime)) {
            $sql_pre.=" and DATE_FORMAT(add_time, '%Y-%m-%d') <=DATE_FORMAT('$endTime', '%Y-%m-%d')";
        }
        $sql_pre.='order by add_time asc';
        $db = create_pdo();
        $result = $db->query($sql_pre);
        $models = $result->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($models); $i++) {
            if ($models[$i]['st'] == 1)
                $models[$i]['st'] = '待审核';
            if ($models[$i]['st'] == 2)
                $models[$i]['st'] = '已审核';
            if ($models[$i]['st'] == 3)
                $models[$i]['st'] = '已驳回';
        }

        $title_array = array('添加时间', 'qq', '二销项目 ', '金额', '平台接待', '班主任', '支付方式', '客户来源', '是否审核', '收款账号', '付款人');
        $expolt->create($title_array, $models, "自助对账数据导出", "自助对账");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $generationOperationData = request_object();
    $id_arr = $generationOperationData->id;
    $db = create_pdo();
    if ($action == 6) { //取消驳回
        $re = new P_Fills_second($id_arr);
        $result = $re->load($db, $re);
        if (!$result[0])
            die_error(USER_ERROR, '获取数据失败~');
        $re->reset();
        $re->set_id($id_arr);
        $re->set_st_is_approve(1);
        $result = $re->update($db);
        if (!$result[0])
            die_error(USER_ERROR, '取消驳回失败~');
        echo_msg('取消驳回成功');
    }
    $username = request_login_username();
    //财务审核
    if ($action == 1) {
        for ($i = 0; $i < count($id_arr); $i++) {
            $id = $id_arr[$i];
            $re = new P_Fills_second($id);
            $result = $re->load($db, $re);
            if (!$result[0])
                die_error(USER_ERROR, '获取数据失败,请刷新页面重新尝试~');
            if ($re->get_st_is_approve() == 0)
                die_error(USER_ERROR, '该条数据已经审核，请刷新页面查看');
            $st = new st_fills_second();
            $st->set_addtime($generationOperationData->add_time);
            $st->set_qq($re->get_qq());
            $st->set_se_id($id);
            $st->set_headmaster($re->get_headmaster());
            $st->set_customer($re->get_customer());
            $st->set_customer_type($re->get_customer_type());
            $st->set_platform_rception($re->get_platform_rception());
            $st->set_money($re->get_play_price());
            $st->set_rcept_account($re->get_rcept_account());
            $st->set_pay_user($re->get_pay_user());
            $st->set_compare_user($username);
            $st->set_remark($re->get_remark());
            pdo_transaction($db, function($db)use($db, $st, $re, $id) {
                $result = $st->insert($db);
                if (!$result[0])
                    die_error(USER_ERROR, '审核数据失败');
                $re->set_st_is_approve(2);
                $re->set_id($id);
                $result = $re->update($db);
                if (!$result[0])
                    die_error(USER_ERROR, '审核数据失败~');
            });
        }
        $gen = new P_Fills_second();
        $gen->set_custom_where(' AND (st_is_approve=1 or st_is_approve=3)');
        $gen->set_custom_where("and second_type!='代运营补欠款'");
        $gen->set_custom_where(" AND  DATE_FORMAT(add_time, '%Y-%m-%d')= DATE_FORMAT('$generationOperationData->add_time', '%Y-%m-%d')");
        $result = Model::query_list($db, $gen, NULL, true);
        if (!$result[0])
            echo_msg('审核成功~');
        $models = Model::list_to_array($result['models']);
        if (count($models) > 0)
            echo_msg('审核成功~');
        //需要弹窗
        $post = new postMsgClass();
        $post->msgtype = msgType::caiwu_result;
        $post->createtime = $generationOperationData->add_time;
        $post->backtime = date('Y-m-d');
        $post->username = request_username();
        $post->userid = Depart::D6;
        $post->receiveruser = '曾祥一';
        $msg = json_encode($post);
        $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
        if ($result == 'True')
            echo_msg('审核成功~');
        else
            die_error(USER_ERROR, "审核成功，发送审核通知失败~");
    }
    //反审核
    if ($action == 2) {
        $re = new P_Fills_second($id_arr[0]);
        $result = $re->load($db, $re);
        $status = $re->get_st_is_approve();
        if ($status == 1)
            die_error(USER_ERROR, '该数据已经处于未审核状态，请不要重复反审');
        $re->reset();
        $re->set_id($id_arr[0]);
        $re->set_st_is_approve(1);
        $result = $re->update($db);
        if (!$result[0])
            die_error(USER_ERROR, '反审失败~~');
        echo_msg('反审成功~');
    }

    //驳回
    if ($action == 3) {
        $remark = $generationOperationData->content;
        $gen = new P_Fills_second($id_arr[0]);
        $result = $gen->load($db, $gen);
        if (!$result[0])
            die_error(USER_ERROR, '获取数据失败!');
        $rmk = $gen->get_remark();
        $gen->reset();
        $gen->set_id($id_arr[0]);
        if (!empty($rmk))
            $remark = $rmk . '|' . $remark;
        $gen->set_remark($remark);
        $gen->set_st_is_approve(3);
        pdo_transaction($db, function($db)use($db, $gen) {
            $result = $gen->update($db);
            if (!$result[0])
                die_error(USER_ERROR, '驳回失败~~');
        });
        echo_msg('驳回成功');
    }
});

