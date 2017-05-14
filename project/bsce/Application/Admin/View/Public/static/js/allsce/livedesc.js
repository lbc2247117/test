var
        GET_TYPE_API = 'getLivetag',
        EDIT_API = 'updateLive',
        CNT = new Vue({
            el: '#wrapper',
            data: {
                videoTypes: [],
                id: '',
                audioUrl: '',
                staticAudioUrl: '',
                videoTag: [],
                videoPath: '',
                videoPic: '',
                videoName: '',
                remark: '',
                musicVisible: !1,
                upMusicVisible: !1,
                isLock: 0,
            },
            methods: {
                init: function () {
                    this.getVideoType();
                    this.getData();
                },
                getVideoType: function () {
                    $.post(GET_TYPE_API, {}, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status == 1) {
                            CNT.videoTypes = rst.data;
                            CNT.initVideoType();
                        }
                    });
                },
                initVideoType: function () {
                    var _html = '';
                    CNT.videoTypes.forEach(function (obj) {
                        _html += '<option value="' + obj.id + '">' + obj.tagName + '</option>';
                    });
                    $('#liveType').html(_html);
                    $('#liveType').selectpicker('refresh');
                },
                getData: function () {
                    var data = localStorage.getItem('livedesc');
                    data = JSON.parse(data);
                    this.setData(data);
                },
                setData: function (data) {
                    this.id = data.id;
                    this.audioUrl = data.audioUrl;
                    this.staticAudioUrl = data.audioUrl;
                    this.videoTag = data.videoTag.split(';');
                    this.videoPath = data.videoPath;
                    this.videoPic = data.videoPic;
                    this.videoName = data.videoName;
                    this.remark = data.remark;
                    if (this.audioUrl != 0 && this.audioUrl != 1) {
                        this.musicVisible = !0;
                        this.upMusicVisible = !0;
                        this.audioUrl = 2;
                        $('#audioView').attr('src', this.staticAudioUrl);
                    }
                    BASE.initTextCount();
                    setTimeout(function () {
                        $('#liveType').selectpicker('val', CNT.videoTag);
                    }, 200);
                },
                saveEdit: function () {
                    if (this.isLock == 1)
                        return;
                    //判断输入
                    if (STRING_LENGTH(TRIM(CNT.videoName)) == 0) {
                        BASE.showAlert('请输入直播名称');
                        return;
                    }
                    var _selLiveArr = $('#liveType').val();
                    var _selLive = '';
                    if (_selLiveArr) {
                        for (var i = 0; i < _selLiveArr.length; i++) {
                            _selLive += _selLiveArr[i] + ';';
                        }
                    }
                    this.isLock = 1;
                    $('#editform').ajaxSubmit({
                        url: EDIT_API,
                        data: {
                            id: CNT.id,
                            videoName: CNT.videoName,
                            remark: CNT.videoRemark,
                            audioUrl: CNT.audioUrl,
                            videoTag: _selLive,
                        },
                        beforeSubmit: function () {
                            $('#loading').show();
                        },
                        success: function (rst) {
                            $('#loading').hide();
                            CNT.isLock = 0;
                            rst = JSON.parse(rst);
                            if (rst.status != 1) {
                                BASE.showAlert(rst.msg, 'warning');
                                return;
                            }
                            $.post('getVideomsg', {id: CNT.id}, function (rstData) {
                                rstData = JSON.parse(rstData);
                                if (rstData.status == 1) {
                                    localStorage.setItem('livedesc', JSON.stringify(rstData.data));
                                }
                            });
                            BASE.showAlert('保存成功');
                            window.location.reload();
                        },
                    });
                },
                setMusic: function () {
                    if (this.audioUrl == 0 || this.audioUrl == 1) {
                        this.musicVisible = !1;
                        this.upMusicVisible = !1;
                    } else {
                        this.upMusicVisible = !0;
                        if ($('#music').val() || (this.staticAudioUrl != 0 && this.staticAudioUrl != 1))
                            this.musicVisible = !0;
                    }
                },
                cancle: function () {
                    window.location.href = 'live.html';
                },
                playMusic: function () {
                    var audio = $('#audioView')[0];
                    if (audio.paused) {
                        audio.play();
                        $('#musicPlayCnr img').attr('src', '/bsce/Public/img/pause.png');
                    } else {
                        audio.pause();
                        $('#musicPlayCnr img').attr('src', '/bsce/Public/img/play.png');
                    }
                },
            }
        });
CNT.init();
$(function () {
    var uploadCover = new uploadPreview({
        UpBtn: 'pic',
        ImgShow: 'picView',
        ImgType: ['jpg', 'png'],
        ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种'
    });
    var rII = new uploadPreview({
        UpBtn: "music",
        ImgShow: "audioView",
        ImgType: ["mp3"],
        ErrMsg: "选择文件错误,现仅支持MP3格式的视频",
        callback: function () {
            CNT.musicVisible = !0;
        }
    });
    $('#scelive').addClass('open');
    $('#scelive').parents('.dropdown').addClass('open');
});