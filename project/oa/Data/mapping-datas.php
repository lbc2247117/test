<?php

/**
 * 映射数据
 *
 * @author ChenHao
 * @copyright (c) 2015, 非时序集团
 * @version 2015/05/27
 */
$GLOBALS['/Data/mapping-datas.php'] = 1;

function get_bool_text_mapping() {
    return array(
        0 => '否',
        1 => '是'
    );
}

function get_sex_text_mapping() {
    return array(
        0 => '女',
        1 => '男'
    );
}

//员工状态映射
function get_employee_status_mapping() {
    return array(
        1 => '正式',
        2 => '试用',
        3 => '离职',
        4 => '停薪留职'
    );
}
