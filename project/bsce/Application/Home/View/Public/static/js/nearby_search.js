var
        GET_DATA_API = '../near/searchNear',
        LAIA = new Vue({
            el: '#laiaCnr',
            data: {
                searchKey: URL_PARAM('key'),
                lon: SEARCH_LON,
                lat: SEARCH_LAT,
                placeArr: [],
                foodArr: [],
                hotelArr: [],
                shopArr: [],
                funArr: []
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getData: function () {
                    var _dt = {
                        searchName: this.searchKey,
                        lon: this.lon,
                        lat: this.lat
                    };
                    $.post(GET_DATA_API, _dt, function (rst, status) {
                        if (status == 'success') {
                            if (typeof rst != 'object')
                                rst = $.parseJSON(rst);
                            if (rst.status == '1') {
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
                    this.placeArr = data.ScenicMapSpotVo;
                    this.foodArr = data.food;
                    this.hotelArr = data.hotel;
                    this.shopArr = data.shopping;
                    this.funArr = data.amusement;
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
                clear: function () {
                    this.searchKey = '';
                    //window.history.go(-1);
                },
                bgImg: function (url) {
                    return url ? ('url(' + url + ')') : '';
                },
                toBiz: function (id) {
                    window.location.href = 'biz.html?id=' + id + '&lat=' + URL_PARAM('lat') + '&lon=' + URL_PARAM('lon');
                },
                toPlace: function (lat, lon ,id) {
                    window.location.href = 'place.html?lat=' + LAIA.lat + '&lon=' + LAIA.lon+'&maplon='+lon+'&maplat='+lat+'&id='+id;
                },
                toMore: function (type) {
                    window.location.href = 'nearby_search_more.html?lon=' + this.lon + '&lat=' + this.lat + '&type=' + type + '&key=' + encodeURIComponent(this.searchKey);
                },
                viewMore: function (cate) {
                    switch (cate) {
                        case 'place':
                            this.toMore('4');
                            break;
                        case 'fun':
                            this.toMore('3');
                            break;
                        case 'hotel':
                            this.toMore('1');
                            break;
                        case 'food':
                            this.toMore('0');
                            break;
                        case 'shop':
                            this.toMore('2');
                            break;
                    }
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
                PULL_UP_EL.querySelector('.pull-up-label').innerHTML = '上拉加载更多';
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
    }, 800);
};
$(function () {
    LOADED_FN();
    LAIA.init();
});