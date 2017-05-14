var
        GET_DATA_API = 'SpecialInfo',
        EDIT_API = 'updateSpecial',
        ADD_API = 'addSpecial',
        UPDATE_COVER_API = 'updateSpecialcovertemp',
        DEL_IMG_API = 'delSpecialcover',
        ID = SEARCH_ID,
        PID = URL_PARAM('pid'),
        ISADD = !ID,
        CNT = new Vue({
            el: '#pageWrapper',
            data: {
                isAdd: ISADD,
                name: '',
                remark: '',
                imgList: [],
                idx: -1,
                isDel: !1,
            },
            methods: {
                init: function () {
                    if (!ISADD)
                        this.getData();
                },
                getData: function () {
                    var _dt = {
                        id: ID
                    };
                    $('#loading').show();
                    $.post(GET_DATA_API, _dt, function (rst) {
                        $('#loading').hide();
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg);
                            return false;
                        }
                        CNT.listData(rst.data);
                    });
                },
                listData: function (data) {
                    this.name = data.proName;
                    this.remark = data.proRemark;
                    this.imgList = data.pic;
                },
                back: function () {
                    this.gotoUnique();
                },
                save: function () {
                    var
                            _api = ISADD ? ADD_API : EDIT_API;
                    if ('' == TRIM(CNT.name)) {
                        BASE.showAlert('请填写名称');
                        return !1;
                    } else if ('' == TRIM(CNT.remark)) {
                        BASE.showAlert('请填写简介');
                        return !1;
                    }

                    $('#laiaForm').ajaxSubmit({
                        url: _api,
                        data: {
                            id: ISADD ? PID : ID,
                            proName: CNT.name,
                            proRemark: CNT.remark
                        },
                        success: function (rst, status) {
                            if (status == 'success') {
                                if (typeof rst != 'object')
                                    rst = JSON.parse(rst);
                                if (rst.status == '1') {
                                    BASE.showConfirm('保存成功啦~<br>将跳转到特色产品列表页', function () {
                                        CNT.back();
                                    });
                                } else {
                                    BASE.showAlert(rst.msg);
                                }
                            } else {
                                BASE.showAlert('网络有点儿问题~');
                            }
                        }
                    });
                },
                coverChange: function () {

                    $('#laiaForm').ajaxSubmit({
                        url: UPDATE_COVER_API,
                        data: {
                            id: ID,
                            path: CNT.idx == -1 ? '' : CNT.imgList[CNT.idx],
                        },
                        success: function (rst, status) {
                            if (status == 'success') {
                                if (typeof rst != 'object')
                                    rst = JSON.parse(rst);
                                if (rst.status == '1') {
                                    BASE.showAlert('修改成功！');
                                    CNT.getData();
                                } else {
                                    BASE.showAlert(rst.msg);
                                }
                            } else {
                                BASE.showAlert('网络有点儿问题~');
                            }
                        }
                    });
                },
                delImg: function (idx) {
                    CNT.isDel = !0;
                    var _dt = {
                        id: ID,
                        path: CNT.imgList[idx],
                    };
                    $.post(DEL_IMG_API, _dt, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != '1') {
                            BASE.showAlert(rst.msg);
                            return;
                        }
                        BASE.showAlert('删除成功');
                        CNT.getData();
                    });
                },
                gotoShortBus: function () {
                    window.location.href = 'shortbus.html?id=' + PID;
                },
                gotoBiz: function () {
                    window.location.href = 'bizlist.html?id=' + PID;
                },
                gotoBusData: function () {
                    window.location.href = 'busdata.html?id=' + PID;
                },
                gotoBusiness: function () {
                    window.location.href = 'base.html?id=' + PID;
                },
                gotoUnique: function () {
                    window.location.href = 'unique.html?id=' + PID;
                },
                showUpLoad: function (idx) {
                    if (this.isDel) {
                        this.isDel = !1;
                        return;
                    }
                    this.idx = idx;
                    document.getElementById('coverIpt').click();
                }
            },
        });
$(function () {
    CNT.init();
    $('#business').addClass('open');
    $('#business').parents('.dropdown').addClass('open');
    var
            resetCover = function (id) {
                $('#cover' + id).css({
                    backgroundImage: 'url(' + $('#coverView' + id).attr('src') + ')',
                    backgroundSize: 'cover',
                    border: '1px solid #eee'
                });
            },
            coverI = new uploadPreview({
                UpBtn: 'coverIptI',
                ImgShow: 'coverViewI',
                ImgType: ['jpg', 'png'],
                ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种',
                callback: function () {
                    resetCover('I');
                }
            }),
            coverII = new uploadPreview({
                UpBtn: 'coverIptII',
                ImgShow: 'coverViewII',
                ImgType: ['jpg', 'png'],
                ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种',
                callback: function () {
                    resetCover('II');
                }
            }),
            coverIII = new uploadPreview({
                UpBtn: 'coverIptIII',
                ImgShow: 'coverViewIII',
                ImgType: ['jpg', 'png'],
                ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种',
                callback: function () {
                    resetCover('III');
                }
            });
});