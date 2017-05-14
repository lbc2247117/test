<?php

namespace Admin\Controller;

use Think\Controller;

class LoginController extends Controller {

    public function login() {
        $this->display();
    }

    public function userLogin() {
        $username = I('post.username');
        $password = I('post.password');
        $where['username'] = $username;
        $where['password'] = $password;
        $rst = M('admin')->where($where)->field('username,lastLoginTime')->find();
        if (!$rst) {
            $result['status'] = 0;
            $result['msg'] = '用户名或密码错误';
            exit(json_encode($result));
        }
        $data['lastLoginTime'] = date("Y-m-d H:i:s", time());
        M('admin')->where($where)->save($data);
        session('userinfo', $rst);
        $result['status'] = 1;
        $result['msg'] = '登录成功';
        exit(json_encode($result));
    }

    public function logout() {
        session('userinfo', null);
        $this->redirect('/Admin/Login/login');
    }

}
