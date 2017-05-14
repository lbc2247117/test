<?php
namespace Admin\Controller;
use Think\Controller;
//行程路线

defined('UPLOAD_ROAD') or define('UPLOAD_ROAD', 'picture/bsce/road/');
defined('UPLOAD_PATH') or define('UPLOAD_PATH', 'upload/');        //上传图片路径

class RoadController extends BaseController{
    //查询路线图列表
    public function selectRoad(){
        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotWayVo&page&size&lon&lat&serchName
        $lon = $this->lon;
        $lat = $this->lat;
        $page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 20 : I('post.size');
        $serchName = I('post.serchName');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotWayVo&page='.$page.'&size='.$size.'&lon='.$lon.'&lat='.$lat;
        empty($serchName) ? $apiUrl : $apiUrl .= '&serchName='.$serchName;
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
            if (!file_exists(UPLOAD_PATH.$upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['cover']);
            if (!empty($info)) {
                $travelPic = UPLOAD_ROAD.$info['savepath'].$info['savename'];
            } else {
                $this->returnJson(0,'上传图片出错');
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
            if (!file_exists(UPLOAD_PATH.$upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['road']);
            if (!empty($info)) {
                $url = UPLOAD_ROAD.$info['savepath'].$info['savename'];
            } else {
                $this->returnJson(0,'上传图片出错');
                exit;
            }
        }else{
            $this->returnJson(0,'请选择图片');
        }
        $lon = $this->lon;
        $lat = $this->lat;
        $is_param = 1;
        !empty($name) ? $name : $is_param = 0;
        !empty($routeWay) ? $routeWay : $is_param = 0;
        !empty($remark) ? $remark : $is_param = 0;
        !empty($percentNum) ? $percentNum : $is_param = 0;
        !is_null($state) ? $state : $is_param = 0;
        if($is_param == 0) $this->returnJson(0,'参数缺失');

        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayVo&remark='.$remark.'&lon='.$lon.'&lat='.$lat;
        $apiUrl .= '&travelPic='.$travelPic.'&name='.$name.'&url='.$url.'&percentNum='.$percentNum.'&routeWay='.$routeWay.'&state='.$state;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        //if($result['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        $scenicSpotWayID = $resultApi['result'];
        //将附件表的内容存储起来
        $infoSpot = $_REQUEST['infoSpot'];
        $this->addRoadPlug($scenicSpotWayID,$infoSpot);
    }

    //根据景点，返回素材
    public function queryMapinfo(){
        //http://ip:port/ssh2/sceman?cmd=querySceMapResourceByLonLat&lon=102.027572&lat=29.584219
        //景点的经纬度
        $lon = I('post.lon');
        $lat = I('post.lat');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=querySceMapResourceByLonLat&lon='.$lon.'&lat='.$lat;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        foreach ($resultApi['result'] as $key => $value) {
            if($key == 'remark') continue;
            if(empty($value)) continue;
            $resultApi['result'][$key] = C('IMG_PRE').$value;
        }
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //路线附件表信息
    public function addRoadPlug($id,$json){
        header('Content-Type:text/html; charset=utf-8;');
        //$json = returnArray();
        $info = json_decode($json,true);
        foreach ($info as $k1 => $v1) {
            $belongsDay = $k1;
            foreach ($v1 as $k2 => $v2) {
                $name = $k2;
                $associatedTag = $v2['associatedTag'];
                $sceMapID = $v2['id'];
                foreach ($v2['resource'] as $k3 => $v3) {
                    switch ($v3['resourceType']) {
                        case 0:
                            $resourceType = 0;
                            $remark = urlencode($v3['remark']);
                            $shortPic = 0;
                            $picUrl = urlencode($v3['picUrl']);
                            $resourceID = 0;
                            break;
                        case 1:
                            $resourceType = 1;
                            $remark = urlencode($v3['remark']);
                            $shortPic = urlencode($v3['shortPic']);
                            $picUrl = urlencode($v3['picUrl']);
                            $resourceID = 0;
                            break;
                        case 2:
                            $resourceType = 2;
                            $remark = 0;
                            $shortPic = 0;
                            $picUrl = 0;
                            $resourceID = trim($v3['resourceID']);
                            break;
                        case 3:
                            $resourceType = 3;
                            $remark = urlencode($v3['remark']);
                            $shortPic = 0;
                            $picUrl = 0;
                            $resourceID = 0;
                            break;
                    }
                    $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayAttrVo&scenicSpotWayID='.$id.'&showIndex=0';
                    $apiUrl .= '&picUrl='.$picUrl.'&resourceID='.$resourceID.'&resourceType='.$resourceType.'&sceMapID='.$sceMapID;
                    $apiUrl .= '&shortPic='.$shortPic.'&name='.$name.'&remark='.$remark.'&belongsDay='.$belongsDay.'&associatedTag='.$associatedTag;
                    $resultApi = json_decode(file_get_contents($apiUrl),true);
                    //因为无法进行事务处理，只能当作这些接口都执行成功
                }
            }
        }
        //执行完所有的接口后再返回前端信息
        $this->returnJson(1,'成功');
    }

    //点击编辑一条路线是的接口
    public function editRoad(){
        $id = I('post.id');//'57f89a1d45ce8c5d72b7ddeb';//
        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotWayVoByID&id
        $apiRoad = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotWayVoByID&id='.$id;
        $resRoad = json_decode(file_get_contents($apiRoad),true);
        $resRoad['result']['url'] = C('IMG_PRE').$resRoad['result']['url'];
        $resRoad['result']['travelPic'] = C('IMG_PRE').$resRoad['result']['travelPic'];
        if($resRoad['flag']) $infoRoad['road'] = $resRoad['result'];
        //附件表的内容http://ip:port/ssh2/sceman?cmd=queryScenicSpotWayAttrVo&page&size&scenicSpotWayID
        $apiMsg = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotWayAttrVo&page=1&size=10000&scenicSpotWayID='.$id;
        $resultMsg = json_decode(file_get_contents($apiMsg),true);
        //数据分类
        $temp = array();
        foreach ($resultMsg['result'] as $key => $value) {
            $belongsDay = $value['belongsDay'];
            $name = $value['name'];
            $sceMapID = $value['sceMapID'];
            $associatedTag = $value['associatedTag'];
            switch ($value['resourceType']) {                
                case 0: //图片
                    $resource['id'] = $value['id'];
                    $resource['resourceType'] = 0;
                    $resource['remark'] = $value['remark'];
                    $resource['shortPic'] = 0;
                    $resource['picUrl'] = $value['picUrl'];
                    $resource['islive'] = 0;
                    $resource['resourceID'] = 0;
                    break;
                case 1: //视频
                    $resource['id'] = $value['id'];
                    $resource['resourceType'] = 1;
                    $resource['remark'] = $value['remark'];
                    $resource['shortPic'] = $value['shortPic'];
                    $resource['picUrl'] = $value['picUrl'];
                    $exten = explode('.',$value['picUrl']);
                    $resource['islive'] = end($exten) == 'm3u8' ? 1 : 0;
                    $resource['resourceID'] = 0;
                    break;
                case 2: //商家
                    $resource['id'] = $value['id'];
                    $resource['resourceType'] = 2;
                    $resource['remark'] = 0;
                    $resource['shortPic'] = 0;
                    $resource['picUrl'] = 0;
                    $resource['islive'] = 0;
                    unset($value['resourceMap']['CommercialTenantAttrVoList']);
                    $value['resourceMap']['commercialTenantType'] = typeToName($value['resourceMap']['commercialTenantType']);
                    foreach ($value['resourceMap']['backPic'] as $k => $v) {
                        $value['resourceMap']['backPic'][$k] = C('IMG_PRE').$v;
                    }
                    if(!is_array($value['resourceMap']['backPic'])) 
                        $value['resourceMap']['backPic'] = '';
                    $resource['resourceID'] = $value['resourceMap'];
                    break;
                case 3: //文字描述
                    $resource['id'] = $value['id'];
                    $resource['resourceType'] = 3;
                    $resource['remark'] = $value['remark'];
                    $resource['shortPic'] = 0;
                    $resource['picUrl'] = 0;
                    $resource['islive'] = 0;
                    $resource['resourceID'] = 0;
                    break;
            }
            $infoDay['name'] = $name;
            $infoDay['sceMapID'] = $sceMapID;
            $infoDay['associatedTag'] = $associatedTag;
            $infoDay['resource'] = $resource;
            $temp[$belongsDay][$name]['name'] = $name;
            $temp[$belongsDay][$name]['sceMapID'] = $sceMapID;
            $temp[$belongsDay][$name]['associatedTag'] = $associatedTag;
            $temp[$belongsDay][$name]['resource'][] = $resource;
        }
        foreach ($temp as $key => $value) {
            $info[] = $value;
        }
        $infoRoad['spot'] = $info;
        //数据返回
        if($resultMsg['flag'] == 1) $this->returnJson(1,'成功',$infoRoad);
        else if($resultMsg['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //删除一条附件信息
    public function delRoadspot(){
        $id = I('post.id');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=delScenicSpotWayAttrVo&id='.$id;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$info);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //新增一条附件信息
    public function addRoadspot(){
        //接收数据
        $id = I('post.id');
        $resourceType = I('post.resourceType');
        $name = I('post.name');
        $belongsDay = I('post.belongsDay');
        $sceMapID = I('post.sceMapID');
        $associatedTag = I('post.associatedTag');
        switch ($resourceType) {                
            case 0: //图片
                $resourceType = 0;
                $remark = urlencode(I('post.remark'));
                $shortPic = 0;
                $picUrl = urlencode(I('post.picUrl'));
                $resourceID = 0;
                break;
            case 1: //视频
                $resourceType = 1;
                $remark = urlencode(I('post.remark'));
                $shortPic = urlencode(I('post.shortPic'));
                $picUrl = urlencode(I('post.picUrl'));
                $resourceID = 0;
                break;
            case 2: //商家
                $resourceType = 2;
                $remark = 0;
                $shortPic = 0;
                $picUrl = 0;
                $resourceID = I('post.resourceID');
                break;
            case 3: //文字描述
                $resourceType = 3;
                $remark = urlencode(I('post.remark'));
                $shortPic = 0;
                $picUrl = 0;
                $resourceID = 0;
                break;
        }
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotWayAttrVo&scenicSpotWayID='.$id.'&showIndex=0';
        $apiUrl .= '&picUrl='.$picUrl.'&resourceID='.$resourceID.'&resourceType='.$resourceType.'&sceMapID='.$sceMapID;
        $apiUrl .= '&shortPic='.$shortPic.'&name='.$name.'&remark='.$remark.'&belongsDay='.$belongsDay.'&associatedTag='.$associatedTag;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //修改一个路线附加的信息
    public function updateRoadspot(){
        //&picUrl&resourceID&resourceType&shortPic&remark
        $id = I('post.id'); //'57f9b65045ce21d317d30ea7';//
        $resourceType = I('post.resourceType');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=modifyScenicSpotWayAttrVo&id='.$id.'&resourceType='.$resourceType;
        switch ($resourceType) {                
            case 0: //图片
                $remark = urlencode(I('post.remark'));
                $picUrl = urlencode(I('post.picUrl'));
                break;
            case 1: //视频
                $remark = urlencode(I('post.remark'));
                $shortPic = urlencode(I('post.shortPic'));
                $picUrl = urlencode(I('post.picUrl'));
                break;
            case 2: //商家
                $resourceID = I('post.resourceID');
                break;
            case 3: //文字描述
                $remark = urlencode(I('post.remark'));
                break;
        }
        !empty($remark) && $apiUrl .= '&remark='.$remark;
        !empty($picUrl) && $apiUrl .= '&picUrl='.$picUrl;
        !empty($shortPic) && $apiUrl .= '&shortPic='.$shortPic;
        !empty($resourceID) && $apiUrl .= '&picUrl='.$picUrl;
        !empty($picUrl) && $apiUrl .= '&picUrl='.$picUrl;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功');
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
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=delScenicSpotWayLableAttrVo&id='.$idDay;
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
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=delScenicSpotWayAttrVo&id='.$idDay;
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
        !empty($routeWay) && $apiUrl .= '&routeWay='.$routeWay;
        //封面图片处理
        if (!empty($_FILES['cover']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $upload->rootPath = UPLOAD_PATH.UPLOAD_ROAD ;
            if (!file_exists(UPLOAD_PATH.$upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['cover']);
            if (!empty($info)) {
                $apiUrl .= '&travelPic='.UPLOAD_ROAD.$info['savepath'].$info['savename'];
            } else {
                $this->returnJson(0,'上传图片出错');
                exit;
            }
        }
        //路线图片处理
        if (!empty($_FILES['road']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $upload->rootPath = UPLOAD_PATH.UPLOAD_ROAD ;
            if (!file_exists(UPLOAD_PATH.$upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['road']);
            if (!empty($info)) {
                $apiUrl .= '&url='.UPLOAD_ROAD.$info['savepath'].$info['savename'];
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

    //根据路线id和景点名字删除某个景点的数据
    public function delOnesce(){
        //接收参数http://ip:port/ssh2/sceman?cmd=delScenicSpotWayAttrVoBypidName&pid&name
        $pid = I('post.pid');
        $name = I('post.name');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=delScenicSpotWayAttrVoBypidName&pid='.$pid.'&name='.$name;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功');
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    //根据路线id和天数删除某一天的数据
    public function delOneday(){
        //http://ip:port/ssh2/sceman?cmd=delScenicSpotWayAttrVoBypidDay&pid&day
        $pid = I('post.pid');
        $day = I('post.day');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=delScenicSpotWayAttrVoBypidDay&pid='.$pid.'&day='.$day;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功');
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

}

//方法，返回规定好的数据格式
function returnArray(){
    $array = array(
        '1'=>array(
            '情人桥'=>array(
                'sceMapID'=>'景点的id',
                'associatedTag' => '早餐;午餐;美食',
                'resource' => array(
                    0 => array(
                            'resourceType' => 3,
                            'remark' => '情人桥是贺岁片《私人订制》重要拍摄地，原是座铁索桥，是当年守岛部队的海上了望点。走在铁索桥上摇摇晃晃的，需要几分胆量和机灵。有些小姐既想过桥到了望点体验一下，又怕掉进海水里，过桥时紧紧抓住朋友的手不放，因此这桥又被戏称为“情人桥”。后来为客人安全着想，将原来的铁索桥改造成现在的木板桥。这里依旧成为情侣们最向往的地方。',
                            'shortPic' => '',
                            'picUrl' => '',
                            'resourceID' => ''
                        ),
                    1 => array(
                            'resourceType' => 0,
                            'remark' => '',
                            'shortPic' => '',
                            'picUrl' => 'picture/bsce/qr/u423.jpg',
                            'resourceID' => ''
                        ),
                    2 => array(
                            'resourceType' => 1,
                            'remark' => '',
                            'shortPic' => 'picture/allsce/116.477651+39.994925/thumb/600_600_57ce7b4c37f16.jpg',
                            'picUrl' => 'video/15281060117/2016-09-08/9f0c8c2fd1d846f787c1cd9b1dbe9937.mp4',
                            'resourceID' => ''
                        ),
                    3 => array(
                            'resourceType' => 2,
                            'remark' => '',
                            'shortPic' => '',
                            'picUrl' => '',
                            'resourceID' => '57c69e33a01f9f64eda8caa7'
                        )
                )
            ),
            '白沙滩'=>array(
                'sceMapID'=>'景点的id',
                'associatedTag' => '早餐;午餐;美食',
                'resource' => array(
                    0 => array(
                            'resourceType' => 3,
                            'remark' => '情人桥是贺岁片《私人订制》重要拍摄地，原是座铁索桥，是当年守岛部队的海上了望点。走在铁索桥上摇摇晃晃的，需要几分胆量和机灵。有些小姐既想过桥到了望点体验一下，又怕掉进海水里，过桥时紧紧抓住朋友的手不放，因此这桥又被戏称为“情人桥”。后来为客人安全着想，将原来的铁索桥改造成现在的木板桥。这里依旧成为情侣们最向往的地方。',
                            'shortPic' => '',
                            'picUrl' => '',
                            'resourceID' => ''
                        ),
                    1 => array(
                            'resourceType' => 0,
                            'remark' => '',
                            'shortPic' => '',
                            'picUrl' => 'picture/bsce/qr/u423.jpg',
                            'resourceID' => ''
                        ),
                    2 => array(
                            'resourceType' => 1,
                            'remark' => '',
                            'shortPic' => 'picture/allsce/116.477651+39.994925/thumb/600_600_57ce7b4c37f16.jpg',
                            'picUrl' => 'video/15281060117/2016-09-08/9f0c8c2fd1d846f787c1cd9b1dbe9937.mp4',
                            'resourceID' => ''
                        ),
                    3 => array(
                            'resourceType' => 2,
                            'remark' => '',
                            'shortPic' => '',
                            'picUrl' => '',
                            'resourceID' => '57c69e33a01f9f64eda8caa7'
                        )
                )
            )
        ),
        '2'=>array(
            '情人桥'=>array(
                'sceMapID'=>'景点的id',
                'associatedTag' => '早餐;住宿',
                'resource' => array(
                    0 => array(
                            'resourceType' => 3,
                            'remark' => '情人桥是贺岁片《私人订制》重要拍摄地，原是座铁索桥，是当年守岛部队的海上了望点。走在铁索桥上摇摇晃晃的，需要几分胆量和机灵。有些小姐既想过桥到了望点体验一下，又怕掉进海水里，过桥时紧紧抓住朋友的手不放，因此这桥又被戏称为“情人桥”。后来为客人安全着想，将原来的铁索桥改造成现在的木板桥。这里依旧成为情侣们最向往的地方。',
                            'shortPic' => '',
                            'picUrl' => '',
                            'resourceID' => ''
                        ),
                    1 => array(
                            'resourceType' => 0,
                            'remark' => '',
                            'shortPic' => '',
                            'picUrl' => 'picture/bsce/qr/u423.jpg',
                            'resourceID' => ''
                        ),
                    2 => array(
                            'resourceType' => 1,
                            'remark' => '',
                            'shortPic' => 'picture/allsce/116.477651+39.994925/thumb/600_600_57ce7b4c37f16.jpg',
                            'picUrl' => 'video/15281060117/2016-09-08/9f0c8c2fd1d846f787c1cd9b1dbe9937.mp4',
                            'resourceID' => ''
                        ),
                    3 => array(
                            'resourceType' => 2,
                            'remark' => '',
                            'shortPic' => '',
                            'picUrl' => '',
                            'resourceID' => '57c69e33a01f9f64eda8caa7'
                        )
                )
            ),
            '白沙滩'=>array(
                'sceMapID'=>'景点的id',
                'associatedTag' => '美食',
                'resource' => array(
                    0 => array(
                            'resourceType' => 3,
                            'remark' => '情人桥是贺岁片《私人订制》重要拍摄地，原是座铁索桥，是当年守岛部队的海上了望点。走在铁索桥上摇摇晃晃的，需要几分胆量和机灵。有些小姐既想过桥到了望点体验一下，又怕掉进海水里，过桥时紧紧抓住朋友的手不放，因此这桥又被戏称为“情人桥”。后来为客人安全着想，将原来的铁索桥改造成现在的木板桥。这里依旧成为情侣们最向往的地方。',
                            'shortPic' => '',
                            'picUrl' => '',
                            'resourceID' => ''
                        ),
                    1 => array(
                            'resourceType' => 0,
                            'remark' => '',
                            'shortPic' => '',
                            'picUrl' => 'picture/bsce/qr/u423.jpg',
                            'resourceID' => ''
                        ),
                    2 => array(
                            'resourceType' => 1,
                            'remark' => '',
                            'shortPic' => 'picture/allsce/116.477651+39.994925/thumb/600_600_57ce7b4c37f16.jpg',
                            'picUrl' => 'video/15281060117/2016-09-08/9f0c8c2fd1d846f787c1cd9b1dbe9937.mp4',
                            'resourceID' => ''
                        ),
                    3 => array(
                            'resourceType' => 2,
                            'remark' => '',
                            'shortPic' => '',
                            'picUrl' => '',
                            'resourceID' => '57c69e33a01f9f64eda8caa7'
                        )
                )
            )
        )

    );
    return json_encode($array);
}