<?php

/**
 * 补欠款
 *
 * @author 自动生成的实体类
 * @copyright (c) 2015, 非时序集团
 * @version 1.0
 */

namespace Models;

$GLOBALS['/Models/staff_mein.php'] = 1;

use Models\Base\Model;

class staff_mein extends Model {

    public static $field_id;
    public static $field_dic_title;
    public static $field_dic_addtime;
    public static $field_type;
    public static $field_attachment_url;
    public static $field_title;
    public static $field_addtime;
    public static $field_upload_user;
    public static $field_looknum;
    public static $MODEL_SCHEMA;

    static function init_schema() {
        self::$field_id = Model::define_primary_key('id', 'int', 0, true);
        self::$field_dic_title = Model::define_field('dic_title', 'string', NULL);
        self::$field_dic_addtime = Model::define_field('dic_addtime', 'datetime', NULL);
        self::$field_type = Model::define_field('type', 'int', NULL);
        self::$field_attachment_url = Model::define_field('attachment_url', 'string', NULL);
        self::$field_title = Model::define_field('title', 'string', NULL);
        self::$field_addtime = Model::define_field('addtime', 'datetime', NULL);
        self::$field_upload_user = Model::define_field('upload_user', 'string', NULL);
        self::$field_looknum = Model::define_field('looknum', 'int', NULL);
        self::$MODEL_SCHEMA = Model::build_schema('staff_mein', array(
                    self::$field_id,
                    self::$field_dic_title,
                    self::$field_dic_addtime,
                    self::$field_type,
                    self::$field_attachment_url,
                    self::$field_title,
                    self::$field_addtime,
                    self::$field_upload_user,
                    self::$field_looknum
        ));
    }

    public function get_id() {
        return $this->get_field_value(self::$field_id);
    }

    public function set_id($id) {
        $this->set_field_value(self::$field_id, $id);
    }
    
    public function get_dic_title() {
        return $this->get_field_value(self::$field_dic_title);
    }

    public function set_dic_title($dic_title) {
        $this->set_field_value(self::$field_dic_title, $dic_title);
    }

    public function get_dic_addtime() {
        return $this->get_field_value(self::$field_dic_addtime);
    }

    public function set_dic_addtime($dic_addtime) {
        $this->set_field_value(self::$field_dic_addtime, $dic_addtime);
    }

    public function get_type() {
        return $this->get_field_value(self::$field_type);
    }

    public function set_type($type) {
        $this->set_field_value(self::$field_type, $type);
    }
    
    public function get_attachment_url() {
        return $this->get_field_value(self::$field_attachment_url);
    }

    public function set_attachment_url($attachment_url) {
        $this->set_field_value(self::$field_attachment_url, $attachment_url);
    }

    public function get_title() {
        return $this->get_field_value(self::$field_title);
    }

    public function set_title($title) {
        $this->set_field_value(self::$field_title, $title);
    }

    public function get_addtime() {
        return $this->get_field_value(self::$field_addtime);
    }

    public function set_addtime($addtime) {
        $this->set_field_value(self::$field_addtime, $addtime);
    }

    public function get_upload_user() {
        return $this->get_field_value(self::$field_upload_user);
    }

    public function set_upload_user($upload_user) {
        $this->set_field_value(self::$field_upload_user, $upload_user);
    }

    public function get_looknum() {
        return $this->get_field_value(self::$field_looknum);
    }

    public function set_looknum($looknum) {
        $this->set_field_value(self::$field_looknum, $looknum);
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

staff_mein::init_schema();
