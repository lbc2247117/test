<?php

/**
 * Created by PhpStorm.
 * User: liubocheng
 * Date: 17-6-30
 * Time: 下午3:55
 */

require __DIR__ . '/../Service/UserServer.php';
require __DIR__ . '/../Service/OrderServer.php';


class Middle
{
    public static function getData($class, $action, $param)
    {
        $classReflection = new ReflectionClass($class);
        $method = $classReflection->getMethod($action);
        $result = $method->invokeArgs($classReflection->newInstance(), $param);
        return $result;
    }
}



//use Service\User;
//
//class Middle
//{
//    public static function getData($class, $action, $param)
//    {
//       var_dump($class);
//        $rpc = false;
//        if ($rpc) {
//            return self:: getDataFromRPC($class, $action, $param);
//        } else {
//            return self:: getDateFromCode($class, $action, $param);
//        }
//    }
//
//    public static function getDataFromRPC($class, $action, $param)
//    {
//        $client = Client::create(C($class));
//        return $client->$action($param);
//    }
//
//    public static function getDateFromCode($class, $action, $param)
//    {
//        if (!class_exists($class)) {
//            return ['code' => 300, 'msg' => '类不存在'];
//        }
//        $classReflection = new ReflectionClass($class);
//        if (!$classReflection->hasMethod($action)) {
//            return ['code' => 300, 'msg' => '方法不存在'];
//        }
//        return $class:: $action($param);
//
//    }
//}
//
//$result = Middle::getData(User::class, 'getUserInfo', ['id' => 1]);
//exit(json_encode($result));