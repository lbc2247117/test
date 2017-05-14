<?php

/**
 * 活动接口
 *
 * @author LiuBoCheng
 * @copyright (c) 2016, 云道
 * @version 2016-08-27
 */

namespace Home\Controller;

class ActiveController extends BaseController {

    public function index() {
        $appid = APPID;
        $secret = SECRET;
        require_once 'Wechat.php';
        $wechatObj = new \wechatCallbackapiTest();
        $token = S('access_token');
        if (!$token) {
            $token = $wechatObj->getToken(APPID, SECRET);
            S('access_token', $token, 3600);
        }
        $jsapiTicket = $wechatObj->getJsApiTicket($token);
        if ($jsapiTicket == FALSE) {
            $token = $wechatObj->getToken(APPID, SECRET);
            S('access_token', $token, 3600);
            $jsapiTicket = $wechatObj->getJsApiTicket($token);
        }
        $signPackage = $wechatObj->GetSignPackage(APPID, $jsapiTicket);
        $this->assign('sign', $signPackage);
        $where['type'] = ACTIVE_TYPE;
        $where['countName'] = 'clickCount';
        $count = M('count')->where($where)->find();
        if (!$count) {
            $this->display();
            exit();
        }
        $data['count'] = $count['count'] + 1;
        M('count')->where($where)->save($data);
        $this->display();
    }

    public function racing() {
        $appid = APPID;
        $secret = SECRET;
        require_once 'Wechat.php';
        $wechatObj = new \wechatCallbackapiTest();
        $token = S('access_token');
        if (!$token) {
            $token = $wechatObj->getToken(APPID, SECRET);
            S('access_token', $token, 3600);
        }
        $jsapiTicket = $wechatObj->getJsApiTicket($token);
        if ($jsapiTicket == FALSE) {
            $token = $wechatObj->getToken(APPID, SECRET);
            S('access_token', $token, 3600);
            $jsapiTicket = $wechatObj->getJsApiTicket($token);
        }
        $signPackage = $wechatObj->GetSignPackage(APPID, $jsapiTicket);
        $this->assign('sign', $signPackage);
        $where['type'] = 1;
        $where['countName'] = 'clickCount';
        $count = M('count')->where($where)->find();
        if (!$count) {
            $this->display();
            exit();
        }
        $data['count'] = $count['count'] + 1;
        M('count')->where($where)->save($data);
        $this->display();
    }

    public function racingapp() {
        $appid = APPID;
        $secret = SECRET;
        require_once 'Wechat.php';
        $wechatObj = new \wechatCallbackapiTest();
        $token = S('access_token');
        if (!$token) {
            $token = $wechatObj->getToken(APPID, SECRET);
            S('access_token', $token, 3600);
        }
        $jsapiTicket = $wechatObj->getJsApiTicket($token);
        if ($jsapiTicket == FALSE) {
            $token = $wechatObj->getToken(APPID, SECRET);
            S('access_token', $token, 3600);
            $jsapiTicket = $wechatObj->getJsApiTicket($token);
        }
        $signPackage = $wechatObj->GetSignPackage(APPID, $jsapiTicket);
        $this->assign('sign', $signPackage);
        $where['type'] = 1;
        $where['countName'] = 'clickCount';
        $count = M('count')->where($where)->find();
        if (!$count) {
            $this->display();
            exit();
        }
        $data['count'] = $count['count'] + 1;
        M('count')->where($where)->save($data);
        $this->display();
    }

    //发送短信
    public function sendSMSAPI() {
        $mobile = I('post.mobile', '', MOBILE) ? I('post.mobile', '', MOBILE) : $this->returnJson(0, '手机号格式不对~');
        $code = I('post.verify', '', 'strtolower');
        //验证验证码是否正确
        if (!($this->check_verify($code))) {
            $this->returnJson(0, '验证码输入错误~');
        }
        //判断是否已经报了名
        $where['mobile'] = $mobile;
        $userinfo = M('sendsms')->where($where)->find();
        if ($userinfo)
            $this->returnJson(0, '您已经参加过活动，兑换门票短信通过手机短信发送，请注意查收');
        $count = M('sendsms')->count();
        if ($count > 499)
            $this->returnJson(0, '非常抱歉，已经没有名额了噢~');
        $result = json_decode(file_get_contents("http://www.yundao91.cn/ssh2/operation?cmd=sentMsgActivity&tel=$mobile&temNum=114052"), TRUE);
        if (!$result['flag'])
            $this->returnJson(0, '短信发送失败请重试.');
        $data['mobile'] = $mobile;
        $data['addtime'] = date('Y-m-d H:i:s');
        $data['status'] = 0;
        $data['type'] = 0;
        M('sendsms')->add($data);
        $this->returnJson(1, '报名成功，兑换门票短信通过手机短信发送，请注意查收');
    }

    //获取剩余名额
    public function getSurplusCount() {
        $count = M('sendsms')->count();
        $count = 500 - $count;
        if ($count < 0)
            $count = 0;
        $this->returnJson(1, '获取成功', $count);
    }

    //验证码
    public function verify() {
        $Verify = new \Think\Verify();
        $Verify->codeSet = '0123456789';
        $Verify->fontSize = 13;
        $Verify->length = 4;
        $Verify->entry();
    }

    protected function check_verify($code) {
        $verify = new \Think\Verify();
        return $verify->check($code);
    }

    public function shareSuccess() {
        $type = I('post.type', '0', INT);
        $where['type'] = $type;
        $where['countName'] = 'shareCount';
        $count = M('count')->where($where)->find();
        if (!$count) {
            exit();
        }
        $data['count'] = $count['count'] + 1;
        M('count')->where($where)->save($data);
    }

    public function responseWechat() {
        require_once 'Wechat.php';
        $wechatObj = new \wechatCallbackapiTest();
        if (isset($_GET['echostr'])) {
            $wechatObj->valid();
            exit();
        }
        //$file = file_get_contents("https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=$token");
        //$wechatObj->createmenu($token);
        //$wechatObj->logger("access_token:".S('access_token'));
        $wechatMsg = $wechatObj->getWechatMsg();
        $openid = $wechatMsg->FromUserName;
        $this->saveUser($openid);
        if (($wechatMsg->MsgType == "text" && ($wechatMsg->Content == '教师节' || $wechatMsg->Content == '投票')) || ($wechatMsg->MsgType == "event" && ($wechatMsg->EventKey == 'teacher' || $wechatMsg->Event == 'subscribe'))) {
            $content = array();
            $content['Title'] = "来吖旅行|教师节活动";
            $content['Description'] = "来吖旅行|老师很嗨，教师节活动，赢取五月天成都站演唱会门票，快来参加吧~";
            $content['PicUrl'] = "http://7xnjsm.com1.z0.glb.clouddn.com/banner831.jpg";
            $content['Url'] = 'http://www.sda88.cn/Active/index.html?openid=' . $openid;
            $data[] = $content;
            $result = $wechatObj->transmitNews($wechatMsg, $data);
            exit($result);
        }
    }

    /**
     * 获取用户信息
     * @return string userinfo{headurlimg,nickname}
     */
    public function getUserInfo() {
        $openid = I('post.openid');
        $userinfo = M('user')->where("openid='%s'", $openid)->find();
        if (!$userinfo)
            $this->returnJson(0, '未获取到用户信息,请从公众号进入活动页~');
        $data['nickname'] = $userinfo['nickname'];
        $data['headimgurl'] = $userinfo['headimgurl'];
        $this->returnJson(1, '获取成功', $data);
    }

    public function join() {
        $appid = APPID;
        $secret = SECRET;
        require_once 'Wechat.php';
        $wechatObj = new \wechatCallbackapiTest();
        $token = S('access_token');
        if (!$token) {
            $token = $wechatObj->getToken(APPID, SECRET);
            S('access_token', $token, 3600);
        }
        $jsapiTicket = $wechatObj->getJsApiTicket($token);
        if ($jsapiTicket == FALSE) {
            $token = $wechatObj->getToken(APPID, SECRET);
            S('access_token', $token, 3600);
            $jsapiTicket = $wechatObj->getJsApiTicket($token);
        }
        $signPackage = $wechatObj->GetSignPackage(APPID, $jsapiTicket);
        $this->assign('sign', $signPackage);
        $this->display();
    }

    public function callBack() {
        $appid = APPID;
        $secret = SECRET;
        if (!isset($_GET['code'])) {    //没有code则让用户跳转到微信
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $url = urlencode($url);
            header("location:https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$url&response_type=code&scope=snsapi_base&state=diy&connect_redirect=1#wechat_redirect");
            exit();
        }
        $code = $_GET['code'];
        //获取openid
        $urlOpen = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
        $result = file_get_contents($urlOpen);
        $result = json_decode($result, true);
        $openid = $result['openid'];
        //获取用户信息
        require_once 'jssdk.php';
        $jssdk = new \JSSDK($appid, $secret);
        $accessToken = $jssdk->getAccessToken();
        $result = file_get_contents("https://api.weixin.qq.com/cgi-bin/user/info?access_token=$accessToken&openid=$openid&lang=zh_CN");
        //存放用户信息
        session('userinfo', null);
        session('userinfo', $result);
        //跳转到活动页面
        $this->redirect('index.html');
    }

    /*
     * 判断是否从公众号进入
     */

    public function clickEntry() {
        if (session('userinfo'))
            $this->returnJson(1, 'true');
        $this->returnJson(0, 'false');
    }

    //报名活动
    public function entryActive() {

        $openid = I('post.openid');
        $mobile = I('post.mobile', '', MOBILE) ? I('post.mobile', '', MOBILE) : $this->returnJson(0, 'OMG,吖妞发现你的手机号填写有误，请返回重新填写。');
        $userinfo = M('user')->where("openid='%s'", $openid)->find();
        if (!$userinfo)
            $this->returnJson(0, '发现你没有关注公众号哦，请先关注公众号再报名~');
        //先判断是否已经报名
        $openid = $userinfo['openid'];
        $result = M('entry')->where(array('openid' => $openid, 'type' => 0))->find();
        if ($result)
            $this->returnJson(0, '亲，你已经报过名了呢~不要重复提交哦~');
        $userinfo['mobile'] = $mobile;
        $userinfo['count'] = 0;
        $userinfo['entrytime'] = date('Y-m-d H:i:s');
        $userinfo['status'] = 0;
        $userinfo['type'] = ACTIVE_TYPE;
        $result = M('entry')->add($userinfo);
        if (!$result)
            $this->returnJson(0, '报名失败，请联系客服人员~');
        $lastID = M('entry')->getLastInsID();
        $this->returnJson(1, "本次报名成功,你是第" . $lastID . "号用户噢,火速去分享首页拉票吧!");
    }

    //抽奖
    public function lottery() {
        $openid = I('post.openid');
        $userinfo = M('user')->where("openid='%s'", $openid)->find();
        if (!$userinfo)
            $this->returnJson(2, '请不要随意填写openid');
        //判断是否已经抽过奖
        $result = M('getprize')->where(array('openid' => $userinfo['openid']))->find();
        if ($result)
            $this->returnJson(0, '亲,您的抽奖机会已经用完了噢~赶紧联系工作人员领取奖品吧!');
        //获取剩余奖品数量
        $result = M('prize')->select();
        if ($result === FALSE)
            $this->returnJson(0, '未获取到奖品信息，请稍后重试~');
        $prize = array();
        foreach ($result as $key => $value) {
            $sub = $value['total'] - $value['count'];
            if ($sub > 0) {
                for ($i = 0; $i < $sub; $i++) {
                    array_push($prize, $value['id']);
                }
            }
        }
        if (count($prize) < 1)
            $this->returnJson(0, '您来晚了一步，已经没有奖品了哦~');
        $type = $prize[rand(0, count($prize)-1)];
        unset($userinfo['id']);
        $userinfo['type'] = $type;
        $userinfo['prizetime'] = date('Y-m-d H:i:s');
        $userinfo['status'] = 0;
        M()->startTrans();
        $rstPrize = M('getprize')->add($userinfo);
        $prizeFind = M('prize')->where("id=$type")->find();
        $data['count'] = $prizeFind['count'] + 1;
        $savePrize = M('prize')->where("id=$type")->save($data);
        if (!$rstPrize || !$savePrize || $data['count'] > $prizeFind['total']) {
            M()->rollback();
            $this->returnJson(0, '未获取到奖品信息，请稍后重试~');
        }
        //M()->commit();
        $this->returnJson(1, '获得' . $prizeFind['text'] . '!',$type);
    }

    //填写手机号
    public function setMobile() {
        $openid = I('post.openid');
        $mobile = I('post.mobile', '', MOBILE) ? I('post.mobile', '', MOBILE) : $this->returnJson(0, 'OMG,吖妞发现你的手机号填写有误，请返回重新填写。');
        $userinfo = M('user')->where("openid='%s'", $openid)->find();
        if (!$userinfo)
            $this->returnJson(0, '发现你没有关注公众号哦，请先关注公众号再抽奖~');
        $data['mobile'] = $mobile;
        $result = M('getprize')->where("openid='%s'", $openid)->save($data);
        if ($result === FALSE)
            $this->returnJson(0, '填写手机号失败，请稍后重试~');
        $this->returnJson(1, '填写成功');
    }

    //投票
    public function poll() {

        $openid = I('post.openid');
        $userinfo = M('user')->where("openid='%s'", $openid)->find();
        if (!$userinfo)
            $this->returnJson(2, '请不要随意填写openid');
        //判断是否已经投过票
        $result = M('vote')->where(array('openid' => $userinfo['openid'], 'type' => ACTIVE_TYPE))->find();
        if ($result)
            $this->returnJson(0, '亲,您的投票机会已经用完了噢~想赢取奖品就火速参加活动吧!');
        //判断被投人员是否存在和下线
        $id = I('post.id', '', INT) ? I('post.id', '', INT) : $this->returnJson(0, '请不要用程序提交接口~');
        $activeType = ACTIVE_TYPE;
        $result = M('entry')->where("id=%d and type=$activeType", $id)->find();
        if (!$result)
            $this->returnJson(0, '您要投票的人员不存在');
        if ($result['status'] == 1)
            $this->returnJson(0, '您要投票的人员被管理员关进小黑屋了，暂时不能对TA进行投票噢~');
        $userinfo['count'] = 1;
        $userinfo['votetime'] = date('Y-m-d H:i:s');
        $userinfo['entryid'] = $id;
        $userinfo['type'] = $activeType;
        //开启事务
        M()->startTrans();
        $resultVote = M('vote')->add($userinfo);
        $where = array('id' => $id, 'type' => ACTIVE_TYPE);
        $findEntry = M('entry')->where($where)->find();
        $data['count'] = $findEntry['count'] + 1;
        $resultEntry = M('entry')->where($where)->save($data);
        if (!$resultEntry || !$resultVote) {
            //回滚事务
            M()->rollback();
            $this->returnJson(0, '抱歉，投票失败，请稍后重试~');
        }
        //提交事务
        M()->commit();
        $this->returnJson(1, '恭喜你,投票成功~');
    }

    //获取投票信息
    public function getData() {
        $activeType = ACTIVE_TYPE;
        $page = I('post.page', '1', INT) ? I('post.page', '1', INT) : 1;
        $size = 10;
        $keyWord = I('post.keyWord');
        $order = I('post.sort') == 'entrytime' ? 'entrytime' : 'count';
        $model = M('entry');
        $where = "type=$activeType and status=0";
        if (!empty($keyWord)) {
            $where.=" and (id=%d or nickname='%s')";
        }
        $model->where($where, $keyWord, $keyWord);
        $count = $model->count();
        $result = $model->where($where, $keyWord, $keyWord)->page($page, $size)->order("$order desc")->field('id,count,headimgurl,nickname')->select();
        if ($result === FALSE)
            $this->returnJson(0, '获取数据失败');

        //找出所有的数据排名。
        $dataAll = M('entry')->where(array('status' => 0, 'type' => ACTIVE_TYPE))->field('id,count,headimgurl,nickname')->order('count desc')->select();
        if (!empty($keyWord) || $order != 'count') {
            foreach ($result as $key => $value) {
                for ($i = 0; $i < count($dataAll); $i++) {
                    if ($value['id'] == $dataAll[$i]['id']) {
                        $result[$key]['top'] = $i + 1;
                    }
                }
            }
        } else {
            $result = array_slice($dataAll, 10 * ($page - 1), 10);
            for ($i = 0; $i < count($result); $i++)
                $result[$i]['top'] = 10 * ($page - 1) + $i + 1;
        }

        $data['count'] = $count;
        $data['info'] = $result;
        $this->returnJson(1, '获取数据成功', $data);
    }

}
