<?php

/**
 * 平台业绩
 *
 * @author bocheng
 * @copyright 2015 非时序
 * @version 2015/12/23
 */
use Models\Base\Model;
use Models\P_Platform;
use Models\Base\SqlOperator;
use Models\P_Customerrecord;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../common/http.php';
require '../../Models/postMsgClass.php';
//require '../../api/excel/excel_reader2.php';
require '../../api/excel/PHPExcel/IOFactory.php';
//require '../../api/excel/PHPExcel.php';
require '../../Helper/CommonDal.php';

$upload = $_GET['upload'];
//导入excel
if ($upload == 1) {
    $file = $_FILES['filename']['tmp_name'];
    $fullpath = BASE_PATH . "/upload/" . md5(uniqid('', TRUE)) . ".xls";
    move_uploaded_file($file, $fullpath);


//    $data = new Spreadsheet_Excel_Reader();
//    $data->setOutputEncoding('UTF-8');
//    $data->read(iconv('utf-8', 'gb2312', $fullpath));
    $objReader = PHPExcel_IOFactory::createReaderForFile($fullpath);
    //$objPHPExcel = $objReader->load($fullpath);
    // Get the active sheet as an array
    $document = PHPExcel_IOFactory::load($fullpath);
    $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

    $arr_one = array();
    $save_one = array();
    foreach ($activeSheetData as $key => $value) {
        if ($key > 1) {
            if (!empty($value["A"])) {
                $arr_one["add_time"] = $value["A"];
                $arr_one["username"] = $value["B"];
                $arr_one["money"] = $value["C"];
                $arr_one["mobile"] = $value["D"];
                $arr_one["qq"] = $value["E"];
                $arr_one["customer"] = $value["F"];
                $arr_one["tradeNo"] = $value["G"];
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
                if (!is_numeric($arr_one["money"])) {
                    die_error(USER_ERROR, '请填写正确的付款金额');
                }
                array_push($save_one, $arr_one);
            } else {
                continue;
            }
        }
    }
    //$objPHPExcel->setActiveSheetIndex(1);
//    $objWorksheet = $objPHPExcel->getActiveSheet();
//    $i=0;
//    foreach ($objWorksheet->getRowIterator() as $row) {
//        $cellIterator = $row->getCellIterator();
//        $cellIterator->setIterateOnlyExistingCells(false);
//        foreach ($cellIterator as $key => $cell) {
//            switch($key){
//                    case 0:
//                        //$arr["addTime"]=$data->sheets[0]['cells'][$i][$j]==null?"":$data->sheets[0]['cells'][$i][$j];
//                        $arr["add_time"]=date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($cell->getValue()));
//                    break;
//                    case 1:
//                        $arr["username"]=$cell->getValue();
//                    break;
//                    case 2:
//                        $arr["money"]=$cell->getValue();
//                    break;
//                    case 3:
//                        $arr["mobile"]=$cell->getValue();
//                    break;
//                    case 4:
//                        $arr["qq"]=$cell->getValue();
//                    break;
//                    case 5:
//                        $arr["customer"]=$cell->getValue();
//                    break;
//                    case6:
//                        $arr["tradeNo"]=$cell->getValue();
//                    break;
//                    case 7:
//                        $arr["rception_staff"]=$cell->getValue();
//                    break;
//                    case 8:
//                        $arr["remark"]=$cell->getValue();
//                    break;
//                }
//        }
//        $array[$i] = $arr;
//        $i++;
//    }
//    $platForm = new P_Platform();
//    $platForm->set_add_time($array[])    

    $newArray = array_values($save_one);

    if (CommonHelper::Insert("p_platform", $newArray, TRUE)) {
        echo_msg('导入平台业绩成功~');
    } else {
        die_error(USER_ERROR, '导入平台业绩失败');
    }
}

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {


    $login_userid = request_login_userid();
    $login_username = request_login_username();
    $manager_role_ids = array(0, 101, 102, 103, 401, 402, 403, 405, 601, 602, 603, 701, 703, 704, 803, 806, 808, 1105);
    $manager_userids = array(7);
    $is_manager = in_array(get_role_id(), $manager_role_ids) || in_array($login_userid, $manager_userids);


    $sort = request_string('sort');
    $sortname = request_string('sortname');
    $workName = request_string("workName");
    $isRecept = request_string("isRecept");
    // $wwName = request_string("wwName");
    $searchTime = request_string("searchTime");
    $searchStartTime = request_string("searchStartTime");
    $searchEndTime = request_string("searchEndTime");
    $qq = request_string("qq");
    if ($action == 1) {
        $platform = new P_Platform();
        if (isset($workName)) {
            $platform->set_custom_where(" AND (customer LIKE '%" . $workName . "%' OR rception_staff LIKE '%" . $workName . "%' OR tradeNo LIKE '%" . $workName . "%' OR mobile LIKE '%" . $workName . "%' OR qq LIKE '%" . $workName . "%' OR money LIKE '%" . $workName . "%' OR username LIKE '%" . $workName . "%') ");
        }
//        if (isset($wwName)) {
//            $platform->set_where_and(P_Platform::$field_ww, SqlOperator::Like, "%" . $wwName . "%");
//        }
        if (isset($isRecept)) {
            if ($isRecept == 1) {
                $platform->set_custom_where('AND rception_staff is not null');
            } else if ($isRecept == 2) {
                $platform->set_custom_where('AND rception_staff is null');
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
            $platform->set_custom_where(" and (rception_staff='$login_username')");
        }
        if (isset($sort) && isset($sortname)) {
            $platform->set_order_by($platform->get_field_by_name($sortname), $sort);
        } else {
            $platform->set_order_by(P_Platform::$field_add_time, 'DESC');
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
            $totalmoney+=$row['money'];
        }
        $page_no = request_pageno();
        $total = $result['total_count'];
        $max_page_no = ceil($total / request_pagesize());
        $result = array('totalmoney' => $totalmoney, 'total_count' => $total, "list" => $models, 'page_no' => $page_no, 'max_page_no' => $max_page_no, 'code' => 0);
        exit(get_response($result));
    }
    if ($action == 2) {
        $customer = new P_Customerrecord();
        $customer->set_status(1);
        $customer->set_query_fields(array('userid', 'username', 'nickname'));
        $db = create_pdo();
        $customer_result = Model::query_list($db, $customer);
        $customer_list = Model::list_to_array($customer_result['models'], array(), function(&$d) {
                    $d['id'] = $d['userid'];
                    $d['text'] = $d['username'] . "(" . $d['nickname'] . ")";
                    unset($d['userid']);
                    unset($d['username']);
                    unset($d['nickname']);
                });
        echo_result($customer_list);
    }
    if ($action == 5) {
        $platform = new P_Platform();
        $platform->set_custom_where(" AND ( qq like '%" . $qq . "%' OR ww like '%" . $qq . "%' ) ");
        $db = create_pdo();
        $platform_result = Model::query_list($db, $platform);
        $platform_list = Model::list_to_array($platform_result['models']);
        echo_result($platform_list);
    }
    if ($action == 10) {
        $startTime = request_string("start_time");
        $endTime = request_string("end_time");
        $expolt = new ExportData2Excel();
        $platform = new P_Platform();
        if (isset($startTime)) {
            $platform->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $platform->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') <= '" . $endTime . "' ");
        }
        $platform->set_query_fields(array('add_time', 'mobile', 'qq', 'username', 'money', 'customer', 'tradeNo', 'rception_staff'));
        $db = create_pdo();
        $result = Model::query_list($db, $platform, NULL, true);
        if (!$result[0]) {
            $expolt->create(array('导出错误'), array(array('平台业绩数据导出失败,请稍后重试!')), "平台业绩数据导出", "平台业绩");
        }
        $models = Model::list_to_array($result['models'], array(), function() {
                    
                });
        $title_array = array('日期', '客户手机', 'QQ号', '用户名', '付款金额', '售后名称', '交易号', '平台接待人员');
        $expolt->create($title_array, $models, "平台业绩数据导出", "平台业绩");
    }
    if ($action == 11) {
        $time_unit = request_int('time_unit', 1, 3);
        $condition_mapping = array(
            1 => 'TO_DAYS(add_time) = TO_DAYS(NOW())',
            2 => 'add_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)',
            3 => "DATE_FORMAT(add_time,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m')"
        );
        $sql = "SELECT a.customer,a.customer_id,SUM(a.count) count  FROM(";
        $sql .="SELECT * FROM (";
        $sql .="SELECT customer customer,customer_id customer_id,SUM(decoration_price) count FROM P_Decoration WHERE " . $condition_mapping[$time_unit] . " GROUP BY customer ORDER BY COUNT(customer) DESC";
        $sql .=") d ";
        $sql .="UNION ALL ";
        $sql .="SELECT * FROM (";
        $sql .="SELECT customer customer,customer_id customer_id,SUM(money) count FROM P_Platform WHERE " . $condition_mapping[$time_unit] . " GROUP BY customer ORDER BY COUNT(customer) DESC";
        $sql .=") p ";
        $sql .="UNION ALL ";
        $sql .="SELECT * FROM (";
        $sql .="SELECT customer customer,customer_id customer_id,SUM(all_price) count FROM P_Physica h WHERE " . $condition_mapping[$time_unit] . " GROUP BY customer ORDER BY COUNT(customer) DESC";
        $sql .=") h ";
        $sql .=")a GROUP BY a.customer_id ORDER BY SUM(a.count) desc";
        $db = create_pdo();
        $result = Model::execute_custom_sql($db, $sql);
        if (!$result[0])
            die_error(USER_ERROR, '获取排名数据失败，请重试');
        $result = $result['results'];
        echo_result($result);
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $platformData = request_object();
    //添加
    if ($action == 1) {
        $platform = new P_Platform();
        $platform->set_field_from_array($platformData);
        $add_time = $platformData->add_time;
        $add_date = $platformData->add_date;
        $add_time = $add_date . " $add_time";
        $platform->set_add_time($add_time);
        if (!empty($rception)) {
            $platform->set_allot_time($add_time);
        }
        $db = create_pdo();
        $result = $platform->insert($db);
        if (!$result[0])
            die_error(USER_ERROR, "添加平台业绩信息失败~");
        $rception = $platformData->rception_staff;
        if (empty($rception)) {
            echo_msg("添加平台业绩信息成功~");
        } else {
            //推送消息
            $postmsg = new postMsgClass();
            $postmsg->msgtype = msgType::rception_platform;
            $postmsg->userid = $platformData->rception_staff_id;
            $postmsg->username = $platformData->rception_staff;
            $postmsg->clientinfo = $platformData->qq;
            $postmsg->money = $platformData->money;
            $postmsg->tradeNo = $platformData->tradeNo;
            $postmsg->customer = $platformData->customer;
            $postmsg->mobile = $platformData->mobile;
            $postmsg->content = $platformData->remark;
            $postmsg->clientuser = $platformData->username;
            $postmsg->receiveruser = $platformData->rception_staff;
            $msg = json_encode($postmsg);
            $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
            if ($result == 'True') {
                echo_msg("添加平台业绩信息成功~");
            } else {
                echo_msg("添加平台业绩信息成功,推送消息失败~");
            }
        }
    }
    //删除
    if ($action == 2) {
        $platform = new P_Platform($platformData->id);
        $db = create_pdo();
        $result = $platform->delete($db);
        if (!$result[0])
            die_error(USER_ERROR, '删除平台业绩信息失败');
        echo_msg('删除平台业绩信息成功~');
    }
    //修改信息
    if ($action == 3) {
        $platform = new P_Platform($platformData->id);
        $db = create_pdo();
        $result = $platform->load($db, $platform);
        if (!$result[0])
            die_error(USER_ERROR, '系统错误,请稍后重试~');
        $rceptiondb = $platform->get_rception_staff();
        $platform->reset();
        $platform->set_field_from_array($platformData);
        $platform->set_id($platformData->id);
        $platform->set_is_edit(1);
        if (!empty($rception) && $rception != $rceptiondb) {
            $platform->set_allot_time('now');
        }
        $result = $platform->update($db, true);
        if (!$result[0])
            die_error(USER_ERROR, '修改平台业绩失败');
        $rception = $platformData->rception_staff;
        if (!empty($rception) && $rception != $rceptiondb) {
            //推送消息
            $postmsg = new postMsgClass();
            $postmsg->msgtype = msgType::rception_platform;
            $postmsg->userid = $platformData->rception_staff_id;
            $postmsg->username = $platformData->rception_staff;
            $postmsg->clientinfo = $platformData->qq;
            $postmsg->money = $platformData->money;
            $postmsg->tradeNo = $platformData->tradeNo;
            $postmsg->customer = $platformData->customer;
            $postmsg->mobile = $platformData->mobile;
            $postmsg->content = $platformData->remark;
            $postmsg->clientuser = $platformData->username;
            $postmsg->receiveruser = $platformData->rception_staff;
            $msg = json_encode($postmsg);
            $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
            if ($result != 'True') {
                echo_msg("修改平台业绩信息成功,推送消息失败~");
            }
        }
        echo_msg('修改平台业绩信息成功');
    }
    //批量删除
    if ($action == 10) {
        $id = $_POST['id'];
        $sql = "delete from p_platform where id in($id)";
        $db = create_pdo();
        $result = $db->exec($sql);
        if (!$result)
            die_error(USER_ERROR, '删除数据失败，请稍后重新!');
        echo_msg('删除数据成功');
    }
});
