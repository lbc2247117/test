<?php

/**
 * 对账列表
 *
 * @author 自动生成的实体类
 * @copyright (c) 2015, 非时序集团
 * @version 1.0
 */

namespace Models;

$GLOBALS['/Models/st_seller_front_agent.php'] = 1;

use Models\Base\Model;

class st_seller_front_agent extends Model {

    public static $field_id;
    public static $field_addtime;
    public static $field_qq;
    public static $field_seller_front;
    public static $field_submit_money;
    public static $field_alipay_account;
    public static $field_rcept_account;
    public static $field_transfer_time;
    public static $field_transfer_money;
    public static $field_is_question;
    public static $field_compare_time;
    public static $field_compare_user;
    public static $field_remark;
    public static $field_gen_id;
    public static $field_customer_type;
    public static $field_customer;
    public static $field_customer_id;
    public static $MODEL_SCHEMA;

    static function init_schema() {
        self::$field_id = Model::define_primary_key('id', 'int', 0, true);
        self::$field_addtime = Model::define_field('addtime', 'datetime', NULL);
        self::$field_qq = Model::define_field('qq', 'string', NULL);
        self::$field_seller_front = Model::define_field('seller_front', 'string', NULL);
        self::$field_submit_money = Model::define_field('submit_money', 'float', 0.00);
        self::$field_alipay_account = Model::define_field('alipay_account', 'string', NULL);
        self::$field_rcept_account = Model::define_field('rcept_account', 'string', NULL);
        self::$field_transfer_time = Model::define_field('transfer_time', 'datetime', NULL);
        self::$field_transfer_money = Model::define_field('transfer_money', 'float', 0.00);
        self::$field_is_question = Model::define_field('is_question', 'int', 0);
        self::$field_compare_time = Model::define_field('compare_time', 'datetime', NULL);
        self::$field_compare_user = Model::define_field('compare_user', 'string', NULL);
        self::$field_remark = Model::define_field('remark', 'string', NULL);
        self::$field_gen_id = Model::define_field('gen_id', 'int', 0);
        self::$field_customer_type = Model::define_field('customer_type', 'string', NULL);
        self::$field_customer = Model::define_field('customer', 'string', NULL);
        self::$field_customer_id = Model::define_field('customer_id', 'int', 0);


        self::$MODEL_SCHEMA = Model::build_schema('st_seller_front_agent', array(
                    self::$field_id,
                    self::$field_addtime,
                    self::$field_qq,
                    self::$field_seller_front,
                    self::$field_submit_money,
                    self::$field_rcept_account,
                    self::$field_alipay_account,
                    self::$field_transfer_time,
                    self::$field_transfer_money,
                    self::$field_is_question,
                    self::$field_compare_time,
                    self::$field_remark,
                    self::$field_gen_id,
                    self::$field_compare_user,
                    self::$field_customer_type,
                    self::$field_customer,
                    self::$field_customer_id
        ));
    }

    public function get_id() {
        return $this->get_field_value(self::$field_id);
    }

    public function set_id($id) {
        $this->set_field_value(self::$field_id, $id);
    }

    public function get_alipay_account() {
        return $this->get_field_value(self::$field_alipay_account);
    }

    public function set_alipay_account($alipay_account) {
        $this->set_field_value(self::$field_alipay_account, $alipay_account);
    }

    public function get_seller_front() {
        return $this->get_field_value(self::$field_seller_front);
    }

    public function set_seller_front($seller_front) {
        $this->set_field_value(self::$field_seller_front, $seller_front);
    }

    public function get_qq() {
        return $this->get_field_value(self::$field_qq);
    }

    public function set_qq($qq) {
        $this->set_field_value(self::$field_qq, $qq);
    }

    public function get_submit_money() {
        return $this->get_field_value(self::$submit_money);
    }

    public function set_submit_money($submit_money) {
        $this->set_field_value(self::$field_submit_money, $submit_money);
    }

    public function get_addtime() {
        return $this->get_field_value(self::$field_addtime);
    }

    public function set_addtime($addtime) {
        $this->set_field_value(self::$field_addtime, $addtime);
    }

    public function get_transfer_time() {
        return $this->get_field_value(self::$field_transfer_time);
    }

    public function set_transfer_time($transfer_time) {
        $this->set_field_value(self::$field_transfer_time, $transfer_time);
    }

    public function get_transfer_money() {
        return $this->get_field_value(self::$field_transfer_money);
    }

    public function set_transfer_money($transfer_money) {
        $this->set_field_value(self::$field_transfer_money, $transfer_money);
    }

    public function get_is_question() {
        return $this->get_field_value(self::$field_is_question);
    }

    public function set_is_question($is_question) {
        $this->set_field_value(self::$field_is_question, $is_question);
    }

    public function get_compare_time() {
        return $this->get_field_value(self::$field_compare_time);
    }

    public function set_compare_time($compare_time) {
        $this->set_field_value(self::$field_compare_time, $compare_time);
    }

    public function get_compare_user() {
        return $this->get_field_value(self::$field_compare_user);
    }

    public function set_compare_user($compare_user) {
        $this->set_field_value(self::$field_compare_user, $compare_user);
    }

    public function get_remark() {
        return $this->get_field_value(self::$field_remark);
    }

    public function set_remark($remark) {
        $this->set_field_value(self::$field_remark, $remark);
    }

    public function get_gen_id() {
        return $this->get_field_value(self::$field_gen_id);
    }

    public function set_gen_id($gen_id) {
        $this->set_field_value(self::$field_gen_id, $gen_id);
    }

    public function get_rcept_account() {
        return $this->get_field_value(self::$field_rcept_account);
    }

    public function set_rcept_account($rcept_account) {
        $this->set_field_value(self::$field_rcept_account, $rcept_account);
    }

    public function get_customer_type() {
        return $this->get_field_value(self::$field_customer_type);
    }

    public function set_customer_type($customer_type) {
        $this->set_field_value(self::$field_customer_type, $customer_type);
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

    public function to_array(array $options = array(), callable $func = NULL) {
        $arr = parent::to_array($options, $func);
        //unset($arr[self::$field_id['name']]);
        //unset($arr[self::$field_is_edit['name']]);
        //unset($arr[self::$field_add_time['name']]);
        //unset($arr[self::$field_platform_num['name']]);
        //unset($arr[self::$field_qq['name']]);
        //unset($arr[self::$field_sales_numbers['name']]);
        //unset($arr[self::$field_payment_amount['name']]);
        //unset($arr[self::$field_isArrears['name']]);
        //unset($arr[self::$field_platform_sales['name']]);
        //unset($arr[self::$field_platform_sales_id['name']]);
        //unset($arr[self::$field_customer['name']]);
        //unset($arr[self::$field_customer_id['name']]);
        //unset($arr[self::$field_headmaster['name']]);
        //unset($arr[self::$field_headmaster_id['name']]);
        //unset($arr[self::$field_payment_method['name']]);
        //unset($arr[self::$field_share_performance['name']]);
        //unset($arr[self::$field_remark['name']]);
        return $arr;
    }

}

st_seller_front_agent::init_schema();
