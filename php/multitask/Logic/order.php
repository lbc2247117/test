<?php

require '../Scheduler/scheduler.php';

class OrderLogic
{
    public static function getUserOrder($uid)
    {
        if (empty($uid)) {
            return ['code' => 200, 'msg' => '缺少参数', 'data' => []];
        }
        $where['uid'] = $uid;
        $result = scheduler::getUserOrder($where);
        if (empty($result['code']) || $result['code'] != 200) {
            return $result;
        }
        foreach ($result['data'] as $k => $v) {
            $result['data'][$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
        }
        return $result;

    }
}