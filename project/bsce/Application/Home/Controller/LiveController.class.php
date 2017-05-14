<?php

/**
 * 此类用于存放景区美景直播模块相关的接口
 */

namespace Home\Controller;

//定义本类使用的全局宏
defined('UPLOAD_PATH') or define('UPLOAD_PATH', 'upload/');        //上传资源的路径
defined('SELECT_VIDEO') or define('SELECT_VIDEO', '/ssh2/sceman?cmd=querySceMapVideoBylonlat&lon='); //查询景区景点的视频信息
defined('SELECT_PICTURE') or define('SELECT_PICTURE', '/ssh2/sceman?cmd=querySceMapPicBylonlat&lon='); //查询景区景点的视频信息

class LiveController extends BaseController {

    /**
     * 查询景区or景点的视频
     *  
     * @param int $type 0 为景区 1为景点 
     * @param string $lon 景区横坐标  
     * @param string $lat 景区纵坐标   
     *    
     * @return json
     */
    public function selectVideo() {
        $param = I('post.');
        extract($param);
        $is_param = 1;  //is_param代表参数是否缺失
        !empty($lon) ? $lon : $is_param = 0;
        !empty($lat) ? $lat : $is_param = 0;
        $page = empty($page) ? 1 : $page;
        $size = empty($size) ? 4 : $size;
        $type = empty($type) ? 0 : $type; //0 为景区 1为景点
        $videoType = empty($videoType) ? -1 : $videoType; //-1：所有视频 1：直播  7：一般官方视频
        $waptag = empty($waptag) ? '' : $waptag; //视频标签
        if ($waptag == -1)
            $waptag = '';
        if ($is_param == 0) {
            $this->returnJson(0, '参数缺失');
        }
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=querySceMapVideoBylonlat&lon=' . $lon . '&lat=' . $lat . '&page=' . $page . '&size=' . $size . '&type=' . $type . '&videoType=' . $videoType . '&serchName=&waptag=' . $waptag;
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1) {
            foreach ($result['result']['data'] as $key => $val) {
                $result['result']['data'][$key]['videoPic'] = PRO_PATH . UPLOAD_PATH . $result['result']['data'][$key]['videoPic'];
                $result['result']['data'][$key]['videoPath'] = PRO_PATH . UPLOAD_PATH . $result['result']['data'][$key]['videoPath'];
                if ($result['result']['data'][$key]['audioUrl'] != "0" && $result['result']['data'][$key]['audioUrl'] != "1") {
                    $result['result']['data'][$key]['audioUrl'] = C('FULL_PATH') . $result['result']['data'][$key]['audioUrl'];
                }
                foreach ($result['result']['data'][$key]['audioUrls'] as $k => $val) {
                    $result['result']['data'][$key]['audioUrls'][$k] = C('FULL_PATH') .'audio/'. $result['result']['data'][$key]['audioUrls'][$k];
                }
            }
            $this->returnJson(1, '操作成功', $result['result']);
        } else
            $this->returnJson(0, $result['result']);
    }

    /**
     * 查询景区or景点的图片
     *  
     * @param int $type 0 为景区 1为景点 
     * @param string $lon 景区横坐标  
     * @param string $lat 景区纵坐标   
     *    
     * @return json
     */
    public function selectPicture() {
        $param = I('post.');
        extract($param);
        /* $lon = 116.477651;
          $lat = 39.994925; */
        $is_param = 1;  //is_param代表参数是否缺失
        !empty($lon) ? $lon : $is_param = 0;
        !empty($lat) ? $lat : $is_param = 0;
        $page = empty($page) ? 1 : $page;
        $size = empty($size) ? 20 : $size;
        $type = empty($type) ? 0 : $type; //0 为景区 1为景点
        if ($is_param == 0) {
            $this->returnJson(0, '参数缺失');
        }
        $this->_mvokeJava($lon, $lat, $page, $size, $type, SELECT_PICTURE, 'picPath', '');
    }

    /**
     * 私有函数，调用接口并返回数据
     *
     * @return json
     */
    private function _mvokeJava($lon, $lat, $page, $size, $type, $cmd, $picture, $video = '', $videoType = '', $videoPath, $waptag = '', $waptagpic) {
        $url = C('IP_BASE') . $cmd . $lon . '&lat=' . $lat . '&page=' . $page . '&size=' . $size . '&type=' . $type . $video . $videoType . $waptag . $waptagpic;
        $result = file_get_contents($url);
        if ((json_decode($result)->flag) == 1) {
            $data['count'] = (json_decode($result)->result->num);
            $data['list'] = (json_decode($result)->result->data);
            foreach ($data['list'] as $key => $val) {
                $val->$picture = C('IMG_PRE') . $val->$picture;
                if ($videoPath != '') {
                    $val->$videoPath = C('IMG_PRE') . $val->$videoPath;
                }
            }
            $this->returnJson(1, '操作成功', $data);
        } else {
            $this->returnJson(0, json_decode($result)->result);
        }
    }

    //景区视频 ----- 视频标签
    public function selectTag() {
        //http://ip:port/ssh2/sceman?cmd=queryCameraTag&page&size

        $url = C('IP_BASE') . '/ssh2/sceman?cmd=queryCameraTag&page=1&size=20';
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1) {
            $this->returnJson(1, '成功', $result['result']);
        } else {
            $this->returnJson(1, $result['result']);
        }
    }

}
