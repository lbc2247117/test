<?php

return array(
    'URL_MODEL' => 2,
    'LOAD_EXT_CONFIG' => 'db,const',
    'APP_GROUP_LIST' => 'Home,Admin,Business', //项目分组设定 'DEFAULT_GROUP' => 'Home', //默认分组
    //全局变量配置
    'IP_BASE' => 'http://192.168.0.201:1234',
    'IP_LIVE' => 'http://192.168.0.201:7777',       //董哥的接口前缀
    'IMG_PRE' => 'http://192.168.0.201:7777/ssh2/',
    'PUBLIC' => 'Public/',
    'OFFICIAL_TEL' => '13076000099', //如果推送视频和图片没有绑定app账号，用这个官方账号
    'FULL_PATH' => '/bsce/upload/', //访问资源额全路劲
    'SCE_HOME_URL' => 'http://192.168.0.201/bsce/home/Index/sce?',  //景区主页的前台访问地址
    'ACT_HOME_URL' => 'http://192.168.0.201/bsce/home/Index/act?',  //普通活动前台页面地址
    'LIVE_HOME_URL' => 'http://192.168.0.201/bsce/home/Index/liveplay?',     //移动高清直播的前台访问地址
);
