<?php

namespace Admin\Controller;
use Think\Controller;

defined('MEDIA_PATH') or define('MEDIA_PATH', 'upload/');   //B端景区媒体文件存放地址
defined('IMG_PATH') or define('IMG_PATH', 'picture/bsce/'); //B端景区图片存放地址
defined('QR_PATH') or define('QR_PATH', 'qr/'); //B端景区图片存放地址

class BaseController extends Controller {

    protected $lon;
    protected $lat;

    protected function _initialize() {
        $sid = session('sce_adminId');
        $this->lon = session('sce_lon');
        $this->lat = session('sce_lat');
        //查询景区的名字发给前端
        $sceInfo = M('AllSce')->field('SceName')->where(array('Longitude' => $this->lon, 'Latitude' => $this->lat))->find();
        $this->assign('scename', $sceInfo['SceName']);
        S('sceName', $sceInfo['SceName']);
        //判断用户是否登陆
        if (!isset($sid) || !isset($this->lon) || !isset($this->lat)) {
            redirect(U('Login/index'));
        }
        $this->getAuth();
    }

    protected function getAuth() {
        //获取左侧菜单
        $menuModel = M('sce_menu');
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

    /**
     * 上传图片或视频
     * @param array $file 要上传的文件
     * @param string $ctrlName 要存放的子目录，一般对应控制器名
     * @param float $lat 景区的经度
     * @param float $lon 景区的纬度
     * @param int $size 文件的最大大小
     * @param string $type 文件的类型，默认为image/jpeg|image/png
     * 
     * @return string 文件的相对路径：picture/motion/e213E89/pic.jpg
     */
    protected function uploadPic($file, $ctrlName = 'public', $lat = 0, $lon = 0, $size = 1048576, $type = 'image/jpeg|image/png') {
        if (empty($file))
            return false;
        if ($file["error"] > 0)
            $this->returnJson(0, '上传文件出错，请稍后重试');
        if ($file['size'] > $size)
            $this->returnJson(0, '上传的文件不能超过' . $size . '字节，请重新选择文件');

        $ext = explode('.', $file["name"]);
        $format = $ext[1];
        $filename = md5($ext[0] . time()) . '.' . $format;
        $Imgpath = dirname($_SERVER['SCRIPT_FILENAME']) . '/upload/picture/' . $ctrlName . '/' . md5($lat . $lon) . '/' . $filename;
        $pagepic = 'picture/' . $ctrlName . '/' . md5($lat . $lon) . '/' . $filename;
        if (!is_dir(dirname($Imgpath))) {
            @mkdir(dirname($Imgpath), 0777, true);
        }
        if (!move_uploaded_file($file["tmp_name"], $Imgpath))
            $this->returnJson(0, '移动文件至指定文件夹失败');
        return $pagepic;
    }

    /**
     * 根据经纬度返回id
     */
    protected function selectId($lon, $lat) {
        $sceId = S(md5($lon . $lat));
        if ($sceId)
            return $sceId;
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotVoIDBylonlat&lon=' . $lon . '&lat=' . $lat;
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1) {
            S(md5($lon . $lat), $result['result'], array('type' => 'file', 'expire' => 7200));
            return $result['result'];
        } else
            $this->returnJson(0, $result['result']);
    }

    /**
     * 生成符合规则的二维码
     * 
     * @param file logo 如果上传了图片，则作为二维码的logo
     * @param string $jumpurl 扫描二维码跳转网页
     * @param int $size 二维码的大小 默认4。500*500 传18,1000*1000传35
     * 
     * @return string 二维码的地址
     */
    public function generateQR($jumpurl,$size,$srcLogo = FALSE){
        $qrdir = MEDIA_PATH.IMG_PATH.QR_PATH.date("Y-m-d");
        if(!is_dir($qrdir)){
            @mkdir($qrdir,0777,true);
        }
        $file = $qrdir.'/'.$size.'_'.time().'.jpg';
        qrcodelogo($jumpurl,$size,$file,$srcLogo);//带logo的二维码生成
        return $file;
    }

    //生成一张二维码中间的logo
    public function generateLogo(){
        if (!empty($_FILES['logo']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = IMG_PATH.QR_PATH;
            $upload->rootPath = MEDIA_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['logo']);
            if (!empty($info)) {
                //图片裁剪成一张正方形
                $srcLogo = MEDIA_PATH.$imgpath.$info['savepath'].$info['savename'];
                $image = new \Think\Image(); 
                $image->open($srcLogo);
                $logoWidth = $image->width(); // 返回图片的宽度
                $logoHeight = $image->height(); // 返回图片的高度
                if($logoWidth <= $logoHeight) $logoSzie = $logoWidth;
                else $logoSzie = $logoHeight;
                $image->thumb($logoSzie, $logoSzie,\Think\Image::IMAGE_THUMB_FIXED)->save($srcLogo);
                //为logo图加上白边
                //添加白边
                $bianSize = $logoSzie*5/4;
                $final_image = imagecreatetruecolor($bianSize, $bianSize);
                $color = imagecolorallocate($final_image, 255, 255, 255);
                $colorpath = MEDIA_PATH.$imgpath.$info['savepath'].'tmp.jpg';
                imagefill($final_image, 0, 0, $color);
                imagepng($final_image, $colorpath);
                $image->open($colorpath)->water($srcLogo,\Think\Image::IMAGE_WATER_CENTER,100)->save($srcLogo); 
                unlink($colorpath);
            } else {
                $this->returnJson(0, '检查图片的大小或格式');
            }
        }
        return $srcLogo;
    }
}
