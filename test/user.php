<?php
/**
 * Created by PhpStorm.
 * User: liubocheng
 * Date: 17-3-26
 * Time: 下午5:11
 */
namespace test\controller;

class user{
    private $_age;
    function __construct()
    {
        $this->_age='30';
    }
    public function getAge(){
        return $this->_age;
    }
}