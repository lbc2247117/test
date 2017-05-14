var getUrlParam = function (name) {
    var
            reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)'),
            r = window.location.search.substr(1).match(reg);
    return !!r ? unescape(r[2]) : '';
};
var
        OPENID = getUrlParam('openid'),
        GET_DATA_API = 'getData',
        POLL_API = 'poll',
        CHECK = 'clickEntry',
        CNT = new Vue({
            el: '#content',
            data: {
                showGuide: !1,
                iosGuide: !1,
                adrGuide: !1,
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
                init: function () {
                    this.getList();
                },
                getData: function (api, dt, cb) {
                    if (api && dt && cb) {
                        $.post(api, dt, function (rst, status) {
                            if (status == 'success') {
                                if (typeof rst != 'object')
                                    rst = $.parseJSON(rst);
                                cb(rst);
                            } else {
                                CNT.showAlert('网络出了点儿小问题~ <br>|@_@|~ <br>一会儿再试吧~');
                            }
                        });
                    } else {
                        return !1;
                    }
                },
                getList: function (cb) {
                    var
                            _dt = {
                                keyWord: this.searchKey,
                                page: this.curPage,
                                sort: this.rankType
                            };
                    this.getData(GET_DATA_API, _dt, function (rst) {
                        if (rst.data.count > 0) {
                            CNT.totalPage = Math.ceil(rst.data.count / 10);
                            CNT.listData(rst.data.info);
                            if (cb)
                                cb();
                        } else {
                            CNT.showAlert('没有搜索到相关内容~');
                        }
                    });
                },
                regetList: function (type) {
                    this.rankType = type;
                    this.getList();
                },
                listData: function (data) {
                    this.tableData = data;
                },
                searchFn: function () {
                    this.curPage = 1;
                    this.getList();
                },
                pollFn: function (id) {
                    if (!OPENID) {
                        CNT.showQR('在公众号内才能参赛哦~快来关注吧~');
                        return !1;
                    }
                    var _dt = {
                        openid: OPENID,
                        id: id
                    }
                    $.post(POLL_API, _dt, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status == 2) {
                            CNT.showQR('在公众号内才能参赛哦~快来关注吧~');
                            return !1;
                        } else if (rst.status != 1) {
                            CNT.showAlert(rst.msg);
                            return !1;
                        }
                        CNT.getList();
                        CNT.showAlert(rst.msg);

                    });

                },
                pageNav: function (type) {
                    var
                            _i = type == 'pre' ? -1 : 1;
                    this.curPage += _i;
                    this.getList(function () {
                        window.location.href = "#searchCnr";
                    });
                },
                toJoin: function () {
                    if (!OPENID) {
                        CNT.showQR('在公众号内才能参赛哦~快来关注吧~');
                        return !1;
                    }
                    window.location.href = 'join.html?openid=' + OPENID;
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
CNT.init();
$(function () {
    $('#keyWord').bind('keypress', function (event) {
        if (event.keyCode == "13") {
            CNT.curPage = 1;
            CNT.getList();
        }
    });
});