<?php

require '../Middles/middle.php';

class Scheduler
{
    public static function getUserOrder($where)
    {

        $userInfo = Middle::getData('\Service\UserServer', 'getInfo', $where);
        $userOrder = Middle::getData('\Service\OrderServer', 'getList', $where);
        $userInfo = $userInfo['data'];
        $userOrder = $userOrder['data'];
        foreach ($userOrder as $k => $v) {
            $userOrder[$k]['username'] = $userInfo['username'];
            $userOrder[$k]['type'] = $userInfo['type'];
            $userOrder[$k]['avatar'] = $userInfo['avatar'];
        }
        return ['code' => 200, 'msg' => '成功', 'data' => $userOrder];

    }
}

