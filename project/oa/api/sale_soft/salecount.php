<?php

/**
 * 销售业绩统计表
 *
 * @author QI
 * @copyright 2015 星密码
 * @version 2015/3/16
 */
use Models\Base\Model;
use Models\P_Salecount_soft;
use Models\Base\SqlOperator;
use Models\P_Customerrecord_soft;
use Models\P_Customerdetails_soft;
use Models\p_refund_soft;
use Models\Base\SqlSortType;
use Models\M_User;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../common/http.php';

$use_redis = false;
$redis_config = array('host' => '127.0.0.1', 'port' => 6380);
$threshold = '03:00:00';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action, $use_redis, $redis_config, $threshold) {
    $login_userid = request_login_userid();
    $is_manager = is_manager($login_userid, 2);
    if (!isset($action)) $action = 1;
    if ($action == 1) {
        $salecount = new P_Salecount_soft();
        $sort = request_string('sort');
        $sortname = request_string('sortname');
        $searchName = request_string('searchName');
        $searchSetmeal = request_string("searchSetmeal");
        $searchStartTime = request_string("searchStartTime");
        $searchEndTime = request_string("searchEndTime");
        $searchChannel = request_string("searchChannel");
        $status = request_int('status');

        if (isset($searchName)) {
            $salecount->set_custom_where(" AND ( nick_name like '%" . $searchName . "%' OR money = '" . $searchName . "' OR mobile like '%" . $searchName . "%' OR  ww like '%" . $searchName . "%' OR name LIKE '%" . $searchName . "%' OR qq like'%" . $searchName . "%' OR presales like '%" . $searchName . "%' OR customer LIKE '%" . $searchName . "%' ) ");
        }
        if (isset($searchSetmeal)) {
            $salecount->set_where_and(P_Salecount_soft::$field_setmeal, SqlOperator::Equals, $searchSetmeal);
        }
        if (isset($searchStartTime)) {
            $salecount->set_custom_where(" and DATE_FORMAT(addtime, '%Y-%m-%d %H:%i:%s') >= '" . $searchStartTime . ":00' ");
        }
        if (isset($searchEndTime)) {
            $salecount->set_custom_where(" and DATE_FORMAT(addtime, '%Y-%m-%d %H:%i:%s') <= '" . $searchEndTime . ":00' ");
        }
        if (isset($searchChannel)) {
            $salecount->set_where_and(P_Salecount_soft::$field_channel, SqlOperator::Equals, $searchChannel);
        }
        if (isset($status)) {
            switch ($status) {
                case 1:
                    $salecount->set_where_and(P_Salecount_soft::$field_status, SqlOperator::Equals, 1);
                    break;
                case 2:
                    $salecount->set_where_and(P_Salecount_soft::$field_conflictWith, SqlOperator::NotEquals, 0);
                    break;
                case 3:
                    $salecount->set_where_and(P_Salecount_soft::$field_customer_id, SqlOperator::Equals, 0);
                    break;
            }
        }
        if (!$is_manager) {
            $salecount->set_custom_where(" and  (customer_id = " . $login_userid . " or presales_id = " . $login_userid . " or customer_id2 = " . $login_userid . " )");
        }
        if (isset($sort) && isset($sortname)) {
            $salecount->set_order_by($salecount->get_field_by_name($sortname), $sort);
        } else {
            $salecount->set_order_by(P_Salecount_soft::$field_id, 'DESC');
        }
        $salecount->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $salecount, NULL, true);
        if (!$result[0]) die_error(USER_ERROR, '获取统计资料失败，请重试');
        $models = Model::list_to_array($result['models']);
//    $models = Model::list_to_array($result['models'], array(), function(&$d) use($is_manager, $login_userid) {
//                if (!$is_manager && !str_equals($d['presales_id'], $login_userid) && !str_equals($d['customer_id'], $login_userid)) {
//                    $d['ww'] = '****';
//                    $d['name'] = '***';
//                    $d['qq'] = '********';
//                    $d['mobile'] = '***********';
//                    $d['province'] = '***';
//                }
//            });
        echo_list_result($result, $models, array('currentDate' => date('Y-m-d'), 'is_manager' => $is_manager));
    }
    //排名
    if ($action == 2) {
        $time_unit = request_int('time_unit', 1, 3);
        $sql = 'SELECT A.presales_id,A.presales,us.role_id as group_id,B.sale_count,B.money FROM p_salecount_soft A,(SELECT id,COUNT(*) AS sale_count,MAX(id) AS max_id ,SUM(money) money FROM p_salecount_soft WHERE 1=1 ';
        switch ($time_unit) {
            case 1:
                $h = (int) date("H");
                if ($h < 3) {
                    $sql.= "AND '" . date('Y-m-d', strtotime("-1 day")) . " 03:00:00' <=  DATE_FORMAT(addtime,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(addtime,'%Y-%m-%d %H:%i:%s') <= '" . date('Y-m-d') . " 02:59:59' ";
                } else {
                    $sql.= "AND '" . date('Y-m-d') . " 03:00:00' <=  DATE_FORMAT(addtime,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(addtime,'%Y-%m-%d %H:%i:%s') <= '" . date('Y-m-d', strtotime("+1 day")) . " 02:59:59' ";
                }
                break;
            case 2:
                $date = date('Y-m-d');  //当前日期
                $first = 1; //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
                $w = date('w', strtotime($date));  //获取当前周的第几天 周日是 0 周一到周六是 1 - 6 
                $now_start = date('Y-m-d', strtotime("$date -" . ($w ? $w - $first : 6) . ' days')) . ' 03:00:00'; //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
                $now_end = date('Y-m-d', strtotime("$now_start +7 days")) . ' 02:59:59';  //本周结束日期
                $sql.= "AND '" . $now_start . "' <=  DATE_FORMAT(addtime,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(addtime,'%Y-%m-%d %H:%i:%s') <= '" . $now_end . "' ";
                break;
            case 3:
                $date = date('Y-m-d');
                $firstday = date('Y-m-01') . ' 03:00:00';
                $lastday = date('Y-m-01', strtotime(date('Y-m-01', strtotime($date)) . ' +1 month')) . ' 02:59:59';
                $sql.= "AND '" . $firstday . "' <=  DATE_FORMAT(addtime,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(addtime,'%Y-%m-%d %H:%i:%s') <= '" . $lastday . "' ";
                break;
        }
        $sql .='GROUP BY presales_id) B,M_user us WHERE  A.id = B.max_id AND us.userid = A.presales_id ORDER BY sale_count DESC';
        $db = create_pdo();
        $result = Model::execute_custom_sql($db, $sql);
        if (!$result[0]) die_error(USER_ERROR, '获取排名数据失败，请重试');
        echo_result($result['results']);
    }
    if ($action == 4) {
        $retArray = array('TodayWWTotalsSoft' => 0, 'TodayBDTotalsSoft' => 0, 'Today360TotalsSoft' => 0, 'TodayUCTotalsSoft' => 0, 'TodaySGTotalsSoft' => 0, 'TodayYHZTotalsSoft' => 0,
            'TodayWWTimelyTotalsSoft' => 0, 'TodayBDTimelyTotalsSoft' => 0, 'Today360TimelyTotalsSoft' => 0, 'TodayUCTimelyTotalsSoft' => 0, 'TodaySGTimelyTotalsSoft' => 0, 'TodayYHZTimelyTotalsSoft' => 0);
        $redis = new Redis();
        $redis_connected = $redis->pconnect($redis_config['host'], $redis_config['port']);
        if ($use_redis) {
            $use_redis = $redis_connected;
            if ($redis_connected === true) {
                $date_adjusted_format = adjust_time('Y_m_d', $threshold) . '_';
                array_walk($retArray, function(&$val, $key) use($redis, $date_adjusted_format) {
                    $val = (int) $redis->get($date_adjusted_format . $key);
                });
            }
        }
        if (!$use_redis) {
            array_walk($retArray, function(&$val, $key) {
                $val = 0;
            });
            $groupSumCountSql = "SELECT ps.isTimely,ps.channel FROM P_Salecount_soft ps WHERE " . getWhereSql("ps");
            $salecount_soft_count = new P_Salecount_soft();
            $db = create_pdo();
            $groupSumCountResult = Model::query_list($db, $salecount_soft_count, $groupSumCountSql);
            $sumCountModel = Model::list_to_array($groupSumCountResult['models']);
            foreach ($sumCountModel as $model) {
                $timely = isset($model['isTimely']) ? $model['isTimely'] : 0;
                switch ($model['channel']) {
                    case '旺旺':
                        $retArray['TodayWWTotalsSoft'] += 1;
                        if ($timely == 1) {
                            $retArray['TodayWWTimelyTotalsSoft'] += 1;
                        }
                        break;
                    case '百度':
                        $retArray['TodayBDTotalsSoft'] += 1;
                        if ($timely == 1) {
                            $retArray['TodayBDTimelyTotalsSoft'] += 1;
                        }
                        break;
                    case '360':
                        $retArray['Today360TotalsSoft'] += 1;
                        if ($timely == 1) {
                            $retArray['Today360TimelyTotalsSoft'] += 1;
                        }
                        break;
                    case 'UC神马':
                        $retArray['TodayUCTotalsSoft'] += 1;
                        if ($timely == 1) {
                            $retArray['TodayUCTimelyTotalsSoft'] += 1;
                        }
                        break;
                    case '搜狗':
                        $retArray['TodaySGTotalsSoft'] += 1;
                        if ($timely == 1) {
                            $retArray['TodaySGTimelyTotalsSoft'] += 1;
                        }
                        break;
                    case '优化站':
                        $retArray['TodayYHZTotalsSoft'] += 1;
                        if ($timely == 1) {
                            $retArray['TodayYHZTimelyTotalsSoft'] += 1;
                        }
                        break;
                }
            }
            if ($redis_connected) {
                $date_adjusted_format = adjust_time('Y_m_d', $threshold) . '_';
                foreach ($retArray as $key => $value) {
                    $redis->set($date_adjusted_format . $key, $value);
                }
                $redis->set($date_adjusted_format . 'TodayTotalsSoft', ($retArray['TodayWWTotalsSoft'] + $retArray['TodayBDTotalsSoft'] + $retArray['Today360TotalsSoft'] + $retArray['TodayUCTotalsSoft'] + $retArray['TodaySGTotalsSoft'] + $retArray['TodayYHZTotalsSoft']));
                $redis->set($date_adjusted_format . 'TodayTotalsTimelySoft', ($retArray['TodayWWTimelyTotalsSoft'] + $retArray['TodayBDTimelyTotalsSoft'] + $retArray['Today360TimelyTotalsSoft'] + $retArray['TodayUCTimelyTotalsSoft'] + $retArray['TodaySGTimelyTotalsSoft'] + $retArray['TodayYHZTimelyTotalsSoft']));
            }
        }
        echo_result($retArray);
    }
    if ($action == 10) {
        $db = create_pdo();
        $customer = new P_Customerrecord_soft();
        $customer->set_status(1);
        $customer->set_query_fields(array('userid', 'username', 'nickname', 'qqReception'));
        $customer_result = Model::query_list($db, $customer);
        $customer_list = Model::list_to_array($customer_result['models'], array(), function(&$d) {
                    $d['id'] = $d['userid'];
                    $d['text'] = $d['username'] . "(" . $d['nickname'] . ")";
                    unset($d['userid']);
                    unset($d['username']);
                    unset($d['nickname']);
                });

        $presales = new M_User();
        $presales->set_where_and(M_User::$field_role_id, SqlOperator::Equals, 1104);
        $presales->set_custom_where(" and status in (1,2) ");
        $presales->set_query_fields(array('userid', 'username'));
        $presales_result = Model::query_list($db, $presales);
        $presales_list = Model::list_to_array($presales_result['models'], array(), function(&$d) {
                    $d['id'] = $d['userid'];
                    $d['text'] = $d['username'];
                    unset($d['userid']);
                    unset($d['username']);
                });
        echo_result(array('code' => 0, 'presales_list' => $presales_list, 'customer_list' => $customer_list));
    }
    if ($action == 11) {
        $startTime = request_datetime("start_time");
        $endTime = request_datetime("end_time");
        $export = new ExportData2Excel();
        $salecount = new P_Salecount_soft();
        if (isset($startTime)) {
            $salecount->set_custom_where(" and DATE_FORMAT(addtime, '%Y-%m-%d %H:%i:%s') >= '" . $startTime . " 03:00:00' ");
        }
        if (isset($endTime)) {
            $salecount->set_custom_where(" and DATE_FORMAT(addtime, '%Y-%m-%d %H:%i:%s') <= '" . date('Y-m-d', strtotime("+1 day", strtotime($endTime))) . " 02:59:59' ");
        }
        $field = array('addtime', 'ww', 'name', 'qq', 'mobile', 'money', 'arrears', 'setmeal', 'payment', 'channel', 'c_shop', 'isTimely', 'presales', 'customer', 'customer2', 'province', 'address', 'remark');

        $salecount->set_query_fields($field);
        $db = create_pdo();
        $result = Model::query_list($db, $salecount, NULL, true);
        if (!$result[0]) {
            $export->create(array('导出错误'), array(array('销售统计数据导出失败,请稍后重试!')), "销售统计数据导出", "销售统计");
        }
        $models = Model::list_to_array($result['models'], array(), function(&$d) {
                    $d['isTimely'] = $d['isTimely'] === 0 ? "否" : '是';
                });
        $title_array = array('日期', '旺旺号', '真实姓名', 'QQ号', '手机号', '金额', '欠款', '套餐类型', '收款方式', '接入渠道', '收款店铺', '是否及时', '售前', '售后', '更换老师', '省份', '地址', '备注');
        $export->set_field($field);
        $export->set_field_width(array(20, 20, 10, 12, 13, 8, 8, 10, 10, 10, 15, 9, 10, 10, 10, 20, 20, 30));
        $export->create($title_array, $models, "销售统计数据导出", "销售统计");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action, $use_redis, $redis_config, $threshold) {
    $salecountData = request_object();
    filter_numeric($salecountData->arrears, 0);
    if ($action == 1) {
        $db = create_pdo();
        $salecountCount = new P_Salecount_soft();
        $customerdetails = new P_Customerdetails_soft();
        if ($salecountData->ww != '无' && $salecountData->ww != 'QQ') {
            $salecountCount->set_where_and(P_Salecount_soft::$field_ww, SqlOperator::Equals, $salecountData->ww);
        }
        if ($salecountData->qq != '无' && $salecountData->qq != '旺旺') {
            $salecountCount->set_where_and(P_Salecount_soft::$field_qq, SqlOperator::Equals, $salecountData->qq);
        }
        $salecountCount->set_where_or(P_Salecount_soft::$field_mobile, SqlOperator::Equals, $salecountData->mobile);
        $salecountCount->set_order_by(P_Salecount_soft::$field_addtime, SqlSortType::Asc);
        $result = Model::query_list($db, $salecountCount);
        if (!$result[0]) die_error(USER_ERROR, '添加失败,请稍后重试~');
        $models = Model::list_to_array($result['models']);

        // addstart
        $customerdetails->set_date("now");
        if (isset($salecountData->ww)) $customerdetails->set_ww($salecountData->ww);
        if (isset($salecountData->qq)) $customerdetails->set_qq($salecountData->qq);
        if (isset($salecountData->money)) $customerdetails->set_money($salecountData->money);
        if (isset($salecountData->presales)) $customerdetails->set_presales($salecountData->presales);
        if (isset($salecountData->presales_id)) $customerdetails->set_presales_id($salecountData->presales_id);
        if (isset($salecountData->payment)) $customerdetails->set_payment($salecountData->payment);
        $customerdetails->set_is_receive(1);
        $customerdetails->set_is_reviews(1);
        $customerdetails->set_is_addto_reviews(1);
        $employees = get_employees();
        $group_id = $employees[$salecountData->presales_id]['role_id'];
        // addend

        if ($result['count'] >= 1) {
            $salecount = new P_Salecount_soft();
            $salecount->set_field_from_array($salecountData);
            $salecount->set_status(0);
            $salecount->set_addtime("now");
            $salecount->set_conflictWith($models[0]['id']);

            $update_status_sql = "update P_Salecount_soft set status = 0 where id = " . $models[0]['id'];
            pdo_transaction($db, function($db) use($update_status_sql, $salecount, $customerdetails, $salecountData) {
                $result = Model::execute_custom_sql($db, $update_status_sql);
                if (!$result[0]) throw new TransactionException(PDO_ERROR_CODE, '保存统计资料失败。' . $result['detail_cn'], $result);
                $result = $salecount->insert($db);
                if (!$result[0]) throw new TransactionException(PDO_ERROR_CODE, '保存统计资料失败。' . $result['detail_cn'], $result);

                $customerdetails->set_sale_id($salecount->get_id());
                $customerdetails->set_customer("无");
                $customerdetails->set_customer_id(0);
                $result_customerdetails = $customerdetails->insert($db);
                if (!$result_customerdetails[0]) throw new TransactionException(PDO_ERROR_CODE, '添加客户详情资料失败。' . $result['detail_cn'], $result);
                add_data_add_log($db, $salecountData, new P_Salecount_soft($salecount->get_id()), 6);
            });
            echo_msg("<label style='color:red;'>保存统计资料成功,但与已录入数据冲突,请及时处理</label>~");
        }else {
            $salecount = new P_Salecount_soft();
            $salecount->set_field_from_array($salecountData);
            $salecount->set_addtime("now");
            $salecount->set_group_id($group_id);
            $update_distribution_sql = "";
            $customer = new P_Customerrecord_soft();

            //0:接单上限   1：正常
            $sql = "SELECT pc.id,pc.userid,pc.username,pc.nickname,pc.toplimit,IFNULL(ps.finish,0) AS finish ,pc.`status`,pc.lastDistribution,pc.qqReception,pc.starttime,pc.endtime ";
            $sql .= "FROM P_Customerrecord_soft pc ";
            $sql .= "LEFT JOIN ( ";
            $sql .= "SELECT sa.customer,sa.customer_id, IFNULL(COUNT(sa.customer_id),0) AS finish FROM P_Salecount_soft sa WHERE " . getWhereSql("sa") . " AND sa.customer_id != 0 GROUP BY sa.customer_id ";
            $sql .= ") AS ps ON pc.userid = ps.customer_id ";
            $sql .= "WHERE pc.toplimit > IFNULL(ps.finish,0) AND pc.`status` = 1 ";
            if ($salecountData->isQQTeach == 1) {//QQ教学 指定分配
                $sql .= " AND pc.qqReception = 1 ";
            }
            $sql .= " ORDER BY pc.lastDistribution ASC ";
            $customerres = Model::query_list($db, $customer, $sql);
            $models = Model::list_to_array($customerres['models']);
            $customerres_count = $customerres['count'];
            if ($customerres_count != 0) {
                $user = $models[0];
                $userid = $user['userid'];
                $username = $user['username'];
                $nickName = $user['nickname'];
                $toplimit = $user['toplimit'];
                $finish = $user['finish'];
                $salecount->set_customer_id($userid);
                $salecount->set_customer($username);
                $salecount->set_nick_name($nickName);
                $salecount->set_status(1);
                //addto   插入售后ID
                $customerdetails->set_customer_id($userid);
                $customerdetails->set_customer($username);
                //addend
                $update_distribution_sql = "update P_Customerrecord_soft SET lastDistribution = '" . (microtime(TRUE) * 10000) . "' where id = " . $user['id'];
                pdo_transaction($db, function($db) use($salecount, $customerdetails, $update_distribution_sql, $salecountData) {
                    if ($update_distribution_sql != '') {
                        $cresult = Model::execute_custom_sql($db, $update_distribution_sql);
                        if (!$cresult[0]) throw new TransactionException(PDO_ERROR_CODE, '添加销售统计资料失败。' . $cresult['detail_cn'], $cresult);
                    }
                    $result = $salecount->insert($db);
                    if (!$result[0]) throw new TransactionException(PDO_ERROR_CODE, '添加销售统计资料失败。' . $result['detail_cn'], $result);
                    $customerdetails->set_sale_id($salecount->get_id());
                    $result_customerdetails = $customerdetails->insert($db);
                    if (!$result_customerdetails[0]) throw new TransactionException(PDO_ERROR_CODE, '添加客户详情资料失败。' . $result['detail_cn'], $result);
                    add_data_add_log($db, $salecountData, new P_Salecount_soft($salecount->get_id()), 6);
                });
            } else {
                pdo_transaction($db, function($db) use($salecount, $customerdetails, $salecountData) {
                    $result = $salecount->insert($db);
                    if (!$result[0]) throw new TransactionException(PDO_ERROR_CODE, '添加销售统计资料失败。' . $result['detail_cn'], $result);
                    $customerdetails->set_sale_id($salecount->get_id());
                    $customerdetails->set_customer("无");
                    $customerdetails->set_customer_id(0);
                    $result_customerdetails = $customerdetails->insert($db);
                    if (!$result_customerdetails[0]) throw new TransactionException(PDO_ERROR_CODE, '添加客户详情资料失败。' . $result['detail_cn'], $result);
                    add_data_add_log($db, $salecountData, new P_Salecount_soft($salecount->get_id()), 6);
                });
            }

            if ($use_redis) {
                //更新统计信息+1
                refresh_sale_statistics('incr', $group_id, $salecountData, $redis_config, $threshold);
            }

            $push_array = get_push_array($salecountData->presales_id, $db, $use_redis, $redis_config, $threshold);
            $msg = json_encode($push_array);
            $users = get_receive_sale_msg_userids(11);
            send_push_msg($msg, implode(',', $users));
            if ($customerres_count != 0) {
                echo_msg('添加成功~');
            } else {
                echo_msg('添加成功,但未分配售后~');
            }
        }
    }
    //修改销售统计信息
    if ($action == 2) {
        //$employees = get_employees();
        $salecount = new P_Salecount_soft($salecountData->id);

        $customerdetailsadd = new P_Customerdetails_soft();

        if (isset($salecountData->customer_id)) {
            if ($salecountData->customer_id2 == $salecountData->customer_id) {
                die_error(USER_ERROR, '请不要重复选择老师');
            }
        }
        $salecount->set_field_from_array($salecountData);

        $db = create_pdo();
        add_data_change_log($db, $salecountData, new P_Salecount_soft($salecountData->id), 6);
        $result = $salecount->update($db, true);
        if (!$result[0]) die_error(USER_ERROR, '保存统计资料失败');

        if (isset($salecountData->id)) {
            $customerdetailsadd->set_where_and(P_Customerdetails_soft::$field_sale_id, SqlOperator::Equals, $salecountData->id);
        }
        if (isset($salecountData->mode)) {
            //更新
            if (isset($salecountData->customer)) $customerdetailsadd->set_customer($salecountData->customer);
            if (isset($salecountData->customer_id)) $customerdetailsadd->set_customer_id($salecountData->customer_id);
            if (isset($salecountData->presales)) $customerdetailsadd->set_presales($salecountData->presales);
            if (isset($salecountData->presales_id)) $customerdetailsadd->set_presales_id($salecountData->presales_id);
            //$customerdetailsadd->set_field_from_array($salecountData);
            $result = $customerdetailsadd->update($db, true);
            if (!$result[0]) die_error(USER_ERROR, '保存失败');
        }else {

            $result = Model::query_list($db, $customerdetailsadd);
            if ($result[count] >= 1) {
                $customerdetailsadd->set_field_from_array($salecountData);
                $result = $customerdetailsadd->update($db, true);
                if (!$result[0]) die_error(USER_ERROR, '保存失败');
            }else {
                //添加
                $customerdetailsadd->set_date("now");
                if (isset($salecountData->id)) $customerdetailsadd->set_sale_id($salecountData->id);
                if (isset($salecountData->ww)) $customerdetailsadd->set_ww($salecountData->ww);
                if (isset($salecountData->qq)) $customerdetailsadd->set_qq($salecountData->qq);
                if (isset($salecountData->money)) $customerdetailsadd->set_money($salecountData->money);
                if (isset($salecountData->presales)) $customerdetailsadd->set_presales($salecountData->presales);
                if (isset($salecountData->presales_id)) $customerdetailsadd->set_presales_id($salecountData->presales_id);
                if (isset($salecountData->customer)) $customerdetailsadd->set_customer($salecountData->customer);
                if (isset($salecountData->customer_id)) $customerdetailsadd->set_customer_id($salecountData->customer_id);
                if (isset($salecountData->payment)) $customerdetailsadd->set_payment($salecountData->payment);
                $customerdetailsadd->set_is_receive(1);
                $customerdetailsadd->set_is_reviews(1);
                $customerdetailsadd->set_is_addto_reviews(1);
                $ok = $customerdetailsadd->insert($db);
                if (!$ok[0]) die_error(USER_ERROR, '添加客户详情失败');
            }
        }
        echo_msg('保存成功');
    }
    //删除
    if ($action == 3) {
        $salecount = new P_Salecount_soft();
        $conflictWith = $salecountData->conflictWith;
        $change_status_sql = "";
        $change_conflict_sql = "";
        $conflict_id = 0;

        $db = create_pdo();
        if ($conflictWith != 0) {
            $salecount->set_where_and(P_Salecount_soft::$field_conflictWith, SqlOperator::Equals, $conflictWith);
            $salecount_list_results = Model::query_list($db, $salecount);
            if (!$salecount_list_results[0]) die_error(USER_ERROR, '重新分配失败,请稍后重试~');
            if ($salecount_list_results['count'] == 1) {
                $change_status_sql = "update P_Salecount_soft SET STATUS = 1 WHERE id =" . $conflictWith;
            }
        } else {
            $salecount->set_where_and(P_Salecount_soft::$field_conflictWith, SqlOperator::Equals, $salecountData->id);
            $salecount_list_results = Model::query_list($db, $salecount);
            $models = Model::list_to_array($salecount_list_results['models']);
            if (count($models) != 0) {
                $conflict_id = $models[0]['id'];
                $change_conflict_sql = "update P_Salecount_soft SET conflictWith = " . $conflict_id . ' WHERE conflictWith = ' . $salecountData->id;
            }
        }
        $salecount->reset();
        $salecount->set_field_from_array($salecountData);
        pdo_transaction($db, function($db) use($salecount, $change_status_sql, $change_conflict_sql) {
            if ($change_status_sql != '') {
                $csresult = Model::execute_custom_sql($db, $change_status_sql);
                if (!$csresult[0]) throw new TransactionException(PDO_ERROR_CODE, '删除失败~' . $cresult['detail_cn'], $cresult);
            }
            if ($change_conflict_sql != '') {
                $wsresult = Model::execute_custom_sql($db, $change_conflict_sql);
                if (!$wsresult[0]) throw new TransactionException(PDO_ERROR_CODE, '删除失败~' . $wsresult['detail_cn'], $wsresult);
            }
            $result = $salecount->delete($db, true);
            if (!$result[0]) throw new TransactionException(PDO_ERROR_CODE, '删除失败~' . $result['detail_cn'], $result);
        });
        if ($use_redis) {
            $employees = get_employees();
            $group_id = $employees[$salecountData->presales_id]['role_id'];
            //更新统计信息-1
            refresh_sale_statistics('decr', $group_id, $salecountData, $redis_config, $threshold);
        }
        echo_result(array("code" => 0, "msg" => "删除成功", "conflict_id" => $conflict_id));
    }
    //分配
    if ($action == 4) {
        $id = request_int('sale_id');
        if (!isset($id)) die_error(USER_ERROR, '操作失败,请稍后重试~');
        $db = create_pdo();
        $salecount = new P_Salecount_soft($id);
        $salecount_result = $salecount->load($db, $salecount);
        if (!$salecount_result[0]) die_error(USER_ERROR, '重新分配失败,请稍后重试~');
        $isQQTeach = $salecount->get_isQQTeach();
        $update_distribution_sql = "";
        $change_status_sql = "";
        $customer = new P_Customerrecord_soft();
        //0:接单上限   1：正常
        $sql = "SELECT pc.id,pc.userid,pc.username,pc.nickname,pc.toplimit,IFNULL(ps.finish,0) AS finish ,pc.`status`,pc.lastDistribution,pc.qqReception,pc.starttime,pc.endtime ";
        $sql .= "FROM P_Customerrecord_soft pc ";
        $sql .= "LEFT JOIN ( ";
        $sql .= "SELECT sa.customer,sa.customer_id, IFNULL(COUNT(sa.customer_id),0) AS finish FROM P_Salecount_soft sa WHERE " . getWhereSql("sa") . " AND sa.customer_id != 0 GROUP BY sa.customer_id ";
        $sql .= ") AS ps ON pc.userid = ps.customer_id ";
        $sql .= "WHERE pc.toplimit > IFNULL(ps.finish,0) AND pc.`status` = 1 ";
        if ($isQQTeach == 1) {//QQ教学 指定分配
            $sql .= " AND pc.qqReception = 1 ";
        }
        $sql .= " ORDER BY pc.lastDistribution ASC ";

        $customerres = Model::query_list($db, $customer, $sql);
        $models = Model::list_to_array($customerres['models']);
        if ($customerres['count'] != 0) {
            $conflictWith = $salecount->get_conflictWith();
            if ($conflictWith != 0) {
                $salecount->reset();
                $salecount->set_where_and(P_Salecount_soft::$field_conflictWith, SqlOperator::Equals, $conflictWith);
                $salecount_list_results = Model::query_list($db, $salecount);
                if (!$salecount_list_results[0]) die_error(USER_ERROR, '重新分配失败,请稍后重试~');
                if ($salecount_list_results['count'] == 1) {
                    $change_status_sql = "update P_Salecount_soft SET STATUS = 1 WHERE id =" . $conflictWith;
                }
            }
            $user = $models[0];
            $userid = $user['userid'];
            $username = $user['username'];
            $nickName = $user['nickname'];
            $toplimit = $user['toplimit'];
            $finish = $user['finish'];
            $salecount->reset();
            $salecount->set_id($id);
            $salecount->set_customer_id($userid);
            $salecount->set_customer($username);
            $salecount->set_nick_name($nickName);
            $salecount->set_status(1);
            $salecount->set_conflictWith(0);
            $update_distribution_sql = "update P_Customerrecord_soft SET lastDistribution = '" . (microtime(TRUE) * 10000) . "' where id = " . $user['id'];
            pdo_transaction($db, function($db) use($salecount, $update_distribution_sql, $change_status_sql) {
                if ($update_distribution_sql != '') {
                    $cresult = Model::execute_custom_sql($db, $update_distribution_sql);
                    if (!$cresult[0]) throw new TransactionException(PDO_ERROR_CODE, '重新分配失败~' . $cresult['detail_cn'], $cresult);
                }
                if ($change_status_sql != '') {
                    $csresult = Model::execute_custom_sql($db, $change_status_sql);
                    if (!$csresult[0]) throw new TransactionException(PDO_ERROR_CODE, '重新分配失败~' . $cresult['detail_cn'], $cresult);
                }
                $result = $salecount->update($db);
                if (!$result[0]) throw new TransactionException(PDO_ERROR_CODE, '重新分配失败~' . $result['detail_cn'], $result);
            });
            echo_msg('分配成功');
        }else {
            die_error(USER_ERROR, '真的没有售后了~');
        }
    }
});

function get_push_array($group_id, $db) {
    $retArray = array('Saler' => '', 'SalerTotals' => '', 'TodayTotals' => '', 'TodayBaiduTotals' => 0, 'Today360Totals' => 0, 'TodaySogouTotals' => 0, 'FirstSaler' => '', 'FirstTotals' => '', 'SecondSaler' => '', 'SecondTotals' => '', 'ThirdSaler' => '', 'ThirdTotals' => '', 'Code' => 0, 'Msg' => '', 'Remark' => '', 'MsgType' => 2);
    $userSalecountSql = "SELECT ps.presales AS Saler, COUNT(ps.presales_id) AS SalerTotals FROM P_Salecount_soft ps WHERE " . getWhereSql("ps") . " AND ps.presales_id = " . $group_id . " GROUP BY ps.presales_id";
    $userSalecountResult = Model::execute_custom_sql($db, $userSalecountSql);
    $userSalecountModel = $userSalecountResult['results'][0];
    $retArray['Saler'] = $userSalecountModel['Saler'];
    $retArray['SalerTotals'] = $userSalecountModel['SalerTotals'];

    $sumCountSql = "SELECT COUNT(*) AS TodayTotals FROM P_Salecount_soft ps WHERE " . getWhereSql("ps");
    $sumCountResult = Model::execute_custom_sql($db, $sumCountSql);
    $sumCountModel = $sumCountResult['results'][0];
    $retArray['TodayTotals'] = $sumCountModel['TodayTotals'];

    $top3CountSql = "SELECT ps2.presales AS Saler, ps2.group_id AS `Group`, count(ps2.presales_id) AS Count FROM P_Salecount_soft AS ps2 WHERE " . getWhereSql("ps2") . " GROUP BY ps2.presales_id ORDER BY COUNT(ps2.presales_id) DESC LIMIT 3";
    $top3CountResult = Model::execute_custom_sql($db, $top3CountSql);
    $top3CountModel = $top3CountResult['results'];
    foreach ($top3CountModel as $id => $model) {
        switch ($id) {
            case 0:
                $retArray['FirstSaler'] = isset($model['Saler']) ? $model['Saler'] : '';
                $retArray['FirstTotals'] = isset($model['Count']) ? $model['Count'] : '';
                break;
            case 1:
                $retArray['SecondSaler'] = isset($model['Saler']) ? $model['Saler'] : '';
                $retArray['SecondTotals'] = isset($model['Count']) ? $model['Count'] : '';
                break;
            case 2:
                $retArray['ThirdSaler'] = isset($model['Saler']) ? $model['Saler'] : '';
                $retArray['ThirdTotals'] = isset($model['Count']) ? $model['Count'] : '';
                break;
        }
    }
    return $retArray;
}

function refresh_sale_statistics($incr_or_decr = 'incr', $group_id, $salecountData, $redis_config, $threshold) {
    $redis = new Redis();
    $redis_connected = $redis->pconnect($redis_config['host'], $redis_config['port']);
    if ($redis_connected === true) {
        $date_adjusted_format = adjust_time('Y_m_d', $threshold);
        $is_today = str_equals($date_adjusted_format, date('Y_m_d', strtotime($salecountData->addtime)));
        $date_adjusted_format .= '_';
        $group_name = $salecountData->channel;

        $sale_statistics_array = array('旺旺' => 'TodayWWTotalsSoft', '百度' => 'TodayBDTotalsSoft', '360' => 'Today360TotalsSoft', 'UC神马' => 'TodayUCTotalsSoft', '搜狗' => 'TodaySGTotalsSoft', '优化站' => 'TodayYHZTotalsSoft');
        $sale_statistics_timely_array = array('旺旺' => 'TodayWWTimelyTotalsSoft', '百度' => 'TodayBDTimelyTotalsSoft', '360' => 'Today360TimelyTotalsSoft', 'UC神马' => 'TodayUCTimelyTotalsSoft', '搜狗' => 'TodaySGTimelyTotalsSoft', '优化站' => 'TodayYHZTimelyTotalsSoft');

        $sale_statistics_key = $sale_statistics_array[$group_name];
        if (str_equals($incr_or_decr, 'decr')) {
            if ($is_today && $redis->get($date_adjusted_format . $sale_statistics_key) > 0) {
                $redis->decr($date_adjusted_format . $sale_statistics_key);
                $redis->decr($date_adjusted_format . 'TodayTotalsSoft');
            }
        } else {
            $redis->incr($date_adjusted_format . $sale_statistics_key);
            $redis->incr($date_adjusted_format . 'TodayTotalsSoft');
        }
        if ($salecountData->isTimely == 1) {
            $sale_statistics_timely_key = $sale_statistics_timely_array[$group_name];
            if (str_equals($incr_or_decr, 'decr')) {
                if ($is_today && $redis->get($date_adjusted_format . $sale_statistics_timely_key) > 0) {
                    $redis->decr($date_adjusted_format . $sale_statistics_timely_key);
                    $redis->decr($date_adjusted_format . 'TodayTotalsTimelySoft');
                }
            } else {
                $redis->incr($date_adjusted_format . $sale_statistics_timely_key);
                $redis->incr($date_adjusted_format . 'TodayTotalsTimelySoft');
            }
        }
    }
}
