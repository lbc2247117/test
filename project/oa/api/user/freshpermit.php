<?php

header("Content-type: text/html; charset=utf-8");
$userid = $_REQUEST['userid'];
$db = create_pdo();
$sql = 'select permit,role_id from m_user where userid=' . $userid;
$result = $db->query($sql);
$result = $result->fetchAll(pdo::FETCH_ASSOC);
if (count($result) == 0) {
    $result['code'] = '0x300';
    $result['msg'] = '获取用户权限失败';
    exit(json_encode($result));
}

$permit_user = $result[0]['permit'];
$role_id = $result[0]['role_id'];
$sql_role = 'select permit from m_role where id=' . $role_id;
$result = $db->query($sql_role);
$result = $result->fetchAll(pdo::FETCH_ASSOC);
if (count($result) == 0) {
    $result['code'] = '0x300';
    $result['msg'] = '获取职位权限失败';
    exit(json_encode($result));
}
$permit_role = $result[0]['permit'];
$result['permit_user'] = implode(',', array_unique(array_merge(explode(',', $permit_role), explode(',', $permit_user))));
$result['code'] = '0';
exit(json_encode($result));

function create_pdo() {
    $pdo_dsn = 'mysql:host='.URL.';port=3306;dbname=zh_oa';
    $pdo_options = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'/* ,
              PDO::ATTR_PERSISTENT => true */
    );
    try {
        $pdo = new PDO($pdo_dsn,DB_USR,DB_PWD, $pdo_options);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        return $pdo;
    } catch (PDOException $ex) {
        exit('0');
    }
}
