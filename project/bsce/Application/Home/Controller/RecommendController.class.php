<?php

/**
 *此类用于存放行程推荐模块相关的接口
 */

namespace Home\Controller;

class RecommendController extends BaseController {
	/**   
    * 查询一个景区的所有线路信息
    *  
    * @param string $lon 景区横坐标  
    * @param string $lat 景区纵坐标   
    *    
    * @return json
    */
    public function queryRoad() {
    	$page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 20 : I('post.size');
        $lon = I('post.lon');
        $lat = I('post.lat');

        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotWayVo&page='.$page.'&size='.$size.'&lon='.$lon.'&lat='.$lat.'&state=0';
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if($resultApi['flag'] == 1){
        	foreach ($resultApi['result']['data'] as $key => $value) {

        		$resultApi['result']['data'][$key]['url'] = C('IMG_PRE').$value['travelPic'];
        	}
        }
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    /**   
    * 查询路线详细信息
    *  
    * @param string $lon 景区横坐标  
    * @param string $lat 景区纵坐标   
    *    
    * @return json
    */
    public function queryRoadDetail(){
    	$page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 10000 : I('post.size');
        $id = I('post.id');
        $lon = I('post.lon');
        $lat = I('post.lat');

        //先查询出路线主表信息
        $api = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotWayVoByID&id='.$id;
        $result = json_decode(file_get_contents($api),true);
        if($result['flag']){
            $result['result']['url'] = C('IMG_PRE').$result['result']['url'];
            $res['info'] = $result['result'];
        }else $this->returnJson(0,$resultApi['result']);
        
        //查出附件表的信息
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotWayAttrVo&page='.$page.'&size='.$size.'&scenicSpotWayID='.$id;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if($resultApi['flag'] == 1){
        	foreach ($resultApi['result'] as $key => $value) {
        		foreach ($value['resourceMap']['backPic'] as $k => $v) {
	        		$resultApi['result'][$key]['resourceMap']['backPic'][$k] = C('IMG_PRE').$v;
	        	}
	        	foreach ($value['resourceMap']['CommercialTenantAttrVoList'] as $k => $v) {
	        		$resultApi['result'][$key]['resourceMap']['CommercialTenantAttrVoList'][$k]['picUrl'] = C('IMG_PRE').$v['picUrl'];
	        		$resultApi['result'][$key]['resourceMap']['CommercialTenantAttrVoList'][$k]['url'] = C('IMG_PRE').$v['url'];
	        	}
                //将相同的路线点放到一个数组中
                $k = $value['name'];
                $res[$k][] = $resultApi['result'][$key];
        	}
        }

        //查出推荐的信息
        $apiRmd = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotWayVo&page=1&size=100&lon='.$lon.'&lat='.$lat;
        $resultRmd = json_decode(file_get_contents($apiRmd),true);
        if($resultRmd['flag']){
            if(count($resultRmd['result']) == 1){
                $res['info']['rmd'] = $resultRmd['result'][0];
            }else{
                if($id == $resultRmd['result'][0]['id'])
                    $res['info']['rmd'] = $resultRmd['result'][1];
                else
                    $res['info']['rmd'] = $resultRmd['result'][0];
            }
            $res['info']['rmd']['url'] = C('IMG_PRE').$res['info']['rmd']['url'];
        }

        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$res);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    public function getRoadinfo(){
        $id = I('post.id');
        $lon = I('post.lon');
        $lat = I('post.lat');
        //先查询出路线主表信息
        $apiRoad = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotWayVoByID&id='.$id;
        $resRoad = json_decode(file_get_contents($apiRoad),true);
        $resRoad['result']['url'] = C('IMG_PRE').$resRoad['result']['url'];
        $resRoad['result']['travelPic'] = C('IMG_PRE').$resRoad['result']['travelPic'];
        if($resRoad['flag']) $infoRoad['road'] = $resRoad['result'];
        //路线附表
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

        //查出推荐的信息
        $apiRmd = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotWayVo&page=1&size=100&lon='.$lon.'&lat='.$lat;
        $resultRmd = json_decode(file_get_contents($apiRmd),true);
        if($resultRmd['flag']){
            if(count($resultRmd['result']['data']) == 1){
                $rcmd = $resultRmd['result']['data'][0];
            }else{
                if($id == $resultRmd['result']['data'][0]['id'])
                    $rcmd = $resultRmd['result']['data'][1];
                else
                    $rcmd = $resultRmd['result']['data'][0];
            }
            $rcmd['url'] = C('IMG_PRE').$rcmd['url'];
            $rcmd['travelPic'] = C('IMG_PRE').$rcmd['travelPic'];
        }
        $infoRoad['rcmd'] = $rcmd;

        //数据返回
        if($resultMsg['flag'] == 1) $this->returnJson(1,'成功',$infoRoad);
        else if($resultMsg['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    public function roadInfo(){
        $id = I('post.id');
        $lon = I('post.lon');
        $lat = I('post.lat');
        empty($id) && empty($lon) && empty($lat) && $this->returnJson(0,'参数缺失');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotWayVoAllInforByID&id='.$id;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //处理商家的价格
        foreach ($resultApi['result']['ScenicSpotWayDayVo'] as $key => $value) {
            foreach ($value['ScenicSpotWayLableAttrVo'] as $k => $v) {
                if($v['lableType'] == 3 || $v['lableType'] == 2){
                    foreach ($v['ScenicSpotWayAttrVo'] as $ka => $va) {
                        $resultApi['result']['ScenicSpotWayDayVo'][$key]['ScenicSpotWayLableAttrVo'][$k]['ScenicSpotWayAttrVo'][$ka]['resourceMap']['averPrice'] = intval($va['resourceMap']['averPrice']);
                    }
                }
            }
        }
        //查出推荐的信息
        $apiRmd = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotWayVo&page=1&size=3&lon='.$lon.'&lat='.$lat;
        $resultRmd = json_decode(file_get_contents($apiRmd),true);
        if($resultRmd['flag']){
            if(count($resultRmd['result']['data']) == 1){
                $rcmd = $resultRmd['result']['data'][0];
            }else{
                if($id == $resultRmd['result']['data'][0]['id'])
                    $rcmd = $resultRmd['result']['data'][1];
                else
                    $rcmd = $resultRmd['result']['data'][0];
            }
            $rcmd['url'] = C('FULL_PATH').$rcmd['url'];
            $rcmd['travelPic'] = C('FULL_PATH').$rcmd['travelPic'];
        }
        $resultApi['result']['rcmd'] = $rcmd;
        $resultApi['result']['imgpre'] = C('IMG_PRE');

        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }
}


function typeToName($type){
    switch ($type) {
        case 0:
            $name = '美食';
            break;
        case 1:
            $name = '住宿';
            break;
        case 2:
            $name = '购物';
            break;
        case 3:
            $name = '娱乐';
            break;
        
        default:
            $name = '美食';
            break;
    }
    return $name;
}