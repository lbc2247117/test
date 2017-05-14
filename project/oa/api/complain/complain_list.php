<?php

/**
 * 投诉记录
 *
 * @author B.Maru
 * @copyright 2015 星密码
 * @version 2015/10/25
 */
use Models\Base\Model;
use Models\p_complaint_record;
use Models\Base\SqlOperator;
use Models\Base\SqlSortType;
use Models\p_complain;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../Common/http.php';
require '../../Models/postMsgClass.php';


$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    if ($action == 1) {
        $searchName = request_string('searchName');
        $complaint_record = new p_complain();
        if (isset($searchName)) {
            $complaint_record->set_custom_where(" AND (customerQQ like '%" . $searchName . "%') OR teacherName like '%" . $searchName . "%' OR customerPhone like '%" . $searchName . "%' ");
        }
        $field = array('id', 'receiveTime', 'customerPhone', 'customerQQ', 'teacherName', 'departName','flowType', 'proType', 'anotherDetail', 'dealResult', 'isDeal', 'refundMoney', 'addUser', 'isPleased');
        $complaint_record->set_query_fields($field);
        $complaint_record->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $complaint_record, NULL, true);
        if (!$result[0]) {
            die_error(USER_ERROR, '获取投诉记录失败，请重试');
        }
        $employees = get_employees();
        $models = Model::list_to_array($result['models'], array(), function(&$d) use($employees) {
                    $d['addUser'] = $employees[$d['addUser']]['username'];
                });
        echo_list_result($result, $models);
    }
    if ($action == 11) {
        $export = new ExportData2Excel();
        $complaint_record = new p_complain();
        $startTime = request_datetime("start_time");
        $endTime = request_datetime("end_time");
        if (isset($startTime)) {
            $complaint_record->set_custom_where(" and DATE_FORMAT(receiveTime, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $complaint_record->set_custom_where(" and DATE_FORMAT(receiveTime, '%Y-%m-%d') <= '" . $endTime . "' ");
        }

        $field = array('receiveTime', 'customerPhone', 'customerQQ', 'teacherName', 'complainPro', 'dealResult', 'isDeal', 'refundMoney', 'proType', 'dealUser');
        $complaint_record->set_query_fields($field);
        $db = create_pdo();
        $result = Model::query_list($db, $complaint_record, NULL, true);
        if (!$result[0]) {
            $export->create(array('导出错误'), array(array('投诉记录导出失败,请稍后重试!')), "投诉记录导出", "投诉记录");
        }
        $employees = get_employees();
        $models = Model::list_to_array($result['models'], array(), function(&$d) use($employees) {
                    $d['dealUser'] = $employees[$d['dealUser']]['username'];
                });
        $title_array = array('添加时间', '客户电话', '客户QQ', '老师姓名', '投诉问题', '处理结果', '是否处理', '退款金额', '问题类型', '添加人员');
        $export->set_field($field);
        $export->create($title_array, $models, "投诉记录导出", "投诉记录");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $complaintRecordData = request_object();
    /**
     * 添加
     */
    if ($action == 1) {
        $complaint_record = new p_complain();

        if ($complaintRecordData->proType == "其他") {
            $complaintRecordData->anotherDetail = $complaintRecordData->anotherPro;
        }
        $complaintRecordData->isDeal = "未处理";
        if ($complaintRecordData->proType == "咨询") {
            $complaintRecordData->dealResult = "咨询";
            $complaintRecordData->isDeal = "无需处理";
            $complaintRecordData->isPleased = "满意";
        }
        $complaint_record->set_field_from_array($complaintRecordData);
        $complaint_record->set_addUser(request_login_userid());

        $db = create_pdo();
        $result = $complaint_record->insert($db);
        if (!$result[0])
            die_error(USER_ERROR, '添加投诉记录失败~');
//        add_data_add_log($db, $complaintRecordData, $complaint_record, 11);
        echo_msg('添加投诉记录成功~');
    }

    /**
     * 删除
     */
    if ($action == 2) {
        $complaint_record = new p_complain($complaintRecordData->id);
        $db = create_pdo();
        $result = $complaint_record->delete($db);
        if (!$result[0])
            die_error(USER_ERROR, '删除投诉记录失败~');
        echo_msg('删除投诉记录成功~');
    }

    /**
     * 修改
     */
    if ($action == 3) {
        $complaint_record = new p_complain($complaintRecordData->id);
        $complaintRecordData->addUser = request_userid();
        $complaint_record->set_field_from_array($complaintRecordData);

        $db = create_pdo();
        pdo_transaction($db, function($db) use($complaint_record, $complaintRecordData) {
            add_data_change_log($db, $complaintRecordData, $complaint_record, 11);
            $result = $complaint_record->update($db, TRUE);
            if (!$result[0])
                throw new TransactionException(PDO_ERROR_CODE, '修改投诉记录失败~' . $result['detail_cn'], $result);
        });
        echo_msg('修改投诉记录成功~');
    }

    /**
     * 处理问题
     */
    if ($action == 4) {
        $complaint_record = new p_complain($complaintRecordData->id);
        $complaintRecordData->dealUser = request_userid();
        $complaintRecordData->isDeal = "已处理";
        $complaint_record->set_field_from_array($complaintRecordData);

        $db = create_pdo();
        $result = $complaint_record->update($db, TRUE);
        if (!$result[0])
            throw new TransactionException(PDO_ERROR_CODE, '处理投诉记录失败~' . $result['detail_cn'], $result);

        $post = new postMsgClass();
        $post->msgtype = msgType::complain_deal;
        $post->userid = $complaintRecordData->userid;
        $post->username = get_employees()[$complaintRecordData->userid]["username"];
        $post->clientinfo = $complaintRecordData->clientinfo;
        $post->mobile = $complaintRecordData->mobile;

        $msg = json_encode($post);
        $resultLast = curl_http_post(PUSH_MESSAGE_URL, $msg);
        
        echo_msg('处理投诉记录成功~');
    }

    /**
     * 满意度调查
     */
    if ($action == 5) {
        $complaintRecordData->id = $complaintRecordData->secondId;
        $complaintRecordData->isDeal = "已二次回访";
        $complaint_record = new p_complain($complaintRecordData->id);
        $complaint_record->set_field_from_array($complaintRecordData);

        $db = create_pdo();
        $result = $complaint_record->update($db, TRUE);
        if (!$result[0])
            throw new TransactionException(PDO_ERROR_CODE, '二次回访添加满意度调查记录失败~' . $result['detail_cn'], $result);

        echo_msg('二次回访添加满意度调查记录成功~');
    }
});