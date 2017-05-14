var
    JOIN_ACTIVITY = 'sendMobileMsg',
    LAIA = new Vue({
        el: '#laiaCnr',
        data: {
            dataObj: {},
            id: SEARCH_ID,
            lon: SEARCH_LON,
            lat: SEARCH_LAT,
            addVisible: !1,
            endVisible: !1,
            title:SEARCH_TITLE
        },
        methods: {
            init: function () {

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
            join: function () {
                var _dt = {
                    lon: this.lon,
                    lat: this.lat,
                    id: this.id,
                    mobile: $('#mobile').val(),
                    verify: $('#verify').val(),
                };
                $.post(JOIN_ACTIVITY, _dt, function (rst, status) {
                    if (status == 'success') {
                        if (typeof rst != 'object')
                            rst = $.parseJSON(rst);
                        if (rst.status == '1') {
                            BASE.showAlert('您已成功报名'+LAIA.title+'！高清直播看起来，评论互动玩起来，大家一起来吐槽！');
                            setTimeout(function () {
                                window.location.href = 'liveplay.html?id=' + LAIA.id + '&lon=' + LAIA.lon + '&lat=' + LAIA.lat;
                            }, 3000);
                        } else if(rst.status == '-1') {
                            BASE.showAlert('您报名'+LAIA.title+'失败，点击重试。');
                        }else{
                            BASE.showAlert(rst.msg);
                        }
                    } else {
                        BASE.showConfirm('网络有点儿问题');
                    }
                });

            },
            toback:function(){
                window.location.href = 'liveplay.html?id=' + LAIA.id + '&lon=' + LAIA.lon + '&lat=' + LAIA.lat;
            },
            gotoCurLocation: function (e) {
                setTimeout(function () {
                    $("html,body").animate({scrollTop: $(e).offset().top}, 500);
                }, 500);
            },

        }
    });
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