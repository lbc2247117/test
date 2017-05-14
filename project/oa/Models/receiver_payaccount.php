<?php

/**
 * 代运营定金从表
 *
 * @author 自动生成的实体类
 * @copyright (c) 2015, 非时序集团
 * @version 1.0
 */

namespace Models;

$GLOBALS['/Models/receiver_payaccount.php'] = 1;

use Models\Base\Model;

class receiver_payaccount extends Model {

    public static $field_id;
    public static $field_general_operal_id;
    public static $field_pay_account_type;
    public static $field_pay_money;
    public static $field_pay_rate;
    public static $field_transfer_time;
    public static $field_pay_username;
    public static $field_add_time;
    public static $field_add_user;
    public static $field_platform_sales;
    public static $field_platform_sales_id;
    public static $field_approve_status;
    public static $field_payment_method;
    public static $MODEL_SCHEMA;

    static function init_schema() {
        self::$field_id = Model::define_primary_key('id', 'int', 0, true);
        self::$field_general_operal_id = Model::define_field('general_operal_id', 'int', 0);
        self::$field_pay_account_type = Model::define_field('pay_account_type', 'string', NULL);
        self::$field_pay_money = Model::define_field('pay_money', 'float', 0.00);
        self::$field_pay_rate = Model::define_field('pay_rate', 'float', 0.00);
        self::$field_transfer_time = Model::define_field('transfer_time', 'datetime', NULL);
        self::$field_pay_username = Model::define_field('pay_username', 'string', NULL);
        self::$field_add_time = Model::define_field('add_time', 'datetime', NULL);
        self::$field_add_user = Model::define_field('add_user', 'string', NULL);
        self::$field_platform_sales = Model::define_field('platform_sales', 'string', NULL);
        self::$field_platform_sales_id = Model::define_field('platform_sales_id', 'int', 0);
        self::$field_approve_status = Model::define_field("approve_status", "int", 1);
        self::$field_payment_method = Model::define_field('payment_method', 'string', NULL);
        self::$MODEL_SCHEMA = Model::build_schema('receiver_payaccount', array(
                    self::$field_id,
                    self::$field_general_operal_id,
                    self::$field_pay_account_type,
                    self::$field_pay_money,
                    self::$field_pay_rate,
                    self::$field_transfer_time,
                    self::$field_pay_username,
                    self::$field_add_time,
                    self::$field_add_user,
                    self::$field_platform_sales,
                    self::$field_platform_sales_id,
                    self::$field_approve_status,
                    self::$field_payment_method
        ));
    }

    public function get_id() {
        return $this->get_field_value(self::$field_id);
    }

    public function set_id($id) {
        $this->set_field_value(self::$field_id, $id);
    }

    public function get_general_operal_id() {
        return $this->get_field_value(self::$field_general_operal_id);
    }

    public function set_general_operal_id($general_operal_id) {
        $this->set_field_value(self::$field_general_operal_id, $general_operal_id);
    }

    public function get_pay_account_type() {
        return $this->get_field_value(self::$field_pay_account_type);
    }

    public function set_pay_account_type($pay_account_type) {
        $this->set_field_value(self::$field_pay_account_type, $pay_account_type);
    }

    public function get_pay_money() {
        return $this->get_field_value(self::$field_pay_money);
    }

    public function set_pay_money($pay_money) {
        $this->set_field_value(self::$field_pay_money, $pay_money);
    }

    public function get_pay_rate() {
        return $this->get_field_value(self::$field_pay_rate);
    }

    public function set_pay_rate($pay_rate) {
        $this->set_field_value(self::$field_pay_rate, $pay_rate);
    }

    public function get_transfer_time() {
        return $this->get_field_value(self::$field_transfer_time);
    }

    public function set_transfer_time($transfer_time) {
        $this->set_field_value(self::$field_transfer_time, $transfer_time);
    }

    public function get_pay_username() {
        return $this->get_field_value(self::$field_pay_username);
    }

    public function set_pay_username($pay_username) {
        $this->set_field_value(self::$field_pay_username, $pay_username);
    }

    public function get_add_time() {
        return $this->get_field_value(self::$field_add_time);
    }

    public function set_add_time($add_time) {
        $this->set_field_value(self::$field_add_time, $add_time);
    }

    public function get_add_user() {
        return $this->get_field_value(self::$field_add_user);
    }

    public function set_add_user($add_user) {
        $this->set_field_value(self::$field_add_user, $add_user);
    }

    public function get_platform_sales() {
        return $this->get_field_value(self::$field_platform_sales);
    }

    public function set_platform_sales($platform_sales) {
        $this->set_field_value(self::$field_platform_sales, $platform_sales);
    }

    public function get_platform_sales_id() {
        return $this->get_field_value(self::$field_platform_sales_id);
    }

    public function set_platform_sales_id($platform_sales_id) {
        $this->set_field_value(self::$field_platform_sales_id, $platform_sales_id);
    }

    public function get_approve_status() {
        return $this->get_field_value(self::$field_approve_status);
    }

    public function set_approve_status($approve_status) {
        $this->set_field_value(self::$field_approve_status, $approve_status);
    }

    public function get_payment_method() {
        return $this->get_field_value(self::$field_payment_method);
    }

    public function set_payment_method($payment_method) {
        $this->set_field_value(self::$field_payment_method, $payment_method);
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

receiver_payaccount::init_schema();
