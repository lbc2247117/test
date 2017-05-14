<?php
class service
{
  public function HelloWorld()
   {
      return  "Hello";
   }
  public  function Add($a,$b)
   {
      return $a+$b;
   }
}
$server=new SoapServer(null,array('uri' => "abcd"));
$server->setClass("service");
$server->handle();
?>