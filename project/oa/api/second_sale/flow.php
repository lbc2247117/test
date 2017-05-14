<?php

/**
 * 流量业绩
 *
 * @author bocheng
 * @copyright 2016 非时序
 * @version 2016-01-12
 */
use Models\Base\Model;
use Models\p_flow;
use Models\Base\SqlOperator;
use Models\P_Customerrecord;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../common/http.php';
require '../../Models/postMsgClass.php';
require '../../api/excel/PHPExcel/IOFactory.php';
require '../../Helper/CommonDal.php';

ini_set('memory_limit', '-1');
set_time_limit(0);
$upload = $_GET['upload'];
//导入excel
if ($upload == 1) {
    $file = $_FILES['filename']['tmp_name'];
    $fullpath = BASE_PATH . "/upload/" . md5(uniqid('', TRUE)) . ".xls";
    move_uploaded_file($file, $fullpath);
    $objReader = PHPExcel_IOFactory::createReaderForFile($fullpath);

    $document = PHPExcel_IOFactory::load($fullpath);
    $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

    $arr_one = array();
    $save_one = array();
    foreach ($activeSheetData as $key => $value) {
        if ($key > 1) {
            if (!empty($value["A"])) {
                $arr_one["add_time"] = $value["A"];
                $arr_one["username"] = $value["C"];
                $arr_one["paymoney"] = $value["D"];
                $arr_one["customer"] = $value["E"];
                $arr_one["qq"] = $value["F"];
                $arr_one["rception_staff"] = $value["G"];
                $arr_one["tradeNo"] = $value["H"];
                $arr_one["paymoney"] = trim($arr_one["paymoney"]);
                if (!strtotime($arr_one["add_time"])) {
                    die_error(USER_ERROR, '第' . $key . '行时间格式不对，请输入例如2015-01-01的格式');
                }
                if (!is_numeric($arr_one["paymoney"])) {
                    die_error(USER_ERROR, '第' . $key . '行请填写正确的付款金额');
                }
                if ($arr_one["tradeNo"] == 'NULL') {
                    $arr_one["tradeNo"] = '';
                }
                array_push($save_one, $arr_one);
            } else {
                continue;
            }
        }
    }
    $newArray = array_values($save_one);

    if (CommonHelper::Insert("p_flow", $newArray, TRUE)) {
        echo_msg('导入流量业绩成功~');
    } else {
        die_error(USER_ERROR, '导入流量业绩失败');
    }
}

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {

    $login_userid = request_login_userid();
    $login_username = request_login_username();
    $manager_role_ids = array(0, 101, 102, 103, 401, 402, 403, 405, 601, 602, 603, 701, 703, 704, 803, 806, 808, 1105, 1301);
    $manager_userids = array(7, 71, 215);
    $is_manager = in_array(get_role_id(), $manager_role_ids) || in_array($login_userid, $manager_userids);

    $sort = request_string('sort');
    $sortname = request_string('sortname');
    $workName = request_string("workName");
    $isRecept = request_string("isRecept");
    $searchTime = request_string("searchTime");
    $searchStartTime = request_string("searchStartTime");
    $searchEndTime = request_string("searchEndTime");
    $qq = request_string("qq");
    if ($action == 1) {
        $flow = new p_flow();
        if (isset($workName)) {
            $flow->set_custom_where(" AND (customer LIKE '%" . $workName . "%' OR rception_staff LIKE '%" . $workName . "%' OR mobile LIKE '%" . $workName . "%' OR qq LIKE '%" . $workName . "%' OR paymoney LIKE '%" . $workName . "%' OR username LIKE '%" . $workName . "%') ");
        }
        if (isset($isRecept)) {
            if ($isRecept == 1) {
                $flow->set_custom_where('AND rception_staff is not null');
            } else if ($isRecept == 2) {
                $flow->set_custom_where('AND rception_staff is null');
            }
        }
        if (isset($searchTime)) {
            $flow->set_custom_where(" AND DATE_FORMAT(add_time,'%Y-%m-%d') = '" . $searchTime . "' ");
        }
        if (isset($searchStartTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchStartTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $flow->set_custom_where(" and DATE_FORMAT(add_time, '" . $formatStr . "') >= '" . $searchStartTime . "' ");
        }
        if (isset($searchEndTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchEndTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $flow->set_custom_where(" and DATE_FORMAT(add_time, '" . $formatStr . "') <= '" . $searchEndTime . "' ");
        }
        if (!$is_manager) {
            $flow->set_custom_where(" and (rception_staff='$login_username')");
        }
        if (isset($sort) && isset($sortname)) {
            $flow->set_order_by($flow->get_field_by_name($sortname), $sort);
        } else {
            $flow->set_order_by(p_flow::$field_add_time, 'DESC');
        }
        $flow->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $flow, NULL, true);
        if (!$result[0])
            die_error(USER_ERROR, '获取统计资料失败，请重试');
        $models = Model::list_to_array($result['models'], array(), function(&$d) use($is_manager, $login_userid) {
                    
                });
        $totalmoney = 0;
        foreach ($models as $row) {
            $totalmoney+=$row['paymoney'];
        }
        $page_no = request_pageno();
        $total = $result['total_count'];
        $max_page_no = ceil($total / request_pagesize());
        $result = array('totalmoney' => $totalmoney, 'total_count' => $total, "list" => $models, 'page_no' => $page_no, 'max_page_no' => $max_page_no, 'code' => 0);
        exit(get_response($result));
    }
    if ($action == 10) { //导出excel
        $startTime = request_string("start_time");
        $endTime = request_string("end_time");
        $expolt = new ExportData2Excel();
        $flow = new p_flow();
        if (isset($startTime)) {
            $flow->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $flow->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') <= '" . $endTime . "' ");
        }
        $flow->set_query_fields(array('add_time', 'mobile', 'qq', 'username', 'paymoney', 'tradeNo', 'customer', 'rception_staff'));
        $db = create_pdo();
        $result = Model::query_list($db, $flow, NULL, true);
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
    $flowData = request_object();
    $rception = $flowData->rception_staff;
    //添加
    if ($action == 1) {
        $add_time = $flowData->add_time;
        $add_date = $flowData->add_date;
        $add_time = $add_date . " $add_time";
        $flow = new p_flow();
        $flow->set_field_from_array($flowData);
        $remark = $flowData->remark;
        if (!empty($rception))
            $flow->set_allot_time($add_time);
        $flow->set_add_time($add_time);
        $db = create_pdo();
        $result = $flow->insert($db);
        if (!$result[0])
            die_error(USER_ERROR, "添加流量业绩信息失败~");
        if ($remark == '补款') {
            //推送消息
            $postmsg = new postMsgClass();
            $postmsg->msgtype = msgType::flowBukuan;
            $postmsg->userid = $flowData->rception_staff_id;
            $postmsg->username = $flowData->rception_staff;
            $postmsg->clientinfo = $flowData->qq; //qq
            $postmsg->money = $flowData->paymoney; //付款金额
            $postmsg->customer = $flowData->customer; //售后
            $postmsg->mobile = $flowData->mobile; //手机
            $postmsg->content = $flowData->remark; //备注
            $postmsg->clientuser = $flowData->username; //用户名
            $postmsg->final_money = 1000;
            $postmsg->receiveruser = $flowData->rception_staff;
            $msg = json_encode($postmsg);
            $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
            if ($result != 'True') {
                echo_msg("添加流量业绩信息成功,推送消息失败~");
            }
            echo_msg("添加流量补款业绩信息成功~");
        } else {
            if (!empty($rception)) {
                //推送消息
                $postmsg = new postMsgClass();
                $postmsg->msgtype = msgType::receivercard;
                $postmsg->userid = $flowData->rception_staff_id;
                $postmsg->username = $flowData->rception_staff;
                $postmsg->clientinfo = $flowData->qq;
                $postmsg->money = $flowData->paymoney;
                $postmsg->tradeNo = $flowData->tradeNo;
                $postmsg->customer = $flowData->customer;
                $postmsg->mobile = $flowData->mobile;
                $postmsg->content = $flowData->remark;
                $postmsg->clientuser = $flowData->username;
                $postmsg->receiveruser = $flowData->rception_staff;
                $msg = json_encode($postmsg);
                $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
                if ($result != 'True') {
                    echo_msg("添加流量补款业绩信息成功,推送消息失败~");
                }
            }
            echo_msg("添加流量业绩信息成功~");
        }
    }
    //删除
    if ($action == 2) {
        $flow = new p_flow($flowData->id);
        $db = create_pdo();
        $result = $flow->delete($db);
        if (!$result[0])
            die_error(USER_ERROR, '删除流量业绩信息失败');
        //推送消息
        $postmsg = new postMsgClass();
        $postmsg->msgtype = msgType::flowCancel;
        $postmsg->userid = $flowData->rception_staff_id;
        $postmsg->username = $flowData->rception_staff;
        $postmsg->clientinfo = $flowData->qq;
        $postmsg->money = $flowData->paymoney;
        $postmsg->tradeNo = $flowData->tradeNo;
        $postmsg->customer = $flowData->customer;
        $postmsg->mobile = $flowData->mobile;
        $postmsg->content = $flowData->remark;
        $postmsg->clientuser = $flowData->username;
        $postmsg->receiveruser = $flowData->rception_staff;
        $msg = json_encode($postmsg);
        $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
        if ($result != 'True') {
            echo_msg("删除流量业绩信息成功,推送消息失败~");
        }

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
        $rceptiondb_id = $flow->get_rception_staff_id();
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
            $postmsg->tradeNo = $flowData->tradeNo;
            $postmsg->customer = $flowData->customer;
            $postmsg->mobile = $flowData->mobile;
            $postmsg->content = $flowData->remark;
            $postmsg->clientuser = $flowData->username;
            $postmsg->receiveruser = $flowData->rception_staff;
            $postmsg->point = $flowData->point;
            $msg = json_encode($postmsg);
            $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
            if ($result != 'True') {
                echo_msg("修改流量业绩信息成功,推送消息失败~");
            }
            if (trim($rceptiondb) != '') {
                //推送消息(作废)
                $postmsgDel = new postMsgClass();
                $postmsgDel->msgtype = msgType::flowCancel;
                $postmsgDel->userid = $rceptiondb_id;
                $postmsgDel->username = $rceptiondb;
                $postmsgDel->clientinfo = $flowData->qq;
                $postmsgDel->money = $flowData->paymoney;
                $postmsgDel->tradeNo = $flowData->tradeNo;
                $postmsgDel->customer = $flowData->customer;
                $postmsgDel->mobile = $flowData->mobile;
                $postmsgDel->content = $flowData->remark;
                $postmsgDel->clientuser = $flowData->username;
                $postmsgDel->receiveruser = $rceptiondb;
                $msg = json_encode($postmsgDel);
                $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
                if ($result != 'True') {
                    echo_msg("删除流量业绩信息成功,推送消息失败~");
                }
            }
        }
        echo_msg('保存成功');
    }if ($action == 6) {
        $db = create_pdo();
        $flow = new p_flow($flowData->id);
        $flow->set_is_refund(1);
        $result = $flow->update($db);
        if (!$result[0])
            die_error(USER_ERROR, '退款操作失败');
        echo_msg('退款成功');
    }
    //批量删除
    if ($action == 10) {
        $id = $_POST['id'];
        $sql = "delete from p_flow where id in($id)";
        $db = create_pdo();
        $result = $db->exec($sql);
        if (!$result)
            die_error(USER_ERROR, '删除数据失败，请稍后重新!');
        echo_msg('删除数据成功');
    }
});
