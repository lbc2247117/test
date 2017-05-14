<?php
namespace Business\Controller;
use Think\Controller;
//商家自营系统

defined('UPLOAD_PATH') or define('UPLOAD_PATH', 'upload/');        //文件目录
defined('UPLOAD_TICKET') or define('UPLOAD_TICKET', 'picture/bsce/ticket/');        //优惠券
defined('UPLOAD_SELLER') or define('UPLOAD_SELLER', 'picture/bsce/seller/');        //商家
defined('UPLOAD_TMP_PATH') or define('UPLOAD_TMP_PATH', 'tmp/');    //图片上传的临时目录
defined('UPLOAD_SELLERCARD') or define('UPLOAD_SELLERCARD', 'picture/bsce/sellerxiu/');        //商家
defined('UPLOAD_ORG_PATH') or define('UPLOAD_ORG_PATH', 'org/');    //原始图片保存目录
defined('THUMB_PREFIX') or define('THUMB_PREFIX', '600_600_');     //裁剪图片的前缀
defined('THUMB_SIZE') or define('THUMB_SIZE', 600);                //裁剪的大小规格
defined('UPLOAD_THUMB_PATH') or define('UPLOAD_THUMB_PATH', 'thumb/');      //裁剪后图片保存目录
class SellerController extends BaseController {
    //查询商家店家秀的基本信息
    public function queryBuss(){
        //http://ip:port/ssh2/sceman?cmd=queryCommercialTenantVoByID&id
        $id = $this->sellerId;
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantVoByID&id='.$id;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
        foreach ($resultApi['result']['backPic'] as $key => $value) {
            $resultApi['result']['backPic'][$key] = C('FULL_PATH').$value;
        }
        $resultApi['result']['commercialTenantType'] = typeToName($resultApi['result']['commercialTenantType']);
        $this->returnJson(1,'成功',$resultApi['result']);
    }

    //修改店家的基本信息,商家的3-5张图片上传单独处理
    public function alertBuss(){
        //http://ip:port/ssh2/sceman?cmd=modifyCommercialTenantVo&adress&comlat&comlon&lat&lon&name&remark&tel&CommercialTenantType&id&CommercialTenantStyle&backPic&frameState
        $name = I('poat.name');
        $type = I('post.type');
        $adress = I('post.adress');
        $remark = I('post.remark');
        $tel = I('post.tel');
        $id = $this->sellerId;
        //拼接url
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=modifyCommercialTenantVo&id='.$id;
        !empty($name) && $apiUrl .= '&name='.urlencode($name);
        !empty($type) && $apiUrl .= '&CommercialTenantType='.trim($type);
        !empty($adress) && $apiUrl .= '&adress='.urlencode($adress);
        !empty($remark) && $apiUrl .= '&remark='.urlencode($remark);
        !empty($tel) && $apiUrl .= '&tel='.trim($tel);
        //调用接口
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
        if($resultApi['flag']) $this->returnJson(1,'成功');
    }

    //上传商家背景图片
    public function uploadCover(){

        if (!empty($_FILES)) {
            $maxFileAge = 5 * 3600; // Temp file age in seconds
            $tmpPath=UPLOAD_PATH.UPLOAD_SELLER.UPLOAD_TMP_PATH;
            if (!file_exists($tmpPath)) {
                @mkdir($tmpPath, 0777, true);
            }
            $ext = "jpg";
            if (isset($_REQUEST["name"])) {
                $ext = pathinfo($_REQUEST["name"], PATHINFO_EXTENSION);
            } elseif (!empty($_FILES)) {
                $ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
            }
            $fileName = uniqid("") . '.' . $ext;
            $filePath = $tmpPath . '/' . $fileName;
            $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
            $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;
            if (!is_dir($tmpPath) || !$dir = opendir($tmpPath)) {
                echo ('{"status": "1", "msg" : {"message": "打开tm目录失败."}}');
                return;
            }
            //清理临时目录中的临时文件
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $tmpPath . '/' . $file;
                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                    continue;
                }
                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
            // Open temp file
            if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
                echo ('{"status": "0", "msg" : {"message": "Failed to open output stream."}}');
                return;
            }
            closedir($dir);
            // Open temp file
            if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
                echo ('{"status": "0", "msg" : {"message": "Failed to open output stream."}}');
                return;
            }
            //读取上传文件的分片文件
            if (!empty($_FILES)) {
                if ($_FILES["file"]["msg"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                    echo ('{"status": "0", "msg" : {"message": "Failed to move uploaded file."}}');
                    return;
                }
                // Read binary input stream and append it to temp file
                if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                    echo ('{"status": "0", "msg" : {"message": "Failed to open input stream."}}');
                    return;
                }
            } else {
                if (!$in = @fopen("php://input", "rb")) {
                    echo ('{"status": "0", "msg" : {"message": "Failed to open input stream."}}');
                    return;
                }
            }
            while ($buff = fread($in, 4096)) {
                fwrite($out, $buff);
            }
            @fclose($out);
            @fclose($in);
            //改名
            rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");

            $index = 0;
            $done = true;
            for ($index = 0; $index < $chunks; $index++) {
                if (!file_exists("{$filePath}_{$index}.part")) {
                    $done = false;
                    break;
                }
            }
            if ($done) {
                $orgPath = UPLOAD_PATH.UPLOAD_SELLER . UPLOAD_ORG_PATH;
                //创建原始图目录
                if (!file_exists($orgPath)) {
                    @mkdir($orgPath, 0777, true);
                }
                if (!$out = @fopen($orgPath . '/' . $fileName, "wb")) {
                    echo ('{"status": "0", "msg" : {"message": "Failed to open output stream."}}');
                    return;
                }
                if (flock($out, LOCK_EX)) {
                    for ($index = 0; $index < $chunks; $index++) {
                        if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
                            break;
                        }
                        while ($buff = fread($in, 4096)) {
                            fwrite($out, $buff);
                        }
                        @fclose($in);
                        @unlink("{$filePath}_{$index}.part");
                    }
                    flock($out, LOCK_UN);
                }
                @fclose($out);
                //判断是否有图
                if ($orgPath) {
                    $image = new \Think\Image();
                    $image->open($orgPath . '/' . $fileName);
                    // 按照原图的比例生成一个最大为缩略图尺寸的缩略图并保存为.jpg
                    $thumbPath = UPLOAD_PATH.UPLOAD_SELLER . UPLOAD_THUMB_PATH;
                    //创建缩略图目录
                    if (!file_exists($thumbPath)) {
                        @mkdir($thumbPath, 0777, true);
                    }
                    $thunmpathname = $thumbPath . '/' . THUMB_PREFIX . $fileName;
                    $image->thumb(THUMB_SIZE, THUMB_SIZE)->save($thunmpathname);
                    //将图片存放在数据库中去
                    $thumbname = THUMB_PREFIX . $fileName;
                    $id=$this->sellerId;
                    $picUrl[]=UPLOAD_SELLER.UPLOAD_THUMB_PATH.$thumbname;
                    $apisel = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantVoByID&id='.$id;
                    $resultSel = json_decode(file_get_contents($apisel),true);
                    if($resultSel['flag']){
                        !empty($resultSel['result']['backPic']) && $oldimgs = $resultSel['result']['backPic'];
                    }
                    $allimg = array_merge($picUrl,$oldimgs);
                    foreach ($allimg as $key => $value) {
                        $file['val'] = $value;
                        $files[] = $file;
                    }
                    $fileJson = json_encode($files);
                    //更新
                    $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=modifyCommercialTenantVo&id='.$id.'&backPic='.$fileJson;
                    $resultApi = json_decode(file_get_contents($apiUrl),true);
                    if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
                    if($resultApi['flag']) $this->returnJson(1,'成功');
                }else {
                    echo ('{"status": "0", "msg" : {"message": "Failed to open org file."}}');
                }
            }
        }
    }

    //删除一张商家背图片
    public function delOnepic(){
        $path = I('post.path');
        $pic = str_replace(C('FULL_PATH'), '', $path);
        $id = $this->sellerId;
        $apisel = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantVoByID&id='.$id;
        $resultSel = json_decode(file_get_contents($apisel),true);
        if($resultSel['flag']){
            $oldimgs = $resultSel['result']['backPic'];
        }
        //至少要有三张图片
        if(count($oldimgs) <= 3) $this->returnJson(0,'图片需保证三张以上，不支持再删除');
        //遍历循环，删除对应的那种图片
        foreach ($oldimgs as $key => $value) {
            if ($value == $pic) continue;
            $file['val'] = $value;
            $files[] = $file;
        }
        $fileJson = json_encode($files);
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=modifyCommercialTenantVo&id='.$id.'&backPic='.$fileJson;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
        if($resultApi['flag']) $this->returnJson(1,'成功');

    }

    //查询商家的一个店家秀
    public function querySellerxiu(){
        $id = $this->sellerId;
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantVoByID&id='.$id;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
        $url = $resultApi['result']['url'];
        $msg = file_get_contents(UPLOAD_PATH.$url);
        if(!$msg || empty($msg)){
            $msg = '';
        }
        $this->returnJson(1,'成功',$msg);
    }

    //修改商家的店家秀
    public function alertSellerxiu(){
        $id = $this->sellerId;
        $msg = $_REQUEST['msg'];
        empty($msg) && $this->returnJson(0,'请填写数据');
        $path = UPLOAD_SELLER.time().'.txt';
        if (!file_exists(UPLOAD_PATH.UPLOAD_SELLER)) {
            mkdir(UPLOAD_PATH.UPLOAD_SELLER, 0777, true);
        }
        file_put_contents(UPLOAD_PATH.$path, $msg);
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=modifyCommercialTenantVo&id='.$id.'&url='.$path;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
        $this->returnJson(1,'成功');
    }

    //商家的优惠券
    public function queryTicket(){
        //http://ip:port/ssh2/sceman?cmd=queryCommercialTenantVoucherVo&page&size&commercialTenantID&lon&lat
        $page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 20 : I('post.size');
        $voucherName = I('post.serchName') ;
        $id = $this->sellerId;
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantVoucherVo&page='.$page.'&size='.$size.'&commercialTenantID='.$id.'&voucherName='.$voucherName;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
        foreach ($resultApi['result']['data'] as $key => $value) {
            $resultApi['result']['data'][$key]['picUrl'] = C('IMG_PRE').$value['picUrl'];
            $time = explode('~', $value['useTime']);
            $times = strtotime($time[0]);
            $timee = strtotime($time[1]);
            $now = time();
            if($times < $now && $now < $timee)
                $resultApi['result']['data'][$key]['beyond'] = 1;
            else
                $resultApi['result']['data'][$key]['beyond'] = 0;
        }
        $this->returnJson(1,'成功',$resultApi['result']);
    }

    //新增一张优惠券
    public function addTicket(){
        //http://ip:port/ssh2/sceman?cmd=addCommercialTenantVoucherVo&commercialTenantID&picUrl&remark&voucherName&zk&lon&lat&useTime
        $id = $this->sellerId;
        $voucherName = I('post.voucherName');
        $zk = I('post.zk');
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
                $this->returnJson(0,'上传图片出错');
                exit;
            }
        }else{
            $this->returnJson(0,'请选择图片上传');
        }
        //保存描述文件
        $msg = $_REQUEST['msg'];
        $msg = empty($msg) ? '' : $msg;
        $pathRe = UPLOAD_TICKET.'msg/'.date("Y-m-d").'/';
        $fileRe = time().'.txt';
        if (!file_exists(UPLOAD_PATH.$pathRe)) {
            mkdir(UPLOAD_PATH.$pathRe, 0777, true);
        }
        $path = $pathRe.$fileRe;
        file_put_contents(UPLOAD_PATH.$path, $msg);

        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=addCommercialTenantVoucherVo&state=1&commercialTenantID='.$id.'&picUrl='.urlencode($picUrl);
        $apiUrl .= '&remark='.$path.'&voucherName='.$voucherName.'&zk='.$zk.'&lon='.$this->lon.'&lat='.$this->lat.'&useTime='.$useTime;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
        if($resultApi['flag']) $this->returnJson(1,'成功');
    }

    //修改一张优惠券信息
     public function alertTicket(){
        //http://ip:port/ssh2/sceman?cmd=modifyCommercialTenantVoucherVo&commercialTenantID&picUrl&remark&voucherName&zk&id&lon&lat&useTime
        $id = I('post.id');
        empty($id) && $this->returnJson(0,'id必须传入');
        //保存描述文件
        $msg = $_REQUEST['msg'];
        $pathRe = UPLOAD_TICKET.'msg/'.date("Y-m-d").'/';
        $fileRe = time().'.txt';
        if (!file_exists(UPLOAD_PATH.$pathRe)) {
            mkdir(UPLOAD_PATH.$pathRe, 0777, true);
        }
        $path = $pathRe.$fileRe;
        !empty($msg) && file_put_contents(UPLOAD_PATH.$path, $msg);

        $voucherName = I('post.voucherName');
        $zk = I('post.zk');
        $state = I('post.state');
        $useTime = I('post.useTime');
        $cid = $this->sellerId;
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=modifyCommercialTenantVoucherVo&commercialTenantID='.$cid.'&id='.$id.'&lon='.$this->lon.'&lat='.$this->lat;;
        !empty($msg) && $apiUrl .= '&remark='.$path;
        !empty($voucherName) && $apiUrl .= '&voucherName='.$voucherName;
        !is_null($state) && $apiUrl .= '&state='.$state;
        !empty($zk) && $apiUrl .= '&zk='.$zk;
        !empty($useTime) && $apiUrl .= '&useTime='.$useTime;

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
                $apiUrl .= '&picUrl='.$picUrl;
            } else {
                $this->returnJson(0,'上传图片出错');
                exit;
            }
        }
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
        if($resultApi['flag']) $this->returnJson(1,'成功');
    }

    //删除优惠券
    public function delTicket(){
        //http://ip:port/ssh2/sceman?cmd=delCommercialTenantVoucherVo&id
        $id = I('post.id');
        if(empty($id)) $this->returnJson(0,'参数缺失');
        foreach ($id as $key => $value) {
            $url = C('IP_BASE').'/ssh2/sceman?cmd=delCommercialTenantVoucherVo&id='.$value;
            $result = json_decode(file_get_contents($url),true);
            if($result['flag'] == 1) continue;
            else $this->returnJson(0,'删除失败，请稍后再试');
        }
        $this->returnJson(1,'删除成功');
    }


    //根据优惠券id查询优惠券详细信息
    public function selTIcket(){
        //http://ip:port/ssh2/sceman?cmd=queryCommercialTenantVoucherVoByID&id
        $id=I('post.id');
        if(empty($id)){
            $this->returnJson(0,'数据缺失');
        }
        $url=C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantVoucherVoByID&id='.$id;
        $result=json_decode(file_get_contents($url),true);
        if($result['flag'] == 1){
            $result['result']['useTime'] = explode("~",$result['result']['useTime']);
            $msg = file_get_contents(UPLOAD_PATH.$result['result']['remark']);
            $result['result']['remark'] = $msg ? $msg : '';
            $result['result']['picUrl'] = C('FULL_PATH').$result['result']['picUrl'];
            $this->returnJson(1,'成功',$result['result']);
        }

        else $this->returnJson(0,'查询出错');
    }

    //店铺订单列表
    public function queryOrder(){
        //http://ip:port/ssh2/sceman?cmd=queryCommercialTenantOrderVo&CommercialTenantContentID&page&size&state
        $id = $this->sellerId;
        $page = I('post.page') == NULL ? 1 : I('post.page');
        $size = I('post.size') == NULL ? 20 : I('post.size');
        $state = I('post.state');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantOrderVo&CommercialTenantContentID='.$id.'&page='.$page.'&size='.$size;
        !empty($state) && $apiUrl .= '&state='.$state;
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
        if($resultApi['flag']) $this->returnJson(1,'成功',$resultApi['result']);
    }

    //删除订单
    public function delOrder(){
        //http://ip:port/ssh2/sceman?cmd=delCommercialTenantOrderVo&id
        $id = I('post.id');
        //判断订单是否是未接受状态
        //http://ip:port/ssh2/sceman?cmd= queryCommercialTenantOrderVoByID&id
        if(empty($id)) $this->returnJson(0,'参数缺失');
        foreach ($id as $key => $value) {
            $id = $value;
            $apiInfo = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantOrderVoByID&id='.$id;
            $resultInfo = json_decode(file_get_contents($apiInfo),true);
            if($resultInfo['result']['orderState'] == '1') $this->returnJson(0,$resultInfo['result']['name']."的订单未接单，不允许删除！");
        }
        foreach ($id as $key => $value) {
            $url = C('IP_BASE').'/ssh2/sceman?cmd=delCommercialTenantOrderVo&id='.$value;
            $result = json_decode(file_get_contents($url),true);
            if($result['flag'] == 1) continue;
            else $this->returnJson(0,'删除失败，请稍后再试');
        }
        $this->returnJson(1,'删除成功');
    }

    //接单
    public function passOrder(){
        //http://ip:port/ssh2/sceman?cmd=modifyCommercialTenantOrderVo&assumpsitTime&CommercialTenantID&name&tel&personNum&id
        $id = I('post.id');
        $tel = I('post.tel');
        empty($id) && empty($tel) && $this->returnJson(0,'参数缺失！');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=modifyCommercialTenantOrderVo&id='.$id.'&orderState=2';
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if($resultApi['flag']){
            $apiInfo = C('IP_BASE').'/ssh2/sceman?cmd=queryCommercialTenantVoByID&id='.$this->sellerId;
            $resultInfo = json_decode(file_get_contents($apiInfo),true);
            //发送接单验证码   http://ip:port/ssh2/sceman?cmd=sendMessageByList&tel&temID&arrayVal
            $sellername = session('sce_sellerName');
            $sellertel = $resultInfo['result']['tel'];
            $idMsg = 129790;
            $msg = json_encode(array($sellername, $sellertel));
            $apiMsg = C('IP_BASE').'/ssh2/sceman?cmd=sendMessageByList&tel='.$tel.'&temID='.$idMsg.'&arrayVal='.$msg;
            $resultMsg = json_decode(file_get_contents($apiMsg),true);
            if(!$resultMsg['flag']) $this->returnJson(0,'该用户超出每天能接受的最多短信，请电话联系对方！');
        }
        if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
        if($resultApi['flag']) $this->returnJson(1,'成功',$resultApi['result']);
    }

    //拒绝订单
    public function rejectOrder(){
        //http://ip:port/ssh2/sceman?cmd=modifyCommercialTenantOrderVo&assumpsitTime&CommercialTenantID&name&tel&personNum&id
        $id = I('post.id');
        $tel = I('post.tel');
        empty($id) && empty($tel) && $this->returnJson(0,'参数缺失！稍后再试');
        $apiUrl = C('IP_BASE').'/ssh2/sceman?cmd=modifyCommercialTenantOrderVo&id='.$id.'&orderState=3';
        //发短信暂时没有短信模板
        $resultApi = json_decode(file_get_contents($apiUrl),true);
        if($resultApi['flag']){
            //发送接单验证码   http://ip:port/ssh2/sceman?cmd=sendMessageByList&tel&temID&arrayVal
            $sellername = session('sce_sellerName');
            $sellertel = $tel;
            $idMsg = 129791;
            $msg = json_encode(array($sellername));
            $apiMsg = C('IP_BASE').'/ssh2/sceman?cmd=sendMessageByList&tel='.$tel.'&temID='.$idMsg.'&arrayVal='.$msg;
            $resultMsg = json_decode(file_get_contents($apiMsg),true);
            if(!$resultMsg['flag']) $this->returnJson(0,'该用户超出每天能接受的最多短信，请电话联系对方！');
        }
        if(!$resultApi['flag']) $this->returnJson(0,$resultApi['result']);
        if($resultApi['flag']) $this->returnJson(1,'成功',$resultApi['result']);
    }
}

//将商家的类型id转换成名字返回
function typeToName($type){
    switch ($type) {
        case 0:
            $name = '美食';
            break;
        case 1:
            $name = '住宿';
            break;
        case 2:
            $name = '购物';
            break;
        case 3:
            $name = '娱乐';
            break;
        
        default:
            $name = '美食';
            break;
    }
    return $name;
}