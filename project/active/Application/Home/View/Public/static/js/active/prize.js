var getUrlParam = function (name) {
    var
            reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)'),
            r = window.location.search.substr(1).match(reg);
    return !!r ? unescape(r[2]) : '';
};
var
        OPENID = getUrlParam('openid'),
        GET_PRIZE = 'lottery',
        SET_MOBILE = 'setMobile',
        CNT = new Vue({
            el: '#content',
            data: {
                islock: 0,
                showGuide: !1,
                iosGuide: !1,
                adrGuide: !1,
                prizeLocation: [{id: 1, a: 67}, {id: 2, a: 283}, {id: 3, a: 144}, {id: 4, a: 218}, {id: 5, a: 0}],
                startPoint: 0,
                alert: {
                    show: !1,
                    msg: ''
                },
                tableData: '',
                curPage: 1,
                totalPage: 1,
                showPageNav: !0,
                rankType: 'count',
                searchKey: '',
                baseUrl: '',
                ruleCnrVisible: !1,
                qrCnrVisible: !1,
                qrMsg: ''
            },
            methods: {
                activeFn: function () {
                    if (CNT.islock == 1)
                        return;
                    if (!OPENID) {
                        CNT.showQR('在公众号内才能参赛哦~快来关注吧~');
                        return !1;
                    }
                    var _dt = {
                        openid: OPENID,
                    }
                    CNT.islock = 1;
                    $.post(GET_PRIZE, _dt, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status == 2) {
                            CNT.showQR('在公众号内才能参赛哦~快来关注吧~');
                            CNT.islock = 0;
                            return !1;
                        } else if (rst.status != 1) {
                            CNT.showAlert(rst.msg);
                            CNT.islock = 0;
                            return !1;
                        }

                        var rand = Math.ceil(Math.random() * 2) + 5;
                        var during_time = 6;
                        CNT.startPoint = CNT.startPoint - rand * 360 - CNT.prizeLocation[rst.data - 1].a - CNT.startPoint % 360;
                        $('#rollCnt').css({
                            'transform': 'rotate(' + CNT.startPoint + 'deg)',
                            '-ms-transform': 'rotate(' + CNT.startPoint + 'deg)',
                            '-webkit-transform': 'rotate(' + CNT.startPoint + 'deg)',
                            '-moz-transform': 'rotate(' + CNT.startPoint + 'deg)',
                            '-o-transform': 'rotate(' + CNT.startPoint + 'deg)',
                            'transition': 'transform ease-out ' + during_time + 's',
                            '-moz-transition': '-moz-transform ease-out ' + during_time + 's',
                            '-webkit-transition': '-webkit-transform ease-out ' + during_time + 's',
                            '-o-transition': '-o-transform ease-out ' + during_time + 's'
                        });
                        setTimeout(function () {
                            CNT.showAlert(rst.msg);
                            CNT.islock = 0;
                        }, during_time * 1000);

                    });
                },
                toTop: function () {
                    $('body').animate({scrollTop: '0px'}, 500);
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