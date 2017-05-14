<?php

/*
 * 福利假
 */

use Models\Base\Model;
use Models\Base\SqlSortType;
use Models\A_Causeleave;
use Models\personnel_punch;
use Models\Base\SqlOperator;
use Models\M_User;
use Common\ExtDateTime;
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
        $hr->set_custom_where(" AND type=4 ");
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
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $causeleaveData = request_object();
    $db = create_pdo();
    if ($action == 1) {
        $user = get_employees()[request_int('userid')];
        $userid = request_userid();
        $username = request_username();
        $depts = get_depts();
        $depName = $depts[$user['dept1_id']]['text'];
        $rest_date = $causeleaveData->starttime; //开始时间
        $adjust_to = $causeleaveData->endtime; //结束时间
        $reason = $causeleaveData->reason;      //事由
        if (count($adjust_to) !== count($rest_date))
            die_error(USER_ERROR, '请填写正确的日期');
        if (count($adjust_to) == 1) {
            $adjust_to = array($adjust_to);
            $rest_date = array($rest_date);
        }

        //通过前端所传数据进行插入操作
        for ($i = 0; $i < count($rest_date); $i++) {

            $begintime = $rest_date[$i];
            $endtime = $adjust_to[$i];

            $begintime = $begintime . "00:00:00";
            $endtime = $endtime . "00:00:00";
            $time_s = strtotime($endtime) - strtotime($begintime);
            if ($time_s < 0)
                die_error(USER_ERROR, '结束时间不能小于开始时间');
            $lastTime = $time_s / 3600;

            $pHr = new P_hr();
            $pHr->set_userid($userid);
            $pHr->set_username($username);
            $pHr->set_departid($user['dept1_id']);
            $pHr->set_depart($depName);
            $pHr->set_begindate($begintime);
            $pHr->set_enddate($endtime);
            $pHr->set_addtime(date("Y-m-d H:i:s", time()));
            $pHr->set_pursh_time($lastTime);
            $pHr->set_type(P_type::fulijia);
            $pHr->set_child_type($causeleaveData->child_type);
            $pHr->set_status(P_status::wait_confirm);
            $pHr->set_view_status(View_status::wait_submit);
            $pHr->set_is_fuzong_approve(Is_fuzong_approve::no);
            $pHr->set_is_zongjing_approve(Is_zongjing_approve::yes);
            $pHr->set_reason($reason);

            //最后数据填写完毕，插入数据库
            $result = $pHr->insert($db);
            if (!$result[0])
                die_error(USER_ERROR, '添加福利假申请失败~');
        }

        echo_msg('添加福利假申请成功');
    }
    if ($action == 2) {
        $userid = $causeleaveData->userid;
        $id = $causeleaveData->id;
        $postUserid = 0;
        $hr = new P_hr();
        $hr->set_id($id);
        $result = $hr->load($db, $hr);
        if (!$result[0])
            die_error(USER_ERROR, '获取数据失败');
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
                $hr->set_status(Permion_btn::Renshi_leader);
                $hr->set_view_status(View_status::wait_renshi_leader_approve);
                $postUserid = Leader::renshi;
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
        $chlidType = "";
        switch ($hr->get_child_type()) {
            case Child_type::marry:
                $chlidType = "婚假";
                break;
            case Child_type::peichanjia:
                $chlidType = "陪产假";
                break;
            case Child_type::dead:
                $chlidType = "丧假";
                break;
            case Child_type::child:
                $chlidType = "产假";
                break;
            case Child_type::heath:
                $chlidType = "病假";
                break;
        }
        //提交审核推送消息
        attendancePostMsg($hr, $postUserid, msgType::welfare, null, $chlidType);
    }
    if ($action == 3) { //删除
        $personData = request_object();
        $person = new P_hr($personData->id);
        $db = create_pdo();
        $result = $person->delete($db, true);
        if (!$result[0])
            die_error(USER_ERROR, '删除请假申请失败~');
        echo_msg('删除请假申请成功~');
    }
    if ($action == 4) {
        $person = new P_hr();
        $person->set_field_from_array($causeleaveData);
        $time_s = strtotime($causeleaveData->enddate) - strtotime($causeleaveData->begindate);
        if ($time_s < 0)
            die_error(USER_ERROR, '结束时间不能小于开始时间');
        $hours = $time_s / 3600;
        $person->set_pursh_time($hours);
        $result = $person->update($db, true);
        if (!$result[0])
            die_error(USER_ERROR, '修改调休信息失败~');
        echo_msg('修改调休信息成功~');
    }
    if ($action == 5) {
        $person = new P_hr($causeleaveData->id);
        $person->set_isRead(1);
        $result = $person->update($db);
        if (!$result[0])
            die_error(USER_ERROR, '标记为已读失败');
        echo_msg('标记为已读成功');
    }
    if ($action == 10) { //审核操作
        $hr = new P_hr($causeleaveData->id);
        $postUserid = 0;
        $punch_sugger = array();
        $userid = request_userid();
        $remark = $causeleaveData->remark;
        $isAgree = $causeleaveData->isAgree;
        $deptid = get_dept_id();
        $result = $hr->load($db, $hr);
        if (!$result[0])
            die_error(USER_ERROR, '获取数据失败');
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
        $chlidType = "";
        switch ($hr->get_child_type()) {
            case Child_type::marry:
                $chlidType = "婚假";
                break;
            case Child_type::peichanjia:
                $chlidType = "陪产假";
                break;
            case Child_type::dead:
                $chlidType = "丧假";
                break;
            case Child_type::child:
                $chlidType = "产假";
                break;
            case Child_type::heath:
                $chlidType = "病假";
                break;
        }
        $postHr = $hr;
        attendancePostMsg($postHr, $postUserid, msgType::welfare, $punch_sugger, $chlidType);
    }
});
