<?php

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;

/**
 * 上传文件到七牛云
 * 
 * @param string $filePath path and filename
 * @param string $fileName filename
 * 
 * @return bool SUCCESS or FAIL
 */
function uploadToQiNiu($filePath, $fileName) {
    Vendor('qiniu.autoload');
    $accessKey = ACCESSKEY;
    $secretKey = SECRETKEY;
    $auth = new Auth($accessKey, $secretKey);
    $bucket = QINIUNAMESPACE;
    $token = $auth->uploadToken($bucket);
    $key = $fileName;
    $uploadMgr = new UploadManager();
    list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
    if ($err !== null) {
        return FALSE;
    } else {
        return TRUE;
    }
}

/**
 * 删除七牛云的文件
 * 
 * @param string $fileName filename
 * 
 * @return bool TRUE or FALSE
 */
function deleteFromQiNiu($fileName) {
    Vendor('qiniu.autoload');
    $accessKey = ACCESSKEY;
    $secretKey = SECRETKEY;
    $auth = new Auth($accessKey, $secretKey);
    $bucketMgr = new BucketManager($auth);
    $bucket = QINIUNAMESPACE;
    $err = $bucketMgr->delete($bucket, $fileName);
    if ($err !== null) {
        return FALSE;
    } else {
        return TRUE;
    }
}

/**
 * 上传文件
 * 
 * @param Array $upLoadFile 前端input file标签的name
 * @param String $sourceFile 要删除的文件名
 */
function uploadFile($upLoadFile, $sourceFile = NULL) {

    //上传文件
    $extension = pathinfo($upLoadFile['name'], PATHINFO_EXTENSION);
    $filePath = UPLOADPATH . '/img/';
    $tmpFile = $upLoadFile['tmp_name'];
    if (!file_exists($filePath)) {
        mkdir($filePath, 0777, true);
        chmod($filePath, 0777);
    }
    $fileArr = explode('.', $upLoadFile['basename']);
    $fileName = md5(time() . $fileArr[0]) . '.' . $extension;
    if (!move_uploaded_file($tmpFile, $filePath . $fileName))
        returnJson(0, '上传文件到服务器失败');
    if (!uploadToQiNiu($filePath . $fileName, $fileName))
        returnJson(0, '上传文件到七牛云失败');
    //删除文件
    if ($sourceFile) {
        $baseName = pathinfo($sourceFile, PATHINFO_BASENAME);
        unlink($filePath . $baseName); //删除本地文件
        deleteFromQiNiu($baseName); //删除七牛云文件
    }
    return OUTLINK . '/' . $fileName;
}

/**
 * 删除本地文件和七牛云文件
 *
 * @param String $file File fullPath  
 * 
 * @return Bool Success or Fail
 * 
 * @author 刘波成 <22913113@qq.com>
 */
function delFile($file) {
    $baseName = pathinfo($file, PATHINFO_BASENAME);
    $filePath = UPLOADPATH . '/img/';
    $rstLocal = unlink($filePath . $baseName); //删除本地文件
    $rstQiNiu = deleteFromQiNiu($baseName); //删除七牛云文件
    if ($rstLocal && $rstQiNiu)
        return TRUE;
    return FALSE;
}

function returnJson($status = 0, $msg = '', $data = '') {
    $result['status'] = $status;
    $result['msg'] = $msg;
    $result['data'] = $data;
    exit(json_encode($result));
}
