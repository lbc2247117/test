<?php

/**
 * 投诉记录
 *
 * @author B.Maru
 * @copyright 2015 星密码
 * @version 2015/10/25
 */
use Models\Base\Model;
use Models\p_complainchat;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../Common/http.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    if ($action == 1) {
        $searchName = request_string('searchName');
        $complaint_record = new p_complainchat();
        if (isset($searchName)) {
            $complaint_record->set_custom_where(" AND (customerQQ like '%" . $searchName . "%') OR teacherName like '%" . $searchName . "%' OR exceptionDetail like '%" . $searchName . "%' ");
        }
        $field = array('id', 'chatTime', 'teacherName', 'customerQQ', 'departName', 'exceptionDetail', 'proType', 'dealResult', 'isDeal', 'chatUser');
        $complaint_record->set_query_fields($field);
        $complaint_record->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $complaint_record, NULL, true);
        if (!$result[0]) {
            die_error(USER_ERROR, '获取投诉记录失败，请重试');
        }
        $employees = get_employees();
        $models = Model::list_to_array($result['models'], array(), function(&$d) use($employees) {
                    $d['chatUser'] = $employees[$d['chatUser']]['username'];
                });
        echo_list_result($result, $models);
    }
    if ($action == 11) {
        $export = new ExportData2Excel();
        $complaint_record = new p_complainchat();
        $startTime = request_datetime("start_time");
        $endTime = request_datetime("end_time");
        if (isset($startTime)) {
            $complaint_record->set_custom_where(" and DATE_FORMAT(chatTime, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $complaint_record->set_custom_where(" and DATE_FORMAT(chatTime, '%Y-%m-%d') <= '" . $endTime . "' ");
        }

        $field = array('chatTime', 'teacherName', 'customerQQ', 'departName', 'exceptionDetail', 'proType', 'isDeal', 'dealResult', 'refundMoney', 'chatUser', 'dealUser');
        $complaint_record->set_query_fields($field);
        $db = create_pdo();
        $result = Model::query_list($db, $complaint_record, NULL, true);
        if (!$result[0]) {
            $export->create(array('导出错误'), array(array('抽查聊天记录导出失败,请稍后重试!')), "抽查聊天记录导出", "抽查聊天记录");
        }
        $employees = get_employees();
        $models = Model::list_to_array($result['models'], array(), function(&$d) use($employees) {
                    $d['chatUser'] = $employees[$d['chatUser']]['username'];
                    $d['dealUser'] = $employees[$d['dealUser']]['username'];
                });
        $title_array = array('抽查时间', '抽查班主任', '客户QQ', '归属部门', '异常情况', '问题类型', '是否处理', '处理结果', '退款金额', '抽查人员', '处理人员');
        $export->set_field($field);
        $export->create($title_array, $models, "抽查聊天记录导出", "抽查聊天记录");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $complaintRecordData = request_object();
    /**
     * 添加
     */
    if ($action == 1) {
        $complaint_record = new p_complainchat();
        
        $complaintRecordData->isDeal = "未处理";
        if ($complaintRecordData->proType == "无异常") {
            $complaintRecordData->isDeal = "无需处理";
            $complaintRecordData->dealResult = "无异常";
            $complaintRecordData->exceptionDetail = "无异常";
        } 
        $complaint_record->set_field_from_array($complaintRecordData);
        $complaint_record->set_chatUser(request_login_userid());

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
        $complaint_record = new p_complainchat($complaintRecordData->id);
        $db = create_pdo();
        $result = $complaint_record->delete($db);
        if (!$result[0])
            die_error(USER_ERROR, '删除抽查聊天记录失败~');
        echo_msg('删除抽查聊天记录成功~');
    }

    /**
     * 修改
     */
    if ($action == 3) {
        $complaint_record = new p_complainchat($complaintRecordData->id);
        $complaintRecordData->chatUser = request_userid();
        $complaint_record->set_field_from_array($complaintRecordData);

        $db = create_pdo();
        $result = $complaint_record->update($db, TRUE);
        if (!$result[0])
            throw new TransactionException(PDO_ERROR_CODE, '修改抽查聊天记录失败~' . $result['detail_cn'], $result);
        echo_msg('修改抽查聊天记录成功~');
    }

    /**
     * 处理问题
     */
    if ($action == 4) {
        $complaint_record = new p_complainchat($complaintRecordData->id);
        $complaintRecordData->dealUser = request_userid();
        $complaintRecordData->isDeal = "已处理";
        $complaint_record->set_field_from_array($complaintRecordData);

        $db = create_pdo();
        $result = $complaint_record->update($db, TRUE);
        if (!$result[0])
            throw new TransactionException(PDO_ERROR_CODE, '处理抽查记录记录失败~' . $result['detail_cn'], $result);

        echo_msg('处理抽查记录记录成功~');
    }
});
