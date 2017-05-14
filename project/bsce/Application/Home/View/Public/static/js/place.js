var
        GET_DATA_API = '../Near/scemapInfo ',
        ADD_VIEW_API = 'addClick',
        LAIA = new Vue({
            el: '#laiaCnr',
            data: {
                id: SEARCH_ID,
                lon: SEARCH_MAPLON,
                lat: SEARCH_MAPLAT,
                page: 1,
                size: 3,
                headObj: {
                    MapMark: '',
                    SceName: '',
                    mapRemark: '',
                    price: '',
                },
                videoObj: []
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getData: function () {
                    var _dt = {
                        lon: LAIA.lon,
                        lat: LAIA.lat,
                        page: LAIA.page,
                        size: LAIA.size,
                        id: LAIA.id
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
                jumptoalbum: function () {
                    window.location.href = 'album?lon=' + this.lon + '&lat=' + this.lat + '&type=1';
                },
                playEvent: function (idx) {
                    var _dt = this.videoObj[idx];
                    var id = _dt['id'];
                    $.post(ADD_VIEW_API, {id: id}, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status == '1') {
                            LAIA.videoObj[idx]['watchNum'] = LAIA.videoObj[idx]['watchNum'] + 1;
                        }
                    });
                },
                listData: function (data) {
                    this.headObj.MapMark = data.map.pageFm;
                    this.headObj.mapRemark = data.map.sceRemark;
                    this.headObj.SceName = data.map.name;
                    this.headObj.price = data.map.price;
                    this.videoObj = data.video;
                },
                bgImg: function (url) {
                    return url ? ('url(' + url + ')') : '';
                },
                toggle: function (e) {
                    if (e.currentTarget.className.indexOf('active') > -1) {
                        BASE.removeClass(e, 'active');
                    } else {
                        BASE.addClass(e, 'active');
                    }
                }
            }
        });
$(function () {
    LAIA.init();
});