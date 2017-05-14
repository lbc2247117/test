<?php

namespace Admin\Controller;

class SysController extends BaseController {

    public function getAdminList() {
        $rst = M('admin')->select();
        if ($rst === FALSE)
            $this->returnJson(0, '获取数据失败');
        $data['count'] = count($rst);
        $data['list'] = $rst;
        $this->returnJson(1, '成功', $data);
    }

    public function addAdmin() {
        $data = I('post.');
        if (empty($data['username']))
            $this->returnJson(0, '请输入用户名');
        if (empty($data['password']))
            $this->returnJson(0, '请输入密码');
        //判断用户名是否存在
        $rstFind = M('admin')->where(array('username' => $data['username']))->find();
        if ($rstFind)
            $this->returnJson(0, '该管理员已存在');
        $data = array_filter($data);
        $data['regTime'] = date('Y-m-d H:i:s', time());
        $rstAdd = M('admin')->add($data);
        if (!$rstAdd)
            $this->returnJson(0, '添加管理员失败');
        $this->returnJson(1, '添加成功');
    }

    public function delAdmin() {
        $ids = I('post.ids');
        if (in_array(1, $ids))
            $this->returnJson(0, '超级管理员不能删除');
        $idsStr = implode(',', $ids);
        $map['id'] = array('in', $idsStr);
        $rstDel = M('admin')->where($map)->delete();
        if ($rstDel === FALSE)
            $this->returnJson(0, '删除失败');
        $this->returnJson(1, '删除成功');
    }

}
