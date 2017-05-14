<?php
namespace Admin\Controller;
use Think\Controller;
//给运营后台包管理的接口

class SettingController extends BaseController {
    //首页商家 内容显示顺序
    //新增設置
    public function addSet(){
        //http://ip:port/ssh2/sceman?cmd=addSceBasicSetting&showIndex&tName&showNum&tclass
        $param=I('post.');
        extract(myurlencode($param));
        $is_param = 1;  //is_param代表参数是否缺失
        !empty($showIndex) ? $showIndex : $is_param = 0;
        !empty($tName) ? $tName : $is_param = 0;
        !empty($showNum) ? $showNum : $is_param = 0;
        !empty($tclass) ? $tclass : $is_param = 0;
        $url=C('IP_BASE').'/ssh2/sceman?cmd=addSceBasicSetting&showIndex='.$showIndex.'&tName='.$tName.'&showNum='.$showNum.'&tclass='.$tclass;
        $result=file_get_contents($url);
        if((json_decode($result)->flag) == 1 ){
            returnJson(1,'新增成功');
        }else{
            returnJson(0,json_decode($result)->result);
        }
    }

    //刪除設置
    public function  deleteSet(){
        //http://ip:port/ssh2/sceman?cmd=delSceBasicSetting&id
        $id=I('post.id');
        if($id){
            returnJson(0,'参数缺失');
        }
        $url=C('IP_BASE').'/ssh2/sceman?cmd=delSceBasicSetting&id='.$id;
        $result=file_get_contents($url);
        if((json_decode($result)->flag) == 1 ){
            returnJson(1,json_decode($result)->result);
        }else{
            returnJson(0,json_decode($result)->result);
        }
    }

    //查询首页基础设置
    public function selectSet(){
        //http://ip:port/ssh2/sceman?cmd=querySceBasicSetting&page&size
        $page = I('post.page');
        $size = I('post.size');
        $page = empty($page) ? 1 : $page;
        $size = empty($size) ? 20 : $size;
        $url = C('IP_BASE').'/ssh2/sceman?cmd=querySceBasicSetting&page='.$page.'&size='.$size;
        $result = file_get_contents($url);
        if((json_decode($result)->flag) == 1 ){
            returnJson(1,'查詢成功',(json_decode($result)->result));
        }else{
            returnJson(0,json_decode($result)->result);
        }
    }

    //首页显示的内容
    public function infoSet(){
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotVoByWap&lon='.$this->lon.'&lat='.$this->lat;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //活动
        $info['ScenicSpotActivityVo'] = $resultApi['result']['ScenicSpotActivityVo'];
        foreach ($info['ScenicSpotActivityVo'] as $key => $value) {
            $info['ScenicSpotActivityVo'][$key]['url'] = C('IMG_PRE').$value['url'];
            $info['ScenicSpotActivityVo'][$key]['tClass'] = 'ScenicSpotActivityVo';
            $info['ScenicSpotActivityVo'][$key]['title'] = $info['ScenicSpotActivityVo'][$key]['titleName'];
        }
        //路线
        $info['ScenicSpotWayVo'] = $resultApi['result']['ScenicSpotWayVo'];
        foreach ($info['ScenicSpotWayVo'] as $key => $value) {
            $info['ScenicSpotWayVo'][$key]['url'] = C('IMG_PRE').$value['url'];
            $info['ScenicSpotWayVo'][$key]['travelPic'] = C('IMG_PRE').$value['travelPic'];
            $info['ScenicSpotWayVo'][$key]['tClass'] = 'ScenicSpotWayVo';
            $info['ScenicSpotWayVo'][$key]['title'] =  $info['ScenicSpotWayVo'][$key]['name'];
        }
        //美景直播
        $info['CameraVideoVo'] = $resultApi['result']['CameraVideoVo'];
        foreach ($info['CameraVideoVo'] as $key => $value) {
            $info['CameraVideoVo'][$key]['url'] = C('IMG_PRE').$value['videoPic'];
            $info['CameraVideoVo'][$key]['videoPath'] = C('IMG_PRE').$value['videoPath'];
            $info['CameraVideoVo'][$key]['tClass'] = 'CameraVideoVo';
            $info['CameraVideoVo'][$key]['title'] = $info['CameraVideoVo'][$key]['videoName'];
        }
        $info= array_merge($info['ScenicSpotActivityVo'], $info['ScenicSpotWayVo'],$info['CameraVideoVo'] );
        if($resultApi['flag']) returnJson(1,'成功',$info);
    }

    //修還設置
    public function updateSet(){
        //http://ip:port/ssh2/sceman?cmd=modifySceBasicSetting&showIndex&tName&showNum&tclass&id
        $id = I('post.id');
        $showNum = I('post.showNum');
        $url = C('IP_BASE').'/ssh2/sceman?cmd=modifySceBasicSetting&showNum='.$showNum.'&id='.$id;

        $result=file_get_contents($url);
        if((json_decode($result)->flag) == 1 ){
            returnJson(1,'修改成功');
        }else{
            returnJson(0,json_decode($result)->result);
        }
    }

    //調整設置
    public function adjustmentSet(){
        //http://ip:port/ssh2/sceman?cmd=turnCameraVideoVo&id&showIndex
        $param=I('post.');
        extract($param);
        $is_param = 1;  //is_param代表参数是否缺失
        !empty($showIndex) ? $showIndex : $is_param = 0;
        !empty($id) ? $id : $is_param = 0;
        !empty($lon) ? $lon : $is_param = 0;
        !empty($lat) ? $lat : $is_param = 0;
        $id='57cd213f45ce4d1f4a8af6f6';
        $showIndex=100;
        $url=C('IP_BASE').'/ssh2/sceman?cmd=turnCameraVideoVo&id='.$id.'&showIndex='.$showIndex.'&lon='.$lon.'&lat='.$lat;
        $result=file_get_contents($url);
        if((json_decode($result)->flag) == 1 ){
            returnJson(1,'调整成功');
        }else{
            returnJson(0,json_decode($result)->result);
        }
    }

    //新增优惠券
    public function addCoupon(){
        //http://ip:port/ssh2/sceman?cmd=addCommercialTenantVoucherVo&commercialTenantID&picUrl&remark&voucherName&zk&lon&lat
        $param = I('post.');
        extract(myurlencode($param));
        $is_param = 1;  //is_param代表参数是否缺失
        !empty($commercialTenantID) ? $commercialTenantID : $is_param = 0;
        !empty($remark) ? $remark : $is_param = 0;
        !empty($voucherName) ? $voucherName : $is_param = 0;
        !empty($zk) ? $zk: $is_param = 0;
        !empty($lon) ?$lon: $is_param = 0;
        !empty($lat) ? $lat : $is_param = 0;
        if (!empty($_FILES['cover']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $upload->rootPath = C('PUBLIC') . UPLOAD_COUPON;
            if (!is_dir($upload->rootPath)) {       // 创建目录
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['cover']);
            if (!empty($info)) {
                $picUrl = UPLOAD_COUPON . $info['savepath'] . $info['savename'];
            } else {
                returnJson(-1,'接受图片失败');
            }
        }
        $url=C('IP_BASE').'/ssh2/sceman?cmd=addCommercialTenantVoucherVo&commercialTenantID='.$commercialTenantID.'&picUrl='.$picUrl.'&remark='.$remark.'&picUrl='.$picUrl.'&voucherName='.$voucherName.'&zk='.$zk.'&lon='.$lon.'&lat='.$lat;
        $result=file_get_contents($url);
        if((json_decode($result)->flag) == 1 ){
            returnJson(1,'新增成功');
        }else{
            returnJson(0,json_decode($result)->result);
        }
    }

    //删除优惠券
    public function  deleteCoupon(){
        //http://ip:port/ssh2/sceman?cmd=delCommercialTenantVoucherVo&id
        $id=I('post.id');
        if($id){
            returnJson(0,'参数缺失');
        }
        $url=C('IP_BASE').'/ssh2/sceman?cmd=delCommercialTenantVoucherVo&id='.$id;
        $result=file_get_contents($url);
        if((json_decode($result)->flag) == 1 ){
            returnJson(1,json_decode($result)->result);
        }else{
            returnJson(0,json_decode($result)->result);
        }
    }

    //修改优惠券
    public function updateCoupon(){
        //http://ip:port/ssh2/sceman?cmd=modifyCommercialTenantVoucherVo&commercialTenantID&picUrl&remark&voucherName&zk&id&lon&lat
        $param = I('post.');
        extract(myurlencode($param));
        $is_param = 1;  //is_param代表参数是否缺失
        !empty($commercialTenantID) ? $commercialTenantID : $is_param = 0;
        !empty($remark) ? $remark : $is_param = 0;
        !empty($voucherName) ? $voucherName : $is_param = 0;
        !empty($zk) ? $zk: $is_param = 0;
        !empty($lon) ?$lon: $is_param = 0;
        !empty($lat) ? $lat : $is_param = 0;
        !empty($id) ? $id : $is_param = 0;
        if (!empty($_FILES['cover']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $upload->rootPath = C('PUBLIC') . UPLOAD_COUPON;
            if (!is_dir($upload->rootPath)) {       // 创建目录
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['cover']);
            if (!empty($info)) {
                $picUrl = UPLOAD_COUPON . $info['savepath'] . $info['savename'];
            } else {
                returnJson(-1,'接受图片失败');
            }
        }
        $url=C('IP_BASE').'/ssh2/sceman?cmd=modifyCommercialTenantVoucherVo&commercialTenantID='.$commercialTenantID.'&picUrl='.$picUrl.'&remark='.$remark.'&picUrl='.$picUrl.'&voucherName='.$voucherName.'&zk='.$zk.'&lon='.$lon.'&lat='.$lat.'&id='.$id;
        $result=file_get_contents($url);
        if((json_decode($result)->flag) == 1 ){
            returnJson(1,'修改成功');
        }else{
            returnJson(0,json_decode($result)->result);
        }
    }
}
