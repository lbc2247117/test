<?php

namespace Admin\Controller;

use Think\Controller;

class BaseController extends Controller {

    protected function _initialize() {
        if (!session('userinfo'))
            $this->redirect('/Admin/Login/login');
        $this->assign('userinfo', session('userinfo'));
        $this->getAuth();
    }

    protected function getAuth() {
        //获取左侧菜单
        $menuModel = M('menu');
        $whereMenu['disabled'] = 1;
        $menu = $menuModel->where($whereMenu)->order('sort asc')->select();
        $menus = array();
        for ($i = 0; $i < count($menu); $i++) {
            if ($menu[$i]['pid'] == 0)
                $menus[] = $menu[$i];
        }
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
