<?php

require '../Logic/order.php';

class orderController
{

    public function get_user_order()
    {
        $uid = 70;
        exit(json_encode(orderLogic::getUserOrder($uid)));
    }
}

$order = new orderController();
$order->get_user_order();