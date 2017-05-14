var
        GET_DATA_API = 'selectAllcse',
        GET_SCETYPE_API = 'sceClassify',
        SAVE_SCE_API = 'saveAllsce',
        EDIT_API = 'saveAllsce',
        UP_QR_API = '../Qr/createSceqr',
        CNT = new Vue({
            el: '#wrapper',
            data: {
                /* 标签相关变量 开始 */
                allSysTagArr: [], //数组。从接口获取的系统标签列表。（从接口获取数据填充）。
                sysTags: '', //字符串。从接口获取的已选系统标签ID。（从接口获取数据填充）。
                sysTagArr: [], //数组。已选系统标签ID。
                sysTagObjArr: [], //数组。整理已选系统标签，根据标签ID显示标签名称。
                usrTags: '', //字符串。从接口获取的用户自定义标签。（从接口获取数据填充）。
                usrTagArr: [], //数组。用户自定义标签。
                /* 标签相关变量 结束 */
                audefinedType: '',
                star: '',
                PicUrl: '',
                sceName: '',
                sceSynopsis: '',
                qrCode: '',
                jumpurl: '',
                qrCodeVisable: !1,
            },
            methods: {
                init: function () {
                    this.getTags();
                    this.getData();
                },
                getData: function () {
                    var _dt = {
                    };
                    $('#loading').show();
                    $.post(GET_DATA_API, _dt, function (rst) {
                        $('#loading').hide();
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert('获取数据失败，请重新操作', 'warning');
                            return false;
                        }
                        CNT.setData(rst.data[0]);
                        CNT.jumpurl = rst.data.jumpurl;
                    });
                },
                /* 标签相关函数 开始 */
                getTags: function () { //从接口获取数据后，填充对应变量，然后初始化标签列表。
                    $.post(GET_SCETYPE_API, {}, function (rst) {
                        rst = $.parseJSON(rst);
                        if (rst['status'] == '1') {
                            CNT.allSysTagArr = rst.data;
                            //CNT.initTag();
                        } else {
                            BASE.showAlert('获取景区种类失败');
                        }
                    });
                },
                initTag: function () { //初始化标签列表
                    var _html = '';
                    this.allSysTagArr.forEach(function (obj) {
                        _html += '<option value="' + obj.id + '">' + obj.typeName + '</option>'; //根据接口返回数据中的Key，修改obj.id和obj.name
                    });
                    $('#tagPcr').html(_html);
                    $('#tagPcr').selectpicker('refresh');
                },
                addUsrTag: function () {
                    if ((this.sysTagArr.length + this.usrTagArr.length) >= 4) {
                        BASE.showAlert('景区标签最多只能设置4个');
                        return false;
                    }
                    setTimeout(function () {
                        if (CNT.usrTagArr.indexOf(TRIM(CNT.usrTag)) < 0 && TRIM(CNT.usrTag) != '')
                            CNT.usrTagArr.push(TRIM(CNT.usrTag));
                        CNT.usrTag = '';
                    }, 200);
                },
                selectTag: function (id) {
                    var _class = $('#' + id).parent()[0].className;
                    if (_class == 'tag-cnr') {
                        $('#' + id).parent().removeClass('tag-cnr').addClass('tag-cnr-nocheck');
                        var _idx = 0;
                        for (var i = 0; i < this.sysTagArr.length; i++) {
                            if (this.sysTagArr[i] == id) {
                                _idx = i;
                            }
                        }
                        this.sysTagArr.splice(_idx, 1);
                    }
                    else {
                        if (this.sysTagArr.length >= 5) {
                            BASE.showAlert('景区标签最多只能设置5个');
                            return false;
                        }
                        $('#' + id).parent().removeClass('tag-cnr-nocheck').addClass('tag-cnr');
                        this.sysTagArr.push(id);
                    }
                    this.resetSysObjArr();
                },
                resetSysObjArr: function () {
                    this.sysTagObjArr = [];
                    this.allSysTagArr.forEach(function (obj) {
                        if (CNT.sysTagArr.indexOf(obj.id) > -1)
                            CNT.sysTagObjArr.push({id: obj.id, name: obj.typeName});
                    });
                },
                removeTag: function (type, idx) {
                    if (type == 'usr') {
                        this.usrTagArr.splice(idx, 1);
                    } else if (type == 'sys') {
                        $('#' + this.sysTagArr[idx]).parent().removeClass('tag-cnr').addClass('tag-cnr-nocheck');
                        this.sysTagArr.splice(idx, 1);
                        this.sysTagObjArr.splice(idx, 1);
                    }
                },
                postTag: function () { //保存数据时，将标签分别转换为字符串后再保存。
                    this.sysTags = this.sysTagArr.join(',');
                    this.usrTags = this.usrTagArr.join(',');
                },
                /*标签相关函数 结束*/
                showQrCode: function () {
                    this.qrCodeVisable = !0;
                },
                cancle: function () {
                    this.qrCodeVisable = !1;
                },
                downQr: function (type) {
                    window.location.href = '/bsce/admin/Qr/downSceQr?type=' + type;
                },
                saveEdit: function () {
                    var _tagCount = CNT.sysTagArr.length;
                    if (_tagCount > 5) {
                        BASE.showAlert('景区标签最多只能设置5个');
                        return false;
                    }
                    var _sceTpye = '';
                    if (CNT.sysTagArr.length > 0) {
                        for (var i = 0; i < CNT.sysTagArr.length; i++) {
                            if (!!CNT.sysTagArr[i]) {
                                _sceTpye += CNT.sysTagArr[i] + ",";
                            }
                        }
                        _sceTpye = _sceTpye.substr(0, _sceTpye.length - 1);
                    }
                    $('#editform').ajaxSubmit({
                        url: 'saveAllsce',
                        data: {
                            sceType: _sceTpye,
                            sceSynopsis: TRIM(CNT.sceSynopsis) == '' ? '-1' : CNT.sceSynopsis,
                        },
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
                            BASE.showAlert(rst.msg);
                        }
                    });
                },
                upQr: function () {
                    $('#logo').click();
                },
                setData: function (rst) {
                    CNT.sceName = rst['sceName'];
                    CNT.star = rst['star'];
                    switch (parseInt(rst['star']))
                    {
                        case 1:
                            CNT.star = 'A';
                            break;
                        case 2:
                            CNT.star = 'AA';
                            break;
                        case 3:
                            CNT.star = 'AAA';
                            break;
                        case 4:
                            CNT.star = 'AAAA';
                            break;
                        case 5:
                            CNT.star = 'AAAAA';
                            break;
                        default:
                            CNT.star = 'S';
                    }
                    this.sysTagArr = rst['sceType'];
                    for (var i = 0; i < this.sysTagArr.length; i++) {
                        $('#' + this.sysTagArr[i]).parent().removeClass('tag-cnr-nocheck').addClass('tag-cnr');
                    }
                    this.PicUrl = rst['backgroundpic'];
                    this.sceSynopsis = rst['sceSynopsis'] == '-1' ? '' : rst['sceSynopsis'];
                    this.qrCode = rst['ewmUrl'];
                    CNT.audefinedType = rst['audefinedType'].join();
                    CNT.resetSysObjArr();
                    BASE.initTextCount();

                },
            }
        });
CNT.init();
$(function () {
    var picView = new uploadPreview({
        UpBtn: 'pic',
        ImgShow: 'picView',
        ImgType: ['jpg', 'png'],
        ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种'
    });
    var QrView = new uploadPreview({
        UpBtn: 'logo',
        ImgShow: 'logoView',
        ImgType: ['jpg', 'png'],
        ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种',
        callback: function () {
            $('#uploadQr').ajaxSubmit({
                url: UP_QR_API,
                beforeSubmit: function () {
                    $('#loading').show();
                },
                success: function (rst) {
                    $('#loading').hide();
                    rst = JSON.parse(rst);
                    if (rst.status != '1') {
                        BASE.showAlert(rst.msg);
                        return false;
                    }
                    CNT.qrCode = rst.data;
                }
            });
        }
    });
    $('#scebase').addClass('open');
    $('#scebase').parents('.dropdown').addClass('open');
});