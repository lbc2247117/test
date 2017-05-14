<?php

/**
 * 员工手册
 *
 * @author Qi
 * @copyright 2015 星密码
 * @version 2015/5/13
 */
use Models\Base\Model;
use Models\M_Role;
use Models\E_Manual;
use Models\Base\SqlOperator;
use Models\Base\SqlSortType;

require '../../application.php';
require '../../loader-api.php';
require '../../Common/FileUpload.php';
require '../../Common/word2pdf.php';
require '../../Helper/upyun.class.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() {
    $manual = new E_Manual();
    $sort = request_string('sort');
    $sortname = request_string('sortname');
    $searchName = request_string('searchName');
    $sysclass = request_string("sys_class");
    if (isset($searchName)) {
        $manual->set_where_and(E_Manual::$field_sys_title, SqlOperator::Like, '%' . $searchName . '%');
    }
    if (isset($sysclass)) {
        if ($sysclass == "自定义") {
            $manual->set_where_and(E_Manual::$field_sys_class, SqlOperator::NotEquals, "福利");
            $manual->set_where_and(E_Manual::$field_sys_class, SqlOperator::NotEquals, "考勤");
        } else {
            $manual->set_where_and(E_Manual::$field_sys_class, SqlOperator::Equals, $sysclass);
        }
    }
    if (isset($sort) && isset($sortname)) {
        $manual->set_order_by(E_Manual::$field_top, 'DESC');
        $manual->set_order_by(E_Manual::$field_date, 'DESC');
        $manual->set_order_by($manual->get_field_by_name($sortname), $sort);
    } else {
        $manual->set_order_by(E_Manual::$field_top, 'DESC');
        $manual->set_order_by(E_Manual::$field_date, 'DESC');
        $manual->set_order_by(E_Manual::$field_date, SqlSortType::Desc);
    }
    $manual->set_limit_paged(request_pageno(), request_pagesize());
    $db = create_pdo();
    $result = Model::query_list($db, $manual, NULL, true);
    if (!$result[0])
        die_error(USER_ERROR, '获取申购资料失败，请重试');
    $models = Model::list_to_array($result['models'], array(), "id_2_text");
    echo_list_result($result, $models);
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    $manualData = request_object();
    //添加制度内容
    if ($action == 1) {
        if ($_POST["sys_title"] == "" || $_POST["sys_title"] == null) {
            die_error(USER_ERROR, "手册标题不能为空...");
        }
        if ($_POST["sys_class"] == "" || $_POST["sys_class"] == null) {
            die_error(USER_ERROR, "手册分类不能为空...");
        }
        if ($_POST["sys_content"] == "" || $_POST["sys_content"] == null) {
            die_error(USER_ERROR, "手册内容不能为空...");
        }
        if ($_FILES["file"] == "" || $_FILES["file"] == null) {
            die_error(USER_ERROR, "上传文件不能为空...");
        }
        $fileHZ = array(
            'application/octet-stream',
            'application/msword',
            'application/vnd.ms-powerpoint',
            'application/vnd.ms-excel',
            'text/plain', 'text/html',
            'image/jpeg', 'image/gif',
            'application/pdf'
        );
        $file = $_FILES["file"];
        $fileName = $file["name"];
        $filesize = $file['size'];
        if ($filesize > (40 * 1024 * 1000)) { //限制上传大小 
            die_error(USER_ERROR, '文件大小不能超过40M。');
        }
        if (!in_array($file['type'], $fileHZ)) {
            die_error(USER_ERROR, '文件格式不支持。');
        }
        $files = $_FILES["file"]["tmp_name"];
        /* UPYUN 上传图片到服务器 */
        $returnUrl = up($files, $fileName);

        $manualInsert = new E_Manual();

        $manualInsert->set_sys_title($_POST["sys_title"]);
        $manualInsert->set_sys_content($_POST["sys_content"]);
        $manualInsert->set_sys_class($_POST["sys_class"]);
        $manualInsert->set_date(date("Y-m-d H:i:s", time()));
        $manualInsert->set_top(0);
        $manualInsert->set_file_path($returnUrl);
        $manualInsert->set_pdf_file_name($fileName);
        $manualInsert->set_userid(request_userid());
        $dbmanual = create_pdo();
        $result = $manualInsert->insert($dbmanual);
        if (!$result[0])
            die_error(USER_ERROR, '添加制度内容失败。');
        echo_msg('添加成功');
    }
    //修改制度内容信息
    if ($action == 2) {
        if ($_POST["sys_title"] == "" || $_POST["sys_title"] == null) {
            die_error(USER_ERROR, "手册标题不能为空...");
        }
        if ($_POST["sys_class"] == "" || $_POST["sys_class"] == null) {
            die_error(USER_ERROR, "手册分类不能为空...");
        }
        if ($_POST["sys_content"] == "" || $_POST["sys_content"] == null) {
            die_error(USER_ERROR, "手册内容不能为空...");
        }
        $fileHZ = array(
            'application/octet-stream',
            'application/msword',
            'application/vnd.ms-powerpoint',
            'application/vnd.ms-excel',
            'text/plain', 'text/html',
            'image/jpeg', 'image/gif',
            'application/pdf'
        );
        $id = $_POST["id"];
        $returnUrl = "";
        if ($_FILES["editFile"] != "" || $_FILES["editFile"] != null) {
            $file = $_FILES["editFile"];
            $fileName = $file["name"];
            $filesize = $file['size'];
            if ($filesize > (40 * 1024 * 1000)) { //限制上传大小 
                die_error(USER_ERROR, '文件大小不能超过40M。');
            }
            if (!in_array($file['type'], $fileHZ)) {
                die_error(USER_ERROR, '文件格式不支持。');
            }

            $files = $_FILES["editFile"]["tmp_name"];
            /* UPYUN 上传图片到服务器 */
            $returnUrl = up($files, $fileName);
        }

        $dbMan = create_pdo();
        $editManual = new E_Manual($id);
        $editManual->load($dbMan, $editManual);

        $editManual->set_sys_title($_POST["sys_title"]);
        $editManual->set_sys_content($_POST["sys_content"]);
        $editManual->set_sys_class($_POST["sys_class"]);
        $editManual->set_date(date("Y-m-d H:i:s", time()));
        $editManual->set_top(0);
        if (!empty($returnUrl)) {
            $editManual->set_file_path($returnUrl);
            $editManual->set_pdf_file_name($fileName);
        }
        $editManual->set_userid(request_userid());

        $result = $editManual->update($dbMan, true);
        if (!$result[0])
            die_error(USER_ERROR, '保存制度内容失败');
        echo_msg('保存成功');
    }
    //修改置顶
    if ($action == 4) {
        $usystem = new E_Manual();
        $usystem->set_where(E_Manual::$field_top, SqlOperator::Equals, 1);
        $usystem->set_top(0);
        $manual = new E_Manual();
        $manual->set_field_from_array($manualData);
        $manual->set_top(1);
        $db = create_pdo();
        pdo_transaction($db, function($db) use($usystem, $manual) {
            $result = $usystem->update($db, true);
            if (!$result[0])
                throw new TransactionException(PDO_ERROR_CODE, '置顶失败', $result);
            $result = $manual->update($db, true);
            if (!$result[0])
                throw new TransactionException(PDO_ERROR_CODE, '置顶失败', $result);
        });
        echo_msg('置顶成功');
    }

    if ($action == 5) {
        $usystem = new E_Manual($manualData->id);
        $db = create_pdo();
        $result = $usystem->delete($db);
        if (!$result[0])
            die_error(USER_ERROR, "删除制度内容失败~");
        if (is_file(DEFAULT_FILE_UPLOAD_DIR . ($manualData->file_path))) {
            unlink(DEFAULT_FILE_UPLOAD_DIR . ($manualData->file_path));
        }
        if (is_file(DEFAULT_PDF_OUTPUT_DIR . ($manualData->pdf_file_name))) {
            unlink(DEFAULT_PDF_OUTPUT_DIR . ($manualData->pdf_file_name));
        }
        if (is_file(DEFAULT_SWF_OUTPUT_DIR . ($manualData->pdf_file_name) . '.' . 'swf')) {
            unlink(DEFAULT_SWF_OUTPUT_DIR . ($manualData->pdf_file_name) . '.' . 'swf');
        }
        echo_msg("删除制度内容成功~");
    }
});

function del_system_files($manual) {
    $files = $manual->get_file_path();
    $pdf = $manual->get_pdf_file_name();
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
        //$md = md5(uniqid('', TRUE));   //substr(md5($url), 0, 10); //这边使用md5是防止生成相同文件名,命名方式很多 自定      
        $path = "/img" . '/' . $type;
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
