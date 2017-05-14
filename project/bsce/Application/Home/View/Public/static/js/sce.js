var
        GET_DATA_API = 'homePage',
        GET_LIVE_API = 'getHomeLive',
        ADD_ACTIVITY = '../Score/addClick',
        ADD_TICKET = '../Score/addTicket',
        DOWN_API = 'downQr',
        LAIA = new Vue({
            el: '#laiaCnr',
            data: {
                lon: '',
                lat: '',
                id: '',
                headObj: {
                    cover: '',
                    qr: '',
                    weather: '',
                    temp: '',
                    weathericon: '',
                    star: '',
                    score: ''
                },
                dscObj: {
                    id: '',
                    name: '',
                    dsc: '',
                    tags: [],
                    qr: '',
                    sceSynopsis: '',
                },
                actLiveArr: [],
                actArr: [],
                rcmdArr: [],
                liveArr: [],
                ticketArr: [],
                qrVisible: !1,
                actVisible: !1,
                rcmdVisible: !1,
                liveVisible: !1,
                ticketVisible: !1,
            },
            methods: {
                init: function () {
                    this.lon = SEARCH_LON;
                    this.lat = SEARCH_LAT;
                    this.getData();
                    this.getLive();
                },
                getLive: function () {
                    var _dt = {
                        lon: this.lon,
                        lat: this.lat,
                    };
                    $.post(GET_LIVE_API, _dt, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != '1') {
                            return false;
                        }
                        var data = rst.data.list;
                        if (data.length > 0) {
                            for (var i = 0; i < data.length; i++) {
                                for (var j = 0; j < data[i].device.length; j++) {
                                    if (data[i].mainID == data[i].device[j].channelID) {
                                        data[i]['firstDevice'] = data[i].device[j].PullHls;
                                    } else {
                                        data[i]['secondDevice'] = data[i].device[j].PullHls;
                                    }
                                }
                            }
                        }
                        LAIA.actLiveArr = data;
                        if (typeof (LAIA.actArr) == 'object' && LAIA.actArr != null && LAIA.actArr.length > 0)
                            LAIA.actVisible = !0;
                    });
                },
                getData: function () {
                    var _dt = {
                        lon: this.lon,
                        lat: this.lat
                    };
                    $.post(GET_DATA_API, _dt, function (rst, status) {
                        if (status != 'success') {
                            BASE.showConfirm('网络有点儿问题');
                            return false;
                        }
                        rst = $.parseJSON(rst);
                        if (rst.status != '1') {
                            BASE.showAlert(rst.msg);
                            return false;
                        }
                        LAIA.listData(rst.data);
                    });
                },
                jumptoActLive: function (idx) {
                    var _dt = this.actLiveArr[idx];
                    var id = _dt['id'];
//                    localStorage.setItem('cover', _dt['cover']);
//                    _dt['vstate'] == 2 ? localStorage.setItem('videoPath', _dt['bakPath']) : localStorage.setItem('videoPath', _dt['firstDevice']);
                    window.location.href = 'liveplay.html?id=' + id + '&lon=' + this.lon + '&lat=' + this.lat;
                },
                rcmdNo: function (idx) {
                    return idx < 9 ? ('0' + String(idx + 1) + '.') : (String(idx + 1) + '.');
                },
                jumptoweather: function () {
                    window.location.href = 'weather?lon=' + this.lon + '&lat=' + this.lat + '&bg=' + encodeURIComponent(this.headObj.cover);
                },
                jumptovideo: function () {
                    window.location.href = 'video_list?tag=-1&lon=' + this.lon + '&lat=' + this.lat + '&videoType=11';
                },
                jumptoalbum: function () {
                    window.location.href = 'album?lon=' + this.lon + '&lat=' + this.lat;
                },
                jumptonear: function () {
                    window.location.href = 'nearby_place?lon=' + this.lon + '&lat=' + this.lat;
                },
                jumptovod: function () {
                    window.location.href = 'video_list?lon=' + this.lon + '&lat=' + this.lat + '&videoType=1';
                },
                jumptorcmd: function () {
                    window.location.href = 'rcmd_list?lon=' + this.lon + '&lat=' + this.lat;
                },
                jumptofaq: function () {
                    window.location.href = 'faq?lon=' + this.lon + '&lat=' + this.lat;
                },
                jumptoactlist: function () {
                    window.location.href = 'act_list?lon=' + this.lon + '&lat=' + this.lat;
                },
                jumptoshake: function () {
                    window.location.href = 'shake?lon=' + this.lon + '&lat=' + this.lat;
                },
                jumptodsc: function () {
                    window.location.href = 'dsc?lon=' + this.lon + '&lat=' + this.lat + '&id=' + this.id;
                },
                jumptoscore: function () {
                    window.location.href = 'scescore.html?lon=' + this.lon + '&lat=' + this.lat;
                },
                listData: function (data) {
                    this.headObj.cover = data.backgroundpic;
                    this.headObj.qr = data.ewmUrl;
                    this.headObj.weather = data.weather.weather;
                    this.headObj.weathericon = data.weather.weatherPic;
                    this.headObj.temp = (!!data.weather.l_tmp ? data.weather.l_tmp : '0') + '-' + (!!data.weather.h_tmp ? data.weather.h_tmp : '0');
                    this.headObj.star = data.star;
                    this.headObj.score = data.star * 2;
                    this.dscObj.id = data.id;
                    this.dscObj.sceSynopsis = data.sceSynopsis;
                    this.dscObj.name = data.sceName;
                    if (data.sceType != '[]')
                        this.dscObj.tags = data.sceType;
                    this.dscObj.dsc = data.sceRemark;
                    this.actArr = data.ScenicSpotActivityVo;
                    this.liveArr = data.CameraVideoVo;
                    this.rcmdArr = data.ScenicSpotWayVo;
                    this.ticketArr = data.ScenicSpotTicketVo;
                    this.id = data.id;
                    if ((typeof (LAIA.actArr) == 'object' && LAIA.actArr != null && LAIA.actArr.length > 0) || (typeof (LAIA.actLiveArr) == 'object' && LAIA.actLiveArr != null && LAIA.actLiveArr.length > 0))
                        LAIA.actVisible = !0;
                    if (typeof (LAIA.liveArr) == 'object' && LAIA.liveArr != null && LAIA.liveArr.length > 0)
                        LAIA.liveVisible = !0;
                    if (typeof (LAIA.rcmdArr) == 'object' && LAIA.rcmdArr != null && LAIA.rcmdArr.length > 0)
                        LAIA.rcmdVisible = !0;
                    if (typeof (LAIA.ticketArr) == 'object' && LAIA.ticketArr != null && LAIA.ticketArr.length > 0)
                        LAIA.ticketVisible = !0;
                    setTimeout(function () {
                        LAIA_SCROLL.refresh();
                    }, 300);
                },
                bgImg: function (url) {
                    return url ? ('url(' + url + ')') : '';
                },
                weatherBg: function (weather) {
                    var
                            _d = new Date(),
                            _h = _d.getHours(),
                            _b = (_h < 18 && _h > 5) ? 'day/' : 'night/',
                            _u;
                    switch (weather) {
                        case 'sunny':
                            _u = '00.png';
                            break;
                        case 'windy':
                            _u = '01.png';
                            break;
                        case 'cloudy':
                            _u = '02.png';
                            break;
                        default:
                            _u = '00.png';
                    }
                    return _b + _u;
                },
                toinfo: function (id) {
                    var _dt={
                        id :id,
                        type: 0
                    };
                    $.post(ADD_TICKET, _dt, function (rst, status) {

                    });
                    window.location.href = 'ticketinfo.html?id=' + id + '&lon=' + this.lon + '&lat=' + this.lat;
                },
                jumptoticketlist: function () {
                    window.location.href = 'ticketlist.html?lon=' + this.lon + '&lat=' + this.lat;
                },
                mathHeight: function (percent, baseEl) {
                    baseEl = baseEl ? $(baseEl) : $(window);
                    return String(percent ? (baseEl.width() * percent) : 0) + 'px';
                },
                toAct: function (id) {
                    var _dt = {
                        id:id,
                        type: 1
                    };
                    $.post(ADD_ACTIVITY, _dt, function (rst, status) {

                    });
                    window.location.href = 'act.html?id=' + id + '&lon=' + this.lon + '&lat=' + this.lat;
                },
                toRcmd: function (id) {
                    window.location.href = 'rcmd.html?id=' + id + '&lon=' + this.lon + '&lat=' + this.lat;
                },
                toLive: function (videoPath) {
                    window.location.href = videoPath;
                },
                showQR: function () {
                    this.qrVisible = !0;
                },
                hideQR: function () {
                    this.qrVisible = !1;
                },
                downLoadQR: function (url) {
                    window.location.href = 'downQr.html?address=' + url;
                }
            }
        });
PULL_DOWN_FN = function () {
    clearTimeout(PULL_DOWN_TIMER);
    PULL_DOWN_TIMER = setTimeout(function () {
        LAIA.init();
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
            console.log(this.y);
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
                //alert(PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML);
                PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '加载中';
                PULL_DOWN_FN();
            }
        }
    });
    setTimeout(function () {
        document.getElementById('laiaCnr').style.left = '0';
    }, 800);
};
$(function () {
    if (!SEARCH_INAPP) {
        $('#headCover').css('top', '40px');
        $('#navBack').hide();
    }
    LOADED_FN();
    LAIA.init();
});