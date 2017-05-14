<?php
namespace Business\Controller;
use Think\Controller;
//商家信息

defined('UPLOAD_PATH') or define('UPLOAD_PATH', 'upload/');        //文件目录
defined('UPLOAD_TICKET') or define('UPLOAD_TICKET', 'picture/bsce/ticket/');        //优惠券
defined('UPLOAD_SELLER') or define('UPLOAD_SELLER', 'picture/bsce/sellerxiu/');        //商家
defined('UPLOAD_SELLERCARD') or define('UPLOAD_SELLERCARD', 'picture/bsce/sellerxiu/');        //商家

class InfoController extends BaseController {
    //判断商家是否完成信息完善
    public function isFullmsg(){
        //查询用户表是否生成了记录
        $model = M('sce_member');
        $where['mogoid'] = $this->sellerId;
        $where['type'] = 2;
        $info = $model->where($where)->find();
        $data = empty($info) ? 0 : 1;
        $this->returnJson(1,'成功',$data);
    }

	//查询商家证件
    public function querySellerinfo(){
        $where['mogoid'] = $this->sellerId;
        $model = M('sce_seller');
        $info = $model->where($where)->find();
        if($info) $this->returnJson(1,'成功',$info);
        else $this->returnJson(1,'暂无数据');
    }
    
    //商家资料补全
    public function addSellerinfo(){
        //上传身份证
        if (!empty($_FILES['card']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = UPLOAD_SELLERCARD;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['card']);
            if (!empty($info)) {
                $data['card'] = $imgpath . $info['savepath'] . $info['savename'];
            } else {
                $this->returnJson(0,'上传手持身份证出错');
                exit;
            }
        }
        //营业执照
        if (!empty($_FILES['service']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = UPLOAD_SELLERCARD;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['service']);
            if (!empty($info)) {
                $data['service'] = $imgpath . $info['savepath'] . $info['savename'];
            } else {
                $this->returnJson(0,'上传营业执照出错');
                exit;
            }
        }
        //存入数据库，先判断是修改还是新增记录
        $data['mogoid'] = $this->sellerId;
        $where['mogoid'] = $this->sellerId;
        $model = M('sce_seller');
        $seller = $model->where(array('mogoid'=>$data['mogoid']))->find();
        if($seller){
            if($model->where($where)->save($data)) $this->returnJson(1,'成功');
            else $this->returnJson(0,'未做修改');
        }else{
            if($model->add($data)) $this->returnJson(1,'成功');
            else $this->returnJson(0,'请稍后再试2');
        }
    }

    //手机发送验证码的接口
    public function sendCode(){
        $tel = I('post.tel');
        empty($tel) && $this->returnJson(0,'请输入手机号码');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=sendMessage&tel='.$tel.'&mius=5';
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if(!$resultApi['flag']) $this->returnJson(0,'超出一天可接受验证码的次数。更换手机号码或明天再试');
        session('authCode',null);
        session_start();
        $authCode = $resultApi['result'];
        $this->set('authCode', $authCode, 300);
        $this->returnJson(1,'成功');
    }

    //新增商家用户数据
    public function addSeller(){
    	$tel = I('post.tel');
    	$code = I('post.code');
    	$pwd = I('post.pwd');
    	$rePwd = I('post.rePwd');
        $mogoid = $this->sellerId;
        //判断手机号或mogoid用户是否已注册
        $model = M('sce_member');
        $isRegtel = $model->where(array('tel'=>$tel))->find();
        !empty($isRegtel) && $this->returnJson(0,'你已经注册了，请更换手机号码');
        $isRegid = $model->where(array('mogoid'=>$mogoid))->find();
        !empty($isRegid) && $this->returnJson(0,"你已经注册了手机号码,".$isRegid['tel']);
    	//验证码比对
    	$authCode = $this->get('authCode');
    	if(!$authCode) $this->returnJson(0,'验证码失效，请重新请求');
    	if($code != $authCode) $this->returnJson(0,'验证码不匹配');
    	if($pwd != $rePwd) $this->returnJson(0,'两次输入密码不正确');
        $data['mogoid'] = $mogoid;
    	$data['tel'] = $tel;
        $data['username'] = session('sce_sellerName');
    	$data['password'] = $pwd;
    	$data['create_at'] = time();
    	$data['type'] = 2;	//1:景区用户 2:商家用户 
    	//根据用户的mongoid去查询商家所在景区的经纬度
    	$id = $this->sellerId;
    	$apiSeller = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantVoByID&id='.$id;
        $resultApiseller = json_decode(file_get_contents($apiSeller),true);
    	$data['Longitude'] = $resultApiseller['result']['lon'];
    	$data['Latitude'] = $resultApiseller['result']['lat'];
    	//数据库新增这个用户
    	if($model->add($data)) $this->returnJson(1,'注册成功');
    	else $this->returnJson(0,'请稍后再试');
    }

    //商家修改账号和密码
    public function updateSeller(){
        $tel = I('post.tel');
        $pwd = I('post.pwd');
        $repwd = I('post.rePwd');
        $code = I('post.code');
        //验证码比对
        $authCode = $this->get('authCode');
        if(!$authCode) $this->returnJson(0,'验证码失效，请重新请求');
        if($code != $authCode) $this->returnJson(0,'验证码不匹配');
        if(!empty($pwd)){
            if($pwd != $repwd) $this->returnJson(0,'两次输入密码不正确');
        }
        !empty($tel) && $data['tel'] = $tel;
        !empty($pwd) && $data['password'] = $pwd;
        //数据保存
        $model = M('sce_member');
        $where['mogoid'] = $this->sellerId;
        if($model->where($where)->save($data))
            $this->returnJson(1,'成功');
        else
            $this->returnJson(0,'未做修改');
    }

    //修改密码时，获取登录手机号的账号
    public function getTel(){
        $where['mogoid'] = $this->sellerId;
        $model = M('sce_member');
        $info = $model->where($where)->field('tel')->find();
        $tel = $info['tel'];
        if($tel)
            $this->returnJson(1,'成功',$tel);
        else
            $this->returnJson(0,'请稍后再试！');
    }

    //比对修改密码时的验证码对不对
    public function checkCode(){
        $code = I('post.code');
        $authCode = $this->get('authCode');
        if(!$authCode) $this->returnJson(0,'验证码失效，请重新请求');
        if($authCode == $code)
            $this->returnJson(1,'成功');
        else{
            $this->returnJson(0,'验证码输入错误，请重新请求');
            session('authCode',null);
        }
    }

}