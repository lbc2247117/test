<?php

/**
 * 员工列表/员工增删改操作
 *
 * @author Qi
 * @copyright 2015 星密码
 * @version 2015/3/3
 */
use Models\Base\Model;
use Models\M_Role;
use Models\W_Task;
use Models\W_TaskDetails;
use Models\Base\SqlOperator;
use Models\Base\SqlSortType;

require '../../application.php';
require '../../loader-api.php';
require '../../Common/FileUpload.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    if ($action == 1) {
        $task = new W_Task();
        $sort = request_string('sort');
        $sortname = request_string('sortname');

        $searchType = request_int('searchType');
        $searchStatus = request_int('searchStatus');
        $searchTime = request_string("searchTime");
        $searchName = request_string("searchName");
        if (isset($searchType)) {
            $task->set_where_and(W_Task::$field_type, SqlOperator::Equals, $searchType);
        }
        if (isset($searchStatus)) {
            $task->set_where_and(W_Task::$field_status, SqlOperator::Equals, $searchStatus);
        } else {
            $task->set_where_and(W_Task::$field_status, SqlOperator::NotEquals, -1);
        }
        if (isset($searchName)) {
            $task->set_custom_where(" and ( title like '%" . $searchName . "%' or addUserName like '%" . $searchName . "%' or takeOverName like '%" . $searchName . "%' )");
        }
        if (isset($searchTime)) {
            $task->set_custom_where(" and DATE_FORMAT(addTime,'%Y-%m-%d') = '" . $searchTime . "' ");
        }
        if (isset($sort) && isset($sortname)) {
            $task->set_order_by($task->get_field_by_name($sortname), $sort);
        } else {
            $task->set_order_by(W_Task::$field_addTime, SqlSortType::Desc);
        }
        $task->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $task, NULL, true);
        if (!$result[0]) die_error(USER_ERROR, '获取任务列表失败，请重试');
        $models = Model::list_to_array($result['models']);
        echo_list_result($result, $models);
    }
    if ($action == 2) {
        $task_id = request_int("task_id");
        $task_details = new W_TaskDetails();
        $task_details->set_where_and(W_TaskDetails::$field_taskId, SqlOperator::Equals, $task_id);
        $task_details->set_order_by(W_TaskDetails::$field_addTime, SqlSortType::Asc);
        $db = create_pdo();
        $result = Model::query_list($db, $task_details, NULL, true);
        if (!$result[0]) die_error(USER_ERROR, '获取任务列表失败，请重试');
        $models = Model::list_to_array($result['models']);
        $sql = "select u.avatar from M_UserToken u INNER JOIN W_Task t ON u.userid = t.addUserId WHERE t.id = " . $task_id . ";";
        $avatar = Model::execute_custom_sql($db, $sql);
        $task = new W_Task($task_id);
        $task->load($db, $task);
        $c = count($avatar['results']);
        if ($c > 0) {
            echo_list_result($result, $models, array('avatar' => $avatar['results'][0]['avatar'], 'c_date' => date("Y-m-d"), 'task' => $task->to_array()));
        }
        echo_list_result($result, $models, array('avatar' => "../../upload_avatar/default.png", 'c_date' => date("Y-m-d"), 'task' => $task->to_array()));
    }
});
execute_request(HttpRequestMethod::Post, function() use($action) {
    $taskData = request_object();
    //添加制度内容
    if ($action == 1) {
        if (!str_equals(request_login_username(), 'admin')) {
            $task = new W_Task();
            $task->set_field_from_array($taskData);
            $task->set_addUserId(request_login_userid());
            $task->set_addUserName(request_login_username());
            $take_over = take_over_array($taskData->type);
            $task->set_takeOverId($take_over['id']);
            $task->set_takeOverName($take_over['name']);
            $task->set_addTime("now");

            $task_details = new W_TaskDetails();
            $task_details->set_content($taskData->editorValue);
            $task_details->set_addUserId(request_login_userid());
            $task_details->set_addUserName(request_login_username());
            $task_details->set_addTime("now");
            $db = create_pdo();
            pdo_transaction($db, function($db) use($task, $task_details) {
                $result = $task->insert($db);
                if (!$result[0]) throw new TransactionException(PDO_ERROR_CODE, '添加任务失败。' . $result['detail_cn'], $result);
                $task_details->set_taskId($task->get_id());
                $result_details = $task_details->insert($db);
                if (!$result[0]) throw new TransactionException(PDO_ERROR_CODE, '添加任务失败。' . $result_details['detail_cn'], $result_details);
            });
            echo_msg('任务添加成功,请等待技术人员解决~');
        }else {
            echo_result(array('code' => USER_ERROR, 'msg' => '禁止使用系统账号发布任务..'));
        }
    }
    if ($action == 2) {
        if (!str_equals(request_login_username(), 'admin')) {
            $task_details = new W_TaskDetails();
            $task_details->set_content($taskData->content);
            $task_details->set_taskId($taskData->taskId);
            $task_details->set_addUserId(request_login_userid());
            $task_details->set_addUserName(request_login_username());
            $task_details->set_addTime("now");
            $db = create_pdo();
            $result = $task_details->insert($db);
            if (!$result[0]) die_error(USER_ERROR, '消息发送失败，请稍后重试~');
            $d_array = $task_details->to_array();
            $d_array["code"] = 0;
            $d_array["c_date"] = date('Y-m-d');
            echo_result($d_array);
        }else {
            echo_result(array('code' => USER_ERROR, 'msg' => '禁止使用系统账号发布任务..'));
        }
    }

    if ($action == 3) {
        $task = new W_Task();
        $task->set_field_from_array($taskData);
        $task_details = new W_TaskDetails();
        switch ($taskData->status) {
            case 1:
                $task_details->set_content("<span style='color: rgb(255, 0, 0);'>任务已提交,等待开始.</span>");
                break;
            case 2:
                $task_details->set_content("<span style='color: rgb(255, 0, 0);'>任务已开始,请耐心等待.</span>");
                $task->set_startTime("now");
                break;
            case 3:
                $task_details->set_content("<span style='color: rgb(255, 0, 0);'>任务已完成,请查验.</span>");
                $task->set_endTime("now");
                break;
            case 4:
                $task_details->set_content("<span style='color: rgb(255, 0, 0);'>任务已驳回,请仔细检查任务详细描述是否清楚明了.</span>");
                break;
            case 5:
                $task_details->set_content("<span style='color: rgb(255, 0, 0);'>任务已被终止,如需继续请'重启任务'.</span>");
                break;
            case 6:
                $task_details->set_content("<span style='color: rgb(255, 0, 0);'>任务已重启,任务已恢复原始状态.</span>");
                break;
            case -1:
                $task_details->set_content("<span style='color: rgb(255, 0, 0);'>任务已被删除,如需继续请联系管理员.</span>");
                break;
        }
        $task_details->set_taskId($taskData->id);
        $task_details->set_addUserId(1);
        $task_details->set_addUserName('admin');
        $task_details->set_addTime("now");
        $db = create_pdo();
        pdo_transaction($db, function($db) use($task, $task_details) {
            $result = $task->update($db);
            if (!$result[0]) throw new TransactionException(PDO_ERROR_CODE, '添加销售统计资料失败。' . $result['detail_cn'], $result);
            $result_details = $task_details->insert($db);
            if (!$result_details[0]) throw new TransactionException(PDO_ERROR_CODE, '添加销售统计资料失败。' . $result_details['detail_cn'], $result);
        });
        $d_array = $task_details->to_array();
        $d_array["code"] = 0;
        $d_array["msg"] = '修改成功~';
        $d_array['c_date'] = date('Y-m-d');
        $d_array['task'] = $task->to_array();
        echo_result($d_array);
    }
});

function take_over_array($type) {
    $take_over_array = array(
        1 => array('id' => '134', 'name' => '闫雄'),
        2 => array('id' => '123', 'name' => '陈浩'),
        3 => array('id' => '126', 'name' => '齐林江'),
        4 => array('id' => '133', 'name' => '王鹏'),
        5 => array('id' => '133', 'name' => '王鹏'),
        6 => array('id' => '123', 'name' => '陈浩'),
        99 => array('id' => '123', 'name' => '陈浩')
    );
    return $take_over_array[$type];
}
