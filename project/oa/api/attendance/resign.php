<?php

/*
 * 补卡(补签)
 */

use Models\Base\Model;
use Models\Base\SqlSortType;
use Models\A_Resign;
use Models\Base\SqlOperator;
use Models\personnel_punch;
use Common\ExtDateTime;
use Models\M_User;
use Models\P_hr;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../common/http.php';
require '../../Models/postMsgClass.php';
require '../../common/Enum.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    if ($action == 1) {
        $userid = request_userid();
        $sort = request_string('sort');
        $sortname = request_string('sortname');
        $searchName = request_string('searchName');
        $searchStartTime = request_string('searchStartTime');
        $searchEndTime = request_string('searchEndTime');
        $deptiid = request_string('deptid');

        $order_by = " order by case when status<5 then DATE_FORMAT(addtime,'%Y-%m-%d') else 1 end desc,DATE_FORMAT(addtime,'%Y-%m-%d') DESC,status asc,view_status='完成' desc,CONVERT(view_status USING gbk) ";
        $deptid = get_dept_id();
        $models = "";
        $hr = new P_hr();
        $hr->set_custom_where(" AND type in (3,5) ");
        if (isset($searchStartTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchStartTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $hr->set_custom_where(" AND DATE_FORMAT(begindate, '" . $formatStr . "') >= DATE_FORMAT('" . $searchStartTime . "','" . $formatStr . "') ");
        }
        if (isset($searchName)) {
            $hr->set_custom_where(" and (depart LIKE '%$searchName%' or username LIKE '%$searchName%')");
        }
        if (isset($deptiid)) {
            $hr->set_custom_where(' and departid=' . $deptiid);
        }
        if (isset($searchEndTime)) {
            $formatStr = '%Y-%m-%d';
            if (strlen($searchEndTime) > 10) {
                $formatStr = "%Y-%m-%d %H:%i";
            }
            $hr->set_custom_where(" AND DATE_FORMAT(enddate, '" . $formatStr . "') <= DATE_FORMAT('" . $searchEndTime . "','" . $formatStr . "') ");
        }
//        if ($role_type == role_Type::common) {
//            $personnel_punch->set_custom_where(" and (userid=" . request_userid() . ')');
//        } else if ($role_type == role_Type::leader) {
//            $personnel_punch->set_custom_where(" and (departid=$loginDeptid and (status>0 or userid=" . request_userid() . "))");
//        } else if ($role_type == role_Type::Personer) {
//            $personnel_punch->set_custom_where(" and (status in (1,2,3,4,5,6,7) or userid=" . request_userid() . ")");
//            $personnel_punch->set_custom_where(" order by case when status<6 then DATE_FORMAT(addtime,'%Y-%m-%d') else 1 end desc,DATE_FORMAT(addtime,'%Y-%m-%d') DESC,status asc,departid DESC");
//        } else if ($role_type == role_Type::reception) {
//            $personnel_punch->set_custom_where(" and (status in (1,2,3,4,5,6,7) or userid=" . request_userid() . ")");
//            $personnel_punch->set_custom_where(' order by addtime desc');
//        } else {
//            $personnel_punch->set_custom_where(" and (status in (4,6,7) or userid=" . request_userid() . ")");
//        }
        switch ($userid) {
            case Leader::tousu:
            case Leader::dianpukefu:
            case Leader::fuwu:
            case Leader::caiwu:
            case Leader::huodong:
            case Leader::sheji:
            case Leader::shouhou:
            case Leader::shouqian:
            case Leader::yunying:
            case Leader::pingtaikefu:
                $hr->set_custom_where(" and ((userid=" . $userid . " or status!=0 ) and  departid=" . $deptid . ")" . $order_by);
                $hr->set_limit_paged(request_pageno(), request_pagesize());
                $db = create_pdo();
                $result = Model::query_list($db, $hr, NULL, true);
                $models = Model::list_to_array($result['models']);
                foreach ($models as $key => $value) {
                    switch ($value["status"]) {
                        case Permion_btn::Wait_submit:
                            $models[$key]["permit"] = '1011';
                            break;
                        case Permion_btn::Depart_leader:
                            $models[$key]["permit"] = '0100';
                            break;
                        default:
                            $models[$key]["permit"] = '0000';
                            break;
                    }
                }
                break;
            case Leader::renshi:
                $hr->set_custom_where(" and (userid=" . $userid . " or status!=0 ) " . $order_by);
                $hr->set_limit_paged(request_pageno(), request_pagesize());
                $db = create_pdo();
                $result = Model::query_list($db, $hr, NULL, true);
                $models = Model::list_to_array($result['models']);
                foreach ($models as $key => $value) {
                    switch ($value["status"]) {
                        case Permion_btn::Wait_submit:
                            $models[$key]["permit"] = '1011';
                            break;
                        case Permion_btn::Renshi_leader;
                            $models[$key]["permit"] = '0100';
                            break;
                        case Permion_btn::Depart_leader:
                            if ($value["departid"] == $deptid) {
                                $models[$key]["permit"] = '0100';
                            } else {
                                $models[$key]["permit"] = '0001';
                            }
                            break;
                        default:
                            $models[$key]["permit"] = '0001';
                            break;
                    }
                }
                break;
            case Leader::zongjingli:
                $hr->set_custom_where(" and is_zongjing_approve=1 and status!=0" . $order_by);
                $hr->set_limit_paged(request_pageno(), request_pagesize());
                $db = create_pdo();
                $result = Model::query_list($db, $hr, NULL, true);
                $models = Model::list_to_array($result['models']);
                foreach ($models as $key => $value) {
                    switch ($value["status"]) {
                        case Permion_btn::Wait_submit:
                            $models[$key]["permit"] = '1011';
                            break;
                        case Permion_btn::Zongjingli;
                            $models[$key]["permit"] = '0100';
                            break;
                        default:
                            $models[$key]["permit"] = '0000';
                            break;
                    }
                }
                break;
            case Leader::fuzong:
                $hr->set_custom_where(" and is_fuzong_approve=1 and status!=0 " . $order_by);
                $hr->set_limit_paged(request_pageno(), request_pagesize());
                $db = create_pdo();
                $result = Model::query_list($db, $hr, NULL, true);
                $models = Model::list_to_array($result['models']);
                foreach ($models as $key => $value) {
                    switch ($value["status"]) {
                        case Permion_btn::Wait_submit:
                            $models[$key]["permit"] = '1011';
                            break;
                        case Permion_btn::Fuzong;
                            $models[$key]["permit"] = '0100';
                            break;
                        default:
                            $models[$key]["permit"] = '0000';
                            break;
                    }
                }
                break;
            default:
                if ($userid == RenshiEmployee::lwh || $userid == RenshiEmployee::hcx || $userid == RenshiEmployee::fmj) {
                    $hr->set_custom_where(" and (userid=" . $userid . " or status!=0 )" . $order_by);
                } else {
                    $hr->set_custom_where(" and userid=" . $userid . $order_by);
                }
                $hr->set_limit_paged(request_pageno(), request_pagesize());
                $db = create_pdo();
                $result = Model::query_list($db, $hr, NULL, true);
                $models = Model::list_to_array($result['models']);
                foreach ($models as $key => $value) {
                    switch ($value["status"]) {
                        case Permion_btn::Wait_submit:
                            $models[$key]["permit"] = '1011';
                            break;
                        default:
                            $models[$key]["permit"] = '0000';
                            break;
                    }
                }
                break;
        }
        echo_list_result($result, $models);
    }
//    if ($action == 11) {
//        $depts = get_depts();
//        $startTime = request_datetime("start_time");
//        $endTime = request_datetime("end_time");
//        $export = new ExportData2Excel();
//        $resign = new personnel_punch();
//        if (isset($startTime)) {
//            $resign->set_custom_where(" and DATE_FORMAT(begindate, '%Y-%m-%d') >= '" . $startTime . "' ");
//        }
//        if (isset($endTime)) {
//            $resign->set_custom_where(" and DATE_FORMAT(enddate, '%Y-%m-%d') <= '" . $endTime . "' ");
//        }
//        $field = array('username', 'dept1_id', 'remark', 'signtype', 'begindate');
//        $resign->set_query_fields($field);
//        $db = create_pdo();
//        $result = Model::query_list($db, $resign, NULL, true);
//        if (!$result[0]) {
//            $export->create(array('导出错误'), array(array('情况说明(补签)数据导出失败,请稍后重试!')), "情况说明(补签)数据导出", "情况说明(补签)");
//        }
//        $reasons = array(0 => '签到', 1 => '签退');
//        $models = Model::list_to_array($result['models'], array(), function (&$d) use($depts, $reasons) {
//                    $d['signtype'] = $reasons[$d['signtype']];
//                    $d['dept1_id'] = $depts[$d['dept1_id']]['text'];
//                });
//        $title_array = array('姓名', '所属部门', '原因', '补签类型', '补签时间');
//        $export->set_field($field);
//        $export->set_field_width(array(8, 15, 50, 15, 20));
//        $export->create($title_array, $models, "情况说明(补签)数据导出", "情况说明(补签)");
//    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $resignData = request_object();
    $db = create_pdo();
    if ($action == 1) {
        $db = create_pdo();
        $rest_date = $resignData->signdate; //开始时间
        $retroType = $resignData->retroType; //填写类型（1：个人填写 2：部门填写）
        $signType = $resignData->signtype;  //情况说明类型
        $reason = $resignData->reason;      //情况说明原因
        $user = get_employees()[request_int('userid')];
        $userid = request_userid();
        $username = request_username();
        $depts = get_depts();
        $depName = $depts[$user['dept1_id']]['text'];
        //添加情况说明
        $Is_fuzong_approve = Is_fuzong_approve::no;
        $Is_zongjing_approve = Is_zongjing_approve::no;

        if (count($rest_date) == 1) {
            $rest_date = array($rest_date);
        }

        //区分个人情况说明或部门情况说明
        if ($retroType == 1) {
            for ($i = 0; $i < count($rest_date); $i++) {
                //筛选部门负责需要谁审批
                switch (request_int('userid')) {
                    case Leader::tousu:
                    case Leader::shouhou:
                    case Leader::shouqian:
                    case Leader::fuwu;
                    case Leader::sheji:
                    case Leader::yunying:
                    case Leader::dianpukefu:
                    case Leader::huodong:
                    case Leader::caiwu:
                    case Leader::renshi:
                    case Leader::pingtaikefu:
                    case Leader::fuzong:
                        $Is_zongjing_approve = Is_zongjing_approve::yes;
                        
                        break;
                }
                //通过情况说明类型转换值
                if ($signType == 0) {
                    $signValue = "签到";
                } else if ($signType == 1) {
                    $signValue = "签退";
                }
                //通过前端所传数据进行插入操作
                $pHr = new P_hr();
                $pHr->set_userid($userid);
                $pHr->set_username($username);
                $pHr->set_departid($user['dept1_id']);
                $pHr->set_depart($depName);
                $pHr->set_begindate($rest_date[$i]);
                $pHr->set_addtime(date("Y-m-d H:i:s", time()));
                $pHr->set_type(P_type::qingkuangshuoming);
                $pHr->set_status(P_status::wait_confirm);
                $pHr->set_view_status(View_status::wait_submit);
                $pHr->set_is_fuzong_approve(Is_fuzong_approve::no);
                $pHr->set_is_zongjing_approve($Is_zongjing_approve);
                $pHr->set_matters_type($signValue);
                $pHr->set_reason($reason);

                //最后数据填写完毕，插入数据库
                $result = $pHr->insert($db);
                if (!$result[0])
                    die_error(USER_ERROR, '添加情况说明申请失败~');
            }
        } else if ($retroType == 2) {
            //添加部门情况说明
            //筛选部门负责需要谁审批
            for ($i = 0; $i < count($rest_date); $i++) {
                switch (request_int('userid')) {
                    case Leader::tousu:
                    case Leader::shouhou:
                    case Leader::shouqian:
                    case Leader::fuwu;
                        $Is_fuzong_approve = Is_fuzong_approve::yes;
                        break;
                    case Leader::sheji:
                    case Leader::yunying:
                    case Leader::dianpukefu:
                    case Leader::huodong:
                    case Leader::renshi:
                    case Leader::caiwu:
                    case Leader::pingtaikefu:
                        $Is_zongjing_approve = Is_zongjing_approve::yes;
                        
                        break;
                }
                //通过情况说明类型转换值
                if ($signType == 0) {
                    $signValue = "签到";
                } else if ($signType == 1) {
                    $signValue = "签退";
                }
                //通过前端所传数据进行插入操作
                $pHr = new P_hr();
                $pHr->set_userid($userid);
                $pHr->set_username($username);
                $pHr->set_departid($user['dept1_id']);
                $pHr->set_depart($depName);
                $pHr->set_begindate($rest_date[$i]);
                $pHr->set_addtime(date("Y-m-d H:i:s", time()));
                $pHr->set_type(P_type::depart_qingkuang);
                $pHr->set_status(P_status::wait_confirm);
                $pHr->set_view_status(View_status::wait_submit);
                $pHr->set_is_fuzong_approve($Is_fuzong_approve);
                $pHr->set_is_zongjing_approve($Is_zongjing_approve);
                $pHr->set_matters_type($signValue);
                $pHr->set_reason($reason);

                //最后数据填写完毕，插入数据库
                $result = $pHr->insert($db);
                if (!$result[0])
                    die_error(USER_ERROR, '添加部门情况说明申请失败~');
            }
        }
        echo_msg('添加情况说明申请成功');
    }
    if ($action == 2) {
        $userid = $resignData->userid;
        $id = $resignData->id;
        $postUserid = 0;
        $hr = new P_hr();
        $hr->set_id($id);
        $result = $hr->load($db, $hr);
        if (!$result[0])
            die_error(USER_ERROR, '获取数据失败');
        $bukaType = $hr->get_matters_type();
        switch ($userid) {
            case Leader::tousu:
            case Leader::shouhou:
            case Leader::shouqian:
            case Leader::fuwu:
            case Leader::dianpukefu:
            case Leader::huodong:
            case Leader::caiwu:
            case Leader::sheji:
            case Leader::yunying:
            case Leader::renshi:
            case Leader::pingtaikefu:
                $hr->set_status(Permion_btn::Zongjingli);
                $hr->set_view_status(View_status::wait_zongjingli_approve);
                $postUserid = Leader::zongjingli;
                break;
            default :
                $hr->set_status(Permion_btn::Depart_leader);
                $hr->set_view_status(View_status::wait_depart_leader_approve);
                $postUserid = get_header_id($hr->get_departid());
                break;
        }
        $db = create_pdo();
        $result = $hr->update($db);
        if (!$result[0])
            die_error(USER_ERROR, '提交审核失败~');
        //提交审核推送消息
        attendancePostMsg($hr, $postUserid, msgType::buka, null, $bukaType);
    }
    if ($action == 3) { //删除
        $person = new P_hr($resignData->id);
        $result = $person->delete($db, true);
        if (!$result[0])
            die_error(USER_ERROR, '删除情况说明失败~');
        echo_msg('删除情况说明成功~');
    }
    if ($action == 4) {
        $person = new P_hr();
        $person->set_field_from_array($resignData);
        $result = $person->update($db, true);
        if (!$result[0])
            die_error(USER_ERROR, '修改情况说明失败~');
        echo_msg('修改情况说明成功~');
    }
    if ($action == 5) {
        $person = new P_hr($resignData->id);
        $person->set_isRead(1);
        $result = $person->update($db);
        if (!$result[0])
            die_error(USER_ERROR, '标记为已读失败');
        echo_msg('标记为已读成功');
    }
    if ($action == 10) { //审核操作
        $hr = new P_hr($resignData->id);
        $postUserid = 0;
        $punch_sugger = array();
        $userid = request_userid();
        $remark = $resignData->remark;
        $isAgree = $resignData->isAgree;
        $deptid = get_dept_id();
        $result = $hr->load($db, $hr);
        if (!$result[0])
            die_error(USER_ERROR, '获取数据失败');
        $bukaType = $hr->get_matters_type();
        $headuser = new M_User(get_header_id($hr->get_departid()));
        $headresult = $headuser->load($db, $headuser);
        if (!$headresult[0])
            die_error(USER_ERROR, '获取数据失败');
        $departLeaderName = $headuser->get_username();
        switch ($userid) {
            case Leader::tousu:
            case Leader::dianpukefu:
            case Leader::fuwu:
            case Leader::caiwu:
            case Leader::huodong:
            case Leader::sheji:
            case Leader::shouhou:
            case Leader::shouqian:
            case Leader::yunying:
            case Leader::pingtaikefu:
                if ($isAgree == Depart_leader_mean::yes) {
                    if ($hr->get_is_fuzong_approve() == Is_fuzong_approve::yes) {
                        $hr->set_status(Permion_btn::Fuzong);
                        $hr->set_view_status(View_status::wait_fuzong_approve);
                        $postUserid = Leader::fuzong;
                    } else if ($hr->get_is_zongjing_approve() == Is_zongjing_approve::yes) {
                        $hr->set_status(Permion_btn::Zongjingli);
                        $hr->set_view_status(View_status::wait_zongjingli_approve);
                        $postUserid = Leader::zongjingli;
                    } else {
                        $hr->set_status(Permion_btn::Renshi_leader);
                        $hr->set_view_status(View_status::wait_renshi_leader_approve);
                        $postUserid = Leader::renshi;
                    }
                    $hr->set_depart_leader_mean("同意");
                } else {
                    $hr->set_status(Permion_btn::complete);
                    $hr->set_depart_leader_mean("不同意");
                    $hr->set_view_status(View_status::depart_leader_approve_fail);
                    $postUserid = $hr->get_userid();
                }
                $hr->set_depart_leader_remark($remark);
                $hr->set_depart_leader_time('now');
                array_push($punch_sugger, array('tabName' => '部门负责人', 'yiJian' => $hr->get_depart_leader_mean(), 'jieShi' => $hr->get_depart_leader_remark(), 'leader' => $departLeaderName, 'apporve_time' => date('Y-m-d')));
                break;
            case Leader::fuzong:
                if ($isAgree == Fuzong_mean::yes) {
                    if ($hr->get_is_zongjing_approve() == Is_zongjing_approve::yes) {
                        $hr->set_status(Permion_btn::Zongjingli);
                        $hr->set_view_status(View_status::wait_zongjingli_approve);
                        $postUserid = Leader::zongjingli;
                    } else {
                        $hr->set_status(Permion_btn::Renshi_leader);
                        $hr->set_view_status(View_status::wait_renshi_leader_approve);
                        $postUserid = Leader::renshi;
                    }
                    $hr->set_fuzong_mean("同意");
                } else {
                    $hr->set_status(Permion_btn::complete);
                    $hr->set_fuzong_mean("不同意");
                    $hr->set_view_status(View_status::fuzong_apprvoe_fail);
                    $postUserid = $hr->get_userid();
                }
                $hr->set_fuzong_remark($remark);
                $hr->set_fuzong_time('now');
                if ($hr->get_depart_leader_mean() != null) {
                    array_push($punch_sugger, array('tabName' => '部门负责人', 'yiJian' => $hr->get_depart_leader_mean(), 'jieShi' => $hr->get_depart_leader_remark(), 'leader' => $departLeaderName, 'apporve_time' => date('Y-m-d')));
                }
                array_push($punch_sugger, array('tabName' => '副总经理', 'yiJian' => $hr->get_fuzong_mean(), 'jieShi' => $hr->get_fuzong_remark(), 'leader' => LeaderName::fuzong, 'apporve_time' => date('Y-m-d')));
                break;
            case Leader::zongjingli:
                if ($isAgree == Zongjing_mean::yes) {
                    if ($hr->get_departid() == Department::renshi) {
                        $hr->set_status(Permion_btn::complete);
                        $hr->set_zongjing_mean("同意");
                        $hr->set_view_status(View_status::sucess);
                        $postUserid = $hr->get_userid();
                    } else {
                        $hr->set_status(Permion_btn::Renshi_leader);
                        $hr->set_view_status(View_status::wait_renshi_leader_approve);
                        $hr->set_zongjing_mean("同意");
                        $postUserid = Leader::renshi;
                    }
                } else {
                    $hr->set_status(Permion_btn::complete);
                    $hr->set_zongjing_mean("不同意");
                    $hr->set_view_status(View_status::zongjingli_approve_fail);
                    $postUserid = $hr->get_userid();
                }
                $hr->set_zongjing_remark($remark);
                $hr->set_zongjing_time("now");
                if ($hr->get_is_fuzong_approve() == Is_fuzong_approve::yes) {
                    if ($hr->get_depart_leader_mean() != null) {
                        array_push($punch_sugger, array('tabName' => '部门负责人', 'yiJian' => $hr->get_depart_leader_mean(), 'jieShi' => $hr->get_depart_leader_remark(), 'leader' => $departLeaderName, 'apporve_time' => date('Y-m-d')));
                    }
                    array_push($punch_sugger, array('tabName' => '副总经理', 'yiJian' => $hr->get_fuzong_mean(), 'jieShi' => $hr->get_fuzong_remark(), 'leader' => LeaderName::fuzong, 'apporve_time' => date('Y-m-d')));
                } else {
                    if ($hr->get_depart_leader_mean() != null) {
                        array_push($punch_sugger, array('tabName' => '部门负责人', 'yiJian' => $hr->get_depart_leader_mean(), 'jieShi' => $hr->get_depart_leader_remark(), 'leader' => $departLeaderName, 'apporve_time' => date('Y-m-d')));
                    }
                }
                array_push($punch_sugger, array('tabName' => '总经理', 'yiJian' => $hr->get_zongjing_mean(), 'jieShi' => $hr->get_fuzong_remark(), 'leader' => LeaderName::zongjingli, 'apporve_time' => date('Y-m-d')));
                break;
            case Leader::renshi:
                if ($isAgree == Renshi_leader_mean::yes) {
                    if ($deptid == $hr->get_departid()) {
                        if ($hr->get_is_fuzong_approve() == Is_fuzong_approve::yes) {
                            $hr->set_status(Permion_btn::Fuzong);
                            $hr->set_view_status(View_status::wait_fuzong_approve);
                            $postUserid = Leader::fuzong;
                        } else if ($hr->get_is_zongjing_approve() == Is_zongjing_approve::yes) {
                            $hr->set_status(Permion_btn::Zongjingli);
                            $hr->set_view_status(View_status::wait_zongjingli_approve);
                            $postUserid = Leader::zongjingli;
                        } else {
                            $hr->set_status(Permion_btn::complete);
                            $hr->set_view_status(View_status::sucess);
                            $postUserid = $hr->get_userid();
                        }
                        $hr->set_depart_leader_mean("同意");
                        $hr->set_depart_leader_remark($remark);
                        $hr->set_depart_leader_time('now');
                        array_push($punch_sugger, array('tabName' => '部门负责人', 'yiJian' => $hr->get_depart_leader_mean(), 'jieShi' => $hr->get_depart_leader_remark(), 'leader' => $departLeaderName, 'apporve_time' => date('Y-m-d')));
                    } else {
                        $hr->set_status(Permion_btn::complete);
                        $hr->set_view_status(View_status::sucess);
                        $postUserid = $hr->get_userid();
                        $hr->set_renshi_leader_mean("同意");
                        $hr->set_renshi_leader_remark($remark);
                        $hr->set_renshi_leader_time('now');
                        if ($hr->get_depart_leader_mean() != null) {
                            array_push($punch_sugger, array('tabName' => '部门负责人', 'yiJian' => $hr->get_depart_leader_mean(), 'jieShi' => $hr->get_depart_leader_remark(), 'leader' => $departLeaderName, 'apporve_time' => date('Y-m-d')));
                        }
                        if ($hr->get_is_fuzong_approve() == Is_fuzong_approve::yes) {
                            array_push($punch_sugger, array('tabName' => '副总经理', 'yiJian' => $hr->get_fuzong_mean(), 'jieShi' => $hr->get_fuzong_remark(), 'leader' => LeaderName::fuzong, 'apporve_time' => date('Y-m-d')));
                        }if ($hr->get_is_zongjing_approve() == Is_zongjing_approve::yes) {
                            array_push($punch_sugger, array('tabName' => '总经理', 'yiJian' => $hr->get_zongjing_mean(), 'jieShi' => $hr->get_fuzong_remark(), 'leader' => LeaderName::zongjingli, 'apporve_time' => date('Y-m-d')));
                        }
                        array_push($punch_sugger, array('tabName' => '人事经理', 'yiJian' => $hr->get_renshi_leader_mean(), 'jieShi' => $hr->get_renshi_leader_remark(), 'leader' => LeaderName::renshi, 'apporve_time' => date('Y-m-d')));
                    }
                } else {
                    if ($deptid == $hr->get_departid()) {
                        $hr->set_status(Permion_btn::complete);
                        $hr->set_view_status(View_status::depart_leader_approve_fail);
                        $hr->set_depart_leader_mean("不同意");
                        $hr->set_depart_leader_remark($remark);
                        $hr->set_depart_leader_time('now');
                        $postUserid = $hr->get_userid();
                        array_push($punch_sugger, array('tabName' => '部门负责人', 'yiJian' => $hr->get_depart_leader_mean(), 'jieShi' => $hr->get_depart_leader_remark(), 'leader' => $departLeaderName, 'apporve_time' => date('Y-m-d')));
                    } else {
                        $hr->set_status(Permion_btn::complete);
                        $hr->set_view_status(View_status::renshi_leader_approve_fail);
                        $hr->set_renshi_leader_mean("不同意");
                        $hr->set_renshi_leader_remark($remark);
                        $hr->set_renshi_leader_time('now');
                        $postUserid = $hr->get_userid();
                        if ($hr->get_depart_leader_mean() != null) {
                            array_push($punch_sugger, array('tabName' => '部门负责人', 'yiJian' => $hr->get_depart_leader_mean(), 'jieShi' => $hr->get_depart_leader_remark(), 'leader' => $departLeaderName, 'apporve_time' => date('Y-m-d')));
                        }
                        if ($hr->get_is_fuzong_approve() == Is_fuzong_approve::yes) {
                            array_push($punch_sugger, array('tabName' => '副总经理', 'yiJian' => $hr->get_fuzong_mean(), 'jieShi' => $hr->get_fuzong_remark(), 'leader' => LeaderName::fuzong, 'apporve_time' => date('Y-m-d')));
                        }if ($hr->get_is_zongjing_approve() == Is_zongjing_approve::yes) {
                            array_push($punch_sugger, array('tabName' => '总经理', 'yiJian' => $hr->get_zongjing_mean(), 'jieShi' => $hr->get_fuzong_remark(), 'leader' => LeaderName::zongjingli, 'apporve_time' => date('Y-m-d')));
                        }
                        array_push($punch_sugger, array('tabName' => '人事经理', 'yiJian' => $hr->get_renshi_leader_mean(), 'jieShi' => $hr->get_renshi_leader_remark(), 'leader' => LeaderName::renshi, 'apporve_time' => date('Y-m-d')));
                    }
                }
                break;
        }
        $result = $hr->update($db);
        if (!$result[0])
            die_error(USER_ERROR, '更新请假信息失败');
        $postHr = $hr;
        attendancePostMsg($postHr, $postUserid, msgType::buka, $punch_sugger, $bukaType);
    }
});
