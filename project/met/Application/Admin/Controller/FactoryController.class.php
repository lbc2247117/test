<?php

namespace Admin\Controller;

/**
 * 工厂设置接口
 *
 * @author 刘波成
 */
class FactoryController extends BaseController {

    /**
     * 获取工厂展示数据
     */
    function getFactoryData() {
        $rst = M('factory')->order('sort asc')->select();
        if ($rst === FALSE)
            $this->returnJson(0, '获取数据失败');
        $data['count'] = count($rst);
        $data['list'] = $rst;
        $this->returnJson(1, '成功', $data);
    }

    /**
     * 设置工厂展示的接口
     */
    function setFactory() {
        $data = I('post.');
        I('post.sort', '', INT) ? '' : $this->returnJson(0, '序号只能是整数');
        $data = array_filter($data);
        $rst = M('factory')->where(array('id' => $data['id']))->find();
        if (!$rst)
            $this->returnJson(0, '没有相关数据~');
        if ($_FILES['picPath']) {
            $data['picPath'] = uploadFile($_FILES['picPath'], $rst['picPath']);
        }
        $rstSet = M('factory')->where(array('id' => $data['id']))->save($data);
        if (!$rstSet)
            $this->returnJson(0, '保存数据失败~');
        $this->returnJson(1, '操作成功');
    }

    function addFactory() {
        $data = I('post.');
        I('post.sort', '', INT) ? '' : $this->returnJson(0, '序号只能是整数，请重新填写');
        $data = array_filter($data);
        if (!$_FILES['picPath'])
            $this->returnJson(0, '请选择要上传的图片~');
        $data['picPath'] = uploadFile($_FILES['picPath']);
        $rst = M('factory')->add($data);
        if (!$rst)
            $this->returnJson(0, '添加数据失败');
        $this->returnJson(1, '操作成功');
    }

    function delFactory() {
        $ids = I('post.ids');
        $idstr = implode(',', $ids);
        $map['id'] = array('in', $idstr);
        $rst = M('factory')->where($map)->field('picPath')->select();
        $rstDel = M('factory')->where($map)->delete();
        if (!$rstDel)
            $this->returnJson(0, '删除失败~');
        foreach ($rst as $key => $value) {
            delFile($value['picPath']);
        }
        $this->returnJson(1, '删除成功');
    }

}
