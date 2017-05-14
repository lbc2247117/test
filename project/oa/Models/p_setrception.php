<?php

/**
 * 指派班主任
 *
 * @author 自动生成的实体类
 * @copyright (c) 2015, 非时序集团
 * @version 1.0
 */

namespace Models;

$GLOBALS['/Models/p_setrception.php'] = 1;

use Models\Base\Model;

class p_setrception extends Model {

    public static $field_id;
    public static $field_add_time;
    public static $field_rception_sale;
    public static $field_rception_sale_id;
    public static $field_maxnumber;
    public static $field_curnumber;
    public static $field_maxmoney;
    public static $MODEL_SCHEMA;

    static function init_schema() {
        self::$field_id = Model::define_primary_key('id', 'int', 0, true);
        self::$field_add_time = Model::define_field('add_time', 'datetime', NULL);
        self::$field_rception_sale = Model::define_field('rception_sale', 'string', NULL);
        self::$field_rception_sale_id = Model::define_field('rception_sale_id', 'int', 0);
        self::$field_maxnumber = Model::define_field('maxnumber', 'int', 0);
        self::$field_curnumber = Model::define_field('curnumber', 'int', 0);
        self::$field_maxmoney = Model::define_field('maxmoney', 'float', 0.00);
        self::$MODEL_SCHEMA = Model::build_schema('p_setrception', array(
                    self::$field_id,
                    self::$field_add_time,
                    self::$field_rception_sale,
                    self::$field_rception_sale_id,
                    self::$field_maxnumber,
                    self::$field_curnumber,
                    self::$field_maxmoney
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

    public function get_rception_sale() {
        return $this->get_field_value(self::$field_rception_sale);
    }

    public function set_rception_sale($rception_sale) {
        $this->set_rception_sale(self::$field_rception_sale, $rception_sale);
    }

    public function get_rception_sale_id() {
        return $this->get_field_value(self::$field_rception_sale_id);
    }

    public function set_rception_sale_id($rception_sale_id) {
        $this->set_field_value(self::$field_rception_sale_id, $rception_sale_id);
    }

    public function get_maxnumber() {
        return $this->get_field_value(self::$field_maxnumber);
    }

    public function set_maxnumber($maxnumber) {
        $this->set_field_value(self::$field_maxnumber, $maxnumber);
    }

    public function get_curnumber() {
        return $this->get_field_value(self::$field_curnumber);
    }

    public function set_curnumber($curnumber) {
        $this->set_field_value(self::$field_curnumber, $curnumber);
    }

    public function get_maxmoney() {
        return $this->get_field_value(self::$field_maxmoney);
    }

    public function set_maxmoney($maxmoney) {
        $this->set_field_value(self::$field_maxmoney, $maxmoney);
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

p_setrception::init_schema();
