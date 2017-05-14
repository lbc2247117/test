<?php

/*
 * 二销业绩
 *
 * @author bocheng
 * @copyright 2015 非时序
 * @version 2015/12/23
 */

use Models\Base\Model;
use Models\P_Salecount;
use Models\Base\SqlOperator;
use Models\P_Customerrecord;
use Models\P_Fills_second;

//use Common\PHPExcel\PHPExcel;
require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../common/http.php';
//require '../../api/excel/excel_reader2.php';
require '../../api/excel/PHPExcel/IOFactory.php';
//require '../../api/excel/PHPExcel.php';
require '../../Models/postMsgClass.php';
require '../../Helper/CommonDal.php';

$upload = $_GET['upload'];

//导入excel
if ($upload == 1) {
    $file = $_FILES['filename']['tmp_name'];
    $fullpath = BASE_PATH . "upload/" . md5(uniqid('', TRUE)) . ".xls";
    move_uploaded_file($file, $fullpath);
    $document = PHPExcel_IOFactory::load($fullpath);

// Get the active sheet as an array
    $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

    $arr_one = array();
    $save_one = array();
    foreach ($activeSheetData as $key => $value) {
        if ($key > 1) {
            if (!empty($value["A"])) {
                $arr_one["add_time"] = $value["A"];
                if (empty($value["K"])) {
                    $arr_one["customer_type"] = "新流程";
                } else {
                    $arr_one["customer_type"] = "老流程";
                }

                $arr_one["second_type"] = $value["C"];

                $arr_one["qq"] = $value["D"];
                $arr_one["platform_rception"] = $value["E"];
                $arr_one["headmaster"] = $value["F"];
                $arr_one["payment_method"] = $value["G"];
                $arr_one["play_price"] = $value["J"] + $value["H"] + $value["I"];
                $arr_one["remark"] = $value["K"];

                if (!strtotime(trim($arr_one["add_time"]))) {
                    die_error(USER_ERROR, '第' . $key . '行时间格式不对，请输入例如2015-01-01的格式');
                }
                if (!is_qq(trim($arr_one['qq']))) {
                    die_error(USER_ERROR, $arr_one['qq'] . '第' . $key . '行请填写正确的QQ');
                }
                if (!is_numeric(trim($arr_one["play_price"]))) {
                    die_error(USER_ERROR, '第' . $key . '行请填写正确的金额');
                }
                array_push($save_one, $arr_one);
            } else {
                continue;
            }
        }
    }

    $newArray = array_values($save_one);
    $result = CommonHelper::Insert("p_fills_second", $newArray, TRUE);
    if ($result) {
        echo_msg('导入二销业绩成功~');
    } else {
        die_error(USER_ERROR, '导入二销业绩失败');
    }
}

$action = request_action();

execute_request(HttpRequestMethod::Get, function() use($action) {


    if (!isset($action))
        $action = -1;
    $sort = request_string('sort');
    $sortname = request_string('sortname');
    $workName = request_string("workName");
    $customer_type = request_string("customer_type");
    $searchTime = request_string("searchTime");
    $searchStartTime = request_string("searchStartTime");
    $searchEndTime = request_string("searchEndTime");
    if ($action == 1 || $action == "1") {

        $login_userid = request_login_userid();
        $login_username = request_login_username();
        $manager_role_ids = array(0, 101, 102, 103, 401, 402, 403, 405, 601, 602, 603, 701, 703, 704, 803, 806, 808,1301);
        $manager_userids = array(7, 162, 215,227);
        $is_manager = in_array(get_role_id(), $manager_role_ids) || in_array($login_userid, $manager_userids);
        $platform = new P_Fills_second();
        if (isset($workName)) {
            $platform->set_custom_where(" AND (headmaster LIKE '%" . $workName . "%' OR platform_rception LIKE '%" . $workName . "%' OR second_type LIKE '%" . $workName . "%' OR qq LIKE '%" . $workName . "%' OR play_price LIKE '%" . $workName . "%' OR server_sales LIKE '%" . $workName . "%') ");
        }
        if (isset($customer_type)) {
            if ($customer_type == 1) {
                $platform->set_custom_where(" AND customer_type='新流程'");
            } else if ($customer_type == 2) {
                $platform->set_custom_where(" AND customer_type='老流程'");
            }
        }
        if (isset($searchTime)) {
            $platform->set_custom_where(" AND DATE_FORMAT(add_time,'%Y-%m-%d') = '" . $searchTime . "' ");
        }
        if (isset($searchStartTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchStartTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $platform->set_custom_where(" and DATE_FORMAT(add_time, '" . $formatStr . "') >= '" . $searchStartTime . "' ");
        }
        if (isset($searchEndTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchEndTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $platform->set_custom_where(" and DATE_FORMAT(add_time, '" . $formatStr . "') <= '" . $searchEndTime . "' ");
        }
        if (!$is_manager) {
            $platform->set_custom_where(" and (platform_rception='$login_username' or headmaster='$login_username')");
        }
        if (isset($sort) && isset($sortname)) {
            $platform->set_order_by($platform->get_field_by_name($sortname), $sort);
        } else {
            $platform->set_order_by(P_Fills_second::$field_add_time, 'DESC');
        }
        $platform->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $platform, NULL, true);
        if (!$result[0])
            die_error(USER_ERROR, '获取统计资料失败，请重试');
        $models = Model::list_to_array($result['models'], array(), function(&$d) use($is_manager, $login_userid) {
                    
                });
        $totalmoney = 0;
        foreach ($models as $row) {
            $totalmoney+=$row['play_price'];
        }
        $page_no = request_pageno();
        $total = $result['total_count'];
        $max_page_no = ceil($total / request_pagesize());
        $result = array('totalmoney' => $totalmoney, 'total_count' => $total, "list" => $models, 'page_no' => $page_no, 'max_page_no' => $max_page_no, 'code' => 0);
        exit(get_response($result));
        // echo_list_result($result, $models);
    }
    if ($action == 10) {
        $startTime = request_string("start_time");
        $endTime = request_string("end_time");
        $expolt = new ExportData2Excel();
        $p_fill_second = new P_Fills_second();
        if (isset($startTime)) {
            $p_fill_second->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $p_fill_second->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') <= '" . $endTime . "' ");
        }
        $p_fill_second->set_query_fields(array('add_time', 'customer_type', 'second_type', 'qq', 'play_price', 'fill_sum', 'platform_rception', 'headmaster', 'payment_method', 'remark'));
        $db = create_pdo();
        $result = Model::query_list($db, $p_fill_second, NULL, true);
        if (!$result[0]) {
            $expolt->create(array('导出错误'), array(array('二销业绩数据导出失败,请稍后重试!')), "二销业绩数据导出", "二销业绩");
        }
        $models = Model::list_to_array($result['models'], array(), function() {
                    
                });
        $title_array = array('日期', '客户类型', '二销项目', '客户QQ', '付款金额', '欠款金额', '平台销售', '班主任', '支付方式', '备注');
        $expolt->create($title_array, $models, "二销业绩数据导出", "二销营业绩");
    }
});
execute_request(HttpRequestMethod::Post, function() use($action) {
    $platformData = request_object();
    //添加
    if ($action == 1) {
        $platform = new P_Fills_second();
        $platform->set_field_from_array($platformData);
        $add_time = $platformData->add_time;
        $platform->set_add_time($add_time);
        $platform->set_st_is_approve(1);
        $platform->set_play_price($platformData->play_price*(1-$platformData->pay_rate));
        $db = create_pdo();
        $result = $platform->insert($db);
        if (!$result[0])
            die_error(USER_ERROR, '添加二销业绩失败');


        $ordermsg = new postMsgClass();
        $username = $platformData->headmaster;
        $ordermsg->sellerorder = $platformData->play_price;
        $ordermsg->userid = $platformData->platform_rception_id; //售后全部弹，售前指定人弹
        $ordermsg->username = $username;
        $strSql = "select sum(play_price) from P_Fills_second where headmaster='$username' and  DATE_FORMAT(add_time,'%Y-%m')= DATE_FORMAT(SYSDATE(),'%Y-%m')";
        $result = $db->query($strSql);
        $allorderArr = $result->fetch(PDO::FETCH_NUM);
        $allorder = $allorderArr[0];

        $strSql = "select sum(play_price) from P_Fills_second where DATE_FORMAT(add_time,'%Y-%m-%d')= DATE_FORMAT('$add_time','%Y-%m-%d')";
        $result = $db->query($strSql);
        $dayorderArr = $result->fetch(PDO::FETCH_NUM);
        $dayorder = $dayorderArr[0];
        $strSql = "select headmaster,sum(play_price) from P_Fills_second where DATE_FORMAT(add_time,'%Y-%m-%d')= DATE_FORMAT('$add_time','%Y-%m-%d') GROUP BY headmaster order by sum(play_price) desc LIMIT 3";
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
        $ordermsg->msgtype = msgType::sellerback;
        $postmsg->createtime = $add_time; //日期
        $ordermsg->rewardType = 2;
        $omsg = json_encode($ordermsg);
        $result = curl_http_post(PUSH_MESSAGE_URL, $omsg);
        if ($result != 'True')
            die_error(USER_ERROR, "添加二销业绩成功，推送消息失败~");
        echo_msg("添加二销业绩信息成功~");
    }
    //删除
    if ($action == 2) {
        $platform = new P_Fills_second($platformData->id);
        $db = create_pdo();
        $result = $platform->load($db, $platform);
        if (!$result[0])
            die_error(USER_ERROR, '获取数据失败');
        $second_type = $platform->get_second_type();
        if ($second_type == '代运营补欠款')
            die_error(USER_ERROR, '代运营补欠款请到代运营补款页面删除~');
        $platform->reset();
        $platform->set_id($platformData->id);
        $result = $platform->delete($db);
        if (!$result[0])
            die_error(USER_ERROR, '删除二销业绩信息失败');
        echo_msg('删除二销业绩信息成功~');
    }
    //修改
    if ($action == 3) {
        $db = create_pdo();
        $platform = new P_Fills_second($platformData->id);
        $result = $platform->load($db, $platform);
        if (!$result[0])
            die_error(USER_ERROR, '获取数据失败');
        $second_type = $platform->get_second_type();
        if ($second_type == '代运营补欠款')
            die_error(USER_ERROR, '代运营补欠款请到代运营补款页面修改~');
        $platform->reset();
        $platform->set_field_from_array($platformData);
        $platform->set_id($platformData->id);
        $result = $platform->update($db, true);
        if (!$result[0])
            die_error(USER_ERROR, '保存二销业绩失败');
        echo_msg('保存成功');
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

