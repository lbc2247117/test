<?php
/**
 * Created by PhpStorm.
 * User: liubocheng
 * Date: 17-7-1
 * Time: 下午6:59
 */

namespace Service;
require __DIR__ . '/../Middles/UserMiddle.php';

class UserServer implements \UserMiddle
{
    public function getInfo()
    {
        $data['uid'] = 1;
        $data['username'] = '刘波成';
        $data['invitation'] = '666666';
        $data['invitation_from'] = '888888';
        $data['avatar'] = 'upload/app/20161014/6511dbc368c37dcc3a3d7a104bb87998.jpg';
        $data['mobile'] = '18780810899';
        $data['type'] = 2;
        $data['uuid'] = '123ae98ce3ff2ab343b1692bffa123cb';
        return ['code' => 200, 'msg' => '成功', 'data' => $data];
    }

    public function getList()
    {
        return ['code' => 200, 'msg' => '成功', 'data' => ['username' => 'liubocheng']];
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

