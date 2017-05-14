<?php

/**
 * 补欠款
 *
 * @author 自动生成的实体类
 * @copyright (c) 2015, 非时序集团
 * @version 1.0
 */

namespace Models;

$GLOBALS['/Models/work_later.php'] = 1;

use Models\Base\Model;

class work_later extends Model {

    public static $field_id;
    public static $field_userid;
    public static $field_username;
    public static $field_depart;
    public static $field_lateType;
    public static $field_later_day;
    public static $field_later_time;
    public static $field_add_time;
    public static $field_add_user;
    public static $MODEL_SCHEMA;

    static function init_schema() {
        self::$field_id = Model::define_primary_key('id', 'int', 0, true);
        self::$field_userid = Model::define_field('userid', 'int', 0, true);
        self::$field_username = Model::define_field('username', 'string', NULL);
        self::$field_depart = Model::define_field('depart', 'string', NULL);
        self::$field_lateType = Model::define_field('lateType', 'string', NULL);
        self::$field_later_day = Model::define_field('later_day', 'date', NULL);
        self::$field_later_time = Model::define_field('later_time', 'int', NULL);
        self::$field_add_time = Model::define_field('add_time', 'date', NULL);
        self::$field_add_user = Model::define_field('add_user', 'string', NULL);
        self::$MODEL_SCHEMA = Model::build_schema('work_later', array(
                    self::$field_id,
                    self::$field_userid,
                    self::$field_username,
                    self::$field_depart,
                    self::$field_lateType,
                    self::$field_later_day,
                    self::$field_later_time,
                    self::$field_add_time,
                    self::$field_add_user
        ));
    }

    public function get_id() {
        return $this->get_field_value(self::$field_id);
    }

    public function set_id($id) {
        $this->set_field_value(self::$field_id, $id);
    }
    
    public function get_userid() {
        return $this->get_field_value(self::$field_userid);
    }

    public function set_userid($userid) {
        $this->set_field_value(self::$field_userid, $userid);
    }

    public function get_username() {
        return $this->get_field_value(self::$field_username);
    }

    public function set_username($username) {
        $this->set_field_value(self::$field_username, $username);
    }

    public function get_depart() {
        return $this->get_field_value(self::$field_depart);
    }

    public function set_depart($later_type) {
        $this->set_field_value(self::$field_depart, $later_type);
    }
    
    public function get_lateType() {
        return $this->get_field_value(self::$field_lateType);
    }

    public function set_lateType($lateType) {
        $this->set_field_value(self::$field_lateType, $lateType);
    }

    public function get_later_day() {
        return $this->get_field_value(self::$field_later_day);
    }

    public function set_later_day($later_day) {
        $this->set_field_value(self::$field_later_day, $later_day);
    }

    public function get_later_time() {
        return $this->get_field_value(self::$field_later_time);
    }

    public function set_later_time($later_time) {
        $this->set_field_value(self::$field_later_time, $later_time);
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

work_later::init_schema();
