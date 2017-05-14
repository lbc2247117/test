<?php

/**
 * 投诉记录
 *
 * @author B.Maru
 * @copyright 2015 星密码
 * @version 2015/10/25
 */
use Models\Base\Model;
use Models\p_complainvisit;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../Common/http.php';
require '../../Models/postMsgClass.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    if ($action == 1) {
        $searchName = request_string('searchName');
        $complaint_record = new p_complainvisit();
        if (isset($searchName)) {
            $complaint_record->set_custom_where(" AND (customerQQ like '%" . $searchName . "%') OR customerPhone like '%" . $searchName . "%' OR reflectPro like '%" . $searchName . "%' ");
        }
        $field = array('id', 'visitTime', 'visitLevel', 'customerQQ', 'customerPhone', 'departName', 'reflectPro','isDeal', 'dealResult', 'addUser', 'visitUser');
        $complaint_record->set_query_fields($field);
        $complaint_record->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $complaint_record, NULL, true);
        if (!$result[0]) {
            die_error(USER_ERROR, '获取回访记录失败，请重试');
        }
        $employees = get_employees();
        $models = Model::list_to_array($result['models'], array(), function(&$d) use($employees) {
                    $d['visitUser'] = $employees[$d['visitUser']]['username'];
//                    $d['visitUser'] = $employees[$d['visitUser']]['username'];
                });
        echo_list_result($result, $models);
    }
    if ($action == 11) {
        $export = new ExportData2Excel();
        $complaint_record = new p_complainvisit();
        $startTime = request_datetime("start_time");
        $endTime = request_datetime("end_time");
        if (isset($startTime)) {
            $complaint_record->set_custom_where(" and DATE_FORMAT(visitTime, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $complaint_record->set_custom_where(" and DATE_FORMAT(visitTime, '%Y-%m-%d') <= '" . $endTime . "' ");
        }

        $field = array('visitTime', 'visitLevel', 'customerQQ', 'customerPhone', 'departName', 'reflectPro', 'dealResult', 'addUser', 'visitUser');
        $complaint_record->set_query_fields($field);
        $db = create_pdo();
        $result = Model::query_list($db, $complaint_record, NULL, true);
        if (!$result[0]) {
            $export->create(array('导出错误'), array(array('回访记录导出失败,请稍后重试!')), "回访记录导出", "回访记录");
        }
        $employees = get_employees();
        $models = Model::list_to_array($result['models'], array(), function(&$d) use($employees) {
                    $d['visitUser'] = $employees[$d['visitUser']]['username'];
                    $d['addUser'] = $employees[$d['addUser']]['username'];
                });
        $title_array = array('回访时间', '回访级别', '客户QQ', '客户电话', '归属部门', '反映问题', '处理结果', '添加记录人员', '回访人员');
        $export->set_field($field);
        $export->create($title_array, $models, "回访记录导出", "回访记录");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $complaintRecordData = request_object();
    /**
     * 添加
     */
    if ($action == 1) {
        $complaint_record = new p_complainvisit();

        if ($complaintRecordData->reflectPro == "正常") {
            $newResult = "无需处理";
            $complaintRecordData->dealResult = "回访情况正常";
        } else {
            $newResult = "未处理";
        }
        $complaintRecordData->isDeal = $newResult;
        $complaint_record->set_field_from_array($complaintRecordData);
        $complaint_record->set_addUser(request_login_userid());

        $db = create_pdo();
        $result = $complaint_record->insert($db);
        if (!$result[0])
            die_error(USER_ERROR, '添加回访记录失败~');
//        add_data_add_log($db, $complaintRecordData, $complaint_record, 11);
        echo_msg('添加回访记录成功~');
    }

    /**
     * 删除
     */
    if ($action == 2) {
        $complaint_record = new p_complainvisit($complaintRecordData->id);
        $db = create_pdo();
        $result = $complaint_record->delete($db);
        if (!$result[0])
            die_error(USER_ERROR, '删除回访记录失败~');
        echo_msg('删除回访记录成功~');
    }

    /**
     * 修改
     */
    if ($action == 3) {
        $complaint_record = new p_complainvisit($complaintRecordData->id);
        $complaintRecordData->visitUser = request_userid();
        $complaint_record->set_field_from_array($complaintRecordData);

        $db = create_pdo();
        $result = $complaint_record->update($db, TRUE);
        if (!$result[0])
            throw new TransactionException(PDO_ERROR_CODE, '修改回访记录失败~' . $result['detail_cn'], $result);
        echo_msg('修改回访记录成功~');
    }

    /**
     * 处理问题
     */
    if ($action == 4) {
        $complaint_record = new p_complainvisit($complaintRecordData->id);
        $complaintRecordData->dealUser = request_userid();
        $complaintRecordData->isDeal = "已处理";
        $complaint_record->set_field_from_array($complaintRecordData);

        $db = create_pdo();
        $result = $complaint_record->update($db, TRUE);
        if (!$result[0])
            throw new TransactionException(PDO_ERROR_CODE, '处理抽查记录失败~' . $result['detail_cn'], $result);

        $post = new postMsgClass();
        $post->msgtype = msgType::reception_complain;
        $post->userid = $complaintRecordData->userid;
        $post->username = get_employees()[$complaintRecordData->userid]["username"];
        $post->clientinfo = $complaintRecordData->clientinfo;
        $post->mobile = $complaintRecordData->mobile;

        $msg = json_encode($post);
        $resultLast = curl_http_post(PUSH_MESSAGE_URL, $msg);
        
        echo_msg('处理抽查记录成功~');
    }
    
    /**
     * 满意度调查
     */
    if ($action == 5) {
        $complaint_record = new p_complainvisit($complaintRecordData->id);
        $complaintRecordData->isDeal = "已二次回访";
        $complaintRecordData->id = $complaintRecordData->secondVisitId;
        $complaint_record->set_field_from_array($complaintRecordData);

        $db = create_pdo();
        $result = $complaint_record->update($db, TRUE);
        if (!$result[0])
            throw new TransactionException(PDO_ERROR_CODE, '满意度调查记录失败~' . $result['detail_cn'], $result);

        echo_msg('满意度调查记录成功~');
    }
});
