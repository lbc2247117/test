<?php

/**
 * 公共函数库
 *
 * @author LiuBoCheng
 * @copyright (c) 2016, 云道
 * @version 2016-08-23
 */

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
        $extension = pathinfo($imgPath, PATHINFO_EXTENSION);
        $imgPath = str_replace($extension, '', $imgPath);
        $new_file = "$imgPath$type";
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64String)))) {
            return TRUE;
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
