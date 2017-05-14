<?php
/*是否需要副总经理审核*/
class Is_fuzong_approve{
    const no=0;  //不需要
    const yes=1; //需要
}
/*是否需要总经理审核*/
class Is_zongjing_approve{
    const no=0; //不需要
    const yes=1; //需要
}
/*考勤状态*/
class P_status{
    const wait_confirm =0; //待确认
    const depart_leader_approveing =1; //部门主管审核中
    const fuzong_approveing=2; //副总经理审核中
    const zongjing_approving=3; //总经理审核中
    const renshi_leader_approving=4; //人事经理审核中
    const sucess=5; //完成
}
/*部门主管审核意见*/
class Depart_leader_mean{
    const no=0; //不同意
    const yes=1; //同意
}
/*人事经理审核意见*/
class Renshi_leader_mean{
    const no=0; //不同意
    const yes=1; //同意
}
/*副总经理审核意见*/
class Fuzong_mean{
    const no=0; //不同意
    const yes=1; //同意   
}
/*总经理审核意见*/
class Zongjing_mean{
    const no=0; //不同意
    const yes=1; //同意   
}
/*人事类型*/
class P_type{
    const qingjia=1; //请假
    const tiaoxiu=2; //调休
    const qingkuangshuoming=3; //情况说明
    const fulijia=4; //福利假
    const depart_qingkuang=5; //部门情况说明
}
/*福利假类型*/
class Child_type{
    const marry=1;//婚假
    const heath=2; //病假
    const dead=3; //丧假
    const child=4;//产假
    const peichanjia=5;//陪产假
}

/*显示状态*/
class View_status{
    const wait_submit="等待提交审核";
    const wait_depart_leader_approve="等待主管审核";
    const wait_renshi_leader_approve="等待人事经理审核";
    const wait_fuzong_approve="等待副总审核";
    const wait_zongjingli_approve="等待总经理审核";
    const depart_leader_approve_fail="部门主管(经理)不同意";
    const renshi_leader_approve_fail="人事经理不同意";
    const fuzong_apprvoe_fail="副总不同意";
    const zongjingli_approve_fail="总经理不同意";
    const sucess="完成";
}
/*部门负责人*/
class Leader{
    const tousu=167; //投诉部(严佳)
    const dianpukefu =106; //店铺客服部(段中潇)
    const huodong=102; //活动部(邓寿辉)
    const fuwu=122 ;// 服务部(关晨光)
    const shouqian=20; //售前部(王杰)
    const shouhou =19;//售后部(曾详一)
    const sheji=69;//设计部(韩文学)
    const caiwu=65;//财务部(王yi)
    const renshi=203;//人事(任波)
    const yunying=6;//陈清
    const fuzong=62; //副总经理(郑亚飞)
    const zongjingli=16; //总经理(赵永强)
    const pingtaikefu=112; //平台客服(黄海林)
}

/*部门负责人姓名*/
class LeaderName{
    const tousu='严佳'; //投诉部(严佳)
    const dianpukefu ='段中潇'; //店铺客服部(段中潇)
    const huodong='邓寿辉'; //活动部(邓寿辉)
    const fuwu='关晨光' ;// 服务部(关晨光)
    const shouqian='王杰'; //售前部(王杰)
    const shouhou ='曾详一';//售后部(曾详一)
    const sheji='韩文学';//设计部(韩文学)
    const caiwu='王漪';//财务部(王yi)
    const renshi='任波';//人事(任波)
    const yunying='陈清';//陈清
    const fuzong='郑亚飞'; //副总经理(郑亚飞)
    const zongjingli='赵永强'; //总经理(赵永强)
    const pingtaikefu='黄海林';
}

/*人事部员工ID*/
class RenshiEmployee{
    const lwh=18; //廖伟宏
    const fmj =204; //方梦娇
    const hcx=205; //黄彩霞
}

/*部门编号*/
class Department{
    const tousu=502; //投诉部
    const shouqian=7;  //售前部
    const shouhou=6; //售后部
    const fuwu=9; //服务部
    const renshi=2; //人事部
    const sheji=5; //设计部
    const yunying=8; //运营部
    const dianpukefu=12;//店铺客服
    const huodong=10; //活动部
    const pingtaikefu=11; //平台客服
}
class Permion_btn{
    const Wait_submit=0;
    const Depart_leader=1;
    const Renshi_leader=4;
    const Fuzong=2;
    const Zongjingli=3;
    const complete=5;
}
