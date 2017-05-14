<?php

/**
 * 售前排行榜
 *
 * @author bocheng
 * @copyright 2015 非时序
 * @version 2015/12/20
 */
use Models\Base\Model;
use Models\Base\SqlOperator;
use Models\P_GenerationOperation;
use Models\P_Customerrecord_second;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {

    $searchTime = request_string("searchTime");
    $searchStartTime = request_string("searchStartTime");
    $searchEndTime = request_string("searchEndTime");
    $page = request_pageno();
    $maxpersize = request_pagesize();
    $timemonth = request_string("timemonth");
    $timeyear = request_string("timeyear");
    $inneraction = request_string("inneraction"); //{1;"上一年",2:"上一月";3:"下一月";4:"下一年"}

    $onlineDate = ONLINEDATE;

    if ($maxpersize == 20) {
        $maxpersize = 60;
    }
    if ($action == 1) { //获取排行榜
        $timerange = '';
        if (!isset($timemonth) && !isset($timeyear)) {
            $timemonth = date('Y-m-d');
            $timeyear = date('Y-m-d');
        }
        $db = create_pdo();
        $customer = new P_Customerrecord_second();
        $customer->set_custom_where("AND type=1");
        $result = Model::query_list($db, $customer, NULL, TRUE); //查询出售前名单
        if (!$result[0])
            die_error(USER_ERROR, '获取售前排行榜失败');
        $models_user = Model::list_to_array($result['models']);
        $dateArray = explode("-", $timemonth);

        $onlineArray = explode("-", $onlineDate);

        $strsql = "select qq,platform_sales,payment_amount,payType from P_GenerationOperation where 1=1"; //查询代运营业绩
        $strsql_receptflow = 'select rception_staff,count(*) from p_flow where rception_staff is not null'; //查询流量业绩接待数
        $strsql_yj = 'select platform_sales,sum(payment_amount) payment_amount from p_generationoperation where 1 = 1'; //代运营定金业绩表
        $receptionsql = "select sum(rception_money) rception_money,platform_sales from p_generationoperation where payType=0 "; //查询接待金额
        $totalSql = "SELECT SUM(payment_amount) payment_amount,platform_sales FROM p_generationoperation where 1 = 1"; //综合业绩
        $secondsql = "select SUM(play_price) play_price,platform_rception from p_fills_second where second_type <> '代运营补欠款' ";
        $recepsql = "select SUM(paymoney) paymoney,rception_staff from p_flow where 1 = 1 ";
        $secondbk = "select SUM(play_price) play_price,platform_rception from p_fills_second where second_type = '代运营补欠款' ";

        if (!empty($inneraction)) {
            if ($inneraction == 1) {
                $searchEndTime = date('Y-12-31', strtotime('-1 year', strtotime($timeyear)));
                $timeyear = date('Y-01-01', strtotime('-1 year', strtotime($timeyear)));
                $searchStartTime = $timeyear;
                $strsql.=" and DATE_FORMAT(add_time,'%Y')=DATE_FORMAT('$timeyear','%Y')";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time,'%Y')=DATE_FORMAT('$timeyear','%Y')";
                $strsql_yj.=" and DATE_FORMAT(add_time,'%Y')=DATE_FORMAT('$timeyear','%Y')";
            } else if ($inneraction == 2) {
                $timemonth = date('Y-m-01', strtotime($timemonth));
                $searchEndTime = date('Y-m-d', strtotime("$timemonth -1 day"));
                $timemonth = date('Y-m-01', strtotime('-1 month', strtotime($timemonth)));
                $searchStartTime = $timemonth;
                $strsql.=" and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $receptionsql .= " and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $strsql_yj .= " and DATE_FORMAT(add_time,'%Y-%m')=date_format('$timemonth','%Y-%m') and (payType = 0 or payType = 2) GROUP BY platform_sales";
                $totalSql .= " and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $secondsql .= " and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $secondbk .= " and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $recepsql .= " and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
            } else if ($inneraction == 3) {
                $timemonth = date('Y-m-01', strtotime('+1 month', strtotime($timemonth)));
                $searchStartTime = $timemonth;
                $searchEndTime = date('Y-m-d', strtotime("$timemonth +1 month"));
                $searchEndTime = date('Y-m-d', strtotime("$searchEndTime -1 day"));
                $strsql.=" and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $receptionsql .= " and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $strsql_yj .= " and DATE_FORMAT(add_time,'%Y-%m')=date_format('$timemonth','%Y-%m') and (payType = 0 or payType = 2) GROUP BY platform_sales";
                $totalSql .= " and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $secondsql .= " and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $secondbk .= " and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $recepsql .= " and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
            } else if ($inneraction == 4) {
                $searchEndTime = date('Y-12-31', strtotime('+1 year', strtotime($timeyear)));
                $timeyear = date('Y-01-01', strtotime('+1 year', strtotime($timeyear)));
                $searchStartTime = $timeyear;
                $strsql.=" and DATE_FORMAT(add_time,'%Y')=DATE_FORMAT('$timeyear','%Y')";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time,'%Y')=DATE_FORMAT('$timeyear','%Y')";
                $strsql_yj.=" and DATE_FORMAT(add_time,'%Y')=DATE_FORMAT('$timeyear','%Y')";
            }
            $timerange = $searchStartTime . '到' . $searchEndTime;
        } else {
            if (isset($searchTime)) {
                exit($searchTime);
                $strsql.=" and  DATE_FORMAT(add_time,'%Y-%m-%d')='$searchTime'";
                $strsql_receptflow.=" and  DATE_FORMAT(allot_time,'%Y-%m-%d')='$searchTime'";
                $strsql_yj.=" and  DATE_FORMAT(add_time,'%Y-%m-%d')='$searchTime'";
                $timerange = $searchTime;
            } else {
                if (isset($searchStartTime)) {
                    $formatStr = '%Y-%m-%d';
                    if (strlen($searchStartTime) > 10) {
                        $formatStr = "%Y-%m-%d %H:%i";
                    }
                    $strsql.=" and DATE_FORMAT(add_time, '" . $formatStr . "') >= '" . $searchStartTime . "' ";
                    $strsql_receptflow.=" and DATE_FORMAT(allot_time, '" . $formatStr . "') >= '" . $searchStartTime . "' ";
                    $strsql_yj.=" and DATE_FORMAT(add_time, '" . $formatStr . "') >= '" . $searchStartTime . "' ";
                    $timerange = $searchStartTime . "到";
                    if (isset($searchEndTime)) {
                        $timerange.=$searchEndTime;
                    } else
                        $timerange.='现在';
                }
                if (isset($searchEndTime)) {
                    $formatStr = '%Y-%m-%d';
                    if (strlen($searchEndTime) > 10) {
                        $formatStr = "%Y-%m-%d %H:%i";
                    }
                    $strsql.=" and DATE_FORMAT(add_time, '" . $formatStr . "') <= '" . $searchEndTime . "' ";
                    $strsql_receptflow.=" and DATE_FORMAT(allot_time, '" . $formatStr . "') <= '" . $searchEndTime . "' ";
                    $strsql_yj.=" and DATE_FORMAT(add_time, '" . $formatStr . "') <= '" . $searchEndTime . "' ";
                    if (isset($searchStartTime)) {
                        $timerange = $searchStartTime . "至" . $searchEndTime;
                    } else {
                        $timerange = '截止至' . $searchEndTime;
                    }
                }
                if (empty($searchTime) && empty($searchStartTime) && empty($searchEndTime)) {
                    $strsql.=" and DATE_FORMAT(add_time,'%Y-%m')=date_format(now(),'%Y-%m')";
                    $strsql_receptflow.=" and DATE_FORMAT(allot_time,'%Y-%m')=date_format(now(),'%Y-%m')";
                    $strsql_yj .= " and DATE_FORMAT(add_time,'%Y-%m')=date_format(now(),'%Y-%m') and (payType = 0 or payType = 2) GROUP BY platform_sales";
                    $receptionsql .= " and DATE_FORMAT(add_time,'%Y-%m')=date_format(now(),'%Y-%m')";
                    $totalSql .= " and DATE_FORMAT(add_time,'%Y-%m')=date_format(now(),'%Y-%m')";
                    $secondsql .= " and DATE_FORMAT(add_time,'%Y-%m')=date_format(now(),'%Y-%m')";
                    $secondbk .= " and DATE_FORMAT(add_time,'%Y-%m')=date_format(now(),'%Y-%m')";
                    $recepsql .= " and DATE_FORMAT(add_time,'%Y-%m')=date_format(now(),'%Y-%m')";

                    $timerange = '本月';
                }
            }
        }

        $strsql_receptflow.=" GROUP BY rception_staff";

        $receptionsql .= " GROUP BY platform_sales";
        $totalSql .= " GROUP BY platform_sales";
        $secondsql .= " GROUP BY platform_rception";
        $secondbk .= " GROUP BY platform_rception";
        $recepsql .= " GROUP BY rception_staff";

        $result_gen = $db->query($strsql);
        $result_gen = $result_gen->fetchAll(PDO::FETCH_ASSOC);
        $result_receptflow = $db->query($strsql_receptflow);
        $result_receptflow = $result_receptflow->fetchAll(PDO::FETCH_ASSOC);
        $result_yj = $db->query($strsql_yj);
        $result_yj = $result_yj->fetchAll(PDO::FETCH_ASSOC);
        $result_reception = $db->query($receptionsql);
        $result_reception = $result_reception->fetchAll(PDO::FETCH_ASSOC);
        $result_total = $db->query($totalSql);
        $result_newtotal = $result_total->fetchAll(PDO::FETCH_ASSOC);
        $result_second = $db->query($secondsql);
        $result_newsecond = $result_second->fetchAll(PDO::FETCH_ASSOC);

        $result_secondbk = $db->query($secondbk);
        $result_newsecondbk = $result_secondbk->fetchAll(PDO::FETCH_ASSOC);

        $result_recep = $db->query($recepsql);
        $result_newrecep = $result_recep->fetchAll(PDO::FETCH_ASSOC);

        $arr_rank = array();
        for ($i = 0; $i < count($models_user); $i++) {
            $arr_rank[$i]['userName'] = $models_user[$i]['username']; //平台接待
            $arr_rank[$i]['userid'] = $models_user[$i]['userid']; //平台接待id
            $arr_rank[$i]['receptCount'] = 0; //接待数
            if ($result_receptflow[0]) {
                for ($j = 0; $j < count($result_receptflow); $j++) {
                    if ($arr_rank[$i]['userName'] == $result_receptflow[$j]['rception_staff']) {
                        $arr_rank[$i]['receptCount'] = $arr_rank[$i]['receptCount'] + $result_receptflow[$j]['count(*)'];
                        break;
                    }
                }
            }
            $arr_rank[$i]['dealCount'] = 0; //成交数
            $arr_rank[$i]['firstMoney'] = 0; //定金金额
            $arr_rank[$i]['totalMoney'] = 0; //综合业绩(代运营定金+代运营补款)只统计payType等于0和2的
            $arr_rank[$i]['rceptMoney'] = 0; //接待业绩
            $arr_rank[$i]['allMoney'] = 0;   //二销业绩
            $arr_rank[$i]['fill_bk'] = 0;   //补款业绩

            if ($result_gen[0]) {
                $sql_qq = '|';
                for ($j = 0; $j < count($result_gen); $j++) {
                    if (trim($arr_rank[$i]['userName']) == trim($result_gen[$j]['platform_sales'])) {
                        //当payType=0时的判断操作
                        if ($result_gen[$j]['payType'] == 0 || $result_gen[$j]['payType'] == 0) {
                            if (strpos($sql_qq, trim($result_gen[$j]['qq'])) === FALSE) {
                                $arr_rank[$i]['dealCount'] = $arr_rank[$i]['dealCount'] + 1;
                                $sql_qq = $sql_qq . trim($result_gen[$j]['qq']) . '|';
                            }
//                            $arr_rank[$i]['totalMoney'] = $arr_rank[$i]['totalMoney'] + $result_gen[$j]['payment_amount'];
                        }
                    }
                }
            }

            if ($result_reception[0]) {
                for ($j = 0; $j < count($result_reception); $j++) {
                    if (trim($arr_rank[$i]['userName']) == trim($result_reception[$j]['platform_sales'])) {
//                        $arr_rank[$i]['firstMoney'] = $arr_rank[$i]['firstMoney'] + $result_yj[$j]['rception_money'];
                        $arr_rank[$i]['totalMoney'] = $arr_rank[$i]['totalMoney'] + $result_reception[$j]['rception_money'];
                    }
                }
            }

            if ($result_newsecondbk[0]) {
                for ($m = 0; $m < count($result_newsecond); $m++) {
                    if (trim($arr_rank[$i]['userName']) == trim($result_newsecondbk[$m]['platform_rception'])) {
                        $arr_rank[$i]['fill_bk'] = $arr_rank[$i]['fill_bk'] + $result_newsecondbk[$m]['play_price'];
                    }
                }
            }

            if ($result_newsecond[0]) {
                for ($m = 0; $m < count($result_newsecond); $m++) {
                    if (trim($arr_rank[$i]['userName']) == trim($result_newsecond[$m]['platform_rception'])) {
                        $arr_rank[$i]['allMoney'] = $arr_rank[$i]['allMoney'] + $result_newsecond[$m]['play_price'];
                    }
                }
            }

            if ($result_newrecep[0]) {
                for ($z = 0; $z < count($result_newrecep); $z++) {
                    if (trim($arr_rank[$i]['userName']) == trim($result_newrecep[$z]['rception_staff'])) {
                        $arr_rank[$i]['rceptMoney'] = $arr_rank[$i]['rceptMoney'] + $result_newrecep[$z]['paymoney'];
                    }
                }
            }

            if ($result_yj[0]) {
                for ($j = 0; $j < count($result_yj); $j++) {
                    if (trim($arr_rank[$i]['userName']) == trim($result_yj[$j]['platform_sales'])) {
                        $arr_rank[$i]['firstMoney'] = $arr_rank[$i]['firstMoney'] + $result_yj[$j]['payment_amount'];
//                        $arr_rank[$i]['totalMoney'] = $arr_rank[$i]['totalMoney'] + $result_yj[$j]['pay_money'];
                    }
                }
            }
            $english_format_number = 0;
            $arr_rank[$i]['conversion'] = '0.00%'; //转化率
            if ($arr_rank[$i]['receptCount'] != 0 && $arr_rank[$i]['dealCount'] != 0) {
                $english_format_number = number_format(($arr_rank[$i]['dealCount'] / $arr_rank[$i]['receptCount']) * 100, 2, '.', '');
                $arr_rank[$i]['conversion'] = (string) $english_format_number . "%";
            }
            $arr_rank[$i]['perform'] = $english_format_number / 100 * $arr_rank[$i]['firstMoney'];
        }
        $arr_sort1 = arr_sort($arr_rank, 'firstMoney', 'desc');
        $total_count = count($arr_sort1);
        $i = 1;
        $arr_sort = [];
        foreach ($arr_sort1 as $row) {
            $row['index'] = $i;
            $row['time'] = $timerange;
            $arr_sort[] = $row;
            $i++;
        }
        $max_page_no = ceil($total_count / $maxpersize);
        $arr_sort = array_slice($arr_sort, ($page - 1) * $maxpersize, $maxpersize);
        $totalmoney = 0;
        foreach ($arr_sort as $row) {
            $totalmoney+=$row['firstMoney'];
        }
        $result = array('searchStartTime' => $searchStartTime, 'searchEndTime' => $searchEndTime, 'timemonth' => $timemonth, 'timeyear' => $timeyear, 'totalmoney' => $totalmoney, 'total_count' => $total_count, "list" => $arr_sort, 'page_no' => $page, 'max_page_no' => $max_page_no, 'code' => 0, 'page_size' => $maxpersize);
        exit(get_response($result));
    }

    if ($action == 11) {
        $startTime = request_datetime("start_time");
        $endTime = request_datetime("end_time");
        $export = new ExportData2Excel();
        $fills = new P_Fills();
        if (isset($startTime)) {
            $fills->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') >= '" . $startTime . "' ");
        }
        if (isset($endTime)) {
            $fills->set_custom_where(" and DATE_FORMAT(add_time, '%Y-%m-%d') <= '" . $endTime . "' ");
        }
        $field = array('add_time', 'ww', 'name', 'mobile', 'fill_sum', 'payment', 'channel', 'customer', 'customer2', 'add_name');
        $fills->set_query_fields($field);
        $db = create_pdo();
        $result = Model::query_list($db, $fills, NULL, true);
        if (!$result[0]) {
            $export->create(array('导出错误'), array(array('补欠款数据导出失败,请稍后重试!')), "补欠款数据导出", "补欠款");
        }
        $models = Model::list_to_array($result['models']);
        $title_array = array('日期', '旺旺号', '真实姓名', '手机号', '补欠金额', '收款方式', '接入渠道', '售后老师', '更换老师', '添加人');
        $export->set_field($field);
        $export->create($title_array, $models, "补欠款数据导出", "补欠款");
    }
});
