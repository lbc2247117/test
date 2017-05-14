<?php

/**
 * 推送消息类
 * @author BoCheng
 */
class postMsgClass {

    public $msgtype;  //消息类型
    public $userid;  //用户ID
    public $username;   //用户名
    public $title;      //标题
    public $department;  //部门
    public $resttime;    //原休息时间
    public $tiaoxiutime;  //调休时间
    public $content;   //原因
    public $createtime;    //申请时间
    public $worktime;  //上班时间
    public $fillcardtime; //补卡时间
    public $bukaType; //补卡类型
    public $leaveltime;  //请假时间
    public $leavelTpye; //请假类型
    public $backtime;  //回归时间
    public $sellerorder;  //售出金额
    public $dayorder;  //今日售出金额
    public $allorder;  //总销售金额
    public $rewardType; //业绩类型 1售前  2售后
    public $first;  //第一名
    public $second; //第二名
    public $third; //第三名
    public $receiveruser; // 接收人
    public $clientinfo; //客户QQ
    public $money; //平台金额，代运营定金金额
    public $mobile; //手机号
    public $final_money; //代运营欠款
    public $payment_method; //付款方式
    public $tradeNo; //交易单号
    public $point; //流量点
    public $customer; //优程售后
    public $clientuser; //客户用户名
    public $request_name; //请求者
    public $Punch_Sugger; //选项卡组合
    public $Punch_Name; // 请假，调休，情况说明人
    public $Punch_BeginDate; //开始时间
    public $Punch_EndDate; //结束时间
    public $Punch_Remakr; //调休，请假，情况说明原因
    public $Punch_ShiChang; //请假时长

}

class msgType {

    const notice = 30701; //内容通知
    const reward = 30702; //售前排行
    const sellerback = 30703; //售后排行
    const tiaoxiu = 30704; //调休
    const qingjia = 30705; //请假
    const buka = 30706; //补卡
    const receivercard = 30707;  //指派平台接待
    const callteacher = 30708;   //指派班主任
    const bumoney = 30709;  //代运营补款
    const tousu = 30710;  //投诉
    //const rception_platform = 30711; //平台业绩的指派平台接待
    const flowBukuan = 30712;  //流量业绩补款
    const flowCancel = 30713;  //流量业绩作废
    //const create_newflow = 30714;  //新流量业绩通知
    const sendbacksale = 30715; //推送消息
    const caiwu_result = 30716; //财务审核结果
    const personel_kill = 30717; //行政处罚(公司处罚)
    const depart_kill = 30718; //部门处罚
    const change_permissons = 30719; //权限变更
    const level_user=30720; //离职人员
    const welfare=30721; //福利假
    const person_late =30722; //迟到早退
    const complain_deal = 30723; //投诉处理需回访
    const reception_complain = 30724; //客户回访处理
}

class KQType {

    const causeleave = 1;
    const adjustrest = 2;
    const resign = 3;

}

class role_Type {

    const reception = 0; //前台
    const common = 1; //普通员工
    const leader = 2; //部门负责人
    const Personer = 3; //人事经理
    const secondmaster = 4; //副总
    const firstmaster = 5; //老总

}

class Depart {

    const D502 = 167; //投诉部(严佳)
    const D12 = 106; //店铺客服部(段中潇)
    const D11 = 112; //平台客服部(黄海林)
    const D10 = 102; //活动部(邓寿辉)
    const D9 = 122; //服务部(关晨光)
    const D7 = 20; //售前（王杰）
    const D6 = 19; //售后（曾祥一）
    const D5 = 69; //设计部（韩文学）
    const D4 = 65; //财务(王一)
    const D2 = 203;  //人事部(任波)
    const D8 = 6;   //运营部(陈清)
    const D102 = 62; //郑亚飞
    const D101 = 16; //赵永强

}

class DeptName {

    const master = 1; //总经办
    const personer = 2; //人事部
    const finance = 4; //财务部
    const design = 5; //设计部
    const customer = 6; //售后部
    const pre_sale = 7; //售前部
    const operation = 8; //运营部
    const service = 9; //服务部
    const activity = 10; //活动部
    const platform = 11; //平台客服部
    const store = 12; //店铺客服部
    const complaints = 502; //投诉部

}

class KQstatus {

    const waitself = 0; //待审核
    const waitleader = 1; //等待部门负责人审核
    const leaderSuccess = 2; //部门负责人审核成功
    const leaderFail = 3; //部门不责任审核失败
    const personerSuccess = 4; //人事成功
    const personerFail = 5; //人事失败
    const masterSuccess = 6; //总经办成功
    const masterFail = 7; //总经办失败

}

class Leader_Id {

    const masterfirst = 16; //总经理赵永强
    const mastersecond = 62; //副总经理郑亚飞
    const personer = 203;  //人事任波
    const finance = 65; //财务王漪
    const design = 69; //设计部韩文学
    const pre_sale = 20; //售前王杰
    const customer = 19; //售后曾祥一
    const operation = 6;  //运营陈清
    const service = 122; //服务部关晨光
    const activity = 102; //活动部邓寿辉
    const platform = 112; //平台客服黄海林
    const store = 106; //店铺客服段中萧
    const complaints = 167; //投诉部严佳

}
