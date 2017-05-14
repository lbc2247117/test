var
        GET_DATA_API = '../Live/selectPicture',
        LIKE_API = '',
        LAIA = new Vue({
            el: '#laiaCnr',
            data: {
                pullData: [],
                dataArr: [],
                evenArr: [],
                oddArr: [],
                viewIdx: 0,
                viewVisible: !1,
                first: 1,
                type: URL_PARAM('type'),
                page: 1,
                size: 20,
                lon: SEARCH_LON,
                lat: SEARCH_LAT,
                startX: '', startY: '', endX: '', endY: ''
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getData: function () {
                    var _dt = {
                        lon: this.lon,
                        lat: this.lat,
                        type: this.type,
                        page: this.page,
                        size: this.size
                    };
                    $.post(GET_DATA_API, _dt, function (rst, status) {
                        if (status == 'success') {
                            if (typeof rst != 'object')
                                rst = $.parseJSON(rst);
                            if (rst.status == '1') {
                                if (LAIA.first) {
                                    LAIA.dataArr =[];
                                    LAIA.listData(rst.data.list);
                                } else {
                                    LAIA.pullData = rst.data.list;
                                    if (rst.data.list == null || rst.data.list == '') {
                                        LAIA.pullData = null;
                                        LAIA.page = LAIA.page - 1;
                                    }
                                    LAIA.listData(rst.data.list);
                                }
                            }
                        } else {
                            BASE.showConfirm('网络有点儿问题');
                        }
                    });
                },
                listData: function (data) {
                    if (data != null && data != '') {
                        for (var i = 0; i < data.length; i++) {
                            data[i].idx = i + (LAIA.page - 1) * 20;
                            LAIA.dataArr.push(data[i]);
                            if (i % 2 == 0) {
                                LAIA.evenArr.push(data[i]);
                            }
                            else
                                LAIA.oddArr.push(data[i]);
                        }
                    }
                    setTimeout(function () {
                        LAIA_SCROLL.refresh();
                    }, 200);
                },
                view: function (idx) {
                    this.addClass(document.getElementsByClassName('sgl-view')[idx], 'active');
                    this.viewIdx = idx;
                    this.viewVisible = !0;
                },
                hideView: function () {
                    this.viewVisible = !1;
                },
                touchS: function (e) {
                    var
                            _touch = e.touches[0];
                    this.startX = _touch.pageX;
                },
                touchM: function (e) {
                    var
                            _touch = e.touches[0];
                    this.endX = _touch.pageX;
                },
                touchE: function () {
                    var _e = document.getElementsByClassName('sgl-view')[this.viewIdx];
                    if ((this.startX - this.endX) > 100) { //left
                        if (this.viewIdx < this.dataArr.length - 1) {
                            $(_e).animate(
                                    {
                                        left: '-101%'
                                    }, '', '',
                                    function () {
                                        $(_e).removeClass('active').removeClass('next').addClass('prev');
                                    }
                            );
                            $(_e).next().animate(
                                    {
                                        left: '0'
                                    }, '', '',
                                    function () {
                                        $(_e).next().removeClass('prev').removeClass('next').addClass('active');
                                    }
                            );
                            this.viewIdx++;
                        } else {
                            return !1;
                        }
                    }
                    if ((this.startX - this.endX) < -100) { //right
                        if (this.viewIdx >= 1) {
                            $(_e).animate(
                                    {
                                        left: '101%'
                                    }, '', '',
                                    function () {
                                        $(_e).removeClass('active').removeClass('prev').addClass('next');
                                    }
                            );
                            $(_e).prev().animate(
                                    {
                                        left: '0'
                                    }, '', '',
                                    function () {
                                        $(_e).prev().removeClass('prev').removeClass('next').addClass('active');
                                    }
                            );
                            this.viewIdx--;
                        } else {
                            return !1;
                        }
                    }
                },
                addClass: function (e, name) {
                    e.className += ' ' + name;
                },
                removeClass: function (e, name) {
                    if (e.className.match(new RegExp('(\\s|^)' + name))) {
                        e.className = e.className.replace(new RegExp('(\\s|^)' + name), '');
                    }
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
                if (LAIA.pullData == null) {
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
});