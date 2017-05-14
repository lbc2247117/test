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

    /**
     * 判断openid是否存在，如果不存在，则保存用户信息；如果用户是取消关注，则删除用户信息
     * 
     * @param string $openid 公众号id
     */
    protected function saveUser($openid) {
        require_once 'Wechat.php';
        $wechatObj = new \wechatCallbackapiTest();
        $user = M('user');
        $where['openid'] = $openid;
        $result = $user->where("openid='$openid'")->select();
        if (!$result[0]) {
            $token = S('access_token');
            if (!$token) {
                $token = $wechatObj->getToken(APPID, SECRET);
                S('access_token', $token, 3600);
            }
            $userinfo = $wechatObj->getUser($openid, $token);
            if (!$userinfo['openid']) {
                $token = $wechatObj->getToken(APPID, SECRET);
                S('access_token', $token, 3600);
                $userinfo = $wechatObj->getUser($openid, $token);
            }
            if ($userinfo['subscribe'] == 0) {
                M('user')->where("openid='$openid'")->delete();
            } else {
                M('user')->add($userinfo);
            }
        }
    }

}
