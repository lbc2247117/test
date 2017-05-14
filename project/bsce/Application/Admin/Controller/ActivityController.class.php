<?php

namespace Admin\Controller;

use Think\Controller;

/**
 * 活动相关接口相关接口   
 */
defined('UPLOAD_PATH') or define('UPLOAD_PATH', 'upload/');        //上传资源的路径
defined('UPLOAD_ACT') or define('UPLOAD_ACT', 'picture/bsce/activity/');        //摇一摇活动图片

class ActivityController extends BaseController {

    //普通活动----活动搜索
    public function selectActivity() {
        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotActivityVo&page&size&lon&lat&state&serchName
        $lon = $this->lon;
        $lat = $this->lat;
        $page = I('post.page');
        $size = I('post.size');
        $state = I('post.state');
        $serchName = I('post.serchName');
        $size = empty($size) ? 20 : $size;
        $page = empty($page) ? 1 : $page;
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotActivityVo&page=' . $page . '&size=' . $size . '&lon=' . $lon . '&lat=' . $lat . '&state=' . $state . '&serchName=' . urlencode($serchName);
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1) {
            foreach ($result['result']['data'] as $key => $value) {
                $result['result']['data'][$key]['url'] = C('FULL_PATH') . $value['url'];
                $result['result']['data'][$key]['jumpurl'] = C('ACT_HOME_URL') . 'lon=' . $lon . '&lat=' . $lat . '&id=' . $value['id'];
                $result['result']['data'][$key]['QRCodeUrl'] = C('FULL_PATH') . $value['QRCodeUrl'];
                $result['result']['data'][$key]['content'] = file_get_contents(C('IMG_PRE') . $value['content']);
            }
            $this->returnJson(1, '操作成功', $result['result']);
        } else {
            $this->returnJson(0, $result['result']);
        }
    }

    //增加景区活动
    public function addActivity() {
        //http://ip:port/ssh2/sceman?cmd=addScenicSpotActivityVo&startTime&titleName&lon&lat&endTime&mapBackgroudPic&name&url&state&acTitle&acRuke&acjp
        $param = I('post.');
        $param = myurlencode($param);
        extract($param);
        $is_param = 1;  //is_param代表参数是否缺失
        $lon = $this->lon;
        $lat = $this->lat;
        $msg = $_REQUEST['msg'];
        !empty($titleName) ? $titleName : $is_param = 0;
        //上传图片
        if (!empty($_FILES['cover']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = UPLOAD_ACT;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['cover']);
            if (!empty($info)) {
                $url = $imgpath . $info['savepath'] . $info['savename'];
            } else {
                $this->returnJson(0, '检查图片的大小或格式');
            }
        } else
            $this->returnJson(0, '上传图片出错');
        !empty($startTime) ? $startTime : $is_param = 0;
        !empty($endTime) ? $endTime : $is_param = 0;
        !empty($name) ? $name : $is_param = 0;
        !empty($acTitle) ? $acTitle : $is_param = 0;
        !empty($acRuke) ? $acRuke : $is_param = 0;
        !empty($acjp) ? $acjp : $is_param = 0;
        !empty($signUp) ? $signUp = 1 : $signUp = 0;
        //处理文本信息
        $mapBackgroudPic = UPLOAD_ACT . date("Y-m-d") . '/' . time() . '.txt';   //url->封面图，mapBackgroudPic->详情文本
        file_put_contents(UPLOAD_PATH . $mapBackgroudPic, $msg);
        $state = 2;     // 0：生效 1：失效 2：下线
        if ($is_param == 0)
            $this->returnJson(0, '参数缺失');
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=addScenicSpotActivityVo';
        $apiUrl .= '&titleName=' . $titleName . '&lon=' . $lon . '&lat=' . $lat . '&startTime=' . $startTime . '&endTime=' . $endTime;
        $apiUrl .= '&mapBackgroudPic=' . $mapBackgroudPic . '&name=' . $name . '&url=' . $url . '&state=' . $state . '&acTitle=' . $acTitle . '&acRuke=' . $acRuke . '&acjp=' . $acjp . '&signUp=' . $signUp;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        //数据返回
        if ($resultApi['flag'] == 1)
            $this->returnJson(1, '成功');
        else if ($resultApi['flag'] == 0)
            $this->returnJson(0, $resultApi['result']);
        else
            $this->returnJson(0, '网络异常，稍后再试！');
    }

    //编辑一个景区的活动
    public function editActivity() {
        $id = I('post.id');
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotActivityVoByID&id=' . $id;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        $resultApi['result']['url'] = C('FULL_PATH') . $resultApi['result']['url'];
        $desc = file_get_contents(UPLOAD_PATH . $resultApi['result']['content']);
        $resultApi['result']['content'] = $desc ? $desc : '';
        //数据返回
        if ($resultApi['flag'] == 1) {
            if ($resultApi['result']['signUp'] == 'false') {
                $resultApi['result']['signUp'] = 0;
            } else {
                $resultApi['result']['signUp'] = 1;
            }
            $this->returnJson(1, '成功', $resultApi['result']);
        } else if ($resultApi['flag'] == 0)
            $this->returnJson(0, $resultApi['result']);
        else
            $this->returnJson(0, '网络异常，稍后再试！');
    }

    //修改景区活动
    public function updateActivity() {
        // http://ip:port/ssh2/sceman?cmd=modifyScenicSpotActivityVo&id&startTime&titleName&lon&lat&endTime&mapBackgroudPic&name&url&state&acTitle&acjp&acRuke
        $param = I('post.');
        $param = myurlencode($param);
        extract($param);
        $is_param = 1;  //is_param代表参数是否缺失
        $lon = $this->lon;
        $lat = $this->lat;
        $msg = $_REQUEST['msg'];
        !empty($id) ? $id : $is_param = 0;
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=modifyScenicSpotActivityVo&id=' . $id . '&lon=' . $lon . '&lat=' . $lat;
        !empty($titleName) && $apiUrl .= '&titleName=' . $titleName;
        !empty($startTime) && $apiUrl .= '&startTime=' . $startTime;
        !empty($endTime) && $apiUrl .= '&endTime=' . $endTime;
        if (mb_strlen(I('post.name'), 'utf-8') > 200) {
            $this->returnJson(0, '请填写活动内容,长度为0到200个字符');
        }
        !empty($name) && $apiUrl .= '&name=' . $name;
        if (mb_strlen(I('post.acTitle'), 'utf-8') > 140) {
            $this->returnJson(0, '请填写活动主题,长度为0到140个字符');
        }
        !empty($acTitle) && $apiUrl .= '&acTitle=' . $acTitle;
        if (mb_strlen(I('post.acRuke'), 'utf-8') > 200) {
            $this->returnJson(0, '请填写活动规则,长度为0到200个字符');
        }
        !empty($acRuke) && $apiUrl .= '&acRuke=' . $acRuke;
        if (mb_strlen(I('post.acjp'), 'utf-8') > 200) {
            $this->returnJson(0, '请填写活动奖品,长度为0到200个字符');
        }
        !empty($acjp) && $apiUrl .= '&acjp=' . $acjp;
        !empty($signUp) ? $signUp = 1 : $signUp = 0;
        $apiUrl .= '&signUp=' . $signUp;
        //处理修改的图片
        if (!empty($_FILES['cover']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = UPLOAD_ACT;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['cover']);
            if (!empty($info)) {
                $apiUrl .= '&url=' . $imgpath . $info['savepath'] . $info['savename'];
            } else {
                $this->returnJson(0, '检查图片的大小或格式');
            }
        }
        if (!empty($msg)) {
            $dir = UPLOAD_ACT . date("Y-m-d");
            if (!file_exists(UPLOAD_PATH . $dir)) {
                mkdir(UPLOAD_PATH . $dir, 0777, true);
            }
            $mapBackgroudPic = $dir . '/' . time() . '.txt';   //url->封面图，mapBackgroudPic->详情文本
            file_put_contents(UPLOAD_PATH . $mapBackgroudPic, $msg);
            $apiUrl .= '&mapBackgroudPic=' . $mapBackgroudPic;
        }
        if ($is_param == 0)
            $this->returnJson(0, '参数缺失');

        $resultApi = json_decode(file_get_contents($apiUrl), true);
        //数据返回
        if ($resultApi['flag'] == 1)
            $this->returnJson(1, '成功');
        else if ($resultApi['flag'] == 0)
            $this->returnJson(0, $resultApi['result']);
        else
            $this->returnJson(0, '网络异常，稍后再试！');
    }

    //删除景区活动
    public function delActivity() {
        //http://ip:port/ssh2/sceman?cmd=delScenicSpotActivityVo&id
        $id = I('post.ids');
        foreach ($id as $key => $value) {
            $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=delScenicSpotActivityVo&id=' . $value;
            $resultApi = json_decode(file_get_contents($apiUrl), true);
            if ($resultApi['flag'] == 1)
                continue;
            else
                $this->returnJson(0, '删除失败，请稍后再试');
        }
        $this->returnJson(1, '删除成功');
    }

    //活动上下架
    public function upActivity() {
        $state = I('post.state');
        $id = I('post.id');
        if (empty($id))
            $this->returnJson(0, '数据缺失');
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=modifyScenicSpotActivityVo&state=' . $state . '&id=' . $id;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        //数据返回
        if ($resultApi['flag'] == 1)
            $this->returnJson(1, '成功');
        else if ($resultApi['flag'] == 0)
            $this->returnJson(0, $resultApi['result']);
        else
            $this->returnJson(0, '网络异常，稍后再试！');
    }

    //获取移动高清直播数据
    public function getMoveVideoData() {
        $page = I('post.page', '1', FILTER_VALIDATE_INT);
        $size = I('post.size', '20', FILTER_VALIDATE_INT);
        $sort = I('post.sort', 'createTime'); //排序字段
        $order = I('post.order', '0'); //排序规则:0=>降序,1=>升序
        $type = I('post.type'); //搜索类型，没有则不填，name表示按直播名称搜索，vstate表示按状态搜索
        $status = I('post.status'); //搜索的状态
        $search = I('post.search');
        //有搜索
        if (!empty($search) || !empty($status)) {
            if (!empty($status)) {
                $type = 'vstate';
                if ($status == 1)
                    $search = -1;
                else if ($status == 2)
                    $search = 1;
                else if ($status == 3)
                    $search = 2;
            }
            $url = C('IP_LIVE') . '/ssh2/cloudvideo?cmd=schsce&sec=756489214459&search=' . urlencode($search) . '&type=' . $type . '&lat=' . $this->lat . '&lon=' . $this->lon;
        } else {
            //没有搜素时，默认只能查询该景区的直播
            $type = 'scenicName';
            $search = S('sceName');
            $url = C('IP_LIVE') . '/ssh2/cloudvideo?cmd=schsce&sec=756489214459&search=' . urlencode($search) . '&type=' . $type;
        }
        $url .= '&page=' . $page . '&size=' . $size . '&sort=' . $sort . '&order=' . $order;
        $result = json_decode(file_get_contents($url), TRUE);
        if ($result['flag'] != 1) {
            $this->returnJson(0, $result['result']);
        }
        $data['list'] = $result['result'];
        for ($i = 0; $i < count($data['list']); $i++) {
            $data['list'][$i]['bakPath'] = C('FULL_PATH') . 'video/' . $data['list'][$i]['bakPath']; //'http://www.51laiya.com/bsce/upload/video/live/e851efb5e18a4e339da83dc1d0af60cc-20161010.m3u8.m3u8';
            $data['list'][$i]['cover'] = C('FULL_PATH') . $data['list'][$i]['cover'];
            $data['list'][$i]['jumpurl'] = C('LIVE_HOME_URL') . 'lon=' . $this->lon . '&lat=' . $this->lat . '&id=' . $data['list'][$i]['id'];
        }
        $data['count'] = 0;
        if (count($data['list']) > 0)
            $data['count'] = $data['list'][0]['count'];
        $this->returnJson(1, '成功', $data);
    }

    //查询活动报名列表
    public function signupList() {
        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotActivitySignVo&page&size&activityID&ScenicSpotID
        $page = I('post.page');
        $size = I('post.size');
        $activityID = I('post.activityID');
        $page = empty($page) ? 1 : $page;
        $size = empty($size) ? 20 : $size;
        if (!$activityID) {
            $this->returnJson(1, '未接受到活动id');
        }
        $lon = $this->lon;
        $lat = $this->lat;
        $ScenicSpotID = $this->selectId($lon, $lat);
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotActivitySignVo&page=' . $page . '&size=' . $size . '&activityID=' . $activityID . '&ScenicSpotID=' . $ScenicSpotID;
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1) {
            foreach ($result['result']['data'] as $key => $val) {
                if ($key % 2 == 0) {
                    $data['data1'][] = $result['result']['data'][$key];
                } else {
                    $data['data2'][] = $result['result']['data'][$key];
                }
            }
            $result['result'] = $data;
            $this->returnJson(1, '成功', $result['result']);
        } else
            $this->returnJson(0, $result['result']);
    }

    public function exportSignUp() {
        $activityID = I('get.activityID');
        $lon = $this->lon;
        $lat = $this->lat;
        $ScenicSpotID = $this->selectId($lon, $lat);
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotActivitySignVo&page=1&size=1000&activityID=' . $activityID . '&ScenicSpotID=' . $ScenicSpotID;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if ($resultApi['flag'] != '1')
            $this->returnJson(0, $resultApi['result']);
        $arr = array();
        for ($i = 0; $i < count($resultApi['result']['data']); $i++) {
            $arr[$i]['name'] = $resultApi['result']['data'][$i]['name'];
            $arr[$i]['tel'] = $resultApi['result']['data'][$i]['tel'];
        }
        $th = array('姓名', '电话');
        createExcel($th, $arr, '', '活动报名');
    }

}
