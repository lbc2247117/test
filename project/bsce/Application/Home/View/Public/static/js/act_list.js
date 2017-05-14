var
        GET_DATA_API = 'selectActivity',
        GET_LIVE_API = 'getActLive',
        ADD_ACTIVITY = '../Score/addClick',
        LIKE_API = '',
        LAIA = new Vue({
            el: '#laiaCnr',
            data: {
                dataArr: [],
                dataArr2: [],
                evenArr: [],
                oddArr: [],
                viewIdx: 0,
                viewVisible: !1,
                first: 1,
                page: 1,
                size: 3,
                actLiveArr: [],
                lon: SEARCH_LON,
                lat: SEARCH_LAT,
                startX: '', startY: '', endX: '', endY: ''
            },
            methods: {
                init: function () {
                    this.getLiveData();
                    this.getData();
                },
                getLiveData: function () {
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
                    });
                },
                getData: function () {
                    var _dt = {
                        lon: this.lon,
                        lat: this.lat,
                        page: this.page,
                        size: this.size,
                    };
                    $.post(GET_DATA_API, _dt, function (rst, status) {
                        if (status == 'success') {
                            if (typeof rst != 'object')
                                rst = $.parseJSON(rst);
                            if (rst.status == '1') {
                                if (LAIA.first) {
                                    LAIA.listData(rst.data.data);
                                } else {
                                    if (rst.data.data == null || rst.data.data == '') {
                                        LAIA.page = LAIA.page - 1;
                                        LAIA.dataArr2 = null;
                                    }
                                    LAIA.pushlistData(rst.data.data);
                                }
                            } else {
                                BASE.showAlert(rst.msg);
                            }
                        } else {
                            BASE.showConfirm('网络有点儿问题');
                        }
                    });
                },
                jumptoActLive: function (idx) {
                    var _dt = this.actLiveArr[idx];
                    var id = _dt['id'];
//                    localStorage.setItem('cover', _dt['cover']);
//                    _dt['vstate'] == 2 ? localStorage.setItem('videoPath', _dt['bakPath']) : localStorage.setItem('videoPath', _dt['firstDevice']);
                    window.location.href = 'liveplay.html?id=' + id + '&lon=' + this.lon + '&lat=' + this.lat;
                },
                jumptoact: function (id) {
                    var _dt = {
                        id:id,
                        type: 1
                    };
                    $.post(ADD_ACTIVITY, _dt, function (rst, status) {

                    });
                    window.location.href = 'act.html?id=' + id + '&lon=' + this.lon + '&lat=' + this.lat;
                },
                listData: function (data) {
                    LAIA.dataArr = data;
                    setTimeout(function () {
                        LAIA_SCROLL.refresh();
                    }, 200);
                },
                pushlistData: function (data) {
                    if (data != null && data != '') {
                        for (var i = 0; i < data.length; i++) {
                            LAIA.dataArr.push(data[i]);
                        }
                    }
                    setTimeout(function () {
                        LAIA_SCROLL.refresh();
                    }, 200);
                },
                rcmdNo: function (idx) {
                    return idx < 9 ? ('0' + String(idx + 1) + '.') : (String(idx + 1) + '.');
                },
                bgImg: function (url) {
                    return url ? ('url(' + url + ')') : '';
                }
            }
        });
PULL_DOWN_FN = function () {
    clearTimeout(PULL_DOWN_TIMER);
    PULL_DOWN_TIMER = setTimeout(function () {
        LAIA.first = 1;
        LAIA.page = 1;
        LAIA.dataArr2 = [];
        LAIA.init();
    }, 1000);
};
PULL_UP_FN = function () {
    clearTimeout(PULL_UP_TIMER);
    PULL_UP_TIMER = setTimeout(function () {
        LAIA.first = 0;
        LAIA.page = LAIA.page + 1;
        LAIA.init();
    }, 1000);
};
LOADED_FN = function () {
    PULL_DOWN_EL = document.getElementById('pullDown');
    PULL_DOWN_OFFSET = PULL_DOWN_EL.offsetHeight;
    PULL_UP_EL = document.getElementById('pullUp');
    PULL_UP_OFFSET = PULL_UP_EL.offsetHeight;

    LAIA_SCROLL = new iScroll('laiaCnr', {
        useTransition: false,
        topOffset: PULL_DOWN_OFFSET,
        onRefresh: function () {
            if (PULL_DOWN_EL.className.match('loading')) {
                PULL_DOWN_EL.className = '';
                PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '下拉刷新';
                PULL_UP_EL.querySelector('.pull-up-label').innerHTML = '上拉加载更多';
            } else if (PULL_UP_EL.className.match('loading')) {
                PULL_UP_EL.className = '';
                if (LAIA.dataArr2 == null) {
                    PULL_UP_EL.querySelector('.pull-up-label').innerHTML = '没有更多内容了';
                } else {
                    PULL_UP_EL.querySelector('.pull-up-label').innerHTML = '上拉加载更多';
                }
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
            } else if (this.y < (this.maxScrollY - 5) && !PULL_UP_EL.className.match('flip')) {
                PULL_UP_EL.className = 'flip';
                PULL_UP_EL.querySelector('.pull-up-label').innerHTML = '释放更新';
                this.maxScrollY = this.maxScrollY;
            } else if (this.y > (this.maxScrollY + 5) && PULL_UP_EL.className.match('flip')) {
                PULL_UP_EL.className = '';
                PULL_UP_EL.querySelector('.pull-up-label').innerHTML = '上拉加载更多';
                this.maxScrollY = PULL_UP_OFFSET;
            }
        },
        onScrollEnd: function () {
            if (PULL_DOWN_EL.className.match('flip')) {
                PULL_DOWN_EL.className = 'loading';
                PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '加载中';
                PULL_DOWN_FN();
            } else if (PULL_UP_EL.className.match('flip')) {
                PULL_UP_EL.className = 'loading';
                PULL_UP_EL.querySelector('.pull-up-label').innerHTML = '加载中';
                PULL_UP_FN();
            }
        }
    });
    setTimeout(function () {
        document.getElementById('laiaCnr').style.left = '0';
    }, 800);
};
$(function () {
    LOADED_FN();
    LAIA.init();
    if (!SEARCH_INAPP) {
        $('#navBack').off('click');
        $('#navBack').on('click', function () {
            var _search = '?lon=' + SEARCH_LON + '&lat=' + SEARCH_LAT;
            window.location.href = 'sce.html' + _search;
        });
    }
});