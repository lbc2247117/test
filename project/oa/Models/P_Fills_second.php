<?php

/**
 * 二销业绩
 *
 * @author 自动生成的实体类
 * @copyright (c) 2015, 非时序集团
 * @version 1.0
 */

namespace Models;

$GLOBALS['/Models/P_Fills_second.php'] = 1;

use Models\Base\Model;

class P_Fills_second extends Model {

    public static $field_id;
    public static $field_type;
    public static $field_add_time;
    public static $field_ww;
    public static $field_qq;
    public static $field_name;
    public static $field_platform_num;
    public static $field_play_price;
    public static $field_fill_sum;
    public static $field_customer;
    public static $field_customer_id;
    public static $field_platform_rception;
    public static $field_platform_rception_id;
    public static $field_add_name;
    public static $field_parent_id;
    public static $field_remark;
    public static $field_headmaster;
    public static $field_headmaster_id;
    public static $field_customer_type;
    public static $field_second_type;
    public static $field_server_sales;
    public static $field_server_sales_id;
    public static $field_payment_method;
    public static $field_rception_money;
    public static $field_rcept_account;
    public static $field_pay_user;
    public static $field_st_is_approve;
    public static $field_transfer_time;
    public static $MODEL_SCHEMA;

    static function init_schema() {
        self::$field_id = Model::define_primary_key('id', 'int', 0, true);
        self::$field_type = Model::define_field('type', 'int', 0);
        self::$field_add_time = Model::define_field('add_time', 'datetime', NULL);
        self::$field_ww = Model::define_field('ww', 'string', NULL);
        self::$field_qq = Model::define_field('qq', 'string', NULL);
        self::$field_name = Model::define_field('name', 'string', NULL);
        self::$field_platform_num = Model::define_field('platform_num', 'string', NULL);
        self::$field_play_price = Model::define_field('play_price', 'float', 0.00);
        self::$field_fill_sum = Model::define_field('fill_sum', 'float', 0.00);
        self::$field_customer = Model::define_field('customer', 'string', NULL);
        self::$field_customer_id = Model::define_field('customer_id', 'int', 0);
        self::$field_platform_rception = Model::define_field('platform_rception', 'string', NULL);
        self::$field_platform_rception_id = Model::define_field('platform_rception_id', 'int', 0);
        self::$field_add_name = Model::define_field('add_name', 'string', NULL);
        self::$field_parent_id = Model::define_field('parent_id', 'int', 0);
        self::$field_remark = Model::define_field('remark', 'string', NULL);
        self::$field_headmaster = Model::define_field('headmaster', 'string', NULL);
        self::$field_headmaster_id = Model::define_field('headmaster_id', 'int', 0);
        self::$field_customer_type = Model::define_field('customer_type', 'string', NULL);
        self::$field_second_type = Model::define_field('second_type', 'string', NULL);
        self::$field_server_sales = Model::define_field('server_sales', 'string', NULL);
        self::$field_server_sales_id = Model::define_field('server_sales_id', 'int', 0);
        self::$field_payment_method = Model::define_field('payment_method', 'string', NULL);
        self::$field_rception_money = Model::define_field('rception_money', 'float', 0.00);
        self::$field_rcept_account = Model::define_field('rcept_account', 'string', NULL);
        self::$field_pay_user = Model::define_field('pay_user', 'string', NULL);
        self::$field_st_is_approve = Model::define_field('st_is_approve', 'int', 0);
        self::$field_transfer_time=Model::define_field("transfer_time", 'datetime',NULL);
        self::$MODEL_SCHEMA = Model::build_schema('P_Fills_second', array(
                    self::$field_id,
                    self::$field_type,
                    self::$field_add_time,
                    self::$field_ww,
                    self::$field_qq,
                    self::$field_name,
                    self::$field_platform_num,
                    self::$field_play_price,
                    self::$field_fill_sum,
                    self::$field_customer,
                    self::$field_customer_id,
                    self::$field_platform_rception,
                    self::$field_platform_rception_id,
                    self::$field_add_name,
                    self::$field_parent_id,
                    self::$field_remark,
                    self::$field_headmaster,
                    self::$field_customer_type,
                    self::$field_second_type,
                    self::$field_server_sales,
                    self::$field_server_sales_id,
                    self::$field_payment_method,
                    self::$field_headmaster_id,
                    self::$field_rception_money,
                    self::$field_rcept_account,
                    self::$field_pay_user,
                    self::$field_st_is_approve,
                    self::$field_transfer_time
        ));
    }

    public function get_id() {
        return $this->get_field_value(self::$field_id);
    }

    public function set_id($id) {
        $this->set_field_value(self::$field_id, $id);
    }

    public function get_type() {
        return $this->get_field_value(self::$field_type);
    }

    public function set_type($type) {
        $this->set_field_value(self::$field_type, $type);
    }

    public function get_add_time() {
        return $this->get_field_value(self::$field_add_time);
    }

    public function set_add_time($add_time) {
        $this->set_field_value(self::$field_add_time, $add_time);
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

    public function get_name() {
        return $this->get_field_value(self::$field_name);
    }

    public function set_name($name) {
        $this->set_field_value(self::$field_name, $name);
    }

    public function get_platform_num() {
        return $this->get_field_value(self::$field_platform_num);
    }

    public function set_platform_num($mobile) {
        $this->set_field_value(self::$field_platform_num, $mobile);
    }

    public function get_play_price() {
        return $this->get_field_value(self::$field_play_price);
    }

    public function set_play_price($play_price) {
        $this->set_field_value(self::$field_play_price, $play_price);
    }

    public function get_fill_sum() {
        return $this->get_field_value(self::$field_fill_sum);
    }

    public function set_fill_sum($fill_sum) {
        $this->set_field_value(self::$field_fill_sum, $fill_sum);
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

    public function get_platform_rception() {
        return $this->get_field_value(self::$field_platform_rception);
    }

    public function set_platform_rception($platform_rception) {
        $this->set_field_value(self::$field_platform_rception, $platform_rception);
    }

    public function get_platform_rception_id() {
        return $this->get_field_value(self::$field_platform_rception_id);
    }

    public function set_platform_rception_id($platform_rception_id) {
        $this->set_field_value(self::$field_platform_rception_id, $platform_rception_id);
    }

    public function get_add_name() {
        return $this->get_field_value(self::$field_add_name);
    }

    public function set_add_name($add_name) {
        $this->set_field_value(self::$field_add_name, $add_name);
    }

    public function get_parent_id() {
        return $this->get_field_value(self::$field_parent_id);
    }

    public function set_parent_id($parent_id) {
        $this->set_field_value(self::$field_parent_id, $parent_id);
    }

    public function get_remark() {
        return $this->get_field_value(self::$field_remark);
    }

    public function set_remark($remark) {
        $this->set_field_value(self::$field_remark, $remark);
    }

    public function get_headmaster() {
        return $this->get_field_value(self::$field_headmaster);
    }

    public function set_headmaster($headmaster) {
        $this->set_field_value(self::$field_headmaster, $headmaster);
    }

    public function get_headmaster_id() {
        $this->get_field_value(self::$field_headmaster_id);
    }

    public function set_headmaster_id($headmaster_id) {
        $this->set_field_value(self::$field_headmaster_id, $headmaster_id);
    }

    public function get_customer_type() {
        return $this->get_field_value(self::$field_customer_type);
    }

    public function set_customer_type($customer_type) {
        $this->set_field_value(self::$field_customer_type, $customer_type);
    }

    public function get_second_type() {
        return $this->get_field_value(self::$field_second_type);
    }

    public function set_second_type($second_type) {
        $this->set_field_value(self::$field_second_type, $second_type);
    }

    public function get_server_sales() {
        return $this->get_field_value(self::$field_server_sales);
    }

    public function set_server_sales($server_sales) {
        $this->set_field_value(self::$field_server_sales, $server_sales);
    }

    public function get_server_sales_id() {
        return $this->get_field_value(self::$field_server_sales_id);
    }

    public function set_server_sales_id($server_sales_id) {
        $this->set_field_value(self::$field_server_sales_id, $server_sales_id);
    }

    public function get_payment_method() {
        return $this->get_field_value(self::$field_payment_method);
    }

    public function set_payment_method($payment_method) {
        $this->set_field_value(self::$field_payment_method, $payment_method);
    }

    public function get_rception_money() {
        $this->get_field_value(self::$field_rception_money);
    }

    public function set_rception_money($rception_money) {
        $this->set_field_value(self::$field_rception_money, $rception_money);
    }

    public function get_rcept_account() {
        return $this->get_field_value(self::$field_rcept_account);
    }

    public function set_rcept_account($rcept_account) {
        $this->set_field_value(self::$field_rcept_account, $rcept_account);
    }

    public function get_pay_user() {
        return $this->get_field_value(self::$field_pay_user);
    }

    public function set_pay_user($pay_user) {
        $this->set_field_value(self::$field_pay_user, $pay_user);
    }

    public function get_st_is_approve() {
        return $this->get_field_value(self::$field_st_is_approve);
    }

    public function set_st_is_approve($st_is_approve) {
        $this->set_field_value(self::$field_st_is_approve, $st_is_approve);
    }
            
    public function get_transfer_time() {
        return $this->get_field_value(self::$field_transfer_time);
    }

    public function set_transfer_time($transfer_time) {
        $this->set_field_value(self::$field_transfer_time, $transfer_time);
    }

    public function to_array(array $options = array(), callable $func = NULL) {
        $arr = parent::to_array($options, $func);
        //unset($arr[self::$field_id['name']]);
        //unset($arr[self::$field_type['name']]);
        //unset($arr[self::$field_add_time['name']]);
        //unset($arr[self::$field_ww['name']]);
        //unset($arr[self::$field_qq['name']]);
        //unset($arr[self::$field_name['name']]);
        //unset($arr[self::$field_mobile['name']]);
        //unset($arr[self::$field_play_price['name']]);
        //unset($arr[self::$field_fill_sum['name']]);
        //unset($arr[self::$field_customer['name']]);
        //unset($arr[self::$field_customer_id['name']]);
        //unset($arr[self::$field_platform_rception['name']]);
        //unset($arr[self::$field_platform_rception_id['name']]);
        //unset($arr[self::$field_add_name['name']]);
        //unset($arr[self::$field_parent_id['name']]);
        //unset($arr[self::$field_remark['name']]);
        return $arr;
    }

}

P_Fills_second::init_schema();
