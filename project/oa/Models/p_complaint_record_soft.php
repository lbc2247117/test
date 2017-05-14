<?php

/**
* 
*
* @author 自动生成的实体类
* @copyright (c) 2015, 星密码集团
* @version 1.0
*/

namespace Models;

$GLOBALS['/Models/p_complaint_record_soft.php'] = 1;

use Models\Base\Model;

class p_complaint_record_soft extends Model{

	public static $field_id;
	public static $field_addtime;
	public static $field_mobile;
	public static $field_shop;
	public static $field_qq;
	public static $field_phone;
	public static $field_complaint_custom;
	public static $field_complaint_content;
	public static $field_hand_personnel;
	public static $field_hand_result;
	public static $field_add_userid;
	public static $MODEL_SCHEMA;

	static function init_schema() {
		self::$field_id = Model::define_primary_key('id', 'int', 0, true);
		self::$field_addtime = Model::define_field('addtime', 'datetime', NULL);
		self::$field_mobile = Model::define_field('mobile', 'string', NULL);
		self::$field_shop = Model::define_field('shop', 'string', NULL);
		self::$field_qq = Model::define_field('qq', 'string', NULL);
		self::$field_phone = Model::define_field('phone', 'string', NULL);
		self::$field_complaint_custom = Model::define_field('complaint_custom', 'string', NULL);
		self::$field_complaint_content = Model::define_field('complaint_content', 'string', NULL);
		self::$field_hand_personnel = Model::define_field('hand_personnel', 'string', NULL);
		self::$field_hand_result = Model::define_field('hand_result', 'string', NULL);
		self::$field_add_userid = Model::define_field('add_userid', 'int', 0);
		self::$MODEL_SCHEMA = Model::build_schema('p_complaint_record_soft', array(
			self::$field_id,
			self::$field_addtime,
			self::$field_mobile,
			self::$field_shop,
			self::$field_qq,
			self::$field_phone,
			self::$field_complaint_custom,
			self::$field_complaint_content,
			self::$field_hand_personnel,
			self::$field_hand_result,
			self::$field_add_userid
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

	public function get_mobile() {
		return $this->get_field_value(self::$field_mobile);
	}

	public function set_mobile($mobile) {
		$this->set_field_value(self::$field_ww, $mobile);
	}

	public function get_shop() {
		return $this->get_field_value(self::$field_shop);
	}

	public function set_shop($shop) {
		$this->set_field_value(self::$field_shop, $shop);
	}

	public function get_qq() {
		return $this->get_field_value(self::$field_qq);
	}

	public function set_qq($qq) {
		$this->set_field_value(self::$field_qq, $qq);
	}

	public function get_phone() {
		return $this->get_field_value(self::$field_phone);
	}

	public function set_phone($phone) {
		$this->set_field_value(self::$field_phone, $phone);
	}

	public function get_complaint_custom() {
		return $this->get_field_value(self::$field_complaint_custom);
	}

	public function set_complaint_custom($complaint_custom) {
		$this->set_field_value(self::$field_complaint_custom, $complaint_custom);
	}

	public function get_complaint_content() {
		return $this->get_field_value(self::$field_complaint_content);
	}

	public function set_complaint_content($complaint_content) {
		$this->set_field_value(self::$field_complaint_content, $complaint_content);
	}

	public function get_hand_personnel() {
		return $this->get_field_value(self::$field_hand_personnel);
	}

	public function set_hand_personnel($hand_personnel) {
		$this->set_field_value(self::$field_hand_personnel, $hand_personnel);
	}

	public function get_hand_result() {
		return $this->get_field_value(self::$field_hand_result);
	}

	public function set_hand_result($hand_result) {
		$this->set_field_value(self::$field_hand_result, $hand_result);
	}

	public function get_add_userid() {
		return $this->get_field_value(self::$field_add_userid);
	}

	public function set_add_userid($add_userid) {
		$this->set_field_value(self::$field_add_userid, $add_userid);
	}

	public function to_array(array $options = array(), callable $func = NULL) {
		$arr = parent::to_array($options, $func);
		//unset($arr[self::$field_id['name']]);
		//unset($arr[self::$field_addtime['name']]);
		//unset($arr[self::$field_ww['name']]);
		//unset($arr[self::$field_shop['name']]);
		//unset($arr[self::$field_qq['name']]);
		//unset($arr[self::$field_phone['name']]);
		//unset($arr[self::$field_complaint_custom['name']]);
		//unset($arr[self::$field_complaint_content['name']]);
		//unset($arr[self::$field_hand_personnel['name']]);
		//unset($arr[self::$field_hand_result['name']]);
		//unset($arr[self::$field_add_userid['name']]);
		return $arr;
	}

}

p_complaint_record_soft::init_schema();
