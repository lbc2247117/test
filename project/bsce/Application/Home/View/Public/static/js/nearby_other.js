var
        GET_DATA_API = '../Near/querySeller',
        LAIA = new Vue({
            el: '#laiaCnr',
            data: {
                id: '',
                cate: URL_PARAM('cate'),
                searchKey: '',
                dataArr: [],
                pullData: [],
                first: 1,
                lon: SEARCH_LON,
                lat: SEARCH_LAT,
                page: 1,
                size: 20,
                type: URL_PARAM('cate')
            },
            methods: {
                navi: function (cate) {
                    window.location.href = 'nearby_other?cate=' + cate + '&lon=' + this.lon + '&lat=' + this.lat;
                },
                init: function () {
                    this.getData();
                },
                getData: function () {
                    var _dt = {
                        lon: this.lon,
                        lat: this.lat,
                        page: this.page,
                        size: this.size,
                        type: this.type
                    };
                    $.post(GET_DATA_API, _dt, function (rst, status) {
                        if (status == 'success') {
                            if (typeof rst != 'object')
                                rst = $.parseJSON(rst);
                            if (rst.status == '1') {
                                if (LAIA.first) {
                                    LAIA.listData(rst.data.data);
                                }
                                else {
                                    LAIA.pullData = rst.data.data;
                                    if (rst.data.data == '' || rst.data.data == null) {
                                        LAIA.pullData = null;
                                        LAIA.page = LAIA.page - 1;
                                    }
                                    LAIA.pushlistData(rst.data.data);
                                }
                            }
                        } else {
                            BASE.showConfirm('网络有点儿问题');
                        }
                    });
                },
                jumptopointlist: function () {
                    window.location.href = 'nearby_place.html?lon=' + this.lon + '&lat=' + this.lat;
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
                search: function () {
                    if (this.searchKey) {
                        window.location.href = 'nearby_search.html?lon=' + this.lon + '&lat=' + this.lat + '&key=' + encodeURIComponent(this.searchKey);
                    } else {
                        BASE.showAlert('请输入要搜索的关键词');
                    }
                },
                bgImg: function (url) {
                    return url ? ('url(' + url + ')') : '';
                },
                go: function (idx) {
                    var _dt = LAIA.dataArr[idx];
                    window.location.href = 'biz.html?id=' + _dt['id'] + '&lon=' + this.lon + '&lat=' + this.lat + '&cate=' + URL_PARAM('cate');
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
                    PULL_UP_EL.querySelector('.pull-up-label').innerHTML = '没有更多内容';
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
  if (!SEARCH_INAPP) {
    $('#navBack').off('click');
    $('#navBack').on('click', function() {
      var _search = '?lon=' + SEARCH_LON + '&lat=' + SEARCH_LAT;
      window.location.href = 'sce.html' + _search;
    });
  }
  LOADED_FN();
  LAIA.init();
});