<?php

/**
 * 活动控制器
 *
 * @author LiuBoCheng
 * @copyright (c) 2016, 云道
 * @version 2016-09-09
 */

namespace Home\Controller;

class ActiveController extends BaseController {

    //验证验证码
    protected function check_verify($code){
        $verify = new \Think\Verify();
        return $verify->check($code);
    }

    //报名参与活动
    public function joinActivity() {
        //http://ip:port/ssh2/sceman?cmd=addScenicSpotActivitySignVo&activityID&name&ScenicSpotID&tel
        $verify = I('verify','','strtolower');
        //验证验证码是否正确
        if(!($this->check_verify($verify))){
            $this->returnJson(0,'验证码错误');
        }
        $lon = I('request.lon', '') ? I('request.lon') : $this->returnJson(0, '缺少景区经度');
        $lat = I('request.lat', '') ? I('request.lat') : $this->returnJson(0, '缺少景区维度');
        $ScenicSpotID = $this->selectId($lon, $lat);
        $name = I('post.name', '') ? I('post.name') : $this->returnJson(0, '缺少名字');
        $activityID = I('post.activityID', '') ? I('post.activityID') : $this->returnJson(0, '缺少活动id');
        $tel = I('post.tel', '') ? I('post.tel') : $this->returnJson(0, '缺少电话号码');
        $url = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotActivitySignVo&activityID='.$activityID.'&name='.$name.'&ScenicSpotID='.$ScenicSpotID.'&tel='.$tel;
        $result = json_decode(file_get_contents($url), TRUE);
        if ($result['flag'] != 1)
            $this->returnJson(-1, $result['result']);
        $this->returnJson('1', '报名成功');
    }

    //查询景区票务列表
    public function  selectTicket(){
        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotTicketVo&ScenicSpotID&page&size
        $page = I('post.page');
        $size = I('post.size');
        $name = I('post.name');
        $page = empty($page) ? 1 : $page;
        $size = empty($size) ? 20 : $size;
        $lon = I('request.lon', '') ? I('request.lon') : $this->returnJson(0, '缺少景区经度');
        $lat = I('request.lat', '') ? I('request.lat') : $this->returnJson(0, '缺少景区维度');
        $ScenicSpotID =$this->selectId($lon, $lat);
        $url=C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotTicketVo&page='.$page.'&size='.$size.'&ScenicSpotID='.$ScenicSpotID.'&name='.$name;
        $result=json_decode(file_get_contents($url),true);
        if ($result['flag'] == 1)
            $this->returnJson(1, '成功', $result['result']);
        else
            $this->returnJson(0, $result['result']);
    }

    //根据id查询景区票务
    public  function  selectTicketById(){
        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotTicketVoByID&id
        $id=I('post.id');
        if(!$id){
            $this->returnJson(0, '缺少票务id');
        }
        $url=C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotTicketVoByID&id='.$id;
        $result=json_decode(file_get_contents($url),true);
        if ($result['flag'] == 1)
            $this->returnJson(1, '成功',$result['result']);
        else
            $this->returnJson(0, $result['result']);
    }

    //发送验证码
    public function sendCDkey(){
        //http://ip:port/ssh2/sceman?cmd=sendMessage&tel&mius
        $tel = I('post.tel', '') ? I('post.tel') : $this->returnJson(0, '未接受到手机号码');
        $url=C('IP_BASE').'/ssh2/sceman?cmd=sendMessage&tel='.$tel.'&mius=5';
        $result=json_decode(file_get_contents($url),true);
        if ($result['flag'] == 1)
            $this->returnJson(1, '成功',$result['result']);
        else
            $this->returnJson(0, $result['result']);
    }




}
