<?php

namespace Business\Controller;

use Think\Controller;

//商家自营基类

class BaseController extends Controller {

    protected $sellerId;
    protected $lon;
    protected $lat;

    //初始化函数
    protected function _initialize() {
        $this->sellerId = session('sce_sellerId');
        $this->lon = session('sce_lon');
        $this->lat = session('sce_lat');
        //判断用户是否登陆
        if (!$this->sellerId) {
            $this->redirect("/Admin/Login/index");
        }
        //判断这个id是否有手机号码
        $where['mogoid'] = session('sce_sellerId');
        $isReg = M('sce_member')->where($where)->find();
        if($isReg) $this->assign('isReg',1);
        else $this->assign('isReg',0);
        $this->getAuth();
    }

    protected function getAuth() {
        //获取左侧菜单
        $menuModel = M('sce_bmenu');
        $whereMenu['disabled'] = 1;
        $menu = $menuModel->where($whereMenu)->order('sort asc')->select();
        $menus = array_filter($menu, function($item) {
            return $item['pid'] == 0;
        });
        for ($i = 0; $i < count($menus); $i++) {
            $menup = $menus[$i];
            $childs = array_filter($menu, function($item) use($menup) {
                return $item['pid'] == $menup['id'];
            });
            $menus[$i]['childs'] = array_values($childs);
        }
        $result['menus'] = $menus;
        $this->assign('auth', $result);
    }

    /**
     * 设置session
     * @param String $name  session name
     * @param Mixed $data  session data
     * @param Int  $expire 超时时间(秒)
     */
    protected function set($name, $data, $expire = 120) {
        $session_data = array();
        $session_data['data'] = $data;
        $session_data['expire'] = time() + $expire;
        $_SESSION[$name] = $session_data;
    }

    /**
     * 读取session
     * @param String $name session name
     * @return Mixed
     */
    public function get($name) {
        if (isset($_SESSION[$name])) {
            if ($_SESSION[$name]['expire'] > time()) {
                return $_SESSION[$name]['data'];
            } else {
                session($name,null);
            }
        }
        return false;
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
