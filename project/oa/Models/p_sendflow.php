<?php

/**
 * 提交流量业绩
 *
 * @author 自动生成的实体类
 * @copyright (c) 2015, 非时序集团
 * @version 1.0
 */

namespace Models;

$GLOBALS['/Models/p_sendflow.php'] = 1;

use Models\Base\Model;

class p_sendflow extends Model {

    public static $field_id;
    public static $field_add_time;
    public static $field_status;
    public static $field_rception_staff;
    public static $field_rception_staff_id;
    public static $field_serviceEmployee_id;
    public static $field_serviceEmployee;
    public static $field_isfirst;
    public static $field_ww;
    public static $field_qq;
    public static $field_hoturl;
    public static $field_goodsbase;
    public static $field_level;
    public static $field_point;
    public static $field_day_point;
    public static $field_remark;
    public static $MODEL_SCHEMA;

    static function init_schema() {
        self::$field_id = Model::define_primary_key('id', 'int', 0, true);
        self::$field_add_time = Model::define_field('add_time', 'datetime', NULL);
        self::$field_status = Model::define_field('status', 'int', 0);
        self::$field_rception_staff = Model::define_field('rception_staff', 'string', NULL);
        self::$field_rception_staff_id = Model::define_field('rception_staff_id', 'int', 0);
        self::$field_serviceEmployee_id = Model::define_field('serviceEmployee_id', 'int', 0);
        self::$field_serviceEmployee = Model::define_field('serviceEmployee', 'string', NULL);
        self::$field_isfirst = Model::define_field('isfirst', 'int', 0);
        self::$field_ww = Model::define_field('ww', 'string', NULL);
        self::$field_qq = Model::define_field('qq', 'string', NULL);
        self::$field_hoturl = Model::define_field('hoturl', 'string', NULL);
        self::$field_goodsbase = Model::define_field('goodsbase', 'string', NULL);
        self::$field_level = Model::define_field('level', 'string', NULL);
        self::$field_point = Model::define_field('point', 'float', 0.00);
        self::$field_day_point = Model::define_field('day_point', 'int', 0);
        self::$field_remark = Model::define_field('remark', 'string', NULL);
        self::$MODEL_SCHEMA = Model::build_schema('p_sendflow', array(
                    self::$field_id,
                    self::$field_add_time,
                    self::$field_status,
                    self::$field_rception_staff,
                    self::$field_rception_staff_id,
                    self::$field_serviceEmployee_id,
                    self::$field_serviceEmployee,
                    self::$field_isfirst,
                    self::$field_ww,
                    self::$field_qq,
                    self::$field_hoturl,
                    self::$field_goodsbase,
                    self::$field_level,
                    self::$field_point,
                    self::$field_day_point,
                    self::$field_remark
        ));
    }

    public function get_id() {
        return $this->get_field_value(self::$field_id);
    }

    public function set_id($id) {
        $this->set_field_value(self::$field_id, $id);
    }

    public function get_add_time() {
        return $this->get_field_value(self::$field_add_time);
    }

    public function set_add_time($add_time) {
        $this->set_field_value(self::$field_add_time, $add_time);
    }

    public function get_status() {
        return $this->get_field_value(self::$field_status);
    }

    public function set_status($status) {
        $this->set_field_value(self::$field_status, $status);
    }

    public function get_rception_staff_id() {
        return $this->get_field_value(self::$field_rception_staff_id);
    }

    public function set_rception_staff_id($rception_staff_id) {
        $this->set_field_value(self::$field_rception_staff_id, $rception_staff_id);
    }

    public function get_rception_staff() {
        return $this->get_field_value(self::$field_rception_staff);
    }

    public function set_rception_staff($rception_staff) {
        $this->set_field_value(self::$field_rception_staff, $rception_staff);
    }

    public function get_serviceEmployee_id() {
        return $this->get_field_value(self::$field_serviceEmployee_id);
    }

    public function set_serviceEmployee_id($serviceEmployee_id) {
        $this->set_field_value(self::$field_serviceEmployee_id, $serviceEmployee_id);
    }

    public function get_serviceEmployee() {
        return $this->get_field_value(self::$field_serviceEmployee);
    }

    public function set_serviceEmployee($serviceEmployee) {
        $this->set_field_value(self::$field_serviceEmployee, $serviceEmployee);
    }

    public function get_isfirst() {
        return $this->get_field_value(self::$field_isfirst);
    }

    public function set_isfirst($isfirst) {
        $this->set_field_value(self::$field_isfirst, $isfirst);
    }

    public function get_ww() {
        return $this->get_field_value(self::$field_ww);
    }

    public function set_ww($ww) {
        $this->set_field_value(self::$field_ww, $ww);
    }

    public function get_qq() {
        return $this->get_field_value(self::$field_qq);
    }

    public function set_qq($qq) {
        $this->set_field_value(self::$field_qq, $qq);
    }

    public function get_hoturl() {
        return $this->get_field_value(self::$field_hoturl);
    }

    public function set_hoturl($hoturl) {
        $this->set_field_value(self::$field_hoturl, $hoturl);
    }

    public function get_goodsbase() {
        return $this->get_field_value(self::$field_goodsbase);
    }

    public function set_goodsbase($goodsbase) {
        $this->set_field_value(self::$field_goodsbase, $goodsbase);
    }

    public function get_level() {
        return $this->get_field_value(self::$field_level);
    }

    public function set_level($level) {
        $this->set_field_value(self::$field_level, $level);
    }

    public function get_point() {
        return $this->get_field_value(self::$field_point);
    }

    public function set_point($point) {
        $this->set_field_value(self::$field_point, $point);
    }

    public function get_day_point() {
        return $this->get_field_value(self::$fielf_day_point);
    }

    public function set_day_point($day_point) {
        $this->set_field_value(self::$field_day_point, $day_point);
    }

    public function get_remark() {
        return $this->get_field_value(self::$field_remark);
    }

    public function set_remark($remark) {
        $this->set_field_value(self::$field_remark, $remark);
    }

    public function to_array(array $options = array(), callable $func = NULL) {
        $arr = parent::to_array($options, $func);
        //unset($arr[self::$field_id['name']]);
        //unset($arr[self::$field_name['name']]);
        //unset($arr[self::$field_ww['name']]);
        //unset($arr[self::$field_mobile['name']]);
        //unset($arr[self::$field_fill_sum['name']]);
        //unset($arr[self::$field_add_time['name']]);
        //unset($arr[self::$field_add_name['name']]);
        //unset($arr[self::$field_add_name_id['name']]);
        //unset($arr[self::$field_payment['name']]);
        //unset($arr[self::$field_channel['name']]);
        //unset($arr[self::$field_attachment['name']]);
        //unset($arr[self::$field_customer['name']]);
        //unset($arr[self::$field_customer_id['name']]);
        //unset($arr[self::$field_nick_name['name']]);
        //unset($arr[self::$field_customer2['name']]);
        //unset($arr[self::$field_customer_id2['name']]);
        //unset($arr[self::$field_nick_name2['name']]);
        //unset($arr[self::$field_sale_id['name']]);
        //unset($arr[self::$field_isQQTeach['name']]);
        return $arr;
    }

}

p_sendflow::init_schema();
