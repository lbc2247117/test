<?php

/**
 * 考勤表
 *
 * @author 自动生成的实体类
 * @copyright (c) 2015, 非时序集团
 * @version 1.0
 */

namespace Models;

$GLOBALS['/Models/P_hr.php'] = 1;

use Models\Base\Model;

class P_hr extends Model {

    public static $field_id;
    public static $field_userid;
    public static $field_username;
    public static $field_mobile;
    public static $field_departid;
    public static $field_depart;
    public static $field_begindate;
    public static $field_enddate;
    public static $field_addtime;
    public static $field_pursh_time;
    public static $field_is_fuzong_approve;
    public static $field_is_zongjing_approve;
    public static $field_status;
    public static $field_view_status;
    public static $field_depart_leader_mean;
    public static $field_depart_leader_remark;
    public static $field_depart_leader_time;
    public static $field_renshi_leader_mean;
    public static $field_renshi_leader_remark;
    public static $field_renshi_leader_time;
    public static $field_fuzong_mean;
    public static $field_fuzong_remark;
    public static $field_fuzong_time;
    public static $field_zongjing_mean;
    public static $field_zongjing_remark;
    public static $field_zongjing_time;
    public static $field_type;
    public static $field_child_type;
    public static $field_matters_type;
    public static $field_reason;
    public static $field_isRead;
    public static $MODEL_SCHEMA;

    static function init_schema() {
        self::$field_id = Model::define_primary_key('id', 'int', 0, true);
        self::$field_userid = Model::define_field('userid', 'int', 0);
        self::$field_username = Model::define_field('username', 'string', NULL);
        self::$field_mobile = Model::define_field('mobile', 'string', NULL);
        self::$field_departid = Model::define_field('departid', 'int', 0);
        self::$field_depart = Model::define_field('depart', 'string', NULL);
        self::$field_begindate = Model::define_field('begindate', 'datetime', NULL);
        self::$field_enddate = Model::define_field('enddate', 'datetime', NULL);
        self::$field_addtime = Model::define_field('addtime', 'datetime', NULL);
        self::$field_pursh_time = Model::define_field('pursh_time', 'float', NULL);
        self::$field_is_fuzong_approve = Model::define_field('is_fuzong_approve', 'int', 0);
        self::$field_is_zongjing_approve = Model::define_field('is_zongjing_approve', 'int', 0);
        self::$field_status = Model::define_field('status', 'int', 0);
        self::$field_view_status = Model::define_field('view_status', 'string', NULL);
        self::$field_depart_leader_mean = Model::define_field('depart_leader_mean', 'string', NULL);
        self::$field_depart_leader_remark = Model::define_field('depart_leader_remark', 'string', NULL);
        self::$field_depart_leader_time = Model::define_field('depart_leader_time', 'datetime', NULL);
        self::$field_renshi_leader_mean = Model::define_field('renshi_leader_mean', 'string', NULL);
        self::$field_renshi_leader_remark = Model::define_field('renshi_leader_remark', 'string', NULL);
        self::$field_renshi_leader_time = Model::define_field('renshi_leader_time', 'datetime', NULL);
        self::$field_fuzong_mean = Model::define_field('fuzong_mean', 'string', NULL);
        self::$field_fuzong_remark = Model::define_field('fuzong_remark', 'string', NULL);
        self::$field_fuzong_time = Model::define_field('fuzong_time', 'datetime', NULL);
        self::$field_zongjing_mean = Model::define_field('zongjing_mean', 'string', NULL);
        self::$field_zongjing_remark = Model::define_field('zongjing_remark', 'string', NULL);
        self::$field_zongjing_time = Model::define_field('zongjing_time', 'datetime', NULL);
        self::$field_type = Model::define_field('type', 'int', 0);
        self::$field_child_type = Model::define_field('child_type', 'int', 0);
        self::$field_matters_type = Model::define_field('matters_type', 'string', NULL);
        self::$field_reason = Model::define_field('reason', 'string', NULL);
        self::$field_isRead=Model::define_field('isRead','int',0);
        self::$MODEL_SCHEMA = Model::build_schema('p_hr', array(
                    self::$field_id,
                    self::$field_userid,
                    self::$field_username,
                    self::$field_mobile,
                    self::$field_departid,
                    self::$field_depart,
                    self::$field_begindate,
                    self::$field_enddate,
                    self::$field_addtime,
                    self::$field_pursh_time,
                    self::$field_is_fuzong_approve,
                    self::$field_is_zongjing_approve,
                    self::$field_status,
                    self::$field_view_status,
                    self::$field_depart_leader_mean,
                    self::$field_depart_leader_remark,
                    self::$field_depart_leader_time,
                    self::$field_renshi_leader_mean,
                    self::$field_renshi_leader_remark,
                    self::$field_renshi_leader_time,
                    self::$field_fuzong_mean,
                    self::$field_fuzong_remark,
                    self::$field_fuzong_time,
                    self::$field_zongjing_mean,
                    self::$field_zongjing_remark,
                    self::$field_zongjing_time,
                    self::$field_type,
                    self::$field_child_type,
                    self::$field_matters_type,
                    self::$field_reason,
                    self::$field_isRead
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

    public function get_mobile() {
        return $this->get_field_value(self::$field_mobile);
    }

    public function set_mobile($mobile) {
        $this->set_field_value(self::$field_mobile, $mobile);
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

    public function get_pursh_time() {
        return $this->get_field_value(self::$field_pursh_time);
    }

    public function set_pursh_time($pursh_time) {
        $this->set_field_value(self::$field_pursh_time, $pursh_time);
    }

    public function get_is_fuzong_approve() {
        return $this->get_field_value(self::$field_is_fuzong_approve);
    }

    public function set_is_fuzong_approve($is_fuzong_approve) {
        $this->set_field_value(self::$field_is_fuzong_approve, $is_fuzong_approve);
    }

    public function get_is_zongjing_approve() {
        return $this->get_field_value(self::$field_is_zongjing_approve);
    }

    public function set_is_zongjing_approve($is_zongjing_approve) {
        $this->set_field_value(self::$field_is_zongjing_approve, $is_zongjing_approve);
    }

    public function get_status() {
        return $this->get_field_value(self::$field_status);
    }

    public function set_status($status) {
        $this->set_field_value(self::$field_status, $status);
    }

    public function get_view_status() {
        return $this->get_field_value(self::$field_view_status);
    }

    public function set_view_status($view_status) {
        $this->set_field_value(self::$field_view_status, $view_status);
    }

    public function get_depart_leader_mean() {
        return $this->get_field_value(self::$field_depart_leader_mean);
    }

    public function set_depart_leader_mean($depart_leader_mean) {
        $this->set_field_value(self::$field_depart_leader_mean, $depart_leader_mean);
    }

    public function get_depart_leader_remark() {
        return $this->get_field_value(self::$field_depart_leader_remark);
    }

    public function set_depart_leader_remark($depart_leader_remark) {
        $this->set_field_value(self::$field_depart_leader_remark, $depart_leader_remark);
    }

    public function get_depart_leader_time() {
        return $this->get_field_value(self::$field_depart_leader_time);
    }

    public function set_depart_leader_time($depart_leader_time) {
        $this->set_field_value(self::$field_depart_leader_time, $depart_leader_time);
    }

    public function get_renshi_leader_mean() {
        return $this->get_field_value(self::$field_renshi_leader_mean);
    }

    public function set_renshi_leader_mean($renshi_leader_mean) {
        $this->set_field_value(self::$field_renshi_leader_mean, $renshi_leader_mean);
    }

    public function get_renshi_leader_remark() {
        return $this->get_field_value(self::$field_renshi_leader_remark);
    }

    public function set_renshi_leader_remark($renshi_leader_remark) {
        $this->set_field_value(self::$field_renshi_leader_remark, $renshi_leader_remark);
    }

    public function get_renshi_leader_time() {
        return $this->get_field_value(self::$field_renshi_leader_time);
    }

    public function set_renshi_leader_time($renshi_leader_time) {
        $this->set_field_value(self::$field_renshi_leader_time, $renshi_leader_time);
    }

    public function get_fuzong_mean() {
        return $this->get_field_value(self::$field_fuzong_mean);
    }

    public function set_fuzong_mean($fuzong_mean) {
        $this->set_field_value(self::$field_fuzong_mean, $fuzong_mean);
    }

    public function get_fuzong_remark() {
        return $this->get_field_value(self::$field_fuzong_remark);
    }

    public function set_fuzong_remark($fuzong_remark) {
        $this->set_field_value(self::$field_fuzong_remark, $fuzong_remark);
    }

    public function get_fuzong_time() {
        return $this->get_field_value(self::$field_fuzong_time);
    }

    public function set_fuzong_time($fuzong_time) {
        $this->set_field_value(self::$field_fuzong_time, $fuzong_time);
    }

    public function get_zongjing_mean() {
        return $this->get_field_value(self::$field_zongjing_mean);
    }

    public function set_zongjing_mean($zongjing_mean) {
        $this->set_field_value(self::$field_zongjing_mean, $zongjing_mean);
    }

    public function get_zongjing_remark() {
        return $this->get_field_value(self::$field_zongjing_remark);
    }

    public function set_zongjing_remark($zongjing_remark) {
        $this->set_field_value(self::$field_zongjing_remark, $zongjing_remark);
    }

    public function get_zongjing_time() {
        return $this->get_field_value(self::$field_zongjing_time);
    }

    public function set_zongjing_time($zongjing_time) {
        $this->set_field_value(self::$field_zongjing_time, $zongjing_time);
    }

    public function get_type() {
        return $this->get_field_value(self::$field_type);
    }

    public function set_type($type) {
        $this->set_field_value(self::$field_type, $type);
    }

    public function get_child_type() {
        return $this->get_field_value(self::$field_child_type);
    }

    public function set_child_type($child_type) {
        $this->set_field_value(self::$field_child_type, $child_type);
    }

    public function get_matters_type() {
        return $this->get_field_value(self::$field_matters_type);
    }

    public function set_matters_type($matters_type) {
        $this->set_field_value(self::$field_matters_type, $matters_type);
    }
    
    public function get_reason() {
        return $this->get_field_value(self::$field_reason);
    }

    public function set_reason($reason) {
        $this->set_field_value(self::$field_reason, $reason);
    }
    
    public function get_isRead() {
        return $this->get_field_value(self::$field_isRead);
    }

    public function set_isRead($isRead) {
        $this->set_field_value(self::$field_isRead, $isRead);
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

P_hr::init_schema();
