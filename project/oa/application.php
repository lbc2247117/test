<?php
define('DEBUG_MODE', true);

define('DEFAULT_ENC', 'UTF-8');
define('EMPTY_STRING', '');
define('ROOT_PATH', __DIR__);

define('BASE_PATH', str_replace('\\', '/', realpath(dirname(__FILE__) . '/')) . "/");

// 数据库配置
define('URL', '127.0.0.1');
define('DB_HOST', URL);
define('DB_PORT', 3306);
define('DB_NAME', 'zh_oa');
define('DB_USR', 'root');
define('DB_PWD', 'zhyy123.');

define('BACKEND_KEY', '400636D5E1B1217701B4A62C996CB9BB');
define('PRIVATE_KEY', '1F1B6CB7B86832D808B5AF5FA7AE8748');
define('PUBLIC_KEY', 'D3CD5F8C7906544E36731BA9F395A83A');
define('SESSION_EXPIRE_HOUR', 168); //会话有效期，单位：小时
// API配置
define('ONLY_ALLOW_DEFAULT_CONTENT_TYPE', true); // 仅允许使用默认的Content-Type
define('DEFAULT_CONTENT_TYPE', 'json'); // 默认Content-Type
define('DEFAULT_CHARSET', 'utf-8'); // 默认Charset
define('DEFAULT_PAGESIZE', 20); // 默认列表每页数据条数
define("START_YEAR", 2015);
define("PAGE_VERSION", '1.0.0');

define("ONLINEDATE", "2016-04-06"); //定义查询业绩的时间节点

/*
  word 转pdf目录
 */
define("DEFAULT_FILE_UPLOAD_DIR", 'D:/word2pdf2show/files/');
define('DEFAULT_PDF_OUTPUT_DIR', 'D:/word2pdf2show/pdfs/');
define('DEFAULT_SWF_OUTPUT_DIR', 'D:/word2pdf2show/swfs/');

/* * PDF转SWF* */
define("CMD_CONVERSION_SINGLEDOC", "D:\swftools\pdf2swf.exe {path.pdf}{pdffile} -o {path.swf}{pdffile}.swf -f -T 9 -t -s storeallcharacters");
define("CMD_CONVERSION_SPLITPAGES", "D:\swftools\pdf2swf.exe {path.pdf}{pdffile} -o {path.swf}{pdffile}%.swf -f -T 9 -t -s storeallcharacters -s linknameurl");
define("CMD_SEARCHING_EXTRACTTEXT", "D:\swftools\swfstrings.exe {path.swf}{swffile}");

/**
 * 消息发送接口地址
 */
define('WEBAPIURL', 'http://' . URL . ':88/');
define('LOCALHOST_PUSH_API', true);
if (LOCALHOST_PUSH_API) {
    define('PUSH_API_URL', 'http://localhost:90/');
    define('PUSH_MESSAGE_URL',WEBAPIURL . 'pushmsg.ashx');
} else {
    define('PUSH_API_URL', WEBAPIURL);
    define('PUSH_MESSAGE_URL', WEBAPIURL . 'pushmsg.ashx');
}
define('PUSH_MESSAGE_PRIVATE_KEY', '4792D6631BC47547DEDBC008C5538EE3');
define('PUSH_MESSAGE_CALLER', 0);
//消息推送API配置
define('HEARTBEAT_PACKET', '{"Code":0,"Msg":"!!!","MsgType":-1}'); //心跳包
define('YC_OA_CLIENT_LIST_KEY', 'yc_oa_client_code_list');
define('YC_OA_NOTIFY_MSG_KEY_PREFIX', 'yc_oa_notify_msg_');
//Redis配置
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379); //星密码
define('REDIS_PORT_ZH', 6389); //佐航
//客户端配置
define('CLIENT_VERSION', '2.0.1.0');
define('VERSION_CONTENT', '检测到新版本，请更新后再登录！rn(OA需要您的参与，运营部虚怀若谷的期待你的意见和建议！)rn 本次版本更新内容如下：rn1、取消平台业绩模块rn2、修改代运营下拉框样式rn3、修改所有业绩模块日期控件rn4、新增客户来源：领远rn5、套餐类型改为下拉框形式rn6、新增提交流量模块');
define('CLIENT_AES_KEY', 'ZD6Q2L0W7IYK1E94SG7L34S1J6P8VOFU');
define('AES_IV', '3658326857378781');
define('AES_KEY', '7824513655283716');
//RabbitMQ配置
define('RABBITMQ_SERVER_IP', '192.168.106.250');
define('RABBITMQ_LOGINNAME', 'rollen');
define('RABBITMQ_PASSWORD', 'root');


error_reporting(E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_RECOVERABLE_ERROR);
date_default_timezone_set('PRC');
mb_internal_encoding(DEFAULT_ENC);
mb_regex_encoding(DEFAULT_ENC);

spl_autoload_register(function($class) {
    ud_require_once('/' . $class . '.php');
});

function ud_require_once($filename) {
    $filename = str_replace('\\', '/', $filename);
    isset($GLOBALS[$filename]) or require __DIR__ . $filename;
}
