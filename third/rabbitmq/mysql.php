<?php
/**
 * Created by PhpStorm.
 * User: liubocheng
 * Date: 17-5-7
 * Time: 下午6:00
 */
class mysqlPDO{
    public static function create(){
        $dsn='mysql:dbname=test;host=127.0.0.1';
        $user='root';
        $pwd='root';
        try{
            $dbh=new PDO($dsn,$user,$pwd);
            return $dbh;
        }catch(PDOException $e){
            return 'Connection failed:'.$e->getMessage();
        }
    }
}