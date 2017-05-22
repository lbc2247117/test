<?php
header ( 'Content-type:text/html;charset=utf8' );
include_once $_SERVER ['DOCUMENT_ROOT'] . '/upacp_demo_df/sdk/acp_service.php';

/**
 * 重要：联调测试时请仔细阅读注释！
 * 
 * 产品：代付产品<br>
 * 交易：批量交易状态查询类交易：后台交易，用户查询批量结果文件<br>
 * 日期： 2015-09<br>
 * 版权： 中国银联<br>
 * 说明：以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己需要，按照技术文档编写。该代码仅供参考，不提供编码性能规范性等方面的保障<br>
 * 提示：该接口参考文档位置：open.unionpay.com帮助中心 下载  产品接口规范  《代付产品接口规范》，<br>
 *                  《全渠道平台接入接口规范 第3部分 文件接口》（4.批量文件基本约定）<br>
 * 测试过程中的如果遇到疑问或问题您可以：1）优先在open平台中查找答案：
 * 							        调试过程中的问题或其他问题请在 https://open.unionpay.com/ajweb/help/faq/list 帮助中心 FAQ 搜索解决方案
 *                             测试过程中产生的7位应答码问题疑问请在https://open.unionpay.com/ajweb/help/respCode/respCodeList 输入应答码搜索解决方案
 *                          2） 咨询在线人工支持： open.unionpay.com注册一个用户并登陆在右上角点击“在线客服”，咨询人工QQ测试支持。
 *                          3）  测试环境测试支付请使用测试卡号测试， FAQ搜索“测试卡号”
 *                          4） 切换生产环境要点请FAQ搜索“切换”
 * 交易说明: 1)确定批量结果请调用此交易。
 *        2)批量文件格式请参考 《全渠道平台接入接口规范 第3部分 文件接口》（4.批量文件基本约定）
 *        3)批量交易状态查询的时间机制：建议间隔1小时后查询。
 */


$params = array(

		//以下信息非特殊情况不需要改动
		'version' => com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->version,				//版本号
		'encoding' => 'UTF-8',				//编码方式
		'signMethod' => com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->signMethod,				//签名方法
		'txnType' => '22',					//交易类型
		'txnSubType' => '03',				//交易子类型
		'bizType' => '000401',				//业务类型
		'accessType' => '0',				//接入类型
		'channelType'=>'07',				//渠道类型

		//TODO 以下信息需要填写
		'merId' => $_POST["merId"],         //商户代码，请改成自己的测试商户号	
		'txnTime' => $_POST["txnTime"],     //订单发送时间，取原批量交易订单发送时间
		'batchNo' => $_POST["batchNo"],	    //批次号，填原批量交易批次号
	);

com\unionpay\acp\sdk\AcpService::sign ( $params ); // 签名
$url = com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->batchTransUrl;

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

$filePath = "d:/file/";
//TODO 处理文件，保存路径上面那行设置，注意预先建立文件夹并授读写权限

echo "应答报文验签成功<br>\n";
if ($result_arr["respCode"] == "00"){
	//交易已受理
	//需发起交易批量状态查询交易（Form10_6_6_BatchQuery）确定交易状态【建议1小时后查询】
	//TODO
	echo "受理成功。<br>\n";
	if (com\unionpay\acp\sdk\AcpService::deCodeFileContent( $result_arr, $filePath )) //文件保存目录在配置文件SDKConfig.php中修改
		echo "文件成功保存到".$filePath."目录下。<br>\n";
	else
		echo "文件保存失败，请看下日志文件中的报错信息。<br>\n";
	
} else if ($result_arr["respCode"] == "03"
		|| $result_arr["respCode"] == "04"
		|| $result_arr["respCode"] == "05" ){
	//状态未知
	//发起交易批量状态查询交易（Form10_6_6_BatchQuery）确定交易状态
	//TODO
	echo "状态未知，稍后发批量查询。<br>\n";
} else {
	//其他应答码做以失败处理
	//TODO
	echo "失败：respMsg=" . $result_arr["respMsg"] . "。<br>\n";
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