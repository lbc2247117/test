<?php
namespace Admin\Controller;
use Think\Controller;

defined('MEDIA_PATH') or define('MEDIA_PATH', 'upload/');	//B端景区媒体文件存放地址
defined('IMG_PATH') or define('IMG_PATH', 'picture/bsce/');	//B端景区图片存放地址
defined('QR_PATH') or define('QR_PATH', 'qr/');	//B端景区图片存放地址

class IndexController extends BaseController{

    public function index(){
        $this->display();
    }

    public function getQR(){
        $url = 'http://www.baidu.com';
        $size = 35;   //18 = 500*500的二维码  35 = 1000*1000的二维码
        echo $this->generateQR($url,$size);
    }
}