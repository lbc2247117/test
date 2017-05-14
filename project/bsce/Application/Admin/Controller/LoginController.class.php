<?php
namespace Admin\Controller;
use Think\Controller;

class LoginController extends Controller {
    //登陆主页
    public function index(){
        $this->display();
    }
    //登陆验证
    public function login(){
        if(!IS_POST)$this->error("非法请求");
        $member = M('sce_member');
        $code = I('verify','','strtolower');
        //验证验证码是否正确
        if(!($this->check_verify($code))){
            $this->error('验证码错误');
        }
        //判断是否是商家的第一次登陆，登陆名是mongoid，密码是id的后六位
        $username = trim(I('username'));
        if(!isMobile($username)){
            //判断这个id是真实存在的。
            //http://192.168.0.201:7777/ssh2/sceman?cmd=queryCommercialTenantVoByID&id=57ddf7940fcd46118899dc88
            $apiSeller = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantVoByID&id='.$username;
            $resultApiseller = json_decode(file_get_contents($apiSeller),true);
            if($resultApiseller['flag']){
                //判断是否已经激活，即已经登录过了
                $islogin = $member->where(array('mogoid'=>$username))->find();
                if($islogin) $this->error('你已经激活账号，请用手机号码登陆 :(');
                //判断密码和其后六位是否一致
                if(substr($username, -6) == I('post.password'));
                //登陆成功，将mogodbid存到session中保持登陆
                session('sce_sellerId',$username);
                session('sce_sellerName',$resultApiseller['result']['name']);
                session('sce_lon',$resultApiseller['result']['lon']);
                session('sce_lat',$resultApiseller['result']['lat']);
                $this->redirect("/Business/Seller/create");
            }else{
                $this->error('账号id不存在 :(') ;
            }
        }else{

            $username =I('username');
            $password =I('password','','md5');
            //验证账号密码是否正确
            $user = $member->where(array('tel'=>$username,'password'=>$password))->find();
            if(!$user) {
                $this->error('账号或密码错误 :(') ;
            }

            //验证账户是否被禁用
            if($user['status'] == 0){
                $this->error('账号被禁用，请联系超级管理员 :(') ;
            }
            //更新登陆信息
            $data =array(
                'id' => $user['id'],
                'update_at' => time(),
                'login_ip' => get_client_ip(),
            );
            
            //如果数据更新成功,根据不同的type类型，跳转不同的页面，跳转到后台主页
            if($member->save($data)){
                if($user['type'] == 1){
                    session('sce_adminId',$user['id']);
                    session('sce_username',$user['username']);
                    session('sce_lon',$user['Longitude']);
                    session('sce_lat',$user['Latitude']);
                    //$this->success("登陆成功",U('Index/index'));
                    $this->redirect("Allsce/base");
                }else if($user['type'] == 2){
                    session('sce_sellerId',$user['mogoid']);    //mogodb中的id
                    session('sce_sellerMid',$user['id']);       //mysql中的主键id
                    session('sce_sellerName',$user['username']);
                    session('sce_lon',$user['Longitude']);
                    session('sce_lat',$user['Latitude']);
                    $this->redirect("/Business/Order/list");
                }
            }
        }
    }

    //验证码
    public function verify(){
        $Verify = new \Think\Verify();
        $Verify->codeSet = '0123456789';
        $Verify->fontSize = 24;
        $Verify->length = 4;
        $Verify->imageW = 160;
        $Verify->imageH = 50;
        $Verify->entry();
    }
    
    protected function check_verify($code){
        $verify = new \Think\Verify();
        return $verify->check($code);
    }

    public function logout(){
        session('sce_adminId',null);
        session('username',null);
        redirect(U('Login/index'));
    }
}