<?php
include_once '../application.php';
/**
 * PDO方式操作数据库
 * @author tian
 */


class MysqlPDO {
    private $pdo;
    /**
     *初始化
     */
    function __construct() {
        $dbms = 'mysql';       //数据库类型，oracle用ODI,对于开发者来说，使用不同的数据库，只要改这个，不用记住那么多的函数了
        $host = DB_HOST; // $config['host']; 	//数据库主机名
        $dbname = DB_NAME;  //$config['dbname'];  //使用的数据库名称
        $user = DB_USR;  //$config['user'];      //数据库连接用户名
        $pass = DB_PWD;  //$config['pass'];		//数据库连接密码

        $dsn = "$dbms:host=$host;dbname=$dbname";
        $this->pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname", $user, $pass); //初始化一个PDO对象
        $this->pdo->query("set names 'utf8'");
    }
    
    /**
     * 查询数据[自带分页判断]
     * @param string $table
     * @param array(key=>value)|string $where
     * @param array(key=>value) $order 排序方式传入d或者a，d=desc,a=asc
     * @param array(page=>1,pagesize=>20)|null $pageing
     * @return array('rows'=>rows,'data'=>data)
     */
    public function Select($table, $where, $order = null, $pageing = null) {
        $list = array();
        $sql = "";
        //组合查询条件
        if ($where != null) {
            if (is_array($where)) {
                $sql .= " where ";
                $i = 1;
                foreach ($where as $key => $value) {
                    if (is_array($value)) {
                        //指定匹配方式
                        switch ($value[0]) {
                            case 'in':
                                $sql .= " `$key` in (:$key)";
                                break;
                            default:
                                $sql .= " `$key` " . $value[0] . " :$key";
                                break;
                        }
                    } else {
                        //不指定匹配方式，默认使用等于
                        $sql .= " `$key` = :$key";
                    }
                    //未结束，条件用,分隔
                    if ($i < count($where)) {
                        $sql .= " and ";
                    }

                    $i++;
                }
            } else {
                $sql .= " where $where";
            }
        }

        //组合排序方式
        if ($order != null) {
            $sql .= " order by";
            $i = 1;
            foreach ($order as $key => $value) {
                if ($value == 'd')
                    $value = "desc";
                else
                    $value = "asc";
                $sql .= " `$key` $value";
                if ($i < count($order)) {
                    $sql .= ",";
                }
                $i++;
            }
        }

        //取得行数
        $rows = 0;
        $rowsql = "select count(1) as count from $table $sql";
        $stmt = $this->pdo->prepare($rowsql);
        //绑定参数结果
        if (!empty($where) && is_array($where)) {
            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    if ($value[0] == 'like') {
                        $stmt->bindValue(":$key", '%' . $value[1] . '%');
                    } else {
                        $stmt->bindValue(":$key", $value[1]);
                    }
                } else {
                    $stmt->bindValue(":$key", $value);
                }
            }
        }
        $stmt->execute();
        foreach ($stmt as $item) {
            $rows = $item['count'];
            break;
        }
        //组合完整sql语句
        $sql = "SELECT * FROM `$table` " . $sql;

        //判断分页
        if ($pageing != null) {
            $sql .= ' limit ' . (($pageing['page'] - 1) * $pageing['pagesize']) . ',' . $pageing['pagesize'];
        }

        //预处理sql语句
        $sth = $this->pdo->prepare($sql);
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        //绑定参数结果
        if (!empty($where) && is_array($where)) {
            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    if ($value[0] == 'like') {
                        $sth->bindValue(":$key", '%' . $value[1] . '%');
                    } else {
                        $sth->bindValue(":$key", $value[1]);
                    }
                } else {
                    $sth->bindValue(":$key", $value);
                }
            }
        }
        //取得结果
        $sth->execute();
        //将结果集转为array
        $list = $sth->fetchAll();

        //将结果转为array
        /* foreach ($sth as $row){
          $list[] = $row;
          } */
        return array("rows" => $rows, "pageIndex" => $pageing['page'], "data" => $list);
    }

    /**
     * 为EasyUI提供JSON格式数据返回结果
     */
    public function JsonSelect($table, $where, $order, $pageing) {
        $data = $this->Select($table, $where, $order, $pageing);
        return array('total' => $data['rows'], 'rows' => $data['data']);
    }

    /*
     * 查询总条数
     */

    public function QueryCountByWhere($table, $where) {
        $sqlrow = "select count(*) count from $table where 1=1 ";
        $bindList = array();
        if (!empty($where) && is_array($where)) {
            $cursor = 0;
            foreach ($where as $key => $val) {
                switch ($key) {
                    case QueryType::In:
                        foreach ($val as $ckey => $cval) {
                            $sqlrow .= " and $ckey in(";
                            $instr = '';
                            foreach ($cval as $cckey => $ccval) {
                                $instr .= ":$ckey$cursor,";
                                $bindList[] = array(":$ckey$cursor" => $ccval);
                                $cursor++;
                            }
                            $sqlrow .=substr($instr, 0, strlen($instr) - 1) . ")";
                        }
                        break;
                    case QueryType::Eq:
                        foreach ($val as $ckey => $cval) {
                            $sqlrow .= " and $ckey =:$ckey$cursor";
                            $bindList[] = array(":$ckey$cursor" => $cval);
                            $cursor++;
                        }
                        break;
                    case QueryType::Is:
                        foreach ($val as $ckey => $cval) {
                            $sqlrow .= " and $ckey  is $cval";

                            //$bindList[] =array(":$ckey$cursor"=>$cval);
                            //$cursor++;
                        }
                        break;
                    case QueryType::Maybe:
                        foreach ($val as $ckey => $cval) {
                            $sqlrow .= " and (";
                            $instr = '';
                            foreach ($cval as $cckey => $ccval) {
                                $instr .= "$ckey= :$ckey$cursor or ";
                                $bindList[] = array(":$ckey$cursor" => $ccval);
                                $cursor++;
                            }
                            $sqlrow .=substr($instr, 0, strlen($instr) - 3) . ")";
                        }
                        break;
                    case QueryType::Range:
                        foreach ($val as $ckey => $cval) {
                            $sqlrow .= " and ($ckey between ";
                            $instr = '';
                            foreach ($cval as $cckey => $ccval) {
                                $instr .= ":$ckey$cursor and ";
                                $bindList[] = array(":$ckey$cursor" => $ccval);
                                $cursor++;
                            }
                            $sqlrow .=substr($instr, 0, strlen($instr) - 4) . ")";
                        }
                        break;
                    case QueryType::Like:
                        foreach ($val as $ckey => $cval) {
                            $sqlrow .= " and $ckey like :$ckey$cursor";
                            $bindList[] = array(":$ckey$cursor" => "%$cval%");
                            $cursor++;
                        }
                        break;
                }
            }
        }
        $abc = $this->pdo->prepare($sqlrow);
        if (!empty($bindList) && is_array($bindList)) {
            if (!empty($bindList) && is_array($bindList) && count($bindList) > 0) {
                foreach ($bindList as $key => $val) {
                    if (!empty($val) && is_array($val) && count($val) > 0) {
                        foreach ($val as $ckey => $cval) {
                            $abc->bindValue($ckey, $cval);
                        }
                    }
                }
            }
        }
        $rows = 0;
        $abc->execute();
        foreach ($abc as $item) {
            $rows = $item['count'];
            break;
        }
        return $rows;
    }

    /**
     * 获取单条信息
     * @param unknown $table
     * @param unknown $where
     * @param unknown $order
     */
    public function Single($table, $where, $order = null) {
        $list = $this->Select($table, $where, $order, null);
        $data = $list['data'];
        if ($data != null) {
            return $data[0];
        } else {
            return null;
        }
    }

    /**
     * 写入数据库
     * @param unknown $table
     * @param unknown $fields
     */
    public function Insert($table, $fields) {
        $sql = "insert into `$table` ";
        //组合字段
        $sql .= "(";
        $i = 1;
        foreach ($fields as $key => $value) {
            $sql .= "`$key`";
            if ($i < count($fields)) {
                $sql .= ",";
            }
            $i++;
        }
        $sql .= ") values (";

        //组合字段值参数
        $i = 1;
        foreach ($fields as $key => $value) {
            $sql .= ":$key";
            if ($i < count($fields)) {
                $sql .= ",";
            }
            $i++;
        }
        $sql .= ")";

        //预处理sql语句
        $stmt = $this->pdo->prepare($sql);

        //绑定参数结果
        foreach ($fields as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $result = $stmt->execute();
        $rid = $this->pdo->lastInsertId();
        return $rid;
    }

    /**
     * 更新数据库
     * @param unknown $table
     * @param unknown $fields
     * @param unknown $where
     */
    public function Update($table, $fields, $where) {
        $sql = "update $table set ";
        $i = 1;
        foreach ($fields as $key => $value) {
            $sql .= "`$key`=:$key";
            if ($i < count($fields))
                $sql .= ',';
            $i++;
        }
        //更新条件
        if (is_array($where)) {
            $i = 1;
            $sql .= ' where ';
            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    if (key($value) == 'in') {
                        $sql .= " `$key` in (" . current($value) . ") ";
                    } else {
                        $sql .= " `$key` " . key($value) . " " . current($value);
                    }
                } else {
                    $sql .= "`$key`='$value'";
                }
                if ($i < count($where))
                    $sql .= ' and ';
                $i++;
            }
        }

        //预处理sql语句
        $stmt = $this->pdo->prepare($sql);
        //绑定参数结果
        foreach ($fields as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $result = $stmt->execute();
        return $result;
    }

    /**
     * 删除指定数据
     * @param unknown $table
     * @param unknown $where
     * @return boolean
     */
    public function Del($table, $where) {
        $sql = "delete from $table where";
        $i = 1;
        foreach ($where as $key => $value) {
            if (is_array($value)) {
                //指定匹配方式
                switch ($value[0]) {
                    case 'in':
                        $sql .= " `$key` in (:$key)";
                        break;
                    default:
                        $sql .= " `$key` " . $value[0] . " :$key";
                        break;
                }
            } else {
                //不指定匹配方式，默认使用等于
                $sql .= " `$key` = :$key";
            }
            //未结束，条件用,分隔
            if ($i < count($where)) {
                $sql .= " and ";
            }
            /* $sql .= "`$key`=:$key";
              if ($i < count($fields))
              $sql .= ' and '; */
            $i++;
        }
        //预处理sql语句
        $stmt = $this->pdo->prepare($sql);

        //绑定参数结果
        foreach ($where as $key => $value) {
            //$stmt->bindValue(":$key", $value);
            if (is_array($value)) {
                if ($value[0] == 'like') {
                    $stmt->bindValue(":$key", '%' . $value[1] . '%');
                } else {
                    $stmt->bindValue(":$key", $value[1]);
                }
            } else {
                $stmt->bindValue(":$key", $value);
            }
        }

        $result = $stmt->execute();
        return $result;
    }

    /**
     * 执行指定sql语句
     * @param unknown $sql
     */
    public function Execute($sql) {
        $rs = $this->pdo->query($sql);
        $rs->setFetchMode(PDO::FETCH_ASSOC);
        /**
         * PDO::FETCH_ASSOC -- 关联数组形式
         * PDO::FETCH_NUM -- 数字索引数组形式
         * PDO::FETCH_BOTH -- 两者数组形式都有，这是缺省的
         * PDO::FETCH_OBJ -- 按照对象的形式，类似于以前的 mysql_fetch_object()
         */
        $array = $rs->fetchAll();
        return $array;
    }

    /**
     * 执行sql查询，返回结果集
     * @param unknown $sql
     * @return multitype:
     */
    public function Query($sql) {
        $result = $this->pdo->prepare($sql);
        $result->execute();
        $list = $result->fetchAll(PDO::FETCH_ASSOC);
        return $list;
    }

    /**
     * 执行多条insert 语句
     * $table 表名
     * $arr 插入对象 (array(array('UserName'=>'chen'),array('UserName'=>'qing')))
     * * */
    public function InsertArray($table, $arr) {
        $sql = "insert into $table";
        $colum = '(';
        $vallist = '';
        if (!empty($arr) && is_array($arr)) {
            foreach ($arr[0] as $cky => $cval) {
                $colum .= $cky . ',';
            }
            if (!empty($colum) && strlen($colum) > 0) {
                $colum = substr($colum, 0, strlen($colum) - 1).")" ;
            }
            $i = 0;
            foreach ($arr as $key => $value) {
                $i++;
                $vallist.="(";
                if (!empty($value) && is_array($value)) {

                    foreach ($value as $ckey => $cval) {
                        $vallist .= ":$ckey$i,";
                    }
                    if (!empty($vallist) && strlen($vallist) > 0) {
                        $vallist = substr($vallist, 0, strlen($vallist) - 1) . "),";
                    }
                }
            }
            if (!empty($vallist) && strlen($vallist) > 0) {
                $vallist = substr($vallist, 0, strlen($vallist) - 1);
            }
            $sql .= $colum . 'values' . $vallist;
            $stmt = $this->pdo->prepare($sql);
            $i = 0;
            foreach ($arr as $key => $val) {
                $i++;
                if (!empty($val) && is_array($val)) {
                    foreach ($val as $ckey => $cval) {
                        $stmt->bindValue(":$ckey$i", $cval);
                    }
                }
            }
        }
        $result = $stmt->execute();
        return $result;
    }


    /*
     * 开始事物
     */

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /*
     * 回滚事物
     */

    public function rollBack() {
        return $this->pdo->rollBack();
    }

    /*
     * 提交事物
     */

    public function commit() {
        return $this->pdo->commit();
    }

    /*
     * 查询单条信息
     */

    public function GetEnityByWhere($table, $where = null, $order = NULL, $fileds = NULL) {
        $getfields = '';
        if (!empty($fileds) && is_array($fileds) && count($fileds) > 0) {
            foreach ($fileds as $value) {
                $getfields .= format("$value,");
            }
            $getfields = substr($getfields, 0, strlen($getfields) - 1);
        }
        $sql = "select * from $table where 1=1 ";
        if (strlen($getfields) > 0) {
            $sql = "select $getfields from $table where 1=1 ";
        }
        $bindList = array();
        if (!empty($where) && is_array($where)) {
            $cursor = 0;
            foreach ($where as $key => $val) {
                switch ($key) {
                    case QueryType::In:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and $ckey in(";
                            $instr = '';
                            foreach ($cval as $cckey => $ccval) {
                                $instr .= ":$ckey$cursor,";
                                $bindList[] = array(":$ckey$cursor" => $ccval);
                                $cursor++;
                            }
                            $sql .=substr($instr, 0, strlen($instr) - 1) . ")";
                        }
                        break;
                    case QueryType::Eq:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and $ckey =:$ckey$cursor";
                            $bindList[] = array(":$ckey$cursor" => $cval);
                            $cursor++;
                        }
                        break;
                    case QueryType::Maybe:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and (";
                            $instr = '';
                            foreach ($cval as $cckey => $ccval) {
                                $instr .= "$ckey= :$ckey$cursor or ";
                                $bindList[] = array(":$ckey$cursor" => $ccval);
                                $cursor++;
                            }
                            $sql .=substr($instr, 0, strlen($instr) - 3) . ")";
                        }
                        break;
                    case QueryType::Range:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and ($ckey between ";
                            $instr = '';
                            foreach ($cval as $cckey => $ccval) {
                                $instr .= ":$ckey$cursor and ";
                                $bindList[] = array(":$ckey$cursor" => $ccval);
                                $cursor++;
                            }
                            $sql .=substr($instr, 0, strlen($instr) - 4) . ")";
                        }
                        break;
                    case QueryType::Like:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and $ckey like :$ckey$cursor";
                            $bindList[] = array(":$ckey$cursor" => "%$cval%");
                            $cursor++;
                        }
                        break;
                }
            }
        }

        if (!empty($order) && is_array($order) && count($order) > 0) {
            $sql .= ' order by ';
            $str = '';
            foreach ($order as $key => $val) {
                $str.= format(" $key $val,");
            }
            if (strlen($str) > 0) {
                $str = substr($str, 0, strlen($str) - 1);
            }
            $sql .= $str;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        if (!empty($bindList) && is_array($bindList)) {
            if (!empty($bindList) && is_array($bindList) && count($bindList) > 0) {
                foreach ($bindList as $key => $val) {
                    if (!empty($val) && is_array($val) && count($val) > 0) {
                        foreach ($val as $ckey => $cval) {
                            $stmt->bindValue($ckey, $cval);
                        }
                    }
                }
            }
        }
        //取得结果
        $stmt->execute();
        //将结果集转为array
        $list = $stmt->fetchAll();
        return $list;
    }

    /**
     * 根据条件查询
     *  $where=array(QueryType::In=>array("name"=>array("3","4","5")),  QueryType::Eq=>array("sex"=>"男","name"=>"陈"),  QueryType::Maybe=>array("name"=>array("a","b","c")),  QueryType::Range=>array("name"=>array("1","10")),  QueryType::Like=>array('name'=>'a','sex'=>'b'));
     * * */
    public function GetList($table, $where = null, $size = NULL, $order = NULL, $fileds = NULL) {
        $getfields = '';
        if (!empty($fileds) && is_array($fileds) && count($fileds) > 0) {
            foreach ($fileds as $value) {
                $getfields .= format("$value,");
            }
            $getfields = substr($getfields, 0, strlen($getfields) - 1);
        }
        $sql = "select * from $table where 1=1 ";
        if (strlen($getfields) > 0) {
            $sql = "select $getfields from $table where 1=1 ";
        }
        $bindList = array();
        if (!empty($where) && is_array($where)) {
            $cursor = 0;
            foreach ($where as $key => $val) {
                switch ($key) {
                    case QueryType::In:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and $ckey in(";
                            $instr = '';
                            foreach ($cval as $cckey => $ccval) {
                                $instr .= ":$ckey$cursor,";
                                $bindList[] = array(":$ckey$cursor" => $ccval);
                                $cursor++;
                            }
                            $sql .=substr($instr, 0, strlen($instr) - 1) . ")";
                        }
                        break;
                    case QueryType::NotIn:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and $ckey not in(";
                            $instr = '';
                            foreach ($cval as $cckey => $ccval) {
                                $instr .= ":$ckey$cursor,";
                                $bindList[] = array(":$ckey$cursor" => $ccval);
                                $cursor++;
                            }
                            $sql .=substr($instr, 0, strlen($instr) - 1) . ")";
                        }
                        break;
                    case QueryType::Eq:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and $ckey =:$ckey$cursor";
                            $bindList[] = array(":$ckey$cursor" => $cval);
                            $cursor++;
                        }
                        break;
                    case QueryType::Is:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and $ckey  is $cval";
                            //$bindList[] =array(":$ckey$cursor"=>$cval);
                            //$cursor++;
                        }
                        break;
                    case QueryType::Maybe:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and (";
                            $instr = '';
                            foreach ($cval as $cckey => $ccval) {
                                $instr .= "$ckey= :$ckey$cursor or ";
                                $bindList[] = array(":$ckey$cursor" => $ccval);
                                $cursor++;
                            }
                            $sql .=substr($instr, 0, strlen($instr) - 3) . ")";
                        }
                        break;
                    case QueryType::Range:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and ($ckey between ";
                            $instr = '';
                            foreach ($cval as $cckey => $ccval) {
                                $instr .= ":$ckey$cursor and ";
                                $bindList[] = array(":$ckey$cursor" => $ccval);
                                $cursor++;
                            }
                            $sql .=substr($instr, 0, strlen($instr) - 4) . ")";
                        }
                        break;
                    case QueryType::PlusThan:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and $ckey > :$ckey$cursor";
                            $sqlrow .= " and $ckey > :$ckey$cursor";
                            $bindList[] = array(":$ckey$cursor" => $cval);
                            $cursor++;
                        }
                        break;
                    case QueryType::LessThan:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and $ckey < :$ckey$cursor";
                            $sqlrow .= " and $ckey < :$ckey$cursor";
                            $bindList[] = array(":$ckey$cursor" => $cval);
                            $cursor++;
                        }
                        break;
                    case QueryType::Like:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and $ckey like :$ckey$cursor";
                            $bindList[] = array(":$ckey$cursor" => "%$cval%");
                            $cursor++;
                        }
                        break;
                }
            }
        }

        if (!empty($order) && is_array($order) && count($order) > 0) {
            $sql .= ' order by ';
            $str = '';
            foreach ($order as $key => $val) {
                $str.= format(" $key $val,");
            }
            if (strlen($str) > 0) {
                $str = substr($str, 0, strlen($str) - 1);
            }
            $sql .= $str;
        }

        if (!empty($size)) {
            if(is_array($size)){
                  $sql.= " limit $size[0],$size[1]";
            }else{
            $sql.= " limit $size";
            }
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        if (!empty($bindList) && is_array($bindList)) {
            if (!empty($bindList) && is_array($bindList) && count($bindList) > 0) {
                foreach ($bindList as $key => $val) {
                    if (!empty($val) && is_array($val) && count($val) > 0) {
                        foreach ($val as $ckey => $cval) {
                            $stmt->bindValue($ckey, $cval);
                        }
                    }
                }
            }
        }
        //取得结果
        $stmt->execute();
        //将结果集转为array
        $list = $stmt->fetchAll();
        return $list;
    }

    /**
     * 根据条件查询
     *  $where=array(QueryType::In=>array("name"=>array("3","4","5")),  QueryType::Eq=>array("sex"=>"男","name"=>"陈"),  QueryType::Maybe=>array("name"=>array("a","b","c")),  QueryType::Range=>array("name"=>array("1","10")),  QueryType::Like=>array('name'=>'a','sex'=>'b'));
     * * */
    public function GetListByPager($table, $where = null, $order = NULL, $fileds = NULL, $paging) {
        $getfields = '';
        if (!empty($fileds) && is_array($fileds) && count($fileds) > 0) {
            foreach ($fileds as $value) {
                $getfields .= format("$value,");
            }
            $getfields = substr($getfields, 0, strlen($getfields) - 1);
        }
        $sqlrow = "select count(*) count from $table where 1=1 ";
        $sql = "select * from $table where 1=1 ";
        if (strlen($getfields) > 0) {
            $sql = "select $getfields from $table where 1=1 ";
        }
        $bindList = array();
        if (!empty($where) && is_array($where)) {
            $cursor = 0;
            foreach ($where as $key => $val) {
                switch ($key) {
                    case QueryType::In:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and $ckey in(";
                            $sqlrow .= " and $ckey in(";
                            $instr = '';
                            foreach ($cval as $cckey => $ccval) {
                                $instr .= ":$ckey$cursor,";
                                $bindList[] = array(":$ckey$cursor" => $ccval);
                                $cursor++;
                            }
                            $sql .=substr($instr, 0, strlen($instr) - 1) . ")";
                            $sqlrow .=substr($instr, 0, strlen($instr) - 1) . ")";
                        }
                        break;
                    case QueryType::Eq:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and $ckey =:$ckey$cursor";
                            $sqlrow .= " and $ckey =:$ckey$cursor";
                            $bindList[] = array(":$ckey$cursor" => $cval);
                            $cursor++;
                        }
                        break;
                    case QueryType::Is:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and $ckey  is $cval";
                            $sqlrow .= " and $ckey  is $cval";

                            //$bindList[] =array(":$ckey$cursor"=>$cval);
                            //$cursor++;
                        }
                        break;
                    case QueryType::Maybe:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and (";
                            $sqlrow .= " and (";

                            $instr = '';
                            foreach ($cval as $cckey => $ccval) {
                                $instr .= "$ckey= :$ckey$cursor or ";
                                $bindList[] = array(":$ckey$cursor" => $ccval);
                                $cursor++;
                            }
                            $sql .=substr($instr, 0, strlen($instr) - 3) . ")";
                            $sqlrow .=substr($instr, 0, strlen($instr) - 3) . ")";
                        }
                        break;
                    case QueryType::Range:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and ($ckey between ";
                            $sqlrow .= " and ($ckey between ";
                            $instr = '';
                            foreach ($cval as $cckey => $ccval) {
                                $instr .= ":$ckey$cursor and ";
                                $bindList[] = array(":$ckey$cursor" => $ccval);
                                $cursor++;
                            }
                            $sql .=substr($instr, 0, strlen($instr) - 4) . ")";
                            $sqlrow .=substr($instr, 0, strlen($instr) - 4) . ")";
                        }
                        break;
                    case QueryType::PlusThan:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and TO_DAYS(NOW()) - TO_DAYS($ckey) > :$ckey$cursor";
                            $sqlrow .= " and TO_DAYS(NOW()) - TO_DAYS($ckey) > :$ckey$cursor";
                            $bindList[] = array(":$ckey$cursor" => $cval);
                            $cursor++;
                        }
                        break;
                    case QueryType::LessThan:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and $ckey < :$ckey$cursor";
                            $sqlrow .= " and $ckey < :$ckey$cursor";
                            $bindList[] = array(":$ckey$cursor" => $cval);
                            $cursor++;
                        }
                        break;
                    case QueryType::Like:
                        foreach ($val as $ckey => $cval) {
                            $sql .= " and $ckey like :$ckey$cursor";
                            $sqlrow .= " and $ckey like :$ckey$cursor";
                            $bindList[] = array(":$ckey$cursor" => "%$cval%");
                            $cursor++;
                        }
                        break;
                }
            }
        }

        if (!empty($order) && is_array($order) && count($order) > 0) {
            $sql .= ' order by ';
            $str = '';
            foreach ($order as $key => $val) {
                $str.= format(" $key $val,");
            }
            if (strlen($str) > 0) {
                $str = substr($str, 0, strlen($str) - 1);
            }
            $sql .= $str;
        }

        //判断分页
        if ($paging != null) {
            $sql .= ' limit ' . (($paging['page'] - 1) * $paging['pagesize']) . ',' . $paging['pagesize'];
        }

        $stmt = $this->pdo->prepare($sql);
        $abc = $this->pdo->prepare($sqlrow);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        if (!empty($bindList) && is_array($bindList)) {
            if (!empty($bindList) && is_array($bindList) && count($bindList) > 0) {
                foreach ($bindList as $key => $val) {
                    if (!empty($val) && is_array($val) && count($val) > 0) {
                        foreach ($val as $ckey => $cval) {
                            $stmt->bindValue($ckey, $cval);
                            $abc->bindValue($ckey, $cval);
                        }
                    }
                }
            }
        }
        //取得结果
        $stmt->execute();
        //将结果集转为array
        $list = $stmt->fetchAll();
        $rows = 0;

        $abc->execute();
        foreach ($abc as $item) {
            $rows = $item['count'];
            break;
        }

        $allPage = $rows % $paging["pagesize"] == 0 ? $rows / $paging["pagesize"] : intval($rows / $paging["pagesize"]) + 1;
        return array("rows" => $rows, "pageIndex" => $paging['page'], "allPage" => $allPage, "list" => $list);
    }

    /*
     * 多表联合查询
     */

    function ExcuteManyTable($sql, $where) {
        //预处理sql语句
        $sth = $this->pdo->prepare($sql);
        $sth->setFetchMode(PDO::FETCH_ASSOC);

        //绑定参数结果
        if (!empty($where) && is_array($where)) {
            foreach ($where as $key => $val) {
                $sth->bindValue($key, $val);
            }
        }
        //取得结果
        $sth->execute();
        //将结果集转为array
        $list = $sth->fetchAll();
        //将结果转为array
        /* foreach ($sth as $row){
          $list[] = $row;
          } */
        return array("rows" => count($list), "data" => $list);
    }

    function ExcuteManyTablePager($sql, $sqlrow, $bindList, $pager) {
        //预处理sql语句
        $sth = $this->pdo->prepare($sql);
        $abc = $this->pdo->prepare($sqlrow);
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        //绑定参数结果
        if (!empty($bindList) && is_array($bindList)) {
            if (!empty($bindList) && is_array($bindList) && count($bindList) > 0) {
                foreach ($bindList as $key => $val) {
                    if (!empty($val) && is_array($val) && count($val) > 0) {
                        foreach ($val as $ckey => $cval) {
                            $sth->bindValue($ckey, $cval);
                            $abc->bindValue($ckey, $cval);
                        }
                    }
                }
            }
        }
        //取得结果
        $sth->execute();
        //将结果集转为array
        $list = $sth->fetchAll();

        $abc->execute();
        $rows = 0;
        foreach ($abc as $item) {
            $rows = $item['count'];
            break;
        }
        //将结果转为array
        /* foreach ($sth as $row){
          $list[] = $row;
          } */
        $allPage = $rows % $pager["pagesize"] == 0 ? $rows / $pager["pagesize"] : intval($rows / $pager["pagesize"]) + 1;
        return array("pageNum" => $rows, "pageIndex" => $pager['page'], "pageSize" => $pager['pagesize'], "pageTotal" => $allPage, "data" => $list);
    }

}

?>
