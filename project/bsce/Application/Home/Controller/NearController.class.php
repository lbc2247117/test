<?php

/**
 *此类用于存放附近服务模块相关的接口
 */

namespace Home\Controller;
defined('UPLOAD_PATH') or define('UPLOAD_PATH', 'upload/');        //上传资源的路径

class NearController extends BaseController {
    /**   
    * 获取一个种类，一个景区的商家
    *  
    * @param int $type 类型   0：美食 1：住宿 2：购物 3：娱乐
    * @param string $lon 景区横坐标  
    * @param string $lat 景区纵坐标   
    *    
    * @return json
    */
    public function querySeller() {
    	//获取前端数据
    	$page = I('post.page') == NULL ? 1 : I('post.page');
    	$size = I('post.size') == NULL ? 20 : I('post.size');
    	$type = I('post.type');
        switch ($type) {
            case 'food':
                $type = 0;break;
            case 'hotel':
                $type = 1;break;
            case 'shop':
                $type = 2;break;
            case 'fun':
                $type = 3;break;
        }
        $lon = I('post.lon');
        $lat = I('post.lat');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantVo&page='.$page.'&size='.$size;
        $apiUrl .= '&CommercialTenantStyle=-1&frameState=0&CommercialTenantType='.$type.'&lon='.$lon.'&lat='.$lat;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if($resultApi['flag'] == 1){
            foreach ($resultApi['result']['data'] as $key => $value) {
                foreach ($value['backPic'] as $k => $v) {
                    $resultApi['result']['data'][$key]['backPic'][$k] = C('IMG_PRE').$v;
                }
                //处理人均价格
                $resultApi['result']['data'][$key]['averPrice'] = intval($value['averPrice']);
            }
        }
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    /**   
    * 附近搜索
    *  
    * @param string $serchName 搜索关键字
    * @param string $lon 景区横坐标  
    * @param string $lat 景区纵坐标   
    *    
    * @return json
    */
    public function searchNear(){
        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotVoByName&serchName&lon&lat
        $serchName = I('post.searchName');
        $lon = I('post.lon');
        $lat = I('post.lat');

        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotVoByName&serchName='.$serchName.'&lon='.$lon.'&lat='.$lat;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        foreach ($resultApi['result'] as $key => $value) {
            //拼接景点的数据图片
            if($key == 'ScenicMapSpotVo'){
                //var_dump($value);exit;
                foreach ($value['data'] as $k => $v) {
                    $resultApi['result'][$key]['data'][$k]['pageFm'] = C('IMG_PRE').$v['pageFm'];
                }
            }
            //拼接商铺的图片
            else{
                foreach ($value['data'] as $k => $v) {
                    foreach ($v['backPic'] as $k1 => $v1) {
                        $resultApi['result'][$key]['data'][$k]['backPic'][$k1] = C('IMG_PRE').$v1;
                    }
                }
            }
        }

        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    /**   
    * 附近搜索(景点)
    *  
    * @param string $serchName 搜索关键字
    * @param string $lon 景区横坐标  
    * @param string $lat 景区纵坐标   
    *    
    * @return json
    */
    public function searchMap(){
        $serchName = I('post.searchName');
        $lon = I('post.lon');
        $lat = I('post.lat');
        $page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 20 : I('post.size');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=SigqueryScenicSpotVoByName&lon='.$lon.'&lat='.$lat.'&serchName='.$serchName.'&page='.$page.'&size='.$size;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if($resultApi['flag']){
            foreach ($resultApi['result'] as $key => $value) {
                foreach ($value['mapBackgroudPic'] as $k => $v) {
                    $resultApi['result'][$key]['mapBackgroudPic'][$k] = C('IMG_PRE').$v;
                }
            }
        }

        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    /**   
    * 附近搜索(商家)
    *  
    * @param string $serchName 搜索关键字
    * @param string $lon 景区横坐标  
    * @param string $lat 景区纵坐标   
    *    
    * @return json
    */
    public function searchCom(){
        $serchName = I('post.searchName');
        $lon = I('post.lon');
        $lat = I('post.lat');
        $page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 20 : I('post.size');
        $type = 2;//I('post.type');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=SigqueryCommercialTenantVoByType&lon='.$lon.'&lat='.$lat.'&serchName='.$serchName;

        $apiUrl .= '&page='.$page.'&size='.$size.'&type='.$type;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if($resultApi['flag']){
            foreach ($resultApi['result'] as $key => $value) {
                foreach ($value['backPic'] as $k => $v) {
                    $resultApi['result'][$key]['backPic'][$k] = C('IMG_PRE').$v;
                }
            }
        }

        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    /**   
    * 根据商家id查询商家基础信息
    *  
    * @param string $id 类型   商家id
    *    
    * @return json
    */
    public function sellerInfo(){
        $id = I('post.id');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantVoByID&id='.$id;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        foreach ($resultApi['result']['backPic'] as $key => $value) {
            $resultApi['result']['backPic'][$key] = C('IMG_PRE').$value;
        }
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    /**   
    * 进入一个商家的详情页店家秀
    *  
    * @param string $id 类型   商家id
    *    
    * @return json
    */
    public function sellerDetail(){
    	//获取前端的数据
        $page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 20 : I('post.size');
        $id = I('post.id');

        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantAttrVo&page='.$page.'&size='.$size.'&commercialTenantID='.$id;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if($resultApi['flag'] == 1){
            foreach ($resultApi['result'] as $key => $value) {
                $resultApi['result'][$key]['picUrl'] = C('IMG_PRE').$value['picUrl'];
                $resultApi['result'][$key]['url'] = C('IMG_PRE').$value['url'];
            }
        }
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    
    /**   
    * 查询商家的优惠券
    *  
    * @param string $id 类型   商家id
    *    
    * @return json
    */
    public function sellerFree(){
        //接收前端参数
        $page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 20 : I('post.size');
        $id = I('post.id'); //商家id
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantVoucherVo&page='.$page.'&size='.$size.'&commercialTenantID='.$id;
        $resultApi = json_decode(file_get_contents($apiUrl),true);

        if($resultApi['flag'] == 1){
            foreach ($resultApi['result']['data'] as $key => $value) {
                $resultApi['result']['data'][$key]['picUrl'] = C('IMG_PRE').$resultApi['result']['data'][$key]['picUrl'];
            }
        }
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    /**   
    * 查询景区下所有景点信息
    *  
    * @param string $lon 景区横坐标  
    * @param string $lat 景区纵坐标   
    *    
    * @return json
    */
    public function queryScemap(){
        $lon = I('post.lon');
        $lat = I('post.lat');
        $page = I('post.page');
        $size = I('post.size');
        $page = empty($page)?1:$page;
        $size = empty($size)?10:$size;
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=querySceMapBylonlat&lon='.$lon.'&lat='.$lat.'&page='.$page.'&size='.$size;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if($resultApi['flag'] == 1){
            foreach ($resultApi['result'] as $key => $value) {
                $resultApi['result'][$key]['MapMark'] = C('IMG_PRE').$value['MapMark'];
            }
        }

        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$resultApi['result']);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

    /**   
    * 景点详情，图片，描述，名字传到后续页面去，只有视频信息在这里查询
    *  
    * @param string $lon 景区横坐标  
    * @param string $lat 景区纵坐标   
    *    
    * @return json
    */
    public function scemapInfo(){

        $page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 20 : I('post.size');
        $lon = I('request.lon');
        $lat = I('request.lat');
        //景点信息
        $id = I('post.id');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicMapSpotVoByID&id='.$id;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        $resultApi['result']['pageFm'] = C('IMG_PRE').$resultApi['result']['pageFm'];
        //数据返回
        if($resultApi['flag'] == 1) $mapinfo=$resultApi['result'];
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        //视频信息
        $apiUrlVideo = C('IP_BASE').'/ssh2/sceman?cmd=querySceMapVideoBylonlat&lon='.$lon.'&lat='.$lat.'&page='.$page.'&size='.$size.'&type=1&videoType=-1';
        $resultApi = json_decode(file_get_contents($apiUrlVideo),true);
        if($resultApi['flag'] == 1){
            foreach ($resultApi['result']['data'] as $key => $value) {
                $temp_video['videoType'] = $value['videoType'];
                $temp_video['videoPic'] = C('IMG_PRE').$value['videoPic'];
                $temp_video['videoPath'] = C('IMG_PRE').$value['videoPath'];
                $temp_video['hour_long'] = $value['hour_long'];
                $temp_video['reviews'] = $value['reviews'];
                $temp_video['videoName'] = $value['videoName'];
                $temp_video['wapTagName'] = $value['wapTagName'];
                $temp_video['creatDate'] = friendlyDate($value['creatDate']);
                $temp_video['watchNum'] = $value['watchNum'];
                $temp_video['id'] = $value['id'];
                $temps_video[] = $temp_video;
            }
        }
        $data['map'] = $mapinfo;
        $data['video'] = $temps_video;
        $data['num'] = $resultApi['result']['num'];
        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功',$data);
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }


    //根据商家id查询商家；
    public function SellerId(){
        //http://ip:port/ssh2/sceman?cmd=queryCommercialTenantVoByID&id
        $id = I('post.id');
        $url=C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantVoByID&id='.$id;
        $result=json_decode(file_get_contents($url),true);
        if($result['flag'] == 1){
            foreach ($result['result']['backPic'] as $key => $value) {
                $result['result']['backPic'][$key] = C('IMG_PRE').$value;
            }
        }
        $desc = file_get_contents(UPLOAD_PATH.$result['result']['url']);
        $result['result']['url'] = $desc ? $desc : '';

        $this->returnJson(1,'操作成功',$result['result']);
    }


    //根据优惠券id查询优惠券信息
    public function freeId(){
        //http://ip:port/ssh2/sceman?cmd=queryCommercialTenantVoucherVoByID&id
        $id = I('post.id');
        $url = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantVoucherVoByID&id='.$id;
        $result = json_decode(file_get_contents($url),true);
        if($result['flag'] == 1){
            $result['result']['picUrl'] = C('IMG_PRE').$result['result']['picUrl'];
            $remark = file_get_contents('upload/'.$result['result']['remark']);
            $result['result']['remark'] = $remark ? $remark : '';
            $id = $result['result']['commercialTenantID'];
            $api = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantVoByID&id='.$id;
            $result_co = json_decode(file_get_contents($api),true);
            $result['result']['commericalname'] = $result_co['result']['name'];
        }
        $this->returnJson(1,'操作成功',$result);
    }

    //商家首页
    public function selInfo(){
        //http://ip:port/ssh2/sceman?cmd= queryAllCommercialTenantVoByID&id
        $id = I('post.id');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryAllCommercialTenantVoByID&id='.$id;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if($resultApi['flag']){
            //过滤掉失效的优惠券
            foreach ($resultApi['result']['CommercialTenantVoucherVo'] as $key => $value) {
                $time = explode('~', $value['useTime']);
                $times = strtotime($time[0]);
                $timee = strtotime($time[1]);
                $now = time();
                //if($times < $now && $now < $timee)
                if($now < $timee)
                    continue;
                else
                    unset($resultApi['result']['CommercialTenantVoucherVo'][$key]);
            }
            //人均价格去整
            $resultApi['result']['averPrice'] = intval($resultApi['result']['averPrice']);
            //背景图片
            foreach ($resultApi['result']['backPic'] as $key => $value) {
                $resultApi['result']['backPic'][$key] = C('FULL_PATH').$value;
            }
            //特色产品
            foreach ($resultApi['result']['CommercialTenantProductVo'] as $key => $value) {
                foreach ($value['pic'] as $k => $v) {
                    $resultApi['result']['CommercialTenantProductVo'][$key]['pic'][$k] = C('FULL_PATH').$v;
                }
            }
            //周边好店
            foreach ($resultApi['result']['commercialTenantVo'] as $key => $value) {
                $resultApi['result']['commercialTenantVo'][$key]['backPic'] = C('FULL_PATH').$value['backPic'][0];
                //过滤掉自己
                if($value['id'] == $id) unset($resultApi['result']['commercialTenantVo'][$key]);
            }
            //店家秀信息
            $msg = file_get_contents(UPLOAD_PATH.$resultApi['result']['url']);
            $resultApi['result']['msg'] = empty($msg) ? '' : $msg;
            //地图和logo
            !empty($resultApi['result']['mapUrl']) && $resultApi['result']['mapUrl'] = C('FULL_PATH').$resultApi['result']['mapUrl'];
            !empty($resultApi['result']['logo']) && $resultApi['result']['logo'] = C('FULL_PATH').$resultApi['result']['logo'];
        }
        echo json_encode($resultApi);
    }

    //获取验证码
    public function getCode(){
        //http://ip:port/ssh2/sceman?cmd=sendmessage&tel&mius
        $tel = I('post.tel');
        empty($tel) && $this->returnJson(0,'请输入正确手机号码！');
        !isMobile($tel) && $this->returnJson(0,'请输入正确手机号码！');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=sendMessage&tel='.trim($tel).'&mius=5';
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if(!$resultApi['flag']) $this->returnJson(0,'超出一天可接受验证码的次数。更换手机号码或明天再试');
        session('orderCode',null);
        session_start();
        $orderCode = $resultApi['result'];
        $this->set('orderCode', $orderCode, 300);
        $this->returnJson(1,'成功');
    }

    //预定功能
    public function addOrder(){
        //http://ip:port/ssh2/sceman?cmd= addCommercialTenantOrderVo&assumpsitTime&CommercialTenantID&name&tel&personNum
        $time = I('post.time');
        $id = I('post.id');
        $name = I('post.name');
        $personNum = I('post.personNum');
        $tel = I('post.tel');
        $code = I('post.code');
        $realCode = $this->get('orderCode');
        if(!$realCode) $this->returnJson(0,'验证码失效，请重新请求');
        if($code != $realCode) $this->returnJson(0,'验证码不匹配');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=addCommercialTenantOrderVo';
        $apiUrl .= '&assumpsitTime='.urlencode($time).'&CommercialTenantID='.$id.'&name='.urlencode($name).'&tel='.$tel.'&personNum='.$personNum;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if($resultApi['flag']){
            //发送通知短信给用户
            //通过id查询商家的手机号码http://ip:port/ssh2/sceman?cmd=queryCommercialTenantVoByID&id
            $apiInfo = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantVoByID&id='.$id;
            $resultInfo = json_decode(file_get_contents($apiInfo),true);
            if($resultInfo['flag']){
                $sellername = $resultInfo['result']['name'];
                $sellertel = $resultInfo['result']['tel'];
                $idMsg = 129786;
                $infos = $name.'等'.$personNum.'人希望于'.$time.'光临本店';
                $msg = json_encode(array($tel,$sellername,$infos));
                $apiMsg = C('IP_BASE').'/ssh2/sceman?cmd=sendMessageByList&tel='.$sellertel.'&temID='.$idMsg.'&arrayVal='.$msg;
                $resultMsg = json_decode(file_get_contents($apiMsg),true);
            }
            //如果商家收不到短信不提示。
        }
        //数据返回
        if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
        if($resultApi['flag']) $this->returnJson(1,'发送成功');
    }

    //评论页面
    public function getCommentlist(){
        //http://ip:port/ssh2/sceman?cmd= queryCommercialTenantContentRelationVo&page&size
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantContentRelationVo&page=1&size=20';
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
        if($resultApi['flag']) $this->returnJson(1,'成功',$resultApi['result']);
    }

    //发表评论
    public function publishComment(){
        //http://ip:port/ssh2/sceman?cmd=addCommercialTenantContentVo&content&CommercialTenantID&contentXj&star
        $content = I('post.content');
        $id = I('post.id');
        $contentXj = I('post.contentXj');
        $star = I('post.star');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=addCommercialTenantContentVo';
        $apiUrl .= '&content='.$content.'&CommercialTenantID='.$id.'&contentXj='.$contentXj.'&star='.$star;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
        if($resultApi['flag']) $this->returnJson(1,'成功',$resultApi['result']);
    }

    //评论页面详细
    public function commentInfo(){
        //http://ip:port/ssh2/sceman?cmd=queryAllCommercialTenantContentVoByID&id
        $id = I('post.id');
        empty($id) && $this->returnJson(0,'参数缺失id');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryAllCommercialTenantContentVoByID&id='.$id;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        //数据返回
        if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
        if($resultApi['flag']) $this->returnJson(1,'成功',$resultApi['result']);
    }
}
