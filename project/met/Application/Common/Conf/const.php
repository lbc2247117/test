<?php

/**
 * 应用常量
 */
define('EMAIL', FILTER_VALIDATE_EMAIL);
define('URL', FILTER_VALIDATE_URL);
define('IP', FILTER_VALIDATE_IP);
define('INT', FILTER_VALIDATE_INT);
define('FLOAT', FILTER_VALIDATE_FLOAT);
define('MONEY', '/^(([1-9]{1}\d*)|([0]{1}))(\.(\d){1,2})?$/');
define('MOBILE', '/^1[3458][0-9]{9,9}$/');
//微信公众号
define('APPID', 'wx99db8e13bbd0ddd3');
define('SECRET', 'bd73e80f376f06995f25940796b65a46');
//七牛云
define('ACCESSKEY', 'i7LcFM6-Xed0Q98kR7lzClzD6J5PwSYNVyjPm8KF');
define('SECRETKEY', 'HvBdeE5lMf_ftpIWeH87xaI_PJ1tMR7q1jL8wejH');
define('UPLOADPATH', dirname($_SERVER['SCRIPT_FILENAME']) . '/upload');
define('OUTLINK', 'http://7xnjsm.com1.z0.glb.clouddn.com');
define('QINIUNAMESPACE', 'itempic');


