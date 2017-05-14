<?php
namespace Admin\Controller;
use Think\Controller;

class QrController extends BaseController{
    //景区二维码生成
    public function createSceqr(){
    	$lon = $this->lon;
        $lat = $this->lat;
    	$srcLogo = $this->generateLogo();
    	empty($_FILES['logo']['name']) && $srcLogo = FALSE;

    	$jumpurl = C('SCE_HOME_URL').'lon='.$lon.'&lat='.$lat.'&isqr=1';
    	$bigsize = 35;
    	$smallsize = 18;
    	$bigpath = $this->generateQR($jumpurl,$bigsize,$srcLogo);
    	$smallpath = $this->generateQR($jumpurl,$smallsize,$srcLogo);
    	$bigpath = str_replace('upload/', '', $bigpath);
    	$smallpath = str_replace('upload/', '', $smallpath);
        $data = C('FULL_PATH').$smallpath;
    	//组装数组
    	$urls[0]['v']['small'] = $smallpath;
    	$urls[1]['v']['big'] = $bigpath;
    	$urljson = json_encode($urls);
    	//将小的二维码放在
    	$apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=modifyScenicSpotVo&modifyType=1&lon='.$lon.'&lat='.$lat.'&ewmUrl='.$smallpath.'&ewmUrls='.$urljson;
    	$resultApi = json_decode(file_get_contents($apiUrl),true);
    	//数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$data);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //下载景区的二维码
    public function downSceQr() {
        $lon = $this->lon;
        $lat = $this->lat;
        $type = I('get.type');
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotVo&page=1&size=20&lon=' . $this->lon . '&lat=' . $this->lat;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if($resultApi['flag']){
            $small = 'upload/'.$resultApi['result'][0]['rcode'][0]['small'];
            $big = 'upload/'.$resultApi['result'][0]['rcode'][1]['big'];
        }
        switch ($type) {
            case 1: //小尺寸
                $download = $small;
                break;
            case 2: //大尺寸
                $download = $big;
                break;
            
            default:
                $this->returnJson(0,'type参数传递错误');
                break;
        }
        $filename = time() . '.jpg';
        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $content = file_get_contents($download);
        echo $content;
    }

    //获取一个普通活动的二维码
    public function getActqr(){
    	$id = I('post.id');
    	$lon = $this->lon;
        $lat = $this->lat;
        empty($id) && $this->returnJson(0,'参数缺失id！');
        $apiInfo = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotActivityVoByID&id='.$id;
        $resultInfo = json_decode(file_get_contents($apiInfo), true);
        if(empty($resultInfo['result']['QRCodeUrl'])){
        	//生成普通二维码
        	$jumpurl = C('ACT_HOME_URL').'lon='.$lon.'&lat='.$lat.'&id='.$id;
        	$qrpath = $this->generateQR($jumpurl,18);
        	$data = '/bsce/'.$qrpath;
        	//将这张二维码保存起来
        	$qrpath = str_replace('upload/', '', $qrpath);
        	$apiSave = C('IP_BASE').'/ssh2/sceman?cmd=modifyScenicSpotActivityVo&id='.$id.'&QRCodeUrl='.$qrpath;
        	$resultSave = file_get_contents($apiSave,true);
        	if(!$resultSave['flag']) $this->returnJson(0,$resultApi['result']);
        }else{
        	$data = C('FULL_PATH').$resultInfo['result']['QRCodeUrl'];
        }
        $this->returnJson(1,'成功',$data);
    }

    //普通活动二维码的生成
    public function createActqr(){
    	$actid = I('post.id');
    	$lon = $this->lon;
        $lat = $this->lat;
    	empty($actid) && $this->returnJson(0,'参数缺失id！');
    	//处理logo
    	$srcLogo = $this->generateLogo();
    	empty($_FILES['logo']['name']) && $srcLogo = FALSE;

    	$jumpurl = C('ACT_HOME_URL').'lon='.$lon.'&lat='.$lat.'&id='.$actid;
    	$bigsize = 35;
    	$smallsize = 18;
    	$bigpath = $this->generateQR($jumpurl,$bigsize,$srcLogo);
    	$smallpath = $this->generateQR($jumpurl,$smallsize,$srcLogo);
    	$bigpath = str_replace('upload/', '', $bigpath);
    	$smallpath = str_replace('upload/', '', $smallpath);
        $data = C('FULL_PATH').$smallpath;
    	//组装数组
    	$urls[0]['v']['small'] = $smallpath;
    	$urls[1]['v']['big'] = $bigpath;
    	$urljson = json_encode($urls);

    	$apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=modifyScenicSpotActivityVo&id='.$actid.'&QRCodeUrl='.$smallpath.'&QRCodeUrls='.$urljson;
    	$resultApi = json_decode(file_get_contents($apiUrl),true);
    	//数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$data);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //下载普通活动的二维码
    public function downActQr(){
        $id = I('get.id');
        $lon = $this->lon;
        $lat = $this->lat;
        $type = I('get.type');
        empty($id) && empty($type) && $this->returnJson(0,'参数缺失lon，lat！');
        $apiInfo = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotActivityVoByID&id='.$id;
        $resultInfo = json_decode(file_get_contents($apiInfo), true);
        if(empty($resultInfo['result']['rcode'])){
            //生成二维码以供下载
            $jumpurl = C('ACT_HOME_URL').'lon='.$lon.'&lat='.$lat.'&id='.$id;
            $bigsize = 35;
            $smallsize = 18;
            $bigpath = $this->generateQR($jumpurl,$bigsize,$srcLogo);
            $smallpath = $this->generateQR($jumpurl,$smallsize,$srcLogo);
            $bigpath = str_replace('upload/', '', $bigpath);
            $smallpath = str_replace('upload/', '', $smallpath);
            //组装数组
            $urls[0]['v']['small'] = $smallpath;
            $urls[1]['v']['big'] = $bigpath;
            $urljson = json_encode($urls);

            $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=modifyScenicSpotActivityVo&id='.$id.'&QRCodeUrl='.$smallpath.'&QRCodeUrls='.$urljson;
            $resultApi = json_decode(file_get_contents($apiUrl),true);
            //生成成功，则提供下载
            if($resultApi['flag']){
                $small = 'upload/'.$smallpath;
                $big = 'upload/'.$bigpath;
            }else $this->returnJson(0,'二维码生成失败，请先上传logo，生成二维码');
        }else{
            $small = 'upload/'.$resultInfo['result']['rcode'][0]['small'];
            $big = 'upload/'.$resultInfo['result']['rcode'][1]['big'];
        }
        switch ($type) {
            case 1: //小尺寸
                $download = $small;
                break;
            case 2: //大尺寸
                $download = $big;
                break;
            
            default:
                $this->returnJson(0,'type参数传递错误');
                break;
        }
        $filename = time() . '.jpg';
        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $content = file_get_contents($download);
        echo $content;
    }

    //查询移动高清直播的二维码
    public function getLiveqr(){
    	$id = I('post.id');
    	$lon = $this->lon;
        $lat = $this->lat;
    	//$json = I('post.js2dCode');
        $json = $_POST['js2dCode'];
        $array = json_decode($json);
        empty($id) && empty($json) && $this->returnJson(0,'参数缺失');
    	if($json == 'none'){
    		//创建二维码
    		$jumpurl = C('LIVE_HOME_URL').'lon='.$lon.'&lat='.$lat.'&id='.$id;
	    	$bigsize = 35;
	    	$smallsize = 18;
	    	$bigpath = $this->generateQR($jumpurl,$bigsize,$srcLogo);
	    	$smallpath = $this->generateQR($jumpurl,$smallsize,$srcLogo);
	    	$bigpath = str_replace('upload/', '', $bigpath);
	    	$smallpath = str_replace('upload/', '', $smallpath);
	    	$urls['small'] = $smallpath;
	    	$urls['big'] = $bigpath;
	    	$urljson = json_encode($urls);
			
			$apiUrl = C('IP_LIVE').'/ssh2/cloudvideo?cmd=setsce&vid='.$id.'&js2dCode='.$urljson.'&sec=756489214459';
			$resultApi = json_decode(file_get_contents($apiUrl),true);
			if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
			$data['small'] = C('FULL_PATH').$smallpath;
            $data['big'] = C('FULL_PATH').$bigpath;
    	}else{
    		$info = json_decode($json,true);
    		$data['small'] = C('FULL_PATH').$info['small'];
            $data['big'] = C('FULL_PATH').$info['big'];
    	}
    	$this->returnJson(1,'成功',$data);
    }

    //创建移动高清直播的二维码
    public function createLiveqr(){
    	$id = I('post.id');
    	$lon = $this->lon;
        $lat = $this->lat;
    	empty($id) && $this->returnJson(0,'参数缺失id！');
    	$srcLogo = $this->generateLogo();
    	empty($_FILES['logo']['name']) && $srcLogo = FALSE;

    	$jumpurl = C('LIVE_HOME_URL').'lon='.$lon.'&lat='.$lat.'&id='.$id;
    	$bigsize = 35;
    	$smallsize = 18;
    	$bigpath = $this->generateQR($jumpurl,$bigsize,$srcLogo);
    	$smallpath = $this->generateQR($jumpurl,$smallsize,$srcLogo);
    	$bigpath = str_replace('upload/', '', $bigpath);
    	$smallpath = str_replace('upload/', '', $smallpath);
    	$urls['small'] = $smallpath;
    	$urls['big'] = $bigpath;
        $paths['small'] = C('FULL_PATH').$smallpath;
        $paths['big'] = C('FULL_PATH').$bigpath;
    	$urljson = json_encode($urls);
		
		$apiUrl = C('IP_LIVE').'/ssh2/cloudvideo?cmd=setsce&vid='.$id.'&js2dCode='.$urljson.'&sec=756489214459';
		$resultApi = json_decode(file_get_contents($apiUrl),true);
		//数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$paths);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //下载移动高清直播的二维码
    public function downLiveQr(){
        //获取二维码信息
        $path = I('get.path');
        empty($path) && $this->returnJson(0,'参数缺失');
        $path = 'upload/'.str_replace(C('FULL_PATH'), '', $path);
        $filename = time() . '.jpg';
        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $content = file_get_contents($path);
        echo $content;
    }
}