<?php
/**
 * Created by PhpStorm.
 * User: liubocheng
 * Date: 17-7-1
 * Time: 下午6:59
 */

namespace Service;

require   '../Middles/OrderMiddle.php';

class OrderServer implements \OrderMiddle
{
    public function getInfo()
    {
        return ['code' => 200, 'msg' => '成功', 'data' => ['username' => 'liubocheng']];
    }

    public function getList()
    {
        $data = [];
        for ($i = 1; $i < 3; $i++) {
            $data[$i]['user_id'] = 1;
            $data[$i]['pay_no'] = $i;
            $data[$i]['money'] = 100 * $i;
            $data[$i]['pay_money'] = 80 * $i;
            $data[$i]['create_time'] = time() - $i * 10000;
        }
        return ['code' => 200, 'msg' => '成功', 'data' => $data];
    }

    public function addData()
    {
        return ['code' => 200, 'msg' => '成功', 'data' => ['username' => 'liubocheng']];
    }

    public function updateData()
    {
        return ['code' => 200, 'msg' => '成功', 'data' => ['username' => 'liubocheng']];
    }

    public function deleteData()
    {
        return ['code' => 200, 'msg' => '成功', 'data' => ['username' => 'liubocheng']];
    }
}

