var
        GET_DATA_API = 'getSurplusCount',
        SEND_API = 'sendSMSAPI',
        CNT = new Vue({
            el: '#laiaCnr',
            data: {
                count: 0,
                alert: {
                    show: !1,
                    msg: ''
                },
                ruleCnrVisible: !1,
                qrCnrVisible: !1,
                qrMsg: ''
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getData: function () {
                    $.post(GET_DATA_API, {}, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status == 1) {
                            CNT.count = rst.data;
                        }
                    });
                },
                checkCell: function (str) {
                    var
                            _reg = /^(13[0-9]{9})|(14[0-9]{9})|(15[0-9]{9})|(17[0-9]{9})|(18[0-9]{9})$/;
                    if (_reg.test(str)) {
                        return !0;
                    } else {
                        this.showAlert('手机号好像填错啦 ~');
                        return !1;
                    }
                },
                checkCode: function (str) {
                    if (str.length != 4) {
                        this.showAlert('验证码输入错误 ~');
                        return !1;
                    }
                    return !0;
                },
                down: function () {
                    window.location.href = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.undao.traveltesti';
                },
                joinFn: function () {

                    if (!this.checkCell($('#mobile').val()) || !this.checkCode($('#code').val())) {
                        return false;
                    }
                    var _dt = {
                        mobile: $('#mobile').val(),
                        verify: $('#code').val()
                    }
                    $.post(SEND_API, _dt, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            CNT.showAlert(rst.msg);
                            return false;
                        }
                        CNT.showAlert(rst.msg);
                    });
                },
                showQR: function (msg) {
                    this.qrMsg = msg;
                    this.qrCnrVisible = !0;
                },
                hideQR: function () {
                    this.qrMsg = '';
                    this.qrCnrVisible = !1;
                },
                showRule: function () {
                    this.ruleCnrVisible = !0;
                },
                hideRule: function () {
                    this.ruleCnrVisible = !1;
                },
                showAlert: function (msg) {
                    this.alert.msg = msg;
                    this.alert.show = !0;
                    setTimeout(function () {
                        CNT.hideAlert();
                    }, 3000);
                },
                hideAlert: function () {
                    this.alert.msg = '';
                    this.alert.show = !1;
                },
            }
        });
CNT.init();
$(function () {
    $(".verify").click(function () {
        var src = "verify";
        var random = Math.floor(Math.random() * (1000 + 1));
        $(this).attr("src", src + "?random=" + random);
    });
});
