<?php
namespace Business\Controller;
use Think\Controller;
class IndexController extends BaseController {

	//商户退出登录
    public function logout(){
        session('sce_sellerId',null);
        session('sce_sellerName',null);
        $this->redirect("/Admin/Login/index");
    }
}