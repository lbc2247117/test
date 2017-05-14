<?php

namespace Home\Controller;

/**
 * 此类用于存放与景区所有相关的接口
 */
class IndexController extends BaseController {

    public function index() {
        $this->display();
    }

    //验证码
    public function verify() {
        $Verify = new \Think\Verify();
        $Verify->codeSet = '0123456789';
        $Verify->fontSize = 24;
        $Verify->length = 4;
        $Verify->imageW = 160;
        $Verify->imageH = 50;
        $Verify->entry();
    }

    //点击视频增加观看数量
    public function addClick() {
        //http://ip:port/ssh2/sceman?cmd=IncreaseVideo&id&type
        $id = I('post.id');
        if (!$id)
            $this->returnJson(0, '缺少视频id');
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=IncreaseVideo&id=' . $id . '&type=5';
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1) {
            $this->returnJson(1, '成功');
        } else {
            $this->returnJson(0, $result['result']);
        }
    }

    public function liveplay() {
        $id = I('get.id', '') ? I('request.id') : $this->returnJson(0, '缺少直播ID');
        $page = 1;
        $size = 1;
        $sort = 'createTime';
        $order = 0;
        $type = 'id';
        $search = $id;
        $url = C('IP_LIVE') . '/ssh2/cloudvideo?cmd=schsce&sec=756489214459&search=' . urlencode($search) . '&type=' . $type;
        $url .= '&page=' . $page . '&size=' . $size . '&sort=' . $sort . '&order=' . $order;
        $result = json_decode(file_get_contents($url), TRUE);
        if ($result['flag'] != 1) {
            $this->returnJson(0, $result['result']);
        }
        $data['list'] = $result['result'];
        for ($i = 0; $i < count($data['list']); $i++) {
            $filePath = dirname($_SERVER['SCRIPT_FILENAME']) . '/upload/' . $data['list'][$i]['desc'];
            $data['list'][$i]['desc'] = file_get_contents($filePath);
            $data['list'][$i]['desc'] = $data['list'][$i]['desc'] ? $data['list'][$i]['desc'] : '';
            $data['list'][$i]['bakPath'] = C('FULL_PATH') . 'video/' . $data['list'][$i]['bakPath'];
            $data['list'][$i]['cover'] = C('FULL_PATH') . $data['list'][$i]['cover'];
            if ($data['list'][$i]['vstate'] == 2)
                $data['list'][$i]['liveUrl'] = $data['list'][$i]['bakPath'];
            else
                $data['list'][$i]['liveUrl'] = $data['list'][$i]['device'][0]['PullHls'];
        }
        $this->assign('data', $data['list'][0]);
        $this->display();
    }

    /**
     * 根据直播ID获取直播信息
     *
     * @param string $lon 景区经度
     * @param string $lat 景区纬度
     * @param string $id  直播ID     
     */
    public function getLiveById() {
        $id = I('request.id', '') ? I('request.id') : $this->returnJson(0, '缺少直播ID');
        $page = 1;
        $size = 1;
        $sort = 'createTime';
        $order = 0;
        $type = 'id';
        $search = $id;
        $url = C('IP_LIVE') . '/ssh2/cloudvideo?cmd=schsce&sec=756489214459&search=' . urlencode($search) . '&type=' . $type;
        $url .= '&page=' . $page . '&size=' . $size . '&sort=' . $sort . '&order=' . $order;
        $result = json_decode(file_get_contents($url), TRUE);
        if ($result['flag'] != 1) {
            $this->returnJson(0, $result['result']);
        }
        $data['list'] = $result['result'];
        for ($i = 0; $i < count($data['list']); $i++) {
            $filePath = dirname($_SERVER['SCRIPT_FILENAME']) . '/upload/' . $data['list'][$i]['desc'];
            $data['list'][$i]['desc'] = file_get_contents($filePath);
            $data['list'][$i]['desc'] = $data['list'][$i]['desc'] ? $data['list'][$i]['desc'] : '';
            $data['list'][$i]['bakPath'] = C('FULL_PATH') . 'video/' . $data['list'][$i]['bakPath'];
            $data['list'][$i]['cover'] = C('FULL_PATH') . $data['list'][$i]['cover'];
        }
        $data['count'] = 0;
        if (count($data['list']) > 0)
            $data['count'] = $data['list'][0]['count'];
        $this->returnJson(1, '成功', $data);
    }

    /**
     * 获取活动页面的移动高清直播
     * 
     * @param string $lon 景区经度
     * @param string $lat 景区纬度
     */
    public function getActLive() {
        $page = 1;
        $size = 20;
        $sort = 'createTime'; //排序字段
        $order = 0; //排序规则:0=>降序,1=>升序
        $type = 'scenicName'; //搜索类型，按照景区名称搜索
        $lon = I('request.lon');
        $lat = I('request.lat');
        $rst = $this->getSceMsg($lon, $lat);
        $search = $rst['sceName'];
        $url = C('IP_LIVE') . '/ssh2/cloudvideo?cmd=schsce&sec=756489214459&search=' . urlencode($search) . '&type=' . $type;
        $url .= '&page=' . $page . '&size=' . $size . '&sort=' . $sort . '&order=' . $order;
        $result = json_decode(file_get_contents($url), TRUE);
        if ($result['flag'] != 1) {
            $this->returnJson(0, $result['result']);
        }
        $data['list'] = $result['result'];
        for ($i = 0; $i < count($data['list']); $i++) {
            $data['list'][$i]['bakPath'] = C('FULL_PATH') . 'video/' . $data['list'][$i]['bakPath'];
            $data['list'][$i]['cover'] = C('FULL_PATH') . $data['list'][$i]['cover'];
        }
        $data['count'] = 0;
        if (count($data['list']) > 0)
            $data['count'] = $data['list'][0]['count'];
        $this->returnJson(1, '成功', $data);
    }

    /**
     * 获取景区移动高清直播
     *
     * @param string $lon    景区经度
     * @param string $lat    景区纬度
     */
    public function getHomeLive() {
        $page = 1;
        $size = 1;
        $sort = 'createTime'; //排序字段
        $order = 0; //排序规则:0=>降序,1=>升序
        $type = 'scenicName'; //搜索类型，按照景区名称搜索
        $lon = I('request.lon');
        $lat = I('request.lat');
        $rst = $this->getSceMsg($lon, $lat);
        $search = $rst['sceName'];
        $url = C('IP_LIVE') . '/ssh2/cloudvideo?cmd=schsce&sec=756489214459&search=' . urlencode($search) . '&type=' . $type;
        $url .= '&page=' . $page . '&size=' . $size . '&sort=' . $sort . '&order=' . $order;
        $result = json_decode(file_get_contents($url), TRUE);
        if ($result['flag'] != 1) {
            $this->returnJson(0, $result['result']);
        }
        $data['list'] = $result['result'];
        for ($i = 0; $i < count($data['list']); $i++) {
            $data['list'][$i]['bakPath'] = C('FULL_PATH') . 'video/' . $data['list'][$i]['bakPath'];
            $data['list'][$i]['cover'] = C('FULL_PATH') . $data['list'][$i]['cover'];
        }
        $data['count'] = 0;
        if (count($data['list']) > 0)
            $data['count'] = $data['list'][0]['count'];
        $this->returnJson(1, '成功', $data);
    }

    /**
     * 景区首页数据查询
     *  
     * @param string $lon 景区横坐标  
     * @param string $lat 景区纵坐标  
     *    
     * @return json
     */
    public function homePage() {
        //http://192.168.0.201:1234/ssh2/sceman?cmd=queryScenicSpotVoByWap&lon=102.027572&lat=29.584219
        //获取前端post的数据
        $lon = I('post.lon');
        $lat = I('post.lat');

        //根据经纬度查询地址信息
        $where_wea['Longitude'] = $lon;
        $where_wea['Latitude'] = $lat;
        $areamsg = M('weatherarea')->where($where_wea)->find();
        $cityname = $areamsg['name_cn'];
        $cityid = $areamsg['area_id'];
        //查询当天天气情况
        $todayweather = $this->todayweather($cityname,$cityid);
        /* $todayweather = array(
          "status" => 1,
          "msg" => "success",
          "errNum" => 0,
          "weather" => "小雨",
          "l_tmp" => "8",
          "h_tmp" => "15",
          "weatherPic" => '../../Public/img/weather/' . weatherPic('小雨')
          ); */

        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotVoByWap&lon=' . $lon . '&lat=' . $lat;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if ($resultApi['flag'] == 1) {
            $data['id'] = $resultApi['result']['id'];
            $data['sceName'] = $resultApi['result']['sceName'];
            $data['backgroundpic'] = C('IMG_PRE') . $resultApi['result']['backgroundpic'];
            $data['sceType'] = $resultApi['result']['sceType'];
            $data['ewmUrl'] = C('IMG_PRE') . $resultApi['result']['ewmUrl']; //二维码
            $data['sceRemark'] = $resultApi['result']['sceRemark']; //景区描述
            $data['weather'] = $todayweather;
            $data['star'] = intval($resultApi['result']['allNum']*2/ $resultApi['result']['allsize']/3)/2;
            $data['sceSynopsis'] = $resultApi['result']['sceSynopsis'];
            //景区直播
            foreach ($resultApi['result']['CameraVideoVo'] as $key => $value) {
                $temp_video['videoPic'] = C('IMG_PRE') . $value['videoPic'];
                $temp_video['videoPath'] = C('IMG_PRE') . $value['videoPath'];
                $temp_video['hour_long'] = $value['hour_long'];
                $temp_video['videoName'] = $value['videoName'];
                $temp_video['creatDate'] = friendlyDate($value['creatDate']);
                $temps_video[] = $temp_video;
            }
            $data['CameraVideoVo'] = $temps_video;
            //景区活动
            foreach ($resultApi['result']['ScenicSpotActivityVo'] as $key => $value) {
                $temp_acti['titleName'] = $value['titleName'];
                $temp_acti['name'] = $value['name'];
                $temp_acti['url'] = C('IMG_PRE') . $value['url'];
                $temp_acti['id'] = $value['id'];
                $temps_acti[] = $temp_acti;
            }
            $data['ScenicSpotActivityVo'] = $temps_acti;
            //景区攻略
            foreach ($resultApi['result']['ScenicSpotWayVo'] as $key => $value) {
                $resultApi['result']['ScenicSpotWayVo'][$key]['url'] = C('IMG_PRE') . $value['travelPic'];
            }
            $data['ScenicSpotWayVo'] = $resultApi['result']['ScenicSpotWayVo'];
            //票务
            foreach ($resultApi['result']['ScenicSpotTicketVo'] as $key => $value) {
                $temp_tic['name'] = $value['name'];
                $price = explode('.',$value['price']);
                if($price[1] == '0'){
                    $temp_tic['price'] = intval($value['price']);
                }else{
                    $temp_tic['price'] = $value['price'];
                }
                $temp_tic['pic'] = C('FULL_PATH') . $value['pic'];
                $temp_tic['remark'] = $value['remark'];
                $temp_tic['id'] = $value['id'];
                $temps_tic[] = $temp_tic;
            }
            $data['ScenicSpotTicketVo'] = $temps_tic;
        }

        if ($resultApi['flag'] == 1)
            $this->returnJson(1, '成功', $data);
        else if ($resultApi['flag'] == 0)
            $this->returnJson(0, $resultApi['result']);
        else
            $this->returnJson(0, '网络异常，稍后再试！');
    }

    //今日天气
    public function todayweather($cityname, $cityid){
        //http://192.168.0.111:2222/ssh3/userinfo?cmd=getWeather&cityname=&cityid=
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=getWeather&cityname='.$cityname.'&cityid='.$cityid;
        $result = json_decode(file_get_contents($apiUrl),true);
        if($result['errNum']){
            $result = json_decode(file_get_contents($apiUrl),true);
        }
        if(!$result['errNum']){
            $data['status'] = 1;
            $data['msg'] = $result['errMsg'];
            $data['errNum'] = $result['errNum'];
            $data['weather'] = $result['retData']['today']['type'];
            $data['weatherPic'] = '../../Public/img/weather/'.weatherPic($result['retData']['today']['type']);
            $data['l_tmp'] = str_replace('℃', '', $result['retData']['today']['lowtemp']);
            $data['h_tmp'] = str_replace('℃', '', $result['retData']['today']['hightemp']);
        }else{
            $data['status'] = 0;
            $data['msg'] = '请求天气失败。';
        }
        return $data;
    }

    /**
     * 景区简介
     *  
     * @param string $lon 景区横坐标  
     * @param string $lat 景区纵坐标  
     *    
     * @return json
     */
    public function allsceRemark() {
        $id = I('post.id');
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotVoByID&id=' . $id;
        //调用接口并返回数据
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1) {
            $result['result']['backgroundpic'] = C('FULL_PATH') . $result['result']['backgroundpic'];
            foreach ($result['result']['ScenicSpotProgramVo'] as $key => $value) {
                $result['result']['ScenicSpotProgramVo'][$key]['videoPic'] = C('FULL_PATH') . $value['videoPic'];
                $result['result']['ScenicSpotProgramVo'][$key]['videoUrl'] = C('FULL_PATH') . $value['videoUrl'];
                if ($value['remark'] == '-1')
                    $result['result']['ScenicSpotProgramVo'][$key]['remark'] = '';
            }
            $result['result']['guidePic'] = empty($result['result']['guidePic']) ? '' : C('FULL_PATH') . $result['result']['guidePic'];
            $result['result']['specVideoPic'] = C('FULL_PATH') . $result['result']['specVideoPic'];
            $result['result']['specVideoUrl'] = C('FULL_PATH') . $result['result']['specVideoUrl'];
            $result['result']['sceRemarkVideoUrl'] = C('FULL_PATH') . $result['result']['sceRemarkVideoUrl'];
            $result['result']['sceRemarkVideoPic'] = C('FULL_PATH') . $result['result']['sceRemarkVideoPic'];
            if ($result['result']['carefulContent'] == '-1')
                $result['result']['carefulContent'] = '';
            $this->returnJson(1, '操作成功', $result['result']);
        } else {
            $this->returnJson(0, json_decode($result)->result);
        }
    }

    /**
     * 查询景区下的活动
     *  
     * @param string $lon 景区横坐标  
     * @param string $lat 景区纵坐标  
     *    
     * @return json
     */
    public function selectActivity() {
        //获取前端post数据
        $param = I('post.');
        extract($param);
        /* $lon = '109.770938';//I('post.lon');
          $lat = '18.319929';//I('post.lat'); */
        $is_param = 1;  //is_param代表参数是否缺失
        !empty($lon) ? $lon : $is_param = 0;
        !empty($lat) ? $lat : $is_param = 0;
        $page = empty($page) ? 1 : $page;
        $size = empty($size) ? 20 : $size;
        if ($is_param == 0) {
            $this->returnJson(0, '参数缺失');
        }
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotActivityVo&page=' . $page . '&size=' . $size . '&lon=' . $lon . '&lat=' . $lat . '&state=0';

        //调用接口并返回数据
        $result = file_get_contents($url);
        if ((json_decode($result)->flag) == 1) {
            $data = json_decode($result)->result;
            foreach ($data->data as $key => $val) {
                $val->url = C('IMG_PRE') . $val->url;
                //$val->content = file_get_contents(C('IMG_PRE').$val->content);
            }
            $this->returnJson(1, '操作成功', $data);
        } else {
            $this->returnJson(0, json_decode($result)->result);
        }
    }

    /**
     * 根据活动id查询活动的详情
     *  
     * @param string $id 活动的id 
     *    
     * @return json
     */
    public function activityInfo() {
        $id = I('post.id');

        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotActivityVoByID&id=' . $id;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if ($resultApi['flag']) {
            $resultApi['result']['url'] = C('IMG_PRE') . $resultApi['result']['url'];
            $desc = file_get_contents('upload/' . $resultApi['result']['content']);
            $resultApi['result']['content'] = $desc ? $desc : '';
        }

        if ($resultApi['flag'] == 1)
            $this->returnJson(1, '成功', $resultApi['result']);
        else if ($resultApi['flag'] == 0)
            $this->returnJson(0, $resultApi['result']);
        else
            $this->returnJson(0, '网络异常，稍后再试！');
    }

    /**
     * 景区摇一摇页面数据
     *  
     * @param string $id 景区的id 
     *    
     * @return json
     */
    public function nodding() {
        //获取前端数据
        $id = I('post.id'); //景区的id
        $page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 20 : I('post.size');
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryNoddingActionVo&page=' . $page . '&size=' . $size . '&commercialTenantID=' . $id;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if ($resultApi['flag'] == 1) {
            foreach ($resultApi['result'] as $key => $value) {
                $resultApi['result'][$key]['url'] = C('IMG_PRE') . $value['url'];
            }
        }

        if ($resultApi['flag'] == 1)
            $this->returnJson(1, '成功', $resultApi['result']);
        else if ($resultApi['flag'] == 0)
            $this->returnJson(0, $resultApi['result']);
        else
            $this->returnJson(0, '网络异常，稍后再试！');
    }

    /**
     * 摇一摇后的数据返回
     *  
     * @param string $lon 景区横坐标  
     * @param string $lat 景区纵坐标   
     *    
     * @return json
     */
    public function noddingRes() {
        $lon = I('post.lon');
        $lat = I('post.lat');

        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantVoucherVo&page=1&size=2000&lon=' . $lon . '&lat=' . $lat;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if ($resultApi['flag'] == 1) {
            $count = count($resultApi['result']['data']);
            if ($count < 1)
                $this->returnJson(0, '你没有抽中优惠券！');
            $key = rand(1, $count);
            $data = $resultApi['result']['data'][$key - 1];
            if (empty($data))
                $this->returnJson(0, '对不起，你没有抽中优惠券！');
            if ($data) {
                $id = $data['commercialTenantID'];
                $api = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantVoByID&id=' . $id;
                $result = json_decode(file_get_contents($api), true);
                foreach ($result['result']['backPic'] as $key => $value) {
                    $result['result']['backPic'][$key] = C('IMG_PRE') . $value;
                }
            }
            $data['picUrl'] = C('IMG_PRE') . $data['picUrl'];
            $data['seller'] = $result['result'];
        }
        if ($resultApi['flag'] == 1)
            $this->returnJson(1, '成功', $data);
    }

    /**
     * 查询今天及未来四天的天气情况
     *  
     * @param string $lon 景区横坐标  
     * @param string $lat 景区纵坐标   
     *    
     * @return json
     */
    public function weatherDesc() {
        //echo json_encode(weatherInfo());exit;
        $lon = I('post.lon');
        $lat = I('post.lat');
        //根据经纬度查询地址信息
        $where_wea['Longitude'] = $lon;
        $where_wea['Latitude'] = $lat;
        $areamsg = M('weatherarea')->where($where_wea)->find();
        $cityname = $areamsg['name_cn'];
        $cityid = $areamsg['area_id'];
        $data = $this->weatherFor($cityname, $cityid);
        $data['today']['curTemp'] = str_replace('℃', '', $data['today']['curTemp']);
        $this->returnJson(1, '成功', $data);
    }

    //未来四天天气
    private function weatherFor($cityname, $cityid){
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=getWeather&cityname='.$cityname.'&cityid='.$cityid;
        $result = json_decode(file_get_contents($apiUrl),true);
        if($result['errNum']){
            $result = json_decode(file_get_contents($apiUrl),true);
        }
        if(!empty($result)){
            $data['status'] = 1;
            $data['msg'] = $result['errMsg'];
            $data['errNum'] = $result['errNum'];
            $data['today'] = $result['retData']['today'];
            $data['today']['cy'] = $result['retData']['today']['index'][2];
            $data['today']['weatherPic'] = '../../Public/img/weather/'.weatherPic($result['retData']['today']['type']);
            //处理天气图片
            unset($data['today']['index']);
            foreach ($result['retData']['forecast'] as $key => $value) {
                $temp = $value['type'];
                $result['retData']['forecast'][$key]['weatherPic'] = '../../Public/img/weather/'.weatherPic($temp);
            }
            //处理未来四天的时间格式       
            /*foreach ($result['retData']['forecast'] as $key => $value) {
                $tmep_date = explode('-', $value['date']);
                $result['retData']['forecast'][$key]['date'] = $tmep_date[1].'/'.$tmep_date[2];
            }*/
            $data['forecast'] = $result['retData']['forecast'];
        }else{
            $data['status'] = 0;
            $data['msg'] = '请求天气失败。';
        }
        return $data;
    }

    /**
     * 游客预约移动高清直播
     *
     * @param string $id 直播ID
     * @param string $mobile 手机号
     */
    public function sendMobileMsg() {
        $verify = I('verify','','strtolower');
        //验证验证码是否正确
        if(!($this->check_verify($verify))){
            $this->returnJson(0,'验证码错误');
        }
        $id = I('post.id', '') ? I('post.id') : $this->returnJson(0, '缺少直播ID');
        $mobile = I('post.mobile', '', MOBILE) ? I('post.mobile') : $this->returnJson(0, '请填写正确的手机号码');
        $url = C('IP_LIVE') . '/ssh2/cloudvideo?cmd=addsms&tel='.$mobile . '&cid=' . $id;
        $result = json_decode(file_get_contents($url), TRUE);
        if ($result['flag'] != '1')
            $this->returnJson(0, $result['result']);
        $this->returnJson(1, '设置短信提醒成功!');
    }

    //验证验证码
    protected function check_verify($code){
        $verify = new \Think\Verify();
        return $verify->check($code);
    }
}
