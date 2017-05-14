<?php
try{
	$soap = new SoapClient(null,array(
			"location" => "http://test.local.com/php_soap/server.php",
			"uri"      => "abcd",  //资源描述符服务器和客户端必须对应
			"style"    => SOAP_RPC,
			"use"      => SOAP_ENCODED
			   ));

	echo $soap->Add(1,2);
}catch(Exction $e){
	echo print_r($e->getMessage(),true);
}
?>