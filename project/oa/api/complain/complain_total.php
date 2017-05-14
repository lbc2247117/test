<?php

/**
 * 员工列表/员工增删改操作
 *
 * @author ChenHao
 * @copyright 2015 星密码
 * @version 2015/1/27
 */
require '../../application.php';
require '../../loader-api.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    if (!isset($action))
        $action = -1;
    $currDate = request_string('currDate');
    $sqlTotal = "";
    $sqlPro = "";
    $sqlDeal = "";
    switch ($action) {
        case 1:
            $sqlTotal .= "select COUNT(id) TotalNum,DATE_FORMAT(receiveTime, '%Y-%m-%d') dealTime,flowType from p_complain where DATE_FORMAT(receiveTime, '%Y-%m-%d') = DATE_FORMAT('$currDate','%Y-%m-%d')  GROUP BY flowType";

            $sqlPro .= "SELECT proType,COUNT(proType) TotalPro,DATE_FORMAT(receiveTime, '%Y-%m-%d') dealTime,flowType FROM p_complain where DATE_FORMAT(receiveTime, '%Y-%m-%d') = DATE_FORMAT('$currDate','%Y-%m-%d')  GROUP BY proType,flowType";

            $sqlDeal .= "select dealResult,count(dealResult) TotalDeal,SUM(refundMoney) TotalRefund,DATE_FORMAT(receiveTime, '%Y-%m-%d') dealTime,flowType from p_complain where DATE_FORMAT(receiveTime, '%Y-%m-%d') = DATE_FORMAT('$currDate','%Y-%m-%d') GROUP BY dealResult,flowType";

            break;
    }
    $db = create_pdo();
    $resultTotal = $db->query($sqlTotal);
    $resultNewTotal = $resultTotal->fetchAll(PDO::FETCH_ASSOC);
    $resultPro = $db->query($sqlPro);
    $resultNewPro = $resultPro->fetchAll(PDO::FETCH_ASSOC);
    $resultDeal = $db->query($sqlDeal);
    $resultNewDeal = $resultDeal->fetchAll(PDO::FETCH_ASSOC);
    
    $returnArray = array();
    if ($resultNewTotal[0]) {
        for ($i = 0; $i < count($resultNewTotal); $i++) {
            $returnArray[$i]["TotalNum"] = $resultNewTotal[$i]["TotalNum"];
            $returnArray[$i]["DealTime"] = $resultNewTotal[$i]["dealTime"];
            $returnArray[$i]["flowType"] = $resultNewTotal[$i]["flowType"];
            $returnArray[$i]["Advise"] = 0;
            $returnArray[$i]["Cheat"] = 0;
            $returnArray[$i]["NoProfession"] = 0;
            $returnArray[$i]["NoAloof"] = 0;
            $returnArray[$i]["Brush"] = 0;
            $returnArray[$i]["SecondSale"] = 0;
            $returnArray[$i]["Another"] = 0;

            if ($resultNewPro[0]) {
                for ($x = 0; $x < count($resultNewPro); $x++) {
                    if ($resultNewPro[$x]["dealTime"] == $resultNewTotal[$i]["dealTime"] && $resultNewPro[$x]["flowType"] == $resultNewTotal[$i]["flowType"]) {
                        if ($resultNewPro[$x]["proType"] == "咨询") {
                            $returnArray[$i]["Advise"] += $resultNewPro[$x]["TotalPro"];
                        } else if ($resultNewPro[$x]["proType"] == "骗人") {
                            $returnArray[$i]["Cheat"] += $resultNewPro[$x]["TotalPro"];
                        } else if ($resultNewPro[$x]["proType"] == "不专业") {
                            $returnArray[$i]["NoProfession"] += $resultNewPro[$x]["TotalPro"];
                        } else if ($resultNewPro[$x]["proType"] == "不理人") {
                            $returnArray[$i]["NoAloof"] += $resultNewPro[$x]["TotalPro"];
                        } else if ($resultNewPro[$x]["proType"] == "默认刷单") {
                            $returnArray[$i]["Brush"] += $resultNewPro[$x]["TotalPro"];
                        } else if ($resultNewPro[$x]["proType"] == "二销问题") {
                            $returnArray[$i]["SecondSale"] += $resultNewPro[$x]["TotalPro"];
                        } else if ($resultNewPro[$x]["proType"] == "其他") {
                            $returnArray[$i]["Another"] += $resultNewPro[$x]["TotalPro"];
                        }
                    }
                }
            }
            
            $returnArray[$i]["Continue"] = 0;
            $returnArray[$i]["Change"] = 0;
            $returnArray[$i]["Negotiation"] = 0;
            $returnArray[$i]["RefundNum"] = 0;
            $returnArray[$i]["Refund"] = 0;
            $returnArray[$i]["Refunding"] = 0;
            
            if ($resultNewDeal[0]) {
                for ($y = 0; $y < count($resultNewDeal); $y++) {
                    if ($resultNewDeal[$y]["dealTime"] == $resultNewTotal[$i]["dealTime"] && $resultNewDeal[$y]["flowType"] == $resultNewTotal[$i]["flowType"]) {
                        if ($resultNewDeal[$y]["dealResult"] == "继续学习") {
                            $returnArray[$i]["Continue"] += $resultNewDeal[$y]["TotalDeal"];
                        } else if ($resultNewDeal[$y]["dealResult"] == "换老师") {
                            $returnArray[$i]["Change"] += $resultNewDeal[$y]["TotalDeal"];
                        } else if ($resultNewDeal[$y]["dealResult"] == "协商退款") {
                            $returnArray[$i]["Negotiation"] += $resultNewDeal[$y]["TotalDeal"];
                            $returnArray[$i]["RefundNum"] += $resultNewDeal[$y]["TotalDeal"];
                            $returnArray[$i]["Refund"] += $resultNewDeal[$y]["TotalRefund"];
                        }
                    }
                }
            }
        }
    }

    $resultReturn = array("list" => $returnArray);
    exit(get_response($resultReturn));
});
