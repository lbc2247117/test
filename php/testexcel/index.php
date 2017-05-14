<?php

include_once 'PHPExcel/IOFactory.php';
$filePath = 'text.xls';
$phpexcel = new PHPExcel();
$currentSheet = $phpexcel->getActiveSheet();
$currentSheet->fromArray(array(array('姓名', '成绩'), array('刘波成', '100')));
$write = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
header('Content-Type: application/vnd.ms-excel;charset=utf-8');
header('Content-Disposition: attachment;filename="'   . time() . '.xls"');
header('Cache-Control: max-age=0');
header("Pragma: public");
header("Expires: 0");
header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
header("Content-Type:application/force-download");
header("Content-Type:application/octet-stream");
header("Content-Type:application/download");
header("Content-Transfer-Encoding:binary");
$write->save('php://output');
exit();
$objPHPExcel = PHPExcel_IOFactory::load($filePath);
$sheetData = $objPHPExcel->getSheet(0)->toArray(null, true, true, true);
exit(json_encode($sheetData));
