<?php
namespace Admin\Controller;
use Think\Controller;
//行程路线

defined('UPLOAD_ROAD') or define('UPLOAD_ROAD', 'picture/bsce/road/');
defined('UPLOAD_PATH') or define('UPLOAD_PATH', 'upload/');        //上传图片路径

class RoadnewController extends BaseController{
    //查询路线图列表
    public function selectRoad(){
        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotWayVo&page&size&lon&lat&serchName
        //2-美食 4 -交通 5-景点 6-住宿
        $lon = $this->lon;
        $lat = $this->lat;
        $page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 20 : I('post.size');
        $serchName = I('post.serchName');
        $state = I('post.state');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotWayVo&page='.$page.'&size='.$size.'&lon='.$lon.'&lat='.$lat;
        empty($serchName) ? $apiUrl : $apiUrl .= '&serchName='.$serchName;
        $state == -1 ? $apiUrl : $apiUrl .= '&state='.$state;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        foreach ($resultApi['result']['data'] as $key => $value) {
            $resultApi['result']['data'][$key]['url'] = C('IMG_PRE').$value['url'];
            $resultApi['result']['data'][$key]['travelPic'] = C('IMG_PRE').$value['travelPic'];
        }
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //新增路线
    public function addRoad(){
        //http://ip:port/ssh2/sceman?cmd=addScenicSpotWayVo&remark&lon&lat&name&url&percentNum&routeWay&state&travelPic
        //接收参数
        $param = I('post.');
        $param = myurlencode($param);
        extract($param);
        //封面图片处理
        if (!empty($_FILES['cover']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $upload->rootPath = UPLOAD_PATH.UPLOAD_ROAD ;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['cover']);
            if (!empty($info)) {
                $travelPic = UPLOAD_ROAD.$info['savepath'].$info['savename'];
            } else {
                $this->returnJson(0,'上传图片出错，检查封面图片格式或大小');
                exit;
            }
        }else{
            $this->returnJson(0,'请选择图片');
        }
        //路线图片处理
        if (!empty($_FILES['road']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $upload->rootPath = UPLOAD_PATH.UPLOAD_ROAD ;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['road']);
            if (!empty($info)) {
                $url = UPLOAD_ROAD.$info['savepath'].$info['savename'];
            } else {
                $this->returnJson(0,'上传图片出错，检查路线图片格式或大小');
                exit;
            }
        }else{
            $this->returnJson(0,'请选择图片');
        }
        $lon = $this->lon;
        $lat = $this->lat;
        $is_param = 1;
        !empty($name) ? $name : $is_param = 0;
        $routeWay = I('post.routeWay');
        !empty($routeWay) ? $routeWay : $is_param = 0;
        !empty($remark) ? $remark : $is_param = 0;
        !empty($percentNum) ? $percentNum : $is_param = 0;
        //!is_null($state) ? $state : $is_param = 0;
        $state = 2;//默认新增的下架状态
        if($is_param == 0) $this->returnJson(0,'参数缺失');

        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayVo&remark='.$remark.'&lon='.$lon.'&lat='.$lat;
        $apiUrl .= '&travelPic='.$travelPic.'&name='.$name.'&url='.$url.'&percentNum='.$percentNum.'&routeWay='.$routeWay.'&state='.$state;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
        $scenicSpotWayID = $resultApi['result'];
        //将附件表的内容存储起来
        $infoSpot = urldecode($_REQUEST['infoSpot']);
        $this->addRoadPlug($scenicSpotWayID,$infoSpot);
    }

    public function addRoadPlug($scenicSpotWayID,$infoSpot){
        header("Content-type: text/html; charset=utf-8");
    	//新增天数http://ip:port/ssh2/sceman?cmd=addScenicSpotWayDayVo&name&remark&scenicSpotWayID&todayMemorandum
    	$id = $scenicSpotWayID;
    	$array = json_decode($infoSpot,true);
    	foreach ($array as $key => $value) {
    		$name = urlencode($value['name']);
    		$remark = 0;
    		$todayMemorandum = urlencode($value['remark']);
            //新增天数
    		$apiAddday = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayDayVo&name='.$name.'&remark='.$remark.'&scenicSpotWayID='.$id.'&todayMemorandum='.$todayMemorandum;
            $resultApiday = json_decode(file_get_contents($apiAddday),true);
    		if(!$resultApiday['flag']) $this->returnJson(0,$resultApiday['result']);
    		$belongsDay = $resultApiday['result'];
    		foreach ($value['info'] as $k => $v) {
    			switch ($v['type']) {
    				case 4:
    					$resourceType = $v['type'];
    					$name = urlencode($v['name']);
    					$trafficInformation = urlencode($v['trafficInformation']);
                        //新增标签
                        $apiLable = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayLableAttrVo&lableInfor=无&lableType=0&name=交通&ScenicSpotWayDayID='.$belongsDay.'&scenicSpotWayID='.$id;
                        $resultLable = json_decode(file_get_contents($apiLable),true);
                        if(!$resultLable['flag']) $this->returnJson(0,$resultLable['result']);
                        $lableid = $resultLable['result'];
                        //新增路线点
                        $apiSpot = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayAttrVo&belongsDay='.$belongsDay.'&scenicSpotWayID='.$id;
                        $apiSpot .= '&resourceType='.$resourceType.'&name='.$name.'&trafficInformation='.$trafficInformation.'&ScenicSpotWayLableAttrID='.$lableid;
                        $resultSpot = json_decode(file_get_contents($apiSpot),true);
                        if(!$resultSpot['flag']) $this->returnJson(0,$resultSpot['result']);
                        break;
    				case 5:
    					$resourceType = $v['type'];
    					$sceMapID = $v['sceMapID'];
    					$trafficInformation = urlencode($v['trafficInformation']);
    					$recommendedReason = urlencode($v['recommendedReason']);
                        //新增标签
                        $apiLable = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayLableAttrVo&lableInfor=无&lableType=1&name=景点&ScenicSpotWayDayID='.$belongsDay.'&scenicSpotWayID='.$id;
                        $resultLable = json_decode(file_get_contents($apiLable),true);
                        if(!$resultLable['flag']) $this->returnJson(0,$resultLable['result']);
                        $lableid = $resultLable['result'];
                        //新增路线点
                        $apiSpot = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayAttrVo&belongsDay='.$belongsDay.'&scenicSpotWayID='.$id;
    					$apiSpot .= '&resourceType='.$resourceType.'&sceMapID='.$sceMapID.'&trafficInformation='.$trafficInformation.'&recommendedReason='.$recommendedReason.'&ScenicSpotWayLableAttrID='.$lableid;
    					$resultSpot = json_decode(file_get_contents($apiSpot),true);
                        if(!$resultSpot['flag']) $this->returnJson(0,$resultSpot['result']);
                        break;
    				case 2:
    					$resourceType = $v['type'];
                        $lable = $v['tag'];
                        //新增标签
                        $apiLable = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayLableAttrVo&lableInfor='.$lable.'&lableType=2&name=美食商家&ScenicSpotWayDayID='.$belongsDay.'&scenicSpotWayID='.$id;
                        $resultLable = json_decode(file_get_contents($apiLable),true);
                        if(!$resultLable['flag']) $this->returnJson(0,$resultLable['result']);
                        $lableid = $resultLable['result'];
                        //路线点
                        foreach ($v['sellers'] as $ks => $vs) {
                            $resourceID = $vs['resourceID'];
                            $trafficInformation = urlencode($vs['trafficInformation']);
                            $recommendedReason = urlencode($vs['recommendedReason']);
                            $apiSpot = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayAttrVo&belongsDay='.$belongsDay.'&scenicSpotWayID='.$id;
                            $apiSpot .= '&resourceType='.$resourceType.'&resourceID='.$resourceID.'&trafficInformation='.$trafficInformation.'&recommendedReason='.$recommendedReason.'&ScenicSpotWayLableAttrID='.$lableid;
                            $resultSpot = json_decode(file_get_contents($apiSpot),true);
                            if(!$resultSpot['flag']) $this->returnJson(0,$resultSpot['result']);
                        }
    					break;
    				case 6:
                        $resourceType = $v['type'];
    					//新增标签
                        $apiLable = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayLableAttrVo&lableInfor=无&lableType=3&name=住宿商家&ScenicSpotWayDayID='.$belongsDay.'&scenicSpotWayID='.$id;
                        $resultLable = json_decode(file_get_contents($apiLable),true);
                        if(!$resultLable['flag']) $this->returnJson(0,$resultLable['result']);
                        $lableid = $resultLable['result'];
                        //路线点
                        foreach ($v['sellers'] as $ks => $vs) {
                            $resourceID = $vs['resourceID'];
                            $trafficInformation = urlencode($vs['trafficInformation']);
                            $recommendedReason = urlencode($vs['recommendedReason']);
                            $apiSpot = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayAttrVo&belongsDay='.$belongsDay.'&scenicSpotWayID='.$id;
                            $apiSpot .= '&resourceType='.$resourceType.'&resourceID='.$resourceID.'&trafficInformation='.$trafficInformation.'&recommendedReason='.$recommendedReason.'&ScenicSpotWayLableAttrID='.$lableid;
                            $resultSpot = json_decode(file_get_contents($apiSpot),true);
                            if(!$resultSpot['flag']) $this->returnJson(0,$resultSpot['result']);
                        }
    					break;
    			}
    		}
    	}
        $this->returnJson(1,'成功');
    }

    //编辑一条路线信息
    public function editRoad(){
        $id = I('post.id');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotWayVoAllInforByID&id='.$id;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if ($resultApi['flag']) {
            $resultApi['result']['travelPic'] = C('FULL_PATH').$resultApi['result']['travelPic'];
            $resultApi['result']['url'] = C('FULL_PATH').$resultApi['result']['url'];
        }
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

        //获取一天的数据
    public function getDay(){
        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotWayDayVoAllInforByID&id
        $id = I('post.idDay');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotWayDayVoAllInforByID&id='.$id;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //删除路线
    public function delRoad(){
        //http://ip:port/ssh2/sceman?cmd=delScenicSpotWayVo&id
        $id = I('post.id');
        if(empty($id))
            $this->returnJson(0,'参数缺失');
        foreach ($id as $key => $value) {
            $url = C('IP_BASE').'/ssh2/sceman?cmd=delScenicSpotWayVo&id='.$value;
            $result = json_decode(file_get_contents($url),true);
            if($result['flag'] == 1) continue;
            else $this->returnJson(0,'删除失败，请稍后再试');
        }
        $this->returnJson(1,'删除成功');
    }

    //删除一天
    public function delDay(){
        //http://ip:port/ssh2/sceman?cmd=delScenicSpotWayDayVo&id
        $idDay = I('post.idDay');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=delScenicSpotWayDayVo&id='.$idDay;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功');
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //删除标签
    public function delLable(){
        //http://ip:port/ssh2/sceman?cmd=delScenicSpotWayLableAttrVo&id
        $idLable = I('post.idLable');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=delScenicSpotWayLableAttrVo&id='.$idLable;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功');
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //删除单条
    public function delAttr(){
        //http://ip:port/ssh2/sceman?cmd=delScenicSpotWayAttrVo&id
        $idAttr = I('post.idAttr');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=delScenicSpotWayAttrVo&id='.$idAttr;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功');
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //修改天数顺序
    public function alertDayturn(){
        //http://ip:port/ssh2/sceman?cmd=swapScenicSpotWayDayVoIndex&id2&id1 turnScenicSpotWayDayVoIndexByID
        $id1 = I('post.idDay1');
        $id2 = I('post.idDay2');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=swapScenicSpotWayDayVoIndex&id2='.$id2.'&id1='.$id1;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功');
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //修改标签顺序
    public function alertLableturn(){
        //http://ip:port/ssh2/sceman?cmd=swapScenicSpotWayLableAttrVoIndex&id2&id1
        $id1 = I('post.idLable1');
        $id2 = I('post.idLable2');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=swapScenicSpotWayLableAttrVoIndex&id2='.$id2.'&id1='.$id1;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功');
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //修改单条顺序
    public function alertAttrturn(){
        //http://ip:port/ssh2/sceman?cmd=swapScenicSpotWayAttrVoIndex&id2&id1
        $id1 = I('post.idAttr1');
        $id2 = I('post.idAttr2');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=swapScenicSpotWayAttrVoIndex&id2='.$id2.'&id1='.$id1;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功');
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //修改路线主表的信息
    public function updateRoad(){
        //http://ip:port/ssh2/sceman?cmd=modifyScenicSpotWayVo&remark&lon&lat&name&url&id&percentNum&routeWay&state&travelPic
        //接收参数
        $param = I('post.');
        $param = myurlencode($param);
        extract($param);
        empty($id) && $this->returnJson(0,'参数传递缺失id');
        $lon = $this->lon;
        $lat = $this->lat;
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=modifyScenicSpotWayVo&id='.$id.'&lon='.$lon.'&lat='.$lat;
        !is_null($state) && $apiUrl .= '&state='.$state;
        !empty($remark) && $apiUrl .= '&remark='.$remark;
        !empty($name) && $apiUrl .= '&name='.$name;
        !empty($percentNum) && $apiUrl .= '&percentNum='.$percentNum;
        $routeWay = I('post.routeWay');
        !empty($routeWay) && $apiUrl .= '&routeWay='.$routeWay;
        //封面图片处理
        if (!empty($_FILES['cover']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $upload->rootPath = UPLOAD_PATH.UPLOAD_ROAD ;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['cover']);
            if (!empty($info)) {
                $apiUrl .= '&travelPic='.UPLOAD_ROAD.$info['savepath'].$info['savename'];
            } else {
                $this->returnJson(0,'上传图片出错，检查封面图片格式或大小');
                exit;
            }
        }
        //路线图片处理
        if (!empty($_FILES['road']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $upload->rootPath = UPLOAD_PATH.UPLOAD_ROAD ;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['road']);
            if (!empty($info)) {
                $apiUrl .= '&url='.UPLOAD_ROAD.$info['savepath'].$info['savename'];
            } else {
                $this->returnJson(0,'上传图片出错，检查封面图片格式或大小');
                exit;
            }
        }
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功');
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //修改天数信息
    public function updateDay(){
        //http://ip:port/ssh2/sceman?cmd=modifyScenicSpotWayDayVo&&name&&todayMemorandum&id
        $id = I('post.idDay');
        $name = I('post.name');
        $todayMemorandum = I('post.msg');
        empty($name) && empty($todayMemorandum) && $this->returnJson(0,'请填写今日主题和今日备忘');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=modifyScenicSpotWayDayVo&name='.urlencode($name).'&todayMemorandum='.urlencode($todayMemorandum).'&id='.$id;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功');
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //修改标签信息（主要是修改美食商家的早中晚餐）
    public function updateLable(){
        //http://ip:port/ssh2/sceman?cmd=modifyScenicSpotWayLableAttrVo&lableInfor&lableType&name&ScenicSpotWayDayID&scenicSpotWayID&id
        $id = I('post.idLable');
        $lableInfor = I('post.lableInfor');

        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=modifyScenicSpotWayLableAttrVo&lableInfor='.$lableInfor.'&id='.$id;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功');
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //修改单条信息，推荐理由，交通,交通名字
    public function updateAttr(){
        $id = I('post.idAttr');
        $name = I('post.name');
        $trafficInformation = I('post.traffic');
        $recommendedReason = I('post.reason');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=modifyScenicSpotWayAttrVo&id='.$id;
        !empty($name) && $apiUrl .= '&name='.urlencode($name);
        $apiUrl .= '&trafficInformation='.urlencode($trafficInformation);
        $apiUrl .= '&recommendedReason='.urlencode($recommendedReason);
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功');
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //新增一天
    public function addDay(){
        $id = I('post.id');
        $infoSpot = $_REQUEST['infoSpot'];
        $array = json_decode($infoSpot,true);
        $name = urlencode($array['name']);
        $remark = 0;
        $todayMemorandum = urlencode($array['remark']);
        //新增天数
        $apiAddday = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayDayVo&name='.$name.'&remark='.$remark.'&scenicSpotWayID='.$id.'&todayMemorandum='.$todayMemorandum;
        $resultApiday = json_decode(file_get_contents($apiAddday),true);
        if(!$resultApiday['flag']) $this->returnJson(0,$resultApiday['result']);
        $belongsDay = $resultApiday['result'];
        $data = $belongsDay;

        foreach ($array['info'] as $k => $v) {
            switch ($v['type']) {
                case 4:
                    $resourceType = $v['type'];
                    $name = urlencode($v['name']);
                    $trafficInformation = urlencode($v['trafficInformation']);
                    //新增标签
                    $apiLable = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayLableAttrVo&lableInfor=无&lableType=0&name=交通&ScenicSpotWayDayID='.$belongsDay.'&scenicSpotWayID='.$id;
                    $resultLable = json_decode(file_get_contents($apiLable),true);
                    if(!$resultLable['flag']) $this->returnJson(0,$resultLable['result']);
                    $lableid = $resultLable['result'];
                    //新增路线点
                    $apiSpot = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayAttrVo&belongsDay='.$belongsDay.'&scenicSpotWayID='.$id;
                    $apiSpot .= '&resourceType='.$resourceType.'&name='.$name.'&trafficInformation='.$trafficInformation.'&ScenicSpotWayLableAttrID='.$lableid;
                    $resultSpot = json_decode(file_get_contents($apiSpot),true);
                    if(!$resultSpot['flag']) $this->returnJson(0,$resultSpot['result']);
                    break;
                case 5:
                    $resourceType = $v['type'];
                    $sceMapID = $v['sceMapID'];
                    $trafficInformation = urlencode($v['trafficInformation']);
                    $recommendedReason = urlencode($v['recommendedReason']);
                    //新增标签
                    $apiLable = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayLableAttrVo&lableInfor=无&lableType=1&name=景点&ScenicSpotWayDayID='.$belongsDay.'&scenicSpotWayID='.$id;
                    $resultLable = json_decode(file_get_contents($apiLable),true);
                    if(!$resultLable['flag']) $this->returnJson(0,$resultLable['result']);
                    $lableid = $resultLable['result'];
                    //新增路线点
                    $apiSpot = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayAttrVo&belongsDay='.$belongsDay.'&scenicSpotWayID='.$id;
                    $apiSpot .= '&resourceType='.$resourceType.'&sceMapID='.$sceMapID.'&trafficInformation='.$trafficInformation.'&recommendedReason='.$recommendedReason.'&ScenicSpotWayLableAttrID='.$lableid;
                    $resultSpot = json_decode(file_get_contents($apiSpot),true);
                    if(!$resultSpot['flag']) $this->returnJson(0,$resultSpot['result']);
                    break;
                case 2:
                    $resourceType = $v['type'];
                    $lable = $v['tag'];
                    //新增标签
                    $apiLable = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayLableAttrVo&lableInfor='.$lable.'&lableType=2&name=美食商家&ScenicSpotWayDayID='.$belongsDay.'&scenicSpotWayID='.$id;
                    $resultLable = json_decode(file_get_contents($apiLable),true);
                    if(!$resultLable['flag']) $this->returnJson(0,$resultLable['result']);
                    $lableid = $resultLable['result'];
                    //路线点
                    foreach ($v['sellers'] as $ks => $vs) {
                        $resourceID = $vs['resourceID'];
                        $trafficInformation = urlencode($vs['trafficInformation']);
                        $recommendedReason = urlencode($vs['recommendedReason']);
                        $apiSpot = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayAttrVo&belongsDay='.$belongsDay.'&scenicSpotWayID='.$id;
                        $apiSpot .= '&resourceType='.$resourceType.'&resourceID='.$resourceID.'&trafficInformation='.$trafficInformation.'&recommendedReason='.$recommendedReason.'&ScenicSpotWayLableAttrID='.$lableid;
                        $resultSpot = json_decode(file_get_contents($apiSpot),true);
                        if(!$resultSpot['flag']) $this->returnJson(0,$resultSpot['result']);
                    }
                    break;
                case 6:
                    $resourceType = $v['type'];
                    //新增标签
                    $apiLable = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayLableAttrVo&lableInfor=无&lableType=3&name=住宿商家&ScenicSpotWayDayID='.$belongsDay.'&scenicSpotWayID='.$id;
                    $resultLable = json_decode(file_get_contents($apiLable),true);
                    if(!$resultLable['flag']) $this->returnJson(0,$resultLable['result']);
                    $lableid = $resultLable['result'];
                    //路线点
                    foreach ($v['sellers'] as $ks => $vs) {
                        $resourceID = $vs['resourceID'];
                        $trafficInformation = urlencode($vs['trafficInformation']);
                        $recommendedReason = urlencode($vs['recommendedReason']);
                        $apiSpot = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayAttrVo&belongsDay='.$belongsDay.'&scenicSpotWayID='.$id;
                        $apiSpot .= '&resourceType='.$resourceType.'&resourceID='.$resourceID.'&trafficInformation='.$trafficInformation.'&recommendedReason='.$recommendedReason.'&ScenicSpotWayLableAttrID='.$lableid;
                        $resultSpot = json_decode(file_get_contents($apiSpot),true);
                        if(!$resultSpot['flag']) $this->returnJson(0,$resultSpot['result']);
                    }
                    break;
            }
        }
        $this->returnJson(1,'成功',$data);
    }

    //添加一个标签
    public function addLable(){
        $id = I('post.id');
        $belongsDay = I('post.belongsDay');
        $infoSpot = $_REQUEST['infoSpot'];

        $v = json_decode($infoSpot,true);
        switch ($v['type']) {
            case 4:
                $resourceType = $v['type'];
                $name = urlencode($v['name']);
                $trafficInformation = urlencode($v['trafficInformation']);
                //新增标签
                $apiLable = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayLableAttrVo&lableInfor=无&lableType=0&name=交通&ScenicSpotWayDayID='.$belongsDay.'&scenicSpotWayID='.$id;
                $resultLable = json_decode(file_get_contents($apiLable),true);
                if(!$resultLable['flag']) $this->returnJson(0,$resultLable['result']);
                $lableid = $resultLable['result'];
                //新增路线点
                $apiSpot = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayAttrVo&belongsDay='.$belongsDay.'&scenicSpotWayID='.$id;
                $apiSpot .= '&resourceType='.$resourceType.'&name='.$name.'&trafficInformation='.$trafficInformation.'&ScenicSpotWayLableAttrID='.$lableid;
                $resultSpot = json_decode(file_get_contents($apiSpot),true);
                if(!$resultSpot['flag']) $this->returnJson(0,$resultSpot['result']);
                break;
            case 5:
                $resourceType = $v['type'];
                $sceMapID = $v['sceMapID'];
                $trafficInformation = urlencode($v['trafficInformation']);
                $recommendedReason = urlencode($v['recommendedReason']);
                //新增标签
                $apiLable = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayLableAttrVo&lableInfor=无&lableType=1&name=景点&ScenicSpotWayDayID='.$belongsDay.'&scenicSpotWayID='.$id;
                $resultLable = json_decode(file_get_contents($apiLable),true);
                if(!$resultLable['flag']) $this->returnJson(0,$resultLable['result']);
                $lableid = $resultLable['result'];
                //新增路线点
                $apiSpot = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayAttrVo&belongsDay='.$belongsDay.'&scenicSpotWayID='.$id;
                $apiSpot .= '&resourceType='.$resourceType.'&sceMapID='.$sceMapID.'&trafficInformation='.$trafficInformation.'&recommendedReason='.$recommendedReason.'&ScenicSpotWayLableAttrID='.$lableid;
                $resultSpot = json_decode(file_get_contents($apiSpot),true);
                if(!$resultSpot['flag']) $this->returnJson(0,$resultSpot['result']);
                break;
            case 2:
                $resourceType = $v['type'];
                $lable = $v['tag'];
                //新增标签
                $apiLable = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayLableAttrVo&lableInfor='.$lable.'&lableType=2&name=美食商家&ScenicSpotWayDayID='.$belongsDay.'&scenicSpotWayID='.$id;
                $resultLable = json_decode(file_get_contents($apiLable),true);
                if(!$resultLable['flag']) $this->returnJson(0,$resultLable['result']);
                $lableid = $resultLable['result'];
                //路线点
                foreach ($v['sellers'] as $ks => $vs) {
                    $resourceID = $vs['resourceID'];
                    $trafficInformation = urlencode($vs['trafficInformation']);
                    $recommendedReason = urlencode($vs['recommendedReason']);
                    $apiSpot = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayAttrVo&belongsDay='.$belongsDay.'&scenicSpotWayID='.$id;
                    $apiSpot .= '&resourceType='.$resourceType.'&resourceID='.$resourceID.'&trafficInformation='.$trafficInformation.'&recommendedReason='.$recommendedReason.'&ScenicSpotWayLableAttrID='.$lableid;
                    $resultSpot = json_decode(file_get_contents($apiSpot),true);
                    if(!$resultSpot['flag']) $this->returnJson(0,$resultSpot['result']);
                }
                break;
            case 6:
                $resourceType = $v['type'];
                //新增标签
                $apiLable = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayLableAttrVo&lableInfor=无&lableType=3&name=住宿商家&ScenicSpotWayDayID='.$belongsDay.'&scenicSpotWayID='.$id;
                $resultLable = json_decode(file_get_contents($apiLable),true);
                if(!$resultLable['flag']) $this->returnJson(0,$resultLable['result']);
                $lableid = $resultLable['result'];
                //路线点
                foreach ($v['sellers'] as $ks => $vs) {
                    $resourceID = $vs['resourceID'];
                    $trafficInformation = urlencode($vs['trafficInformation']);
                    $recommendedReason = urlencode($vs['recommendedReason']);
                    $apiSpot = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayAttrVo&belongsDay='.$belongsDay.'&scenicSpotWayID='.$id;
                    $apiSpot .= '&resourceType='.$resourceType.'&resourceID='.$resourceID.'&trafficInformation='.$trafficInformation.'&recommendedReason='.$recommendedReason.'&ScenicSpotWayLableAttrID='.$lableid;
                    $resultSpot = json_decode(file_get_contents($apiSpot),true);
                    if(!$resultSpot['flag']) $this->returnJson(0,$resultSpot['result']);
                }
                break;
        }
        $this->returnJson(1,'成功');
    }

    //添加一个元素
    public function addAttr(){
        $id = I('post.id');
        $belongsDay = I('post.belongsDay');
        $lableid = I('post.lableid');
        $resourceType = I('post.resourceType');
        $infoSpot = $_REQUEST['infoSpot'];
        $vs = json_decode($infoSpot,true);
        $resourceID = $vs['resourceID'];
        $trafficInformation = urlencode($vs['trafficInformation']);
        $recommendedReason = urlencode($vs['recommendedReason']);
        $apiSpot = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayAttrVo&belongsDay='.$belongsDay.'&scenicSpotWayID='.$id;
        $apiSpot .= '&resourceType='.$resourceType.'&resourceID='.$resourceID.'&trafficInformation='.$trafficInformation.'&recommendedReason='.$recommendedReason.'&ScenicSpotWayLableAttrID='.$lableid;
        $resultSpot = json_decode(file_get_contents($apiSpot),true);
        if(!$resultSpot['flag']) $this->returnJson(0,$resultSpot['result']);
        $this->returnJson(1,'成功');
    }

    public function getArray(){
    	$array = array(
    		0=>array(
    			'name'=>'出发',
    			'remark'=>'今日备忘：上午游览栉田神社，中午尝尝有名的一兰拉面。',
    			'info'=>array(
    				0=>array(
    					'type'=>4,	//交通
    					'name'=>'交通名字',
    					'trafficInformation'=>'交通信息'
    				),
    				1=>array(
    					'type'=>5,	//景点信息
    					'sceMapID'=>'57fb3d23a01f7e0998dff30a',
    					'trafficInformation'=>'步行十分钟，约2公里',
    					'recommendedReason'=>'海滨浴场好玩不花钱，海滨浴场好玩不花钱，海滨浴场好玩不花钱，海滨浴场好玩不花钱。'
    				),
    				2=>array(
    					'type'=>2,	//美食商家
                        'tag'=>'早餐',
                        'sellers'=>array(
                            0=>array(
                                'resourceID'=>'57d3838d0fcd46182080d84e',
                                'trafficInformation'=>'步行十分钟，约2公里',
                                'recommendedReason'=>'大约9点可以在这里吃早餐。',
                            ),
                            1=>array(
                                'resourceID'=>'57d3838d0fcd46182080d84e',
                                'trafficInformation'=>'步行十分钟，约2公里',
                                'recommendedReason'=>'大约9点可以在这里吃早餐。',
                            )
                        )
    				),
    				3=>array(
    					'type'=>6,	//住宿商家
                        'sellers'=>array(
                            0=>array(
                                'resourceID'=>'57ddf54f0fcd46118899dc86',
                                'trafficInformation'=>'步行十分钟，约2公里',
                                'recommendedReason'=>'晚上可以住这里人均价格198元。'
                            ),
                            1=>array(
                                'resourceID'=>'57ddf54f0fcd46118899dc86',
                                'trafficInformation'=>'步行十分钟，约2公里',
                                'recommendedReason'=>'晚上可以住这里人均价格198元。'
                            )
                        )
    				)
    			)
    		)
    	);
    	return json_encode($array);
    }
}