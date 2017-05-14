<?php

/**
* 
*
* @author 自动生成的实体类
* @copyright (c) 2015, 星密码集团
* @version 1.0
*/

namespace Models;

$GLOBALS['/Models/p_complain.php'] = 1;

use Models\Base\Model;

class p_complain extends Model{

	public static $field_id;
        public static $field_receiveTime;
        public static $field_customerPhone;
        public static $field_customerQQ;
	public static $field_teacherName;
        public static $field_departName;
        public static $field_flowType;
        public static $field_dealResult;
	public static $field_isDeal;
	public static $field_refundMoney;
	public static $field_proType;
        public static $field_anotherDetail;
        public static $field_addUser;
	public static $field_dealUser;
        public static $field_isPleased;
        public static $field_remark;
	public static $MODEL_SCHEMA;

	static function init_schema() {
		self::$field_id = Model::define_primary_key('id', 'int', 0, true);
                self::$field_receiveTime = Model::define_field('receiveTime', 'datetime', 0);
                self::$field_customerPhone = Model::define_field('customerPhone', 'string', NULL);
		self::$field_customerQQ = Model::define_field('customerQQ', 'string', NULL);
		self::$field_teacherName = Model::define_field('teacherName', 'string', NULL);
                self::$field_departName = Model::define_field('departName', 'string', NULL);
                self::$field_flowType = Model::define_field('flowType', 'string', NULL);
		self::$field_dealResult = Model::define_field('dealResult', 'string', NULL);
		self::$field_isDeal = Model::define_field('isDeal', 'string', NULL);
		self::$field_refundMoney = Model::define_field('refundMoney', 'float', 0.00);
		self::$field_proType = Model::define_field('proType', 'string', 0);
                self::$field_anotherDetail = Model::define_field('anotherDetail', 'string', NULL);
                self::$field_addUser = Model::define_field('addUser', 'int', NULL);
		self::$field_dealUser = Model::define_field('dealUser', 'int', NULL);
                self::$field_isPleased = Model::define_field('isPleased', 'string', NULL);
		self::$field_remark = Model::define_field('remark', 'string', NULL);
		self::$MODEL_SCHEMA = Model::build_schema('p_complain', array(
			self::$field_id,
                        self::$field_receiveTime,
                        self::$field_customerPhone,
			self::$field_customerQQ,
			self::$field_teacherName,
                        self::$field_departName,
                        self::$field_flowType,
			self::$field_dealResult,
			self::$field_isDeal,
			self::$field_refundMoney,
			self::$field_proType,
                        self::$field_anotherDetail,
                        self::$field_addUser,
			self::$field_dealUser,
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
        
        public function get_receiveTime() {
		return $this->get_field_value(self::$field_receiveTime);
	}

	public function set_receiveTime($receiveTime) {
		$this->set_field_value(self::$field_receiveTime, $receiveTime);
	}

        public function get_customerPhone() {
		return $this->get_field_value(self::$field_customerPhone);
	}

	public function set_customerPhone($customerPhone) {
		$this->set_field_value(self::$field_customerPhone, $customerPhone);
	}
        
	public function get_customerQQ() {
		return $this->get_field_value(self::$field_customerQQ);
	}

	public function set_customerQQ($customerQQ) {
		$this->set_field_value(self::$field_customerQQ, $customerQQ);
	}

	public function get_teacherName() {
		return $this->get_field_value(self::$field_teacherName);
	}

	public function set_teacherName($teacherName) {
		$this->set_field_value(self::$field_teacherName, $teacherName);
	}
        
        public function get_departName() {
		return $this->get_field_value(self::$field_departName);
	}

	public function set_departName($departName) {
		$this->set_field_value(self::$field_departName, $departName);
	}
        
        public function get_flowType() {
		return $this->get_field_value(self::$field_flowType);
	}

	public function set_flowType($flowType) {
		$this->set_field_value(self::$field_flowType, $flowType);
	}

	public function get_dealResult() {
		return $this->get_field_value(self::$field_dealResult);
	}

	public function set_dealResult($dealResult) {
		$this->set_field_value(self::$field_dealResult, $dealResult);
	}

	public function get_isDeal() {
		return $this->get_field_value(self::$field_isDeal);
	}

	public function set_isDeal($isDeal) {
		$this->set_field_value(self::$field_isDeal, $isDeal);
	}

	public function get_refundMoney() {
		return $this->get_field_value(self::$field_refundMoney);
	}

	public function set_refundMoney($refundMoney) {
		$this->set_field_value(self::$field_refundMoney, $refundMoney);
	}

	public function get_proType() {
		return $this->get_field_value(self::$field_proType);
	}

	public function set_proType($proType) {
		$this->set_field_value(self::$field_proType, $proType);
	}
        
        public function get_anotherDetail() {
		return $this->get_field_value(self::$field_anotherDetail);
	}

	public function set_anotherDetail($anotherDetail) {
		$this->set_field_value(self::$field_anotherDetail, $anotherDetail);
	}

        public function get_addUser() {
		return $this->get_field_value(self::$field_addUser);
	}

	public function set_addUser($addUser) {
		$this->set_field_value(self::$field_addUser, $addUser);
	}
        
	public function get_dealUser() {
		return $this->get_field_value(self::$field_dealUser);
	}

	public function set_dealUser($dealUser) {
		$this->set_field_value(self::$field_dealUser, $dealUser);
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

p_complain::init_schema();
