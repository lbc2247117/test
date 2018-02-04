<?php
/**
 * Created by PhpStorm.
 * User: liubocheng
 * Date: 17-7-1
 * Time: 下午6:08
 */
require_once 'BaseMiddle.php';

interface UserMiddle extends BaseMiddle
{
    public function deleteData();
}