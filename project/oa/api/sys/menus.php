<?php

/**
 * 菜单列表
 *
 * @author ChenHao
 * @copyright 2015 星密码
 * @version 2015/1/26
 */
require '../../application.php';
require '../../loader-api.php';

check_login();

list($userid) = filter_request(array(
    request_userid()));

$user = get_employees()[$userid];
$dept1_id = $user['dept1_id'];
$dept2_id = $user['dept2_id'];
$role_id = $user['role_id'];

$role = get_roles()[$role_id];
$dept1 = get_depts()[$dept1_id];
$permit_user = $user['permit'];
$permit_role = $role['permit'];
$permit_dpt = $dept1['permit'];

if (strlen($user['permit']) > 0 && !str_equals($user['permit'], ';')) {
    $permit = $user['permit'];
} else {
    $role = get_roles()[$role_id];
    if (strlen($role['permit']) > 0 && !str_equals($role['permit'], ';')) {
        $permit = $role['permit'];
    } else {
        $dept1 = get_depts()[$dept1_id];
        $permit = $dept1['permit'];
    }
}
$permit_user = explode(';', $permit_user);
$permit_role = explode(';', $permit_role);
$permit_dpt = explode(';', $permit_dpt);
$user_menus = array();
$user_ctrls = array();

if ($permit_user[0] != '' && $permit_user[1] != '') {
    $user_menus = array_merge($user_menus, explode(',', $permit_user[0]));
    $user_ctrls = array_merge($user_ctrls, explode(',', $permit_user[1]));
}
if ($permit_role[0] != '' && $permit_role[1] != '') {
    $user_menus = array_merge($user_menus, explode(',', $permit_role[0]));
    $user_ctrls = array_merge($user_ctrls, explode(',', $permit_role[1]));
}
if ($permit_dpt[0] != '' && $permit_dpt[1] != '') {
    $user_menus = array_merge($user_menus, explode(',', $permit_dpt[0]));
    $user_ctrls = array_merge($user_ctrls, explode(',', $permit_dpt[1]));
}
$user_menus = array_unique($user_menus);
$user_ctrls = array_unique($user_ctrls);

$user_menus = array_filter($user_menus, function($var) {
    $msgarr = array('307', '30701', '30702', '30703', '30704', '30705', '30706', '30707', '30708', '30709', '30710', '30711', '30712', '30713', '30714', '30715', '30716', '30717', '30718');
    if (!in_array($var, $msgarr))
        return $var;
});
//$menu = new S_Menu();
//$db = create_pdo();
//$result = Model::query_list($db, $menu);
//if (!$result[0]) die_error(USER_ERROR, '读取菜单失败，请刷新重试');
//$result = Model::list_to_array($result['models']);

$result = get_menus();

$menus = array_filter($result, function($item) {
    return !in_array($item['id'], array(1, 2, 3));
});
$menus = array_filter($menus, function($item) use($user_menus) {
    return in_array($item['id'], $user_menus);
});
$user_menus = $menus;

$menus = array_filter($menus, function($item) {
    return in_array($item['parent_id'], array(1, 2, 3));
});

array_walk($menus, function(&$menu) use($user_menus) {
    $childs = array_filter($user_menus, function($item) use($menu) {
        return $item['parent_id'] == $menu['id'];
    });
    $menu['childs'] = array_values($childs);
});
echo_result(array('permitMenus' => array_values($menus), 'permitButtons' => implode(',', $user_ctrls)));
