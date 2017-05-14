<?php
namespace Home\Controller;

class ScoreController extends  BaseController{

    //查询所有的评分关系
    public  function selectScore(){
        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotContentRelationVo&page&size
        $page = I('post.page');
        $size = I('post.size');
        $page = empty($page) ? 1 : $page;
        $size = empty($size) ? 20 : $size;
        $url=C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotContentRelationVo&page='.$page.'&size'.$size;
        $result=json_decode(file_get_contents($url),true);
        if($result['flag']==1){
            $this->returnJson(1,'成功',$result['result']);
        }else{
            $this->returnJson(0,$result['result']);
        }
    }

    //新增游客评分
    public  function addScore(){
        //http://ip:port/ssh2/sceman?cmd=addScenicSpotContentVo&fwScore&mjScore&contentXj&Score&ScenicSpotID&serverScore&zdContent
        $param=I('post.');
        extract($param);
        $is_param=1;
        $lon = I('request.lon', '') ? I('request.lon') : $this->returnJson(0, '缺少景区经度');
        $lat = I('request.lat', '') ? I('request.lat') : $this->returnJson(0, '缺少景区维度');
        $ScenicSpotID = $this->selectId($lon, $lat);
        !empty($fwScore) ? $fwScore : $this->returnJson(0, '缺少氛围评分');
        !empty($serverScore) ? $serverScore : $this->returnJson(0, '缺少服务评分');
        !empty($mjScore) ? $mjScore : $this->returnJson(0, '缺少美景评分');
        !empty($contentXj) ? $contentXj : $this->returnJson(0, '请选择下列标签');
        !empty($Score) ? $Score : $this->returnJson(0, '缺少总分评分');
        !empty($zdContent) ? $zdContent : $this->returnJson(0, '缺少总分评价');
        $url=C('IP_BASE').'/ssh2/sceman?cmd=addScenicSpotContentVo&fwScore='.$fwScore.'&mjScore='.$mjScore.'&contentXj='.$zdContent.'&Score='.$Score.'&ScenicSpotID='.$ScenicSpotID.'&serverScore='.$serverScore.'&zdContent='.$contentXj;
        $result=json_decode(file_get_contents($url),true);
        if($result['flag']==1){
            $this->returnJson(1,'评分成功');
        }else{
            $this->returnJson(0,$result['result']);
        }
    }

    //查询票务列表
    public function  selectTicket(){
        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotTicketVo&ScenicSpotID&page&size
        $page = I('post.page');
        $size = I('post.size');
        $name = I('post.name');
        $page = empty($page) ? 1 : $page;
        $size = empty($size) ? 20 : $size;
        $lon = I('request.lon', '') ? I('request.lon') : $this->returnJson(0, '缺少景区经度');
        $lat = I('request.lat', '') ? I('request.lat') : $this->returnJson(0, '缺少景区维度');
        $ScenicSpotID =$this->selectId($lon, $lat);
        $url=C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotTicketVo&page='.$page.'&size='.$size.'&ScenicSpotID='.$ScenicSpotID.'&name='.$name.'&state=1';
        $result=json_decode(file_get_contents($url),true);
        if ($result['flag'] == 1){
            foreach($result['result']['data'] as  $key=>$val){
                $result['result']['data'][$key]['pic']=C('FULL_PATH').$result['result']['data'][$key]['pic'];
            }
            $this->returnJson(1, '成功', $result['result']);
        }
        else
            $this->returnJson(0, $result['result']);
    }

    //通过票务id查询票务
    public  function  selectTicketById(){
        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotTicketVoByID&id
        $id=I('post.id');
        if(!$id){
            $this->returnJson(0, '缺少票务id');
        }
        $url=C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotTicketVoByID&id='.$id;
        $result=json_decode(file_get_contents($url),true);
        if ($result['flag'] == 1){
            $result['result']['pic']=C('FULL_PATH').$result['result']['pic'];
            $price = explode('.',$result['result']['price']);
            if($price[1] == '0'){
                $result['result']['price'] = intval($result['result']['price']);
            }else{
                $result['result']['price'] = $value['price'];
            }
            $this->returnJson(1, '成功成功',$result['result']);
        }
        else
            $this->returnJson(0, $result['result']);
    }


    //游客点评页面，查询游客的评分
    public  function  selectScorelist(){
        //http://ip:port/ssh2/sceman?cmd=queryScenicSpotVoInfor&ScenicSpotVoID
        $lon = I('request.lon', '') ? I('request.lon') : $this->returnJson(0, '缺少景区经度');
        $lat = I('request.lat', '') ? I('request.lat') : $this->returnJson(0, '缺少景区维度');
        $ScenicSpotID =$this->selectId($lon, $lat);
        $url = C('IP_BASE').'/ssh2/sceman?cmd=queryScenicSpotVoInfor&ScenicSpotVoID='.$ScenicSpotID;
        $result=json_decode(file_get_contents($url),true);
        if($result['flag']==1){
            $result['result']['allscore']=intval($result['result']['allscore']*2/$result['result']['allNum']/3 );
            $result['result']['fw']=intval($result['result']['fw']*2/$result['result']['allNum']);
            $result['result']['server']=intval($result['result']['server']*2/$result['result']['allNum']);
            $result['result']['mj']=intval($result['result']['mj']*2/$result['result']['allNum']);
            $this->returnJson(1,'成功',$result['result']);
        }else{
            $this->returnJson(0,$result['result']);
        }
    }

    //增加活动点击量，分享量
    public function addClick(){
        //http://ip:port/ssh2/sceman?cmd=IncScenicSpotActivityVoByID&id&type&score
        $id = I('post.id', '') ? I('post.id') : $this->returnJson(0, '缺少活动id');
        $type = I('post.type', '') ? I('post.type') : 0;  //0分享  1查看
        $url = C('IP_BASE').'/ssh2/sceman?cmd=IncScenicSpotActivityVoByID&id='.$id.'&type='.$type.'&score=1';
        $result=json_decode(file_get_contents($url),true);
        if($result['flag']==1){
            $this->returnJson(1,'成功',$result['result']);
        }else{
            $this->returnJson(0,$result['result']);
        }
    }

    //票务点击和购买次数增加
    public  function addTicket(){
        //http://ip:port/ssh2/sceman?cmd=modifyScenicSpotTicketVoInforByID&id&type&score
        $id = I('post.id', '') ? I('post.id') : $this->returnJson(0, '缺少票务id');
        $type = I('post.type', '') ? I('post.type') : 0;    //0:点击 1：购买
        $url = C('IP_BASE').'/ssh2/sceman?cmd=modifyScenicSpotTicketVoInforByID&id='.$id.'&type='.$type.'&score=1';
        $result=json_decode(file_get_contents($url),true);
        if($result['flag']==1){
            $this->returnJson(1,'成功',$result['result']);
        }else{
            $this->returnJson(0,$result['result']);
        }
    }
}