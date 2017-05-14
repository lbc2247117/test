var
        GET_DATA_API = 'editScemap',
        EDIT_API = 'updateScemap',
        ADD_API = 'addScemap',
        CNT = new Vue({
            el: '#wrapper',
            data: {
                SceName: '',
                logoUrl: '',
                mapRemark: '',
                Radius: '',
                maplon: '',
                maplat: '',
                price:'',
                id: URL_PARAM('id'),
                mars: '',
                lon:URL_PARAM('lon'),
                lat:URL_PARAM('lat')
            },
            methods: {
                init: function () {
                    if (this.id)
                        this.mars = EDIT_API;
                    else {
                        $('.ishide').hide();
                        this.mars = ADD_API;
                    }

                    this.getData();
                },
                getData: function () {
                    var _dt = {
                        id: this.id
                    };
                    if (this.id) {
                        $('#loading').show();
                        $.post(GET_DATA_API, _dt, function (rst) {
                            $('#loading').hide();
                            rst = JSON.parse(rst);
                            if (rst.status != 1) {
                                BASE.showAlert('获取数据失败');
                                return false;
                            }
                            CNT.setData(rst.data);
                        });
                    }
                },
                saveEdit: function () {
                    //判断输入
                    if (STRING_LENGTH(TRIM(CNT.SceName)) == 0) {
                        BASE.showAlert('请输入景点名称');
                        return;
                    }
                    if (STRING_LENGTH(TRIM(CNT.SceName)) > 32) {
                        BASE.showAlert('景点名称不能大于32个字符');
                        return;
                    }
                    if (STRING_LENGTH(TRIM(CNT.mapRemark)) > 240) {
                        BASE.showAlert('景点介绍不能大于240个字符');
                        return;
                    }
                    $('#editform').ajaxSubmit({
                        beforeSubmit: function () {
                            $('#loading').show();
                        },
                        success: function (rst) {
                            $('#loading').hide();
                            rst = JSON.parse(rst);
                            if (rst.status != 1) {
                                BASE.showAlert(rst.msg, 'warning');
                                return false;
                            }
                            BASE.showAlert(rst.msg, 'success');
                            if (!CNT.id) {
                                window.location.href = 'scespot.html';
                            }
                        }
                    });
                },
                setData: function (rst) {
                    CNT.SceName = rst['name'];
                    CNT.logoUrl = rst['pageFm'];
                    CNT.mapRemark = rst['sceRemark'];
                    CNT.Radius = rst['raduis'];
                    CNT.maplon = rst['maplon'];
                    CNT.maplat = rst['maplat'];
                    CNT.price = rst['price'];
                    localStorage.setItem('lonlat', CNT.maplon + ',' + CNT.maplat);
                    BASE.initTextCount();
                },
                gotobase: function () {
                    window.location.href = 'base.html?id=' + CNT.id;
                },
                gotovideo: function () {
                    window.location.href = 'video.html?id=' + CNT.id;
                },
                gotolive: function () {
                    window.location.href = 'live.html?id=' + CNT.id;
                },
                gotoimg: function () {
                    window.location.href = 'image.html?id=' + CNT.id;
                },
                gotocoordinate: function(){
                    window.location.href = 'coordinate.html?id=' + CNT.id;
                }
            }
        });

CNT.init();
$(function () {
    $('#scemap').addClass('open');
    $('#scemap').parents('.dropdown').addClass('open');
    function showInfo(e) {
        if (!CNT.id) {
            $('#wk').val(e.point.lng);
            $('#wk1').val(e.point.lat);
        }
    }

    var picView = new uploadPreview({
        UpBtn: 'pic',
        ImgShow: 'picView',
        ImgType: ['jpg', 'png'],
        ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种'
    });
    map.addEventListener("click", showInfo);
});


