<?php

/**
 * 用户相关操作
 *
 * @author ChenHao
 * @copyright 2015 星密码
 * @version 2015/2/11
 */
use Models\Base\Model;
use Models\M_User;
use Models\M_UserExt;
use Models\M_UserToken;
use Models\Base\SqlOperator;
use Models\M_Role;

require '../../application.php';
require '../../loader-api.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    if ($action == 0) {
        $reArray = array();
        $par = request_string("term");
        $par = strtoupper($par);
        $users = get_employees();
        foreach ($users as $user) {
            $py = strpos($user["py"], $par) !== false ? true : false;
            $name = strpos($user["username"], $par) !== false ? true : false;
            if ($py || $name) {
                array_push($reArray, $user);
            }
        }
        echo_result($reArray);
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    //$userData = request_object();
    //登录
    if ($action == 1) {
        list($username, $password) = filter_request(array(
            request_string('username'),
            request_md5_16('password')));
        $remember = request_boolean('remember');
        $login_type = request_int('type');
        filter_numeric($login_type, 0);
        if ($login_type == 1) {
            list($machineCode, $version) = filter_request(array(
                request_md5_32('machinecode'),
                request_string('version')));
            if (!str_equals(CLIENT_VERSION, $version)) {
                $result = array('Code' => 'VER_UPDATE_CODE', 'Msg' => '请更新至最新版本再登录');
                die(get_response($result));
            }
        }

        if (!filter_regexp($username, '/^(YC|ZH)\d{5}$/'))
            die_error(USER_ERROR, '登录失败，用户名或密码错误'); //die_error(USER_ERROR, '登录失败，请输入正确的员工编号');

        $userToken = new M_UserToken();
        $userToken->set_query_fields(array(M_UserToken::$field_userid, M_UserToken::$field_employee_no, M_UserToken::$field_username, M_UserToken::$field_status, M_UserToken::$field_token, M_UserToken::$field_avatar));

//        if (filter_regexp($username, '/^YC\d{5}$/i')) {
//            $userToken->set_where_and(M_UserToken::$field_employee_no, SqlOperator::Equals, $username);
//        } else {
//            $userToken->set_where_and(M_UserToken::$field_username, SqlOperator::Equals, $username);
//        }

        $userToken->set_where_and(M_UserToken::$field_employee_no, SqlOperator::Equals, $username);
        $userToken->set_where_and(M_UserToken::$field_password, SqlOperator::Equals, $password);

        $db = create_pdo();
        $result = $userToken->load($db, $userToken);
        if (!$result[0])
            die_error(USER_ERROR, '登录失败1，用户名或密码错误');
        if ($userToken->get_status() == 3)
            die_error(USER_ERROR, '登录失败，当前状态禁止登录');

        //更新用户令牌
        if ($login_type != 1)
            set_cookie($db, $userToken, $remember);

        $result = $userToken->to_array();
        $result['code'] = 0;

        if ($login_type == 1) {
            $result['PublicKey'] = PUBLIC_KEY;
            $result['PushApiUrl'] = PUSH_API_URL;
            $result['UserToken'] = $result['token'];
            $result['EmployeeNo'] = $result['employee_no'];
            $result['rabbitmq_server_ip']=RABBITMQ_SERVER_IP;
            $result['rabbitmq_loginname']=RABBITMQ_LOGINNAME;
            $result['rabbitmq_password']=RABBITMQ_PASSWORD;
            $result['webApiUrl']=WEBAPIURL;
            $user = get_employees()[$userToken->get_userid()];
            $dept = get_depts()[$user['dept1_id']];
            $result['DeptName'] = $dept['text'];
        }

        unset($result['password']);
        unset($result['token']);
        unset($result['session_magic_mark']);

        $user = new M_User($result['userid']);
        $res = $user->load($db, $user);
        if (!$res[0])
            die_error(USER_ERROR, '登录失败，获取用户信息失败');
        $permit_user = $user->get_permit();
        $roleid = $user->get_role_id();
        $role = new M_Role($roleid);
        $res = $role->load($db, $role);
        if (!$res[0])
            die_error(USER_ERROR, '登录失败，获取职位信息失败');
        $permit_role = $role->get_permit();
        $result['permit_user'] = implode(',', array_unique(array_merge(explode(',', $permit_role), explode(',', $permit_user))));
        $result['maxmsgcount'] = 10;
        $result['Deptid'] = get_dept_id($result['userid']);
        echo_result($result);
    }
    //修改资料
    if ($action == 2) {
//        $user = new M_User();
//        $user->set_field_from_array($userData);
//        $db = create_pdo();
//        $result = $user->update($db, true);
//        if (!$result[0]) die_error(USER_ERROR, '保存员工资料失败');
//        echo_msg('保存成功');
    }

    if ($action == 4) {
        $userid = request_int('userid');
        $old_pwd = request_string("old_pwd");
        $new_pwd = request_string("new_pwd");
        $userToken = new M_UserToken($userid);
        $db = create_pdo();
        $result = $userToken->load($db, $userToken);
        if (!$result[0])
            die_error(USER_ERROR, "获取用户信息失败,请重稍后重试~");
        if (str_equals($userToken->get_password(), $new_pwd)) {
            die_error(USER_ERROR, "原始密码与星密码相同,请核对后重试~");
        } else if (str_equals($userToken->get_password(), $old_pwd)) {
            $userToken->set_password($new_pwd);
            $result = $userToken->update($db, true);
            if (!$result[0])
                die_error(USER_ERROR, "密码修改失败,请重稍后重试~");
            echo_msg("密码修改成功,请重新登陆系统~");
        }else {
            die_error(USER_ERROR, "原始密码输入错误,请核对后重试~");
        }
    }
    //客户端重新获取权限
    if($action==5){
        $user = new M_User(request_int('userid'));
        $res = $user->load($db, $user);
        if (!$res[0])
            die_error(USER_ERROR, '登录失败，获取用户信息失败');
        $permit_user = $user->get_permit();
        $roleid = $user->get_role_id();
        $role = new M_Role($roleid);
        $res = $role->load($db, $role);
        if (!$res[0])
            die_error(USER_ERROR, '登录失败，获取职位信息失败');
        $permit_role = $role->get_permit();
        $result['permit_user'] = implode(',', array_unique(array_merge(explode(',', $permit_role), explode(',', $permit_user))));
        $result['maxmsgcount'] = 10;
        $result['Deptid'] = get_dept_id($result['userid']);
        echo_result($result);
    }
});
