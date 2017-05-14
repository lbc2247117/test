<?php

namespace Admin\Controller;

use Think\Controller;

class IndexController extends BaseController {

    /**
     * 获取基本设置数据
     */
    public function getBaseData() {
        $rst = M('setting')->find();
        if (!$rst)
            $this->returnJson(0, '获取数据失败');
        $this->returnJson(1, '成功', $rst);
    }

    /**
     * 设置基本设置的数据
     */
    public function setBaseData() {
        $data = I('post.');
        I('post.mobile', '', MOBILE) ? '' : $this->returnJson(0, '手机号码格式不对');
        $data = array_filter($data); //过滤掉值为空的数据
        $rst = M('setting')->find();
        if ($_FILES['qr']['error'] === 0) {
            $data['qr'] = uploadFile($_FILES['qr'], $rst['qr']);
        }
        if ($_FILES['headrPic']['error'] === 0) {
            $data['headrPic'] = uploadFile($_FILES['headrPic'], $rst['headrPic']);
        }
        $rst = M('setting')->where('1=1')->save($data);
        if (!$rst)
            $this->returnJson(0, '保存数据失败');
        $this->returnJson(1, '保存数据成功');
    }

    /**
     * 获取banner设置的数据
     */
    public function getBannerList() {
        $rst = M('banner')->order('sort asc')->select();
        if ($rst === FALSE)
            $this->returnJson(0, '获取数据失败');
        $data['count'] = count($rst);
        $data['list'] = $rst;
        $this->returnJson(1, '成功', $data);
    }

    /**
     * 添加banner的接口
     */
    public function addBanner() {
        $data = I('post.');
        I('post.sort', '', INT) ? '' : $this->returnJson(0, '序号只能是整数，请重新填写');
        $data = array_filter($data);
        if (!$_FILES['path'])
            $this->returnJson(0, '请选择要上传的图片~');
        $data['path'] = uploadFile($_FILES['path']);
        $rst = M('banner')->add($data);
        if (!$rst)
            $this->returnJson(0, '添加数据失败');
        $this->returnJson(1, '操作成功');
    }

    /**
     * 删除banner的接口
     */
    public function delBanner() {
        $ids = I('post.ids');
        $idstr = implode(',', $ids);
        $map['id'] = array('in', $idstr);
        $rst = M('banner')->where($map)->field('path')->select();
        $rstDel = M('banner')->where($map)->delete();
        if (!$rstDel)
            $this->returnJson(0, '删除失败~');
        foreach ($rst as $key => $value) {
            delFile($value['path']);
        }
        $this->returnJson(1, '删除成功');
    }

    /**
     * 修改banner的接口
     * 
     */
    public function setBanner() {
        $data = I('post.');
        I('post.sort', '', INT) ? '' : $this->returnJson(0, '序号只能是整数');
        $data = array_filter($data);
        $rst = M('banner')->where(array('id' => $data['id']))->find();
        if (!$rst)
            $this->returnJson(0, '没有相关数据~');
        if ($_FILES['path']) {
            $data['path'] = uploadFile($_FILES['path'], $rst['path']);
        }
        $rstSet = M('banner')->where(array('id' => $data['id']))->save($data);
        if (!$rstSet)
            $this->returnJson(0, '保存数据失败~');
        $this->returnJson(1, '操作成功');
    }

    /**
     * 获取首页产品数据
     */
    public function getHomeGoods() {
        $sql = "select a.id id,a.pid pid,a.sort sort,b.type type,b.name name,b.goodPic goodPic from home_goods as a left join product as b on a.pid=b.id order by a.sort asc";
        $result = M()->query($sql);
        if (FALSE === $result)
            $this->returnJson(0, '获取数据失败');
        $data['count'] = count($result);
        $data['list'] = $result;
        $this->returnJson(1, '成功', $data);
    }

    /**
     * 添加首页产品的就扣    
     */
    public function addHomeGoods() {
        $data['pid'] = I('post.pid', '', INT) ? I('post.pid') : $this->returnJson(0, '请输入正确的产品ID');
        $data['sort'] = I('post.sort', '', INT) ? I('post.sort') : $this->returnJson(0, '请输入正确的序号');
        //判断pid是否存在
        $rst = M('product')->where(array('id' => $data['pid']))->find();
        if (!$rst)
            $this->returnJson(0, '该产品ID不存在~');
        $rstAdd = M('home_goods')->add($data);
        if (!$rstAdd)
            $this->returnJson(0, '添加失败~');
        $this->returnJson(1, '添加成功');
    }

    /**
     * 删除首页产品的接口
     */
    public function delHomeGoods() {
        $ids = I('post.ids');
        $idstr = implode(',', $ids);
        $map['id'] = array('in', $idstr);
        $rstDel = M('home_goods')->where($map)->delete();
        if (!$rstDel)
            $this->returnJson(0, '删除失败~');
        $this->returnJson(1, '删除成功');
    }

    /**
     * 修改首页产品的接口
     */
    public function setHomeGoods() {
        $data['id'] = I('post.id', '', INT) ? I('post.id') : $this->returnJson(0, 'ID不正确');
        $data['pid'] = I('post.pid', '', INT) ? I('post.pid') : $this->returnJson(0, '请输入正确的产品ID');
        $data['sort'] = I('post.sort', '', INT) ? I('post.sort') : $this->returnJson(0, '请输入正确的序号');
        $data['oldPid'] = I('post.oldPid', '', INT) ? I('post.oldPid') : $this->returnJson(0, '请输入正确的产品ID');
        //判断id是否存在
        $rstGoods = M('home_goods')->where(array('id' => $data['id']))->find();
        if (!$rstGoods)
            $this->returnJson(0, '不存在该条记录，请刷新页面重新尝试~');
        $rstProduct = M('product')->where(array('id' => $data['pid']))->find();
        if (!$rstProduct)
            $this->returnJson(0, '产品ID不存在，请重新输入~');
        if ($data['oldPid'] !== $data['pid']) {
            $rstExsit = M('home_goods')->where(array('pid' => $data['pid']))->find();
            if ($rstExsit)
                $this->returnJson(0, '该产品已经添加，请勿重复添加~');
        }
        $rstSet = M('home_goods')->where(array('id' => $data['id']))->save($data);
        if (!$rstSet)
            $this->returnJson(0, '操作失败，请稍后重新尝试~');
        $this->returnJson(1, '保存成功');
    }

    //修改密码
    public function editPass() {
        $userinfo = session('userinfo');
        $oldPass = I('post.oldPass');
        $newPass = I('post.newPass');
        if (empty($oldPass))
            $this->returnJson(0, '请输入旧密码');
        if (empty($newPass))
            $this->returnJson(0, '请输入新密码');
        $rstFind = M('admin')->where(array('username' => $userinfo['username'], 'password' => $oldPass))->find();
        if (!$rstFind)
            $this->returnJson(0, '旧密码输入错误');
        $rstSet = M('admin')->where(array('username' => $userinfo['username']))->save(array('password' => $newPass));
        if (!$rstSet)
            $this->returnJson(0, '修改密码失败');
        $this->returnJson(1, '修改成功');
    }

}
