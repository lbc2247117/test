<?php

/**
 * 控制器基类
 *
 * @author LiuBoCheng
 * @copyright (c) 2016, 云道
 * @version 2016-08-23
 */

namespace Admin\Controller;

use Think\Controller;

class BaseController extends Controller {

    /**
     * 给前端返回JSON字符串
     * 
     * @param int $status 状态码，1表示成功，非1表示失败
     * @param string $msg 提示信息
     * @param array $data 数据
     * 
     * @return string 如:{'status':1,'msg':'成功','data':{'count':150,'list':[{'id':1,'name':'li'},{'id':2,'name':'liu'}]}}
     */
    protected function returnJson($status = 0, $msg = '', $data = '') {
        $result['status'] = $status;
        $result['msg'] = $msg;
        $result['data'] = $data;
        exit(json_encode($result));
    }

    protected function _initialize() {
        $sid = session('activeAdminId');
        //判断用户是否登陆
        if (!isset($sid)) {
            redirect(U('Login/index'));
        }
    }

}
