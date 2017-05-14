<?php

namespace Admin\Controller;

class IndexController extends BaseController {

    public function index() {
        $this->display();
    }

    //获取报名信息
    public function getSMSdata() {
        $page = I('post.page', '1', INT) ? I('post.page', '1', INT) : 1;
        $size = 20;
        $keyWord = I('post.keyWord');
        $order = 'addtime';
        $sort = I('post.sortType') == 'desc' ? 'desc' : 'asc';
        $model = M('sendsms');
        $where['type'] = 0;
        if (!empty($keyWord)) {
            $where['mobile'] = $keyWord;
        }
        $count = $model->where($where)->count();
        $result = $model->where($where)->page($page, $size)->order("$order $sort")->select();
        if ($result === FALSE)
            $this->returnJson(0, '获取数据失败');
        $data['count'] = $count;
        $data['info'] = $result;
        $map['type'] = 1;
        $resultCount = M('count')->where($map)->select();
        if ($resultCount[0]) {
            foreach ($resultCount as $key => $value) {
                if ($value['countName'] == 'clickCount')
                    $data['clickCount'] = $value['count'];
                if ($value['countName'] == 'shareCount')
                    $data['shareCount'] = $value['count'];
            }
        }
        $this->returnJson(1, '获取数据成功', $data);
    }

    public function delPrize() {
        $id = I('post.id');
        $id = implode(',', $id);
        $where['id'] = array('IN', $id);
        $rst = M('prize')->where($where)->delete();
        if ($rst === FALSE)
            $this->returnJson(0, '删除失败');
        $this->returnJson(1, '删除成功');
    }

    public function getPrizeData() {
        $page = I('post.page', '1', INT) ? I('post.page', '1', INT) : 1;
        $size = 20;
        $keyWord = I('post.keyWord');
        $order = 'prizetime';
        $sort = I('post.sortType') == 'desc' ? 'desc' : 'asc';
        $selType = I('post.selType', '0', INT);
        $where = "1=1";
        if (!empty($keyWord)) {
            $where.=" and (mobile='%s' or nickname='%s')";
        }
        if (!empty($selType)) {
            $where.=" and type=$selType";
        }
        $count = M('getprize')->where($where, $keyWord, $keyWord)->count();
        $prizeCount = M('getprize')->count();
        $getPrize = M('getprize')->where('status=1')->count();
        $result = M('getprize')->where($where, $keyWord, $keyWord)->page($page, $size)->order("$order $sort")->select();
        $data['count'] = $count;
        $data['prizeCount'] = $prizeCount;
        $data['getPrize'] = $getPrize;
        $data['info'] = $result;
        $map['type'] = 2;
        $resultCount = M('count')->where($map)->select();
        if ($resultCount[0]) {
            foreach ($resultCount as $key => $value) {
                if ($value['countName'] == 'clickCount')
                    $data['clickCount'] = $value['count'];
                if ($value['countName'] == 'shareCount')
                    $data['shareCount'] = $value['count'];
            }
        }
        $this->returnJson(1, '获取数据成功', $data);
    }

    //重发短信
    public function resend() {
        $mobile = I('post.mobile', '', MOBILE) ? I('post.mobile', '', MOBILE) : $this->returnJson(0, '手机号格式不对~');
        //判断是否在报名手册中
        $where['mobile'] = $mobile;
        $userinfo = M('sendsms')->where($where)->find();
        if (!$userinfo)
            $this->returnJson(0, '该手机号没有报名，不能重新发送信息');
        $result = json_decode(file_get_contents("http://www.yundao91.cn/ssh2/operation?cmd=sentMsgActivity&tel=$mobile&temNum=113729"), TRUE);
        if ($result['flag'] != 1)
            $this->returnJson(0, $result['result']);
        //更新数据库
        $data['status'] = 0;
        M('sendsms')->where($where)->save($data);
        $this->returnJson(1, '重新发送短信成功');
    }

    //获取投票信息
    public function getData() {
        $page = I('post.page', '1', INT) ? I('post.page', '1', INT) : 1;
        $size = 20;
        $keyWord = I('post.keyWord');
        $order = I('post.sort') == 'entrytime' ? 'entrytime' : 'count';
        $sort = I('post.sortType') == 'desc' ? 'desc' : 'asc';
        $model = M('entry');
        $type = ACTIVE_TYPE;
        $where = "type=$type";
        if (!empty($keyWord)) {
            $where.=" and (mobile='%s' or nickname='%s')";
        }
        $count = $model->where($where, $keyWord, $keyWord)->count();
        $result = $model->where($where, $keyWord, $keyWord)->page($page, $size)->order("$order $sort")->select();
        if ($result === FALSE)
            $this->returnJson(0, '获取数据失败');
        $entryCount = M('entry')->where(array('type' => ACTIVE_TYPE))->count();
        $sum = M()->query('select sum(count) sum from entry where type=' . ACTIVE_TYPE);
        $data['count'] = $count;
        $data['entryCount'] = $entryCount;
        $data['sum'] = $sum[0]['sum'];
        $data['info'] = $result;
        $map['type'] = ACTIVE_TYPE;
        $resultCount = M('count')->where($map)->select();
        if ($resultCount[0]) {
            foreach ($resultCount as $key => $value) {
                if ($value['countName'] == 'clickCount')
                    $data['clickCount'] = $value['count'];
                if ($value['countName'] == 'shareCount')
                    $data['shareCount'] = $value['count'];
            }
        }
        $this->returnJson(1, '获取数据成功', $data);
    }

    //上线或者下线
    public function onOrOff() {
        $id = I('post.id', '', INT) ? I('post.id', '', INT) : $this->returnJson(0, '用户编号不正确');
        $activeType = ACTIVE_TYPE;
        $result = M('entry')->where("id=%d and type=$activeType", $id)->find();
        $data['status'] = !$result['status'];
        $result = M('entry')->where("id=%d", $id)->save($data);
        if ($result === FALSE)
            $this->returnJson(0, '操作失败');
        $this->returnJson(1, '操作成功');
    }

    //领奖操作
    public function getPrizeAction() {
        $id = I('post.id', '', INT) ? I('post.id', '', INT) : $this->returnJson(0, '用户编号不正确');
        $result = M('getprize')->where("id=%d", $id)->find();
        $data['status'] = !$result['status'];
        $result = M('getprize')->where("id=%d", $id)->save($data);
        if ($result === FALSE)
            $this->returnJson(0, '操作失败');
        $this->returnJson(1, '操作成功');
    }

    //设置奖品，只能设置总数，不能设置已领
    public function setPrize() {
        
    }

    //获取奖品信息
    public function huoPrize() {
        if (IS_GET) {
            $this->display();
            exit();
        }
        $count = M('prize')->count();
        $result = M('prize')->select();
        if ($result === FALSE)
            $this->returnJson(0, '获取数据失败');
        $data['count'] = $count;
        $data['info'] = $result;
        $this->returnJson(1, '获取数据成功', $data);
    }

    //保存奖品信息
    public function savePrize() {
        $id = I('post.id', 0, INT);
        $text = I('post.text');
        $total = I('post.total', 0, INT) ? I('post.total', 0, INT) : $this->returnJson(0, '奖品数量请设置大于0的整数');
        $data['text'] = $text;
        $data['total'] = $total;
        if (empty($id)) {
            $data['count'] = 0;
            $result = M('prize')->add($data);
            if ($result === FALSE)
                $this->returnJson(0, '添加奖品信息失败');
            $this->returnJson(1, '添加成功');
        }
        $rstFind = M('prize')->where("id=$id")->find();
        if (!$rstFind)
            $this->returnJson(0, '没有获取到相关奖品信息');
        if ($rstFind['count'] > $total)
            $this->returnJson(0, '设置的总数不能小于已中奖数');
        $rstEdit = M('prize')->where("id=$id")->save($data);
        if ($rstEdit === FALSE)
            $this->returnJson(0, '修改奖品信息失败');
        $this->returnJson(1, '修改成功');
    }

    //获取详情
    public function getDetail() {
        $page = I('post.page', '', INT) ? I('post.page', '', INT) : 1;
        $size = 20;
        $id = I('post.id', '', INT) ? I('post.id', '', INT) : $this->returnJson(0, '用户编号不正确');
        $activeType = ACTIVE_TYPE;
        $result = M('entry')->where(array('id' => $id, 'type' => ACTIVE_TYPE))->find();
        if (!$result)
            $this->returnJson(0, '没有这个用户噢~');
        $username = $result['nickname'];
        $entryCount = $result['count'];
        $count = M('vote')->where("entryid=%d and type=$activeType", $id)->count();
        $result = M('vote')->where("entryid=%d and type=$activeType", $id)->page($page, $size)->order('votetime desc')->select();
        if ($result === FALSE)
            $this->returnJson(0, '获取数据失败');
        $data['username'] = $username;
        $data['entryCount'] = $entryCount;
        $data['count'] = $count;
        $data['info'] = $result;
        $this->returnJson(1, '获取成功', $data);
    }

    //删除投票
    public function delVote() {
        $id = I('post.id', '', INT) ? I('post.id', '', INT) : $this->returnJson(0, '投票编号不正确');
        $result = M('vote')->where(array('id' => $id, 'type' => ACTIVE_TYPE))->find();
        if (!$result)
            $this->returnJson(0, '获取投票记录失败，该条记录可能已经被删除');
        $count = $result['count'];
        $entryid = $result['entryid'];
        //开启事务
        M()->startTrans();
        $resultDel = M('vote')->where(array('id' => $id, 'type' => ACTIVE_TYPE))->delete();
        $findEntry = M('entry')->where("id=$entryid")->find();
        if (!$findEntry)
            $this->returnJson(0, '获取报名人信息失败');
        $data['count'] = $findEntry['count'] - $count;
        $resultSub = M('entry')->where("id=$entryid")->save($data);
        if (!$resultDel || !$resultSub) {
            //事务回滚
            M()->rollback();
            $this->returnJson(0, '删除投票操作失败~');
        }
        //提交事务
        M()->commit();
        $this->returnJson(1, '删除投票操作成功~');
    }

    //添加投票
    public function addVote() {
        $id = I('post.id', '', INT) ? I('post.id', '', INT) : $this->returnJson(0, '用户编号不正确');
        $count = I('post.count', '', INT) ? I('post.count', '', INT) : $this->returnJson(0, '请输入正确的票数');
        $where['id'] = $id;
        $where['type'] = ACTIVE_TYPE;
        $findEntry = M('entry')->where($where)->find();
        if (!$findEntry)
            $this->returnJson(0, '该用户不存在');
        $data['count'] = $findEntry['count'] + $count;
        $voteinfo['entryid'] = $id;
        $voteinfo['count'] = $count;
        $voteinfo['type'] = ACTIVE_TYPE;
        $voteinfo['openid'] = 'yunyinghoulai';
        $voteinfo['nickname'] = '运营后台';
        $voteinfo['sex'] = '2';
        $voteinfo['city'] = '成都';
        $voteinfo['votetime'] = date('Y-m-d H:i:s');
        M()->startTrans();
        $resultEnrty = M('entry')->where($where)->save($data);
        $resultVote = M('vote')->add($voteinfo);
        if (!$resultEnrty || !$resultVote) {
            //事务回滚
            M()->rollback();
            $this->returnJson(0, '添加投票失败~');
        }
        M()->commit();
        $this->returnJson(1, '添加投票成功');
    }

}
