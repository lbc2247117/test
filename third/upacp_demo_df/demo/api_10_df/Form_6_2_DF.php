﻿<?php
header ( 'Content-type:text/html;charset=utf-8' );
include_once $_SERVER ['DOCUMENT_ROOT'] . '/third/upacp_demo_df/sdk/acp_service.php';

/**
* 重要：联调测试时请仔细阅读注释！
* 
* 产品：代付产品<br>
* 交易：单笔代付：后台异步交易，有同步应答和异步应答<br>
* 日期： 2015-09<br>
* 版权： 中国银联<br>
* 说明：以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己需要，按照技术文档编写。该代码仅供参考，不提供编码性能规范性等方面的保障<br>
* 提示：该接口参考文档位置：open.unionpay.com帮助中心 下载  产品接口规范  《代付产品接口规范》，<br>
*              《平台接入接口规范-第5部分-附录》（内包含应答码接口规范），
*              《全渠道平台接入接口规范 第3部分 文件接口》（对账文件格式说明）<br>
* 测试过程中的如果遇到疑问或问题您可以：1）优先在open平台中查找答案：
* 							        调试过程中的问题或其他问题请在 https://open.unionpay.com/ajweb/help/faq/list 帮助中心 FAQ 搜索解决方案
*                             测试过程中产生的7位应答码问题疑问请在https://open.unionpay.com/ajweb/help/respCode/respCodeList 输入应答码搜索解决方案
*                          2） 咨询在线人工支持： open.unionpay.com注册一个用户并登陆在右上角点击“在线客服”，咨询人工QQ测试支持。
*                          3）  测试环境测试支付请使用测试卡号测试， FAQ搜索“测试卡号”
*                          4） 切换生产环境要点请FAQ搜索“切换”
* 交易说明:以后台通知或交易状态查询交易确定交易成功
*/

//TODO 填寫卡信息
//支付卡要素说明：证件和姓名至少出现一个，其余手机号等要素不送

$accNo = '6226388000000095';
$customerInfo = array(
//		'certifTp' => '01',
//		'certifId' => '510265790128303',
		'customerNm' => '张三',
);

$params = array(
		
		//以下信息非特殊情况不需要改动
		'version' => com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->version,		      //版本号
		'encoding' => 'utf-8',		      //编码方式
		'signMethod' => com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->signMethod,		      //签名方法
		'txnType' => '12',		          //交易类型	
		'txnSubType' => '00',		      //交易子类
		'bizType' => '000401',		      //业务类型
		'accessType' => '0',		      //接入类型
		'channelType' => '08',		      //渠道类型
        'currencyCode' => '156',          //交易币种，境内商户勿改
		'backUrl' => com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->backUrl, //后台通知地址
		'encryptCertId' => com\unionpay\acp\sdk\AcpService::getEncryptCertId(), //验签证书序列号
		
		//TODO 以下信息需要填写
		'merId' => $_POST["merId"],		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
		'orderId' => $_POST["orderId"],	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
		'txnTime' => $_POST["txnTime"],	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
		'txnAmt' => $_POST["txnAmt"],	//交易金额，单位分，此处默认取demo演示页面传递的参数
// 		'billNo' =>'保险',  				//银行附言。会透传给发卡行，完成改造的发卡行会把这个信息在账单、短信中显示给用户的，请按真实情况填写。
		
// 		'accNo' => $accNo,     //卡号，旧规范请按此方式填写
// 		'customerInfo' => com\unionpay\acp\sdk\AcpService::getCustomerInfo($customerInfo), //持卡人身份信息，旧规范请按此方式填写
		'accNo' =>  com\unionpay\acp\sdk\AcpService::encryptData($accNo),     //卡号，新规范请按此方式填写
		'customerInfo' => com\unionpay\acp\sdk\AcpService::getCustomerInfoWithEncrypt($customerInfo), //持卡人身份信息，新规范请按此方式填写

		// 请求方保留域，
		// 透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据。
		// 出现部分特殊字符时可能影响解析，请按下面建议的方式填写：
		// 1. 如果能确定内容不会出现&={}[]"'等符号时，可以直接填写数据，建议的方法如下。
		//    'reqReserved' =>'透传信息1|透传信息2|透传信息3',
		// 2. 内容可能出现&={}[]"'符号时：
		// 1) 如果需要对账文件里能显示，可将字符替换成全角＆＝｛｝【】“‘字符（自己写代码，此处不演示）；
		// 2) 如果对账文件没有显示要求，可做一下base64（如下）。
		//    注意控制数据长度，实际传输的数据长度不能超过1024位。
		//    查询、通知等接口解析时使用base64_decode解base64后再对数据做后续解析。
		//    'reqReserved' => base64_encode('任意格式的信息都可以'),
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
 	    || $result_arr["respCode"] == "05" 
 	    || $result_arr["respCode"] == "01" 
 	    || $result_arr["respCode"] == "12" 
 	    || $result_arr["respCode"] == "34" 
 	    || $result_arr["respCode"] == "60" ){
    //后续需发起交易状态查询交易确定交易状态
    //TODO
     echo "处理超时，请稍后查询。<br>\n";
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
