<?php

namespace Home\Controller;

use Think\Controller;

class BaseController extends Controller {

    protected function _initialize() {
        $this->getSetting();
    }

    protected function getSetting() {
        $result = M('setting')->limit(1)->select();
        $this->assign('setting', $result[0]);
    }

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

}
