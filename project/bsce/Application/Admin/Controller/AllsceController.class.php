<?php

namespace Admin\Controller;

use Think\Controller;

/**
 * 景区相关接口   
 */
defined('UPLOAD_PATH') or define('UPLOAD_PATH', 'upload/');        //上传图片路径
defined('UPLOAD_PICTURE_PATH') or define('UPLOAD_PICTURE_PATH', 'picture/');        //上传图片路径
defined('UPLOAD_SCE') or define('UPLOAD_SCE', 'picture/allsce/');        //景区图片存放
defined('UPLOAD_MAP') or define('UPLOAD_MAP', 'picture/scemap/');        //景点图片存放
defined('UPLOAD_TMP_PATH') or define('UPLOAD_TMP_PATH', 'tmp');    //图片上传的临时目录
defined('UPLOAD_ORG_PATH') or define('UPLOAD_ORG_PATH', 'org');    //原始图片保存目录
defined('UPLOAD_THUMB_PATH') or define('UPLOAD_THUMB_PATH', 'thumb');      //裁剪后图片保存目录
defined('THUMB_SIZE') or define('THUMB_SIZE', 600);                //裁剪的大小规格
defined('THUMB_PREFIX') or define('THUMB_PREFIX', '600_600_');     //裁剪图片的前缀

defined('UPLOAD_VIDEO_PATH') or define('UPLOAD_VIDEO_PATH', 'video/');        //上传视频路径
defined('AUDIO') or define('AUDIO', 'audio/');        //上传音频路径
defined('BSCE') or define('BSCE', 'bsce/');        //b端景区的视频资源
defined('ALLSCE') or define('ALLSCE', 'allsce');        //景区图片存放
defined('SCEMAP') or define('SCEMAP', 'scemap/');        //景区图片存放
defined('PUBLIC_PATH') or define('PUBLIC_PATH', 'public/');        //上传图片路径

class AllsceController extends BaseController {

    //添加景区节目
    public function addFestival() {
        $ScenicSpotID = $this->selectId($this->lon, $this->lat);
        $name = I('post.name') ? I('post.name') : $this->returnJson(0, '请填写节目名称');
        $remark = I('post.remark') ? I('post.remark') : '-1';
        $videoFile = $_FILES['festivalVideo'];
        $coverFile = $_FILES['festivalCover'];
        if (empty($videoFile['name']))
            $this->returnJson(0, '请上传节目视频');
        if (empty($coverFile['name']))
            $this->returnJson(0, '请上传节目视频封面');
        $videoUrl = $this->uploadPic($videoFile, 'festival/video', $this->lat, $this->lon, 200 * 1024 * 1024);
        $videoPic = $this->uploadPic($coverFile, 'festival/cover', $this->lat, $this->lon, 1024 * 1024);
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=addScenicSpotProgramVo&name=' . urlencode($name) . '&remark=' . urlencode($remark) . '&ScenicSpotID=' . $ScenicSpotID . '&videoPic=' . urlencode($videoPic) . '&videoUrl=' . urlencode($videoUrl);
        $result = json_decode(file_get_contents($url), TRUE);
        if ($result['flag'] != '1')
            $this->returnJson(0, $result['result']);
        $this->returnJson(1, '操作成功');
    }

    //删除景区节目
    public function delFestival() {
        $id = I('post.id') ? I('post.id') : $this->returnJson(0, '缺少景区节目ID');
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=delScenicSpotProgramVo&id=' . $id;
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] != '1')
            $this->returnJson(0, $result['result']);
        $this->returnJson(1, '删除成功');
    }

    //修改景区节目
    public function editFestival() {
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=modifyScenicSpotProgramVo';
        $id = I('post.id') ? I('post.id') : $this->returnJson(0, '缺少景区节目ID');
        $name = I('post.name') ? I('post.name') : $this->returnJson(0, '缺少节目名称');
        if (mb_strlen(I('post.remark'), 'utf-8') > 500) {
            $this->returnJson(0, '请填写节目描述,长度为0到500个字符');
        }
        $remark = I('post.remark') ? I('post.remark') : '-1';
        $remark = urldecode($remark);
        $url.='&id=' . $id . '&name=' . urlencode($name) . '&remark=' . urlencode($remark);
        $videoFile = $_FILES['festivalVideo'];
        $coverFile = $_FILES['festivalCover'];
        if (!empty($coverFile['name'])) {       //如果有图片
            $videoPic = $this->uploadPic($coverFile, 'festival/cover', $this->lat, $this->lon, 1024 * 1024);
            $url.='&videoPic=' . urlencode($videoPic);
        }
        if (!empty($videoFile['name'])) {   //如果有视频
            $videoUrl = $this->uploadPic($videoFile, 'festival/video', $this->lat, $this->lon, 200 * 1024 * 1024);
            $url.='&videoUrl=' . urlencode($videoUrl);
        }
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] != '1')
            $this->returnJson(0, $result['result']);
        $this->returnJson(1, '操作成功');
    }

    //美景直播查询
    public function updateLive() {
        //http://ip:port/ssh2/sceman?cmd=modifyLCVideo&userID&cityID&lon&lat&maplon&maplat&sceName&signState&remark&videoName&videoPic&videoPath&videoAdress&hourLeng&videoWidth&videoHeight&isshow&videoTag&logoState&audioUrl&videoType&isOfficial&id
        //接收参数
        $id = I('post.id'); //573d9bbca434a115134198d9
        $videoName = I('post.videoName');
        $remark = I('post.remark');
        $videoTag = I('post.videoTag');
        $audioUrl = I('post.audioUrl');
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=modifyLCVideo&id=' . $id;
        !empty($videoName) && $apiUrl .= '&videoName=' . urlencode($videoName);
        !empty($remark) && $apiUrl .= '&remark=' . urlencode($remark);
        !empty($videoTag) && $apiUrl .= '&videoTag=' . $videoTag;
        !is_null($audioUrl) && $audioUrl != 2 && $apiUrl .= '&audioUrl=' . $audioUrl;
        if (!empty($_FILES['cover']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 1 * 1024 * 1024; // 1M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = UPLOAD_PICTURE_PATH . BSCE . UPLOAD_VIDEO_PATH;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['cover']);
            if (!empty($info)) {
                $apiUrl .= '&videoPic=' . $imgpath . $info['savepath'] . $info['savename'];
            } else {
                $this->returnJson(0, '上传图片出错,检查图片格式和大小');
                exit;
            }
        }
        //处理音频的上传
        if (!empty($_FILES['audio']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 20 * 1024 * 1024; // 4M   
            $upload->exts = array('mp3');
            $audiopath = UPLOAD_PICTURE_PATH . BSCE . AUDIO;
            $upload->rootPath = UPLOAD_PATH . $audiopath;
            if (!is_dir($upload->rootPath)) {       // 创建目录
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['audio']);
            if (!empty($info)) {
                $audioPath = $audiopath . $info['savepath'] . $info['savename'];
                $audioUrl == 2 && $apiUrl .= '&audioUrl=' . $audioPath;
            } else {
                $this->returnJson(0, '音频上传失败！检查格式或大小');
            }
        }
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        //数据返回
        if ($resultApi['flag'] == 1)
            $this->returnJson(1, '成功');
        else if ($resultApi['flag'] == 0)
            $this->returnJson(0, $resultApi['result']);
        else
            $this->returnJson(0, '网络异常，稍后再试！');
    }

    //获取直播视频的标签
    public function getLivetag() {
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryCameraTag&page=1&size=100';
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        //数据返回
        if ($resultApi['flag'] == 1)
            $this->returnJson(1, '成功', $resultApi['result']);
        else if ($resultApi['flag'] == 0)
            $this->returnJson(0, $resultApi['result']);
        else
            $this->returnJson(0, '网络异常，稍后再试！');
    }

    //根据视频id查询视频信息
    public function getVideomsg() {
        //http://ip:port/ssh2/sceman?cmd=queryCameraVideoVo&id
        $id = I('post.id');
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryCameraVideoVo&id=' . $id;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if ($resultApi['result']['audioUrl'] != 0 && $resultApi['result']['audioUrl'] != 1)
            $resultApi['result']['audioUrl'] = C('FULL_PATH') . $resultApi['result']['audioUrl'];
        $resultApi['result']['videoPic'] = C('FULL_PATH') . $resultApi['result']['videoPic'];
        $resultApi['result']['videoPath'] = C('FULL_PATH') . $resultApi['result']['videoPath'];
        //数据返回
        if ($resultApi['flag'] == 1)
            $this->returnJson(1, '成功', $resultApi['result']);
        else if ($resultApi['flag'] == 0)
            $this->returnJson(0, $resultApi['result']);
        else
            $this->returnJson(0, '网络异常，稍后再试！');
    }

    //景区展示板块
    //景区基本资料-----景区查询
    public function selectAllcse() {

        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotVo&page&size&lon&lat
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotVo&page=1&size=20&lon=' . $this->lon . '&lat=' . $this->lat;
        //调用接口并返回数据
        $resultApi = json_decode(file_get_contents($url), true);
        //数据返回
        if ($resultApi['flag'] == 1) {
            $resultApi['result']['jumpurl'] = C('SCE_HOME_URL') . '&lon=' . $this->lon . '&lat=' . $this->lat;
            $resultApi['result']['otherremark'] = urldecode($resultApi['result']['otherremark']);
            $resultApi['result'][0]['backgroundpic'] = C('FULL_PATH') . $resultApi['result'][0]['backgroundpic'];
            $resultApi['result'][0]['ewmUrl'] = C('IMG_PRE') . $resultApi['result'][0]['ewmUrl'];
            foreach ($resultApi['result'][0]['ScenicSpotProgramVo'] as $key => $value) {
                $resultApi['result'][0]['ScenicSpotProgramVo'][$key]['videoPic'] = C('FULL_PATH') . $value['videoPic'];
                $resultApi['result'][0]['ScenicSpotProgramVo'][$key]['videoUrl'] = C('FULL_PATH') . $value['videoUrl'];
                if ($value['remark'] == '-1')
                    $resultApi['result'][0]['ScenicSpotProgramVo'][$key]['remark'] = '';
            }
            $resultApi['result'][0]['guidePic'] = C('FULL_PATH') . $resultApi['result'][0]['guidePic'];
            $resultApi['result'][0]['specVideoPic'] = C('FULL_PATH') . $resultApi['result'][0]['specVideoPic'];
            $resultApi['result'][0]['specVideoUrl'] = C('FULL_PATH') . $resultApi['result'][0]['specVideoUrl'];
            $resultApi['result'][0]['sceRemarkVideoUrl'] = C('FULL_PATH') . $resultApi['result'][0]['sceRemarkVideoUrl'];
            $resultApi['result'][0]['sceRemarkVideoPic'] = C('FULL_PATH') . $resultApi['result'][0]['sceRemarkVideoPic'];
            if ($resultApi['result'][0]['carefulContent'] == '-1')
                $resultApi['result'][0]['carefulContent'] = '';
            $this->returnJson(1, '成功', $resultApi['result']);
        } else if ($resultApi['flag'] == 0)
            $this->returnJson(0, $resultApi['result']);
        else
            $this->returnJson(0, '网络异常，稍后再试！');
    }

    //查询景区标签
    public function sceClassify() {
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=querySceTag&page=1&size=20';
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1)
            $this->returnJson(1, '操作成功', $result['result']);
        else
            $this->returnJson(0, $result['result']);
    }

    //景区展示板块
    //景区基本资料-----景区修改
    public function saveAllsce() {
        $lon = $this->lon;
        $lat = $this->lat;
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=modifyScenicSpotVo&modifyType=1&lon=' . $lon . '&lat=' . $lat;
        $sceType = I('post.sceType');
        if (!empty($sceType)) {     //景区标签
            $sceType = explode(',', $sceType);
            $arrSceType = array();
            for ($i = 0; $i < count($sceType); $i++) {
                $arrSceType[$i]['val'] = $sceType[$i];
            }
            $sceType = json_encode($arrSceType);
            $url.='&sceType=' . $sceType;
        }
        $audefinedType = I('post.audefinedType');   //搜索关键字
        if (mb_strlen(I('post.audefinedType'), 'utf-8') > 30) {
            $this->returnJson(0, '请填写关键词,长度为0到30个字符');
        }
        if (!empty($audefinedType)) {
            $audefinedType = explode(',', $audefinedType);
            $arraudefinedType = array();
            for ($i = 0; $i < count($audefinedType); $i++) {
                $arraudefinedType[$i]['val'] = $audefinedType[$i];
            }
            $audefinedType = json_encode($arraudefinedType);
            $url.='&audefinedType=' . $audefinedType;
        }
        if (mb_strlen(I('post.sceSynopsis'), 'utf-8') > 120) {
            $this->returnJson(0, '请填写景区描述,长度为0到120个字符');
        }
        $sceSynopsis = urldecode(I('post.sceSynopsis'));
        $sceSynopsis ? $url.='&sceSynopsis=' . urlencode($sceSynopsis) : ''; //景区描述
        if (mb_strlen(I('post.sceRemark'), 'utf-8') > 200) {
            $this->returnJson(0, '请填写景区简介,长度为0到200个字符');
        }
        $sceRemark = urldecode(I('post.sceRemark'));
        $sceRemark ? $url.='&sceRemark=' . urlencode($sceRemark) : ''; //景区简介
        if (mb_strlen(I('post.otherremark'), 'utf-8') > 500) {
            $this->returnJson(0, '请填写景区交通,长度为0到500个字符');
        }
        $otherremark = urldecode(I('post.otherremark'));
        $otherremark ? $url.='&otherremark=' . urlencode($otherremark) : ''; //景区交通
        if (mb_strlen(I('post.specRemark'), 'utf-8') > 500) {
            $this->returnJson(0, '请填写景区特色,长度为0到500个字符');
        }
        $specRemark = urldecode(I('post.specRemark'));
        $specRemark ? $url.='&SpecRemark=' . urlencode($specRemark) : ''; //特色介绍
        if (mb_strlen(I('post.carefulContent'), 'utf-8') > 500) {
            $this->returnJson(0, '请填写注意事项,长度为0到500个字符');
        }
        $carefulContent = urldecode(I('post.carefulContent'));
        $carefulContent ? $url.='&carefulContent=' . urlencode($carefulContent) : ''; //注意事项
        if (!empty($_FILES['cover']['name'])) {     //背景图片
            $file = $_FILES['cover'];
            $backgroudPic = $this->uploadPic($file, 'sce', $this->lat, $this->lon);
            $url.='&backgroundpic=' . urlencode($backgroudPic);
        }
        if (!empty($_FILES['guidePic']['name'])) {     //景区导览图
            $file = $_FILES['guidePic'];
            $guidePic = $this->uploadPic($file, 'sce/guide', $this->lat, $this->lon);
            $url.='&guidePic=' . urlencode($guidePic);
        }
        $sceRemarkVideo = $_FILES['sceRemarkVideo'];
        $sceRemarkCover = $_FILES['sceRemarkCover'];
        $specRemarkVideo = $_FILES['specRemarkVideo'];
        $specRemarkCover = $_FILES['specRemarkCover'];
        if (!empty($sceRemarkVideo)) {
            $sceRemarkVideo = $this->uploadPic($sceRemarkVideo, 'sce/video', $lat, $lon, 200 * 1024 * 1024);
            $url.='&sceRemarkVideoUrl=' . urlencode($sceRemarkVideo);
        }
        if (!empty($sceRemarkCover)) {
            $sceRemarkCover = $this->uploadPic($sceRemarkCover, 'sce/cover', $lat, $lon, 1024 * 1024);
            $url.='&sceRemarkVideoPic=' . urlencode($sceRemarkCover);
        }
        if (!empty($specRemarkVideo)) {
            $specRemarkVideo = $this->uploadPic($specRemarkVideo, 'sce/video', $lat, $lon, 200 * 1024 * 1024);
            $url.='&SpecVideoUrl=' . urlencode($specRemarkVideo);
        }
        if (!empty($specRemarkCover)) {
            $specRemarkCover = $this->uploadPic($specRemarkCover, 'sce/cover', $lat, $lon, 1024 * 1024);
            $url.='&SpecVideoPic=' . urlencode($specRemarkCover);
        }
        $result = file_get_contents($url);
        if ((json_decode($result)->flag) == 1) {
            $this->returnJson(1, '操作成功');
        } else {
            $this->returnJson(0, json_decode($result)->result);
        }
    }

    //景区展示板块
    //景区图片-----查询景区或者景点图片
    public function selectPicture() {
        //http://ip:port/ssh2/sceman?cmd=querySceMapPicBylonlat&lon&lat&page&size&type
        $param = I('post.');
        extract($param);
        $is_param = 1;

        !empty($lonlat) ? $lonlat : $is_param = 0;
        $serchType = empty($serchType) ? 0 : 1;  //0：发布时间 1：查看最多
        $page = I('post.page');
        $size = I('post.size');
        $page = empty($page) ? 1 : $page;
        $size = empty($size) ? 20 : $size;
        if ($is_param == 0)
            $this->returnJson(0, '参数缺失');
        if ($lonlat == -1) {
            $lon = $this->lon;
            $lat = $this->lat;
            $type = 0;
        } else {
            $lonlat = explode(',', $lonlat);
            $lon = $lonlat[0];
            $lat = $lonlat[1];
            $type = 1;
        }
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=querySceMapPicBylonlat&lon=' . $lon . '&lat=' . $lat . '&page=' . $page . '&size=' . $size . '&type=' . $type;
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1) {
            for ($i = 0; $i < count($result['result']['data']); $i++) {
                $result['result']['data'][$i]['picShortPath'] = C('FULL_PATH') . $result['result']['data'][$i]['picShortPath'];
            }
            $this->returnJson(1, '操作成功', $result['result']);
        } else {
            $this->returnJson(0, $result['result']);
        }
    }

    //根据景区经纬度查询景点
    public function selectScemap() {
        //http://ip:port/ssh2/sceman?cmd=querySceMapBylonlat&lon&lat&page&size
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=querySceMapBylonlat&lon=' . $this->lon . '&lat=' . $this->lat . '&page=1&size=20';
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1)
            $this->returnJson(1, '操作成功', $result['result']);
        else
            $this->returnJson(0, $result['result']);
    }

    //景区展示板块
    //多图上传接口
    public function addPicture() {
        $lonlat = I('post.lonlat');
        if ($lonlat == -1) {
            $Type = 1;
            $Longitude = $this->lon;
            $Latitude = $this->lat;
            $maplon = 0;
            $maplat = 0;
        } else {
            $point = explode(',', $lonlat);
            $Type = 2;
            $Longitude = $this->lon;
            $Latitude = $this->lat;
            $maplon = $point[0];
            $maplat = $point[1];
        }
        //判断是否存在上传文件
        if (!empty($_FILES)) {
            $maxFileAge = 5 * 3600; // Temp file age in seconds
            switch ($Type) {
                case 1:     //景区
                    $headPath = APP_PATH . '../' . UPLOAD_PATH . UPLOAD_SCE . $Longitude . '+' . $Latitude . '/';
                    break;
                case 2:     //景点
                    $headPath = APP_PATH . '../' . UPLOAD_PATH . UPLOAD_MAP . $maplon . '+' . $maplat . '/';
                    break;

                default:
                    $this->returnJson(0, 'type参数错误！');
                    exit();
            }
            $tmpPath = $headPath . UPLOAD_TMP_PATH;
            //创建临时目录
            if (!file_exists($tmpPath)) {
                @mkdir($tmpPath, 0777, true);
            }

            // Get a file name
            $ext = "jpg";
            if (isset($_REQUEST["name"])) {
                $ext = pathinfo($_REQUEST["name"], PATHINFO_EXTENSION);
            } elseif (!empty($_FILES)) {
                $ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
            }
            $fileName = uniqid("") . '.' . $ext;
            //$fileName = iconv('UTF-8', 'GB2312', $fileName);//转编码
            //上传临时文件路径
            $filePath = $tmpPath . '/' . $fileName;
            // Chunking might be enabled
            $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
            $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;
            //打开临时目录
            if (!is_dir($tmpPath) || !$dir = opendir($tmpPath)) {
                echo ('{"status": "1", "msg" : {"message": "打开tm目录失败."}}');
                return;
            }
            //清理临时目录中的临时文件
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $tmpPath . '/' . $file;
                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                    continue;
                }
                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
            // Open temp file
            if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
                echo ('{"status": "0", "msg" : {"message": "Failed to open output stream."}}');
                return;
            }
            //读取上传文件的分片文件
            if (!empty($_FILES)) {
                if ($_FILES["file"]["msg"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                    echo ('{"status": "0", "msg" : {"message": "Failed to move uploaded file."}}');
                    return;
                }
                // Read binary input stream and append it to temp file
                if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                    echo ('{"status": "0", "msg" : {"message": "Failed to open input stream."}}');
                    return;
                }
            } else {
                if (!$in = @fopen("php://input", "rb")) {
                    echo ('{"status": "0", "msg" : {"message": "Failed to open input stream."}}');
                    return;
                }
            }
            //写入到临时文件
            while ($buff = fread($in, 4096)) {
                fwrite($out, $buff);
            }
            @fclose($out);
            @fclose($in);
            //改名
            rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");

            $index = 0;
            $done = true;
            for ($index = 0; $index < $chunks; $index++) {
                if (!file_exists("{$filePath}_{$index}.part")) {
                    $done = false;
                    break;
                }
            }
            if ($done) {
                $orgPath = $headPath . UPLOAD_ORG_PATH;
                //创建原始图目录
                if (!file_exists($orgPath)) {
                    @mkdir($orgPath, 0777, true);
                }
                if (!$out = @fopen($orgPath . '/' . $fileName, "wb")) {
                    echo ('{"status": "0", "msg" : {"message": "Failed to open output stream."}}');
                    return;
                }

                if (flock($out, LOCK_EX)) {
                    for ($index = 0; $index < $chunks; $index++) {
                        if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
                            break;
                        }

                        while ($buff = fread($in, 4096)) {
                            fwrite($out, $buff);
                        }

                        @fclose($in);
                        @unlink("{$filePath}_{$index}.part");
                    }

                    flock($out, LOCK_UN);
                }
                @fclose($out);
            }
            //判断是否有图
            if ($orgPath) {
                $image = new \Think\Image();
                $image->open($orgPath . '/' . $fileName);
                // 按照原图的比例生成一个最大为缩略图尺寸的缩略图并保存为.jpg
                $thumbPath = $headPath . UPLOAD_THUMB_PATH;
                //创建缩略图目录
                if (!file_exists($thumbPath)) {
                    @mkdir($thumbPath, 0777, true);
                }
                $thunmpathname = $thumbPath . '/' . THUMB_PREFIX . $fileName;
                $image->thumb(THUMB_SIZE, THUMB_SIZE)->save($thunmpathname);
                //获得缩略图的宽高
                $imginfo = getimagesize($thunmpathname);
                //将图片存放在数据库中去
                $thumbname = THUMB_PREFIX . $fileName;
                $this->pushImg($Longitude, $Latitude, $maplon, $maplat, $fileName, $thumbname, $imginfo, $Type);
                if (true == $this->saveOrigin)
                    $rejson = '{"status": "1", "msg" : "' . $orgPath . '/' . $fileName . '"}';
                else
                    $rejson = '{"status": "1", "msg" : "' . $thumbPath . '/' . THUMB_PREFIX . $fileName . '"}';
                //回应路径
                echo $rejson;
            }else {
                echo ('{"status": "0", "msg" : {"message": "Failed to open org file."}}');
            }
        }
    }

    //推送数据到mogodb中去
    public function pushImg($Longitude, $Latitude, $maplon, $maplat, $fileName, $thumbname, $imginfo, $Type) {
        switch ($Type) {
            case 1:     //景区
                $headPath = UPLOAD_SCE . $Longitude . '+' . $Latitude . '/';
                break;
            case 2:     //景区
                $headPath = UPLOAD_MAP . $maplon . '+' . $maplat . '/';
                break;

            default:
                $this->returnJson(0, 'type参数错误！');
                exit();
        }
        $Imgorg = $headPath . UPLOAD_ORG_PATH . '/' . $fileName;
        $Imgthumb = $headPath . UPLOAD_THUMB_PATH . '/' . $thumbname;
        $Width = $imginfo[0];
        $Height = $imginfo[1];
        $where['Longitude'] = $Longitude;
        $where['Latitude'] = $Latitude;
        $city = M('AllSce')->where($where)->field('CityID')->find();
        //组装url
        $tel = C('OFFICIAL_TEL');
        $url_sceimg = C('IP_BASE') . '/ssh2/sceman?cmd=addPictureVo&userID=' . $tel;
        $url_sceimg .= '&lon=' . $Longitude . '&lat=' . $Latitude . '&picShortPath=' . urlencode($Imgthumb) . '&picPath=' . urlencode($Imgorg);
        $url_sceimg .= '&picType=1&isOfficial=2&cityID=' . $city['CityID'] . '&picWidth=' . $Width . '&picHeight=' . $Height . '&maplon=' . $maplon . '&maplat=' . $maplat;
        $result_sceimg = file_get_contents($url_sceimg, true);
        return $result_sceimg;
    }

    //景区展示板块
    //景区图片-----删除图片
    public function deletPicture() {
        $ids = I('post.ids');
        $picUrl = I('post.picUrl');
        $shortPic = I('post.shortPic');
        if (!($ids && $picUrl))
            $this->returnJson(0, '参数缺失');
        foreach ($ids as $key => $id) {
            $url = C('IP_BASE') . '/ssh2/sceman?cmd=delPictureVo&id=' . $id;
            $result = json_decode(file_get_contents($url), true);
            if ($result['flag'] == 1) {
                $picpath = UPLOAD_PATH . $picUrl[$key];
                $shortPath = str_replace('../../', '', $shortPic[$key]);
                unlink($picpath);
                unlink($shortPath);
                continue;
            } else
                $this->returnJson(0, '删除失败，请稍后重试');
        }
        $this->returnJson(1, '删除成功');
    }

    //景区展示板块
    //景区视频 ----- 视频标签
    public function selectTag() {
        //http://ip:port/ssh2/sceman?cmd=queryCameraTag&page&size

        $url = C('IP_BASE') . '/ssh2/sceman?cmd=queryCameraTag&page=1&size=20';
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1) {
            $this->returnJson(1, '成功', $result['result']);
        } else {
            $this->returnJson(1, $result['result']);
        }
    }

    //景区展示板块
    //景区视频 ----- 搜索视频
    public function selectVideo() {
        //http://ip:port/ssh2/sceman?cmd=querySceMapVideoBylonlat&lon&lat&page&size&type&videoType&serchName&waptag
        $param = I('post.');
        extract($param);
        $is_param = 1;
        !empty($lonlat) ? $lonlat : $is_param = 0;
        !empty($videoType) ? $videoType : $is_param = 0;
        $serchName = empty($serchName) ? '' : $serchName;
        $page = empty($page) ? 1 : $page;
        $size = empty($size) ? 20 : $size;
        $waptag = empty($waptag) ? '' : $waptag; //视频标签
        if (!$is_param)
            $this->returnJson(0, '参数缺失');
        if ($lonlat == -1) {
            $lon = $this->lon;
            $lat = $this->lat;
            $type = 0;
        } else {
            $lonlat = explode(',', $lonlat);
            $lon = $lonlat[0];
            $lat = $lonlat[1];
            $type = 1;
        }
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=querySceMapVideoBylonlat&lon=' . $lon . '&lat=' . $lat . '&page=' . $page . '&size=' . $size . '&type=' . $type . '&videoType=' . $videoType . '&serchName=' . $serchName . '&waptag=' . $waptag;
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1) {
            foreach ($result['result']['data'] as $key => $val) {
                $result['result']['data'][$key]['videoPic'] = PRO_PATH . UPLOAD_PATH . $result['result']['data'][$key]['videoPic'];
                $result['result']['data'][$key]['videoPath'] = PRO_PATH . UPLOAD_PATH . $result['result']['data'][$key]['videoPath'];
                if ($result['result']['data'][$key]['audioUrl'] != "0" && $result['result']['data'][$key]['audioUrl'] != "1") {
                    $result['result']['data'][$key]['audioUrl'] = PRO_PATH . UPLOAD_PATH . $result['result']['data'][$key]['audioUrl'];
                }
            }
            $this->returnJson(1, '操作成功', $result['result']);
        } else
            $this->returnJson(0, $result['result']);
    }

    //景区展示板块
    //景区视频 ----- 置顶视频
    public function topVideo() {
        //http://ip:port/ssh2/sceman?cmd=setVideoTop&id
        $ids = I('post.ids');
        if (!$ids)
            $this->returnJson(0, '缺少视频ID');
        foreach ($ids as $id) {
            $url = C('IP_BASE') . '/ssh2/sceman?cmd=setVideoTop&id=' . $id;
            $result = json_decode(file_get_contents($url), true);
            if ($result['flag'])
                continue;
            else
                $this->returnJson(0, '置顶失败，请稍后重试');
        }
        $this->returnJson(1, '置顶成功');
    }

    //景区展示板块
    //景区视频 ----- 添加视频
    public function addVideo() {
        $lon = $this->lon;
        $lat = $this->lat;
        /*$userID = $_SESSION['sce_adminId'];
        $rst = M('sce_member')->where("id=$userID")->field('tel')->find();
        if (!$rst)
            $this->returnJson(0, '获取用户ID失败');
        $tel = $rst['tel'];*/
        $tel = C('OFFICIAL_TEL');
        $where['Longitude'] = $lon;
        $where['Latitude'] = $lat;
        $rst = M('AllSce')->where($where)->field('CityID,SceName')->find();
        if (!$rst)
            $this->returnJson(0, '获取景区信息失败');
        $cityID = $rst['CityID'];
        $sceName = $rst['SceName'];
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=addLCVideo&userID=' . $tel . '&cityID=' . $cityID . '&lon=' . $lon . '&lat=' . $lat . '&sceName=' . urlencode($sceName);
        $param = I('post.');
        extract($param);
        if ($lonlat == -1) {
            $maplon = $lon;
            $maplat = $lat;
        } else {
            $lonlat = explode(',', $lonlat);
            $maplon = $lonlat[0];
            $maplat = $lonlat[1];
        }
        $url.='&maplon=' . $maplon . '&maplat=' . $maplat;
        $rst = M('SceCity')->where(array('CityID' => $cityID))->field('CityName')->find();
        if (!$rst)
            $this->returnJson(0, '获取城市信息失败');
        $cityName = $rst['CityName'];

        $videoAddress = $cityName . '•' . $sceName;
        !empty($videoName) ? $videoName : $this->returnJson(0, '缺少视频描述');
        !empty($hourLeng) ? $hourLeng : $this->returnJson(0, '缺少视频时长');
        !empty($videoHeight) ? $videoHeight : $this->returnJson(0, '缺少视频长度');
        !empty($videoWidth) ? $videoWidth : $this->returnJson(0, '缺少视频宽度');
        !empty($waptag) ? $waptag : $this->returnJson(0, '缺少视频标签');
        $url.= '&videoName=' . urlencode($videoName) . '&videoAdress=' . urlencode($videoAddress) . '&hourLeng=' . urlencode($hourLeng) . '&videoHeight=' . $videoHeight . '&videoWidth=' . $videoWidth;
        $url.= '&signState=1&isshow=0&videoTag=0&logoState=0&audioUrl=0&videoType=11&isOfficial=2&wapTag=' . $waptag;
        //接收视频
        $video = $_FILES['video'];
        if (empty($video['name']))
            $this->returnJson(0, '请上传视频文件');
        $videoPath = $this->uploadPic($video, 'video', $maplat, $maplon, 209715200);
        //接收封面
        if (empty($canData) && empty($_FILES['cover']['name']))
            $this->returnJson(0, '请设置视频封面');
        if (!empty($_FILES['cover']['name'])) {
            $videoPic = $this->uploadPic($_FILES['cover'], 'videoPic', $maplat, $maplon, 1048576);
        }
        if (!empty($canData)) {
            $videoPic = 'picture/videoPic/' . md5($maplat . $maplon) . '/';
            $Imgpath = dirname($_SERVER['SCRIPT_FILENAME']) . '/upload/picture/videoPic/' . md5($maplat . $maplon) . '/';
            if (!is_dir($Imgpath)) {       // 创建目录
                mkdir($Imgpath, 0777, true);
            }
            $fileName = base64ToImg($canData, $Imgpath);
            if ($fileName === FALSE)
                $this->returnJson(0, '保存视频封面失败，请稍后重试');
            $videoPic.=$fileName;
        }
        $url.='&videoPic=' . urlencode($videoPic) . '&videoPath=' . urlencode($videoPath);
        $result = json_decode(file_get_contents($url), TRUE);
        if ($result['flag'] != 1)
            $this->returnJson(0, $result['result']);
        $this->returnJson('1', '上传视频成功');
    }

    public function editVideo() {
        $lon = $this->lon;
        $lat = $this->lat;
        $id = I('post.id', '') ? I('post.id') : $this->returnJson(0, '缺少视频ID');
        $waptag = I('post.waptag', '') ? I('post.waptag') : $this->returnJson(0, '缺少视频标签');
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=modifyLCVideo&id=' . $id . '&wapTag=' . $waptag;
        $lonlat = I('post.lonlat');
        if ($lonlat == -1) {
            $maplon = $lon;
            $maplat = $lat;
        } else {
            $lonlat = explode(',', $lonlat);
            $maplon = $lonlat[0];
            $maplat = $lonlat[1];
        }
        $url.='&lon=' . $lon . '&lat=' . $lat . '&maplon=' . $maplon . '&maplat=' . $maplat;
        $file = $_FILES['video'];
        if (!empty($file)) {
            $videoPath = $this->uploadPic($file, 'video', $maplat, $maplon, 209715200);
            $hourLeng = I('post.hourLeng', '') ? I('post.hourLeng') : $this->returnJson(0, '缺少视频时长');
            $videoHeight = I('post.videoHeight', '') ? I('post.videoHeight') : $this->returnJson(0, '缺少视频长度');
            $videoWidth = I('post.videoWidth', '') ? I('post.videoWidth') : $this->returnJson(0, '缺少视频宽度');
            $url.='&videoPath=' . urlencode($videoPath) . '&hourLeng=' . urlencode($hourLeng) . '&videoHeight=' . $videoHeight . '&videoWidth=' . $videoWidth;
        }
        $videoPicFile = $_FILES['cover'];
        $canData = I('post.canData');
        if (!empty($videoPicFile)) {
            $videoPic = $this->uploadPic($videoPicFile, 'videoPic', $maplat, $maplon, 1048576);
            $url.='&videoPic=' . urlencode($videoPic);
        } else if (!empty($canData)) {
            $videoPic = 'picture/videoPic/' . md5($maplat . $maplon) . '/';
            $Imgpath = dirname($_SERVER['SCRIPT_FILENAME']) . '/upload/picture/videoPic/' . md5($maplat . $maplon) . '/';
            if (!is_dir($Imgpath)) {       // 创建目录
                mkdir($Imgpath, 0777, true);
            }
            $fileName = base64ToImg($canData, $Imgpath);
            if ($fileName === FALSE)
                $this->returnJson(0, '保存视频封面失败，请稍后重试');
            $videoPic.=$fileName;
            $url.='&videoPic=' . urlencode($videoPic);
        }
        $videoName = I('post.videoName', '') ? I('post.videoName') : $this->returnJson(0, '请填写视频标题');
        $url.='&videoName=' . urlencode($videoName);
        $result = json_decode(file_get_contents($url), TRUE);
        if ($result['flag'] != 1)
            $this->returnJson(0, $result['result']);
        $this->returnJson(1, '保存成功');
    }

    //根据ID删除视频
    public function delVideo() {
        $ids = I('post.ids', '') ? I('post.ids') : $this->returnJson(0, '缺少视频ID');
        foreach ($ids as $id) {
            $url = C('IP_BASE') . '/ssh2/sceman?cmd=delLCVideo&id=' . $id;
            $rst = json_decode(file_get_contents($url), TRUE);
            if ($rst['flag'] != 1)
                $this->returnJson(0, $rst['result']);
        }
        $this->returnJson(1, '删除成功');
    }

    /**
     * 二维码下载
     *  
     * @param string $address 二维码的网络可访问地址   
     *    
     * @return json
     */
    public function downQr() {
        $address = I('get.address');    //仅此接口通过get传参访问
        $path = 'upload/' . str_replace(C('IMG_PRE'), '', $address);
        $filename = time() . '.jpg';
        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $content = file_get_contents($address);
        echo $content;
    }

    //景区节目
    //查询景区节目
    public function selectProgram() {
        // http://ip:port/ssh2/sceman?cmd=queryScenicSpotProgramVo&ScenicSpotID&page&size
        $ScenicSpotID = '';
        $page = I('post.page');
        $size = I('post.size');
        $page = empty($page) ? 1 : $page;
        $size = empty($size) ? 20 : $size;
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotProgramVo&ScenicSpotID=' . $ScenicSpotID . '&page=' . $page . '&size=' . $size;
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1)
            $this->returnJson(1, '新增成功', $result['result']);
        else
            $this->returnJson(0, $result['result']);
    }

    //景区节目
    //根据ID查询景区节目
    public function selectProgramById() {
        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotProgramVoByID&id
        $id = I('post.id');
        if (!$id) {
            $this->returnJson(1, '参数id缺失');
        }
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotProgramVoByID&id=' . $id;
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1)
            $this->returnJson(1, '新增成功', $result['result']);
        else
            $this->returnJson(0, $result['result']);
    }

    //景区节目
    //删除景区节目
    public function deleteProgram() {
        //http://ip:port/ssh2/sceman?cmd=delScenicSpotProgramVo&id
        $id = I('post.id');
        if (!$id) {
            $this->returnJson(1, '参数id缺失');
        }
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=delScenicSpotProgramVo&id=' . $id;
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1)
            $this->returnJson(1, '删除成功');
        else
            $this->returnJson(0, $result['result']);
    }

    //景区节目
    //修改景区节目
    public function saveProgram() {
        //http://ip:port/ssh2/sceman?cmd=modifyScenicSpotProgramVo&  name&remark& ScenicSpotID&videoPic&videoUrl&id
        $id = I('post.id', '') ? I('post.id') : $this->returnJson(0, '缺少视频ID');
        $name = I('post.name', '') ? I('post.name') : $this->returnJson(0, '缺少节目名称');
        $remark = I('post.remark', '') ? I('post.remark') : $this->returnJson(0, '缺少文字描述');
        $ScenicSpotID = '';
        $lon = $this->lon;
        $lat = $this->lat;
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=modifyScenicSpotProgramVo&id=' . $id . '&name=' . $name . '&remark=' . urlencode($remark) . '&ScenicSpotID=' . urlencode($ScenicSpotID);
        $file = $_FILES['video'];
        if (!empty($file)) {
            $videoPath = $this->uploadPic($file, 'video', $lat, $lon, 209715200);
            $url.='&videoUrl=' . urlencode($videoPath);
        }
        $videoPicFile = $_FILES['cover'];
        $canData = I('post.canData');
        if (!empty($videoPicFile)) {
            $videoPic = $this->uploadPic($videoPicFile, 'videoPic', $lat, $lon, 1048576);
            $url.='&videoPic=' . urlencode($videoPic);
        } else if (!empty($canData)) {
            $videoPic = 'picture/videoPic/' . md5($lat . $lon) . '/';
            $Imgpath = dirname($_SERVER['SCRIPT_FILENAME']) . '/upload/picture/videoPic/' . md5($lat . $lon) . '/';
            if (!is_dir($Imgpath)) {       // 创建目录
                mkdir($Imgpath, 0777, true);
            }
            $fileName = base64ToImg($canData, $Imgpath);
            if ($fileName === FALSE)
                $this->returnJson(0, '保存视频封面失败，请稍后重试');
            $videoPic.=$fileName;
            $url.='&videoPic=' . urlencode($videoPic);
        }
        $result = json_decode(file_get_contents($url), TRUE);
        if ($result['flag'] != 1)
            $this->returnJson(0, $result['result']);
        $this->returnJson(1, '修改成功');
    }

    //景区节目
    //添加景区节目
    public function addProgram() {
        //http://ip:port/ssh2/sceman?cmd=addScenicSpotProgramVo&name&remark&ScenicSpotID&videoPic&videoUrl
        $name = I('post.name', '') ? I('post.name') : $this->returnJson(0, '缺少节目名称');
        $remark = I('post.remark', '') ? I('post.remark') : $this->returnJson(0, '缺少文字描述');
        $ScenicSpotID = '';
        $lon = $this->lon;
        $lat = $this->lat;
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=addScenicSpotProgramVo&name=' . $name . '&remark=' . $remark . '&ScenicSpotID=' . $ScenicSpotID;
        $video = $_FILES['video'];
        if (empty($video['name']))
            $this->returnJson(0, '请上传视频文件');
        $videoPath = $this->uploadPic($video, 'video', $lat, $lon, 209715200);
        //接收封面
        if (empty($canData) && empty($_FILES['cover']['name']))
            $this->returnJson(0, '请设置视频封面');
        if (!empty($_FILES['cover']['name'])) {
            $videoPic = $this->uploadPic($_FILES['cover'], 'videoPic', $lat, $lon, 1048576);
        }
        if (!empty($canData)) {
            $videoPic = 'picture/videoPic/' . md5($lat . $lon) . '/';
            $Imgpath = dirname($_SERVER['SCRIPT_FILENAME']) . '/upload/picture/videoPic/' . md5($lat . $lon) . '/';
            if (!is_dir($Imgpath)) {       // 创建目录
                mkdir($Imgpath, 0777, true);
            }
            $fileName = base64ToImg($canData, $Imgpath);
            if ($fileName === FALSE)
                $this->returnJson(0, '保存视频封面失败，请稍后重试');
            $videoPic.=$fileName;
        }
        $url.='&videoPic=' . urlencode($videoPic) . '&videoUrl=' . urlencode($videoPath);
        $result = json_decode(file_get_contents($url), TRUE);
        if ($result['flag'] != 1)
            $this->returnJson(0, $result['result']);
        $this->returnJson('1', '上传视频成功');
    }

}
