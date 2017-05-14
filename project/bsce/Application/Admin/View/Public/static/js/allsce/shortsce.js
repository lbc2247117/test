var
        GET_DATA_API = 'selectAllcse',
        EDIT_DATA_API = 'saveAllsce',
        ADD_FESTIVAL_API = 'addFestival',
        EDIT_FESTIVAL_API = 'editFestival',
        DEL_FESTIVAL_API = 'delFestival',
        CNT = new Vue({
            el: '#wrapper',
            data: {
                dataObj: {},
                tableData: [],
                editCnrVisible: !1,
                isAdd: !1,
                isLock: !1,
                noneUrl: '/bask/sd',
                editVideoObj: {},
                videoVisible: !1,
                imgVisible: !1,
            },
            methods: {
                init: function () {
                    this.initFestival();
                    this.getData();
                },
                initFestival: function () {
                    this.editVideoObj = {
                        id: '',
                        name: '',
                        remark: '',
                        videoPic: '',
                        videoUrl: '',
                    };
                    $('#festivalCoverFile').val('');
                    $('#festivalVideoFile').val('');
                    $('#festivalCover').attr('src', '');
                    $('#festivalVideoView').attr('src', '');
                },
                getData: function () {
                    var _dt = {
                    };
                    $('#loading').show();
                    $.post(GET_DATA_API, _dt, function (rst, status) {
                        $('#loading').hide();
                        if (status == 'success') {
                            if (typeof rst != 'object')
                                rst = $.parseJSON(rst);
                            if (rst.status == '1') {
                                CNT.listData(rst.data[0]);
                            } else {
                                BASE.showAlert('接受数据失败', 'warning');
                            }
                        } else {
                            BASE.showAlert('网络有点问题', 'warning');
                        }
                    });
                },
                listData: function (data) {
                    this.dataObj = data;
                    this.dataObj.sceRemark == '-1' ? this.dataObj.sceRemark = '' : '';
                    this.dataObj.otherremark == '-1' ? this.dataObj.otherremark = '' : '';
                    this.dataObj.specRemark == '-1' ? this.dataObj.specRemark = '' : '';
                    this.tableData = data.ScenicSpotProgramVo;
                    BASE.initTextCount();
                },
                saveSceRemark: function () {
                    $('#editform').ajaxSubmit({
                        url: EDIT_DATA_API,
                        beforeSubmit: function () {
                            $('#loading').show();
                        },
                        success: function (rst) {
                            $('#loading').hide();
                            rst = JSON.parse(rst);
                            if (rst.status != '1') {
                                BASE.showAlert('操作失败', 'warning');
                            }
                            else {
                                BASE.showAlert('修改成功');
                                $('#btnSubmit').off('click');
                                CNT.getData();
                            }
                        },
                    });
                },
                addFestival: function () {
                    this.editCnrVisible = !0;
                    this.isAdd = !0;
                    $('#festivalCover').attr('src', CNT.noneUrl);
                    this.videoVisible = !1;
                    this.imgVisible = !0;
                    BASE.initTextCount();
                },
                delFestival: function (idx) {
                    if (this.isLock)
                        return;
                    var _dt = this.tableData[idx];
                    var id = _dt['id'];
                    var _confirm = BASE.showConfirm('确定要删除该条节目吗?', function () {
                        this.isLock = !0;
                        $('#loading').show();
                        $.post(DEL_FESTIVAL_API, {'id': id}, function (rst) {
                            $('#loading').hide();
                            CNT.isLock = !1;
                            rst = JSON.parse(rst);
                            if (rst.status != '1') {
                                BASE.showAlert(rst.msg, 'warning');
                                return false;
                            }
                            BASE.showAlert(rst.msg);
                            CNT.getData();
                        });
                    });
                },
                showEdit: function (idx) {
                    var _dt = this.tableData[idx];
                    this.editVideoObj.name = _dt.name;
                    this.editVideoObj.remark = _dt.remark;
                    this.editVideoObj.videoPic = _dt.videoPic;
                    this.editVideoObj.videoUrl = _dt.videoUrl;
                    this.editVideoObj.id = _dt.id;
                    this.editCnrVisible = !0;
                    this.isAdd = !1;
                    this.videoVisible = !0;
                    this.imgVisible = !1;
                    BASE.initTextCount();
                },
                hideEdit: function () {
                    this.editCnrVisible = !1;
                    this.initFestival();
                },
                festivalCoverBtn: function () {
                    $('#festivalCoverFile').click();
                },
                uploadSceRemarkVideoBtn: function () {
                    $('#uploadSceRemarkVideo').click();
                },
                uploadSceRemarkCoverBtn: function () {
                    $('#uploadSceRemarkCover').click();
                },
                uploadGuidePicBth : function(){
                    $('#uploadGuidePic').click();
                },
                uploadSpecRemarkVideoBtn: function () {
                    $('#uploadSpecRemarkVideo').click();
                },
                uploadSpecRemarkCoverBtn: function () {
                    $('#uploadSpecRemarkCover').click();
                },
                saveFestival: function () {
                    if (this.isLock)
                        return;
                    this.isLock = !0;
                    $('#festivalEditForm').ajaxSubmit({
                        url: CNT.isAdd ? ADD_FESTIVAL_API : EDIT_FESTIVAL_API,
                        beforeSubmit: function () {
                            $('#loading').show();
                        },
                        success: function (rst) {
                            CNT.isLock = !1;
                            $('#loading').hide();
                            rst = JSON.parse(rst);
                            if (rst.status != '1') {
                                BASE.showAlert(rst.msg, 'warning');
                                return false;
                            }
                            BASE.showAlert(rst.msg);
                            CNT.hideEdit();
                            CNT.getData();
                        },
                    });
                },
            }
        });
CNT.init();
$(function () {
    var festivalCover = new uploadPreview({
        UpBtn: "festivalCoverFile",
        ImgShow: "festivalCover",
        ImgType: ["gif", "jpeg", "jpg", "bmp", "png"],
        ErrMsg: "选择文件错误,图片类型必须是(gif,jpeg,jpg,bmp,png)中的一种",
        callback: function () {
            if ($('#festivalCoverFile')[0].files[0].size > (1 * 1024 * 1024)) {
                BASE.showAlert('上传的图片大小不能超过1M');
                $('#festivalCoverFile').val('');
                $('#festivalCover').attr('src', CNT.noneUrl);
            }
        },
    });
    var festivalVideo = new uploadPreview({
        UpBtn: "festivalVideoFile",
        ImgShow: "festivalVideoView",
        ImgType: ["mp4"],
        ErrMsg: "选择文件错误,现仅支持MP4格式的视频",
        callback: function () {
            CNT.videoVisible = !0;
            CNT.imgVisible = !1;
            if ($('#festivalVideoFile')[0].files[0].size > (200 * 1024 * 1024)) {
                BASE.showAlert('上传的视频大小不能超过200M');
                $('#festivalVideoFile').val('');
                $('#festivalVideoView')[0].src = CNT.noneUrl;
            }
            setTimeout(function () {
                $('#festivalVideoView')[0].play();
            }, 500);
        }
    });
    var sceRemarkVideo = new uploadPreview({
        UpBtn: 'uploadSceRemarkVideo',
        ImgShow: 'sceRemarkVideoView',
        ImgType: ['mp4'],
        ErrMsg: '选择文件错误,现仅支持MP4格式的视频',
        callback: function () {
            if ($('#uploadSceRemarkVideo')[0].files[0].size > (200 * 1024 * 1024)) {
                BASE.showAlert('上传的视频大小不能超过200M');
                $('#uploadSceRemarkVideo').val('');
                $('#sceRemarkVideoView')[0].src = CNT.noneUrl;
            }
            setTimeout(function () {
                $('#sceRemarkVideoView')[0].play();
            }, 500);
        },
    });
    var sceRemarkCover = new uploadPreview({
        UpBtn: "uploadSceRemarkCover",
        ImgShow: "sceRemarkCoverView",
        ImgType: ["gif", "jpeg", "jpg", "bmp", "png"],
        ErrMsg: "选择文件错误,图片类型必须是(gif,jpeg,jpg,bmp,png)中的一种",
        callback: function () {
            if ($('#uploadSceRemarkCover')[0].files[0].size > (1 * 1024 * 1024)) {
                BASE.showAlert('上传的图片大小不能超过1M');
                $('#uploadSceRemarkCover').val('');
                $('#sceRemarkCoverView').attr('src', CNT.noneUrl);
            }
        },
    });
    var guidePic= new uploadPreview({
        UpBtn : "uploadGuidePic",
        ImgShow : "sceGuidePicView",
        ImgType: ["gif", "jpeg", "jpg", "bmp", "png"],
        ErrMsg: "选择文件错误,图片类型必须是(gif,jpeg,jpg,bmp,png)中的一种",
        callback: function () {
            if ($('#uploadGuidePic')[0].files[0].size > (1 * 1024 * 1024)) {
                BASE.showAlert('上传的图片大小不能超过1M');
                $('#uploadGuidePic').val('');
                $('#sceGuidePicView').attr('src', CNT.noneUrl);
            }
        },
    });
    var specRemarkVideo = new uploadPreview({
        UpBtn: 'uploadSpecRemarkVideo',
        ImgShow: 'specRemarkVideoView',
        ImgType: ['mp4'],
        ErrMsg: '选择文件错误,现仅支持MP4格式的视频',
        callback: function () {
            if ($('#uploadSpecRemarkVideo')[0].files[0].size > (200 * 1024 * 1024)) {
                BASE.showAlert('上传的视频大小不能超过200M');
                $('#uploadSpecRemarkVideo').val('');
                $('#specRemarkVideoView')[0].src = CNT.noneUrl;
            }
            setTimeout(function () {
                $('#specRemarkVideoView')[0].play();
            }, 500);
        },
    });
    var specRemarkCover = new uploadPreview({
        UpBtn: "uploadSpecRemarkCover",
        ImgShow: "specRemarkCoverView",
        ImgType: ["gif", "jpeg", "jpg", "bmp", "png"],
        ErrMsg: "选择文件错误,图片类型必须是(gif,jpeg,jpg,bmp,png)中的一种",
        callback: function () {
            if ($('#uploadSpecRemarkCover')[0].files[0].size > (1 * 1024 * 1024)) {
                BASE.showAlert('上传的图片大小不能超过1M');
                $('#uploadSpecRemarkCover').val('');
                $('#specRemarkCoverView').attr('src', CNT.noneUrl);
            }
        },
    });
    $('#upFestivalVideoBtn').click(function () {
        $('#festivalVideoFile').click();
    });
});
