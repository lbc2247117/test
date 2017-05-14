<?php

/**
* 周报
*
* @author 自动生成的实体类
* @copyright (c) 2015, 星密码集团
* @version 1.0
*/

namespace Models;

$GLOBALS['/Models/W_Weekly.php'] = 1;

use Models\Base\Model;

class W_Weekly extends Model{

	public static $field_id;
	public static $field_addtime;
	public static $field_userid;
	public static $field_dept1_id;
	public static $field_dept2_id;
	public static $field_matter;
	public static $field_username;
	public static $MODEL_SCHEMA;

	static function init_schema() {
		self::$field_id = Model::define_primary_key('id', 'int', 0, true);
		self::$field_addtime = Model::define_field('addtime', 'datetime', NULL);
		self::$field_userid = Model::define_field('userid', 'int', 0);
		self::$field_dept1_id = Model::define_field('dept1_id', 'int', 0);
		self::$field_dept2_id = Model::define_field('dept2_id', 'int', 0);
		self::$field_matter = Model::define_field('matter', 'string', NULL);
		self::$field_username = Model::define_field('username', 'string', NULL);
		self::$MODEL_SCHEMA = Model::build_schema('W_Weekly', array(
			self::$field_id,
			self::$field_addtime,
			self::$field_userid,
			self::$field_dept1_id,
			self::$field_dept2_id,
			self::$field_matter,
			self::$field_username
		));
	}


	public function get_id() {
		return $this->get_field_value(self::$field_id);
	}

	public function set_id($id) {
		$this->set_field_value(self::$field_id, $id);
	}

	public function get_addtime() {
		return $this->get_field_value(self::$field_addtime);
	}

	public function set_addtime($addtime) {
		$this->set_field_value(self::$field_addtime, $addtime);
	}

	public function get_userid() {
		return $this->get_field_value(self::$field_userid);
	}

	public function set_userid($userid) {
		$this->set_field_value(self::$field_userid, $userid);
	}

	public function get_dept1_id() {
		return $this->get_field_value(self::$field_dept1_id);
	}

	public function set_dept1_id($dept1_id) {
		$this->set_field_value(self::$field_dept1_id, $dept1_id);
	}

	public function get_dept2_id() {
		return $this->get_field_value(self::$field_dept2_id);
	}

	public function set_dept2_id($dept2_id) {
		$this->set_field_value(self::$field_dept2_id, $dept2_id);
	}

	public function get_matter() {
		return $this->get_field_value(self::$field_matter);
	}

	public function set_matter($matter) {
		$this->set_field_value(self::$field_matter, $matter);
	}

	public function get_username() {
		return $this->get_field_value(self::$field_username);
	}

	public function set_username($username) {
		$this->set_field_value(self::$field_username, $username);
	}

	public function to_array(array $options = array(), callable $func = NULL) {
		$arr = parent::to_array($options, $func);
		//unset($arr[self::$field_id['name']]);
		//unset($arr[self::$field_addtime['name']]);
		//unset($arr[self::$field_userid['name']]);
		//unset($arr[self::$field_dept1_id['name']]);
		//unset($arr[self::$field_dept2_id['name']]);
		//unset($arr[self::$field_matter['name']]);
		//unset($arr[self::$field_username['name']]);
		return $arr;
	}

}

W_Weekly::init_schema();
