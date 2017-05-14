<?php

/**
 * 控制器基类
 *
 * @author LiuBoCheng
 * @copyright (c) 2016, 云道
 * @version 2016-08-23
 */

namespace Home\Controller;

use Think\Controller;

class BaseController extends Controller {

    /**
     * 给前端返回JSON字符串
     * 
     * @param int $status 状态码，1表示成功，非1表示失败
     * @param string $msg 提示信息
     * @param array $data 数据
     * 
     * @return string 如:{'status':1,'msg':'成功','data':{'count':150,'list':[{'id':1,'name':'li'},{'id':2,'name':'liu'}]}}
     */
    protected function returnJson($status = 0, $msg = '', $data = '') {
        $result['status'] = $status;
        $result['msg'] = $msg;
        $result['data'] = $data;
        exit(json_encode($result));
    }

    protected function _initialize() {
        //判断是不是扫描二维码进入的主页面，是则页面重定向，并且统计+1
        $isqr = I('get.isqr');
        if (!empty($isqr)) {
            $lon = I('get.lon');
            $lat = I('get.lat');
            $jumpurl = C('SCE_HOME_URL') . 'lon=' . $lon . '&lat=' . $lat;
            $apiqrplus = C('IP_BASE') . '/ssh2/sceman?cmd=IncreaseScenicSpotVo&type=1&lon=' . $lon . '&lat=' . $lat;
            $resultqrplus = json_decode(file_get_contents($apiqrplus), true);
            if ($resultqrplus['flag'])
                redirect($jumpurl);
        }
        $this->getTicket();
        $this->defaultShare();
    }

    protected function getTicket() {
        $rst = json_decode(file_get_contents('http://laiya.91feishixu.com/test.php?sec=zhyy123.'), TRUE);
        if ($rst['status'] != 1)
            return false;
        $ticket = $rst['ticket'];
        $signPackage = GetSignPackage(APPID, $ticket);
        $this->assign('sign', $signPackage);
    }

    protected function defaultShare() {
        if (IS_GET) {
            $lon = I('request.lon');
            $lat = I('request.lat');
            $rst = $this->getSceMsg($lon, $lat);
            $result['title'] = $rst['sceName'] . '官方首页';
            $result['desc'] = $rst['sceName'] . '景区助你成为旅行达人';
            $protocol = "http://";
            $result['imgUrl'] = $protocol . $_SERVER[HTTP_HOST] . C('FULL_PATH') . $rst['backgroundpic'];
            $result['link'] = $protocol . $_SERVER[HTTP_HOST] . '/bsce/home/Index/sce.html?lon=' . $lon . '&lat=' . $lat;
            $this->assign('share', $result);
        }
    }

    protected function getSceMsg($lon, $lat) {
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotVoByWap&lon=' . $lon . '&lat=' . $lat;
        $rst = json_decode(file_get_contents($apiUrl), TRUE);
        if ($rst['flag'] != 1)
            $this->returnJson(0, '获取景区信息失败');
        return $rst['result'];
    }

    /**
     * 设置session
     * @param String $name  session name
     * @param Mixed $data  session data
     * @param Int  $expire 超时时间(秒)
     */
    protected function set($name, $data, $expire = 120) {
        $session_data = array();
        $session_data['data'] = $data;
        $session_data['expire'] = time() + $expire;
        $_SESSION[$name] = $session_data;
    }

    /**
     * 读取session
     * @param String $name session name
     * @return Mixed
     */
    public function get($name) {
        if (isset($_SESSION[$name])) {
            if ($_SESSION[$name]['expire'] > time()) {
                return $_SESSION[$name]['data'];
            } else {
                session($name, null);
            }
        }
        return false;
    }

    /**
     * 根据经纬度返回id
     */
    protected function selectId($lon, $lat) {
        $sceId = S(md5($lon . $lat));
        if ($sceId)
            return $sceId;
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=queryScenicSpotVoIDBylonlat&lon=' . $lon . '&lat=' . $lat;
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1) {
            S(md5($lon . $lat), $result['result'], array('type' => 'file', 'expire' => 7200));
            return $result['result'];
        } else
            $this->returnJson(0, $result['result']);
    }

}
