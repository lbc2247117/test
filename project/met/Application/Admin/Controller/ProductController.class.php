<?php

/**
 * Description of ProductController
 *
 * @author 刘波成
 */

namespace Admin\Controller;

class ProductController extends BaseController {

    //获取产品款式列表
    public function getProductList() {
        $type = I('post.type');
        $status = I('post.status');
        $keyWord = I('post.keyWord');
        $size = I('post.size', '', INT) ? I('post.size') : 20;
        $page = I('post.page', '', INT) ? I('post.page') : 1;
        if ($type != -1)
            $map['type'] = $type;
        if ($status != -1)
            $map['status'] = $status;
        if (!empty($keyWord))
            $map['name'] = $keyWord;
        if ($map) {
            $count = M('product')->where($map)->count();
            $rst = M('product')->where($map)->limit(($page - 1) * $size, $size)->select();
        } else {
            $count = M('product')->count();
            $rst = M('product')->limit(($page - 1) * $size, $size)->select();
        }
        if ($rst === FALSE)
            $this->returnJson(0, '获取数据失败');
        $data['count'] = $count;
        $data['list'] = $rst;
        $this->returnJson(1, '获取数据成功', $data);
    }

    //添加产品款式
    public function addProduct() {
        $data = I('post.');
        I('post.sort', '', INT) ? '' : $this->returnJson(0, '序号填写不正确，序号必须是整数~');
        if (empty($data['name']))
            $this->returnJson(0, '请输入产品名称');
        if (empty($data['remark']))
            $this->returnJson(0, '请输入产品描述');
        if (empty($data['price']))
            $this->returnJson(0, '请输入产品价格');
        if (!$_FILES['goodPic'])
            $this->returnJson(0, '请上传产品图片');
        $data['goodPic'] = uploadFile($_FILES['goodPic']);
        $rstAdd = M('product')->add($data);
        if (!$rstAdd)
            $this->returnJson(0, '添加产品失败~');
        $this->returnJson(1, '添加成功');
    }

    //修改产品款式
    public function setProduct() {
        $data = I('post.');
        I('post.id', '', INT) ? '' : $this->returnJson(0, '产品ID不正确');
        I('post.sort', '', INT) ? '' : $this->returnJson(0, '序号填写不正确，序号必须是整数~');
        if (empty($data['name']))
            $this->returnJson(0, '请输入产品名称');
        if (empty($data['remark']))
            $this->returnJson(0, '请输入产品描述');
        if (empty($data['price']))
            $this->returnJson(0, '请输入产品价格');
        $data = array_filter($data);
        if ($_FILES['goodPic']) {
            $rstFind = M('product')->where(array('id' => $data['id']))->find();
            if (!$rstFind)
                $this->returnJson(0, '不存在该产品，请刷新页面后重新操作~');
            $goodPic = $rstFind['goodPic'];
            $data['goodPic'] = uploadFile($_FILES['goodPic'], $goodPic);
        }
        $rstSet = M('product')->where(array('id' => $data['id']))->save($data);
        if (!$rstSet)
            $this->returnJson(0, '保存失败~');
        $this->returnJson(1, '保存成功');
    }

    //删除产品款式
    public function delProduct() {
        $ids = I('post.ids');
        foreach ($ids as $key => $value) {
            M()->startTrans();
            $rstDelPro = M('product')->where(array('id' => $value))->delete();
            $rstDelSize = M('product_size')->where(array('pid' => $value))->delete();
            $rstDelParam = M('product_param')->where(array('gid' => $value))->delete();
            if ($rstDelPro !== FALSE && $rstDelSize !== FALSE && $rstDelParam !== FALSE)
                M()->commit();
            else {
                M()->rollback();
                $this->returnJson(0, '删除数据失败');
            }
        }
        $this->returnJson(1, '删除成功');
    }

    //下架产品款式
    public function downProduct() {
        $id = I('post.id', '', INT) ? I('post.id') : $this->returnJson(0, '产品ID不正确');
        $data['status'] = 0;
        $rstDown = M('product')->where(array('id' => $id))->save($data);
        if (!$rstDown)
            $this->returnJson(0, '下架失败~');
        $this->returnJson(1, '下架成功');
    }

    //上架产品款式
    public function upProduct() {
        $id = I('post.id', '', INT) ? I('post.id') : $this->returnJson(0, '产品ID不正确');
        $data['status'] = 1;
        $rstDown = M('product')->where(array('id' => $id))->save($data);
        if (!$rstDown)
            $this->returnJson(0, '上架失败~');
        $this->returnJson(1, '上架成功');
    }

    //获取产品型号列表
    public function getProductSizeList() {
        $pid = (I('post.pid', '', INT) ? I('post.pid') : $this->returnJson(0, '产品ID不正确'));
        $status = I('post.status');
        $size = I('post.size', '', INT) ? I('post.size') : 20;
        $page = I('post.page', '', INT) ? I('post.page') : 1;
        if ($status != -1)
            $map['status'] = $status;
        if ($map) {
            $sqlCount = "select count(*) count from product_size as a left join product as b on a.pid=b.id where a.pid='%d' and a.status='%d'";
            $rstCount = M()->query($sqlCount, $pid, $status);
            if ($rstCount === FALSE)
                $this->returnJson(0, '获取数据失败');
            $count = $rstCount[0]['count'];
            $sql = "select a.id id,a.name name,a.status status,a.remark remark,b.name goodname from product_size as a left join product as b on a.pid=b.id where a.pid='%d' and a.status='%d' limit %d,%d";
            $rst = M()->query($sql, $pid, $status, $size * ($page - 1), $size);
        } else {
            $sqlCount = "select count(*) count from product_size as a left join product as b on a.pid=b.id where a.pid='%d'";
            $rstCount = M()->query($sqlCount, $pid);
            if ($rstCount === FALSE)
                $this->returnJson(0, '获取数据失败');
            $count = $rstCount[0]['count'];
            $sql = "select a.id id,a.name name,a.status status,a.remark remark,b.name goodname from product_size as a left join product as b on a.pid=b.id where a.pid='%d' limit %d,%d";
            $rst = M()->query($sql, $pid, $size * ($page - 1), $size);
        }
        if ($rst === FALSE)
            $this->returnJson(0, '获取数据失败');
        $data['count'] = $count;
        $data['list'] = $rst;
        $this->returnJson(1, '获取数据成功', $data);
    }

    //添加产品型号
    public function addProductSize() {
        $data = I('post.');
        I('post.pid', '', INT) ? '' : $this->returnJson(0, '缺少产品ID');
        if (empty($data['name']))
            $this->returnJson(0, '请填写型号名称');
        $data = array_filter($data);
        $rstAdd = M('product_size')->add($data);
        if (!$rstAdd)
            $this->returnJson(0, '添加产品型号失败~');
        $this->returnJson(1, '添加成功');
    }

    //修改产品型号
    public function setProductSize() {
        $data['id'] = (I('post.id', '', INT) ? I('post.id') : $this->returnJson(0, '型号ID不正确'));
        $data['name'] = trim(I('post.name'));
        $data['remark'] = I('post.remark');
        if (empty($data['name']))
            $this->returnJson(0, '请输入型号名称');
        $rstSet = M('product_size')->where(array('id' => $data['id']))->save($data);
        if (!$rstSet)
            $this->returnJson(0, '操作失败');
        $this->returnJson(1, '保存成功');
    }

    //删除产品型号
    public function delProductSize() {
        $ids = I('post.ids');
        foreach ($ids as $key => $val) {
            M()->startTrans();
            $rstDelSize = M('product_size')->where(array('id' => $val))->delete();
            $rstDelParam = M('product_param')->where(array('sid' => $val))->delete();
            if ($rstDelParam !== FALSE && $rstDelSize !== FALSE)
                M()->commit();
            else {
                M()->rollback();
                $this->returnJson(0, '删除数据失败');
            }
        }
        $this->returnJson(1, '删除成功');
    }

    //下架产品型号
    public function downProductSize() {
        $id = (I('post.id', '', INT) ? I('post.id') : $this->returnJson(0, '型号ID不正确'));
        $data['status'] = 0;
        $rst = M('product_size')->where(array('id' => $id))->save($data);
        if (!$rst)
            $this->returnJson(0, '下架失败');
        $this->returnJson(1, '下架成功');
    }

    //上架产品型号
    public function upProductSize() {
        $id = (I('post.id', '', INT) ? I('post.id') : $this->returnJson(0, '型号ID不正确'));
        $data['status'] = 1;
        $rst = M('product_size')->where(array('id' => $id))->save($data);
        if (!$rst)
            $this->returnJson(0, '上架失败');
        $this->returnJson(1, '上架成功');
    }

    //获取产品类型列表
    public function getProductTypeList() {
        $rst = M('product_type')->select();
        if ($rst === FALSE)
            $this->returnJson(0, '获取数据失败');
        $data['count'] = count($rst);
        $data['list'] = $rst;
        $this->returnJson(1, '获取数据成功', $data);
    }

    //添加产品类型
    public function addProductType() {
        $data = I('post.');
        if (empty($data['name']))
            $this->returnJson(0, '请填写车型');
        $rst = M('product_type')->where(array('name' => $data['name']))->find();
        if ($rst)
            $this->returnJson(0, '该车型已存在，请勿重复添加');
        $rstAdd = M('product_type')->add($data);
        if (!$rstAdd)
            $this->returnJson(0, '添加车型失败，请稍后重新尝试~');
        $this->returnJson(1, '添加成功');
    }

    //删除产品类型
    public function delProductType() {
        $id = (I('post.id', '', INT) ? I('post.id') : $this->returnJson(0, '车型ID不正确~'));
        $map['id'] = $id;
        $rst = M('product_type')->where($map)->delete();
        if (!$rst)
            $this->returnJson(0, '删除数据失败~');
        $this->returnJson(1, '删除数据成功');
    }

    //获取产品参数类型列表
    public function getParamTypeList() {
        $rst = M('param_type')->order('sort asc')->select();
        if ($rst === FALSE)
            $this->returnJson(0, '获取数据失败');
        $data['count'] = count($rst);
        $data['list'] = $rst;
        $this->returnJson(1, '获取数据成功', $data);
    }

    //添加参数类型
    public function addParamType() {
        $data = I('post.');
        I('post.sort', '', INT) ? '' : $this->returnJson(0, '请填写正确的序号');
        if (empty($data['name']))
            $this->returnJson(0, '请填写参数类型名称');
        $rst = M('param_type')->where(array('name' => $data['name']))->find();
        if ($rst)
            $this->returnJson(0, '该参数类型已存在，请勿重复添加');
        $data = array_filter($data);
        $rstAdd = M('param_type')->add($data);
        if (!$rstAdd)
            $this->returnJson(0, '添加参数类型失败，请稍后重新尝试~');
        $this->returnJson(1, '添加成功');
    }

    //设置参数类型
    public function setParamType() {
        $data = I('post.');
        I('post.sort', '', INT) ? '' : $this->returnJson(0, '请填写正确的序号');
        if (empty($data['name']))
            $this->returnJson(0, '请填写参数类型名称');
        $rstExsit = M('param_type')->where(array('id' => $data['id']))->find();
        if (!$rstExsit)
            $this->returnJson(0, '不存在该数据');
        if ($data['oldName'] != $data['name']) {
            $rst = M('param_type')->where(array('name' => $data['name']))->find();
            if ($rst)
                $this->returnJson(0, '该条记录已经存在，请勿重复添加');
        }
        $rstSet = M('param_type')->where(array('id' => $data['id']))->save($data);
        if (!$rstSet)
            $this->returnJson(0, '更新数据失败~');
        $this->returnJson(1, '操作成功');
    }

    //删除参数类型
    public function delParamType() {
        $ids = I('post.ids');
        $idstr = implode(',', $ids);
        $map['id'] = array('in', $idstr);
        $rst = M('param_type')->where($map)->delete();
        if (!$rst)
            $this->returnJson(0, '删除数据失败');
        $this->returnJson(1, '删除成功');
    }

    //获取产品参数列表
    public function getProductParamList() {
        $sid = (I('post.sid', '', INT) ? I('post.sid') : $this->returnJson(0, '型号ID不正确'));
        $pid = I('post.pid');
        $size = I('post.size', '', INT) ? I('post.size') : 20;
        $page = I('post.page', '', INT) ? I('post.page') : 1;
        if ($pid != -1)
            $map['pid'] = $pid;
        if ($map) {
            $sqlCount = "select count(*) count from product_param  where sid='%d' and pid='%d'";
            $rstCount = M()->query($sqlCount, $sid, $pid);
            if ($rstCount === FALSE)
                $this->returnJson(0, '获取数据失败');
            $count = $rstCount[0]['count'];
            $sql = "select a.id id,a.pid pid,a.key skey,a.value value,b.name sizename,c.name type from product_param as a left join product_size as b on a.sid=b.id left join param_type as c on a.pid=c.id where a.sid='%d' and a.pid='%d' limit %d,%d";
            $rst = M()->query($sql, $sid, $pid, $size * ($page - 1), $size);
        } else {
            $sqlCount = "select count(*) count from product_param where sid='%d'";
            $rstCount = M()->query($sqlCount, $sid);
            if ($rstCount === FALSE)
                $this->returnJson(0, '获取数据失败');
            $count = $rstCount[0]['count'];
            $sql = "select a.id id,a.pid pid,a.key skey,a.value value,b.name sizename,c.name type from product_param as a left join product_size as b on a.sid=b.id left join param_type as c on a.pid=c.id where a.sid='%d'  limit %d,%d";
            $rst = M()->query($sql, $sid, $size * ($page - 1), $size);
        }
        if ($rst === FALSE)
            $this->returnJson(0, '获取数据失败');
        $data['count'] = $count;
        $data['list'] = $rst;
        $this->returnJson(1, '获取数据成功', $data);
    }

    //添加产品参数
    public function addProductParam() {
        $data = I('post.');
        I('post.gid', '', INT) ? '' : $this->returnJson(0, '产品ID不正确');
        I('post.sid', '', INT) ? '' : $this->returnJson(0, '型号ID不正确');
        I('post.pid', '', INT) ? '' : $this->returnJson(0, '参数类型ID不正确');
        if (empty($data['key']))
            $this->returnJson(0, '请输入参数名');
        if (empty($data['value']))
            $this->returnJson(0, '请输入参数值');
        $rstAdd = M('product_param')->add($data);
        if (!$rstAdd)
            $this->returnJson(0, '添加数据失败~');
        $this->returnJson(1, '添加成功');
    }

    //修改产品参数
    public function setProductParam() {
        $id = (I('post.id', '', INT) ? I('post.id') : $this->returnJson(0, '参数ID不正确'));
        $data['pid'] = (I('post.pid', '', INT) ? I('post.pid') : $this->returnJson(0, '参数类型ID不正确'));
        $data['key'] = I('key');
        $data['value'] = I('value');
        if (empty($data['key']))
            $this->returnJson(0, '请填写参数名');
        if (empty($data['value']))
            $this->returnJson(0, '请填写参数值');
        $rstSet = M('product_param')->where(array('id' => $id))->save($data);
        if (!$rstSet)
            $this->returnJson(0, '操作失败');
        $this->returnJson(1, '保存成功');
    }

    //删除产品参数
    public function delProductParam() {
        $ids = i('post.ids');
        $idsStr = implode(',', $ids);
        $map['id'] = array('in', $idsStr);
        $rstDel = M('product_param')->where($map)->delete();
        if (!$rstDel)
            $this->returnJson(0, '删除数据失败');
        $this->returnJson(1, '删除成功');
    }

}
