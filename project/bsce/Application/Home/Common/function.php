<?php
    /**   
    * 根据经纬度，确定城市
    *  
    * @param string $lon 横坐标  
    * @param string $lat 纵坐标  
    *    
    * @return array
    */
    function areaInfo($lon = '116.322987',$lat = '39.983424'){
    	header('Content-type:text/html;charset=UTF-8');
    	$params = array(
			'coordtype' => 'wgs84ll',
			'location' => $lat . ',' . $lon,
			'ak' => '48iXIUUF5vXrjjmDFX3pcYwKvm6L0Mm8',
			'output' => 'json',
			'pois' => 0
		);
		$resp = async('http://api.map.baidu.com/geocoder/v2/', $params, false);
		$data = json_decode($resp, true);
		if ($data['status'] != 0){
			throw new Exception($data['message']);
		}
		$area =  array(
			'address' => $data['result']['formatted_address'],
			'province' => $data['result']['addressComponent']['province'],
			'city' => $data['result']['addressComponent']['city'],
			'street' => $data['result']['addressComponent']['street'],
			'street_number' => $data['result']['addressComponent']['street_number'],
			'city_code'=>$data['result']['cityCode'],
			'lng'=>$data['result']['location']['lng'],
			'lat'=>$data['result']['location']['lat']
		);
		return $area;
    }

    /**   
    * 确定城市
    *    
    * @return array
    */
	function async($url, $params = array(), $encode = true, $method = 1){
		$ch = curl_init();
		if ($method == 1){
			$url = $url . '?' . http_build_query($params);
			$url = $encode ? $url : urldecode($url);
			curl_setopt($ch, CURLOPT_URL, $url);
		}else{
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}
		curl_setopt($ch, CURLOPT_REFERER, '百度地图referer');
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_0 like Mac OS X; en-us) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11A465 Safari/9537.53');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$resp = curl_exec($ch);
		curl_close($ch);
		return $resp;
	}

    /**   
    * 查询当天的天气
    *  
    * @param string $cityid 城市id    
    *    
    * @return array
    */
	function todayweather($cityname = '朝阳', $cityid = '101010100'){
		header('Content-type:text/html;charset=UTF-8');
		/*$ch = curl_init();
		$url = 'http://apis.baidu.com/apistore/weatherservice/cityid?cityid='.$cityid;
		$header = array(
			'apikey: f50fc0965b45fa4fd8674844109a99ca',
		);
		// 添加apikey到header
		curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// 执行HTTP请求
		curl_setopt($ch , CURLOPT_URL , $url);
		$res = curl_exec($ch);
        //处理数据并返回
	    $result = json_decode($res,true);
	    if(!empty($result)){
	    	$data['status'] = 1;
	    	$data['msg'] = $result['errMsg'];
	    	$data['errNum'] = $result['errNum'];
	    	$data['weather'] = $result['retData']['weather'];
	    	$data['weatherPic'] = '../../Public/img/weather/'.weatherPic($result['retData']['weather']);
	    	$data['l_tmp'] = $result['retData']['l_tmp'];
	    	$data['h_tmp'] = $result['retData']['h_tmp'];
	    }else{
	    	$data['status'] = 0;
	    	$data['msg'] = '请求天气失败。';
	    }*/
        $ch = curl_init();
        $url = 'http://apis.baidu.com/apistore/weatherservice/recentweathers?cityname='.$cityname.'&cityid='.$cityid;

        $header = array(
            'apikey: f50fc0965b45fa4fd8674844109a99ca',
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        $result = json_decode($res,true);
        if(!empty($result)){
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
    * 当天天气，未来4天天气
    *  
    * @param string $cityid 城市id    
    * @param string $cityname 城市名
    *    
    * @return array
    */
	function weatherFor($cityname = '朝阳',$cityid='101010100'){
		header('Content-type:text/html;charset=UTF-8');
		$ch = curl_init();
	    $url = 'http://apis.baidu.com/apistore/weatherservice/recentweathers?cityname='.$cityname.'&cityid='.$cityid;

	    $header = array(
	        'apikey: f50fc0965b45fa4fd8674844109a99ca',
	    );
	    // 添加apikey到header
	    curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    // 执行HTTP请求
	    curl_setopt($ch , CURLOPT_URL , $url);
	    $res = curl_exec($ch);
	    $result = json_decode($res,true);
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
     * 友好的时间显示
     *
     * @param int    $sTime 待显示的时间
     * @return string
     */
     function friendlyDate($sTime) {
        if (!$sTime)
            return '';
        //sTime=源时间，cTime=当前时间，dTime=时间差
        $sTime = strtotime($sTime);
        $cTime      =   time();
        $dTime      =   $cTime - $sTime;
        $dDay       =   intval(date("z",$cTime)) - intval(date("z",$sTime));
        //$dDay     =   intval($dTime/3600/24);
        $dYear      =   intval(date("Y",$cTime)) - intval(date("Y",$sTime));
        if( $dTime < 60 ){
            if($dTime < 10){
                return '刚刚';    //by yangjs
            }else{
                return intval(floor($dTime / 10) * 10)."秒前";
            }
        }elseif( $dTime < 3600 ){
            return intval($dTime/60)."分钟前";
        }elseif( $dTime >= 3600 && $dDay == 0  ){
            return intval($dTime/3600)."小时前";
        }elseif( $dDay > 0 && $dDay<=7 ){
            return intval($dDay)."天前";
        }elseif( $dDay > 7 &&  $dDay <= 30 ){
            return intval($dDay/7) . '周前';
        }elseif( $dDay > 30 && $dDay <= 90){
            return intval($dDay/30) . '个月前';
        }else{
            return date("Y-m-d",$sTime);
        }
    }

    /**
     * 天气图片选择
     *
     * @param int $weather 天气的中文显示
     *
     * @return string
     */
    function weatherPic($weather = '晴'){
    	switch ($weather) {
    		case '晴':
    			$img = '00';break;
    		case '多云':
    			$img = '01';break;
    		case '阴':
    			$img = '02';break;
    		case '阵雨':
    			$img = '03';break;
    		case '雷阵雨':
    			$img = '04';break;
    		case '雷阵雨伴有冰雹':
    			$img = '05';break;
    		case '雨夹雪':
    			$img = '06';break;
    		case '小雨':
    			$img = '07';break;
    		case '中雨':
    			$img = '08';break;
    		case '大雨':
    			$img = '09';break;
    		case '暴雨':
    			$img = '10';break;
    		case '大暴雨':
    			$img = '10';break;
    		case '特大暴雨':
    			$img = '10';break;
    		case '阵雪':
    			$img = '13';break;
    		case '小雪':
    			$img = '14';break;
    		case '中雪':
    			$img = '15';break;
    		case '大雪':
    			$img = '16';break;
    		case '暴雪':
    			$img = '17';break;
    		case '雾':
    			$img = '18';break;
    		case '冻雨':
    			$img = '19';break;
    		case '沙尘暴':
    			$img = '20';break;
    		case '小到中雨':
    			$img = '07';break;
    		case '中到大雨':
    			$img = '08';break;
    		case '大到暴雨':
    			$img = '09';break;
    		case '暴雨到大暴雨':
    			$img = '10';break;
    		case '大暴雨到特大暴雨':
    			$img = '14';break;
    		case '小到中雪':
    			$img = '15';break;
    		case '中到大雪':
    			$img = '16';break;
    		case '大到暴雪':
    			$img = '17';break;
    		case '浮尘':
    			$img = '30';break;
    		case '扬沙':
    			$img = '30';break;
    		case '强沙尘暴':
    			$img = '300';break;
    		case '霾':
    			$img = '53';break;
    		default:
    			$img = 'undefined';break;
    	}
    	return $img.'.png';
    }

    function weatherInfo(){
        $one = array(
            "date"=> date("Y-m-d",strtotime("+1 day")),
            "week"=> "星期五",
            "fengxiang"=> "无持续风向",
            "fengli"=> "微风级",
            "hightemp"=> "12℃",
            "lowtemp"=> "8℃",
            "type"=> "小雨",
            "weatherPic"=>'../../Public/img/weather/'.weatherPic('小雨')
        );
        $two = array(
            "date"=> date("Y-m-d",strtotime("+2 day")),
            "week"=> "星期六",
            "fengxiang"=> "无持续风向",
            "fengli"=> "微风级",
            "hightemp"=> "15℃",
            "lowtemp"=> "6℃",
            "type"=> "小雨",
            "weatherPic"=>'../../Public/img/weather/'.weatherPic('小雨')
        );
        $three = array(
            "date"=> date("Y-m-d",strtotime("+3 day")),
            "week"=> "星期天",
            "fengxiang"=> "无持续风向",
            "fengli"=> "微风级",
            "hightemp"=> "15℃",
            "lowtemp"=> "7℃",
            "type"=> "小雨",
            "weatherPic"=>'../../Public/img/weather/'.weatherPic('小雨')
        );
        $four = array(
            "date"=> date("Y-m-d",strtotime("+4 day")),
            "week"=> "星期一",
            "fengxiang"=> "无持续风向",
            "fengli"=> "微风级",
            "hightemp"=> "10℃",
            "lowtemp"=> "8℃",
            "type"=> "小雨",
            "weatherPic"=>'../../Public/img/weather/'.weatherPic('小雨')
        );
        $infowea = array(
            'status'=>1,
            'msg'=>'成功',
            'data'=>array(
                "status"=>1,
                "msg"=>"success",
                "errNum"=> 0,        //0-有数据，-1没数据。
                "today"=>array(
                    "date"=>date("Y-m-d"),
                    "week"=>"星期四",
                    "curTemp"=> "10",
                    "aqi"=>null,
                    "fengxiang"=> "无持续风向",
                    "fengli"=> "微风级",
                    "hightemp"=> "12℃",
                    "lowtemp"=> "8℃",
                    "type"=> "小雨",
                    "cy"=>array(
                        'details'=>'外套'
                    ),
                    'weatherPic'=>'../../Public/img/weather/'.weatherPic('小雨')
                )
            )
        );
        $infowea['data']['forecast'][] = $one;
        $infowea['data']['forecast'][] = $two;
        $infowea['data']['forecast'][] = $three;
        $infowea['data']['forecast'][] = $four;
        return $infowea;
    }
?>