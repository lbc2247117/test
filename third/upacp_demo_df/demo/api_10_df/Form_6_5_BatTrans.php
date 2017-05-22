<?php
header ( 'Content-type:text/html;charset=utf-8' );
include_once $_SERVER ['DOCUMENT_ROOT'] . '/upacp_demo_df/sdk/acp_service.php';



/**
 * 重要：联调测试时请仔细阅读注释！
 * 
 * 产品：代付产品<br>
 * 交易：批量代付：后台交易<br>
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
 * 交易说明:   1)确定批量结果请调用批量交易状态查询交易,无后台通知。
 *          2)批量文件格式请参考 《全渠道平台接入接口规范 第3部分 文件接口》（4.批量文件基本约定）
 *          3）批量代付文件示例DF00000000777290058110097201507140002I.txt，注意：使用的时候需修改文件内容的批次号，日期（与txnTime前八位相同）总笔数，总金额等于下边参数中batchNo，txnTime，totalQty，totalAmt设定的一致。
 */

$params = array(

		//以下信息非特殊情况不需要改动
		'version' => com\unionpay\acp\sdk\SDKConfig::getSDKConfig()->version,				//版本号
		'encoding' => 'UTF-8',				//编码方式
		'signMethod'=>'01',					//签名方法
		'txnType' => '21',					//交易类型
		'txnSubType' => '03',				//交易子类型 
		'bizType' => '000401',				//业务类型
		'accessType' => '0',				//接入类型
		'channelType'=>'07',				//收单机构代码
		
		//TODO 以下信息需要填写
		'merId' => $_POST["merId"],		    //商户代码，请改成自己的测试商户号	
		'batchNo' => $_POST["batchNo"],		//批次号，当天唯一，0001-9999，商户号+批次号+上交易时间确定一笔交易
		'txnTime' => $_POST["txnTime"],		//订单发送时间，取系统时间
		'totalQty' => $_POST["totalQty"],					 //总笔数
		'totalAmt' => $_POST["totalAmt"],					 //总金额，单位分
		'fileContent' => com\unionpay\acp\sdk\AcpService::enCodeFileContent($_POST["filePath"]), //文件内容，内容组成请参考规范文件部分，文件gbk编码或utf8无dom编码，样例文件请参考assets文件夹下的DF00000000777290058110097201507140002I.txt文件，请注意修改商户号、批次、日期等信息。调接口时文件名不会往后传，所以不用参考规范给文件名命名。
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

echo "应答报文验签成功<br>\n";
if ($result_arr["respCode"] == "00"){
	//交易已受理
    //需发起交易批量状态查询交易（Form10_6_6_BatchQuery）确定交易状态【建议1小时后查询】
    //TODO
	echo "受理成功。<br>\n";
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

