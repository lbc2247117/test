<?php
namespace Admin\Controller;
use Think\Controller;
/**
 *景点相关接口   
*/

defined('UPLOAD_PATH') or define('UPLOAD_PATH', 'upload/');        //上传图片路径
defined('SCEMAP') or define('SCEMAP', 'picture/scemap/');        //景区图片存放

 class ScemapController extends BaseController{
 	 //景点管理
 	 //查询景点

 	 public function selectScemap(){
 		//http://ip:port/ssh2/sceman?cmd=SigqueryScenicSpotVoByName&lon&lat&serchName&page&size
 		$page = I('post.page');
 		$size = I('post.size');
        $page = empty($page) ? 1 : $page;
        $size = empty($size) ? 20 : $size;
 		$serchName = urlencode(I('post.serchName'));
 		$url= C('IP_BASE').'/ssh2/sceman?cmd=SigqueryScenicSpotVoByName&lon='.$this->lon.'&lat='.$this->lat.'&serchName='.$serchName.'&page='.$page.'&size='.$size;
        $result= json_decode(file_get_contents($url),true);
        foreach ($result['result']['data'] as $key => $value) {
            $result['result']['data'][$key]['pageFm'] = C('IMG_PRE').$value['pageFm'];
        }
        $result['result']['lon']=$this->lon;
        $result['result']['lat']=$this->lat;
 		if($result['flag']== 1){
 			$this->returnJson(1,'操作成功',$result['result']);
 		}else{
 			$this->returnJson(0,$result['result']);
 		}
 	}

    //新增景点
    public function addScemap(){
        //http://ip:port/ssh2/sceman?cmd=addScenicMapSpotVo&maplat&maplon&lon&lat&mapBackgroudPic&sceRemark&name&raduis&pageFm
        $lon = $this->lon;
        $lat = $this->lat;
        $maplon = I('post.maplon');
        $maplat = I('post.maplat');
        $sceRemark = urlencode(I('post.sceRemark'));
        $name = urlencode(I('post.name'));
        $raduis = I('post.raduis','','int');
        $price = I('post.price',-1,'float');
        if( $raduis== -1){
            $this->returnJson(0,'景点半径范围格式不正确，请用整数，如100、1000等');
        }
        if( $price== -1){
            $this->returnJson(0,'门票价格格式不正确，请用数字，如99、198等');
        }
        $price=round($price,2);
        if (!empty($_FILES['cover']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = SCEMAP.$maplon.'+'.$maplat.'/' ;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['cover']);
            if (!empty($info)) {
                $pageFm = urlencode($imgpath . $info['savepath'] . $info['savename']);
            } else {
                $this->returnJson(0,'上传图片出错');
                exit;
            }
        }else{
            $this->returnJson(0,'请选择图片上传');
        }
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=addScenicMapSpotVo&lon='.$this->lon.'&lat='.$this->lat;
        $apiUrl .= '&maplat='.$maplat.'&maplon='.$maplon.'&sceRemark='.$sceRemark.'&name='.$name.'&raduis='.$raduis.'&pageFm='.$pageFm.'&price='.$price;

        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功');
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }
 	 //修改景点
 	 public function updateScemap(){
 		//http://ip:port/ssh2/sceman?cmd=modifyScenicMapSpotVo&id&maplat&maplon&lon&lat&mapBackgroudPic&sceRemark&name&raduis&pageFm
        $lon=$this->lon;
        $lat=$this->lat;
        $maplon = I('post.maplon');
        $maplat = I('post.maplat');
        $id = I('post.id');
        $name = urlencode(I('post.name'));
        $sceRemark = urlencode(I('post.sceRemark'));
        $raduis = I('post.raduis',-1,'int');
        $price = I('post.price',-1,'float');
         if( $raduis== -1){
             $this->returnJson(0,'景点半径范围格式不正确，请用整数，如100、1000等');
         }
         if( $price== -1){
             $this->returnJson(0,'门票价格格式不正确，请用数字，如99、198等');
         }
         $price=round($price,2);
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=modifyScenicMapSpotVo&id='.$id.'&lon='.$lon.'&lat='.$lat;
        !empty($name) && $apiUrl .= '&name='.$name;
        !empty($sceRemark) && $apiUrl .= '&sceRemark='.$sceRemark;
        !empty($raduis) && $apiUrl .= '&raduis='.$raduis;
        !empty($price) && $apiUrl .= '&price='.$price;
        if (!empty($_FILES['cover']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = SCEMAP . $maplon . '+' . $maplat . '/' ;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['cover']);
            if (!empty($info)) {
                $apiUrl .= '&pageFm='.urlencode($imgpath . $info['savepath'] . $info['savename']);
            } else {
                $this->returnJson(0,'上传图片出错');
                exit;
            }
        }

        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功');
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
 	}

    //删除一个景点
    public function delScemap(){
        //http://ip:port/ssh2/sceman?cmd=delScenicMapSpotVo&id
        $ids = I('post.ids');
        foreach ($ids as $key => $value) {
            $url = C('IP_BASE').'/ssh2/sceman?cmd=delScenicMapSpotVo&id='.$value;
            $result = json_decode(file_get_contents($url),true);
            if($result['flag'] == 1) continue;
            else $this->returnJson(0,'删除失败，请稍后再试');
        }
        $this->returnJson(1,'删除成功');
    }

    //编辑一个景点
    public function editScemap(){
        //http://ip:port/ssh2/sceman?cmd=queryScenicMapSpotVoByID&id
        $id = I('post.id');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicMapSpotVoByID&id='.$id;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        $resultApi['result']['pageFm'] = C('IMG_PRE').$resultApi['result']['pageFm'];
        if(substr($resultApi['result']['price'],-1)==0){
            $resultApi['result']['price'] =  str_replace('.0','', $resultApi['result']['price']);
            $resultApi['result']['price'] =  str_replace('.00','', $resultApi['result']['price']);
        }
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

 }