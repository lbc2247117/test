<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
        <title>中秋节活动</title>
        <link rel="icon" href="">
        <link rel="stylesheet" href="/active/Application/Home/View//Public/static/css/active/prize.css">
    </head>
    <body>
        <div id="cover" class="cover-cnr pos-rel"></div>
        <div id='content'>
            <div v-show="alert.show" class="alert" style="display:none;">
                <span v-html="alert.msg"></span>
            </div>
            <div id="rollCnr">
                <div id="rollCnt">
                    <img class="fl-l" src="http://7xnjsm.com1.z0.glb.clouddn.com/pan90601.png" width="100%" height="100%">
                    <div class="clear"></div>
                </div>
                <div id="actionBtn"  @click="activeFn"></div>
            </div>

            <div id="nr"></div>
            <!--<div id="ruleCnr" v-show="ruleCnrVisible" @click="hideRule()" style="display:none;">
                <div id="close"></div>
                <div class="clearfix"></div>
                <div class="pad-10">
                    <div style="font-size: 12px;">1.为保证活动的公正性,活动结果由"来吖旅行"粉丝们给的加油点数决定,可通过排行榜查看名次;</div>
                    <br>
                    <div style="font-size: 12px;">2.活动原则上仅限四川省内用户参与,四川省外用户参与请提前在公众号后台留言说明;</div>
                    <br>
                    <div style="font-size: 12px;">3.严禁一切刷票行为,一经发现取消参赛资格;</div>
                    <div style="font-size: 12px;">4.活动不设并列奖,如出现两组成绩并列的情况以时间先后决定名次;</div>
                    <br>
                    <div style="font-size: 12px;">5.活动结果在9月7日的来吖旅行公众号公布,请留意领奖时间:2016.9.7-9.8,逾期领奖将视为自动放弃活动奖品!</div>
                    <br>
                    <div style="font-size: 12px;">本次活动最终解释权归"来吖旅行"所有,如有疑问请后台回复咨询(订阅号:laiyalvyou)。</div>
                </div>
            </div>-->
            <div id="qrCnr" v-show="qrCnrVisible" style="display:none;">
                <div class="close" @click="hideQR()">&times;</div>
                <div class="clearfix"></div>
                <div class="text-center text-lg" v-text="qrMsg"></div>
                <img src="http://7xnjsm.com1.z0.glb.clouddn.com/qr90601.jpg">
            </div>
            <!-- <div id="toTop" @click="toTop"></div>-->
        </div>
        <script src="/active/Public/js/jquery.min.js"></script>
        <script src="/active/Public/js/vue.min.js"></script>
        <script src="/active/Application/Home/View//Public/static/js/app.js"></script>
        <script src="/active/Application/Home/View//Public/static/js/active/prize.js"></script>
        <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
        <script>
            wx.config({
                debug: false,
                appId: "<?php echo ($sign["appId"]); ?>",
                timestamp: <?php echo ($sign["timestamp"]); ?>,
                nonceStr: "<?php echo ($sign["nonceStr"]); ?>",
                signature: "<?php echo ($sign["signature"]); ?>",
                jsApiList: [
                    'onMenuShareTimeline',
                    'onMenuShareAppMessage',
                    'onMenuShareQQ'
                ]
            });
            wx.ready(function () {
                wx.onMenuShareTimeline({
                    title: "来吖旅行|教师节活动", // 分享标题
                    link: "http://www.sda88.cn/Active/index.html", // 分享链接
                    imgUrl: "http://7xnjsm.com1.z0.glb.clouddn.com/shareImg90101.jpg", // 分享图标
                    success: function () {
                        // 用户确认分享后执行的回调函数
                        $.post('shareSuccess', {type: 0}, function (rst) {
                        });
                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }});
                wx.onMenuShareAppMessage({
                    title: "来吖旅行|教师节活动", // 分享标题
                    desc: "来吖旅行|老师很嗨，教师节活动，赢取五月天成都站演唱会门票，快来参加吧~", // 分享描述
                    link: "http://www.sda88.cn/Active/index.html", // 分享链接
                    imgUrl: "http://7xnjsm.com1.z0.glb.clouddn.com/shareImg90101.jpg", // 分享图标
                    type: '', // 分享类型,music、video或link，不填默认为link
                    dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                    success: function () {
                        // 用户确认分享后执行的回调函数
                        $.post('shareSuccess', {type: 0}, function (rst) {
                        });
                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }
                });
                wx.onMenuShareQQ({
                    title: "来吖旅行|教师节活动", // 分享标题
                    desc: "来吖旅行|老师很嗨，教师节活动，赢取五月天成都站演唱会门票，快来参加吧~", // 分享描述
                    link: "http://www.sda88.cn/Active/index.html", // 分享链接
                    imgUrl: "http://7xnjsm.com1.z0.glb.clouddn.com/shareImg90101.jpg", // 分享图标
                    success: function () {
                        // 用户确认分享后执行的回调函数
                        $.post('shareSuccess', {type: 0}, function (rst) {
                        });
                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }
                });
            });
        </script>
    </body>
</html>