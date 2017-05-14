<?php

/**
 * 公共函数库
 *
 * @author LiuBoCheng
 * @copyright (c) 2016, 云道
 * @version 2016-08-23
 */
function returnJson($status = 0, $msg = '', $data = '') {
    $result['status'] = $status;
    $result['msg'] = $msg;
    $result['data'] = $data;
    exit(json_encode($result));
}

/**
 * 获取微信Package
 * 
 * @param string $appid 微信公众号ID
 * @param string $jsapiTicket 微信网页接口要用到的ticket
 */
function getSignPackage($appid, $jsapiTicket) {
    // 注意 URL 一定要动态获取，不能 hardcode.
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $timestamp = time();
    $nonceStr = createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
        "appId" => $appid,
        "nonceStr" => $nonceStr,
        "timestamp" => $timestamp,
        "url" => $url,
        "signature" => $signature,
        "rawString" => $string
    );
    return $signPackage;
}

function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

/**
 * 导入一个csv文件，返回数组
 * @author Laijie
 * @param  $url  文件句柄
 */
function input_csv($handle) {
    setlocale(LC_ALL, 'zh_CN'); //设置简体中文
    $out = array();
    $n = 0;
    while ($data = fgetcsv($handle, 10000)) {
        $num = count($data);
        for ($i = 0; $i < $num; $i++) {
            $out[$n][$i] = $data[$i];
        }
        $n++;
    }
    return $out;
}

/**
 * 导出excel
 * 
 * @author Liubocheng
 * 
 * @param type $th 数据头 array
 * @param type $data 数据源 array
 * @param type $title 数据title String
 * @param type $filename 导出文件名
 */
function createExcel($th = array(), $data = array(), $title = "", $filename = "excel导出") {
    Vendor('phpExcel.PHPExcel');
    Vendor('phpExcel.ExcelAssistant');
    $objExcel = new PHPExcel();
    $objExcel->getProperties()->setCreator("世纪云道");
    $objExcel->setActiveSheetIndex(0);
    $excelAssistant = new ExcelAssistant();
    $excelTit_array = $excelAssistant->GetExcelTit(count($th));

    $i = 1;
    $field_width_array = array();
    $field_array = array();
    $row_height_array = 15;

    if ($title !== "") {
        $mergeCells_name = $excelTit_array[0] . ($i) . ":" . $excelTit_array[count($excelTit_array) - 1] . $i;
        $objExcel->getActiveSheet()->mergeCells($mergeCells_name);
        $objExcel->getActiveSheet()->setCellValue("A1", $title);
        $objExcel->getActiveSheet()->getStyle('A1')->applyFromArray(
                array(
                    'font' => array(
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                )
        );
        $i = 2;
    }
    if (count($field_width_array) > 0) {
        foreach ($excelTit_array as $k => $v) {
            $objExcel->getActiveSheet()->getColumnDimension($v)->setWidth($field_width_array[$k]);
        }
    } else {
        foreach ($excelTit_array as $k => $v) {
            $objExcel->getActiveSheet()->getColumnDimension($v)->setWidth(20);
        }
    }
    foreach ($th as $k => $v) {
        $objExcel->getActiveSheet()->setCellValue(($excelTit_array[$k]) . $i, "$v");
        $objExcel->getActiveSheet()->getStyle(($excelTit_array[$k]) . $i)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                )
        );
    }
    if (count($field_array) > 0) {
        foreach ($data as $k => $v) {
            $i++;
            $c_nul = 0;
            foreach ($field_array as $key => $value) {
                $c_v = $excelTit_array[$c_nul++] . $i;
                $objExcel->getActiveSheet()->setCellValue($c_v, $v[$value]);
                $objExcel->getActiveSheet()->getStyle($c_v)->applyFromArray(
                        array(
                            'alignment' => array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                            )
                        )
                );
            }
        }
    } else {
        foreach ($data as $k => $v) {
            $i++;
            $c_nul = 0;
            foreach ($v as $index => $d) {
                $c_v = $excelTit_array[$c_nul++] . $i;
                $objExcel->getActiveSheet()->setCellValue($c_v, $d);
                $objExcel->getActiveSheet()->getStyle($c_v)->applyFromArray(
                        array(
                            'alignment' => array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
                            )
                        )
                );
            }
        }
    }
    for ($x = 0; $x <= count($data) + 2; $x++) {
        $objExcel->getActiveSheet()->getRowDimension($x)->setRowHeight($row_height_array);
    }

    $timestamp = time();
    $timestr = date("Y-m-d", $timestamp);
    header('Content-Type: application/vnd.ms-excel;charset=utf-8');
    header('Content-Disposition: attachment;filename="' . $filename . '@' . $timestr . '.xls"');
    header('Cache-Control: max-age=0');
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
    header("Content-Type:application/force-download");
    header("Content-Type:application/octet-stream");
    header("Content-Type:application/download");
    header("Content-Transfer-Encoding:binary");
    $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
    ob_clean();
    $objWriter->save("php://output");
}

/**
 * 生成二维码
 * @author Laijie
 * @param  string  $url  url连接
 * @param  integer $size 尺寸 纯数字
 * @param  string $outfile flase 是不保存直接打印二维码，否则传入二维码保存的地址
 */
function qrcode($url, $size = 4, $outfile) {
    Vendor('Phpqrcode.phpqrcode');
    QRcode::png($url, $outfile, QR_ECLEVEL_L, $size, 2, false, 0xFFFFFF, 0x000000);
}

/**
 * 生成带logo的二维码
 * @author Laijie
 * @param  string  $url  url连接
 * @param  integer $size 尺寸 纯数字
 * @param  string $outfile flase 是不保存直接打印二维码，否则传入二维码保存的地址
 * @param  string $logo flase logo的地址
 */
function qrcodelogo($url, $size = 4, $outfile, $logo = FALSE) {
    Vendor('Phpqrcode.phpqrcode');
    QRcode::png($url, $outfile, QR_ECLEVEL_L, $size, 2, false, 0xFFFFFF, 0x000000);

    if ($logo !== FALSE) {
        $QR = imagecreatefromstring(file_get_contents($outfile));
        $logo = imagecreatefromstring(file_get_contents($logo));
        if (imageistruecolor($logo))
            imagetruecolortopalette($logo, false, 65535);
        $QR_width = imagesx($QR); //二维码图片宽度  
        $QR_height = imagesy($QR); //二维码图片高度  
        $logo_width = imagesx($logo); //logo图片宽度  
        $logo_height = imagesy($logo); //logo图片高度  
        $logo_qr_width = $QR_width / 5;
        $scale = $logo_width / $logo_qr_width;
        $logo_qr_height = $logo_height / $scale;
        $from_width = ($QR_width - $logo_qr_width) / 2;
        //重新组合图片并调整大小  
        imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
        //输出图片  
        imagepng($QR, $outfile);
    }
}

/**
 * 验证手机号是否正确
 * @author Laijie
 * @param INT $mobile
 */
function isMobile($mobile) {
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
}

/**
 * 二维数组排序
 * 
 * @param array $array 要排序的数组
 * @param string $key 要排序的键名
 * @param Enmu $order 接受asc或者desc，排序的方式
 * 
 * @return array 排序后的数组
 */
function sortForSecondArr($array, $key, $order = "asc") {
    $arr_nums = $arr = array();
    foreach ($array as $k => $v) {
        $arr_nums[$k] = $v[$key];
    }
    if ($order == 'asc') {
        asort($arr_nums);
    } else {
        arsort($arr_nums);
    }
    foreach ($arr_nums as $k => $v) {
        $arr[$k] = $array[$k];
    }
    return $arr;
}

/**
 * 获取客户端的IP
 * 
 * @return string 返回用户的真实IP
 */
function getClientIP() {
    return $_SERVER["REMOTE_ADDR"];
}

/**
 * 创建一个目录
 * 
 * @param string $dir 目录
 * @param type $mode  模式
 * @return boolean    成功与否
 */
function mkDirs($dir, $mode = 0777) {
    if (!is_dir($dir)) {
        return mkdir($dir, $mode);
    }
    return true;
}

/**
 * 把图片转换为base64字符串
 * 
 * @param type $imgPath 图片的路径
 * @return string 图片的base64代码
 */
function imgToBase64($imgPath) {
    $image_info = getimagesize($imgPath);
    $base64_image_content = "data:{$image_info['mime']};base64," . chunk_split(base64_encode(file_get_contents($imgPath)));
    return $base64_image_content;
}

/**
 * base64转图片
 * 
 * @param string $base64String
 * @param string $imgPath
 * @return boolean
 */
function base64ToImg($base64String, $imgPath) {
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64String, $result)) {
        $type = $result[2];
        $fileName = md5(time()) . '.' . $type;
        $new_file = $imgPath . $fileName;
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64String)))) {
            if (filesize($new_file) > 1024 * 1024) { //大于1M需要裁剪
                $image = new \Think\Image();
                $image->open($new_file);
                // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                $image->thumb(640, 640)->save($new_file);
            }
            return $fileName;
        }
        return FALSE;
    }
    return FALSE;
}

/**
 * 对参数进行urlencode编码
 * 
 * @param string|array $param 
 * @return string|array 过滤后的数组
 */
function myurlencode($param) {
    if (!is_array($param))
        return urlencode($param);
    foreach ($param as $key => $value) {
        $param[$key] = urlencode($value);
    }
    return $param;
}
