<?php

$ip = $_SERVER["REMOTE_ADDR"];
$time = date('Y-m-d H:i:s');
$db = create_pdo();
$sql = "insert into u_access (ip,time)values('$ip','$time')";
$result = $db->exec($sql);
if ($result !== FALSE)
    exit('2');
else
    exit('0');

function create_pdo() {
    $pdo_dsn = 'mysql:host='.URL.';port=3306;dbname=zh_oa';
    $pdo_options = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'/* ,
              PDO::ATTR_PERSISTENT => true */
    );
    try {
        $pdo = new PDO($pdo_dsn, DB_USR, DB_PWD, $pdo_options);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        return $pdo;
    } catch (PDOException $ex) {
        exit('0');
    }
}
