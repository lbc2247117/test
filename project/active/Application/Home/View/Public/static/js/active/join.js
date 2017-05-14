var getUrlParam = function (name) {
    var
            reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)'),
            r = window.location.search.substr(1).match(reg);
    return !!r ? unescape(r[2]) : '';
};
var
        OPENID = getUrlParam('openid'),
        API = 'entryActive',
        GET_INFO = 'getUserInfo',
        CNT = new Vue({
            el: '#laiaCnr',
            data: {
                alert: {
                    show: !1,
                    msg: ''
                },
                confirm: {
                    show: !1,
                    msg: ''
                },
                name: '',
                cell: '',
                headimgurl: '',
            },
            methods: {
                getData: function () {
                    if (!OPENID)
                        return false;
                    var _dt = {
                        openid: OPENID
                    }
                    $.post(GET_INFO, _dt, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            CNT.showAlert(rst.msg);
                            return false;
                        }
                        CNT.name = rst.data['nickname'];
                        $('#cover').css("background-image", "url(" + rst.data['headimgurl'] + ")");
                    });
                },
                init: function () {
                    this.getData();
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
                showConfirm: function (msg) {
                    this.confirm.msg = msg;
                    this.confirm.show = !0;
                },
                hideConfirm: function () {
                    this.confirm.msg = '';
                    this.confirm.show = !1;
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
                checkName: function (str) {
                    if (str.length > 0) {
                        return !0;
                    } else {
                        this.showAlert('没有获取到用户信息，请从公众号进入 ~');
                    }
                },
                joinFn: function () {
                    if (!OPENID) {
                        this.showAlert('没有获取到用户信息，请从公众号进入 ~');
                        return false;
                    }
                    if (!this.checkName($('#username').val()) || !this.checkCell($('#mobile').val())) {
                        return false;
                    }
                    var _dt = {
                        mobile: CNT.cell,
                        openid: OPENID
                    }
                    $.post(API, _dt, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            CNT.showAlert(rst.msg);
                            return false;
                        }
                        CNT.showConfirm(rst.msg);
                    });
                },
                confirmFn: function () {
                    window.location.href = 'index.html?openid=' + OPENID;
                },
                cancelFn: function () {
                    this.hideConfirm();
                }
            }
        });
CNT.init();