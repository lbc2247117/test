<?php

define('MaxSqlSize', 100);
require_once 'MysqlPDO.php';

class CommonHelper {

    // public $dao;

    /*
     * 增加一个实体 array("id"=>'aaa','bb'=>'bbb')
     */
    static function Insert($table, $fields, $isMany = FALSE) {
        $dao = new MysqlPDO();
        if (!$isMany) {
            return $dao->Insert($table, $fields);
        } else {
            if (count($fields) > MaxSqlSize) {
                $i = count($fields) % MaxSqlSize == 0 ? count($fields) / MaxSqlSize : intval(count($fields) / MaxSqlSize) + 1;
                for ($j = 0; $j < $i; $j++) {
                    $y = array();
                    if ($j == $i - 1) {
                        $len = (count($fields) % MaxSqlSize == 0 ) ? MaxSqlSize : count($fields) % MaxSqlSize;
                        $y = array_slice($fields, $j * MaxSqlSize, $len);
                    } else {
                        $y = array_slice($fields, $j * MaxSqlSize, MaxSqlSize);
                    }
                    if (count($y) > 0) {
                        $dao->InsertArray($table, $y);
                    }
                }

                return TRUE;
            } else {

                return $dao->InsertArray($table, $fields);
            }
        }
    }

    /* 更改key */

    static function ChangeArrayKey($array, $key1, $key2) {
        $keys = array_keys($array);
        $index = array_search($key1, $keys);

        if ($index !== false) {
            $keys[$index] = $key2;
            $array = array_combine($keys, $array);
        }

        return $array;
    }

     function TranscationSql($arr) {
           $userDao = new MysqlPDO();
         
        if (!empty($arr) && is_array($arr)) {
            try {
                $this->beginTransaction();
                foreach ($arr as $key => $value) {
                    if ($value["type"] == TranType::create) {
                        $table = $value[0]["table"];
                       
                        $result = $userDao->Insert($table, $value[0]["field"]);
                        if (!$result) {
                            throw new PDOException("error");
                        }
                    } else if ($value["type"] == TranType::delete) {
                        $table = $value[0]["table"];
                        $result = $userDao->Del($table, $value[0]["field"]);
                        if (!$result) {
                            throw new PDOException("error");
                        }
                    } else if ($value ["type"] == TranType::update) {
                        $table = $value[0]["table"];
                        $result = $userDao->Update($table, $value[0]["field"], $value[0]["where"]);
                        if (!$result) {
                            throw new PDOException("error");
                        }
                    }
                }
                $this->commit();
                return true;
            } catch (PDOException $e) {
                $this->rollBack();
                return false;
            }
        }
    }

    /*
     * 开始事物
     */

    public static function beginTransaction() {
         $dao = new MysqlPDO();
        
        return $dao->beginTransaction();
    }

    /*
     * 提交事物
     */

    public static function commit() {
         $dao = new MysqlPDO();
        return $dao->commit();
    }

    /*
     * 回滚事物
     */

    public static function rollBack() {
        $dao = new MysqlPDO();
        return $dao->rollBack();
    }

}
/* 事物处理 */

class TranType {
    const delete = 1;
    const update = 2;
    const create = 3;
}

/*
 * 查询类型
 *  */

class QueryType {

    const In = 'In';
    const NotIn = 'NotIn';
    const Eq = 'Eq';
    const Range = 'Range';
    const Maybe = 'Maybe'; //等于Or
    const Like = 'Like';
    const Is = 'Is';
    const PlusThan = 'PlusThan'; //大于
    const LessThan = 'LessThan'; //小于

}