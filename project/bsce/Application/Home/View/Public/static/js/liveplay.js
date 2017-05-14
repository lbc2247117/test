var
        GET_DATA_API = 'getLiveById',
        SEND_MSG_API = 'sendMobileMsg',
        LAIA = new Vue({
            el: '#laiaCnr',
            data: {
                dataObj: {},
                id: SEARCH_ID,
                lon: SEARCH_LON,
                lat: SEARCH_LAT,
                trailerVisible: !1,
                liveTimeVisible: !1,
                msgVisible: !1,
                mobile: '',
                startTime: '',
                day: 0,
                hour: 0,
                minute: 0,
                second: 0,
                isLock: !1,
                title:'',
            },
            methods: {
                init: function () {
                    this.getData();
                    this.repost();
                },
                getData: function () {
                    var _dt = {
                        id: this.id,
                        lon: this.lon,
                        lat: this.lat,
                    };
                    $.post(GET_DATA_API, _dt, function (rst, status) {
                        if (status == 'success') {
                            if (typeof rst != 'object')
                                rst = $.parseJSON(rst);
                            if (rst.status == '1' && rst.data) {
                                LAIA.listData(rst.data);
                            } else {
                                BASE.showAlert(rst.msg);
                            }
                        } else {
                            BASE.showConfirm('网络有点儿问题');
                        }
                    });
                },
                listData: function (data) {
                    LAIA.dataObj = data.list[0];
                    LAIA.title = data.list[0].name;
                    LAIA.startTime = LAIA.dataObj.startTime;
                    if (LAIA.dataObj.vstate == -1) {
                        LAIA.trailerVisible = !0;
                        LAIA.countDown();
                    }
                    else {
                        LAIA.liveTimeVisible = !0;
                    }
                    setTimeout(function () {
                        LAIA_SCROLL.refresh();
                    }, 200);

                },
                bgImg: function (url) {
                    return url ? ('url(' + url + ')') : '';
                },
                showSendMsg: function () {
                    window.location.href = 'live_callme.html?id=' + this.id + '&lon=' + this.lon + '&lat=' + this.lat+'&title='+this.title;
                },
                hideSendMsg: function () {
                    this.msgVisible = !1;
                },
                jumptoApp: function () {
                    window.location.href = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.undao.traveltesti';
                },
                sendMsg: function () {
                    if (LAIA.isLock)
                        return;
                    var mobile = this.mobile;
                    var _dt = {
                        lon: this.lon,
                        lat: this.lat,
                        mobile: mobile,
                        id: this.id,
                    };
                    LAIA.isLock = !0;
                    $.post(SEND_MSG_API, _dt, function (rst) {
                        LAIA.isLock = !1;
                        rst = JSON.parse(rst);
                        if (rst.status != '1') {
                            BASE.showAlert('您已预约过该活动，请勿重复预约~');
                            return false;
                        }
                        BASE.showAlert(rst.msg);
                        LAIA.hideSendMsg();
                    });
                },
                repost: function () {
                    setTimeout(function () {
                        var _dt = {
                            id: LAIA.id,
                            lon: LAIA.lon,
                            lat: LAIA.lat,
                        };
                        $.post(GET_DATA_API, _dt, function (rst) {
                            rst = $.parseJSON(rst);
                            if (rst.status == '1' && rst.data.list[0].vstate != -1) {
                                LAIA.liveTimeVisible = !0;
                                LAIA.trailerVisible = !1;
                                return;
                            }
                            LAIA.repost();
                        });
                    }, 10000);
                },
                countDown: function () {
                    var _now = new Date();
                    var _startTime = new Date(this.startTime);
                    var _leftTime = _startTime.getTime() - _now.getTime();
                    var _leftsecond = parseInt(_leftTime / 1000);
                    this.day = Math.floor(_leftsecond / (60 * 60 * 24));
                    this.hour = Math.floor((_leftsecond - this.day * 24 * 60 * 60) / 3600);
                    this.minute = Math.floor((_leftsecond - this.day * 24 * 60 * 60 - this.hour * 3600) / 60);
                    this.second = Math.floor(_leftsecond - this.day * 24 * 60 * 60 - this.hour * 3600 - this.minute * 60);
                    if (this.day.toString().length == 1)
                        this.day = '0' + this.day;
                    if (this.hour.toString().length == 1)
                        this.hour = '0' + this.hour;
                    if (this.minute.toString().length == 1)
                        this.minute = '0' + this.minute;
                    if (this.second.toString().length == 1)
                        this.second = '0' + this.second;
                    if (_leftsecond <= 0) {
                        this.day = '00';
                        this.hour = '00';
                        this.minute = '00';
                        this.second = '00';
                        return;
                    }
                    setTimeout(function () {
                        LAIA.countDown();
                    }, 1000);
                },
            }
        });

PULL_DOWN_FN = function () {
    clearTimeout(PULL_DOWN_TIMER);
    PULL_DOWN_TIMER = setTimeout(function () {
        LAIA.init();
        LAIA_SCROLL.refresh();
    }, 1000);
};
LOADED_FN = function () {
    PULL_DOWN_EL = document.getElementById('pullDown');
    PULL_DOWN_OFFSET = PULL_DOWN_EL.offsetHeight;

    LAIA_SCROLL = new iScroll('laiaCnr', {
        useTransition: false,
        topOffset: PULL_DOWN_OFFSET,
        onRefresh: function () {
            if (PULL_DOWN_EL.className.match('loading')) {
                PULL_DOWN_EL.className = '';
                PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '下拉刷新';
            }
        },
        onScrollMove: function () {
            if (this.y > 5 && !PULL_DOWN_EL.className.match('flip')) {
                PULL_DOWN_EL.className = 'flip';
                PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '释放更新';
                this.minScrollY = 0;
            } else if (this.y < 5 && PULL_DOWN_EL.className.match('flip')) {
                PULL_DOWN_EL.className = '';
                PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '下拉刷新';
                this.minScrollY = -PULL_DOWN_OFFSET;
            }
        },
        onScrollEnd: function () {
            if (PULL_DOWN_EL.className.match('flip')) {
                PULL_DOWN_EL.className = 'loading';
                PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '加载中';
                PULL_DOWN_FN();
            }
        }
    });
    setTimeout(function () {
        document.getElementById('laiaCnr').style.left = '0';
        LAIA_SCROLL.refresh();
    }, 800);
};
$(function () {
    LOADED_FN();
    LAIA.init();
    if (!SEARCH_INAPP) {
        $('#navBack').off('click');
        $('#navBack').on('click', function () {
            var _search = '?lon=' + SEARCH_LON + '&lat=' + SEARCH_LAT;
            window.location.href = 'act_list.html' + _search;
        });
    }
});