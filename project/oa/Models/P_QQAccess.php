<?php

/**
* QQ接入
*
* @author 自动生成的实体类
* @copyright (c) 2015, 非时序集团
* @version 1.0
*/

namespace Models;

$GLOBALS['/Models/P_QQAccess.php'] = 1;

use Models\Base\Model;

class P_QQAccess extends Model{

	public static $field_id;
	public static $field_addtime;
	public static $field_add_userid;
	public static $field_add_username;
	public static $field_presales;
	public static $field_presales_id;
	public static $field_qq_num;
	public static $field_customer_num;
	public static $field_customer_address;
	public static $field_quik_write;
	public static $field_channel;
	public static $field_access_time;
	public static $MODEL_SCHEMA;

	static function init_schema() {
		self::$field_id = Model::define_primary_key('id', 'int', 0, true);
		self::$field_addtime = Model::define_field('addtime', 'datetime', NULL);
		self::$field_add_userid = Model::define_field('add_userid', 'int', 0);
		self::$field_add_username = Model::define_field('add_username', 'string', NULL);
		self::$field_presales = Model::define_field('presales', 'string', NULL);
		self::$field_presales_id = Model::define_field('presales_id', 'int', 0);
		self::$field_qq_num = Model::define_field('qq_num', 'string', NULL);
		self::$field_customer_num = Model::define_field('customer_num', 'string', NULL);
		self::$field_customer_address = Model::define_field('customer_address', 'string', NULL);
		self::$field_quik_write = Model::define_field('quik_write', 'string', NULL);
		self::$field_channel = Model::define_field('channel', 'string', NULL);
		self::$field_access_time = Model::define_field('access_time', 'datetime', NULL);
		self::$MODEL_SCHEMA = Model::build_schema('P_QQAccess', array(
			self::$field_id,
			self::$field_addtime,
			self::$field_add_userid,
			self::$field_add_username,
			self::$field_presales,
			self::$field_presales_id,
			self::$field_qq_num,
			self::$field_customer_num,
			self::$field_customer_address,
			self::$field_quik_write,
			self::$field_channel,
			self::$field_access_time
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

	public function get_add_userid() {
		return $this->get_field_value(self::$field_add_userid);
	}

	public function set_add_userid($add_userid) {
		$this->set_field_value(self::$field_add_userid, $add_userid);
	}

	public function get_add_username() {
		return $this->get_field_value(self::$field_add_username);
	}

	public function set_add_username($add_username) {
		$this->set_field_value(self::$field_add_username, $add_username);
	}

	public function get_presales() {
		return $this->get_field_value(self::$field_presales);
	}

	public function set_presales($presales) {
		$this->set_field_value(self::$field_presales, $presales);
	}

	public function get_presales_id() {
		return $this->get_field_value(self::$field_presales_id);
	}

	public function set_presales_id($presales_id) {
		$this->set_field_value(self::$field_presales_id, $presales_id);
	}

	public function get_qq_num() {
		return $this->get_field_value(self::$field_qq_num);
	}

	public function set_qq_num($qq_num) {
		$this->set_field_value(self::$field_qq_num, $qq_num);
	}

	public function get_customer_num() {
		return $this->get_field_value(self::$field_customer_num);
	}

	public function set_customer_num($customer_num) {
		$this->set_field_value(self::$field_customer_num, $customer_num);
	}

	public function get_customer_address() {
		return $this->get_field_value(self::$field_customer_address);
	}

	public function set_customer_address($customer_address) {
		$this->set_field_value(self::$field_customer_address, $customer_address);
	}

	public function get_quik_write() {
		return $this->get_field_value(self::$field_quik_write);
	}

	public function set_quik_write($quik_write) {
		$this->set_field_value(self::$field_quik_write, $quik_write);
	}

	public function get_channel() {
		return $this->get_field_value(self::$field_channel);
	}

	public function set_channel($channel) {
		$this->set_field_value(self::$field_channel, $channel);
	}

	public function get_access_time() {
		return $this->get_field_value(self::$field_access_time);
	}

	public function set_access_time($access_time) {
		$this->set_field_value(self::$field_access_time, $access_time);
	}

	public function to_array(array $options = array(), callable $func = NULL) {
		$arr = parent::to_array($options, $func);
		//unset($arr[self::$field_id['name']]);
		//unset($arr[self::$field_addtime['name']]);
		//unset($arr[self::$field_add_userid['name']]);
		//unset($arr[self::$field_add_username['name']]);
		//unset($arr[self::$field_presales['name']]);
		//unset($arr[self::$field_presales_id['name']]);
		//unset($arr[self::$field_qq_num['name']]);
		//unset($arr[self::$field_customer_num['name']]);
		//unset($arr[self::$field_customer_address['name']]);
		//unset($arr[self::$field_quik_write['name']]);
		//unset($arr[self::$field_channel['name']]);
		//unset($arr[self::$field_access_time['name']]);
		return $arr;
	}

}

P_QQAccess::init_schema();
