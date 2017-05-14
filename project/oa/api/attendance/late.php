<?php

/*
 * 迟到
 */

use Models\Base\Model;
use Models\Base\SqlSortType;
use Models\A_Late;
use Models\Base\SqlOperator;
use Models\work_later;
use Models\M_Dept;
use Models\M_User;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../Models/postMsgClass.php';
require '../../common/http.php';
require '../../api/excel/PHPExcel/IOFactory.php';
require '../../Helper/CommonDal.php';

ini_set('memory_limit', '-1');
set_time_limit(0);
$upload = $_GET['upload'];
//导入excel
if ($upload == 1) {
    $file = $_FILES['filename']['tmp_name'];
    $fullpath = BASE_PATH . "/upload/" . md5(uniqid('', TRUE)) . ".xls";
    move_uploaded_file($file, $fullpath);
    $objReader = PHPExcel_IOFactory::createReaderForFile($fullpath);

    $document = PHPExcel_IOFactory::load($fullpath);
    $activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

    $arr_one = array();
    $save_one = array();
    foreach ($activeSheetData as $key => $value) {
        if ($key > 1) {
            if (!empty($value["A"])) {
                $userNew = new M_User();

                $userNew->set_query_fields(array(M_User::$field_userid));
                $userNew->set_where_and(M_User::$field_username, SqlOperator::Equals, trim($value["A"]));
                $db = create_pdo();
                $result = $userNew->load($db, $userNew);

                if (!$result[0])
                    die_error(USER_ERROR, '第' . $key . '行的用户名不存在，请确认是否为本公司员工...');

                $arr_one["userid"] = $userNew->get_userid();
                $arr_one["username"] = $value["A"];
                $arr_one["depart"] = $value["B"];
                $arr_one["lateType"] = $value["C"];
                $arr_one["later_day"] = $value["D"];
                $arr_one["later_time"] = $value["E"];
                $arr_one["add_time"] = date("Y-m-d H:i:s", time());
                $arr_one["add_user"] = request_userid();

                if (!strtotime($arr_one["later_day"])) {
                    die_error(USER_ERROR, '第' . $key . '行时间格式不对，请输入例如2015-01-01的格式');
                }
                if (!is_numeric($arr_one["later_time"])) {
                    die_error(USER_ERROR, '第' . $key . '行请填写正确的迟到时间');
                }

                array_push($save_one, $arr_one);
            } else {
                continue;
            }
        }
    }
    $newArray = array_values($save_one);

    request_userid();

    if (CommonHelper::Insert("work_later", $newArray, TRUE)) {
        echo_msg('导入迟到/早退记录成功...');
    } else {
        die_error(USER_ERROR, '导入迟到/早退记录失败...');
    }
}

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    if ($action == 1) {
//        $deptid = request_int('deptid');
        $sort = request_string('sort');
        $sortname = request_string('sortname');
        $searchName = request_string('searchName');
        $searchDate = request_string('searchDate');
        $deptid = request_string('deptid');
        $late = new work_later();
        if (isset($searchDate)) {
            $late->set_where_and(work_later::$field_later_day, SqlOperator::Between, array($searchDate . ' 00.00.00', $searchDate . ' 23:59:59'));
        }
        if (isset($deptid)) {
            $deptname = get_depts()[$deptid]['text'];
            $late->set_where_and(work_later::$field_depart, SqlOperator::Equals, $deptname);
        }
        if (isset($searchName)) {
            $late->set_where_and(work_later::$field_username, SqlOperator::Like, '%' . $searchName . '%');
        }
        if (isset($sort) && isset($sortname)) {
            $late->set_order_by($late->get_field_by_name($sortname), $sort);
        } else {
            $late->set_order_by(work_later::$field_later_day, SqlSortType::Desc);
        }
//        get_record_by_role($late);
        $late->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $late, NULL, true);
        if (!$result[0])
            die_error(USER_ERROR, '获取迟到/早退资料失败，请重试');
        $models = Model::list_to_array($result['models'], array(), "id_2_text");
        echo_list_result($result, $models);
    }
    if ($action == 11) {
        $depts = get_depts();
        $startTime = request_datetime("start_time");
        $endTime = request_datetime("end_time");
        $export = new ExportData2Excel();
        $late = new A_Late();
        if (isset($startTime)) {
            $late->set_custom_where(" and DATE_FORMAT(date, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $late->set_custom_where(" and DATE_FORMAT(date, '%Y-%m-%d') <= '" . $endTime . "' ");
        }
        $field = array('username', 'dept1_id', 'mins', 'date');
        $late->set_query_fields($field);
        $db = create_pdo();
        $result = Model::query_list($db, $late, NULL, true);
        if (!$result[0]) {
            $export->create(array('导出错误'), array(array('迟到/早退数据导出失败,请稍后重试!')), "迟到/早退数据导出", "迟到/早退");
        }
        $models = Model::list_to_array($result['models'], array(), function (&$d) use($depts) {
                    $d['dept1_id'] = $depts[$d['dept1_id']]['text'];
                });
        $title_array = array('姓名', '所属部门', '迟到/早退时长(分钟)', '日期');
        $export->set_field($field);
        $export->set_field_width(array(8, 15, 15, 20));
        $export->create($title_array, $models, "迟到/早退数据导出", "迟到");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    if ($action == 1) {
        $lateData = request_object();

        $user = get_employees()[$lateData->userid];
        $depart = new M_Dept($user['dept1_id']);
        $db = create_pdo();
        $depart->load($db, $depart);

        $late = new work_later();
        $late->set_userid($lateData->userid);
        $late->set_username($lateData->username);
        $late->set_depart($depart->get_text());
        $late->set_lateType($lateData->lateType);
        $late->set_later_day($lateData->date);
        $late->set_later_time($lateData->mins);
        $late->set_add_time(date("Y-m-d H:i:s", time()));
        $late->set_add_user(request_userid());

        $result = $late->insert($db);
        if (!$result[0])
            die_error(USER_ERROR, '添加迟到/早退信息失败~');

        $post = new postMsgClass();
        $post->msgtype = msgType::person_late;
        if (intval(date('m', strtotime(date("Y-m-d", time())))) >= 4 && intval(date('m', strtotime(date("Y-m-d", time())))) <= 10) {
            $title = "夏日炎炎";
        } else {
            $title = "天寒地冻";
        }
        $post->title = $title;
        $post->createtime = $lateData->date;

        $workLaterModel = new work_later();
        $workLaterModel->set_custom_where(" AND DATE_FORMAT(later_day, '%Y-%m-%d') >= DATE_FORMAT('" . $lateData->date . "','%Y-%m-%d') order by later_time desc");
        $resultNew = Model::query_list($db, $workLaterModel, NULL, true);
        $models = Model::list_to_array($resultNew['models']);
        $totalContent = "";
        foreach ($models as $key => $value) {
            $totalContent .= $value["depart"];
            $totalContent .= "  ";
            $totalContent .= $value["username"];
            $totalContent .= "  ";
            $totalContent .= $value["lateType"];
            $totalContent .= "  ";
            $totalContent .= $value["later_time"];
            $totalContent .= "分钟";
            $totalContent .= "|";
        }

        $post->content = $totalContent;
        $msg = json_encode($post);
        //$resultLast = curl_http_post(PUSH_MESSAGE_URL, $msg);

        echo_msg('添加迟到/早退信息成功~');
    }
    if ($action == 2) {
        $lateData = request_object();

        $dbLate = create_pdo();
        $late = new work_later($lateData->id);
        $late->load($dbLate, $late);

        $late->set_later_time($lateData->mins);
        $late->set_later_day($lateData->date);

        if ($lateData->userid != "" || $lateData->userid != null) {
            $employee = get_employees()[$lateData->userid];
            $depart = new M_Dept($employee['dept1_id']);
            $db = create_pdo();
            $depart->load($db, $depart);
            $late->set_depart($depart->get_text());
        }

        print_r($late->get_later_time());
        exit();

        $result = $late->update($dbLate, true);

        if (!$result[0])
            die_error(USER_ERROR, '修改迟到/早退信息失败~');
        echo_msg('修改迟到/早退信息成功~');
    }
    if ($action == 3) {
        $lateData = request_object();
        $late = new work_later($lateData->id);
        $db = create_pdo();
        $result = $late->delete($db, true);
        if (!$result[0])
            die_error(USER_ERROR, '删除迟到/早退信息失败~');
        echo_msg('删除迟到/早退信息成功~');
    }
});
