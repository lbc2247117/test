<?php

/**
 *此类用于存放官方问答的模块相关的接口
 */

namespace Home\Controller;

class QuestionController extends BaseController {

    /**   
    * 查询景区的问答
    *  
    * @param string $lon 景区横坐标  
    * @param string $lat 景区纵坐标  
    * @param string $sortType 排序类型，1：时间  2：点赞
    * @param string $serchName 搜索关键字 
    *    
    * @return json
    */
    public function selectQusetion(){
        $param = I('post.');
        extract($param);
        $is_param = 1;  //is_param代表参数是否缺失
        !empty($lon) ? $lon : $is_param = 0;
        !empty($lat) ? $lat : $is_param = 0;
        $page = empty($page) ? 1 : $page;
        $size = empty($size) ? 20 : $size;
        $sortType = empty($sortType) ? 2 : $sortType;  //1：时间  2：点赞
        $serchName = empty($serchName) ? 0 : $serchName;
        $anser=1;
        $lon = I('post.lon');
        $lat = I('post.lat');
        if ($is_param == 0) {
          $this->returnJson(0,'参数缺失');
        }
        $url = C('IP_BASE').'/ssh2/sceman?cmd=queryQuestionsAnswersVo&lon='.$lon.'&lat='.$lat.'&page='.$page.'&size='.$size.'&sortType='.$sortType.'&anser='.$anser;
        if($serchName) $url .= '&serchName='.urlencode($serchName);
        //调用接口并返回数据
        $result=json_decode(file_get_contents($url),true);
        if( $result['flag']== 1 ){
            foreach($result['result']['data']  as  $key => $val){
                $result['result']['data'][$key]['state']=0;
            }
            $data['list']=$result['result'];
            $this->returnJson(1,'操作成功',$data);
        }else{
            $this->returnJson(0,$result['result']);
        }
    }


    /**   
    * 提问（添加问答）
    *  
    * @param string $lon 景区横坐标  
    * @param string $lat 景区纵坐标  
    * @param string $userID 用户id
    * @param string $requestContent 提问问题 
    *    
    * @return json
    */
    public function addQuestion(){
        $param=I('post.');
        extract($param);
        $is_param = 1;  //is_param代表参数是否缺失
        !empty($lon) ? $lon : $is_param = 0;
        !empty($lat) ? $lat : $is_param = 0;
        !empty($userID) ? $userID : $is_param = 0;
        !empty($requestContent) ? $requestContent : $is_param = 0;
        if ($is_param == 0) {
            $this->returnJson(0,'参数缺失');
        }
        $url=C('IP_BASE').'/ssh2/sceman?cmd=addQuestionsAnswersVo&userID='.urlencode($userID).'&lat='.$lat.'&lon='.$lon.'&requestContent='.urlencode($requestContent);
        $result=file_get_contents($url);
        if((json_decode($result)->flag) == 1 ){
            $this->returnJson(1,'提交成功');
        }else{
            $this->returnJson(0,json_decode($result)->result);
        }
    }

    /**   
    * 提问点赞
    *  
    * @param string $lon 景区横坐标  
    * @param string $lat 景区纵坐标  
    * @param string $userID 用户id
    * @param string $requestContent 提问问题 
    *    
    * @return json
    */
    public function clickZambia(){
        $param = I('post.');
        extract($param);
        $is_param = 1;  //is_param代表参数是否缺失
        !empty($id) ? $id : $is_param = 0;
        !empty($mac) ? $mac : $is_param = 0;
       if ($is_param == 0) {
           $this->returnJson(0,'参数缺失');
       }
        $url = C('IP_BASE').'/ssh2/sceman?cmd=voteQuestionsAnswersVo&questid='.$id.'&mac='.$mac;
        $result = file_get_contents($url);
        if((json_decode($result)->result) == 0){
            $this->returnJson(1,'点赞成功');
        }elseif((json_decode($result)->result) == -1){
            $this->returnJson(-1,'已经点赞');
        }else{
            $this->returnJson(-2,'操作异常');
        }
    }
}
