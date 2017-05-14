<?php

/**
 * 行政处罚
 */
use Models\Base\Model;
use Models\Base\SqlSortType;
use Models\A_Penalty;
use Models\Base\SqlOperator;
use Models\S_WorkflowLog;

require '../../application.php';
require '../../loader-api.php';
require '../../common/http.php';
require '../../Models/postMsgClass.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    $deptid = request_int('deptid');
    $sort = request_string('sort');
    $sortname = request_string('sortname');
    $searchName = request_string('searchName');
    $searchDate = request_string('searchDate');
    $penalty = new A_Penalty();
    $role_type = get_roletype(request_login_userid());
    if (isset($searchDate)) {
        $penalty->set_where_and(A_Penalty::$field_addtime, SqlOperator::Between, array($searchDate . ' 0:00:00', $searchDate . ' 23:59:59'));
    }
    if (isset($deptid)) {
        $penalty->set_where_and(A_Penalty::$field_dept1_id, SqlOperator::Equals, $deptid);
        $penalty->set_where_or(A_Penalty::$field_dept2_id, SqlOperator::Equals, $deptid);
    }
    if (isset($searchName)) {
        $penalty->set_where_and(A_Penalty::$field_username, SqlOperator::Like, '%' . $searchName . '%');
    }
    if (isset($sort) && isset($sortname)) {
        $penalty->set_order_by($penalty->get_field_by_name($sortname), $sort);
    } else {
        $penalty->set_order_by(A_Penalty::$field_status, SqlSortType::Asc);
        $penalty->set_order_by(A_Penalty::$field_addtime, SqlSortType::Desc);
    }
    get_record_by_role($penalty);
    $penalty->set_limit_paged(request_pageno(), request_pagesize());
    $db = create_pdo();
    $result = Model::query_list($db, $penalty, NULL, true);
    if (!$result[0])
        die_error(USER_ERROR, '获取行政处罚资料失败，请重试');
    $models = Model::list_to_array($result['models'], array(), "id_2_text");
    $roletype = get_role_type();
    array_walk($models, function(&$model) use($roletype) {
        $workflow_configs = get_penalty_workflow($model);
        get_workflow($workflow_configs, $model, $roletype);
    });
    $page_no = request_pageno();
    $total = $result['total_count'];
    $max_page_no = ceil($total / request_pagesize());
    $result = array('role_type' => $role_type, 'total_count' => $total, "list" => $models, 'page_no' => $page_no, 'max_page_no' => $max_page_no, 'code' => 0);
    exit(get_response($result));
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $penaltyData = request_object();
    if ($action == 1) {
        $penaltyType = $penaltyData->penaltyType;
        $userid = $penaltyData->userid;
        $username = $penaltyData->username;
        $user_rcept = get_employees()[$userid];
        $depts_rcept = get_depts();
        $depName = $depts_rcept[$user_rcept['dept1_id']]['text'];
        $penalty = new A_Penalty();
        $penalty->set_field_from_array($penaltyData);
        $user = get_employees()[request_int('userid')];
        $penalty->set_dept1_id($user['dept1_id']);
        $penalty->set_dept2_id($user['dept2_id']);
        $penalty->set_addtime('now');
        $penalty->set_status(0);
        $db = create_pdo();
        $result = $penalty->insert($db);
        if (!$result[0])
            
        die_error(USER_ERROR, '添加行政处罚单失败~');
        $workflow_configs = get_penalty_workflow((array) $penaltyData);
        add_workflow_log($db, $penaltyData, $workflow_configs, $penalty);
        //PUSH_MESSAGE BEGIN
//        $msg = json_encode(array('Content ' => '   亲，你被处罚了RMB' . $penalty->get_money() . '元，请到OA里面认罚哦~', 'Title' => '处罚通知', 'UserName' => $penalty->get_username(), 'Type' => 11, 'IconName' => 'face_ww_tongku', 'Code' => 0, 'MsgType' => 10));
//        send_push_msg($msg, $penalty->get_userid());
    //PUSH_MESSAGE END

    $post = new postMsgClass();
    if ($penaltyType == 0)
        $post->msgtype = msgType::personel_kill;
    else {
        $post->departid = $user_rcept['dept1_id'];
        $post->msgtype = msgType::depart_kill;
    }
    $post->username = $username;
    $post->department = $depName;
    $post->money = $penaltyData->money;
    $post->content = $penaltyData->reason;
    $post->createtime = $penaltyData->addtime;
    $msg = json_encode($post);
    $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
    if ($result == 'True')
        echo_msg('添加行政处罚单成功~');
    else
        die_error(USER_ERROR, "添加行政处罚单成功，发送通知失败~");
}
if ($action == 2) {
    $penalty = new A_Penalty();
    $penalty->set_field_from_array($penaltyData);
    $workflow_configs = get_penalty_workflow((array) $penaltyData);
    $db = create_pdo();
    set_workflow_status($db, $penaltyData, $workflow_configs, $penalty);
    $result = $penalty->update($db, true);
    if (!$result[0])
        die_error(USER_ERROR, '保存行政处罚单失败~');
    //PUSH_MESSAGE BEGIN
    $workflow_log = new S_WorkflowLog();
    $workflow_log->set_query_fields(array(S_WorkflowLog::$field_userid));
    $workflow_log->set_where_and(S_WorkflowLog::$field_type, SqlOperator::Equals, 1);
    $workflow_log->set_where_and(S_WorkflowLog::$field_workflow_id, SqlOperator::Equals, $penalty->get_id());
    $workflow_log->set_where_and(S_WorkflowLog::$field_workflow_status, SqlOperator::Equals, 0);
    $workflow_log->set_order_by(S_WorkflowLog::$field_addtime, SqlSortType::Desc);
    $workflow_log->set_limit_count(1);
    $result = $workflow_log->load($db, $workflow_log);
    if ($result[0]) {
        $msg = json_encode(array('Content' => $penalty->get_username() . '已经认罚~', 'Title' => '处罚完成', 'UserName' => $penalty->get_username(), 'Type' => 11, 'IconName' => 'face_ww_tianshi', 'Code' => 0, 'MsgType' => 10));
        send_push_msg($msg, $workflow_log->get_userid());
    }
    //PUSH_MESSAGE END
    echo_msg('保存行政处罚单成功~');
}
if ($action == 3) {
    $penalty = new A_Penalty($penaltyData->id);
    $db = create_pdo();
    $result = $penalty->delete($db, true);
    if (!$result[0])
        die_error(USER_ERROR, '删除行政处罚单失败~');
    //PUSH_MESSAGE BEGIN
    $msg = json_encode(array('Content' => '亲，你的处罚已被撤销，虚惊一场哦~', 'Title' => '处罚撤销', 'UserName' => $penalty->get_username(), 'Type' => 11, 'IconName' => 'face_ww_anwei', 'Code' => 0, 'MsgType' => 10));
    send_push_msg($msg, $penaltyData->userid);
    //PUSH_MESSAGE END
    echo_msg('删除行政处罚单成功~');
}
if ($action == 4) {
    $penalty = new A_Penalty($penaltyData->id);
    $db = create_pdo();
    $result = $penalty->update($db);
    if (!$result[0])
        die_error(USER_ERROR, '标记为已读失败~');
    echo_msg('标记为已读成功~');
}
});
