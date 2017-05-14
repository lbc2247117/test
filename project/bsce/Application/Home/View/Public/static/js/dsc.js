var
        GET_DATA_API = 'allsceRemark',
        LAIA = new Vue({
            el: '#laiaCnr',
            data: {
                id: SEARCH_ID,
                searchKey: '',
                dataObj: {},
                lastLocation: -1,
                elementArr: [],
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
                        id: this.id,
                        lat: URL_PARAM('lat'),
                        lon: URL_PARAM('lon'),
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
                    this.dataObj = data;
                    this.dataObj.sceRemark == '-1' ? this.dataObj.sceRemark = '' : '';
                    this.dataObj.otherremark == '-1' ? this.dataObj.otherremark = '' : '';
                    this.dataObj.specRemark == '-1' ? this.dataObj.specRemark = '' : '';
                    this.dataObj.carefulContent == '-1' ? this.dataObj.carefulContent = '' : '';
                    this.dataObj.sceRemark = this.dataObj.sceRemark.replace(/\n/ig, "<br/>");
                    this.dataObj.sceRemark = this.dataObj.sceRemark.replace(/\s/ig, "&nbsp;");
                    this.dataObj.otherremark = this.dataObj.otherremark.replace(/\n/ig, "<br/>");
                    this.dataObj.otherremark = this.dataObj.otherremark.replace(/\s/ig, "&nbsp;");
                    this.dataObj.specRemark = this.dataObj.specRemark.replace(/\n/ig, "<br/>");
                    this.dataObj.specRemark = this.dataObj.specRemark.replace(/\s/ig, "&nbsp;");
                    this.dataObj.carefulContent = this.dataObj.carefulContent.replace(/\n/ig, "<br/>");
                    this.dataObj.carefulContent = this.dataObj.carefulContent.replace(/\s/ig, "&nbsp;");
                    this.elementArr.push('sceRemark');
                    this.elementArr.push('otherremark');
                    this.elementArr.push('specRemark');
                    this.elementArr.push('festival');
                    if (this.dataObj.ScenicSpotProgramVo != null && typeof (this.dataObj.ScenicSpotProgramVo) == 'object' && this.dataObj.ScenicSpotProgramVo.length > 0) {
                        for (var i = 0; i < this.dataObj.ScenicSpotProgramVo.length; i++) {
                            this.elementArr.push(this.dataObj.ScenicSpotProgramVo[i]['id']);
                        }
                    }
                    this.elementArr.push('carefulContent');
                },
                bgImg: function (url) {
                    return url ? ('url(' + url + ')') : '';
                },
                gotoLocation: function (id, e) {
                    $("html,body").animate({scrollTop: $("#" + id).offset().top - 40}, 1000);
                    $('.list-unstyled div').removeClass('active');
                    $(e).addClass('active');
                    this.lastLocation = $("#" + id).offset().top - 40;
                },
                showList: function () {
                    $('#listCnr').show();
                    var _hight = $('body').scrollTop();
                    if (this.lastLocation == _hight)
                        return;
                    $('.list-unstyled div').removeClass('active');
                    for (var i = 0; i < this.elementArr.length; i++) {
                        if (_hight < $('#' + this.elementArr[i]).offset().top - 140) {
                            $('#menu-' + this.elementArr[i - 1]).addClass('active');
                            break;
                        }
                    }
                },
                hideList: function () {
                    $('#listCnr').hide();
                }
            }
        });

//PULL_DOWN_FN = function () {
//    clearTimeout(PULL_DOWN_TIMER);
//    PULL_DOWN_TIMER = setTimeout(function () {
//        LAIA.init();
//        LAIA_SCROLL.refresh();
//    }, 1000);
//};
//LOADED_FN = function () {
//    PULL_DOWN_EL = document.getElementById('pullDown');
//    PULL_DOWN_OFFSET = PULL_DOWN_EL.offsetHeight;
//
//    LAIA_SCROLL = new iScroll('laiaCnr', {
//        useTransition: false,
//        topOffset: PULL_DOWN_OFFSET,
//        onRefresh: function () {
//            if (PULL_DOWN_EL.className.match('loading')) {
//                PULL_DOWN_EL.className = '';
//                PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '下拉刷新';
//            }
//        },
//        onScrollMove: function () {
//            if (this.y > 5 && !PULL_DOWN_EL.className.match('flip')) {
//                PULL_DOWN_EL.className = 'flip';
//                PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '释放更新';
//                this.minScrollY = 0;
//            } else if (this.y < 5 && PULL_DOWN_EL.className.match('flip')) {
//                PULL_DOWN_EL.className = '';
//                PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '下拉刷新';
//                this.minScrollY = -PULL_DOWN_OFFSET;
//            }
//        },
//        onScrollEnd: function () {
//            if (PULL_DOWN_EL.className.match('flip')) {
//                PULL_DOWN_EL.className = 'loading';
//                PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '加载中';
//                PULL_DOWN_FN();
//            }
//        }
//    });
//    setTimeout(function () {
//        document.getElementById('laiaCnr').style.left = '0';
//    }, 800);
//};
$(function () {
    LAIA.init();
    // LOADED_FN();
    $('#headerCnr').each(function () {
        new RTP.PinchZoom($(this), {});
    })
});