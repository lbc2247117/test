<?php

/**
 * 提交流量点业绩
 *
 * @author bocheng
 * @copyright 2016 非时序
 * @version 2016-01-17
 */
use Models\Base\Model;
use Models\p_sendflow;
use Models\Base\SqlOperator;
use Models\P_Customerrecord;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../common/http.php';
require '../../Models/postMsgClass.php';
require '../../api/excel/PHPExcel/IOFactory.php';
require '../../Helper/CommonDal.php';

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
                $arr_one["username"] = $value["B"];
                $arr_one["paymoney"] = $value["C"];
                $arr_one["mobile"] = $value["D"];
                $arr_one["qq"] = $value["E"];
                $arr_one["customer"] = $value["F"];
                $arr_one["point"] = $value["G"];
                $arr_one["rception_staff"] = $value["H"];
                $arr_one["remark"] = $value["I"];
                if (!strtotime($arr_one["add_time"])) {
                    die_error(USER_ERROR, '时间格式不对，请输入例如2015-01-01的格式');
                }
                if (!is_qq($arr_one['qq'])) {
                    die_error(USER_ERROR, '请填写正确的QQ');
                }
                if (!is_mobilephone($arr_one['mobile'])) {
                    die_error(USER_ERROR, '请填写正确的手机号');
                }
                if (!is_numeric($arr_one["point"])) {
                    die_error(USER_ERROR, '请填写正确的流量点');
                }
                if (!is_numeric($arr_one["paymoney"])) {
                    die_error(USER_ERROR, '请填写正确的付款金额');
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
    $manager_role_ids = array(0, 101, 102, 103, 401, 402, 403, 404, 601, 602, 701, 702, 713, 714, 715, 801, 802, 901, 902, 1102);
    $manager_userids = array(43, 187, 39, 48, 42, 52, 291, 298, 324, 326, 349, 71, 215);

    $is_manager = in_array(get_role_id(), $manager_role_ids) || in_array($login_userid, $manager_userids);

    $sort = request_string('sort');
    $sortname = request_string('sortname');
    $workName = request_string("workName");
    $searchTime = request_string("searchTime");
    $searchStartTime = request_string("searchStartTime");
    $searchEndTime = request_string("searchEndTime");
    $qq = request_string("qq");
    if ($action == 1) {
        $sendflow = new p_sendflow();
        if (isset($workName)) {
            $sendflow->set_custom_where(" AND (customer LIKE '%" . $workName . "%' OR rception_staff LIKE '%" . $workName . "%' OR mobile LIKE '%" . $workName . "%' OR qq LIKE '%" . $workName . "%' OR paymoney LIKE '%" . $workName . "%' OR username LIKE '%" . $workName . "%') ");
        }

        if (isset($searchTime)) {
            $sendflow->set_custom_where(" AND DATE_FORMAT(add_time,'%Y-%m-%d') = '" . $searchTime . "' ");
        }
        if (isset($searchStartTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchStartTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $sendflow->set_custom_where(" and DATE_FORMAT(add_time, '" . $formatStr . "') >= '" . $searchStartTime . "' ");
        }
        if (isset($searchEndTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchEndTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $sendflow->set_custom_where(" and DATE_FORMAT(add_time, '" . $formatStr . "') <= '" . $searchEndTime . "' ");
        }
        if (isset($sort) && isset($sortname)) {
            $sendflow->set_order_by($sendflow->get_field_by_name($sortname), $sort);
        } else {
            $sendflow->set_order_by(p_sendflow::$field_add_time, 'DESC');
        }
        $sendflow->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $sendflow, NULL, true);
        if (!$result[0])
            die_error(USER_ERROR, '获取统计资料失败，请重试');
        $models = Model::list_to_array($result['models'], array(), function(&$d) use($is_manager, $login_userid) {
                    
                });
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
    $sendflowData = request_object();
    //$rception = $flowData->rception_staff;
    //添加
    if ($action == 1) {
        $sendflow = new p_sendflow();
        $sendflow->set_field_from_array($sendflowData);
//        if (!empty($rception))
//            $flow->set_allot_time('now');
        $sendflow->set_add_time('now');
        $db = create_pdo();
        $result = $sendflow->insert($db);
        if (!$result[0])
            die_error(USER_ERROR, "添加提交流量业绩信息失败~");
//        if (!empty($rception)) {
//            //推送消息
//            $postmsg = new postMsgClass();
//            $postmsg->msgtype = msgType::receivercard;
//            $postmsg->userid = $flowData->rception_staff_id;
//            $postmsg->username = $flowData->rception_staff;
//            $postmsg->clientinfo = $flowData->qq;
//            $postmsg->money = $flowData->paymoney;
//            $postmsg->point = $flowData->point;
//            $postmsg->customer = $flowData->customer;
//            $postmsg->mobile = $flowData->mobile;
//            $postmsg->content = $flowData->remark;
//            $postmsg->clientuser = $flowData->username;
//            $postmsg->receiveruser = $flowData->rception_staff;
//            $msg = json_encode($postmsg);
//            $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
//            if ($result != 'True') {
//                echo_msg("添加流量业绩信息成功,推送消息失败~");
//            }
//        }
        echo_msg("添加提交流量业绩信息成功~");
    }
    //删除
    if ($action == 2) {
        $p_flow = new p_sendflow($sendflowData->id);
        $db = create_pdo();
        $result = $p_flow->delete($db);
        if (!$result[0])
            die_error(USER_ERROR, '删除流量业绩信息失败');
        echo_msg('删除流量业绩信息成功~');
    }
    //修改信息
    if ($action == 3) {
        $platform = new p_sendflow($sendflowData->id);
        $db = create_pdo();

        $platform->reset();
        $platform->set_field_from_array($sendflowData);
        $platform->set_id($sendflowData->id);

        $result = $platform->update($db, true);
        if (!$result[0])
            die_error(USER_ERROR, '修改提交流量点业绩失败');

        echo_msg('修改提交流量点业绩成功');
    }
});
