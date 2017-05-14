var
        GET_DATA_API = '../Recommend/queryRoad',
        LIKE_API = '',
        LAIA = new Vue({
            el: '#laiaCnr',
            data: {
                dataArr: [],
                evenArr: [],
                oddArr: [],
                viewIdx: 0,
                viewVisible: !1,
                first: 1,
                page: 1,
                size: 3,
                pulldata: [],
                lon: SEARCH_LON,
                lat: SEARCH_LAT,
                startX: '', startY: '', endX: '', endY: ''
            },
            methods: {
                init: function () {
                    this.getData();//4normal
                    //this.listData();//4test
                },
                getData: function () {
                    var _dt = {
                        lon: this.lon,
                        lat: this.lat,
                        page: this.page,
                        size: this.size
                    };
                    $.post(GET_DATA_API, _dt, function (rst, status) {
                        if (status == 'success') {
                            if (typeof rst != 'object')
                                rst = $.parseJSON(rst);
                            if (rst.status == '1' && rst.data.data) {
                                if (LAIA.first) {
                                    LAIA.listData(rst.data.data);
                                } else {
                                    LAIA.pulldata = rst.data.data;
                                    if (!rst.data.data || (typeof rst.data.data == 'object' && rst.length < 1)) {
                                        LAIA.page = LAIA.page - 1;
                                    }
                                    LAIA.pushlistData(rst.data.data);
                                }
                                setTimeout(function () {
                                    LAIA_SCROLL.refresh();
                                }, 200);
                            } else {
                                BASE.showAlert(rst.msg);
                            }
                        } else {
                            BASE.showConfirm('网络有点儿问题');
                        }
                    });
                },
                listData: function (data) {

                    LAIA.dataArr = data;
                    setTimeout(function () {
                        LAIA_SCROLL.refresh();
                    }, 200);
                },
                jumptorcmd: function (idx) {
                    var _dt = LAIA.dataArr[idx];
                    var id = _dt['id'];
                    window.location.href = 'rcmd?lon=' + this.lon + '&lat=' + this.lat + '&id=' + id;
                },
                pushlistData: function (data) {
                    if (data == null)
                        return;
                    for (var i = 0; i < data.length; i++) {
                        if (typeof LAIA.dataArr == 'object')
                            LAIA.dataArr.push(data[i]);
                    }
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
        LAIA.init();
    }, 1000);
};
PULL_UP_FN = function () {
    clearTimeout(PULL_UP_TIMER);
    PULL_UP_TIMER = setTimeout(function () {
        LAIA.first = 0;
        LAIA.page = LAIA.page + 1;
        LAIA.getData();
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
                if (LAIA.pulldata.length < 1) {
                    PULL_UP_EL.querySelector('.pull-up-label').innerHTML = '已经没有更多内容了';
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