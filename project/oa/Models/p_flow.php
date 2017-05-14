<?php

/**
 * 流量业绩
 *
 * @author 自动生成的实体类
 * @copyright (c) 2015, 非时序集团
 * @version 1.0
 */

namespace Models;

$GLOBALS['/Models/p_flow.php'] = 1;

use Models\Base\Model;

class p_flow extends Model {

    public static $field_id;
    public static $field_add_time;
    public static $field_allot_time;
    public static $field_username;
    public static $field_mobile;
    public static $field_qq;
    public static $field_paymoney;
    public static $field_tradeNo;
    public static $field_rception_staff;
    public static $field_rception_staff_id;
    public static $field_customer;
    public static $field_customer_id;
    public static $field_remark;
    public static $field_point;
    public static $field_is_refund;
    public static $MODEL_SCHEMA;

    static function init_schema() {
        self::$field_id = Model::define_primary_key('id', 'int', 0, true);
        self::$field_add_time = Model::define_field('add_time', 'datetime', NULL);
        self::$field_allot_time = Model::define_field('allot_time', 'datetime', NULL);
        self::$field_username = Model::define_field('username', 'string', NULL);
        self::$field_mobile = Model::define_field('mobile', 'string', NULL);
        self::$field_qq = Model::define_field('qq', 'string', NULL);
        self::$field_paymoney = Model::define_field('paymoney', 'float', 0.00);
        self::$field_tradeNo = Model::define_field('tradeNo', 'string', NULL);
        self::$field_rception_staff = Model::define_field('rception_staff', 'string', NULL);
        self::$field_rception_staff_id = Model::define_field('rception_staff_id', 'int', 0);
        self::$field_customer = Model::define_field('customer', 'string', NULL);
        self::$field_customer_id = Model::define_field('customer_id', 'int', 0);
        self::$field_remark = Model::define_field('remark', 'string', NULL);
        self::$field_point = Model::define_field('point', 'int', 0);
        self::$field_is_refund = Model::define_field('is_refund', 'int', 0);
        self::$MODEL_SCHEMA = Model::build_schema('p_flow', array(
                    self::$field_id,
                    self::$field_add_time,
                    self::$field_allot_time,
                    self::$field_username,
                    self::$field_mobile,
                    self::$field_qq,
                    self::$field_paymoney,
                    self::$field_tradeNo,
                    self::$field_rception_staff,
                    self::$field_rception_staff_id,
                    self::$field_customer,
                    self::$field_customer_id,
                    self::$field_point,
                    self::$field_is_refund,
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

    public function get_allot_time() {
        return $this->get_field_value(self::$field_allot_time);
    }

    public function set_allot_time($allot_time) {
        $this->set_field_value(self::$field_allot_time, $allot_time);
    }

    public function get_mobile() {
        return $this->get_field_value(self::$field_mobile);
    }

    public function set_mobile($mobile) {
        $this->set_field_value(self::$field_mobile, $mobile);
    }

    public function get_qq() {
        return $this->get_field_value(self::$field_qq);
    }

    public function set_qq($qq) {
        $this->set_field_value(self::$field_qq, $qq);
    }

    public function get_paymoney() {
        return $this->get_field_value(self::$field_paymoney);
    }

    public function set_paymoney($paymoney) {
        $this->set_field_value(self::$field_paymoney, $paymoney);
    }

    public function get_tradeNo() {
        return $this->get_field_value(self::$field_point);
    }

    public function set_tradeNo($tradeNo) {
        $this->set_field_value(self::$field_point, $tradeNo);
    }

    public function get_rception_staff() {
        return $this->get_field_value(self::$field_rception_staff);
    }

    public function set_rception_staff($rception_staff) {
        $this->set_field_value(self::$field_rception_staff, $rception_staff);
    }

    public function get_rception_staff_id() {
        return $this->get_field_value(self::$field_rception_staff_id);
    }

    public function set_rception_staff_id($rception_staff_id) {
        $this->set_field_value(self::$field_rception_staff_id, $rception_staff_id);
    }

    public function get_customer() {
        return $this->get_field_value(self::$field_customer);
    }

    public function set_customer($customer) {
        $this->set_field_value(self::$field_customer, $customer);
    }

    public function get_customer_id() {
        return $this->get_field_value(self::$field_customer_id);
    }

    public function set_customer_id($customer_id) {
        $this->set_field_value(self::$field_customer_id, $customer_id);
    }

    public function get_remark() {
        return $this->get_field_value(self::$field_remark);
    }

    public function set_remark($remark) {
        $this->set_field_value(self::$field_remark, $remark);
    }

    public function get_point() {
        return $this->get_field_value(self::$field_point);
    }

    public function set_point($point) {
        $this->set_field_value(self::$field_point, $point);
    }

    public function get_is_refund() {
        return $this->get_field_value(self::$field_is_refund);
    }

    public function set_is_refund($is_refund) {
        $this->set_field_value(self::$field_is_refund, $is_refund);
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

p_flow::init_schema();
