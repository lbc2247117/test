<?php
/**
 * Created by PhpStorm.
 * User: liubocheng
 * Date: 17-3-26
 * Time: 下午5:10
 */
namespace test;

use test\controller\user;



class index{

    public static function getAge(){

        // 注册AUTOLOAD方法
       spl_autoload_register('self::autoload');
        register_shutdown_function('self::fatalError');
        $user= new user();
        $age= $user->getAge();
        exit($age);
    }
    public static function autoload($class){
        $filename       =  'user.php';
        require $filename;
    }
    public static function fatalError(){
        var_dump(error_get_last());
    }
}

index::getAge();