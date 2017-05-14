<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
        <title>2016中国超级跑车锦标赛</title>
        <link rel="icon" href="">
        <link rel="stylesheet" href="/active/Application/Home/View//Public/static/css/active/racing.css">
    </head>
    <body>
        <div id="cover" class="cover-cnr pos-rel"></div>

        <div id="laiaCnr">
            <div v-show="alert.show" class="alert" style="display:none;">
                <span v-html="alert.msg"></span>
            </div>
            <div v-show="confirm.show" class="confirm" style="display:none;">
                <div class="confirm-cnt">
                    <span v-html="confirm.msg"></span>
                    <div class="confirm-btn-cnr">
                        <div class="confirm-btn" @click="confirmFn()">确定</div>
                        <div class="confirm-btn" @click="cancelFn()">取消</div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
            <div id="join">
                <div id="subplus"><span>还剩</span><span v-text="count" style="color: red;font-weight:bold;">0</span><span>张门票</span> </div>
                <input type="text" class="join-ipt" v-model="cell" id="mobile" placeholder="请输入手机号领取门票">
                <input type="text" class="join-code" v-model="verify" id="code" maxlength="4" placeholder="验证码">  <a href="javascript:void(0)" class="verify-img"><img class="verify" src="verify" alt="点击刷新"/></a>
                <div id="joinBtn" @click="joinFn()"></div>
            </div>
        </div>
        <script src="/active/Public/js/jquery.min.js"></script>
        <script src="/active/Public/js/vue.min.js"></script>
        <script src="/active/Application/Home/View//Public/static/js/app.js"></script>
        <script src="/active/Application/Home/View//Public/static/js/active/racing.js"></script>
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
                    title: "500张成都GT赛车大赛门票免领取", // 分享标题
                    link: "http://www.sda88.cn/Active/racing.html", // 分享链接
                    imgUrl: "http://7xnjsm.com1.z0.glb.clouddn.com/racing.jpg", // 分享图标
                    success: function () {
                        // 用户确认分享后执行的回调函数

                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }});
                wx.onMenuShareAppMessage({
                    title: "500张成都GT赛车大赛门票免领取", // 分享标题
                    desc: "19台炫酷跑车、百名火辣性感车模及惊心动魄的车速竞技等您来观战", // 分享描述
                    link: "http://www.sda88.cn/Active/racing.html", // 分享链接
                    imgUrl: "http://7xnjsm.com1.z0.glb.clouddn.com/racing.jpg", // 分享图标
                    type: '', // 分享类型,music、video或link，不填默认为link
                    dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                    success: function () {
                        // 用户确认分享后执行的回调函数

                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }
                });
                wx.onMenuShareQQ({
                    title: "500张成都GT赛车大赛门票免领取", // 分享标题
                    desc: "19台炫酷跑车、百名火辣性感车模及惊心动魄的车速竞技等您来观战", // 分享描述
                    link: "http://www.sda88.cn/Active/racing.html", // 分享链接
                    imgUrl: "http://7xnjsm.com1.z0.glb.clouddn.com/racing.jpg", // 分享图标
                    success: function () {
                        // 用户确认分享后执行的回调函数

                    },
                    cancel: function () {
                        // 用户取消分享后执行的回调函数
                    }
                });
            });
        </script>
    </body>
</html>