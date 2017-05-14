<?php

namespace Admin\Controller;

use Think\Controller;

/**
 * 商家相关接口   
 */
defined('UPLOAD_COUPON') or define('UPLOAD_COUPON', 'picture/bsce/coupon/');        //优惠券图片
defined('UPLOAD_TICKET') or define('UPLOAD_TICKET', 'picture/bsce/ticket/');        //优惠券
defined('UPLOAD_PATH') or define('UPLOAD_PATH', 'upload/');
defined('UPLOAD_SELLER') or define('UPLOAD_SELLER', 'picture/bsce/seller/');        //优惠券图片UPLOAD_SPECIAL

class BusinessController extends BaseController {

    //查询商家列表
    public function QueryAllbuss() {
        //http://ip:port/ssh2/sceman?cmd=queryCommercialTenantVo&page&size&CommercialTenantType&lon&lat&CommercialTenantStyle&frameState&serchName
        //0：美食 1：住宿 2：购物 3：娱乐
        $page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 20 : I('post.size');
        $CommercialTenantType = I('post.CommercialTenantType') == NULL ? -1 : I('post.CommercialTenantType');
        $CommercialTenantStyle = I('post.CommercialTenantStyle') == NULL ? -1 : I('post.CommercialTenantStyle');
        $frameState = I('post.frameState') == NULL ? -1 : I('post.frameState');
        $serchName = I('post.serchName');
        $lon = $this->lon;
        $lat = $this->lat;
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantVo&page=' . $page . '&size=' . $size . '&CommercialTenantType=' . $CommercialTenantType . '&lon=' . $lon . '&lat=' . $lat;
        $apiUrl .= '&CommercialTenantStyle=' . $CommercialTenantStyle . '&frameState=' . $frameState;
        !empty($serchName) && $apiUrl .= '&serchName=' . urlencode($serchName);
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        foreach ($resultApi['result']['data'] as $key => $value) {
            //判断账号是否被激活
            $where['mogoid'] = $value['id'];
            $isAlive = M('sce_member')->where($where)->find();
            $resultApi['result']['data'][$key]['isAlive'] = !empty($isAlive) ? '已激活' : '未激活';
            $resultApi['result']['data'][$key]['mobile'] = !empty($isAlive) ? $isAlive['tel'] : '暂无';
            $resultApi['result']['data'][$key]['commercialTenantType'] = typeToName($value['commercialTenantType']);
            foreach ($value['backPic'] as $k => $v) {
                $resultApi['result']['data'][$key]['backPic'][$k] = C('IMG_PRE') . $v;
            }
            if (!is_array($resultApi['result']['data'][$key]['backPic']))
                $resultApi['result']['data'][$key]['backPic'] = '';
        }
        $this->returnJson(1, '成功', $resultApi['result']);
    }

    //查询某个商家的信息
    public function queryBuss() {
        //http://ip:port/ssh2/sceman?cmd=queryCommercialTenantVoByID&id
        $id = I('post.id');
        empty($id) && $this->returnJson(0, '参数缺失id！');
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantVoByID&id=' . $id;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        foreach ($resultApi['result']['backPic'] as $key => $value) {
            $resultApi['result']['backPic'][$key] = C('FULL_PATH') . $value;
        }
        $resultApi['result']['logo'] = C('FULL_PATH') . $resultApi['result']['logo'];
        $resultApi['result']['mapUrl'] = C('FULL_PATH') . $resultApi['result']['mapUrl'];
        //$resultApi['result']['commercialTenantType'] = typeToName($resultApi['result']['commercialTenantType']);
        $this->returnJson(1, '成功', $resultApi['result']);
    }

    //查询商家的一个店家秀
    public function querySellerxiu() {
        $id = I('post.id');
        empty($id) && $this->returnJson(0, '参数缺失id！');
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantVoByID&id=' . $id;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        $url = $resultApi['result']['url'];
        $msg = file_get_contents(UPLOAD_PATH . $url);
        if (empty($msg)) {
            $msg = '';
        }
        $this->returnJson(1, '成功', $msg);
    }

    //查询商家证件
    public function querySellercard() {
        $id = I('post.id');
        $where['mogoid'] = $id;
        empty($id) && $this->returnJson(0, '参数缺失id！');
        $model = M('sce_seller');
        $info = $model->where($where)->find();
        if ($info)
            $this->returnJson(1, '成功', $info);
        else
            $this->returnJson(1, '暂无数据');
    }

    //设置一个商家的上架下架
    public function setUp() {
        $id = I('post.id');
        $frameState = I('post.frameState');
        empty($id) && empty($frameState) && $this->returnJson(0, '参数缺失id！');
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=modifyCommercialTenantVo&id=' . $id . '&frameState=' . $frameState;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        $this->returnJson(1, '成功');
    }

    //下载表格
    public function exportSeller() {

        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantVo&page=1&size=1000&CommercialTenantType=-1&lon=' . $this->lon . '&lat=' . $this->lat . '&CommercialTenantStyle=-1&frameState=-1';
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        $th = array('商家id', '商家名称', '商家类型', '商家种类', '管理员账号', '商家状态', '联系方式');
        $arr = array();
        $i = 0;
        foreach ($resultApi['result']['data'] as $key => $value) {
            switch ($value['commercialTenantStyle']) {  //0：自营商家 1：景区商家
                case 0:
                    $commercialTenantStyle = '自营商家';
                    break;
                case 1:
                    $commercialTenantStyle = '景区商家';
                    break;
            }
            $arr[$i]['id'] = $value['id'];
            $arr[$i]['commercialTenantStyle'] = $commercialTenantStyle;
            $arr[$i]['commercialTenantType'] = typeToName($value['commercialTenantType']);
            //判断账号是否被激活
            $where['mogoid'] = $value['id'];
            $Alive = M('sce_member')->where($where)->find();
            $isAlive = !empty($Alive) ? '已激活' : '未激活';
            $arr[$i]['isAlive'] = $isAlive;
            $mobile = !empty($Alive) ? $Alive['tel'] : '暂无';
            $arr[$i]['mobile'] = $mobile;
            $arr[$i]['tel'] = $value['tel'];
            $frameState = $value['frameState'] ? '下架' : '上架';
            $arr[$i]['frameState'] = $frameState;
            $i++;
        }
        createExcel($th, $arr, '', '商家信息');
    }

    //查询商家下所有特色商品
    public function querySpecial() {
        //http://ip:port/ssh2/sceman?cmd= queryCommercialTenantProductVo&CommercialTenantContentID&page&size&name
        $id = I('post.id');
        empty($id) && $this->returnJson(0, '参数缺失id！');
        $page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 20 : I('post.size');
        $name = I('post.name');
        if (empty($id))
            $this->returnJson(0, '参数缺失id');
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantProductVo&CommercialTenantContentID=' . $id . '&page=' . $page . '&size=' . $size;
        !empty($name) && $apiUrl .= '$name=' . urlencode($name);
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        //json返回
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        if ($resultApi['flag'])
            $this->returnJson(1, '成功', $resultApi['result']);
    }

    //查询某一条特色产品信息
    public function SpecialInfo() {
        //http://ip:port/ssh2/sceman?cmd= queryCommercialTenantProductVoByID&id 
        $id = I('post.id');
        empty($id) && $this->returnJson(0, '参数缺失id！');
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantProductVoByID&id=' . $id;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if ($resultApi['flag']) {
            foreach ($resultApi['result']['pic'] as $key => $value) {
                $resultApi['result']['pic'][$key] = C('FULL_PATH') . $value;
            }
        }
        //json返回
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        if ($resultApi['flag'])
            $this->returnJson(1, '成功', $resultApi['result']);
    }

    //修改特色商品  暂时不能修改三张图片。
    public function updateSpecial() {
        //http://ip:port/ssh2/sceman?cmd= modifyCommercialTenantProductVo&pic&proName&proRemark&CommercialTenantID&id
        $proName = I('post.proName');
        $proRemark = urldecode(I('post.proRemark'));
        $id = I('post.id');
        empty($id) && $this->returnJson(0, '参数缺失id');
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=modifyCommercialTenantProductVo&id=' . $id;
        !empty($proName) && $apiUrl .= '&proName=' . urlencode($proName);
        !empty($proRemark) && $apiUrl .= '&proRemark=' . urlencode($proRemark);
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        //json返回
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        if ($resultApi['flag'])
            $this->returnJson(1, '成功');
    }

    //删除特色产品
    public function delSpecial() {
        //http://ip:port/ssh2/sceman?cmd= delCommercialTenantProductVo&id
        $id = I('post.id');
        empty($id) && $this->returnJson(0, '参数缺失id');
        foreach ($id as $key => $value) {
            $url = C('IP_BASE') . '/ssh2/sceman?cmd=delCommercialTenantProductVo&id=' . $value;
            $result = json_decode(file_get_contents($url), true);
            if ($result['flag'] == 1)
                continue;
            else
                $this->returnJson(0, '删除失败，请稍后再试');
        }
        $this->returnJson(1, '删除成功');
    }

    //新增特色商品
    public function addSpecial() {
        //http://ip:port/ssh2/sceman?cmd=addCommercialTenantProductVo&pic&proName&proRemark&CommercialTenantID
        $proName = I('post.proName');
        $proRemark = I('post.proRemark');
        $CommercialTenantID = I('post.id');
        empty($CommercialTenantID) && empty($proRemark) && empty($proName) && $this->returnJson(0, '参数缺失！');
        //上传图片
        $cover = $_FILES['covers'];
        //if(count($cover) != 3) $this->returnJson(0,'封面必须上传三张！');
        foreach ($cover['name'] as $key => $value) {
            $picfile['name'] = $value;
            $picfile['type'] = $cover['type'][$key];
            $picfile['tmp_name'] = $cover['tmp_name'][$key];
            $picfile['error'] = $cover['error'][$key];
            $picfile['size'] = $cover['size'][$key];
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = UPLOAD_SELLER;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($picfile);
            if (!empty($info)) {
                $pic['val'] = $imgpath . $info['savepath'] . $info['savename'];
                $pics[] = $pic;
            } else {
                $this->returnJson(0, '上传图片出错');
                exit;
            }
        }
        $picjson = json_encode($pics);
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=addCommercialTenantProductVo';
        $apiUrl .= '&proName=' . urlencode($proName) . '&proRemark=' . urlencode($proRemark) . '&CommercialTenantID=' . $CommercialTenantID;
        $apiUrl .= '&pic=' . $picjson;
        //echo $apiUrl;exit;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        //json返回
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        if ($resultApi['flag'])
            $this->returnJson(1, '成功');
    }

    //新增一个商家
    public function addSeller() {
        //http://ip:port/ssh2/sceman?cmd=addCommercialTenantVo&adress&comlat&comlon&lat&lon&name&remark&tel&CommercialTenantType&backPic&CommercialTenantStyle&frameState&url&cyType&CommercialTenantLableID&logo&averPrice&mapUrl
        $address = I('post.address') != NULL ? I('post.address') : $this->this->returnJson(0, '请填写地址');
        $comlon = $lon = $this->lon;
        $comlat = $lat = $this->lat;
        $tel1 = I('post.tel1');
        $name = I('post.name') != NULL ? I('post.name') : $this->this->returnJson(0, '请填写商店名字');
        $remark = I('post.remark') != NULL ? I('post.remark') : $this->this->returnJson(0, '请填写商店简介');
        $tel = I('post.tel') != NULL ? I('post.tel') : $this->this->returnJson(0, '请填写商店联系人手机号码');
        $contactPerson = I('post.contactPerson') != NULL ? I('post.contactPerson') : $this->this->returnJson(0, '请填写商店联系人');
        $type = I('post.type'); //0：美食 1：住宿 2：购物 3：娱乐
        $style = I('post.style');   //0：自营商家 1：景区商家
        $lable = I('post.lable') != NULL ? I('post.lable') : $this->this->returnJson(0, '请填写商店标签'); //标签
        $averPrice = I('post.averPrice', '-1', 'float');
        if ($averPrice == -1) {
            $this->returnJson(0, '人均价格填写错误');
        }
        //图片是：backPic 三张
        $backPic = $_FILES['backPic'];
        if (count($backPic['name']) != 3)
            $this->returnJson(0, '封面必须上传三张！');
        foreach ($backPic['name'] as $key => $value) {
            $picfile['name'] = $value;
            $picfile['type'] = $backPic['type'][$key];
            $picfile['tmp_name'] = $backPic['tmp_name'][$key];
            $picfile['error'] = $backPic['error'][$key];
            $picfile['size'] = $backPic['size'][$key];
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = UPLOAD_SELLER;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($picfile);
            if (!empty($info)) {
                $back['val'] = $imgpath . $info['savepath'] . $info['savename'];
                $backs[] = $back;
            } else {
                $this->returnJson(0, '上传图片出错1');
                exit;
            }
        }
        $backPicjson = json_encode($backs);
        //Url地图
        if (!empty($_FILES['map']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = UPLOAD_SELLER;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['map']);
            if (!empty($info)) {
                $mappath = $imgpath . $info['savepath'] . $info['savename'];
            } else {
                $this->returnJson(0, '上传图片出错2');
                exit;
            }
        } else {
            $this->returnJson(0, '请选择地图图片上传');
        }
        //logo一张
        if (!empty($_FILES['logo']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = UPLOAD_SELLER;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['logo']);
            if (!empty($info)) {
                $logopath = $imgpath . $info['savepath'] . $info['savename'];
            } else {
                $this->returnJson(0, '上传图片出错');
                exit;
            }
        } else {
            $this->returnJson(0, '请选择logo图片上传');
        }
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=addCommercialTenantVo';
        $apiUrl .= '&adress=' . urlencode($address) . '&comlat=' . $comlat . '&comlon=' . $comlon . '&lat=' . $lat . '&lon=' . $lon;
        $apiUrl .= '&name=' . urlencode($name) . '&remark=' . urlencode($remark) . '&tel=' . $tel . '&contactPerson=' . urlencode($contactPerson);
        $apiUrl .= '&CommercialTenantType=' . $type . '&CommercialTenantStyle=' . $style . '&averPrice=' . $averPrice . '&CommercialTenantLableID=' . $lable;
        $apiUrl .= '&backPic=' . $backPicjson . '&mapUrl=' . $mappath . '&logo=' . $logopath;
        $apiUrl .= '&frameState=2&url=0&cyType=0';
        !empty($tel1) && $apiUrl .= '&tel1=' . $tel1;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        //json返回
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        if ($resultApi['flag'])
            $this->returnJson(1, '成功');
    }

    //修改商家信息,暂时不予许修改背景图
    public function updateSeller() {
        //&backPic&logo&mapUrl
        $param = I('post.');
        $param['remark'] = urldecode($param['remark']);
        $param = myurlencode($param);
        extract($param);
        empty($id) && $this->returnJson(0, '参数缺失id');
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=modifyCommercialTenantVo&id=' . $id;
        !empty($address) && $apiUrl .= '&adress=' . $address;
        !empty($name) && $apiUrl .= '&name=' . $name;
        !empty($remark) && $apiUrl .= '&remark=' . $remark;
        !empty($tel) && $apiUrl .= '&tel=' . $tel;
        !empty($contactPerson) && $apiUrl .= '&contactPerson=' . $contactPerson;
        !empty($type) && $apiUrl .='&CommercialTenantType=' . $type;
        !empty($style) && $apiUrl .= '&CommercialTenantStyle=' . $style;
        !empty($lable) && $apiUrl .= '&CommercialTenantLableID=' . $lable;
        //传空值则清空座机号码
        $apiUrl .= '&tel1=' . $tel1;
        $averPrice = I('post.averPrice', '-1', 'float');
        if ($averPrice == -1) {
            $this->returnJson(0, '人均价格填写错误');
        }
        !empty($averPrice) && $apiUrl .= '&averPrice=' . $averPrice;
        //Url地图  logo一张
        if (!empty($_FILES['map']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = UPLOAD_SELLER;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['map']);
            if (!empty($info)) {
                $mappath = $imgpath . $info['savepath'] . $info['savename'];
                $apiUrl .= '&mapUrl=' . $mappath;
            } else {
                $this->returnJson(0, '上传地图图片出错');
                exit;
            }
        }
        //logo一张
        if (!empty($_FILES['logo']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = UPLOAD_SELLER;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['logo']);
            if (!empty($info)) {
                $logopath = $imgpath . $info['savepath'] . $info['savename'];
                $apiUrl .= '&logo=' . $logopath;
            } else {
                $this->returnJson(0, '上传图片出错');
                exit;
            }
        }
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        //json返回
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        if ($resultApi['flag'])
            $this->returnJson(1, '成功');
    }

    //商家删除
    public function delSeller() {
        //http://ip:port/ssh2/sceman?cmd=delCommercialTenantVo&id
        $id = I('post.id');
        empty($id) && $this->returnJson(0, '参数缺失id');
        foreach ($id as $key => $value) {
            $url = C('IP_BASE') . '/ssh2/sceman?cmd=delCommercialTenantVo&id=' . $value;
            $result = json_decode(file_get_contents($url), true);
            if ($result['flag'] == 1)
                continue;
            else
                $this->returnJson(0, '删除失败，请稍后再试');
        }
        $this->returnJson(1, '删除成功');
    }

    //修改商家的店家秀
    public function alertSellerxiu() {
        $id = I('post.id');
        $msg = $_REQUEST['msg'];
        //允许为空  empty($msg) && $this->returnJson(0, '请填写数据');
        $path = UPLOAD_SELLER . time() . '.txt';
        if (!file_exists(UPLOAD_PATH . UPLOAD_SELLER)) {
            mkdir(UPLOAD_PATH . UPLOAD_SELLER, 0777, true);
        }
        file_put_contents(UPLOAD_PATH . $path, $msg);
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=modifyCommercialTenantVo&id=' . $id . '&url=' . $path;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        $this->returnJson(1, '成功');
    }

    //所有商家的评论体系
    public function queryAllComment() {
        //http://ip:port/ssh2/sceman?cmd= queryCommercialTenantVoByNum&page&size&lon&lat&CommercialTenantType
        $lon = $this->lon;
        $lat = $this->lat;
        $page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 20 : I('post.size');
        $type = I('post.type'); //0：美食 1：住宿
        $name = I('post.name'); //商家的关键字搜索
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantVoByNum&page=' . $page . '&size=' . $size;
        $apiUrl .= '&lon=' . $lon . '&lat=' . $lat;
        !is_null($type) && $apiUrl .= '&CommercialTenantType=' . $type;
        !is_null($name) && $apiUrl .= '&name=' . $name;

        $resultApi = json_decode(file_get_contents($apiUrl), true);
        foreach ($resultApi['result']['data'] as $key => $value) {
            $temp['id'] = $value['id'];
            $temp['name'] = $value['name'];
            $temp['commercialTenantType'] = $value['commercialTenantType'];
            $temp['aveScore'] = $value['aveScore'];
            $temp['clickNum'] = $value['clickNum'];
            $temp['orderNum'] = $value['orderNum'];
            $temps[] = $temp;
        }
        $tempall['data'] = $temps;
        $tempall['num'] = $resultApi['result']['num'];
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        if ($resultApi['flag'])
            $this->returnJson(1, '成功', $tempall);
    }

    //商家的评论
    public function queryComment() {
        //http://ip:port/ssh2/sceman?cmd= queryCommercialTenantContentVo&CommercialTenantContentID&page&size&state
        $id = I('post.id');
        empty($id) && $this->returnJson(0, '参数缺失id');
        $page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 20 : I('post.size');
        $state = I('post.state') == NULL ? 0 : I('post.state');
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantContentVo&CommercialTenantContentID=' . $id;
        $apiUrl .= '&page=' . $page . '&size=' . $size . '&state=' . $state;

        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        if ($resultApi['flag'])
            $this->returnJson(1, '成功', $resultApi['result']);
    }

    //批量删除商家评论
    public function delComment() {
        //http://ip:port/ssh2/sceman?cmd=delCommercialTenantContentVo&id
        $id = I('post.id');
        empty($id) && $this->returnJson(0, '参数缺失id');
        foreach ($id as $key => $value) {
            $url = C('IP_BASE') . '/ssh2/sceman?cmd=delCommercialTenantContentVo&id=' . $value;
            $result = json_decode(file_get_contents($url), true);
            if ($result['flag'] == 1)
                continue;
            else
                $this->returnJson(0, '删除失败，请稍后再试');
        }
        $this->returnJson(1, '删除成功');
    }

    //获取商家标签
    public function lableInfo() {
        $page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 50 : I('post.size');
        $lableType = I('post.lableType');   //0-美食，1-住宿
        //http://ip:port/ssh2/sceman?cmd=queryCommercialTenantLableVo&page&size
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantLableVo&page=' . $page . '&size=' . $size;
        !is_null($lableType) && $apiUrl .= '&lableType=' . $lableType;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        if ($resultApi['flag'])
            $this->returnJson(1, '成功', $resultApi['result']);
    }

    //批量导入评论信息
    public function importComment() {
        //http://ip:port/ssh2/sceman?cmd=addCommercialTenantContentVo&content&CommercialTenantID&contentXj&star
        header("Content-type: text/html; charset=utf-8");
        $id = I('post.id');
        empty($id) && $this->returnJson(0, '参数缺失id');
        $filename = $_FILES['file']['tmp_name'];
        empty($filename) && $this->returnJson(0, '请选择要导入的CSV文件！');
        $handle = fopen($filename, 'r');
        $result = input_csv($handle); //解析csv
        foreach ($result as $key => $value) {
            if ($key == 0)
                continue;
            $star = iconv('gb2312', 'utf-8', $result[$key][0]); //中文转码  
            $contentXj = iconv('gb2312', 'utf-8', $result[$key][1]); //中文转码  
            $content = iconv('gb2312', 'utf-8', $result[$key][2]); //中文转码  
            $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=addCommercialTenantContentVo';
            $apiUrl .= '&content=' . $content . '&CommercialTenantID=' . $id . '&contentXj=' . $contentXj . '&star=' . $star;
            $resultApi = json_decode(file_get_contents($apiUrl), true);
            if (!$resultApi['flag'])
                break;
        }
        $this->returnJson(1, '成功');
    }

    //修改封面的图片的某一张
    public function updateCover() {
        //上传一张图片
        $id = I('post.id');
        empty($id) && $this->returnJson(0, '参数缺失id');
        $num = I('post.num');
        switch ($num) {
            case 1:
                $filename = 'cover1';
                break;
            case 2:
                $filename = 'cover2';
                break;
            case 3:
                $filename = 'cover3';
                break;

            default:
                $this->returnJson(0, 'num只能1,2,3');
                break;
        }

        if (!empty($_FILES[$filename]['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = UPLOAD_SELLER;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES[$filename]);
            if (!empty($info)) {
                $picpath = $imgpath . $info['savepath'] . $info['savename'];
            } else {
                $this->returnJson(0, '上传图片出错');
                exit;
            }
        }
        $apisel = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantVoByID&id=' . $id;
        $resultSel = json_decode(file_get_contents($apisel), true);
        if ($resultSel['flag']) {
            !empty($resultSel['result']['backPic']) && $oldimgs = $resultSel['result']['backPic'];
        }
        $oldimgs[$num - 1] = $picpath;
        foreach ($oldimgs as $key => $value) {
            $file['val'] = $value;
            $files[] = $file;
        }
        $fileJson = json_encode($files);
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=modifyCommercialTenantVo&id=' . $id . '&backPic=' . $fileJson;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        if ($resultApi['flag'])
            $this->returnJson(1, '成功');
    }

    //修改特色产品的某一张图片
    public function updateSpecialcover() {
        //http://ip:port/ssh2/sceman?cmd=modifyCommercialTenantProductVo&pic&proName&proRemark&CommercialTenantID&id
        $id = I('post.id');
        empty($id) && $this->returnJson(0, '参数缺失id');
        $num = I('post.num');
        switch ($num) {
            case 1:
                $filename = 'cover1';
                break;
            case 2:
                $filename = 'cover2';
                break;
            case 3:
                $filename = 'cover3';
                break;

            default:
                $this->returnJson(0, 'num只能1,2,3');
                break;
        }

        //上传一张图片
        if (!empty($_FILES[$filename]['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = UPLOAD_SELLER;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES[$filename]);
            if (!empty($info)) {
                $picpath = $imgpath . $info['savepath'] . $info['savename'];
            } else {
                $this->returnJson(0, '上传图片出错');
                exit;
            }
        }
        $apisel = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantProductVoByID&id=' . $id;
        $resultSel = json_decode(file_get_contents($apisel), true);
        if ($resultSel['flag']) {
            !empty($resultSel['result']['pic']) && $oldimgs = $resultSel['result']['pic'];
        }
        $oldimgs[$num - 1] = $picpath;
        foreach ($oldimgs as $key => $value) {
            $file['val'] = $value;
            $files[] = $file;
        }
        $fileJson = json_encode($files);
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=modifyCommercialTenantProductVo&id=' . $id . '&pic=' . $fileJson;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        if ($resultApi['flag'])
            $this->returnJson(1, '成功');
    }

    //修改特色产品的图片（新）
    public function updateSpecialcovertemp() {
        $id = I('post.id');
        $path = I('post.path');
        empty($id) && $this->returnJson(0, '参数缺失id');
        //上传一张图片
        if (!empty($_FILES['cover']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = UPLOAD_SELLER;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['cover']);
            if (!empty($info)) {
                $picpath = $imgpath . $info['savepath'] . $info['savename'];
            } else {
                $this->returnJson(0, '上传图片出错');
                exit;
            }
        } else {
            $this->returnJson(0, '请选择图片');
        }
        $apisel = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantProductVoByID&id=' . $id;
        $resultSel = json_decode(file_get_contents($apisel), true);
        if ($resultSel['flag']) {
            !empty($resultSel['result']['pic']) && $oldimgs = $resultSel['result']['pic'];
        }
        $truePath = str_replace(C('FULL_PATH'), '', $path);
        if (!empty($path)) {
            //修改
            foreach ($oldimgs as $key => $value) {
                $file['val'] = $value == $truePath ? $picpath : $value;
                $files[] = $file;
            }
        } else {
            //新增
            foreach ($oldimgs as $key => $value) {
                $file['val'] = $value;
                $files[] = $file;
            }
            $file['val'] = $picpath;
            $files[] = $file;
        }

        $fileJson = json_encode($files);
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=modifyCommercialTenantProductVo&id=' . $id . '&pic=' . $fileJson;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        if ($resultApi['flag'])
            $this->returnJson(1, '成功');
    }

    //删除特色产品的一张图片
    public function delSpecialcover() {
        //http://ip:port/ssh2/sceman?cmd=modifyCommercialTenantProductVo&pic&proName&proRemark&CommercialTenantID&id
        $id = I('post.id');
        $path = I('post.path');
        empty($id) && empty($path) && $this->returnJson(0, '参数缺失id');
        $apisel = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantProductVoByID&id=' . $id;
        $resultSel = json_decode(file_get_contents($apisel), true);
        if ($resultSel['flag']) {
            !empty($resultSel['result']['pic']) && $oldimgs = $resultSel['result']['pic'];
        }
        count($oldimgs) == 1 && $this->returnJson(0, '仅有一张图片不能删除哦~');
        $truePath = str_replace(C('FULL_PATH'), '', $path);
        foreach ($oldimgs as $key => $value) {
            if ($value == $truePath)
                continue;
            $file['val'] = $value;
            $files[] = $file;
        }
        $fileJson = json_encode($files);
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=modifyCommercialTenantProductVo&id=' . $id . '&pic=' . $fileJson;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        if ($resultApi['flag'])
            $this->returnJson(1, '成功');
    }

    //商家的优惠券
    public function queryTicket() {
        //http://ip:port/ssh2/sceman?cmd=queryCommercialTenantVoucherVo&page&size&commercialTenantID&lon&lat
        $page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 20 : I('post.size');
        $voucherName = I('post.serchName');
        $id = I('post.id');
        empty($id) && $this->returnJson(0, '参数缺失id');
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantVoucherVo&page=' . $page . '&size=' . $size . '&commercialTenantID=' . $id . '&voucherName=' . $voucherName;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        foreach ($resultApi['result']['data'] as $key => $value) {
            $resultApi['result']['data'][$key]['picUrl'] = C('IMG_PRE') . $value['picUrl'];
            $time = explode('~', $value['useTime']);
            $times = strtotime($time[0]);
            $timee = strtotime($time[1]);
            $now = time();
            //if($times < $now && $now < $timee)
            if ($now < $timee)
                $resultApi['result']['data'][$key]['beyond'] = 1;
            else
                $resultApi['result']['data'][$key]['beyond'] = 0;
        }
        $this->returnJson(1, '成功', $resultApi['result']);
    }

    //新增一张优惠券
    public function addTicket() {
        //http://ip:port/ssh2/sceman?cmd=addCommercialTenantVoucherVo&commercialTenantID&picUrl&remark&voucherName&zk&lon&lat&useTime
        $id = I('post.id');
        $voucherName = I('post.voucherName');
        empty($id) && empty($voucherName) && $this->returnJson(0, '请检查未提交的数据。');
        $zk = I('post.zk', '-1', 'float');
        if ($zk == -1) {
            $this->returnJson(0, '折扣填写错误');
        }
        $useTime = I('post.useTime');
        //上传图片
        if (!empty($_FILES['cover']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = UPLOAD_TICKET;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['cover']);
            if (!empty($info)) {
                $picUrl = $imgpath . $info['savepath'] . $info['savename'];
            } else {
                $this->returnJson(0, '上传图片出错');
                exit;
            }
        } else {
            $this->returnJson(0, '请选择图片上传');
        }
        //保存描述文件
        $msg = $_REQUEST['msg'];
        $msg = empty($msg) ? '' : $msg;
        $pathRe = UPLOAD_TICKET . 'msg/' . date("Y-m-d") . '/';
        $fileRe = time() . '.txt';
        if (!file_exists(UPLOAD_PATH . $pathRe)) {
            mkdir(UPLOAD_PATH . $pathRe, 0777, true);
        }
        $path = $pathRe . $fileRe;
        file_put_contents(UPLOAD_PATH . $path, $msg);

        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=addCommercialTenantVoucherVo&state=1&commercialTenantID=' . $id . '&picUrl=' . urlencode($picUrl);
        $apiUrl .= '&remark=' . $path . '&voucherName=' . urlencode($voucherName) . '&zk=' . $zk . '&lon=' . $this->lon . '&lat=' . $this->lat . '&useTime=' . $useTime;
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        if ($resultApi['flag'])
            $this->returnJson(1, '成功');
    }

    //修改一张优惠券信息
    public function alertTicket() {
        //http://ip:port/ssh2/sceman?cmd=modifyCommercialTenantVoucherVo&commercialTenantID&picUrl&remark&voucherName&zk&id&lon&lat&useTime
        $id = I('post.id');
        empty($id) && $this->returnJson(0, 'id必须传入');
        //保存描述文件
        $msg = $_REQUEST['msg'];
        $pathRe = UPLOAD_TICKET . 'msg/' . date("Y-m-d") . '/';
        $fileRe = time() . '.txt';
        if (!file_exists(UPLOAD_PATH . $pathRe)) {
            mkdir(UPLOAD_PATH . $pathRe, 0777, true);
        }
        $path = $pathRe . $fileRe;
        !empty($msg) && file_put_contents(UPLOAD_PATH . $path, $msg);

        $voucherName = I('post.voucherName');
        $zk = I('post.zk', '-1', 'float');
        if ($zk == -1) {
            $this->returnJson(0, '折扣填写错误');
        }
        $state = I('post.state');
        $useTime = I('post.useTime');
        $cid = $this->sellerId;
        $apiUrl = C('IP_BASE') . '/ssh2/sceman?cmd=modifyCommercialTenantVoucherVo&commercialTenantID=' . $cid . '&id=' . $id . '&lon=' . $this->lon . '&lat=' . $this->lat;
        ;
        !empty($msg) && $apiUrl .= '&remark=' . $path;
        !empty($voucherName) && $apiUrl .= '&voucherName=' . urlencode($voucherName);
        !is_null($state) && $apiUrl .= '&state=' . $state;
        !empty($zk) && $apiUrl .= '&zk=' . $zk;
        !empty($useTime) && $apiUrl .= '&useTime=' . $useTime;

        //上传图片
        if (!empty($_FILES['cover']['name'])) {
            $upload = new \Think\Upload();
            $upload->maxSize = 2097152; // 2M
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            $imgpath = UPLOAD_TICKET;
            $upload->rootPath = UPLOAD_PATH . $imgpath;
            if (!file_exists($upload->rootPath)) {
                mkdir($upload->rootPath, 0777, true);
            }
            $upload->savePath = '';
            $upload->autoSub = true;
            $upload->subName = array('date', 'Y-m-d');
            $info = $upload->uploadOne($_FILES['cover']);
            if (!empty($info)) {
                $picUrl = $imgpath . $info['savepath'] . $info['savename'];
                $apiUrl .= '&picUrl=' . $picUrl;
            } else {
                $this->returnJson(0, '上传图片出错');
                exit;
            }
        }
        $resultApi = json_decode(file_get_contents($apiUrl), true);
        if (!$resultApi['flag'])
            $this->returnJson(0, $resultApi['result']);
        if ($resultApi['flag'])
            $this->returnJson(1, '成功');
    }

    //删除优惠券
    public function delTicket() {
        //http://ip:port/ssh2/sceman?cmd=delCommercialTenantVoucherVo&id
        $id = I('post.id');
        if (empty($id))
            $this->returnJson(0, '参数缺失');
        foreach ($id as $key => $value) {
            $url = C('IP_BASE') . '/ssh2/sceman?cmd=delCommercialTenantVoucherVo&id=' . $value;
            $result = json_decode(file_get_contents($url), true);
            if ($result['flag'] == 1)
                continue;
            else
                $this->returnJson(0, '删除失败，请稍后再试');
        }
        $this->returnJson(1, '删除成功');
    }

    //根据优惠券id查询优惠券详细信息
    public function selTIcket() {
        //http://ip:port/ssh2/sceman?cmd=queryCommercialTenantVoucherVoByID&id
        $id = I('post.id');
        if (empty($id)) {
            $this->returnJson(0, '数据缺失');
        }
        $url = C('IP_BASE') . '/ssh2/sceman?cmd=queryCommercialTenantVoucherVoByID&id=' . $id;
        $result = json_decode(file_get_contents($url), true);
        if ($result['flag'] == 1) {
            $result['result']['useTime'] = explode("~", $result['result']['useTime']);
            $msg = file_get_contents(UPLOAD_PATH . $result['result']['remark']);
            $result['result']['remark'] = $msg ? $msg : '';
            $result['result']['picUrl'] = C('FULL_PATH') . $result['result']['picUrl'];
            $this->returnJson(1, '成功', $result['result']);
        } else
            $this->returnJson(0, '查询出错');
    }

}
