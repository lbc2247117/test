<?php

namespace Admin\Controller;

use Think\Controller;

//给运营后台包管理的接口
defined('UPLOAD_PATH') or define('UPLOAD_PATH', 'upload/');        //上传资源的路径
defined('UPLOAD_ACT') or define('UPLOAD_ACT', 'picture/bsce/Ticket/');        //摇一摇活动图片

class TicketController extends BaseController {

    //查询景区票务列表
    public function selectTicket() {
        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotTicketVo&ScenicSpotID&page&size
        $page = I('post.page');
        $size = I('post.size');
        $name = I('post.name');
        $state= I('post.state');
        $page = empty($page) ? 1 : $page;
        $size = empty($size) ? 20 : $size;
        $lon = $this->lon;
        $lat = $this->lat;
        $ScenicSpotID =$this->selectId($lon, $lat);
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotTicketVo&page=' . $page . '&size=' . $size . '&ScenicSpotID=' . $ScenicSpotID . '&name=' . $name.'&state='.$state;
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1){
            $this->returnJson(1, '成功', $result['result']);
        }
        else
            $this->returnJson(0, $result['result']);
    }

    //新增景区票务
    public function addTicket() {
        //http://ip:port/ssh2/sceman?cmd=addScenicSpotTicketVo&includeRemark&name&person1&person2&pic&price&rangeRemark&remark&ruleRemark&ScenicSpotID&tel
        if (!empty($_FILES['cover']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 1048576; // 1M
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
                $pic = $imgpath . $info['savepath'] . $info['savename'];
            } else {
                $this->returnJson(0, '图片大小请不要超过1M');
            }
        } else {
            $this->returnJson(0, '请选择图片上传');
        }
        $param = I('post.');
        extract($param);
        if(mb_strlen(I('post.ruleRemark'),'utf-8')>120){
            $this->returnJson(0, '请填写包含项目,长度为0到120个字符');
        }
        !empty($includeRemark) ? $includeRemark : $this->returnJson(0, '缺少包含项目');
        if(mb_strlen(I('post.name'),'utf-8')>20){
            $this->returnJson(0, '请填写票务名称,长度为0到20个字符');
        }
        !empty($price) ? $price : $this->returnJson(0, '缺少票务价格');
        if(! is_float((float)$price)){
            $this->returnJson(0,'票务价格格式不正确，请重新输入票务价格，如898、199、49等。');
        }
        $price=round($price,2);
        if($person1==0 && $person2==0 ){
            $this->returnJson(0,'成人，儿童票务人数不能同时为空，请填写人数');
        }
        $person1=empty($person1)?0:I('post.person1',-1,'int');
        $person2=empty($person2)?0:I('post.person2',-1,'int');
        if($person1<0 || $person2<0){
            $this->returnJson(0,'票务人数填写格式有误，请重新填写。');
        }

        !empty($name) ? $name : $this->returnJson(0, '缺少票务名称');
        if(mb_strlen(I('post.rangeRemark'),'utf-8')>120){
            $this->returnJson(0, '请填写使用范围,长度为0到120个字符');
        }
        !empty($rangeRemark) ? $rangeRemark : $this->returnJson(0, '缺少使用范围');
        if(mb_strlen(I('post.remark'),'utf-8')>50){
             $this->returnJson(0, '请填写票务描述,长度为0到50个字符');
        }
        !empty($remark) ? $remark : $this->returnJson(0, '缺少票务描述');
        if(mb_strlen(I('post.ruleRemark'),'utf-8')>120){
            $this->returnJson(0, '请填写使用规则,长度为0到120个字符');
        }
        !empty($ruleRemark) ? $ruleRemark : $this->returnJson(0, '缺少使用规则');
        !empty($tel) ? $tel : $this->returnJson(0, '缺少票务订购电话');
        !isMobile($tel) && $this->returnJson(0,'请填写正确的手机号码');
        $lon = $this->lon;
        $lat = $this->lat;
        $ScenicSpotID =$this->selectId($lon, $lat);
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=addScenicSpotTicketVo&includeRemark=' . urlencode($includeRemark) . '&name=' . urlencode($name) . '&person1=' . $person1 . '&person2=' . $person2 . '&pic=' . $pic . '&price=' . $price . '&rangeRemark=' . urlencode($rangeRemark) . '&remark=' . urlencode($remark) . '&ruleRemark=' . urlencode($ruleRemark) . '&ScenicSpotID=' . $ScenicSpotID . '&tel=' . $tel . '&state=0';
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1)
            $this->returnJson(1, '新增成功');
        else
            $this->returnJson(0, $result['result']);
    }

    //票务上下架
    public function DownShelf() {
        //http://ip:port/ssh2/sceman?cmd=modifyScenicSpotTicketVo&includeRemark&name&person1&person2&pic&price&rangeRemark&remark&ruleRemark&ScenicSpotID&tel&id
        $state = I('post.state');
        $id = I('post.id');
        if (!$id) {
            $this->returnJson(1, '参数缺失');
        }
        $state = !empty($state) ? 0 : 1;
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=modifyScenicSpotTicketVo&state=' . $state . '&id=' . $id;
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1)
            $this->returnJson(1, '修改成功');
        else
            $this->returnJson(0, $result['result']);
    }

    //编辑景区票务
    public function saveTicket() {
        //http://ip:port/ssh2/sceman?cmd=modifyScenicSpotTicketVo&includeRemark&name&person1&person2&pic&price&rangeRemark&remark&ruleRemark&ScenicSpotID&tel&id
        if (!empty($_FILES['cover']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 1048576; // 2M
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
                $pic = $imgpath . $info['savepath'] . $info['savename'];
            } else {
                $this->returnJson(0, '图片大小请不要超过1M');
            }
        }
        $param = I('post.');
        extract($param);
        if(mb_strlen(I('post.ruleRemark'),'utf-8')>120){
            $this->returnJson(0, '请填写包含项目,长度为0到120个字符');
        }
        !empty($includeRemark) ? $includeRemark : $this->returnJson(0, '缺少包含项目');
        if(mb_strlen(I('post.name'),'utf-8')>20){
            $this->returnJson(0, '请填写票务名称,长度为0到20个字符');
        }
        !empty($name) ? $name : $this->returnJson(0, '缺少票务名称');
        if($person1==0 && $person2==0 ){
            $this->returnJson(0,'成人，儿童票务人数不能同时为空，请填写人数');
        }
        $person1=empty($person1)?0:I('post.person1',-1,'int');
        $person2=empty($person2)?0:I('post.person2',-1,'int');
        if($person1<0 || $person2<0){
            $this->returnJson(0,'票务人数填写格式有误，请重新填写。');
        }
        !empty($price) ? $price : $this->returnJson(0, '缺少票务价格');
        if(! is_float((float)$price)){
            $this->returnJson(0,'票务价格格式不正确，请重新输入票务价格，如898、199、49等。');
        }
        $price=round($price, 2);
        if(mb_strlen(I('post.rangeRemark'),'utf-8')>120){
            $this->returnJson(0, '请填写使用范围,长度为0到120个字符');
        }
        !empty($rangeRemark) ? $rangeRemark : $this->returnJson(0, '缺少使用范围');
        if(mb_strlen(I('post.remark'),'utf-8')>50){
            $this->returnJson(0, '请填写票务描述,长度为0到50个字符');
        }
        !empty($remark) ? $remark : $this->returnJson(0, '缺少票务描述');
        if(mb_strlen(I('post.ruleRemark'),'utf-8')>120){
            $this->returnJson(0, '请填写使用规则,长度为0到120个字符');
        }
        !empty($ruleRemark) ? $ruleRemark : $this->returnJson(0, '缺少使用规则');
        !empty($tel) ? $tel : $this->returnJson(0, '缺少票务订购电话');
        !isMobile($tel) && $this->returnJson(0,'请填写正确的手机号码');
        !empty($id) ? $id : $this->returnJson(0, '缺少票务id');
        !empty($state) ? $state : $state = 0;
        $lon = $this->lon;
        $lat = $this->lat;
        $ScenicSpotID =$this->selectId($lon, $lat);
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=modifyScenicSpotTicketVo&includeRemark=' . urlencode($includeRemark);
        $url .= '&name=' . urlencode($name) . '&person1=' . $person1 . '&person2=' . $person2 . '&pic=' . $pic . '&price=' . $price;
        $url .= '&rangeRemark=' . urlencode($rangeRemark) . '&remark=' .urlencode($remark) . '&ruleRemark=' . urlencode($ruleRemark);
        $url .= '&ScenicSpotID=' . $ScenicSpotID . '&tel=' . $tel . '&id=' . $id . '&state=' . $state;
        $result =json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1)
            $this->returnJson(1, '修改成功');
        else
            $this->returnJson(0, $result['result']);
    }

    //根据id查询景区票务
    public function selectTicketById() {
        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotTicketVoByID&id
        $id = I('post.id');
        if (!$id) {
            $this->returnJson(0, '缺少票务id');
        }
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotTicketVoByID&id=' . $id;
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1) {
            $result['result']['pic'] = C('FULL_PATH') . $result['result']['pic'];
            if(substr($result['result']['price'],-1)==0){
                $result['result']['price'] =  str_replace('.0','', $result['result']['price']);
                $result['result']['price'] =  str_replace('.00','', $result['result']['price']);
            }
            $this->returnJson(1, '成功成功', $result['result']);
        } else
            $this->returnJson(0, $result['result']);
    }
}
