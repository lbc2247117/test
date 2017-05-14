<?php

/**
 * 售后业绩排行榜
 *
 * @author bocheng
 * @copyright 2015 非时序
 * @version 2015/12/25
 */
use Models\Base\Model;
use Models\Base\SqlOperator;
use Models\P_GenerationOperation;
use Models\P_Fills_second;
use Models\P_Customerrecord_second;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';
require '../../Helper/CommonDal.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    $searchTime = request_string("searchTime");
    $searchStartTime = request_string("searchStartTime");
    $searchEndTime = request_string("searchEndTime");
    $page = request_pageno();
    $maxpersize = request_pagesize();
    $timemonth = request_string("timemonth");
    $timeyear = request_string("timeyear");
    $inneraction = request_string("inneraction"); //{1;"上一年",2:"下一年";3:"上一月";4:"下一月"}
    if ($maxpersize == 20) {
        $maxpersize = 40;
    }
    //查询业绩
    if ($action == 1) {
        $timerange = '';
        if (!isset($timemonth) && !isset($timeyear)) {
            $timemonth = date('Y-m-d');
            $timeyear = date('Y-m-d');
        }

        $onlineDate = ONLINEDATE;
        $nowDateArray = explode("-", $timemonth);
        $onlineDateArray = explode("-", $onlineDate);

        $db = create_pdo();
        $customer = new P_Customerrecord_second();
        $customer->set_custom_where("AND type=5");
        $result = Model::query_list($db, $customer, NULL, TRUE);
        if (!$result[0])
            die_error(USER_ERROR, '获取售后排行榜失败');
        $models_user = Model::list_to_array($result['models']);

        $strsql_after = "select headmaster,play_price,qq,customer_type,second_type from p_fills_second where 1=1";
        $strsql_receptflow = "select headmaster,count(distinct qq) from P_GenerationOperation where payType=0  and headmaster is not null";
        $str_totalreceptmoney = 'SELECT count(distinct qq),headmaster from p_generationoperation where payType=0 GROUP BY headmaster'; //查询历史接待数
        $str_totalSaleMoney = 'SELECT sum(play_price),headmaster from p_fills_second GROUP BY headmaster'; //查询历史销售额
        $second_version = "select SUM(play_price) as total,qq,headmaster from p_fills_second where 1 =1 ";
        $second_qq = "select qq from p_second_qqversion";
        $receptflow_new_reception = "select headmaster,sum(rception_money) rception_money from P_GenerationOperation where 1 = 1";
        $receptflow_new_payment = "select headmaster,sum(payment_amount) payment_amount from P_GenerationOperation where 1 = 1";
        $receptflow_new_final = "select headmaster,sum(final_money) final_money from P_GenerationOperation where 1 = 1";
        if (!empty($inneraction)) {
            if ($inneraction == 1) {
                $searchEndTime = date('Y-12-31', strtotime('-1 year', strtotime($timeyear)));
                $timeyear = date('Y-01-01', strtotime('-1 year', strtotime($timeyear)));
                $searchStartTime = $timeyear;
                $strsql_after.=" and DATE_FORMAT(add_time,'%Y')=DATE_FORMAT('$timeyear','%Y')";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time,'%Y')=DATE_FORMAT('$timeyear','%Y')";
                $second_version .= " and DATE_FORMAT(add_time,'%Y') = DATE_FORMAT('$timeyear','%Y') group by qq";
            } else if ($inneraction == 2) {
                $timemonth = date('Y-m-01', strtotime($timemonth));
                $searchEndTime = date('Y-m-d', strtotime("$timemonth -1 day"));
                $timemonth = date('Y-m-01', strtotime('-1 month', strtotime($timemonth)));
                $searchStartTime = $timemonth;
                $newNowArray = explode("-", $timemonth);
                $strsql_after.=" and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $second_version .= " and DATE_FORMAT(add_time,'%Y-%m') = DATE_FORMAT('$timemonth','%Y-%m') group by qq";
                $receptflow_new_reception .= " and payType = 0 and DATE_FORMAT(add_time,'%Y-%m') = DATE_FORMAT('$timemonth','%Y-%m') GROUP BY headmaster";
                //售后补款金额
                $receptflow_new_final .= " and payType = 1 and DATE_FORMAT(add_time,'%Y-%m') = DATE_FORMAT('$timemonth','%Y-%m') GROUP BY headmaster";
                //当查询的日期的年份与新旧数据分割的时间节点
                if ($newNowArray[1] == $onlineDateArray[1] && $newNowArray[0] == $onlineDateArray[0]) {
                    $receptflow_new_payment = "select headmaster,sum(payment_amount) payment_amount from P_GenerationOperation where 1 = 1 and (payType = 0 or payType = 2) and DATE_FORMAT(add_time,'%Y-%m') = DATE_FORMAT('$timemonth','%Y-%m') and add_time < '$onlineDate' GROUP BY headmaster 
UNION
select result.headmaster,SUM(re.pay_money) from ( SELECT headmaster,id  gpid from p_generationoperation where (payType = 0 or payType = 2) and DATE_FORMAT(add_time,'%Y-%m')= 
DATE_FORMAT('$timemonth','%Y-%m') and add_time > '$onlineDate' GROUP BY headmaster) as result inner JOIN receiver_payaccount re on result.gpid = re.general_operal_id";
                } else if ($newNowArray[0] < $onlineDateArray[0] || ($newNowArray[0] == $onlineDateArray[0] && $newNowArray[1] < $onlineDateArray[1])) {
                    $receptflow_new_payment .= " and (payType = 0 or payType = 2) and headmaster is not null and DATE_FORMAT(add_time,'%Y-%m')=date_format('$timemonth','%Y-%m')  GROUP BY headmaster";
                } else {
                    $receptflow_new_payment = "select result.headmaster,SUM(re.pay_money) payment_amount from ( SELECT headmaster,id  gpid from p_generationoperation where (payType = 0 or payType = 2) and DATE_FORMAT(add_time,'%Y-%m')= DATE_FORMAT('$timemonth','%Y-%m') GROUP BY headmaster) as result inner JOIN receiver_payaccount re on result.gpid = re.general_operal_id";
                }
                $timerange = $searchEndTime . '到' . $searchEndTime;
            } else if ($inneraction == 3) {
                $timemonth = date('Y-m-01', strtotime('+1 month', strtotime($timemonth)));
                $searchStartTime = $timemonth;
                $searchEndTime = date('Y-m-d', strtotime("$timemonth +1 month"));
                $searchEndTime = date('Y-m-d', strtotime("$searchEndTime -1 day"));
                $newNowArray = explode("-", $timemonth);
                $strsql_after.=" and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $second_version .= " and DATE_FORMAT(add_time,'%Y-%m') = DATE_FORMAT('$timemonth','%Y-%m') group by qq";

                $receptflow_new_reception .= " and payType = 0 and DATE_FORMAT(add_time,'%Y-%m') = DATE_FORMAT('$timemonth','%Y-%m') GROUP BY headmaster";
                //售后补款金额
                $receptflow_new_final .= " and payType = 1 and DATE_FORMAT(add_time,'%Y-%m') = DATE_FORMAT('$timemonth','%Y-%m') GROUP BY headmaster";
                //当查询的日期的年份与新旧数据分割的时间节点
                if ($newNowArray[1] == $onlineDateArray[1] && $newNowArray[0] == $onlineDateArray[0]) {
                    $receptflow_new_payment = "select headmaster,sum(payment_amount) payment_amount from P_GenerationOperation where 1 = 1 and (payType = 0 or payType = 2) and DATE_FORMAT(add_time,'%Y-%m') = DATE_FORMAT('$timemonth','%Y-%m') and add_time < '$onlineDate' GROUP BY headmaster 
UNION
select result.headmaster,SUM(re.pay_money) from ( SELECT headmaster,id  gpid from p_generationoperation where (payType = 0 or payType = 2) and DATE_FORMAT(add_time,'%Y-%m')= 
DATE_FORMAT('$timemonth','%Y-%m') and add_time > '$onlineDate' GROUP BY headmaster) as result inner JOIN receiver_payaccount re on result.gpid = re.general_operal_id";
                } else if ($newNowArray[0] < $onlineDateArray[0] || ($newNowArray[0] == $onlineDateArray[0] && $newNowArray[1] < $onlineDateArray[1])) {
                    $receptflow_new_payment .= " and (payType = 0 or payType = 2) and headmaster is not null and DATE_FORMAT(add_time,'%Y-%m')=date_format('$timemonth','%Y-%m')  GROUP BY headmaster";
                } else {
                    $receptflow_new_payment = "select result.headmaster,SUM(re.pay_money) payment_amount from ( SELECT headmaster,id  gpid from p_generationoperation where (payType = 0 or payType = 2) and DATE_FORMAT(add_time,'%Y-%m')= DATE_FORMAT('$timemonth','%Y-%m') GROUP BY headmaster) as result inner JOIN receiver_payaccount re on result.gpid = re.general_operal_id";
                }
            } else if ($inneraction == 4) {
                $searchEndTime = date('Y-12-31', strtotime('+1 year', strtotime($timeyear)));
                $timeyear = date('Y-01-01', strtotime('+1 year', strtotime($timeyear)));
                $searchStartTime = $timeyear;
                $strsql_after.=" and DATE_FORMAT(add_time,'%Y')=DATE_FORMAT('$timeyear','%Y')";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time,'%Y')=DATE_FORMAT('$timeyear','%Y')";
                $second_version .= " and DATE_FORMAT(add_time,'%Y') = DATE_FORMAT('$timeyear','%Y') group by qq";
            }
            $timerange = $searchStartTime . '到' . $searchEndTime;
        } else {
            if (isset($searchTime)) {
                $strsql_after.=" and  DATE_FORMAT(add_time,'%Y-%m-%d')='$searchTime'";

                $timerange = $searchTime;
            }
            if (isset($searchStartTime)) {
                $formatStr = '%Y-%m-%d';
                if (strlen($searchStartTime) > 10) {
                    $formatStr = "%Y-%m-%d %H:%i";
                }
                $strsql_after.=" and DATE_FORMAT(add_time, '" . $formatStr . "') >= '" . $searchStartTime . "' ";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time, '" . $formatStr . "') >= '" . $searchStartTime . "' ";

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
                $strsql_after.=" and DATE_FORMAT(add_time, '" . $formatStr . "') <= '" . $searchEndTime . "' ";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time, '" . $formatStr . "') <= '" . $searchEndTime . "' ";

                if (isset($searchStartTime)) {
                    $timerange = $searchStartTime . "至" . $searchEndTime;
                } else {
                    $timerange = '截止至' . $searchEndTime;
                }
            }
            if (empty($searchTime) && empty($searchStartTime) && empty($searchEndTime)) {
                $strsql_after.=" and DATE_FORMAT(add_time,'%Y-%m')=date_format(now(),'%Y-%m')";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time,'%Y-%m')=date_format(now(),'%Y-%m')";
                $timerange = '本月';
                $second_version .= " and DATE_FORMAT(add_time,'%Y-%m') = DATE_FORMAT('$timemonth','%Y-%m') group by qq";
                //接待金额
                $receptflow_new_reception .= " and payType = 0 and DATE_FORMAT(add_time,'%Y-%m') = DATE_FORMAT('$timemonth','%Y-%m') GROUP BY headmaster";
                //售后补款金额
                $receptflow_new_final .= " and payType = 1 and DATE_FORMAT(add_time,'%Y-%m') = DATE_FORMAT('$timemonth','%Y-%m') GROUP BY headmaster";
                //当查询的日期的年份与新旧数据分割的时间节点
                if ($newNowArray[1] == $onlineDateArray[1] && $newNowArray[0] == $onlineDateArray[0]) {
                    $receptflow_new_payment = "select headmaster,sum(payment_amount) payment_amount from P_GenerationOperation where 1 = 1 and (payType = 0 or payType = 2) and DATE_FORMAT(add_time,'%Y-%m') = DATE_FORMAT('$timemonth','%Y-%m') and add_time < '$onlineDate' GROUP BY headmaster 
UNION
select result.headmaster,SUM(re.pay_money) from ( SELECT headmaster,id  gpid from p_generationoperation where (payType = 0 or payType = 2) and DATE_FORMAT(add_time,'%Y-%m')= 
DATE_FORMAT('$timemonth','%Y-%m') and add_time > '$onlineDate' GROUP BY headmaster) as result inner JOIN receiver_payaccount re on result.gpid = re.general_operal_id";
                } else if ($newNowArray[0] < $onlineDateArray[0] || ($newNowArray[0] == $onlineDateArray[0] && $newNowArray[1] < $onlineDateArray[1])) {
                    $receptflow_new_payment .= " and (payType = 0 or payType = 2) and headmaster is not null and DATE_FORMAT(add_time,'%Y-%m')=date_format('$timemonth','%Y-%m')  GROUP BY headmaster";
                } else {
                    $receptflow_new_payment = "select result.headmaster,SUM(re.pay_money) payment_amount from ( SELECT headmaster,id  gpid from p_generationoperation where (payType = 0 or payType = 2) and DATE_FORMAT(add_time,'%Y-%m')= DATE_FORMAT('$timemonth','%Y-%m') GROUP BY headmaster) as result inner JOIN receiver_payaccount re on result.gpid = re.general_operal_id";
                }
            }
        }

        $strsql_receptflow.=" GROUP BY headmaster";
        $result_after = $db->query($strsql_after);
        $result_after = $result_after->fetchAll(PDO::FETCH_ASSOC);
        $result_receptflow = $db->query($strsql_receptflow);
        $result_receptflow = $result_receptflow->fetchAll(PDO::FETCH_ASSOC);
        $result_totalrecept = $db->query($str_totalreceptmoney);
        $result_totalrecept = $result_totalrecept->fetchAll(PDO::FETCH_ASSOC);
        $result_totalsale = $db->query($str_totalSaleMoney);
        $result_totalsale = $result_totalsale->fetchAll(PDO::FETCH_ASSOC);
        //二销转化率的数据集合
        $second_version_result = $db->query($second_version);
        $second_version_list = $second_version_result->fetchAll(PDO::FETCH_ASSOC);
        //已经计算过二销转化率的qq
        $second_resultqq = $db->query($second_qq);
        $second_qqList = $second_resultqq->fetchAll(PDO::FETCH_ASSOC);
        //得到已经记录了二销转化率的客户qq列表
        $newSecondQqList = array();
        foreach ($second_qqList as $key => $val) {
            $newSecondQqList[] = $val["qq"];
        }
        //接待金额
        $result_receptflow_new = $db->query($receptflow_new_reception);
        $result_new_recept = $result_receptflow_new->fetchAll(PDO::FETCH_ASSOC);
        //预付金额
        $result_payflow_new = $db->query($receptflow_new_payment);
        $result_new_payment = $result_payflow_new->fetchAll(PDO::FETCH_ASSOC);
        //售后补款
        $result_finalflow_new = $db->query($receptflow_new_final);
        $result_new_final = $result_finalflow_new->fetchAll(PDO::FETCH_ASSOC);

        $arr_rank = array();
        for ($i = 0; $i < count($models_user); $i++) {
            $arr_rank[$i]['username'] = $models_user[$i]['username'];
            $arr_rank[$i]['userid'] = $models_user[$i]['userid'];
            //获取新流程欠款和接待数

            $arr_rank[$i]['recept_countflow'] = 0; //接待数
            if ($result_receptflow[0]) {
                for ($j = 0; $j < count($result_receptflow); $j++) {
                    if ($arr_rank[$i]['username'] == $result_receptflow[$j]['headmaster']) {
                        $arr_rank[$i]['recept_countflow'] = $result_receptflow[$j]['count(distinct qq)'];
//                        $arr_rank[$i]['fill_moneyflow'] = $result_receptflow[$j]['sum(final_money)'];
//                        $arr_rank[$i]['recept_moneyflow'] = $result_receptflow[$j]['sum(rception_money)'];
                        break;
                    }
                }
            }
            //获取业绩相关数据
            $arr_rank[$i]['after_moneyflow'] = 0; //补款金额
            $arr_rank[$i]['pay_countflow'] = 0; //补款数
            $arr_rank[$i]['second_moneyflow'] = 0; //VIP
            $arr_rank[$i]['second_countflow'] = 0; //二销数
            $arr_rank[$i]['vipclass'] = 0; //课程
            $arr_rank[$i]['zhuangxiu'] = 0; //装修
            $arr_rank[$i]['huodong'] = 0; //活动
            $arr_rank[$i]['huoyuan'] = 0; //货源
            $arr_rank[$i]['totalmoney'] = 0; //合计（补款+二销）
            $str_qqlflow = "";
            $str_secondqqflow = "";
            if ($result_after[0]) {
                for ($j = 0; $j < count($result_after); $j++) {
                    if (trim($arr_rank[$i]['username']) === trim($result_after[$j]['headmaster'])) {
                        $arr_rank[$i]['totalmoney'] = $arr_rank[$i]['totalmoney'] + $result_after[$j]['play_price'];
                        if ($result_after[$j]['second_type'] == '代运营补欠款') {
//                            $arr_rank[$i]['after_moneyflow'] = $arr_rank[$i]['after_moneyflow'] + $result_after[$j]['play_price'];
                            if (strpos($str_qqlflow, $result_after[$j]['qq']) === FALSE) {
                                $str_qqlflow.=$result_after[$j]['qq'];
                                $arr_rank[$i]['pay_countflow'] = $arr_rank[$i]['pay_countflow'] + 1;
                            }
                        } else {
                            if (strpos($str_secondqqflow, $result_after[$j]['qq']) === FALSE) {
                                $str_secondqqflow.=$result_after[$j]['qq'];
                                $arr_rank[$i]['second_countflow'] = $arr_rank[$i]['second_countflow'] + 1;
                            }
                            if (stripos($result_after[$j]['second_type'], '课程') !== FALSE) {
                                $arr_rank[$i]['vipclass']+= $result_after[$j]['play_price'];
                            } else if (stripos($result_after[$j]['second_type'], '活动') !== FALSE) {
                                $arr_rank[$i]['huodong']+= $result_after[$j]['play_price'];
                            } else if (stripos($result_after[$j]['second_type'], '装修') !== FALSE) {
                                $arr_rank[$i]['zhuangxiu']+= $result_after[$j]['play_price'];
                            } else if (stripos($result_after[$j]['second_type'], '货源') !== FALSE) {
                                $arr_rank[$i]['huoyuan']+= $result_after[$j]['play_price'];
                            } else {
                                $arr_rank[$i]['second_moneyflow'] = $arr_rank[$i]['second_moneyflow'] + $result_after[$j]['play_price'];
                            }
                        }
                    }
                }
            }

            //售后排行的接待金额：（售前的定金和补款 0，2）预付金额 [0，2] 
            $arr_rank[$i]['recept_moneyflow'] = 0; //接待金额
            $arr_rank[$i]['payment_moneyflow'] = 0; //付款金额
            $arr_rank[$i]['final_moneyflow'] = 0; //补款金额
            if ($result_new_recept[0]) {
                for ($j = 0; $j < count($result_new_recept); $j++) {
                    if (trim($arr_rank[$i]['username']) === trim($result_new_recept[$j]['headmaster'])) {
                        $arr_rank[$i]['recept_moneyflow'] = $result_new_recept[$j]['rception_money'];
                    }
                }
            }
            if ($result_new_payment[0]) {
                for ($j = 0; $j < count($result_new_payment); $j++) {
                    if (trim($arr_rank[$i]['username']) === trim($result_new_payment[$j]['headmaster'])) {
                        $arr_rank[$i]['payment_moneyflow'] = $result_new_payment[$j]['payment_amount'];
                    }
                }
            }
            if ($result_new_final[0]) {
                for ($j = 0; $j < count($result_new_final); $j++) {
                    if (trim($arr_rank[$i]['username']) === trim($result_new_final[$j]['headmaster'])) {
                        $arr_rank[$i]['final_moneyflow'] = $result_new_final[$j]['final_money'];
                    }
                }
            }

            $arr_rank[$i]['second_conversion_num'] = 0;
            //如果都已经算过了
            $arr_rank[$i]['second_conversion_num_old'] = 0;

            if ($second_version_list[0]) {
                for ($j = 0; $j < count($second_version_list); $j++) {
                    if (trim($arr_rank[$i]['username']) == trim($second_version_list[$j]['headmaster'])) {
                        //如果客户的qq没有记录二销转化率且预付款大于1000是记录二销率里
                        if (!in_array($second_version_list[$j]["qq"], $newSecondQqList) && $second_version_list[$j]["total"] > 1000) {
                            $arr_rank[$i]['second_conversion_num'] = $arr_rank[$i]['second_conversion_num'] + 1;
                            $lastQQ[] = $second_version_list[$j]["qq"];
                        }
                        if (in_array($second_version_list[$j]["qq"], $newSecondQqList) && $second_version_list[$j]["total"] > 1000) {
                            $arr_rank[$i]['second_conversion_num_old'] = $arr_rank[$i]['second_conversion_num_old'] + 1;
                        }
                    }
                }
            }
            $arr_rank[$i]['totalrecept'] = 0;
            for ($j = 0; $j < count($result_totalrecept); $j++) {
                if (trim($arr_rank[$i]['username']) === trim($result_totalrecept[$j]['headmaster'])) {
                    $arr_rank[$i]['totalrecept'] += $result_totalrecept[$j]['count(distinct qq)'];
                }
            }
            $arr_rank[$i]['totalsale'] = 0;
            for ($j = 0; $j < count($result_totalsale); $j++) {
                if (trim($arr_rank[$i]['username']) === trim($result_totalsale[$j]['headmaster'])) {
                    $arr_rank[$i]['totalsale'] = $result_totalsale[$j]['sum(play_price)'];
                }
            }

            $arr_rank[$i]['after_conversionflow'] = '0.00%';
            $arr_rank[$i]['second_conversionflow'] = '0.00%';
            if ($arr_rank[$i]['recept_countflow'] != 0 && $arr_rank[$i]['pay_countflow'] != 0) {
                $english_format_number = number_format(($arr_rank[$i]['pay_countflow'] / $arr_rank[$i]['recept_countflow']) * 100, 2, '.', '');
                $arr_rank[$i]['after_conversionflow'] = (string) $english_format_number . "%";
            }
            $arr_rank[$i]['second_perpriceflow'] = 0;
            if ($arr_rank[$i]['recept_countflow'] != 0) {
                $arr_rank[$i]['second_perpriceflow'] = number_format((($arr_rank[$i]['totalmoney'] - $arr_rank[$i]['after_moneyflow']) / $arr_rank[$i]['recept_countflow']), 2, '.', '');
            }
            //二销转化率计算
            $arr_rank[$i]['second_conversionflow1'] = '0.00%';
            if ($arr_rank[$i]['second_conversion_num'] != 0) {
                $english_format_number = number_format(($arr_rank[$i]['second_conversion_num'] / $arr_rank[$i]['recept_countflow']) * 100, 2, '.', '');
                $arr_rank[$i]['second_conversionflow1'] = (string) $english_format_number . "%";
            } else {
                if ($arr_rank[$i]['second_conversion_num_old'] != 0) {
                    $english_format_number = number_format(($arr_rank[$i]['second_conversion_num_old'] / $arr_rank[$i]['recept_countflow']) * 100, 2, '.', '');
                    $arr_rank[$i]['second_conversionflow1'] = (string) $english_format_number . "%";
                }
            }
        }

        $newQQArray = array();
        if (count($lastQQ) > 0) {
            $i = 0;
            foreach ($lastQQ as $key => $value) {
                $newQQArray[$i]["qq"] = $value;
                $newQQArray[$i]["add_time"] = date("Y-m-d H:i:s", time());
                $i++;
            }
            CommonHelper::Insert("p_second_qqversion", $newQQArray, TRUE);
        }

        $arr_sort1 = arr_sort($arr_rank, 'totalmoney', 'desc');
        $total_count = count($arr_sort1);
        $arr_sort = [];
        $i = 1;
        foreach ($arr_sort1 as $row) {
            $row['index'] = $i;
            $row['time'] = $timerange;
            $arr_sort[] = $row;
            $i++;
        }

        $totalmoney = 0;
        $max_page_no = ceil($total_count / $maxpersize);
        $arr_sort = array_slice($arr_sort, ($page - 1) * $maxpersize, $maxpersize);
        foreach ($arr_sort as $row) {
            $totalmoney+=$row['totalmoney'];
        }
        $result = array('searchStartTime' => $searchStartTime, 'searchEndTime' => $searchEndTime, 'timemonth' => $timemonth, 'timeyear' => $timeyear, 'totalmoney' => $totalmoney, 'total_count' => $total_count, "list" => $arr_sort, 'page_no' => $page, 'max_page_no' => $max_page_no, 'page_size' => $maxpersize, 'code' => 0);
        exit(get_response($result));
    } else if ($action == 2) {

        $timerange = '';
        if (!isset($timemonth) && !isset($timeyear)) {
            $timemonth = date('Y-m-d');
            $timeyear = date('Y-m-d');
        }
        $db = create_pdo();
        $customer = new P_Customerrecord_second();
        $customer->set_custom_where("AND type=5");
        $result = Model::query_list($db, $customer, NULL, TRUE);
        if (!$result[0])
            die_error(USER_ERROR, '获取售后排行榜失败');
        $models_user = Model::list_to_array($result['models']);
        $strsql_after = "select headmaster,play_price,qq,customer_type,second_type from p_fills_second where 1=1";
        $strsql_receptflow = "select headmaster,count(*),sum(rception_money),sum(final_money) from P_GenerationOperation where payType=0  and headmaster is not null";
        if (!empty($inneraction)) {
            if ($inneraction == 1) {
                $searchEndTime = date('Y-12-31', strtotime('-1 year', strtotime($timeyear)));
                $timeyear = date('Y-01-01', strtotime('-1 year', strtotime($timeyear)));
                $searchStartTime = $timeyear;
                $strsql_after.=" and DATE_FORMAT(add_time,'%Y')=DATE_FORMAT('$timeyear','%Y')";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time,'%Y')=DATE_FORMAT('$timeyear','%Y')";
            } else if ($inneraction == 2) {
                $timemonth = date('Y-m-01', strtotime($timemonth));
                $searchEndTime = date('Y-m-d', strtotime("$timemonth -1 day"));
                $timemonth = date('Y-m-01', strtotime('-1 month', strtotime($timemonth)));
                $searchStartTime = $timemonth;
                $strsql_after.=" and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $timerange = $searchEndTime . '到' . $searchEndTime;
            } else if ($inneraction == 3) {
                $timemonth = date('Y-m-01', strtotime('+1 month', strtotime($timemonth)));
                $searchStartTime = $timemonth;
                $searchEndTime = date('Y-m-d', strtotime("$timemonth +1 month"));
                $searchEndTime = date('Y-m-d', strtotime("$searchEndTime -1 day"));
                $strsql_after.=" and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m')";
            } else if ($inneraction == 4) {
                $searchEndTime = date('Y-12-31', strtotime('+1 year', strtotime($timeyear)));
                $timeyear = date('Y-01-01', strtotime('+1 year', strtotime($timeyear)));
                $searchStartTime = $timeyear;
                $strsql_after.=" and DATE_FORMAT(add_time,'%Y')=DATE_FORMAT('$timeyear','%Y')";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time,'%Y')=DATE_FORMAT('$timeyear','%Y')";
            }
            $timerange = $searchStartTime . '到' . $searchEndTime;
        } else {

            if (isset($searchTime)) {
                $strsql_after.=" and  DATE_FORMAT(add_time,'%Y-%m-%d')='$searchTime'";

                $timerange = $searchTime;
            }
            if (isset($searchStartTime)) {
                $formatStr = '%Y-%m-%d';
                if (strlen($searchStartTime) > 10) {
                    $formatStr = "%Y-%m-%d %H:%i";
                }
                $strsql_after.=" and DATE_FORMAT(add_time, '" . $formatStr . "') >= '" . $searchStartTime . "' ";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time, '" . $formatStr . "') >= '" . $searchStartTime . "' ";

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
                $strsql_after.=" and DATE_FORMAT(add_time, '" . $formatStr . "') <= '" . $searchEndTime . "' ";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time, '" . $formatStr . "') <= '" . $searchEndTime . "' ";

                if (isset($searchStartTime)) {
                    $timerange = $searchStartTime . "至" . $searchEndTime;
                } else {
                    $timerange = '截止至' . $searchEndTime;
                }
            }
            if (empty($searchTime) && empty($searchStartTime) && empty($searchEndTime)) {
                $strsql_after.=" and DATE_FORMAT(add_time,'%Y-%m')=date_format(now(),'%Y-%m')";
                $strsql_receptflow.=" and DATE_FORMAT(allot_time,'%Y-%m')=date_format(now(),'%Y-%m')";
                $timerange = '本月';
            }
        }
        $strsql_receptflow.=" GROUP BY headmaster";
        $result_after = $db->query($strsql_after);
        $result_after = $result_after->fetchAll(PDO::FETCH_ASSOC);
        $result_receptflow = $db->query($strsql_receptflow);
        $result_receptflow = $result_receptflow->fetchAll(PDO::FETCH_ASSOC);


        $arr_rank = array();
        for ($i = 0; $i < count($models_user); $i++) {
            $arr_rank[$i]['username'] = $models_user[$i]['username'];
            $arr_rank[$i]['userid'] = $models_user[$i]['userid'];
            //获取新流程欠款和接待数
            $arr_rank[$i]['recept_moneyflow'] = 0;
            $arr_rank[$i]['recept_countflow'] = 0;
            $arr_rank[$i]['fill_moneyflow'] = 0;
            if ($result_receptflow[0]) {
                for ($j = 0; $j < count($result_receptflow); $j++) {
                    if ($arr_rank[$i]['username'] == $result_receptflow[$j]['headmaster']) {
                        $arr_rank[$i]['recept_countflow'] = $result_receptflow[$j]['count(*)'];
                        $arr_rank[$i]['fill_moneyflow'] = $result_receptflow[$j]['sum(final_money)'];
                        $arr_rank[$i]['recept_moneyflow'] = $result_receptflow[$j]['sum(rception_money)'];
                        break;
                    }
                }
            }
            //获取业绩相关数据
            $arr_rank[$i]['after_moneyflow'] = 0;
            $arr_rank[$i]['pay_countflow'] = 0;
            $arr_rank[$i]['second_moneyflow'] = 0;
            $arr_rank[$i]['second_countflow'] = 0;
            $arr_rank[$i]['vipclass'] = 0;
            $arr_rank[$i]['vip'] = 0;
            $arr_rank[$i]['zhuangxiu'] = 0;
            $arr_rank[$i]['huodong'] = 0;
            $arr_rank[$i]['huoyuan'] = 0;
            $arr_rank[$i]['totalmoney'] = 0;
            $str_qqlflow = "";
            $str_secondqqflow = "";
            if ($result_after[0]) {
                for ($j = 0; $j < count($result_after); $j++) {
                    if (trim($arr_rank[$i]['username']) === trim($result_after[$j]['headmaster'])) {
                        $arr_rank[$i]['totalmoney'] = $arr_rank[$i]['totalmoney'] + $result_after[$j]['play_price'];
                        if ($result_after[$j]['second_type'] == '代运营补欠款') {
                            $arr_rank[$i]['after_moneyflow'] = $arr_rank[$i]['after_moneyflow'] + $result_after[$j]['play_price'];
                            if (strpos($str_qqlflow, $result_after[$j]['qq']) === FALSE) {
                                $str_qqlflow.=$result_after[$j]['qq'];
                                $arr_rank[$i]['pay_countflow'] = $arr_rank[$i]['pay_countflow'] + 1;
                            }
                        } else {
                            if (strpos($str_secondqqflow, $result_after[$j]['qq']) === FALSE) {
                                $str_secondqqflow.=$result_after[$j]['qq'];
                                $arr_rank[$i]['second_countflow'] = $arr_rank[$i]['second_countflow'] + 1;
                            }
                            if (stripos($result_after[$j]['second_type'], 'vip课程') !== FALSE) {
                                $arr_rank[$i]['vipclass']+= $result_after[$j]['play_price'];
                            } else if (stripos($result_after[$j]['second_type'], 'vip') !== FALSE) {
                                $arr_rank[$i]['vip']+= $result_after[$j]['play_price'];
                            } else if (stripos($result_after[$j]['second_type'], '活动') !== FALSE) {
                                $arr_rank[$i]['huodong']+= $result_after[$j]['play_price'];
                            } else if (stripos($result_after[$j]['second_type'], '装修') !== FALSE) {
                                $arr_rank[$i]['zhuangxiu']+= $result_after[$j]['play_price'];
                            } else if (stripos($result_after[$j]['second_type'], '加盟') !== FALSE) {
                                $arr_rank[$i]['huoyuan']+= $result_after[$j]['play_price'];
                            } else {
                                $arr_rank[$i]['second_moneyflow'] = $arr_rank[$i]['second_moneyflow'] + $result_after[$j]['play_price'];
                            }
                        }
                    }
                }
            }
            $arr_rank[$i]['after_conversionflow'] = '0.00%';
            //$arr_rank[$i]['second_conversionflow'] = '0.00%';
            $arr_rank[$i]['second_perpriceflow'] = 0;
            if ($arr_rank[$i]['recept_countflow'] != 0 && $arr_rank[$i]['pay_countflow'] != 0) {
                $english_format_number = number_format(($arr_rank[$i]['pay_countflow'] / $arr_rank[$i]['recept_countflow']) * 100, 2, '.', '');
                $arr_rank[$i]['after_conversionflow'] = (string) $english_format_number . "%";
            }
            if ($arr_rank[$i]['recept_countflow'] != 0 && $arr_rank[$i]['second_countflow'] != 0) {
                $english_format_number = number_format(($arr_rank[$i]['second_countflow'] / $arr_rank[$i]['recept_countflow']) * 100, 2, '.', '');
                //$arr_rank[$i]['second_conversionflow'] = (string) $english_format_number . "%";
                $arr_rank[$i]['second_perpriceflow'] = number_format(($arr_rank[$i]['second_moneyflow'] / $arr_rank[$i]['recept_countflow']), 2, '.', '');
            }
        }
        $arr_sort1 = arr_sort($arr_rank, 'totalmoney', 'desc');
        $total_count = count($arr_sort1);
        $arr_sort = [];
        $i = 1;
        foreach ($arr_sort1 as $row) {
            $row['index'] = $i;
            $row['time'] = $timerange;
            $arr_sort[] = $row;
            $i++;
        }
        $totalmoney = 0;
        $max_page_no = ceil($total_count / request_pagesize());
        $arr_sort = array_slice($arr_sort, ($page - 1) * $maxpersize, $maxpersize);
        foreach ($arr_sort as $row) {
            $totalmoney+=$row['totalmoney'];
        }

        $expolt = new ExportData2Excel();
        $title_array = array('排行', '姓名', '接待数', '接待金额', '欠款金额', '补款金额', 'VIP', '装修', '课程', '活动', '货源', '其他', '总业绩', '客单价', '周期');

        $new_arr = array();
        $i = 0;
        foreach ($arr_sort as $key => $value) {
            $new_arr[$i]["index"] = $value["index"];
            $new_arr[$i]["username"] = $value["username"];
            $new_arr[$i]["recept_countflow"] = $value["recept_countflow"];
            $new_arr[$i]["recept_moneyflow"] = $value["recept_moneyflow"];
            $new_arr[$i]["fill_moneyflow"] = $value["fill_moneyflow"];
            $new_arr[$i]["after_moneyflow"] = $value["after_moneyflow"];
            $new_arr[$i]["vip"] = $value["vip"];
            $new_arr[$i]["zhuangxiu"] = $value["zhuangxiu"];
            $new_arr[$i]["vipclass"] = $value["vipclass"];
            $new_arr[$i]["huodong"] = $value["huodong"];
            $new_arr[$i]["huoyuan"] = $value["huoyuan"];
            $new_arr[$i]["second_moneyflow"] = $value["second_moneyflow"];
            $new_arr[$i]["totalmoney"] = $value["totalmoney"];
            $new_arr[$i]["second_perpriceflow"] = $value["second_perpriceflow"];
            $new_arr[$i]["time"] = "本月";
            $i++;
        }


        $expolt->create($title_array, $new_arr, "售后排行数据导出", "售后排行");

//        $expolt = new ExportData2Excel();
//        $flow = new P_Fills_second();
//       
//        $flow->set_query_fields(array('id', 'add_time', 'qq', 'play_price', 'platform_rception'));
//        $db = create_pdo();
//        $result = Model::query_list($db, $flow, NULL, true);
//        if (!$result[0]) {
//            $expolt->create(array('导出错误'), array(array('流量业绩数据导出失败,请稍后重试!')), "流量业绩数据导出", "流量业绩");
//        }
//        $models = Model::list_to_array($result['models'], array(), function() {
//                    
//                });
//        $title_array = array('序号', '添加时间', 'QQ号', '价格', '名称');
//        $expolt->create($title_array, $models, "售后排名数据导出", "售后排名");
    }
});
