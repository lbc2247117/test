<?php

namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller {

    public function index() {

       /* $redis = new \Redis();
        $result = $redis->connect('192.168.2.106', 6379);
        $redis->set('username','liubocheng');*/
        $this->display();
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    

    public function login() {
        echo S('username');
        $this->display();
    }

    public function AjaxSend() {
        $data = file_get_contents("PHP://input");
        $data = json_decode($data, TRUE);
        $this->ajaxReturn($data);
    
    }

}
