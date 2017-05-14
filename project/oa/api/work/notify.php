<?php

/**
 * 周报列表
 *
 * @author QI
 * @copyright 2015 星密码
 * @version 2015/3/21
 */
use Models\Base\Model;
use Models\S_Notify;
use Models\Base\SqlOperator;
use Models\M_Dept;

require '../../common/http.php';
require '../../application.php';
require '../../loader-api.php';
require '../../Models/postMsgClass.php';


$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    $db = create_pdo();
    if (!isset($action))
        $action = -1;
    $notify = new S_Notify();
    $sort = request_string('sort');
    $sortname = request_string('sortname');
    $searchName = request_string('searchName');
    filter_numeric($status, 1);
    if (isset($searchName)) {
        $notify->set_where(S_Notify::$field_username, SqlOperator::Like, '%' . $searchName . '%');
    }
    if (isset($sort) && isset($sortname)) {
        $notify->set_order_by($notify->get_field_by_name($sortname), $sort);
    } else {
        $notify->set_order_by(S_Notify::$field_addtime, 'DESC');
    }
    $notify->set_limit_paged(request_pageno(), request_pagesize());
    $result = Model::query_list($db, $notify, NULL, true);
    if (!$result[0]) {
        die_error(USER_ERROR, '获取行政通知失败，请重试');
    }
    $models = Model::list_to_array($result['models'], array(), "id_2_text");
    echo_list_result($result, $models);
});

$notifyData = request_object();
//添加
if ($action == 1) {
    $db = create_pdo();
    $user = request_login_username();
    $dpid = get_dept_id();
    $dp = new M_Dept($dpid);
    $result = $dp->load($db, $dp);
    if (!$result[0])
        die_error(USER_ERROR, '获取部门信息失败');
    $pdname = $dp->get_text();
    $notify = new S_Notify();
    $notify->set_field_from_array($notifyData);
    $employee = get_employees()[request_userid()];
    $notify->set_userid(request_userid());
    $notify->set_username(request_username());
    $notify->set_addtime("now");
    $notify->set_dept1_id($employee['dept1_id']);

    pdo_transaction($db, function($db) use($notify) {
        $result = $notify->insert($db);
        if (!$result[0])
            die_error(USER_ERROR, '保存行政通知失败');
    });
    $postmsg = new postMsgClass();
    $postmsg->msgtype = msgType::notice;
    $postmsg->content = $notifyData->content;
    $postmsg->username = $user;
    $postmsg->department = $pdname;
    $postmsg->createtime = date('Y-m-d');
    $postmsg->title = $notifyData->title;
    $msg = json_encode($postmsg);
    $result = curl_http_post(PUSH_MESSAGE_URL, $msg);
    if ($result != 'True')
        die_error(USER_ERROR, "保存行政通知失败,发送失败~");
    exit(json_encode(array("code" => 0, "msg" => "发送成功")));
}
//删除
if ($action == 3) {
    //  $notifyData = request_object();
    $notify = new S_Notify();
    $notify->set_field_from_array($notifyData);
    $db = create_pdo();
    $result = $notify->delete($db, true);
    if (!$result[0])
        die_error(USER_ERROR, '删除失败');
    echo_msg('删除成功');
}


