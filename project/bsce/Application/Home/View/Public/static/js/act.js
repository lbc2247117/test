var
        GET_DATA_API = 'activityInfo',
        JOIN_ACTIVITY = '../Active/joinActivity',
        LAIA = new Vue({
            el: '#laiaCnr',
            data: {
                dataObj: {},
                id: SEARCH_ID,
                lon: SEARCH_LON,
                lat: SEARCH_LAT,
                addVisible: !1,
                endVisible: !1,
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getData: function () {
                    var _dt = {
                        id: this.id,
                        lon: URL_PARAM('lon'),
                        lat: URL_PARAM('lat'),
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
                    LAIA.dataObj = data;
                    var _endTime = new Date(LAIA.dataObj.endTime);
                    var _now = new Date();
                    if (LAIA.dataObj.signUp && LAIA.dataObj.state == 0 && _endTime > _now)
                        this.addVisible = !0;
                    else if (_endTime < _now || LAIA.dataObj.state == 1)
                        this.endVisible = !0;
                    LAIA.dataObj.acTitle = LAIA.dataObj.acTitle.replace(/\n/ig, '<br/>');
                    LAIA.dataObj.acTitle = LAIA.dataObj.acTitle.replace(/\s/ig, '&nbsp');
                    LAIA.dataObj.name = LAIA.dataObj.name.replace(/\n/ig, '<br/>');
                    LAIA.dataObj.name = LAIA.dataObj.name.replace(/\s/ig, '&nbsp');
                    LAIA.dataObj.acRuke = LAIA.dataObj.acRuke.replace(/\n/ig, '<br/>');
                    LAIA.dataObj.acRuke = LAIA.dataObj.acRuke.replace(/\s/ig, '&nbsp');
                    setTimeout(function () {
                        LAIA_SCROLL.refresh();
                    }, 200);

                },
                bgImg: function (url) {
                    return url ? ('url(' + url + ')') : '';
                },
                addAct: function () {
                    window.location.href = 'addact.html?id=' + this.id + '&lon=' + this.lon + '&lat=' + this.lat;
                },
                backHome: function () {
                    window.location.href = 'sce.html?lon=' + this.lon + '&lat=' + this.lat;
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