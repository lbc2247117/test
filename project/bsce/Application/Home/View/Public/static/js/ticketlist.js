
var
    GET_DATA_API = '../Score/selectTicket',
    ADD_TICKET = '../Score/addTicket',
    LAIA = new Vue({
        el: '#scroller',
        data: {
            lon: SEARCH_LON,
            lat: SEARCH_LAT,
            dataArray:[],
            page:1,
            size: 4,
            first: 1,
            pullData:''
        },
        methods: {
            init: function () {
                this.getData();
            },
            getData: function () {
                var _dt = {
                    lon:LAIA.lon,
                    lat:LAIA.lat,
                    page:LAIA.page,
                    size:LAIA.size
                };
                $.post(GET_DATA_API, _dt, function (rst) {
                    if (typeof rst != 'object')
                        rst = $.parseJSON(rst);
                    if (rst.status == '1') {
                        if (LAIA.first) {
                            LAIA.dataArray=rst.data.data;
                            setTimeout(function () {
                                LAIA_SCROLL.refresh();
                            }, 200);
                        } else {
                            LAIA.pullData = rst.data.data;
                            if (rst.data.data == null || rst.data.data == '') {
                                LAIA.pullData = null;
                                LAIA.page = LAIA.page - 1;
                            }
                            LAIA.listData(rst.data.data);
                        }
                    } else {
                        BASE.showAlert(rst.msg);
                    }
                });
            },
            listData: function (data) {
                for (var i = 0; i < data.length; i++) {
                        LAIA.dataArray.push(data[i]);
                }
                setTimeout(function () {
                    LAIA_SCROLL.refresh();
                }, 200);
            },
            bgImg: function(url) {
                return url ? ('url(' + url + ')') : '';
            },
            toinfo :function(id){
                var _dt={
                    id :id,
                    type: 0
                };
                $.post(ADD_TICKET, _dt, function (rst, status) {

                });
                window.location.href = 'ticketinfo.html?lon=' + LAIA.lon + '&lat=' + LAIA.lat+'&id='+id;
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