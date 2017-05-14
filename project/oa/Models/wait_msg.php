<?php

/**
 * 流量业绩
 *
 * @author 自动生成的实体类
 * @copyright (c) 2015, 非时序集团
 * @version 1.0
 */

namespace Models;

$GLOBALS['/Models/wait_msg.php'] = 1;

use Models\Base\Model;

class wait_msg extends Model {

    public static $field_id;
    public static $field_add_time;
    public static $field_msgtype;
    public static $field_request_id;
    public static $field_request_name;
    public static $field_task_id;
    public static $field_response_id;
    public static $field_response_name;
    public static $field_status;
    public static $field_dept_id;
    public static $field_remark;
    public static $MODEL_SCHEMA;

    static function init_schema() {
        self::$field_id = Model::define_primary_key('id', 'int', 0, true);
        self::$field_add_time = Model::define_field('add_time', 'datetime', NULL);
        self::$field_msgtype = Model::define_field('msgtype', 'int', 0);
        self::$field_request_id = Model::define_field('request_id', 'int', 0);
        self::$field_request_name = Model::define_field('request_name', 'string', NULL);
        self::$field_task_id = Model::define_field('task_id', 'int', 0);
        self::$field_response_id = Model::define_field('response_id', 'int', 0);
        self::$field_response_name = Model::define_field('response_name', 'string', NULL);
        self::$field_status = Model::define_field('status', 'int', 1);
        self::$field_dept_id = Model::define_field('dept_id', 'int', 0);
        self::$field_remark = Model::define_field('remark', 'string', NULL);
        self::$MODEL_SCHEMA = Model::build_schema('wait_msg', array(
                    self::$field_id,
                    self::$field_add_time,
                    self::$field_msgtype,
                    self::$field_request_id,
                    self::$field_request_name,
                    self::$field_task_id,
                    self::$field_response_id,
                    self::$field_response_name,
                    self::$field_status,
                    self::$field_dept_id,
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

    public function get_msgtype() {
        $this->get_field_value(self::$field_msgtype);
    }

    public function set_msgtype($msgtype) {
        $this->set_field_value(self::$field_msgtype, $msgtype);
    }

    public function get_request_id() {
        $this->get_field_value(self::$field_request_id);
    }

    public function set_request_id($request_id) {
        $this->set_field_value(self::$field_request_id, $request_id);
    }

    public function get_request_name() {
        $this->get_field_value(self::$field_request_name);
    }

    public function set_request_name($request_name) {
        $this->set_field_value(self::$field_request_name, $request_name);
    }

    public function get_task_id() {
        $this->get_field_value(self::$field_task_id);
    }

    public function set_task_id($task_id) {
        $this->set_field_value(self::$field_task_id, $task_id);
    }

    public function get_response_id() {
        $this->get_field_value(self::$field_response_id);
    }

    public function set_response_id($response_id) {
        $this->set_field_value(self::$field_response_id, $response_id);
    }

    public function get_response_name() {
        $this->get_field_value(self::$field_response_name);
    }

    public function set_response_name($response_name) {
        $this->set_field_value(self::$field_response_name, $response_name);
    }

    public function get_dept_id() {
        $this->get_field_value(self::$field_dept_id);
    }

    public function set_dept_id($dept_id) {
        $this->set_field_value(self::$field_dept_id, $dept_id);
    }

    public function get_status() {
        $this->get_field_value(self::$field_status);
    }

    public function set_status($status) {
        $this->set_field_value(self::$field_status, $status);
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

wait_msg::init_schema();
