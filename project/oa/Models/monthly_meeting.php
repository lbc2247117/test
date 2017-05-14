<?php

/**
 * 迟到
 *
 * @author 自动生成的实体类
 * @copyright (c) 2015, 非时序集团
 * @version 1.0
 */

namespace Models;

$GLOBALS['/Models/monthly_meeting.php'] = 1;

use Models\Base\Model;

class monthly_meeting extends Model {

    public static $field_id;
    public static $field_monthly_title;
    public static $field_monthly_content;
    public static $field_file_name;
    public static $field_file_url;
    public static $field_addtime;
    public static $field_upload_user_id;
    public static $field_update_time;
    public static $MODEL_SCHEMA;

    static function init_schema() {
        self::$field_id = Model::define_primary_key('id', 'int', 0, true);
        self::$field_monthly_title = Model::define_field('monthly_title', 'string', NULL);
        self::$field_monthly_content = Model::define_field('monthly_content', 'string', NULL);
        self::$field_file_name = Model::define_field('file_name', 'string', NULL);
        self::$field_file_url = Model::define_field('file_url', 'string', NULL);
        self::$field_addtime = Model::define_field('addtime', 'date', NULL);
        self::$field_upload_user_id = Model::define_field('upload_user_id', 'int', 0);
        self::$field_update_time = Model::define_field('update_time', 'date', NULL);
        self::$MODEL_SCHEMA = Model::build_schema('monthly_meeting', array(
                    self::$field_id,
                    self::$field_monthly_title,
                    self::$field_monthly_content,
                    self::$field_file_name,
                    self::$field_file_url,
                    self::$field_addtime,
                    self::$field_upload_user_id,
                    self::$field_update_time
        ));
    }

    public function get_id() {
        return $this->get_field_value(self::$field_id);
    }

    public function set_id($id) {
        $this->set_field_value(self::$field_id, $id);
    }

    public function get_monthly_title() {
        return $this->get_field_value(self::$field_monthly_title);
    }

    public function set_monthly_title($monthly_title) {
        $this->set_field_value(self::$field_monthly_title, $monthly_title);
    }

    public function get_monthly_content() {
        return $this->get_field_value(self::$monthly_content);
    }

    public function set_monthly_content($monthly_content) {
        $this->set_field_value(self::$field_monthly_content, $monthly_content);
    }

    public function get_file_name() {
        return $this->get_field_value(self::$field_file_name);
    }

    public function set_file_name($file_name) {
        $this->set_field_value(self::$field_file_name, $file_name);
    }

    public function get_file_url() {
        return $this->get_field_value(self::$field_file_url);
    }

    public function set_file_url($file_url) {
        $this->set_field_value(self::$field_file_url, $file_url);
    }

    public function get_addtime() {
        return $this->get_field_value(self::$field_addtime);
    }

    public function set_addtime($addtime) {
        $this->set_field_value(self::$field_addtime, $addtime);
    }
    
    public function get_upload_user_id() {
        return $this->get_field_value(self::$field_upload_user_id);
    }

    public function set_upload_user_id($upload_user_id) {
        $this->set_field_value(self::$field_upload_user_id, $upload_user_id);
    }
    
    public function get_update_time() {
        return $this->get_field_value(self::$field_update_time);
    }

    public function set_update_time($update_time) {
        $this->set_field_value(self::$field_update_time, $update_time);
    }

    public function to_array(array $options = array(), callable $func = NULL) {
        $arr = parent::to_array($options, $func);
        //unset($arr[self::$field_id['name']]);
        //unset($arr[self::$field_userid['name']]);
        //unset($arr[self::$field_username['name']]);
        //unset($arr[self::$field_dept1_id['name']]);
        //unset($arr[self::$field_dept2_id['name']]);
        //unset($arr[self::$field_mins['name']]);
        //unset($arr[self::$field_date['name']]);
        return $arr;
    }

}

monthly_meeting::init_schema();
