<?php


$m=new Memcache();
$m->addserver('127.0.0.1',11211);  //添加服务器
$m->set("mkey","mvalue1",0); 
