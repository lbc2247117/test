<?php

/**
 * 排班计划
 *
 * @author B.Maru
 * @copyright 2015 星密码
 * @version 2015/5/12
 */
use Models\Base\Model;
use Models\W_WorkTable;
use Models\Base\SqlOperator;

require '../../common/ExportData2Excel.php';
require '../../common/ImportExcel2Data.php';
require '../../application.php';
require '../../loader-api.php';
require '../../common/http.php';
require '../../common/SysKV.php';

$action = request_action();
execute_request(HttpRequestMethod::Get, function() use($action) {
    $userid = request_userid();
    $searchTime = request_string('searchTime');
    $search_dept_id = request_int("dept_id");
    $depts = get_depts();
    $users = get_employees();
    $user = $users[$userid];
    $dept_id = $user['dept1_id'];
    $dept_name = "";
    if (!isset($searchTime)) {
        $searchTime = date('Y-m');
    }
    if ($action == 1) {
        $workTable = new W_WorkTable();
        $sql = "SELECT mu.userid,wt.id,wt.m1,wt.m2,wt.m3,wt.m4,wt.m5,wt.m6,wt.m7,wt.m8,wt.m9,wt.m10,wt.m11,wt.m12,wt.m13,wt.m14,wt.m15,wt.m16,wt.m17,wt.m18,wt.m19,wt.m20,wt.m21,wt.m22,wt.m23,wt.m24,wt.m25,wt.m26,wt.m27,wt.m28,wt.m29,wt.m30,wt.m31,IFNULL(wt.date,'" . $searchTime . "-01') date,wt.remark  ";
        $sql.= "FROM M_User mu  LEFT JOIN (SELECT * FROM W_WorkTable wt WHERE DATE_FORMAT(wt.date,'%Y-%m') = '" . $searchTime . "') wt on mu.userid = wt.userid WHERE mu.username != 'admin' AND mu.`status` IN (1,2) AND mu.employee_no LIKE 'YC%' ";
        if (in_array($dept_id, array(1, 2))) {
            if (isset($search_dept_id)) {
                $sql.="AND mu.dept1_id = " . $search_dept_id . " ";
                $dept_name = $depts[$search_dept_id]['text'];
            } else {
                $dept_name = "全公司";
            }
        } else {
            $sql.="AND mu.dept1_id = " . $dept_id . " ";
            $dept_name = $depts[$dept_id]['text'];
        }
        $sql .= "ORDER BY wt.id DESC";
        $db = create_pdo();
        $result = Model::query_list($db, $workTable, $sql);
        if (!$result[0]) die_error(USER_ERROR, '获取部门人员排班计划列表信息失败~');
        $models = Model::list_to_array($result['models'], array(), function (&$d) use($users) {
                    $d['name'] = $users[$d['userid']]['username'];
                });
        $returnTime = explode("-", $searchTime);
        $title = $returnTime[0] . "年" . $returnTime[1] . "月" . $dept_name . '排班计划表';
        $return_array = array('currentDate' => $searchTime, 'c_d' => date('Y-m-d'), 'currentWeek' => Date("w", strtotime($searchTime)), 'start_year' => START_YEAR, 'current_year' => date('Y'), 'list' => $models, 'title' => $title);
        echo_result($return_array);
    }
    if ($action == 2) {
        $work = new W_WorkTable();
        $work->set_where_and(W_WorkTable::$field_userid, SqlOperator::Equals, $userid);
        $work->set_custom_where(" AND DATE_FORMAT(date,'%Y-%m') = '" . date('Y-m') . "'");
        $db = create_pdo();
        $result = $work->load($db, $work);
        if ($result[0]) {
            echo_result(array('list' => $work->to_array(), 'current_date' => date('Y-m-d')));
        } else {
            echo_result(array('list' => NULL, 'current_date' => date('Y-m-d')));
        }
    }
    if ($action == 11) {
        $export = new ExportData2Excel();
        $workTable = new W_WorkTable();
//        $workTable->set_custom_where(" and DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH),'%Y-%m') and dept_id = " . $dept_id . " and userid != 1 ");
        $workTable->set_custom_where(" and DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') and dept_id = " . $dept_id . " and userid != 1 ");
        $db = create_pdo();
        $result = Model::query_list($db, $workTable, NULL, true);
        if (!$result[0]) {
            $export->create(array('导出错误'), array(array('排班模板导出失败,请稍后重试!')), "排班模板导出", "排班模板");
        }
        $models = Model::list_to_array($result['models'], array(), function(&$d) use ($depts, $users) {
                    $d['dept_id'] = $depts[$d['dept_id']]['text'];
                    $d['username'] = $users[$d['userid']]['username'];
                    $d['date'] = date('Y-m-01');
                });
        $title_array = array('用户编号', '用户姓名', '部门', '时间', '1号', '2号', '3号', '4号', '5号', '6号', '7号', '8号', '9号', '10号', '11号', '12号', '13号', '14号', '15号', '16号', '17号', '18号', '19号', '20号', '21号', '22号', '23号', '24号', '25号', '26号', '27号', '28号', '29号', '30号', '31号', '备注');
        $export->set_field(array('userid', 'username', 'dept_id', 'date', 'm1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8', 'm9', 'm10', 'm11', 'm12', 'm13', 'm14', 'm15', 'm16', 'm17', 'm18', 'm19', 'm20', 'm21', 'm22', 'm23', 'm24', 'm25', 'm26', 'm27', 'm28', 'm29', 'm30', 'm31', 'remark'));
        $export->set_field_width(array(10, 10, 15, 15, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 10, 20));
        $export->create($title_array, $models, "", "排班模板");
    }
});

execute_request(HttpRequestMethod::Post, function() use($action) {
    /**
     * 月度保存
     */
    if ($action == 1) {
        $workData = request_object();
        $work_has = new W_WorkTable();
        $date = date('Y-m');
        if (isset($workData->add_date)) $date = date($workData->add_date);
        $work_has->set_where(W_WorkTable::$field_userid, SqlOperator::Equals, $workData->userid);
        $work_has->set_custom_where(" AND DATE_FORMAT(date,'%Y-%m') = DATE_FORMAT('" . $date . "','%Y-%m') ");
        $db = create_pdo();
        $result = $work_has->load($db, $work_has);
        if ($result[0]) die_error(USER_ERROR, '本月排班计划数据已提交,无需重复提交~');
        $work = new W_WorkTable();
        $work->set_field_from_array($workData);
        $work->set_date($date);
        $result = $work->insert($db);
        if (!$result[0]) die_error(USER_ERROR, '保存排班计划数据失败~');
        echo_msg('保存排班计划数据成功~ ');
    }

    //导入
    if ($action == 2) {
        if (!isset($_FILES['work_table']) || str_length($_FILES['work_table']['name']) <= 0) {
            die_error(-1, '文件上传失败,请稍后重试~');
        }
        $tmp_file = $_FILES ['work_table']['tmp_name'];
        $file_types = explode('.', $_FILES['work_table']['name']);
        $file_type = $file_types[count($file_types) - 1];
        if (!in_array(strtolower($file_type), array('xls'))) die_error(-1, '不是Excel文件，请重新上传');
        $savePath = '../../upload/worktable/';
        $file_name = date('YmdHis') . '_' . md5($_FILES['work_table']['name'] . mt_rand(100, 999)) . '.' . $file_type;
        if (!copy($tmp_file, $savePath . $file_name)) {
            die_error(-1, '上传失败');
        }
        $excelData = ImportExcel2Data::excel2array($savePath . $file_name, 'xls');
        $db = create_pdo();
        foreach ($excelData as $value) {
            if (!is_numeric($value[0])) continue;
            $worktable = new W_WorkTable();
            foreach ($value as $k => $v) {
                if ($k < 4) continue;
                $field_name = $worktable->get_field_by_name('m' . ($k - 3));
                if (!isset($field_name)) continue;
                if (!isset($v)) $v = '';
                $v = str_replace('休', '0', $v);
                $worktable->set_field_value($field_name, $v);
            }
            $worktable->set_remark($value[count($value) - 1]);
            $worktable->set_where_and(W_WorkTable::$field_userid, SqlOperator::Equals, $value[0]);
            $worktable->set_where_and(W_WorkTable::$field_date, SqlOperator::Equals, $value[3]);
            $worktable->update($db);
        }
        echo_code(0);
    }

    //CTRL + S 保存
    if ($action == 3) {
        $json_data = request_string("json_data");
        $json_data = json_decode($json_data, true);
        $db = create_pdo();
        pdo_transaction($db, function($db) use($json_data) {
            foreach ($json_data as $value) {
                $worktable = new W_WorkTable();
                if ($value['id'] == 0) {
                    $worktable->set_field_from_array($value);
                    $result = $worktable->insert($db);
                    if (!$result[0]) throw new TransactionException(PDO_ERROR_CODE, '保存失败~' . $result['detail_cn'], $result);
                } else {
                    $worktable->set_where_and(W_WorkTable::$field_userid, SqlOperator::Equals, $value['userid']);
                    $worktable->set_where_and(W_WorkTable::$field_id, SqlOperator::Equals, $value['id']);
                    unset($value['date']);
                    $worktable->set_field_from_array($value);
                    $result = $worktable->update($db);
                     if (!$result[0]) throw new TransactionException(PDO_ERROR_CODE, '保存失败~' . $result['detail_cn'], $result);
                }
            }
        });
        echo_msg("保存成功~");
    }
});
