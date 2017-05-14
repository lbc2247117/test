<?php

/**
 * 代运营
 *
 * @author B.Maru
 * @copyright 2015 星密码
 * @version 2015/7/13
 */
use Models\Base\Model;
use Models\P_GenerationOperation;
use Models\receiver_payaccount;
use Models\Base\SqlOperator;
use Models\wait_msg;
use Models\M_Dept;
use Models\P_Fills_second;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../common/http.php';
require '../../Models/postMsgClass.php';
require '../../api/excel/PHPExcel/IOFactory.php';
require '../../Helper/CommonDal.php';

$upload = $_GET['upload'];
//header("Content-Type:application/vnd.ms-excel;charset=utf-8");
//导入excel
if ($upload == 1) {
    $file = $_FILES['filename']['tmp_name'];
    $fullpath = BASE_PATH . "/upload/" . md5(uniqid('', TRUE)) . ".xls";
    move_uploaded_file($file, $fullpath);
    $document = PHPExcel_IOFactory::load($fullpath);
    $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

    $arr_one = array();
    $save_one = array();
    foreach ($activeSheetData as $key => $value) {
        if ($key > 1) {
            if (!empty($value["A"])) {
                $arr_one["add_time"] = $value["A"];
                $arr_one["sales_numbers"] = $value["B"];
                $arr_one["platform_num"] = $value["C"];
                $arr_one["qq"] = $value["D"];
                $arr_one["platform_sales"] = $value["E"];
                $arr_one["customer"] = $value["F"];
                $arr_one["headmaster"] = $value["G"];

                $arr_one["payment_amount"] = $value["I"];
                $arr_one["final_money"] = $value["H"] - $value["I"];
                $arr_one["remark"] = $value["J"];
                $arr_one['qq'] = trim($arr_one['qq']);
                $arr_one['payType'] = 0;
                if (!empty($arr_one["headmaster"])) {
                    $arr_one['allot_time'] = $arr_one["add_time"];
                }
                if (!strtotime($arr_one["add_time"])) {
                    die_error(USER_ERROR, '时间格式不对，请输入例如2015-01-01的格式');
                }
                if (!is_qq($arr_one['qq'])) {
                    die_error(USER_ERROR, '请填写正确的QQ');
                }
                if (!is_numeric($arr_one["payment_amount"])) {
                    die_error(USER_ERROR, '请填写正确的付款金额');
                }
                if (!is_numeric($arr_one["final_money"])) {
                    die_error(USER_ERROR, '请填写正确的欠款金额');
                }
                $arr_one["customer_type"] = '新流程';
                array_push($save_one, $arr_one);
            } else {
                continue;
            }
        }
    }
    $newArray = array_values($save_one);
    if (count($newArray) > 0) {
        if (CommonHelper::Insert("p_generationoperation", $newArray, TRUE)) {
            echo_msg('导入代运营定金信息成功~');
        } else {
            die_error(USER_ERROR, '导入代运营定金信息失败');
        }
    }
} elseif ($upload == 2) {

//    $sql = 'select qq from p_generationoperation where payType=1 and customer is NULL';
//    $db = create_pdo();
//    $stmt = $db->prepare($sql);
//    $stmt->execute();
//    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
//    for ($i = 0; $i < count($result); $i++) {
//        $qq = $result[$i]['qq'];
//        $sqlcustomer = "select customer,platform_num from  p_generationoperation where payType=0 and qq='$qq'";
//        $stmtcustomer = $db->prepare($sqlcustomer);
//        $stmtcustomer->execute();
//        $resultcustomer = $stmtcustomer->fetchAll(PDO::FETCH_ASSOC);
//        if ($resultcustomer[0]) {
//            $customer = $resultcustomer[0]['customer'];
//            $mobile = $resultcustomer[0]['platform_num'];
//            $sqlupdate = "update p_generationoperation set customer='$customer',platform_num='$mobile' where qq='$qq' and payType=1";
//            $strRst = $db->exec($sqlupdate);
//        }
//    }
//    echo_msg('导入代运营补款信息成功~');
    $file = $_FILES['filename']['tmp_name'];
    $fullpath = BASE_PATH . "/upload/" . md5(uniqid('', TRUE)) . ".xls";
    move_uploaded_file($file, $fullpath);
    $document = PHPExcel_IOFactory::load($fullpath);
    $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);
    $arr_one = array();
    $save_one = array();
    foreach ($activeSheetData as $key => $value) {
        if ($key > 1) {
            if (!empty($value["A"])) {
                $arr_one["add_time"] = $value["A"];
                $arr_one["qq"] = $value["B"];
                $arr_one["payment_amount"] = $value["C"];
                $arr_one["platform_sales"] = $value["D"];
                $arr_one["headmaster"] = $value["E"];
                $arr_one["customer_type"] = $value["F"];
                $arr_one['payType'] = 1;
                $arr_one['qq'] = trim($arr_one['qq']);

                if (!strtotime($arr_one["add_time"])) {
                    die_error(USER_ERROR, '时间格式不对，请输入例如2015-01-01的格式');
                }
                if (!is_qq($arr_one['qq'])) {
                    die_error(USER_ERROR, '请填写正确的QQ');
                }
                if (!is_numeric($arr_one["payment_amount"])) {
                    die_error(USER_ERROR, '请填写正确的付款金额');
                }
                array_push($save_one, $arr_one);
            } else {
                continue;
            }
        }
    }
    $newArray = array_values($save_one);
    if (count($newArray) > 0) {
        if (CommonHelper::Insert("p_generationoperation", $newArray, TRUE)) {
            echo_msg('导入代运营补款信息成功~');
        } else {
            die_error(USER_ERROR, '导入代运营补款信息失败');
        }
    }
}
$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {

    $login_userid = request_login_userid();
    $login_username = request_login_username();
    $manager_role_ids = array(0, 101, 102, 103, 401, 402, 403, 405, 601, 602, 603, 701, 703, 704, 803, 806, 808,1301);
    $manager_userids = array(7, 71, 215, 162,227);
    $is_manager = in_array(get_role_id(), $manager_role_ids) || in_array($login_userid, $manager_userids);
    $sort = request_string('sort');
    $sortname = request_string('sortname');
    $workName = request_string("workName");
    $searchTime = request_string("searchTime");
    $searchStartTime = request_string("searchStartTime");
    $searchEndTime = request_string("searchEndTime");
    $payType = request_string('payType');
    $qq = request_string("qq");
    if ($action == 1) {
        $generationOperation = new P_GenerationOperation();
        if (isset($workName)) {
            $generationOperation->set_custom_where(" AND (payment_amount LIKE '%" . $workName . "%' OR qq LIKE '%" . $workName . "%' OR platform_num LIKE '%" . $workName . "%' OR platform_sales LIKE '%" . $workName . "%' OR customer LIKE '%" . $workName . "%' OR headmaster LIKE '%" . $workName . "%') ");
        }
        $generationOperation->set_custom_where(" AND payType in " . $payType);
        if (isset($searchTime)) {
            $generationOperation->set_custom_where(" AND DATE_FORMAT(add_time,'%Y-%m-%d') = '" . $searchTime . "' ");
        }
        if (isset($searchStartTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchStartTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $generationOperation->set_custom_where(" and DATE_FORMAT(add_time, '" . $formatStr . "') >= '" . $searchStartTime . "' ");
        }
        if (isset($searchEndTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchEndTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $generationOperation->set_custom_where(" and DATE_FORMAT(add_time, '" . $formatStr . "') <= '" . $searchEndTime . "' ");
        }
        if (!$is_manager) {
            $generationOperation->set_custom_where(" AND (platform_sales='$login_username' or headmaster='$login_username') ");
        }
        //$generationOperation->set_custom_where(" and (headmaster is not null and headmaster <>'')");
        if (isset($sort) && isset($sortname)) {
            $generationOperation->set_custom_where(" order by st_is_approve=3 desc,$sortname $sort");
        } else {
            $generationOperation->set_custom_where(' order by st_is_approve=3 desc,add_time desc');
        }
        $generationOperation->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $generationOperation, NULL, true);
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

    if ($action == 5) {
        $generationOperation = new P_GenerationOperation();
        $generationOperation->set_custom_where(" AND payType=0");
        $generationOperation->set_custom_where(" AND ( qq like '%" . $qq . "%' OR platform_num like '%" . $qq . "%' ) ");
        $db = create_pdo();
        $generationOperation_result = Model::query_list($db, $generationOperation);
        $generationOperation_list = Model::list_to_array($generationOperation_result['models']);
        echo_result($generationOperation_list);
    }
    if ($action == 10) {
        $startTime = request_string("start_time");
        $endTime = request_string("end_time");
        $expolt = new ExportData2Excel();

        $sql_pre = "SELECT  a.transfer_time as add_time,b.platform_num,b.qq,b.sales_numbers,a.pay_money as payment_amount,a.platform_sales,b.customer,b.headmaster,a.payment_method,a.pay_account_type as rcept_account,a.pay_username as pay_user from receiver_payaccount as a left JOIN p_generationoperation as b ON a.general_operal_id=b.id where 1=1";
        if (isset($startTime)) {
            $sql_pre.=" and DATE_FORMAT(a.transfer_time, '%Y-%m-%d') >=DATE_FORMAT('$startTime', '%Y-%m-%d')";
        }
        if (isset($endTime)) {
            $sql_pre.=" and DATE_FORMAT(a.transfer_time, '%Y-%m-%d') <=DATE_FORMAT('$endTime', '%Y-%m-%d')";
        }
        $sql_pre.=' and payType in ' . $payType . ' order by a.transfer_time asc';
        $db = create_pdo();
        $result = $db->query($sql_pre);
        $models = $result->fetchAll(PDO::FETCH_ASSOC);
        $title_array = array('添加时间', '手机号码', 'QQ', '套餐类型', '付款金额', '平台销售', '售后名称', '班主任', '支付方式', '收款账号', '付款人');
        $expolt->create($title_array, $models, "代运营业绩数据导出", "代运营业绩");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $generationOperationData = request_object();
    $db = create_pdo();
    //添加
    if ($action == 1) {
        $lastid = 0;
        $username = request_login_username();
        $userid = request_login_userid();
        $dept_id = get_dept_id();
        if (empty($generationOperationData->customer_type))
            die_error(USER_ERROR, '请选择客户来源');
//        $qq = $generationOperationData->qq;
//        $mobile = $generationOperationData->platform_num;
//        $sales_numbers = $generationOperationData->sales_numbers;
//        $gen = new P_GenerationOperation();
//        $gen->set_custom_where(" and qq='$qq'");
//        $gen->set_custom_where(" and payType=0");
//        $result = Model::query_list($db, $gen, NULL, true);
//        if (!$result[0])
//            die_error(USER_ERROR, '未查询到该条记录');
//        $models = Model::list_to_array($result['models']);
        $generationOperation = new P_GenerationOperation();
        $generationOperation->set_field_from_array($generationOperationData);
        if ($generationOperationData->customer_type == 1) {
            $generationOperation->set_customer_type('优程');
        } else {
            $generationOperation->set_customer_type('领远');
        }
        $add_time = $generationOperationData->add_time;
        $generationOperation->set_add_time($add_time);
        $generationOperation->set_payType(0);
        $generationOperation->set_st_is_approve(1);
//        if (count($models) > 0) {
//            $generationOperation->set_platform_sales($models[0]['platform_sales']);
//            $generationOperation->set_platform_sales_id($models[0]['platform_sales_id']);
//            $generationOperation->set_customer($models[0]['customer']);
//            $generationOperation->set_customer_id($models[0]['customer_id']);
//            $generationOperation->set_headmaster($models[0]['headmaster']);
//            $generationOperation->set_headmaster_id($models[0]['headmaster_id']);
//            $generationOperation->set_allot_time($models[0]['allot_time']);
//            $generationOperation->set_sales_numbers($models[0]['sales_numbers']);
//            $generationOperation->set_customer_type($models[0]['customer_type']);
//            $generationOperation->set_platform_num($models[0]['platform_num']);
//            $generationOperation->set_final_money(0);
//            $generationOperation->set_rception_money(0);
//            $generationOperation->set_payType(2);
////            $result = $generationOperation->insert($db);
////            if (!$result[0])
////                die_error(USER_ERROR, '添加代运营信息失败');
////            echo_msg('添加代运营信息成功~');
//        }
        $wait = new wait_msg();
        $wait->set_add_time('now');
        $wait->set_request_id(request_login_userid());
        $wait->set_request_name(request_login_username());
        $wait->set_dept_id($dept_id);
        $wait->set_msgtype(1);
        $wait->set_status(1);
        $ary_payment_amount = $generationOperationData->payment_amount;
        $ary_rcept_account = $generationOperationData->rcept_account_each;
        $ary_transfer_time = $generationOperationData->transfer_time;
        $ary_pay_rate = $generationOperationData->pay_rate_each;
        $ary_pay_user = $generationOperationData->pay_user;
        $ary_payment_method = $generationOperationData->payment_method_each;
        pdo_transaction($db, function($db)use($generationOperation, $wait, $ary_payment_method, $ary_payment_amount, $ary_rcept_account, $ary_pay_rate, $ary_transfer_time, $ary_pay_user, $username, $generationOperationData) {
            $payment_amount = 0;
            if (count($ary_payment_amount) == 1) {
                $payment_amount = $ary_payment_amount - ($ary_payment_amount * $ary_pay_rate);
            } else {
                for ($i = 0; $i < count($ary_payment_amount); $i++) {
                    $payment_amount+=$ary_payment_amount[$i] - ($ary_payment_amount[$i] * $ary_pay_rate[$i]);
                }
            }
            $generationOperation->set_payment_amount($payment_amount);
            $result = $generationOperation->insert($db);
            if (!$result[0])
                die_error(USER_ERROR, '添加代运营信息失败');
            $id = $db->lastInsertId();
            $genChlid = new P_GenerationOperation($id);
            $genChlid->set_child_id($id);
            $result = $genChlid->update($db);
            if (!$result[0])
                die_error(USER_ERROR, '修改代运营子ID失败~');
            $lastid = $id;
            $wait->set_task_id($id);
            $result = $wait->insert($db);
            if (!$result[0])
                die_error(USER_ERROR, '添加处理信息失败!');
            for ($i = 0; $i < count($ary_payment_amount); $i++) {
                if (count($ary_payment_amount) == 1) {
                    $pay_money = $ary_payment_amount;
                    $pay_account_type = $ary_rcept_account;
                    $transfer_time = $ary_transfer_time;
                    $pay_username = $ary_pay_user;
                    $pay_rate = $ary_pay_rate;
                    $payment_method = $ary_payment_method;
                } else {
                    $pay_money = $ary_payment_amount[$i];
                    $pay_account_type = $ary_rcept_account[$i];
                    $transfer_time = $ary_transfer_time[$i];
                    $pay_username = $ary_pay_user[$i];
                    $pay_rate = $ary_pay_rate[$i];
                    $payment_method = $ary_payment_method[$i];
                }
                $receiver = new receiver_payaccount();
                $receiver->set_general_operal_id($lastid);
                $receiver->set_pay_account_type($pay_account_type);
                $receiver->set_pay_money($pay_money - ($pay_money * $pay_rate));
                $receiver->set_transfer_time($transfer_time);
                $receiver->set_pay_username($pay_username);
                $receiver->set_add_time('now');
                $receiver->set_add_user($username);
                $receiver->set_pay_rate($pay_rate);
                $receiver->set_platform_sales_id($generationOperationData->platform_sales_id);
                $receiver->set_platform_sales($generationOperationData->platform_sales);
                $receiver->set_payment_method($payment_method);
                $receiver->set_approve_status(1);
                $result = $receiver->insert($db);
                if (!$result[0])
                    die_error(USER_ERROR, '添加代运营子记录失败~');
            }
        });
        $dept = new M_Dept($dept_id);
        $result = $dept->load($db, $dept);
        if (!$result[0])
            die_error(USER_ERROR, '系统错误,请稍后重试~');
        $dept_name = $dept->get_text();
        $ary_id=array(162,19,9,227);
        for($i=0;$i<4;$i++){
            $postmsg = new postMsgClass();
            $postmsg->username = $username;
            $postmsg->userid = $ary_id[$i];
            $postmsg->department = $dept_name;
            $postmsg->msgtype = msgType::sendbacksale;
            $msg = json_encode($postmsg);
            $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
        }
        if ($result == 'True')
            echo_msg('添加代运营信息成功~');
        else
            die_error(USER_ERROR, "添加代运营成功，发送广播失败~");
    }
    //补款
    if ($action == 4) {
        $id = $generationOperationData->id;
        $gen = new P_GenerationOperation($id);
        $result = $gen->load($db, $gen);
        if (!$result[0])
            die_error(USER_ERROR, '获取数据失败');
        $generationOperation = new P_GenerationOperation();
        $generationOperation->set_add_time($generationOperationData->addtime);
        $generationOperation->set_qq($gen->get_qq());
        $generationOperation->set_platform_sales($gen->get_platform_sales());
        $generationOperation->set_platform_sales_id($gen->get_platform_sales_id());
        $generationOperation->set_customer($gen->get_customer());
        $generationOperation->set_customer_id($gen->get_customer_id());
        $generationOperation->set_headmaster($gen->get_headmaster());
        $generationOperation->set_headmaster_id($gen->get_headmaster_id());
        $generationOperation->set_allot_time($gen->get_allot_time());
        $generationOperation->set_sales_numbers($gen->get_sales_numbers());
        $generationOperation->set_customer_type($gen->get_customer_type());
        $generationOperation->set_platform_num($gen->get_platform_num());
        $generationOperation->set_rception_money($gen->get_rception_money());
        $generationOperation->set_payType(2);
        $generationOperation->set_st_is_approve(1);
        $genChlid = new P_GenerationOperation($gen->get_child_id());
        $resultChlid = $genChlid->load($db, $genChlid);
        if (!$resultChlid[0])
            die_error(USER_ERROR, '获取数据失败1');
        if ($gen->get_id() != $gen->get_child_id() && $genChlid->get_st_is_approve() != 2) {
            die_error(USER_ERROR, '存在未审核的补款，请在原补款业绩上就行修改');
        }
        $final_money = $genChlid->get_final_money();
        $ary_payment_amount = $generationOperationData->payment_amount;
        $ary_rcept_account = $generationOperationData->rcept_account_each;
        $ary_transfer_time = $generationOperationData->transfer_time;
        $ary_pay_rate = $generationOperationData->pay_rate_each;
        $ary_pay_user = $generationOperationData->pay_user;
        $ary_payment_method = $generationOperationData->payment_method_each;
        pdo_transaction($db, function($db)use($generationOperation, $final_money, $gen, $ary_payment_amount, $ary_rcept_account, $ary_payment_method, $ary_pay_rate, $ary_transfer_time, $ary_pay_user, $username, $generationOperationData) {
            $payment_amount = 0;
            if (count($ary_payment_amount) == 1) {
                $payment_amount = $ary_payment_amount - ($ary_payment_amount * $ary_pay_rate);
            } else {
                for ($i = 0; $i < count($ary_payment_amount); $i++) {
                    $payment_amount+=$ary_payment_amount[$i] - ($ary_payment_amount[$i] * $ary_pay_rate[$i]);
                }
            }
            $generationOperation->set_parent_id($gen->get_id());
            $generationOperation->set_final_money($final_money - $payment_amount);
            $generationOperation->set_payment_amount($payment_amount);
            $result = $generationOperation->insert($db);
            if (!$result[0])
                die_error(USER_ERROR, '添加补款失败');
            $lastid = $db->lastInsertId();
            $genUpdate = new P_GenerationOperation();
            $genUpdate->set_id($gen->get_id());
            $genUpdate->set_child_id($lastid);
            $result = $genUpdate->update($db);
            if (!$result[0])
                die_error(USER_ERROR, '添加补款失败~');
            for ($i = 0; $i < count($ary_payment_amount); $i++) {
                if (count($ary_payment_amount) == 1) {
                    $pay_money = $ary_payment_amount;
                    $pay_account_type = $ary_rcept_account;
                    $transfer_time = $ary_transfer_time;
                    $pay_username = $ary_pay_user;
                    $pay_rate = $ary_pay_rate;
                    $payment_method = $ary_payment_method;
                } else {
                    $pay_money = $ary_payment_amount[$i];
                    $pay_account_type = $ary_rcept_account[$i];
                    $transfer_time = $ary_transfer_time[$i];
                    $pay_username = $ary_pay_user[$i];
                    $pay_rate = $ary_pay_rate[$i];
                    $payment_method = $ary_payment_method[$i];
                }
                $receiver = new receiver_payaccount();
                $receiver->set_general_operal_id($lastid);
                $receiver->set_pay_account_type($pay_account_type);
                $receiver->set_pay_money($pay_money - ($pay_money * $pay_rate));
                $receiver->set_transfer_time($transfer_time);
                $receiver->set_pay_username($pay_username);
                $receiver->set_add_time('now');
                $receiver->set_add_user(request_login_username());
                $receiver->set_pay_rate($pay_rate);
                $receiver->set_platform_sales_id($gen->get_platform_sales_id());
                $receiver->set_platform_sales($gen->get_platform_sales());
                $receiver->set_payment_method($payment_method);
                $receiver->set_approve_status(1);
                $result = $receiver->insert($db);
                if (!$result[0])
                    die_error(USER_ERROR, '添加补款失败~');
            }
        });
        echo_msg('添加补款成功~');
    }
    //删除
    if ($action == 2) {
        $db = create_pdo();
        $id = $generationOperationData->id;
        $generationOperation = new P_GenerationOperation();
        $generationOperation->set_custom_where(" and id=$id");
        $result = Model::query_list($db, $generationOperation, NULL, true);
        if (!$result[0])
            die_error(USER_ERROR, '未查询到该条记录');
        $payType = $generationOperationData->payType;
        if ($payType == 1) {        //删除代运营补款和二销中的代运营
            $qq = $generationOperationData->qq;
            $money = $generationOperationData->payment_amount;
            $heads = $generationOperationData->headmaster;
            pdo_transaction($db, function($db)use($db, $id, $qq, $money, $heads) {
                $gen = new P_GenerationOperation($id);
                $result = $gen->delete($db);
                if (!$result[0])
                    die_error(USER_ERROR, '删除代运营补欠款失败~');
                $sql = "delete from p_fills_second where qq='$qq' and headmaster='$heads' and play_price='$money' and second_type='代运营补欠款'";
                $result = $db->exec($sql);
                if ($result === FALSE)
                    die_error(USER_ERROR, '删除代运营补欠款失败！');
            });
            echo_msg('删除代运营补欠成功~');
        } else {
            //判断财务是否审核，如果审核就不能删除
            $models = Model::list_to_array($result['models']);
            if ($models[0]['st_is_approve'] == 2)
                die_error(USER_ERROR, '该条记录已经财务审核，不能删除,请刷新页面查看最新状态');

            //查询是否有多条
            $qq = $models[0]['qq'];
            $rception_money = $models[0]['rception_money'];
            pdo_transaction($db, function($db)use($db, $qq, $id, $rception_money, $models) {
                $sql = "delete from p_generationoperation where id=$id";
                if ($models[0]["parent_id"] == 0) {
                    $sql.=" or parent_id=$id";
                }
                $result = $db->exec($sql);
                if ($result === FALSE)
                    die_error(USER_ERROR, '删除代运营失败!');
                if ($models[0]["parent_id"] == 0) {
                    $sql = "delete from wait_msg where task_id=$id";
                    $result = $db->exec($sql);
                    if ($result === FALSE)
                        die_error(USER_ERROR, '删除代运营失败!');
                }
                $sql = "delete from receiver_payaccount where general_operal_id=$id";
                $result = $db->exec($sql);
                if ($result === FALSE)
                    die_error(USER_ERROR, '删除代运营失败!');
            });
            echo_msg('删除代运营信息成功~');
        }
    }
    //修改信息
    if ($action == 3) {
        $db = create_pdo();
        $id = $generationOperationData->id;
        $generationOperation = new P_GenerationOperation();
        $generationOperation->set_custom_where(" and id=$id");
        $res = Model::query_list($db, $generationOperation, NULL, true);
        if (!$res[0])
            die_error(USER_ERROR, '未查询到该条记录');
        $models = Model::list_to_array($res['models']);
        if ($models[0]['st_is_approve'] == 2)
            die_error(USER_ERROR, '该条记录已经财务审核，不能修改,请刷新页面查看最新状态');

        $paytpye = $models[0]['payType'];
        $dbpayment_amount = $models[0]['payment_amount'];
        $dbqq = $models[0]['qq'];
        $dbplatform_sales = $models[0]['platform_sales'];
        $headmasterdb = $models[0]['headmaster'];
        $headmaster = $generationOperationData->headmaster;

        $generationOperation->reset();
        $generationOperation->set_field_from_array($generationOperationData);
        if ($generationOperationData->customer_type == 1) {
            $generationOperation->set_customer_type('优程');
        } else {
            $generationOperation->set_customer_type('领远');
        }
        $generationOperation->set_id($generationOperationData->id);
        $generationOperation->set_st_is_approve(1);
        $generationOperation->set_is_edit(1);
        if ($paytpye == 1) { //补欠款，需要更新两表
            //先根据条件查询二销的补款，查询条件是QQ,补款金额，平台销售和班主任
            $fillarrears = new P_Fills_second();
            $fillarrears->set_custom_where(" and play_price='$dbpayment_amount' and headmaster='$headmasterdb' and qq='$dbqq' and platform_rception='$dbplatform_sales'");
            $result = Model::query_list($db, $fillarrears, NULL, true);
            if (!$result[0])
                die_error(USER_ERROR, '该条数据，在二销补款中不存在，建议删除，重新录入！');
            $models = Model::list_to_array($result['models']);
            $final_id = $models[0]['id'];
            $fillarrears = new P_Fills_second();
            $fillarrears->set_id($final_id);
            $fillarrears->set_add_time($generationOperationData->add_time);
            $fillarrears->set_qq($generationOperationData->qq);
            $fillarrears->set_platform_num($generationOperationData->platform_num);
            $fillarrears->set_platform_rception($generationOperationData->platform_sales);
            $fillarrears->set_platform_rception_id($generationOperationData->platform_sales_id);
            $fillarrears->set_headmaster($generationOperationData->headmaster);
            $fillarrears->set_headmaster_id($generationOperationData->headmaster_id);
            $fillarrears->set_customer($generationOperationData->customer);
            $fillarrears->set_customer_id($generationOperationData->customer_id);
            $fillarrears->set_remark($generationOperationData->remark);
            $fillarrears->set_payment_method($generationOperationData->payment_method);
            $fillarrears->set_play_price($generationOperationData->payment_amount);
            $fillarrears->set_pay_user($generationOperationData->pay_user);
            $fillarrears->set_rcept_account($generationOperationData->rcept_account);
            $fillarrears->set_payment_method($generationOperationData->payment_method);
            $fillarrears->set_transfer_time($generationOperationData->transfer_time);
            $fillarrears->set_second_type('代运营补欠款');

            pdo_transaction($db, function($db)use($db, $generationOperation, $fillarrears) {
                $result = $generationOperation->update($db, true);
                if (!$result[0])
                    die_error(USER_ERROR, '保存代运营补款业绩失败!');
                $result = $fillarrears->update($db, true);
                if (!$result[0])
                    die_error(USER_ERROR, '保存代运营补款业绩失败~');
            });
            echo_msg('修改代运营业绩成功');
        }
        $ary_payment_amount = $generationOperationData->payment_amount;
        $ary_rcept_account = $generationOperationData->rcept_account_each;
        $ary_transfer_time = $generationOperationData->transfer_time;
        $ary_pay_rate = $generationOperationData->pay_rate_each;
        $ary_pay_user = $generationOperationData->pay_user;
        $ary_payment_method = $generationOperationData->payment_method_each;
        $delArrayId = $generationOperationData->delArrayId;
        $ary_recid = $generationOperationData->recid;
        $o_ary_payment_amount=$generationOperationData->o_payment_amount;
        $o_ary_pay_rate=$generationOperationData->o_pay_rate_each;
        if (!empty($headmaster) && $paytpye == 0 && $headmaster !== $headmasterdb) {
            $generationOperation->set_allot_time('now');
        }
        pdo_transaction($db, function($db)use($db, $id, $ary_recid,$o_ary_payment_amount,$o_ary_pay_rate,$generationOperation, $generationOperationData, $dbqq, $ary_payment_amount, $ary_rcept_account, $ary_transfer_time, $ary_pay_rate, $ary_pay_user, $ary_payment_method, $delArrayId) {
            $payment_amount = 0;
            if (count($ary_payment_amount) == 1) {
                $payment_amount += $ary_payment_amount - ($ary_payment_amount * $ary_pay_rate);
            } else {
                for ($i = 0; $i < count($ary_payment_amount); $i++) {
                    $payment_amount+=$ary_payment_amount[$i] - ($ary_payment_amount[$i] * $ary_pay_rate[$i]);
                };
            }
            //计算之前的付款金额
            if (count($o_ary_payment_amount) == 1) {
                $payment_amount += $o_ary_payment_amount - ($o_ary_payment_amount * $o_ary_pay_rate);
            } else {
                for ($i = 0; $i < count($o_ary_payment_amount); $i++) {
                    $payment_amount+=$o_ary_payment_amount[$i] - ($o_ary_payment_amount[$i] * $o_ary_pay_rate[$i]);
                };
            }
            $generationOperation->set_payment_amount($payment_amount);
            $result = $generationOperation->update($db, true);
            if (!$result[0])
                die_error(USER_ERROR, '保存代运营业绩失败');
            $qq = $generationOperationData->qq;
            $platform_num = $generationOperationData->platform_num;
            $platform_sales = $generationOperationData->platform_sales;
            $platform_sales_id = $generationOperationData->platform_sales_id;
            $headmaster = $generationOperationData->headmaster;
            $headmaster_id = $generationOperationData->headmaster_id;
            $customer = $generationOperationData->customer;
            $customer_id = $generationOperationData->customer_id;
            if ($generationOperationData->customer_type == 1)
                $customer_type = '优程';
            else
                $customer_type = '领远';
            $sales_numbers = $generationOperationData->sales_numbers;
            $sql = "update p_generationoperation set qq='$qq',platform_num='$platform_num',platform_sales='$platform_sales',platform_sales_id='$platform_sales_id',headmaster='$headmaster',headmaster_id='$headmaster_id',customer='$customer',customer_id='$customer_id',customer_type='$customer_type',sales_numbers='$sales_numbers' where payType<>1 and qq='$dbqq'";
            $result = $db->exec($sql);
            if ($result === FALSE)
                die_error(USER_ERROR, '保存代运营业绩失败');

            for ($i = 0; $i < count($ary_payment_amount); $i++) {
                if (count($ary_payment_amount) == 1) {
                    $pay_money = $ary_payment_amount;
                    $pay_account_type = $ary_rcept_account;
                    $transfer_time = $ary_transfer_time;
                    $pay_username = $ary_pay_user;
                    $pay_rate = $ary_pay_rate;
                    $payment_method = $ary_payment_method;
                    $recid = $ary_recid;
                } else {
                    $pay_money = $ary_payment_amount[$i];
                    $pay_account_type = $ary_rcept_account[$i];
                    $transfer_time = $ary_transfer_time[$i];
                    $pay_username = $ary_pay_user[$i];
                    $pay_rate = $ary_pay_rate[$i];
                    $payment_method = $ary_payment_method[$i];
                    $recid = $ary_recid[$i];
                }
                $receiver = new receiver_payaccount();
                $receiver->set_pay_account_type($pay_account_type);
                $receiver->set_pay_money($pay_money - ($pay_money * $pay_rate));
                $receiver->set_transfer_time($transfer_time);
                $receiver->set_pay_username($pay_username);
                $receiver->set_add_user(request_login_username());
                $receiver->set_pay_rate($pay_rate);
                $receiver->set_platform_sales_id($generationOperationData->platform_sales_id);
                $receiver->set_platform_sales($generationOperationData->platform_sales);
                $receiver->set_payment_method($payment_method);
                if ($recid ==0) {
                    $receiver->set_general_operal_id($id);
                    $receiver->set_add_time('now');
                    $receiver->set_approve_status(1);
                    $result = $receiver->insert($db);
                    if (!$result[0])
                        die_error(USER_ERROR, '插入代运营子记录失败~');
                }else {
                    $receiver->set_id($recid);
                    $result = $receiver->update($db);
                    if (!$result[0])
                        die_error(USER_ERROR, '修改代运营子记录失败~');
                }
            }
            $sql = "delete from receiver_payaccount where id in ($delArrayId)";
            if (str_length($delArrayId) > 0) {
                $result = $db->exec($sql);
                if ($result === FALSE)
                    die_error(USER_ERROR, '修改代运营子记录失败!');
            }
        });

        if (!empty($headmaster) && $paytpye == 0 && $headmaster !== $headmasterdb) {
            //推送消息
            $postmsg = new postMsgClass();
            $postmsg->msgtype = msgType::callteacher; //指派班主任
            $postmsg->userid = $generationOperationData->headmaster_id; //id
            $postmsg->username = $generationOperationData->headmaster; //用户名兼班主任
            $postmsg->clientinfo = $generationOperationData->qq; //qq
            $postmsg->mobile = $generationOperationData->platform_num; //手机号
            $postmsg->money = $generationOperationData->payment_amount + $generationOperationData->final_money; //接待金额
            $postmsg->final_money = $generationOperationData->payment_amount; //付款金额
            $postmsg->receiveruser = $generationOperationData->platform_sales; //平台销售
            $postmsg->payment_method = $generationOperationData->payment_method; //支付方式
            $postmsg->content = $generationOperationData->remark; //备注
            $msg = json_encode($postmsg);
            $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
            if ($result != 'True') {
                echo_msg("修改代运营业绩成功,推送消息失败~");
            }
        }
        echo_msg('修改代运营业绩成功');
    }
    //查找子表
    if ($action == 9) {
        $gen_id = $generationOperationData->id;
        $rec = new receiver_payaccount();
        $rec->set_custom_where("and general_operal_id=$gen_id");
        $result = Model::query_list($db, $rec, NULL, true);
        if (!$result[0])
            die_error(USER_ERROR, '获取数据失败，请重试');
        $models = Model::list_to_array($result['models']);
        $result = array("list" => $models, "code" => 0);
        exit(get_response($result));
    }
    //批量删除
    if ($action == 10) {
        $id = $_POST['id'];
        $sql = "delete from p_generationoperation where id in($id)";
        $db = create_pdo();
        $result = $db->exec($sql);
        if (!$result)
            die_error(USER_ERROR, '删除数据失败，请稍后重新!');
        echo_msg('删除数据成功');
    }
});
