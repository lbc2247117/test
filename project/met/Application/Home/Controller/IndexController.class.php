<?php

namespace Home\Controller;

use Think\Controller;

class IndexController extends BaseController {

    //获取首页数据
    function get_home_data() {
        $result = M('banner')->order('sort asc')->select();
        $data['banner'] = $result;
        $sql = "select a.* from product as a right join  home_goods as b on a.id=b.pid order by b.sort limit 8";
        $result = M()->query($sql);
        $data['product'] = $result;
        $result = M('factory')->limit(0, 8)->select();
        $data['factory'] = $result;
        $result = M('setting')->select();
        $data['setting'] = $result[0];
        $this->returnJson(1, '成功', $data);
    }

    //获取产品数据
    function getProduct() {
        $rst = M('product')->where(array('status' => 1))->select();
        if (!$rst) {
            $this->returnJson(0, '获取数据失败');
        }
        $data['count'] = count($rst);
        $data['list'] = $rst;
        $this->returnJson(1, '成功', $data);
    }

    //获取工厂数据
    function getFactory() {
        $rst = M('factory')->select();
        if (!$rst) {
            $this->returnJson(0, '获取数据失败');
        }
        $data['count'] = count($rst);
        $data['list'] = $rst;
        $this->returnJson(1, '成功', $data);
    }

    //获取关于我们数据
    function getAbout() {
        $rst = M('setting')->select();
        if (!$rst) {
            $this->returnJson(0, '获取数据失败');
        }
        $data = $rst[0];
        $this->returnJson(1, '成功', $data);
    }

    //获取产品信息和产品型号
    function getGoodInfo() {
        $id = I('post.id', '', INT) ? I('post.id') : $this->returnJson(0, '产品ID不正确');
        $rst = M('product')->where(array('id' => $id))->find();
        if (!$rst)
            $this->returnJson(0, '产品ID不正确');
        $data['goodinfo'] = $rst;
        $rstSize = M('product_size')->where(array('pid' => $id))->select();
        if ($rstSize === FALSE)
            $data['goodSize'] = [];
        else
            $data['goodSize'] = $rstSize;
        $this->returnJson(1, '成功', $data);
    }

    //获取产品的参数
    function getGoodParam() {
        $map['gid'] = I('post.gid', '', INT) ? I('post.gid') : $this->returnJson(0, '产品ID不正确');
        $map['sid'] = I('post.sid', '', INT) ? I('post.sid') : $this->returnJson(0, '型号ID不正确');
        $paramType = M('param_type')->where(array('status' => 1))->order('sort asc')->select();
        if ($paramType === FALSE)
            $this->returnJson(0, '获取参数类型失败');
        $goodParam = M('product_param')->where($map)->select();
        if ($goodParam === FALSE)
            $this->returnJson(0, '获取产品参数失败');
        foreach ($paramType as $key => $value) {
            $paramType[$key]['children'] = [];
            for ($i = 0; $i < count($goodParam); $i++) {
                if ($goodParam[$i]['pid'] == $value['id'])
                    $paramType[$key]['children'][] = $goodParam[$i];
            }
        }
        $data['goodParam'] = $paramType;
        $this->returnJson(1, '成功', $data);
    }

}
