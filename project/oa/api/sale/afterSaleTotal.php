<?php

/**
 * 综合汇总
 *
 * @author bocheng
 * @copyright 2016 非时序
 * @version 2016/02/17
 */
use Models\Base\Model;
use Models\Base\SqlOperator;
use Models\P_GenerationOperation;
use Models\p_flow;
use Models\P_Fills_second;

require '../../Common/ExportData2Excel.php';
require '../../application.php';
require '../../loader-api.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    //读取数据库数据
    $timemonth = request_string("timemonth");
    $inner = request_string('inner');
    $onlineDate = ONLINEDATE;

    if ($action == 1) {
        if (!empty($timemonth)) {
            if ($inner == 1) {
                $timemonth = date('Y-m-d', strtotime("$timemonth -1 month"));
            }
            if ($inner == 2) {
                $timemonth = date('Y-m-d', strtotime("$timemonth +1 month"));
            }
        } else {
            $timemonth = date('Y-m-01');
        }
        $dateArr = explode("-", $timemonth);
        $onlineArr = explode("-", $onlineDate);

        $dealNumSql = "SELECT DATE_FORMAT(add_time,'%Y-%m-%d') as add_day,count(distinct qq) as dealNum from p_generationoperation where payType = 0 and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m') GROUP BY DATE_FORMAT(add_time,'%Y-%m-%d')";

        $sumMoneySql = "select DATE_FORMAT(add_time,'%Y-%m-%d') as add_day,sum(payment_amount) payAmount from p_generationoperation where (payType = 0 or payType = 2) and
DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m') GROUP BY DATE_FORMAT(add_time,'%Y-%m-%d')";

//        if ($dateArr[1] == $onlineArr[1] && $dateArr[0] == $onlineArr[0]) {
//            //如果查询的日期是上线的当年并且月份正好是上线的月份,就会查询两种数据，一种是原始数据，一个是新插入的数据
//            $gensql = "SELECT DATE_FORMAT(add_time,'%Y-%m-%d') as add_day,count(distinct qq) as num,sum(payment_amount) as daymoney from p_generationoperation where (payType = 0 or payType = 2) and
//DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m') and add_time < '$onlineDate' GROUP BY DATE_FORMAT(add_time,'%Y-%m-%d')
//UNION
//select result.add_day,result.num,sum(re.pay_money) from ( SELECT DATE_FORMAT(add_time,'%Y-%m-%d') as add_day,count(distinct qq) as num,id  gpid from p_generationoperation 
//where (payType = 0 or payType = 2) and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$onlineDate','%Y-%m') GROUP BY DATE_FORMAT(add_time,'%Y-%m-%d')) as result inner JOIN
//receiver_payaccount re on result.gpid = re.general_operal_id";
//        } else if ($dateArr[0] < $onlineArr[0] || ($dateArr[0] == $onlineArr[0] && $dateArr[1] < $onlineArr[1])) {
//            //如果查询的年份比上线的年份小或者查询的年份相同并且月份小于上线月份，则全部查询旧数据
//            $gensql = "SELECT DATE_FORMAT(add_time,'%Y-%m-%d') as add_day,count(distinct qq) as num,sum(payment_amount) as daymoney from p_generationoperation where (payType = 0 or payType = 2) and
//                        DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m') GROUP BY DATE_FORMAT(add_time,'%Y-%m-%d')";
//        } else {
//            //如果查询的年份与上线的年份相同且月份大于上线月份或者大于上线年份的新数据查询方式
//            $gensql = "select result.add_day,result.num,sum(re.pay_money) from ( SELECT DATE_FORMAT(add_time,'%Y-%m-%d') as add_day,count(distinct qq) as num,id  gpid from 
//p_generationoperation where (payType = 2 or payType = 0) and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m') 
//GROUP BY DATE_FORMAT(add_time,'%Y-%m-%d')) as result inner JOIN receiver_payaccount re on result.gpid = re.general_operal_id";
//        }
        $flowsql = "select DATE_FORMAT(add_time,'%Y-%m-%d') as add_day,count(*) as num from p_flow where DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m') GROUP BY  DATE_FORMAT(add_time,'%Y-%m-%d') order by add_day desc";
//        $gensql = "SELECT DATE_FORMAT(add_time,'%Y-%m-%d') as add_day,count(distinct qq) as num from p_generationoperation where payType=0 and DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m') GROUP BY DATE_FORMAT(add_time,'%Y-%m-%d')";
//        $yjsql = "SELECT DATE_FORMAT(transfer_time,'%Y-%m-%d') as add_day,sum(pay_money) as daymoney from receiver_payaccount where DATE_FORMAT(transfer_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m') GROUP BY DATE_FORMAT(transfer_time,'%Y-%m-%d')";
        $secondsql = "SELECT DATE_FORMAT(add_time,'%Y-%m-%d') as add_day,play_price,second_type from p_fills_second where DATE_FORMAT(add_time,'%Y-%m')=DATE_FORMAT('$timemonth','%Y-%m') order by add_time desc";
        $db = create_pdo();
        $resultflow = $db->query($flowsql);
        $resultflow = $resultflow->fetchAll(PDO::FETCH_ASSOC);
//        $resultgen = $db->query($gensql);
//        $resultgen = $resultgen->fetchAll(PDO::FETCH_ASSOC);
        $resultsecond = $db->query($secondsql);
        $resultsecond = $resultsecond->fetchAll(PDO::FETCH_ASSOC);
        $resultDealNum = $db->query($dealNumSql);
        $resultDealNumList = $resultDealNum->fetchAll(PDO::FETCH_ASSOC);
        $resultsumMoney = $db->query($sumMoneySql);
        $resultSumMoneyList = $resultsumMoney->fetchAll(PDO::FETCH_ASSOC);
        $totalmoneyfirst = 0;
        $listfirst = [];
        if ($resultflow[0]) {
            for ($i = 0; $i < count($resultflow); $i++) {
                $listfirst[$i]['add_day'] = $resultflow[$i]['add_day'];
                $listfirst[$i]['num'] = $resultflow[$i]['num'];
                $listfirst[$i]['dealnum'] = 0;
                $listfirst[$i]['dealmoney'] = 0;
//                if ($resultgen) {
//                    for ($j = 0; $j < count($resultgen); $j++) {
//                        if ($resultflow[$i]['add_day'] == $resultgen[$j]['add_day']) {
//                            $listfirst[$i]['dealnum'] = $resultgen[$j]['num'];
//                            $listfirst[$i]['dealmoney'] = $resultgen[$j]['daymoney'];
//                            $totalmoneyfirst += $resultgen[$j]['daymoney'];
//                        }
//                    }
//                }
                if ($resultDealNumList[0]) {
                    for ($j = 0; $j < count($resultDealNumList); $j++) {
                        if ($resultflow[$i]['add_day'] == $resultDealNumList[$j]['add_day']) {
                            $listfirst[$i]['dealnum'] = $resultDealNumList[$j]['dealNum'];
                        }
                    }
                }
                if ($resultSumMoneyList[0]) {
                    for ($j = 0; $j < count($resultSumMoneyList); $j++) {
                        if ($resultflow[$i]['add_day'] == $resultSumMoneyList[$j]['add_day']) {
                            $listfirst[$i]['dealmoney'] = $resultSumMoneyList[$j]['payAmount'];
                            $totalmoneyfirst += $resultSumMoneyList[$j]['payAmount'];
                        }
                    }
                }
            }
        }
        $listsecond = [];
        $totalmoneysecond = 0;
        $j = 0;
        if ($resultsecond[0]) {
            for ($i = 0; $i < count($resultsecond); $i++) {
                if (count($listsecond) == 0) {
                    $listsecond[$j]['add_day'] = $resultsecond[$i]['add_day'];
                    $listsecond[$j]['final'] = 0;
                    $listsecond[$j]['other'] = 0;
                    $listsecond[$j]['daymoney'] = 0;
                } else {
                    if ($listsecond[$j]['add_day'] != $resultsecond[$i]['add_day']) {
                        $j++;
                        $listsecond[$j]['add_day'] = $resultsecond[$i]['add_day'];
                        $listsecond[$j]['final'] = 0;
                        $listsecond[$j]['other'] = 0;
                        $listsecond[$j]['daymoney'] = 0;
                    }
                }
                $totalmoneysecond+=$resultsecond[$i]['play_price'];
                $listsecond[$j]['daymoney']+=$resultsecond[$i]['play_price'];
                if ($resultsecond[$i]['second_type'] == "代运营补欠款")
                    $listsecond[$j]['final']+=$resultsecond[$i]['play_price'];
                else
                    $listsecond[$j]['other']+=$resultsecond[$i]['play_price'];
            }
        }
        $result = array('listfirst' => $listfirst, 'listsecond' => $listsecond, 'totalmoneyfirst' => $totalmoneyfirst, 'totalmoneysecond' => $totalmoneysecond, 'timemonth' => $timemonth);
        exit(json_encode($result));
    }
});
