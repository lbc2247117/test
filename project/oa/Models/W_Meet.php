<?php

/**
* 会议记录
*
* @author 自动生成的实体类
* @copyright (c) 2015, 星密码集团
* @version 1.0
*/

namespace Models;

$GLOBALS['/Models/W_Meet.php'] = 1;

use Models\Base\Model;

class W_Meet extends Model{

	public static $field_id;
	public static $field_meet_title;
	public static $field_meet_content;
	public static $field_date;
	public static $field_username;
	public static $field_userid;
	public static $MODEL_SCHEMA;

	static function init_schema() {
		self::$field_id = Model::define_primary_key('id', 'int', 0, true);
		self::$field_meet_title = Model::define_field('meet_title', 'string', NULL);
		self::$field_meet_content = Model::define_field('meet_content', 'string', NULL);
		self::$field_date = Model::define_field('date', 'date', NULL);
		self::$field_username = Model::define_field('username', 'string', NULL);
		self::$field_userid = Model::define_field('userid', 'int', 0);
		self::$MODEL_SCHEMA = Model::build_schema('W_Meet', array(
			self::$field_id,
			self::$field_meet_title,
			self::$field_meet_content,
			self::$field_date,
			self::$field_username,
			self::$field_userid
		));
	}


	public function get_id() {
		return $this->get_field_value(self::$field_id);
	}

	public function set_id($id) {
		$this->set_field_value(self::$field_id, $id);
	}

	public function get_meet_title() {
		return $this->get_field_value(self::$field_meet_title);
	}

	public function set_meet_title($meet_title) {
		$this->set_field_value(self::$field_meet_title, $meet_title);
	}

	public function get_meet_content() {
		return $this->get_field_value(self::$field_meet_content);
	}

	public function set_meet_content($meet_content) {
		$this->set_field_value(self::$field_meet_content, $meet_content);
	}

	public function get_date() {
		return $this->get_field_value(self::$field_date);
	}

	public function set_date($date) {
		$this->set_field_value(self::$field_date, $date);
	}

	public function get_username() {
		return $this->get_field_value(self::$field_username);
	}

	public function set_username($username) {
		$this->set_field_value(self::$field_username, $username);
	}

	public function get_userid() {
		return $this->get_field_value(self::$field_userid);
	}

	public function set_userid($userid) {
		$this->set_field_value(self::$field_userid, $userid);
	}

	public function to_array(array $options = array(), callable $func = NULL) {
		$arr = parent::to_array($options, $func);
		//unset($arr[self::$field_id['name']]);
		//unset($arr[self::$field_meet_title['name']]);
		//unset($arr[self::$field_meet_content['name']]);
		//unset($arr[self::$field_date['name']]);
		//unset($arr[self::$field_username['name']]);
		//unset($arr[self::$field_userid['name']]);
		return $arr;
	}

}

W_Meet::init_schema();
