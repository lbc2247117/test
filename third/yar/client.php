<?php
$client = new Yar_Client("http://test.local.com/third/yar/server.php");
$result = $client->testapi("parameter");
exit(json_encode($result));
