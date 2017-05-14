<?php

/**
* 售前统计
*
* @author 自动生成的实体类
* @copyright (c) 2015, 非时序集团
* @version 1.0
*/

namespace Models;

$GLOBALS['/Models/p_salestatistics.php'] = 1;

use Models\Base\Model;

class p_salestatistics extends Model{

	public static $field_id;
	public static $field_channel;
	public static $field_userid;
	public static $field_username;
	public static $field_into_count;
	public static $field_accept_count;
	public static $field_deal_count;
	public static $field_timely_count;
	public static $field_amount;
	public static $field_addtime;
	public static $MODEL_SCHEMA;

	static function init_schema() {
		self::$field_id = Model::define_primary_key('id', 'int', 0, true);
		self::$field_channel = Model::define_field('channel', 'string', NULL);
		self::$field_userid = Model::define_field('userid', 'int', 0);
		self::$field_username = Model::define_field('username', 'string', NULL);
		self::$field_into_count = Model::define_field('into_count', 'int', 0);
		self::$field_accept_count = Model::define_field('accept_count', 'int', 0);
		self::$field_deal_count = Model::define_field('deal_count', 'int', 0);
		self::$field_timely_count = Model::define_field('timely_count', 'int', 0);
		self::$field_amount = Model::define_field('amount', 'float', 0.00);
		self::$field_addtime = Model::define_field('addtime', 'datetime', NULL);
		self::$MODEL_SCHEMA = Model::build_schema('p_salestatistics', array(
			self::$field_id,
			self::$field_channel,
			self::$field_userid,
			self::$field_username,
			self::$field_into_count,
			self::$field_accept_count,
			self::$field_deal_count,
			self::$field_timely_count,
			self::$field_amount,
			self::$field_addtime
		));
	}


	public function get_id() {
		return $this->get_field_value(self::$field_id);
	}

	public function set_id($id) {
		$this->set_field_value(self::$field_id, $id);
	}

	public function get_channel() {
		return $this->get_field_value(self::$field_channel);
	}

	public function set_channel($channel) {
		$this->set_field_value(self::$field_channel, $channel);
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

	public function get_into_count() {
		return $this->get_field_value(self::$field_into_count);
	}

	public function set_into_count($into_count) {
		$this->set_field_value(self::$field_into_count, $into_count);
	}

	public function get_accept_count() {
		return $this->get_field_value(self::$field_accept_count);
	}

	public function set_accept_count($accept_count) {
		$this->set_field_value(self::$field_accept_count, $accept_count);
	}

	public function get_deal_count() {
		return $this->get_field_value(self::$field_deal_count);
	}

	public function set_deal_count($deal_count) {
		$this->set_field_value(self::$field_deal_count, $deal_count);
	}

	public function get_timely_count() {
		return $this->get_field_value(self::$field_timely_count);
	}

	public function set_timely_count($timely_count) {
		$this->set_field_value(self::$field_timely_count, $timely_count);
	}

	public function get_amount() {
		return $this->get_field_value(self::$field_amount);
	}

	public function set_amount($amount) {
		$this->set_field_value(self::$field_amount, $amount);
	}

	public function get_addtime() {
		return $this->get_field_value(self::$field_addtime);
	}

	public function set_addtime($addtime) {
		$this->set_field_value(self::$field_addtime, $addtime);
	}

	public function to_array(array $options = array(), callable $func = NULL) {
		$arr = parent::to_array($options, $func);
		//unset($arr[self::$field_id['name']]);
		//unset($arr[self::$field_channel['name']]);
		//unset($arr[self::$field_userid['name']]);
		//unset($arr[self::$field_username['name']]);
		//unset($arr[self::$field_into_count['name']]);
		//unset($arr[self::$field_accept_count['name']]);
		//unset($arr[self::$field_deal_count['name']]);
		//unset($arr[self::$field_timely_count['name']]);
		//unset($arr[self::$field_amount['name']]);
		//unset($arr[self::$field_addtime['name']]);
		return $arr;
	}

}

p_salestatistics::init_schema();
