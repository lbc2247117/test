<?php
namespace Admin\Controller;
use Think\Controller;
/**
 * 问答类
 *
 * @copyright (c) 2016, 云道
 * @version 2016-09-22
 */

class QuestionController extends BaseController{
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
    public function selectQuse(){
        $param = I('post.');
        extract($param);
        $is_param = 1;  //is_param代表参数是否缺失
        $page = empty($page) ? 1 : $page;
        $size = empty($size) ? 20 : $size;
        $sortType = empty($sortType) ? 2 : $sortType;  //1：时间  2：点赞
        $serchName = empty($serchName) ? 0 : $serchName;
        $anser = empty($anser) ? 0 : $anser; //0:全部 1：回答 2：未回答
        $url = C('IP_BASE').'/ssh2/sceman?cmd=queryQuestionsAnswersVo&lon='.$this->lon.'&lat='.$this->lat.'&page='.$page.'&size='.$size.'&sortType='.$sortType.'&anser='.$anser;
        if($serchName) $url .= '&serchName='.urlencode($serchName);
        //调用接口并返回数据
        $resultApi = json_decode(file_get_contents($url),true);
        //数据返回
        if($resultApi['flag'] == 1) {
            $this->returnJson(1,'成功',$resultApi['result']);
        }
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }

	/**   
    * 删除一条的问答
    *  
    * @param array $id 数组id  
    *    
    * @return json
    */
    public function delQues(){
        $id = I('post.id');
        if(empty($id)) $this->returnJson(0,'参数缺失');
        foreach ($id as $key => $value) {
            $url = C('IP_BASE').'/ssh2/sceman?cmd=delQuestionsAnswersVo&id='.$value;
            $result = json_decode(file_get_contents($url),true);
            if($result['flag'] == 1) continue;
            else $this->returnJson(0,'删除失败，请稍后再试');
        }
        $this->returnJson(1,'删除成功');
    }

    /**   
    * 回答问题
    *  
    * @param array $id 数组id  
    *    
    * @return json
    */
    public function ansQues(){
        $name = session('sce_username');
        $param=I('post.');
        extract($param);
        $is_param = 1;  //is_param代表参数是否缺失
        !empty($requestRepeat) ? $requestRepeat : $is_param = 0;
        !empty($id) ? $id : $is_param = 0;

        if ($is_param == 0) $this->returnJson(0,'参数缺失');
        $requestRepeat= urlencode($requestRepeat);
        $id= urlencode($id);
        $url = C('IP_BASE').'/ssh2/sceman?cmd=modifyQuestionsAnswersVo&userID='.$name.'&lon='.$this->lon.'&vote='.$vote.'&lat='.$this->lat.'&id='.$id.'&requestRepeat='.$requestRepeat;


        $resultApi = json_decode(file_get_contents($url),true);

        //数据返回
        if($resultApi['flag'] == 1) $this->returnJson(1,'成功');
        else if($resultApi['flag'] == 0) $this->returnJson(0,$resultApi['result']);
        else $this->returnJson(0,'网络异常，稍后再试！');
    }
}
    