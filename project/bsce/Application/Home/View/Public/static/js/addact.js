var
        JOIN_ACTIVITY = '../Active/joinActivity',
        GET_DATA_API = 'activityInfo',
        LAIA = new Vue({
            el: '#laiaCnr',
            data: {
                dataObj: {},
                id: SEARCH_ID,
                lon: SEARCH_LON,
                lat: SEARCH_LAT,
                titleName:'',
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
                    LAIA.titleName = data.titleName;
                },
                join: function () {
                    var _dt = {
                        lon: this.lon,
                        lat: this.lat,
                        name: $('#name').val(),
                        activityID: this.id,
                        tel: $('#mobile').val(),
                        verify: $('#verify').val(),
                    };
                    $.post(JOIN_ACTIVITY, _dt, function (rst, status) {
                        if (status == 'success') {
                            if (typeof rst != 'object')
                                rst = $.parseJSON(rst);
                            if (rst.status == '1') {
                                BASE.showAlert('您已成功报名'+LAIA.titleName+'!期待活动现场与你相见');
                                setTimeout(function () {
                                    window.location.href = 'act.html?id=' + LAIA.id + '&lon=' + LAIA.lon + '&lat=' + LAIA.lat;
                                }, 3000);
                            } else if(rst.status == '-1'){
                                BASE.showAlert('您报名'+LAIA.titleName+'失败，点击重试~');
                            }else{
                                BASE.showAlert(rst.msg);
                            }
                        } else {
                            BASE.showConfirm('网络有点儿问题');
                        }
                    });

                },
                bgImg: function (url) {
                    return url ? ('url(' + url + ')') : '';
                },
                gotoCurLocation: function (e) {
                    setTimeout(function () {
                        $("html,body").animate({scrollTop: $(e).offset().top}, 500);
                    }, 500);
                },
            },
        });
LAIA.init();