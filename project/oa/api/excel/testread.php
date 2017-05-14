<?php
define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");
header("Content-Type:text/html;charset=utf-8");
require_once 'excel_reader2.php';
//�������� 
$data = new Spreadsheet_Excel_Reader();

//�����ı�������� 
$data->setOutputEncoding('UTF-8');
//��ȡExcel�ļ� 
$data->read( BASE_PATH.iconv('utf-8', 'gb2312', "../../upload/123.xls"));
//$data->sheets[0]['numRows']ΪExcel���� 
$arr=array();
$array=array();
for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
    //$data->sheets[0]['numCols']ΪExcel���� 
    for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
        //��ʾÿ����Ԫ������ 
        if(isset($data->sheets[0]["cells"][$i][$j])){
            //echo $data->sheets[0]['cells'][$i][$j] . ' ';
            $arr[$j-1]=$data->sheets[0]['cells'][$i][$j];
        }else{
            $arr[$j-1]="";
        }
    }
    //print_r($arr);
    $array[$i-1]=$arr;
} 
echo json_encode($array);