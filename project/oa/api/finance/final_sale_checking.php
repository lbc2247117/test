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
use Models\st_final_sale;
use Models\P_Fills_second;

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
        $sql_pre = "select id,add_time,customer_type,qq,headmaster,customer,platform_sales,payment_amount,payment_method,rcept_account,pay_user,st_is_approve from p_generationoperation where payType = 1 and st_is_approve > 0 ";
        $sql_desc = ' ORDER BY st_is_approve ASC,add_time ASC';

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
            $sql_pre.=" and st_is_approve = $review";
        }
        if (!isset($curTime))
            $curTime = "1";
        if (isset($searchTime))
            $curTime = $searchTime;
        if ($curTime != "1") {
            $sql_pre.=" and  DATE_FORMAT(add_time, '%Y-%m-%d')='$curTime'";
        }
        if (isset($payType)) {
            if ($payType != '全部')
                $sql_pre.=" and payment_method ='$payType'";
        }
        if (isset($account)) {
            if ($account != '全部')
                $sql_pre.=" and rcept_account like '%" . $account . "%'";
        }
        if (isset($workName)) {
            $sql_pre.=" AND (payment_amount LIKE '%" . $workName . "%' OR qq LIKE '%" . $workName . "%' OR platform_sales LIKE '%" . $workName . "%' OR customer LIKE '%" . $workName . "%' OR payment_method LIKE '%" . $workName . "%' OR rcept_account LIKE '%" . $workName . "%' OR  pay_user LIKE '%" . $workName . "%') ";
        }
        $sql_pre .= $sql_desc;

        $result = $db->query($sql_pre);
        $total = $result->rowCount();
        $sql_pre.=' limit ' . request_pagesize() * (request_pageno() - 1) . ',' . request_pagesize();


        $result = $db->query($sql_pre);
        $models = $result->fetchAll(PDO::FETCH_ASSOC);
        $totalmoney = 0;
        foreach ($models as $row) {
            $totalmoney += $row['payment_amount'];
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

        $sql_pre = "select id,add_time,customer_type,qq,headmaster,customer,platform_sales,payment_amount,payment_method,rcept_account,pay_user,st_is_approve from p_generationoperation where payType = 1 and st_is_approve > 0 ";
        if (isset($startTime)) {
            $sql_pre.=" and DATE_FORMAT(add_time, '%Y-%m-%d') >=DATE_FORMAT('$startTime', '%Y-%m-%d')";
        }
        if (isset($endTime)) {
            $sql_pre.=" and DATE_FORMAT(add_time, '%Y-%m-%d') <=DATE_FORMAT('$endTime', '%Y-%m-%d')";
        }
        $sql_pre.=' ORDER BY st_is_approve ASC,add_time ASC';
        $db = create_pdo();
        $result = $db->query($sql_pre);
        $models = $result->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($models); $i++) {
            if ($models[$i]['st_is_approve'] == 1)
                $models[$i]['st_is_approve'] = '未审核';
            if ($models[$i]['st_is_approve'] == 2)
                $models[$i]['st_is_approve'] = '已审核';
            if ($models[$i]['st_is_approve'] == 3)
                $models[$i]['st_is_approve'] = '已驳回';
        }

        $title_array = array('编号', '添加时间', '客户来源', 'QQ', '班主任', '售后', '平台接待', '金额', '付款方式', '收款账号', '付款时间', '是否审核');
        $expolt->create($title_array, $models, "补款对账数据导出", "补款对账");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $generationOperationData = request_object();
    $id_arr = $generationOperationData->id;
    $db = create_pdo();

    $username = request_login_username();
    $gen = new P_GenerationOperation();
    $searchDate = $generationOperationData->add_time;
    //财务审核
    if ($action == 1) {
        for ($i = 0; $i < count($id_arr); $i++) {
            $id = $id_arr[$i];
            $re = new P_GenerationOperation($id);
            $result = $re->load($db, $re);
            if (!$result[0])
                die_error(USER_ERROR, '获取数据失败,请刷新页面重新尝试~');
            if ($re->get_st_is_approve() == 2)
                die_error(USER_ERROR, '该条数据已经审核，请刷新页面查看');

            $add_time = $re->get_add_time();
            $newadd_time = $add_time->format('Y-m-d');
            //在P_Fills_Second表去更新数据
            $pFillsSecond = new P_Fills_second();
            $pFillsSecond->set_custom_where(" and qq = '" . $re->get_qq() . "' ");
            $pFillsSecond->set_custom_where(" and play_price = '" . $re->get_payment_amount() . "' ");
            $pFillsSecond->set_custom_where(" and headmaster = '" . $re->get_headmaster() . "' ");
            $pFillsSecond->set_custom_where(" and second_type = '代运营补欠款' ");

            $pFillsSecond->set_query_fields(array('id', "st_is_approve"));
            $db = create_pdo();
            $resultFill = Model::query_list($db, $pFillsSecond, NULL, true);
            $models = Model::list_to_array($resultFill['models'], array(), function(&$d) {
                        
                    });
            if (!$models[0])
                die_error(USER_ERROR, '获取数据失败,请刷新页面重新尝试~');
            if ($models[0]["st_is_approve"] == 2)
                die_error(USER_ERROR, '该条同步数据已经审核，请刷新页面查看');

            $fillId = $models[0]["id"];

            $stFinalSale = new st_final_sale();

            $stFinalSale->set_addtime($newadd_time);
            $stFinalSale->set_qq($re->get_qq());
            $stFinalSale->set_gen_id($id);
            $stFinalSale->set_seller_front($re->get_platform_sales());
            $stFinalSale->set_money($re->get_payment_amount());
            $stFinalSale->set_rcept_account($re->get_rcept_account());
            $stFinalSale->set_pay_user($re->get_pay_user());
            $stFinalSale->set_customer_type($re->get_customer_type());
            $stFinalSale->set_customer($re->get_customer());
            $stFinalSale->set_customer_id($re->get_customer_id());
            $stFinalSale->set_remark('财务已审核');
            $stFinalSale->set_headmaster($re->get_headmaster());
            $stFinalSale->set_compare_user(request_username());

            $re->reset();
            $re->set_id($id);
            $re->set_st_is_approve(2);

            $pFillsSecond->reset();
            $pFillsSecond->set_id($fillId);
            $pFillsSecond->set_st_is_approve(2);

            pdo_transaction($db, function($db)use($db, $stFinalSale, $re, $pFillsSecond) {
                $resultFinal = $stFinalSale->insert($db);
                if (!$resultFinal[0])
                    die_error(USER_ERROR, '审核数据失败');
                $resultSecond = $pFillsSecond->update($db);
                if (!$resultSecond[0])
                    die_error(USER_ERROR, '审核数据失败~');
                $resultRe = $re->update($db);
                if (!$resultRe[0])
                    die_error(USER_ERROR, '审核数据失败~');
            });
        }
        $gen = new P_GenerationOperation();
        $gen->set_custom_where(' AND (st_is_approve=1 or st_is_approve=3)');
        $gen->set_custom_where(' AND payType = 1');
        $gen->set_custom_where(" AND DATE_FORMAT(add_time, '%Y-%m-%d')= DATE_FORMAT('$searchDate', '%Y-%m-%d')");
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
        $gen = new P_GenerationOperation($id_arr[0]);
        $result = $gen->load($db, $gen);
        if (!$result[0])
            die_error(USER_ERROR, '获取表信息失败~');
        if ($gen->get_st_is_approve() == 1)
            die_error(USER_ERROR, '该数据已经处于未审核状态，请不要重复反审');

        //在P_Fills_Second表去更新数据
        $pFillsSecond = new P_Fills_second();
        $pFillsSecond->set_custom_where(" and qq = '" . $gen->get_qq() . "' ");
        $pFillsSecond->set_custom_where(" and play_price = '" . $gen->get_payment_amount() . "' ");
        $pFillsSecond->set_custom_where(" and headmaster = '" . $gen->get_headmaster() . "' ");
        $pFillsSecond->set_custom_where(" and second_type = '代运营补欠款' ");

        $pFillsSecond->set_query_fields(array('id', "st_is_approve"));
        $db = create_pdo();
        $resultFill = Model::query_list($db, $pFillsSecond, NULL, true);
        $models = Model::list_to_array($resultFill['models'], array(), function(&$d) {
                    
                });
        if (!$models[0])
            die_error(USER_ERROR, '获取数据失败,请刷新页面重新尝试~');
        if ($models[0]["st_is_approve"] == 1)
            die_error(USER_ERROR, '该条同步数据已经处于未审核状态，请不要重复反审');

        $fillId = $models[0]["id"];

        $gen->reset();
        $gen->set_id($id_arr[0]);
        $gen->set_st_is_approve(1);

        $sql_del = 'delete from st_final_sale where gen_id = ' . $id_arr[0];

        $pFillsSecond->reset();
        $pFillsSecond->set_id($fillId);
        $pFillsSecond->set_st_is_approve(1);

        pdo_transaction($db, function($db)use($db, $gen, $sql_del, $pFillsSecond) {
            $result = $pFillsSecond->update($db);
            if (!$result[0])
                die_error(USER_ERROR, '反审核失败~~');
            $result = $gen->update($db);
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
        $gen = new P_GenerationOperation($id_arr[0]);
        $result = $gen->load($db, $gen);
        if (!$result[0])
            die_error(USER_ERROR, '获取数据失败~');
        $rmk = $gen->get_remark();

        //在P_Fills_Second表去更新数据
        $pFillsSecond = new P_Fills_second();
        $pFillsSecond->set_custom_where(" and qq = '" . $gen->get_qq() . "' ");
        $pFillsSecond->set_custom_where(" and play_price = '" . $gen->get_payment_amount() . "' ");
        $pFillsSecond->set_custom_where(" and headmaster = '" . $gen->get_headmaster() . "' ");
        $pFillsSecond->set_custom_where(" and second_type = '代运营补欠款' ");

        $pFillsSecond->set_query_fields(array('id', "st_is_approve"));
        $db = create_pdo();
        $resultFill = Model::query_list($db, $pFillsSecond, NULL, true);
        $models = Model::list_to_array($resultFill['models'], array(), function(&$d) {
                    
                });
        if (!$models[0])
            die_error(USER_ERROR, '获取数据失败,请刷新页面重新尝试~');
        $fillId = $models[0]["id"];

        $gen->reset();
        $gen->set_id($id_arr[0]);
        if (!empty($rmk))
            $remark = $rmk . '|' . $remark;
        $gen->set_remark($remark);
        $gen->set_st_is_approve(3);

        $pFillsSecond->reset();
        $pFillsSecond->set_id($fillId);
        $pFillsSecond->set_st_is_approve(3);

        pdo_transaction($db, function($db)use($db, $gen, $pFillsSecond) {
            $result1 = $gen->update($db);
            if (!$result1[0])
                die_error(USER_ERROR, '驳回失败~~');

            $result1 = $pFillsSecond->update($db);
            if (!$result1[0])
                die_error(USER_ERROR, '驳回失败~~');
        });
        echo_msg('驳回成功');
    }
    if ($action == 4) { //取消驳回
        $gen = new P_GenerationOperation($id_arr);
        $result = $gen->load($db, $gen);
        if (!$result[0])
            die_error(USER_ERROR, "获取表数据失败~");
        //在P_Fills_Second表去更新数据
        $pFillsSecond = new P_Fills_second();
        $pFillsSecond->set_custom_where(" and qq = '" . $gen->get_qq() . "' ");
        $pFillsSecond->set_custom_where(" and play_price = '" . $gen->get_payment_amount() . "' ");
        $pFillsSecond->set_custom_where(" and headmaster = '" . $gen->get_headmaster() . "' ");
        $pFillsSecond->set_custom_where(" and second_type = '代运营补欠款' ");

        $pFillsSecond->set_query_fields(array('id', "st_is_approve"));
        $db = create_pdo();
        $resultFill = Model::query_list($db, $pFillsSecond, NULL, true);
        $models = Model::list_to_array($resultFill['models'], array(), function(&$d) {
                    
                });
        if (!$models[0])
            die_error(USER_ERROR, '获取数据失败,请刷新页面重新尝试~');
        $fillId = $models[0]["id"];

        $gen->reset();
        $gen->set_id($id_arr);
        $gen->set_st_is_approve(1);

        $pFillsSecond->reset();
        $pFillsSecond->set_id($fillId);
        $pFillsSecond->set_st_is_approve(1);

        pdo_transaction($db, function($db)use($db, $gen, $pFillsSecond) {
            $result1 = $gen->update($db);
            if (!$result1[0])
                die_error(USER_ERROR, '取消驳回失败~~');

            $result1 = $pFillsSecond->update($db);
            if (!$result1[0])
                die_error(USER_ERROR, '取消驳回失败~~');
        });

        echo_msg('操作成功');
    }
});

