<?php

/**
 * 考勤表
 *
 * @author 自动生成的实体类
 * @copyright (c) 2015, 非时序集团
 * @version 1.0
 */

namespace Models;

$GLOBALS['/Models/personnel_punch.php'] = 1;

use Models\Base\Model;

class personnel_punch extends Model {

    public static $field_id;
    public static $field_userid;
    public static $field_username;
    public static $field_departid;
    public static $field_depart;
    public static $field_type;
    public static $field_begindate;
    public static $field_enddate;
    public static $field_addtime;
    public static $field_role_type;
    public static $field_remark;
    public static $field_leader_remark;
    public static $field_personnel_remark;
    public static $field_second_general_remark;
    public static $field_first_general_remark;
    public static $field_status;
    public static $field_phone;
    public static $field_ismoreThree;
    public static $field_casetype;
    public static $field_signtype;
    public static $field_shichang;
    public static $field_leader_time;
    public static $field_personer_time;
    public static $field_mastersecond_time;
    public static $field_masterfirst_time;
    public static $field_isSaleDept;
    public static $field_leaderName;
    public static $field_isRead;
    public static $field_readUser;
    public static $MODEL_SCHEMA;

    static function init_schema() {
        self::$field_id = Model::define_primary_key('id', 'int', 0, true);
        self::$field_userid = Model::define_field('userid', 'int', 0);
        self::$field_username = Model::define_field('username', 'string', NULL);
        self::$field_departid = Model::define_field('departid', 'int', 0);
        self::$field_depart = Model::define_field('depart', 'string', NULL);
        self::$field_type = Model::define_field('type', 'int', 0);
        self::$field_begindate = Model::define_field('begindate', 'datetime', NULL);
        self::$field_enddate = Model::define_field('enddate', 'datetime', NULL);
        self::$field_addtime = Model::define_field('addtime', 'datetime', NULL);
        self::$field_role_type = Model::define_field('role_type', 'int', 0);
        self::$field_remark = Model::define_field('remark', 'string', NULL);
        self::$field_leader_remark = Model::define_field('leader_remark', 'string', NULL);
        self::$field_personnel_remark = Model::define_field('personnel_remark', 'string', NULL);
        self::$field_second_general_remark = Model::define_field('second_general_remark', 'string', NULL);
        self::$field_first_general_remark = Model::define_field('first_general_remark', 'string', NULL);
        self::$field_phone = Model::define_field('phone', 'string', NULL);
        self::$field_status = Model::define_field('status', 'int', 0);
        self::$field_ismoreThree = Model::define_field('ismoreThree', 'int', 0);
        self::$field_casetype = Model::define_field('casetype', 'int', 0);
        self::$field_signtype = Model::define_field('signtype', 'int', 0);
        self::$field_shichang = Model::define_field('shichang', 'int', 0);
        self::$field_leader_time = Model::define_field('leader_time', 'datetime', NULL);
        self::$field_personer_time = Model::define_field('personer_time', 'datetime', NULL);
        self::$field_mastersecond_time = Model::define_field('mastersecond_time', 'datetime', NULL);
        self::$field_masterfirst_time = Model::define_field('masterfirst_time', 'datetime', NULL);
        self::$field_isSaleDept = Model::define_field('isSaleDept', 'int', 0);
        self::$field_leaderName = Model::define_field('leaderName', 'string', NULL);
        self::$field_isRead = Model::define_field('isRead', 'int', 0);
        self::$field_readUser = Model::define_field('readUser', 'string', NULL);
        self::$MODEL_SCHEMA = Model::build_schema('personnel_punch', array(
                    self::$field_id,
                    self::$field_userid,
                    self::$field_username,
                    self::$field_departid,
                    self::$field_depart,
                    self::$field_type,
                    self::$field_begindate,
                    self::$field_enddate,
                    self::$field_addtime,
                    self::$field_role_type,
                    self::$field_remark,
                    self::$field_leader_remark,
                    self::$field_personnel_remark,
                    self::$field_second_general_remark,
                    self::$field_first_general_remark,
                    self::$field_phone,
                    self::$field_ismoreThree,
                    self::$field_casetype,
                    self::$field_signtype,
                    self::$field_shichang,
                    self::$field_leader_time,
                    self::$field_personer_time,
                    self::$field_mastersecond_time,
                    self::$field_masterfirst_time,
                    self::$field_isSaleDept,
                    self::$field_leaderName,
                    self::$field_isRead,
                    self::$field_readUser,
                    self::$field_status
        ));
    }

    public function get_id() {
        return $this->get_field_value(self::$field_id);
    }

    public function set_id($id) {
        $this->set_field_value(self::$field_id, $id);
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

    public function get_departid() {
        return $this->get_field_value(self::$field_departid);
    }

    public function set_departid($departid) {
        $this->set_field_value(self::$field_departid, $departid);
    }

    public function get_depart() {
        return $this->get_field_value(self::$field_depart);
    }

    public function set_depart($depart) {
        $this->set_field_value(self::$field_depart, $depart);
    }

    public function get_type() {
        return $this->get_field_value(self::$field_type);
    }

    public function set_type($type) {
        $this->set_field_value(self::$field_type, $type);
    }

    public function get_begindate() {
        return $this->get_field_value(self::$field_begindate);
    }

    public function set_begindate($begindate) {
        $this->set_field_value(self::$field_begindate, $begindate);
    }

    public function get_enddate() {
        return $this->get_field_value(self::$field_enddate);
    }

    public function set_enddate($enddate) {
        $this->set_field_value(self::$field_enddate, $enddate);
    }

    public function get_addtime() {
        return $this->get_field_value(self::$field_addtime);
    }

    public function set_addtime($addtime) {
        $this->set_field_value(self::$field_addtime, $addtime);
    }

    public function get_role_type() {
        return $this->get_field_value(self::$field_role_type);
    }

    public function set_role_type($role_type) {
        $this->set_field_value(self::$field_role_type, $role_type);
    }

    public function get_remark() {
        return $this->get_field_value(self::$field_remark);
    }

    public function set_remark($remark) {
        $this->set_field_value(self::$field_remark, $remark);
    }

    public function get_leader_remark() {
        return $this->get_field_value(self::$field_leader_remark);
    }

    public function set_leader_remark($leader_remark) {
        $this->set_field_value(self::$field_leader_remark, $leader_remark);
    }

    public function get_personnel_remark() {
        return $this->get_field_value(self::$field_personnel_remark);
    }

    public function set_personnel_remark($personnel_remark) {
        $this->set_field_value(self::$field_personnel_remark, $personnel_remark);
    }

    public function get_second_general_remark() {
        return $this->get_field_value(self::$field_second_general_remark);
    }

    public function set_second_general_remark($second_general_remark) {
        $this->set_field_value(self::$field_second_general_remark, $second_general_remark);
    }

    public function get_first_general_remark() {
        return $this->get_field_value(self::$field_first_general_remark);
    }

    public function set_first_general_remark($first_general_remark) {
        $this->set_field_value(self::$field_first_general_remark, $first_general_remark);
    }

    public function get_status() {
        return $this->get_field_value(self::$field_status);
    }

    public function set_status($status) {
        $this->set_field_value(self::$field_status, $status);
    }

    public function get_phone() {
        return $this->get_field_value(self::$field_phone);
    }

    public function set_phone($phone) {
        $this->set_field_value(self::$field_phone, $phone);
    }

    public function get_ismoreThree() {
        return $this->get_field_value(self::$field_ismoreThree);
    }

    public function set_ismoreThree($ismoreThree) {
        $this->set_field_value(self::$field_ismoreThree, $ismoreThree);
    }

    public function get_casetype() {
        return $this->get_field_value(self::$field_casetype);
    }

    public function set_casetype($casetype) {
        $this->set_field_value(self::$field_casetype, $casetype);
    }

    public function get_signtype() {
        return $this->get_field_value(self::$field_signtype);
    }

    public function set_signtype($signtype) {
        $this->set_field_value(self::$field_signtype, $signtype);
    }

    public function get_shichang() {
        return $this->get_field_value(self::$field_shichang);
    }

    public function set_shichang($shichang) {
        $this->set_field_value(self::$field_shichang, $shichang);
    }

    public function get_leader_time() {
        return $this->get_field_value(self::$field_leader_time);
    }

    public function set_leader_time($leader_time) {
        $this->set_field_value(self::$field_leader_time, $leader_time);
    }

    public function get_personer_time() {
        return $this->get_field_value(self::$field_personer_time);
    }

    public function set_personer_time($personer_time) {
        $this->set_field_value(self::$field_personer_time, $personer_time);
    }

    public function get_mastersecond_time() {
        return $this->get_field_value(self::field_mastersecond_time);
    }

    public function set_mastersecond_time($mastersecond_time) {
        $this->set_field_value(self::$field_mastersecond_time, $mastersecond_time);
    }

    public function get_masterfirst_time() {
        return $this->get_field_value(self::$field_masterfirst_time);
    }

    public function set_masterfirst_time($masterfirst_time) {
        $this->set_field_value(self::$field_masterfirst_time, $masterfirst_time);
    }

    public function get_isSaleDept() {
        return $this->get_field_value(self::$field_isSaleDept);
    }

    public function set_isSaleDept($isSaleDept) {
        $this->set_field_value(self::$field_isSaleDept, $isSaleDept);
    }

    public function get_leaderName() {
        return $this->get_field_value(self::$field_leaderName);
    }

    public function set_leaderName($leaderName) {
        $this->set_field_value(self::$field_leaderName, $leaderName);
    }

    public function get_isRead() {
        return $this->get_field_value(self::$field_isRead);
    }

    public function set_isRead($isRead) {
        $this->set_field_value(self::$field_isRead, $isRead);
    }

    public function get_readUser() {
        return $this->get_field_value(self::$field_readUser);
    }

    public function set_readUser($readUser) {
        $this->set_field_value(self::$field_readUser, $readUser);
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

personnel_punch ::init_schema();
