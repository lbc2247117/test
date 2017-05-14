<?php

header("Content-type:text/html;charset=gbk");
$action = $_REQUEST['action'];
if ($action == 'import') {
    $filename = $_FILES['file']['tmp_name'];
    $handle = fopen($filename, 'r');
    $result = input_csv($handle); //解析csv 
    var_dump($result);
} elseif ($action == 'export') { //导出CSV 
  
    $str = "姓名,性别,年龄\n";
    $str = iconv('utf-8', 'gb2312', $str);

    $filename = date('Ymd') . '.csv'; //设置文件名 
    
    export_csv($filename, $str); //导出 
}

function input_csv($handle) {
    $out = array();
    $n = 0;
    while ($data = fgetcsv($handle)) {
        $num = count($data);
        for ($i = 0; $i < $num; $i++) {
            $out[$n][$i] = $data[$i];
        }
        $n++;
    }
    return $out;
}

function export_csv($filename, $data) {
  $file=  fopen($filename, 'w');
  fputcsv($file, array('姓名','性别','年龄'));
  fclose($file);
  
}
