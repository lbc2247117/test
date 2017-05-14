<?php

header("Content-Type:text/html;charset=utf-8");

/**
 * 员工列表/员工增删改操作
 *
 * @author Qi
 * @copyright 2015 星密码
 * @version 2015/3/3
 */
use Models\Base\Model;
use Models\staff_mein;
use Models\Base\SqlOperator;
use Models\Base\SqlSortType;

require '../../application.php';
require '../../loader-api.php';
require '../../Common/FileUpload.php';
require '../../Common/word2pdf.php';
require '../../Helper/upyun.class.php';
require '../../Helper/CommonDal.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() {

    $allAction = $_GET["action"];
    //查询所有的相册
    if ($allAction == 1) {
        $system = new staff_mein();
        $system->set_custom_where(" group by dic_title");
        $system->set_custom_where(" ORDER BY dic_addtime desc");
//        $system->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $system, NULL, true);
        if (!$result[0])
            die_error(USER_ERROR, '获取图片信息失败，请重试');
        $models = Model::list_to_array($result['models'], array(), "id_2_text");

        echo_list_result($result, $models);
    }
    //查询所有的图片
    if ($allAction == 2) {
        $showTitle = $_GET["showTitle"];

        $staff_mein = new staff_mein();
        $staff_mein->set_query_fields(array(staff_mein::$field_id, staff_mein::$field_attachment_url, staff_mein::$field_title, staff_mein::$field_type));
        $staff_mein->set_custom_where(" AND (dic_title = '$showTitle') AND attachment_url is not NULL order by addtime desc");
//        $system->set_limit_paged(request_pageno(), request_pagesize());
        $db = create_pdo();
        $result = Model::query_list($db, $staff_mein, NULL, true);
        if (!$result[0])
            die_error(USER_ERROR, '获取图片信息失败，请重试');
        $models = Model::list_to_array($result['models'], array(), "id_2_text");

        echo_list_result($result, $models);
    }
    //下载图片
    if ($allAction == 3) {
        $url = $_GET["imageUrl"]; //获取参数 
        $curl = curl_init($url);
        $filename = date("Ymdhis") . ".jpg";
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $imageData = curl_exec($curl);
        curl_close($curl);
        $tp = @fopen($filename, 'a');
        fwrite($tp, $imageData);
        fclose($tp);
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $systemData = request_object();
    //添加相册
    if ($action == 1) {
        if ($systemData->picName == "" || $systemData->picName == "") {
            die_error(USER_ERROR, "相册名称不能为空...");
        }
        $staff_mein = new staff_mein();

        $staff_mein->set_dic_title($systemData->picName);
        $staff_mein->set_dic_addtime("now");
        $staff_mein->set_type(1);

        $db = create_pdo();
        $result = $staff_mein->insert($db);
        if (!$result[0])
            die_error(USER_ERROR, '添加相册失败。');
        echo_msg('添加成功');
    }
    //插入相册图片
    if ($action == 2) {
        if ($_POST["dirName"] == "" || $_POST["dirName"] == null) {
            die_error(USER_ERROR, "图片标题不能为空...");
        }
        if ($_FILES["file"] == "" || $_FILES["file"] == null) {
            die_error(USER_ERROR, "上传文件不能为空...");
        }
        $fileHZ = array(
            'image/bmp', 'image/png',
            'image/jpeg', 'image/gif',
            'application/octet-stream'
        );

        $fileImg = $_FILES["file"];

        $insertArray = array();

        for ($i = 0; $i < count($_FILES["file"]["name"]); $i++) {
            $filesize = $fileImg['size'][$i];
            $tmpName = $fileImg["tmp_name"][$i];
            if ($filesize > (40 * 1024 * 1000)) { //限制上传大小 
                die_error(USER_ERROR, '文件大小不能超过40M。');
            }
            if (!in_array($fileImg['type'][$i], $fileHZ)) {
                die_error(USER_ERROR, '文件格式不支持。');
            }

            /* UPYUN 上传图片到服务器 */
            $returnUrl = up($tmpName, $fileImg["name"][$i]);
            //给插入数据数组赋值
            $insertArray[$i]["dic_title"] = $_POST["dic_title"];
            $insertArray[$i]["dic_addtime"] = date("Y-m-d H:i:s", time());
            if (explode(".", $fileImg["name"])[1] == "rar" || explode(".", $fileImg["name"])[1] == "zip") {
                $insertArray[$i]["type"] = 2;
            } else {
                $insertArray[$i]["type"] = 1;
            }
            $insertArray[$i]["attachment_url"] = $returnUrl;
            $insertArray[$i]["title"] = $_POST["dirName"];
            $insertArray[$i]["addtime"] = date("Y-m-d H:i:s", time());
            $insertArray[$i]["upload_user"] = request_userid();
        }

        if (count($insertArray) > 0) {
            CommonHelper::Insert("staff_mein", $insertArray, TRUE);
        }

        echo_msg('添加成功');
    }
    /**
     * 删除相册
     * @param type $system
     */
    if ($action == 3) {
        $dic_title = $systemData->dic_title;
        if ($dic_title == "" || $dic_title == null) {
            die_error(USER_ERROR, "传递参数有误,请联系管理员...");
        }
        $staff = new staff_mein();
        $db = create_pdo();
        $staff->set_custom_where(" AND (dic_title = '$dic_title')");
        $result = $staff->delete($db);
        if (!$result[0])
            die_error(USER_ERROR, '删除相册信息失败');
        echo_msg('删除相册信息成功~');
    }
    /**
     * 删除图片
     * @param type $system
     */
    if ($action == 4) {
        $picId = $systemData->picId;
        if ($picId == "" || $picId == null) {
            die_error(USER_ERROR, "传递参数有误,请联系管理员...");
        }
        $staff = new staff_mein($picId);
        $db = create_pdo();
        $result = $staff->delete($db);
        if (!$result[0])
            die_error(USER_ERROR, '删除图片信息失败');
        echo_msg('删除图片信息成功~');
    }
    /**
     * 多个删除
     * @param type $system
     */
    if ($action == 5) {
        $delId = $systemData->delId;
        if ($delId == "" || $delId == null) {
            die_error(USER_ERROR, "传递参数有误,请联系管理员...");
        }
        $picArray = explode("&", $delId);
        for ($i = 0; $i < count($picArray); $i++) {
            $staff = new staff_mein($picArray[$i]);
            $db = create_pdo();
            $resultFinal = $staff->delete($db);
        }
        if (!$resultFinal[0])
            die_error(USER_ERROR, '删除图片信息失败');
        echo_msg('删除图片信息成功~');
    }
});

function del_system_files($system) {
    $files = $system->get_file_path();
    $pdf = $system->get_pdf_file_name();
    if (is_file(DEFAULT_FILE_UPLOAD_DIR . $files)) {
        unlink(DEFAULT_FILE_UPLOAD_DIR . $files);
    }
    if (is_file(DEFAULT_PDF_OUTPUT_DIR . $pdf)) {
        unlink(DEFAULT_PDF_OUTPUT_DIR . $pdf);
    }
    if (is_file(DEFAULT_SWF_OUTPUT_DIR . $pdf . '.' . 'swf')) {
        unlink(DEFAULT_SWF_OUTPUT_DIR . $pdf . '.' . 'swf');
    }
}

function up($url, $type) {
    try {
        $upyun = new UpYun("maihoho", "cheqin", "abc123456");
        $fh = file_get_contents($url);
        $md = md5(uniqid('', TRUE));   //substr(md5($url), 0, 10); //这边使用md5是防止生成相同文件名,命名方式很多 自定      
        $path = "/img" . '/';
        $hz = explode(".", $type)[1];
        if ($hz == "rar" || $hz == "zip") {
            $path .= "/img" . '/' . $type;
        } else {
            $path .= "/img" . '/' . $md . '.' . $hz;
        }

        $upyun->writeFile($path, $fh, True);
        return format("{0}{1}{2}", "http://", "maihoho.b0.upaiyun.com", $path);
    } catch (Exception $info) {
        echo $info->getCode();
        echo $info->getMessage();
    }
}

/* * 格式化字符串，类似string.format* */

function format() {
    $args = func_get_args();
    if (count($args) == 0) {
        return;
    }
    if (count($args) == 1) {
        return $args[0];
    }
    $str = array_shift($args);
    $str = preg_replace_callback('/\\{(0|[1-9]\\d*)\\}/', create_function('$match', '$args = ' . var_export($args, true) . '; return isset($args[$match[1]]) ? $args[$match[1]] : $match[0];'), $str);
    return $str;
}
