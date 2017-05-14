<?php

header("Content-Type:text/html;charset=utf-8");
require_once 'PHPExcel/IOFactory.php';
require_once 'PHPExcel.php';
if (!file_exists("123.xlsx")) {
    exit("Please run 05featuredemo.php first." . EOL);
}

$objReader = PHPExcel_IOFactory::createReaderForFile("123.xlsx");
$objPHPExcel = $objReader->load("123.xlsx");
//$objPHPExcel->setActiveSheetIndex(1);

$objWorksheet = $objPHPExcel->getActiveSheet();
foreach ($objWorksheet->getRowIterator() as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    foreach ($cellIterator as $key => $cell) {
        if ($key == 2) {
            echo  date("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($cell->getValue()));
        } else {
            echo  $cell->getValue();
        }
    }
}
    