var
        GET_VIDEO_API = '../Allsce/selectVideo',
        ADD_VIDEO_API = '../Allsce/addVideo',
        EDIT_VIDEO_API = '../Allsce/editVideo',
        DEL_VIDEO_API = '../Allsce/delVideo',
        TO_TOP_API = '../Allsce/topVideo',
        GET_WAP_TAG = '../Allsce/selectTag',
        CNT = new Vue({
            el: '#wrapper',
            data: {
                wapTag: [],
                selWapTag: -1,
                wapTagVideo: [],
                selWapTagVideo: -1,
                keyword: '',
                id: URL_PARAM('id'),
                tableVideo: [],
                editCnrVisible: !1,
                pageAllData: [],
                pageShowData: [],
                checkedArr: [],
                showPageNav: !0,
                curPage: 1,
                pageCount: 1,
                isEdit: 0,
                isLock: 0,
                editVideoObj: {},
                videoVisible: !1,
                imgVisible: !1,
            },
            methods: {
                init: function () {
                    this.initVideoObj();
                    this.getWapTag();
                    this.getData();
                },
                initVideoObj: function () {
                    this.editVideoObj = {
                        id: '',
                        videoName: '',
                        videoPath: '',
                        videoPic: '',
                        SceLongitude: '',
                        SceLatitude: '',
                        hourLeng: '',
                        videoWidth: '',
                        videoHeight: '',
                        canData: ''
                    };
                },
                searchFn: function () {
                    this.curPage = 1;
                    this.getData();
                },
                getPointData: function () {
                    this.curPage = 1;
                    this.getData();
                },
                getWapTag: function () {
                    $.post(GET_WAP_TAG, {}, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != '1') {
                            BASE.showAlert(rst.msg, 'warning');
                            return false;
                        }
                        CNT.wapTagVideo = rst.data;
                        CNT.wapTag.push({
                            id: -1,
                            tagName: '全部标签',
                        });
                        for (var i = 0; i < rst.data.length; i++) {
                            CNT.wapTag.push(rst.data[i]);
                        }
                        CNT.selWapTag = -1;
                    });
                },
                getData: function (cb) {
                    var _lonlat = localStorage.getItem('lonlat');
                    var _dt = {
                        videoType: 11,
                        lonlat: _lonlat,
                        page: CNT.curPage,
                        size: 19,
                        serchName: CNT.keyword,
                        waptag: CNT.selWapTag == -1 ? 0 : CNT.selWapTag,
                    }
                    $('#loading').show();
                    $.post(GET_VIDEO_API, _dt, function (rst) {
                        $('#loading').hide();
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg, 'warning');
                            return false;
                        }
                        CNT.checkedArr = [];
                        CNT.tableVideo = rst.data.data;
                        if (CNT.curPage == 1)
                            CNT.getPageNum(rst.data.num);
                        if (cb) {
                            cb();
                        }
                    });
                },
                addvideo: function () {
                    $('#uploadVideo').val('');
                    $('#uploadCover').val('');
                    $('#coverView').attr('src', '');
                    $('#videoView')[0].src = '';
                    this.isEdit = 0;
                    this.editCnrVisible = !0;
                    this.selPointVideo = this.selPoint;
                    this.selWapTagVideo = this.selWapTag;
                    this.videoVisible = !1;
                    this.imgVisible = !0;
                },
                showEdit: function (idx) {
                    this.selWapTagVideo = -1;
                    this.editCnrVisible = !0;
                    $('#uploadVideo').val('');
                    $('#uploadCover').val('');
                    this.isEdit = 1;
                    this.videoVisible = !0;
                    this.imgVisible = !1;
                    var _dt = CNT.tableVideo[idx];
                    CNT.editVideoObj.id = _dt['id'];
                    CNT.editVideoObj.videoPath = _dt['videoPath'];
                    CNT.editVideoObj.videoName = _dt['videoName'];
                    CNT.editVideoObj.videoPic = _dt['videoPic'];
                    this.editCnrVisible = !0;
                    CNT.selWapTagVideo = _dt['wapTag'];
                    setTimeout(function () {
                        $("#videoView")[0].play();
                    }, 1500);

                },
                hideEdit: function () {
                    this.editCnrVisible = !1;
                    this.initVideoObj();

                },
                toTop: function () {
                    if (this.checkedArr.length < 1) {
                        BASE.showAlert('请先选择要置顶的条目！', 'warning');
                        return;
                    }
                    var
                            _dt = {
                                ids: []
                            };
                    this.checkedArr.forEach(function (idx) {
                        _dt.ids.push(CNT.tableVideo[idx].id);
                    });
                    var conf = confirm("您确定要置顶吗");
                    if (!conf)
                        return false;
                    $.post(TO_TOP_API, _dt, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg);
                        }
                        BASE.showAlert(rst.msg);
                        CNT.getData();
                    });

                },
                delVideo: function () {
                    if (this.checkedArr.length < 1) {
                        BASE.showAlert('请先选择要删除的条目！', 'warning');
                        return;
                    }
                    var
                            _dt = {
                                ids: []
                            };
                    this.checkedArr.forEach(function (idx) {
                        _dt.ids.push(CNT.tableVideo[idx].id);
                    });
                    var conf = confirm("您确定要删除吗");
                    if (!conf)
                        return false;
                    $('#loading').show();
                    $.post(DEL_VIDEO_API, _dt, function (rst) {
                        $('#loading').hide();
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg);
                        }
                        BASE.showAlert(rst.msg);
                        CNT.getData();
                        CNT.checkedArr = [];
                    });

                },
                cutImg: function () {
                    var video = $("#videoView")[0];
                    var canvas = document.createElement("canvas");
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                    $('#coverView').attr('src', canvas.toDataURL());
                    CNT.editVideoObj.canData = canvas.toDataURL();
                    $('#uploadCover').val('');
                },
                setTime: function () {
                    $("#videoWidth").val($("#videoView")[0].videoWidth);
                    $("#videoHeight").val($("#videoView")[0].videoHeight);
                    var duration = $("#videoView")[0].duration;
                    var min = Math.floor(duration / 60);
                    var second;
                    var time;
                    if (min < 1) {
                        second = Math.floor(duration % 60);
                        time = second + "''";
                    } else {
                        second = Math.floor(duration % 60);
                        time = min + "'" + second + "''";
                    }
                    $("#hourLeng").val(time);
                },
                getPageNum: function (num) {
                    this.pageAllData = [];
                    if (num > 20) {
                        for (var i = 0; i < Math.ceil(num / 20); i++) {
                            this.pageAllData.push({val: i, num: i + 1});
                        }
                        this.pageCount = this.pageAllData.length;
                        this.pageShowData = this.pageAllData.length > 5 ? [this.pageAllData[0], this.pageAllData[1], this.pageAllData[2], this.pageAllData[3], this.pageAllData[4]] : this.pageAllData;
                        this.showPageNav = !0;
                    } else {
                        this.showPageNav = !1;
                        this.pageCount = 1;
                    }
                },
                pageNav: function (num) {
                    var _cb = function (num) {
                        CNT.curPage = num;
                        var _arr = [];
                        if (CNT.curPage > 3) {
                            if (CNT.curPage < CNT.pageAllData.length - 2) {
                                var x = 0;
                                for (var i = CNT.curPage - 3; i < CNT.curPage + 2; i++) {
                                    _arr.push(CNT.pageAllData[i]);
                                    console.log(CNT.pageAllData[i].num + ' | ' + i + ' | ' + _arr[x].num);
                                    x++;
                                }
                            } else {
                                var count = 5;
                                if (CNT.pageAllData.length < 5)
                                    count = CNT.pageAllData.length;
                                for (var i = CNT.pageAllData.length - count; i < CNT.pageAllData.length; i++) {
                                    _arr.push(CNT.pageAllData[i]);
                                }
                            }
                        } else {
                            if (CNT.pageAllData.length < 6) {
                                for (var i = 0; i < CNT.pageAllData.length; i++) {
                                    _arr.push(CNT.pageAllData[i]);
                                }
                            }
                            else {
                                _arr = [CNT.pageAllData[0], CNT.pageAllData[1], CNT.pageAllData[2], CNT.pageAllData[3], CNT.pageAllData[4]];
                            }
                        }
                        CNT.pageShowData = _arr;
                    }
                    if (!!num && num <= this.pageAllData.length && num >= 0)
                        this.getData(_cb(num));
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
                gotocoordinate: function () {
                    window.location.href = 'coordinate.html?id=' + CNT.id;
                }
            }
        });
CNT.init();
$(function () {

    var rI = new uploadPreview({
        UpBtn: "uploadCover",
        ImgShow: "coverView",
        ImgType: ["gif", "jpeg", "jpg", "bmp", "png"],
        ErrMsg: "选择文件错误,图片类型必须是(gif,jpeg,jpg,bmp,png)中的一种",
    });
    var rII = new uploadPreview({
        UpBtn: "uploadVideo",
        ImgShow: "videoView",
        ImgType: ["mp4"],
        ErrMsg: "选择文件错误,现仅支持MP4格式的视频",
        callback: function () {
            CNT.videoVisible = !0;
            CNT.imgVisible = !1;
            setTimeout(function () {
                $("#videoView")[0].play();
            }, 500);
        }
    });
    $('#upVideoBtn').on('click', function () {
        $('#uploadVideo').click();
    });
    $('#upCoverBtn').on('click', function () {
        $('#uploadCover').click();
    });
    $('#uploadCover').on('change', function () {
        CNT.editVideoObj.canData = '';
    });
    $('#btnSubmit').on('click', function () {
        if (CNT.isLock == 1)
            return;
        if (TRIM(CNT.editVideoObj.videoName) == '') {
            BASE.showAlert('请填写视频标题', 'warning');
            return false;
        }
        if (CNT.isEdit == 0) {  //新增
            if (!$("input[name='video']").val()) {
                BASE.showAlert('请添加视频文件', 'warning');
                return false;
            }
            if (!$('#uploadCover').val() && !CNT.editVideoObj.canData) {
                BASE.showAlert('请选择视频封面', 'warning');
                return false;
            }
        }
        if (CNT.selWapTagVideo == -1) {
            BASE.showAlert('请选择视频标签', 'warning');
            return false;
        }
        if ($("input[name='video']").val()) {
            CNT.setTime();
        }
        CNT.isLock = 1;
        $('#form').ajaxSubmit({
            url: CNT.isEdit ? EDIT_VIDEO_API : ADD_VIDEO_API,
            data: {
                lonlat: localStorage.getItem('lonlat'),
                canData: CNT.editVideoObj.canData,
            },
            beforeSubmit: function () {
                $('#loading').show();
            },
            success: function (rst) {
                $('#loading').hide();
                CNT.isLock = 0;
                rst = JSON.parse(rst);
                $('#btnSubmit').show();
                if (rst.status != 1) {
                    BASE.showAlert(rst.msg, 'warning');
                    return false;
                }
                BASE.showAlert(rst.msg);
                CNT.hideEdit();
                CNT.selWapTag = CNT.selWapTagVideo;
                CNT.getData();
            }
        });
    });
    $('#scemap').addClass('open');
    $('#scemap').parents('.dropdown').addClass('open');
})