<?php

/**
* 
*
* @author 自动生成的实体类
* @copyright (c) 2015, 星密码集团
* @version 1.0
*/

namespace Models;

$GLOBALS['/Models/p_complainvisit.php'] = 1;

use Models\Base\Model;

class p_complainvisit extends Model{

	public static $field_id;
        public static $field_visitTime;
        public static $field_visitLevel;
        public static $field_customerQQ;
	public static $field_customerPhone;
        public static $field_departName;
	public static $field_reflectPro;
        public static $field_isDeal;
        public static $field_dealResult;
        public static $field_refundMoney;
        public static $field_isPleased;
        public static $field_remark;
        public static $field_addUser;
	public static $field_visitUser;
	public static $MODEL_SCHEMA;

	static function init_schema() {
		self::$field_id = Model::define_primary_key('id', 'int', 0, true);
                self::$field_visitTime = Model::define_field('visitTime', 'datetime', 0);
                self::$field_visitLevel = Model::define_field('visitLevel', 'string', NULL);
		self::$field_customerQQ = Model::define_field('customerQQ', 'string', NULL);
		self::$field_customerPhone = Model::define_field('customerPhone', 'string', NULL);
                self::$field_departName = Model::define_field('departName', 'string', NULL);
		self::$field_reflectPro = Model::define_field('reflectPro', 'string', NULL);
                self::$field_isDeal = Model::define_field('isDeal', 'string', NULL);
		self::$field_dealResult = Model::define_field('dealResult', 'string', NULL);
		self::$field_refundMoney = Model::define_field('refundMoney', 'float',0.00);
                self::$field_addUser = Model::define_field('addUser', 'int', 0);
		self::$field_visitUser = Model::define_field('visitUser', 'int', 0);
                self::$field_isPleased = Model::define_field('isPleased', 'string', NULL);
		self::$field_remark = Model::define_field('remark', 'string', NULL);
		self::$MODEL_SCHEMA = Model::build_schema('p_complainvisit', array(
			self::$field_id,
                        self::$field_visitTime,
                        self::$field_visitLevel,
			self::$field_customerQQ,
			self::$field_customerPhone,
                        self::$field_departName,
			self::$field_reflectPro,
                        self::$field_isDeal,
			self::$field_dealResult,
                        self::$field_refundMoney,
			self::$field_addUser,
			self::$field_visitUser,
                        self::$field_isPleased,
			self::$field_remark
		));
	}


	public function get_id() {
		return $this->get_field_value(self::$field_id);
	}

	public function set_id($id) {
		$this->set_field_value(self::$field_id, $id);
	}
        
        public function get_visitTime() {
		return $this->get_field_value(self::$field_visitTime);
	}

	public function set_visitTime($visitTime) {
		$this->set_field_value(self::$field_visitTime, $visitTime);
	}

        public function get_visitLevel() {
		return $this->get_field_value(self::$field_visitLevel);
	}

	public function set_visitLevel($visitLevel) {
		$this->set_field_value(self::$field_visitLevel, $visitLevel);
	}
        
	public function get_customerQQ() {
		return $this->get_field_value(self::$field_customerQQ);
	}

	public function set_customerQQ($customerQQ) {
		$this->set_field_value(self::$field_customerQQ, $customerQQ);
	}

	public function get_customerPhone() {
		return $this->get_field_value(self::$field_customerPhone);
	}

	public function set_customerPhone($customerPhone) {
		$this->set_field_value(self::$field_customerPhone, $customerPhone);
	}
        
        public function get_departName() {
		return $this->get_field_value(self::$field_departName);
	}

	public function set_departName($departName) {
		$this->set_field_value(self::$field_departName, $departName);
	}

        public function get_reflectPro() {
		return $this->get_field_value(self::$field_reflectPro);
	}

	public function set_reflectPro($reflectPro) {
		$this->set_field_value(self::$field_reflectPro, $reflectPro);
	}
        
        public function get_isDeal() {
		return $this->get_field_value(self::$field_isDeal);
	}

	public function set_isDeal($isDeal) {
		$this->set_field_value(self::$field_isDeal, $isDeal);
	}
        
	public function get_dealResult() {
		return $this->get_field_value(self::$field_dealResult);
	}

	public function set_dealResult($dealResult) {
		$this->set_field_value(self::$field_dealResult, $dealResult);
	}
        
        public function get_refundMoney() {
		return $this->get_field_value(self::$field_refundMoney);
	}

	public function set_refundMoney($refundMoney) {
		$this->set_field_value(self::$field_refundMoney, $refundMoney);
	}
        
	public function get_addUser() {
		return $this->get_field_value(self::$field_addUser);
	}

	public function set_addUser($addUser) {
		$this->set_field_value(self::$field_addUser, $addUser);
	}

	public function get_visitUser() {
		return $this->get_field_value(self::$field_visitUser);
	}

	public function set_visitUser($visitUser) {
		$this->set_field_value(self::$field_visitUser, $visitUser);
	}

        public function get_isPleased() {
		return $this->get_field_value(self::$field_isPleased);
	}

	public function set_isPleased($isPleased) {
		$this->set_field_value(self::$field_isPleased, $isPleased);
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

p_complainvisit::init_schema();
