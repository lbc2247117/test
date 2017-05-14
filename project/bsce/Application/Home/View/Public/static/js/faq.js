
if (!localStorage.mac)
{
    localStorage.idx='';
    localStorage.visitorState=1;
    localStorage.mac = parseInt(10000 * Math.random()) + '.' + new Date().getTime();
}else{
    localStorage.visitorState=2;
}
var
        GET_DATA_API = '../Question/selectQusetion',
        LIKE_API = '../Question/clickZambia',
        LAIA = new Vue({
            el: '#laiaCnr',
            data: {
                pullData: [],
                first: 1,
                searchKey: '',
                sortByTime: !0,
                page: 1,
                size: 4,
                anser: 1,
                lon: SEARCH_LON,
                lat: SEARCH_LAT,
                dataArr: [],
                mac: localStorage.mac
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getData: function () {
                    if (LAIA.searchKey) {
                        LAIA.page = 1;
                    }
                    var _dt = {
                        sortType: LAIA.sortByTime ? 1 : 2,
                        serchName: LAIA.searchKey,
                        lon: LAIA.lon,
                        lat: LAIA.lat,
                        anser: LAIA.anser,
                        page: LAIA.page,
                        size: LAIA.size
                    };
                    $.post(GET_DATA_API, _dt, function (rst, status) {
                        if (status == 'success') {
                            if (typeof rst != 'object')
                                rst = $.parseJSON(rst);
                            if (rst.status == '1') {
                                if (LAIA.searchKey) {
                                    LAIA.listData(rst.data.list.data);
                                } else {
                                    if (LAIA.first) {
                                        LAIA.listData(rst.data.list.data);
                                    } else {
                                        LAIA.pullData = rst.data.list.data;
                                        if (rst.data.list.data == '' || rst.data.list.data == []) {
                                            LAIA.pullData = null;
                                            LAIA.page = LAIA.page - 1;
                                        }
                                        LAIA.pushlistData(rst.data.list.data);
                                    }
                                }
                            } else {
                                BASE.showAlert(rst.msg);
                            }
                        } else {
                            BASE.showConfirm('网络有点儿问题');
                        }
                    });
                },
                ask: function (id) {
                    window.location.href = 'ask.html?id=' + this.mac + '&lon=' + this.lon + '&lat=' + this.lat;
                },
                listData: function (data) {
                    if(localStorage.visitorState==1){
                    }else{
                        if(localStorage.idx  ){
                            for(var i=0;i<data.length;i++){
                                if((localStorage.idx).indexOf(data[i].id) != -1){
                                    data[i].state=1;
                                }
                            }
                        }
                    }
                    this.dataArr = data;
                    setTimeout(function () {
                        LAIA_SCROLL.refresh()
                    }, 200);
                },
                pushlistData: function (data) {
                    if(localStorage.visitorState == 1) {

                    }else{
                        for(var i=0;i<data.length;i++){
                            if(localStorage.idx.indexOf(data[i].id) != -1){
                                data[i].state=1;
                            }
                        }
                    }
                    if (data != null & data != '') {
                        for (var i = 0; i < data.length; i++) {
                            LAIA.dataArr.push(data[i]);
                        }
                    }
                    setTimeout(function () {
                        LAIA_SCROLL.refresh();
                    }, 200);
                },
                search: function () {
                    if (this.searchKey) {
                        this.getData();
                    } else {
                        BASE.showAlert('请输入要搜索的关键词');
                    }
                },
                sort: function (by) {
                    this.sortByTime = by == 'time' ? !0 : !1;
                    LAIA.page = 1;
                    LAIA.dataArr = [];
                    this.getData();
                },
                toggle: function (e) {
                    if (e.currentTarget.className.indexOf('active') > -1) {
                        BASE.removeClass(e, 'active');
                    } else {
                        BASE.addClass(e, 'active');
                        setTimeout(function () {
                            LAIA_SCROLL.refresh()
                        }, 200);
                    }
                },
                like: function (idx) {
                    $.post(LIKE_API, {
                        id: this.dataArr[idx].id,
                        mac: this.mac,
                        lon: URL_PARAM('lon'),
                        lat: URL_PARAM('lat'),
                    }, function (rst, status) {
                        if (status == 'success') {
                            if (typeof rst != 'object')
                                rst = $.parseJSON(rst);
                            if (rst.status == '1') {
                                LAIA.dataArr[idx].state = 1;
                                LAIA.dataArr[idx].vote = parseInt(LAIA.dataArr[idx].vote) + 1;
                                localStorage.idx=localStorage.idx+LAIA.dataArr[idx].id;
                            } else {
                                BASE.showAlert('重复点赞！');
                            }
                        } else {
                            BASE.showConfirm('网络有点儿问题');
                        }
                    });
                },
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
                    PULL_UP_EL.querySelector('.pull-up-label').innerHTML = '没有更多数据';
                }
                else {
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