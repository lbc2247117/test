<?php
/**
 * Created by PhpStorm.
 * User: liubocheng
 * Date: 17-5-19
 * Time: 上午9:56
 */
$worker= new GearmanWorker();
$worker->addServer();
$worker->addFunction("reverse", "my_reverse_function");
while ($worker->work());

function my_reverse_function($job)
{
    return strrev($job->workload());
}