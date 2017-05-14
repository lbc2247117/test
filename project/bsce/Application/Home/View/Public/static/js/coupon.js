var
        GET_DATA_API = '../Near/freeId',
        GET_BIZ_DATA_API = '../Near/SellerId',
        LAIA = new Vue({
            el: '#laiaCnr',
            data: {
                fromShake: !!URL_PARAM('fromshake'),
                id: SEARCH_ID,
                searchKey: '',
                dataObj: [],
                bizDataObj: []
            },
            methods: {
                navi: function (cate) {
                    window.location.href = 'nearby_other?cate=' + cate + '&id=' + this.id;
                },
                init: function () {
                    this.getData();
                },
                getData: function () {
                    var _dt = {
                        id: LAIA.id,
                        lat: URL_PARAM('lat'),
                        lon: URL_PARAM('lon'),
                    };
                    $.post(GET_DATA_API, _dt, function (rst, status) {
                        if (status == 'success') {
                            if (typeof rst != 'object')
                                rst = $.parseJSON(rst);
                            if (rst.status == '1' && rst.data.result) {
                                LAIA.listData(rst.data.result);
                            } else {
                                BASE.showAlert(rst.msg);
                            }
                        } else {
                            BASE.showConfirm('网络有点儿问题');
                        }
                    });
                },
                getBizData: function (id) {
                    var _dt = {
                        id: id,
                        lat: URL_PARAM('lat'),
                        lon: URL_PARAM('lon'),
                    };
                    $.post(GET_BIZ_DATA_API, _dt, function (rst, status) {
                        if (status == 'success') {
                            if (typeof rst != 'object')
                                rst = $.parseJSON(rst);
                            if (rst.status == '1' && rst.data) {
                                LAIA.listBizData(rst.data);
                            } else {
                                BASE.showAlert(rst.msg);
                            }
                        } else {
                            BASE.showConfirm('网络有点儿问题');
                        }
                    });
                },
                listData: function (data) {
                    if (this.fromShake) {
                        this.getBizData(data.commercialTenantID);
                    }
                    this.dataObj = data;
                    setTimeout(LOADED_FN, 200);
                },
                listBizData: function (data) {
                    this.bizDataObj = data;
                    setTimeout(LOADED_FN, 200);
                },
                bgImg: function (url) {
                    return url ? ('url(' + url + ')') : '';
                },
                toBiz: function () {
                    window.location.href = 'biz.html?id=' + this.bizDataObj.id + '&lon=' + this.bizDataObj.lon + '&lat=' + this.bizDataObj.lat;
                }
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
    }, 800);
};
$(function () {
    LOADED_FN();
    LAIA.init();
});