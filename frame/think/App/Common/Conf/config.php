<?php

return array(
    'SHOW_PAGE_TRACE' => true,
    'DB_TYPE' => 'mysql',
    'DB_HOST' => '127.0.0.1', // 服务器地址
    'DB_NAME' => 'zh_oa', // 数据库名
    'DB_USER' => 'root', // 用户名
    'DB_PWD' => 'zhyy123.', // 密码
    'DB_PORT' => '3306', // 端口
    'DATA_CACHE_TYPE' => 'Memcache',
    'MEMCACHE_HOST' => 'tcp://127.0.0.1:11211',
    'DATA_CACHE_TIME' => '3600',
    'TOKEN_ON' => true, // 是否开启令牌验证 默认关闭
    'TOKEN_NAME' => '__hash__', // 令牌验证的表单隐藏字段名称，默认为__hash__
    'TOKEN_TYPE' => 'md5', //令牌哈希验证规则 默认为MD5
    'TOKEN_RESET' => true, //令牌验证出错后是否重置令牌 默认为true
);
