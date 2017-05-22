<?php
header ( 'Content-type:text/html;charset=utf-8' );
include_once $_SERVER ['DOCUMENT_ROOT'] . '/upacp_demo_df/sdk/acp_service.php';

/**
* 重要：联调测试时请仔细阅读注释！
* 
* 产品：代付产品<br>
* 交易：实名认证：后台交易，只有同步应答<br>
* 日期： 2015-09<br>
* 版本： 1.0.0
* 版权： 中国银联<br>
* 说明：以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己需要，按照技术文档编写。该代码仅供参考，不提供编码性能规范性等方面的保障<br>
* 该接口参考文档位置：open.unionpay.com帮助中心 下载  产品接口规范  《代付产品接口规范》<br>
*              《平台接入接口规范-第5部分-附录》（内包含应答码接口规范）<br>
* 测试过程中的如果遇到疑问或问题您可以：1）优先在open平台中查找答案：
* 							        调试过程中的问题或其他问题请在 https://open.unionpay.com/ajweb/help/faq/list 帮助中心 FAQ 搜索解决方案
*                             测试过程中产生的7位应答码问题疑问请在https://open.unionpay.com/ajweb/help/respCode/respCodeList 输入应答码搜索解决方案
*                          2） 咨询在线人工支持： open.unionpay.com注册一个用户并登陆在右上角点击“在线客服”，咨询人工QQ测试支持。
* 交易说明:同步交易，不需要接收后台通知或发起交易状态查询交易
*/

//TODO 填寫卡信息
//
//  实名认证交易卡要素说明：
//
//  银联后台会做控制，如使用真实商户号，要素为根据申请表配置，请参考自己的申请表上送。
//
//  测试商户号777290058110097的交易卡要素控制配置：
//  借记卡必送：
//  （实名认证-后台）卡号，姓名，证件类型，证件号码，手机号
//  （实名认证-前台）卡号，姓名，证件类型，证件号码
//
//  贷记卡必送：（使用绑定标识码的代收）
//  （实名认证-后台）卡号，有效期，cvn2,姓名，证件类型，证件号码，手机号，绑定标识码
//  （实名认证-前台）卡号，证件类型，证件号码，绑定标识码

$accNo = '6226090000000048';
$customerInfo = array(
 		'phoneNo' => '18100000000',//手机号
		'certifTp' => '01', //证件类型，01-身份证
		'certifId' => '510265790128303',//证件号，15位身份证不校验尾号，18位会校验尾号，请务必在前端写好校验代码
		'customerNm' => '张三',//姓名
//  		'cvn2' => '248',　//cvn2
//  		'expired' => '1912',　//有效期，YYMM格式，持卡人卡面印的是MMYY的，请注意代码设置倒一下
);

$params = array(

		//以下信息非特殊情况不需要改动
		'version' => com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->version,		           //版本号
		'encoding' => 'utf-8',		           //编码方式
		'signMethod' => com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->signMethod,		           //签名方法
		'txnType' => '72',		               //交易类型
		'txnSubType' => '01',		           //交易子类
		'bizType' => '000401',		           //业务类型
		'accessType' => '0',		           //接入类型
		'channelType' => '08',		           //渠道类型
		'encryptCertId' => com\unionpay\acp\sdk\AcpService::getEncryptCertId(), //验签证书序列号
		
		//TODO 以下信息需要填写
		'merId' => $_POST["merId"],		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
		'orderId' => $_POST["orderId"],	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
		'txnTime' => $_POST["txnTime"],	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
	
// 		'accNo' => $accNo,     //卡号，旧规范请按此方式填写
// 		'customerInfo' => com\unionpay\acp\sdk\AcpService::getCustomerInfo($customerInfo), //持卡人身份信息，旧规范请按此方式填写
		'accNo' => com\unionpay\acp\sdk\AcpService::encryptData($accNo),     //卡号，新规范请按此方式填写
		'customerInfo' => com\unionpay\acp\sdk\AcpService::getCustomerInfoWithEncrypt($customerInfo), //持卡人身份信息，新规范请按此方式填写
);

com\unionpay\acp\sdk\AcpService::sign ( $params ); // 签名

$url = com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->backTransUrl;

$result_arr = com\unionpay\acp\sdk\AcpService::post ( $params, $url);
if(count($result_arr)<=0) { //没收到200应答的情况
	printResult ( $url, $params, "" );
	return;
}

printResult ($url, $params, $result_arr ); //页面打印请求应答数据

if (!com\unionpay\acp\sdk\AcpService::validate ($result_arr) ){
    echo "应答报文验签失败<br>\n";
    return;
}
echo "应答报文验签成功<br>\n";
if ($result_arr["respCode"] == "00"){
    //交易已受理，等待接收后台通知更新订单状态，如果通知长时间未收到也可发起交易状态查询
    //TODO
    echo "受理成功。<br>\n";
} else if ($result_arr["respCode"] == "03"
 	    || $result_arr["respCode"] == "04"
 	    || $result_arr["respCode"] == "05" ){
    //后续需发起交易状态查询交易确定交易状态
    //TODO
     echo "处理超时，请稍微查询。<br>\n";
} else {
    //其他应答码做以失败处理
     //TODO
     echo "失败：" . $result_arr["respMsg"] . "。<br>\n";
}

/**
 * 打印请求应答
 *
 * @param
 *        	$url
 * @param
 *        	$req
 * @param
 *        	$resp
 */
function printResult($url, $req, $resp) {
	echo "=============<br>\n";
	echo "地址：" . $url . "<br>\n";
	echo "请求：" . str_replace ( "\n", "\n<br>", htmlentities ( com\unionpay\acp\sdk\createLinkString ( $req, false, true ) ) ) . "<br>\n";
	echo "应答：" . str_replace ( "\n", "\n<br>", htmlentities ( com\unionpay\acp\sdk\createLinkString ( $resp , false, false )) ) . "<br>\n";
	echo "=============<br>\n";
}
