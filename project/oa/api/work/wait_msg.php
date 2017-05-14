<?php

/**
 * 消息处理中心
 *
 * @author bocheng
 * @copyright 2016 非时序
 * @version 2016-01-26
 */
use Models\Base\Model;
use Models\wait_msg;
use Models\Base\SqlOperator;
use Models\P_Customerrecord;
use Models\P_GenerationOperation;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../common/http.php';
require '../../Models/postMsgClass.php';
require '../../api/excel/PHPExcel/IOFactory.php';
require '../../Helper/CommonDal.php';


$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {

    $login_userid = request_login_userid();
    $header_role_ids = array(0, 101, 102, 103, 601, 602, 603, 803, 806, 808);
    $header_userids = array(7,227);
    $manager_role_ids = array(201, 401, 405, 501, 701, 703, 704, 901, 902, 903, 1001, 1003, 1105, 1201, 1203, 1301, 1303);
    $manager_userids = array(65);
    $is_header = in_array(get_role_id(), $header_role_ids) || in_array($login_userid, $header_userids);
    $is_manager = in_array(get_role_id(), $manager_role_ids) || in_array($login_userid, $manager_userids);
    $dept_id = get_dept_id();

    $sort = request_string('sort');
    $sortname = request_string('sortname');
    $workName = request_string("workName");
    $searchTime = request_string("searchTime");
    $searchStartTime = request_string("searchStartTime");
    $searchEndTime = request_string("searchEndTime");
    $isRecept = request_string('isRecept');
    $qq = request_string("qq");
    if ($action == 1) {
        $wait = new wait_msg();
        if (isset($workName)) {
            $wait->set_custom_where(" AND (request_name LIKE '%" . $workName . "%' OR response_name LIKE '%" . $workName . "%') ");
        }

        if (isset($searchTime)) {
            $wait->set_custom_where(" AND DATE_FORMAT(add_time,'%Y-%m-%d') = '" . $searchTime . "' ");
        }
        if (isset($searchStartTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchStartTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $wait->set_custom_where(" and DATE_FORMAT(add_time, '" . $formatStr . "') >= '" . $searchStartTime . "' ");
        }
        if (isset($searchEndTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchEndTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $wait->set_custom_where(" and DATE_FORMAT(add_time, '" . $formatStr . "') <= '" . $searchEndTime . "' ");
        }
        if (isset($isRecept)) {
            $wait->set_custom_where(" and status=$isRecept");
        }
        if (!$is_header) {
            if ($is_manager) {
                $wait->set_custom_where(" AND (dept_id=$dept_id)");
            } else {
                $wait->set_custom_where(" AND (request_id=$login_userid)");
            }
        }

        if (isset($sort) && isset($sortname)) {
            $wait->set_order_by($wait->get_field_by_name($sortname), $sort);
        } else {
            $wait->set_order_by(wait_msg::$field_add_time, 'DESC');
        }
        $wait->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $wait, NULL, true);
        if (!$result[0])
            die_error(USER_ERROR, '获取统计资料失败，请重试');
        $models = Model::list_to_array($result['models']);
        echo_list_result($result, $models);
    }
    if ($action == 10) { //导出excel
        $startTime = request_string("start_time");
        $endTime = request_string("end_time");
        $expolt = new ExportData2Excel();
        $sendflow = new p_sendflow();
        if (isset($startTime)) {
            $sendflow->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $sendflow->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') <= '" . $endTime . "' ");
        }
        $sendflow->set_query_fields(array('add_time', 'mobile', 'qq', 'username', 'paymoney', 'point', 'customer', 'rception_staff'));
        $db = create_pdo();
        $result = Model::query_list($db, $sendflow, NULL, true);
        if (!$result[0]) {
            $expolt->create(array('导出错误'), array(array('流量业绩数据导出失败,请稍后重试!')), "流量业绩数据导出", "流量业绩");
        }
        $models = Model::list_to_array($result['models'], array(), function() {
                    
                });
        $title_array = array('日期', '客户手机', 'QQ号', '用户名', '付款金额', '流量点', '售后名称', '平台接待人员');
        $expolt->create($title_array, $models, "流量业绩数据导出", "流量业绩");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $wait = request_object();
    //$rception = $flowData->rception_staff;
    //指派班主任
    if ($action == 1) {
        $gen = new P_GenerationOperation($wait->task_id);
        $db = create_pdo();
        $result = $gen->load($db, $gen);
        if (!$result[0])
            die_error(USER_ERROR, '系统错误,请稍后重试~');
        $qq = $gen->get_qq();
        $headmaster = $wait->headmaster;
        $headmaster_id = $wait->headmaster_id;
        $addtime= $wait->add_date;

        $userid = request_login_userid();
        $username = request_login_username();
        $wait_msg = new wait_msg($wait->id);
        $wait_msg->set_response_id($userid);
        $wait_msg->set_response_name($username);
        $wait_msg->set_status(2);
        pdo_transaction($db, function($db)use($db, $qq, $headmaster, $headmaster_id, $wait_msg) {
            $sql = "update p_generationoperation set headmaster='$headmaster',headmaster_id='$headmaster_id',allot_time= now() where qq='$qq' and payType=0";
            $result = $db->exec($sql);
            if ($result === FALSE)
                die_error(USER_ERROR, '指派班主任失败!');
            $result = $wait_msg->update($db);
            if (!$result[0])
                die_error(USER_ERROR, '指派班主任失败~');
        });
        $username = $wait->platform_sales;
        $postmsg = new postMsgClass();
        $postmsg->sellerorder = $wait->payment_amount;
      //  $postmsg->userid = $wait->platform_sales_id;
        $postmsg->username = $username;
        $strSql = "select sum(payment_amount) from P_GenerationOperation where platform_sales='$username' and payType=0 and  DATE_FORMAT(add_time,'%Y-%m')= DATE_FORMAT(SYSDATE(),'%Y-%m')";
        $result = $db->query($strSql);
        $allorderArr = $result->fetch(PDO::FETCH_NUM);
        $allorder = $allorderArr[0];

        $strSql = "select sum(payment_amount) from P_GenerationOperation where DATE(add_time)='$addtime' and payType=0";
        $result = $db->query($strSql);
        $dayorderArr = $result->fetch(PDO::FETCH_NUM);
        $dayorder = $dayorderArr[0];

        $strSql = "select platform_sales,sum(payment_amount) from P_GenerationOperation where DATE(add_time)='$addtime'  and payType=0 GROUP BY platform_sales order by sum(payment_amount) desc LIMIT 3";
        $result = $db->query($strSql);
        $arr = $result->fetchAll(PDO::FETCH_NUM);
        $count = count($arr);
        $postmsg->first = (int) $arr[0][1] . '|' . $arr[0][0];
        if ($count == 2) {
            $postmsg->second = (int) $arr[1][1] . '|' . $arr[1][0];
        } else if ($count == 3) {
            $postmsg->second = (int) $arr[1][1] . '|' . $arr[1][0];
            $postmsg->third = (int) $arr[2][1] . '|' . $arr[2][0];
        }
        $postmsg->allorder = $allorder;
        $postmsg->dayorder = $dayorder;
        $postmsg->msgtype = msgType::reward;
        $postmsg->rewardType = 1;
        $postmsg->createtime = $addtime; //日期
        $msg = json_encode($postmsg);
        $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
        if ($result != 'True')
            die_error(USER_ERROR, "指派班主任成功，发送广播失败~");

        //推送消息
        $postmsg = new postMsgClass();
        $postmsg->msgtype = msgType::callteacher; //指派班主任
        $postmsg->userid = $wait->headmaster_id; //id
        $postmsg->username = $wait->headmaster; //用户名兼班主任
        $postmsg->clientinfo = $wait->qq; //qq
        $postmsg->mobile = $wait->platform_num; //手机号
        $postmsg->createtime = $addtime; //日期
        $postmsg->money = $wait->rception_money; //接待金额
        $postmsg->final_money = $wait->payment_amount; //付款金额
        $postmsg->receiveruser = $wait->platform_sales; //平台销售
        $postmsg->payment_method = $wait->payment_method; //支付方式
        $postmsg->content = $wait->remark; //备注
        $msg = json_encode($postmsg);
        $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
        if ($result != 'True') {
            echo_msg("指派班主任成功,推送班主任消息失败~");
        }
        echo_msg('指派班主任成功~');
    }
    //删除
    if ($action == 2) {
        $flow = new p_flow($flowData->id);
        $db = create_pdo();
        $result = $flow->delete($db);
        if (!$result[0])
            die_error(USER_ERROR, '删除流量业绩信息失败');
        echo_msg('删除流量业绩信息成功~');
    }
    //修改信息
    if ($action == 3) {
        $flow = new p_flow($flowData->id);
        $db = create_pdo();
        $result = $flow->load($db, $flow);
        if (!$result[0])
            die_error(USER_ERROR, '系统错误,请稍后重试~');
        $rceptiondb = $flow->get_rception_staff();
        $flow->reset();
        $flow->set_field_from_array($flowData);
        if (!empty($rception) && $rception != $rceptiondb) {
            $flow->set_allot_time('now');
        }
        $flow->set_id($flowData->id);
        $result = $flow->update($db, true);
        if (!$result[0])
            die_error(USER_ERROR, '保存流量业绩失败');
        if (!empty($rception) && $rception != $rceptiondb) {
            //推送消息
            $postmsg = new postMsgClass();
            $postmsg->msgtype = msgType::receivercard;
            $postmsg->userid = $flowData->rception_staff_id;
            $postmsg->username = $flowData->rception_staff;
            $postmsg->clientinfo = $flowData->qq;
            $postmsg->money = $flowData->paymoney;
            $postmsg->point = $flowData->point;
            $postmsg->customer = $flowData->customer;
            $postmsg->mobile = $flowData->mobile;
            $postmsg->content = $flowData->remark;
            $postmsg->clientuser = $flowData->username;
            $postmsg->receiveruser = $flowData->rception_staff;
            $msg = json_encode($postmsg);
            $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
            if ($result != 'True') {
                echo_msg("修改流量业绩信息成功,推送消息失败~");
            }
        }
        echo_msg('保存成功');
    }
    if ($action == 5) { //获取代运营的信息
        $gid = $wait->task_id;
        $gen = new P_GenerationOperation();
        $gen->set_custom_where("AND (id='$gid')");
        $db = create_pdo();
        $result = Model::query_list($db, $gen, NULL, true);
        if (!$result[0])
            die_error(USER_ERROR, '获取代运营资料失败，请重试');
        $models = Model::list_to_array($result['models']);
        echo_list_result($result, $models);
    }
});
