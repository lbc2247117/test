var
        GET_DATA_API = 'selectActivity',
        DEL_API = 'delActivity',
        UPDPWN_APT = 'upActivity',
        UP_QR_API = '../Qr/createActqr',
        GET_QR_API = '../Qr/getActqr',
        CNT = new Vue({
            el: '#pageWrapper',
            data: {

                status: [{id: '', val: '全部状态'},{id: '0', val: '上线状态'}, {id: '1', val: '下线状态'}],
                tableData: [],
                pageAllData: [],
                pageShowData: [],
                checkedArr: [],
                showPageNav: !0,
                curPage: 1,
                pageCount: 1,
                keyword: '',
                QRCodeUrl: '',
                jumpurl: '',
                titleName: '',
                act_id: '',
                selStatus:'',
                        alert: {
                            show: !1,
                            type: '',
                            msg: ''
                        },
                notice: {
                    show: !1,
                    type: '',
                    msg: ''
                },
                qrCodeVisable: !1,
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getData: function (page, name, cb) {
                    var _dt = {
                        page: !!page ? page : this.curPage,
                        size: '20',
                        state: this.selStatus,
                        serchName: !!name ? name : this.keyword
                    };
                    $('#loading').show();
                    $.post(GET_DATA_API, _dt, function (rst) {
                        $('#loading').hide();
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            CNT.showNotice('warning', rst.msg);
                            return false;
                        }
                        CNT.checkedArr = [];
                        CNT.tableData = rst.data.data;
                        if (CNT.curPage == 1)
                            CNT.getPageNum(rst.data.count);
                        if (cb) {
                            cb();
                        }
                    })
                },
                showQr: function (idx) {
                    var _dt = this.tableData[idx];
                    var id = _dt['id'];
                    $.post(GET_QR_API, {id: id}, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status == 1) {
                            CNT.QRCodeUrl = rst.data;
                            CNT.titleName = _dt['titleName'];
                            CNT.jumpurl = _dt['jumpurl'];
                            CNT.act_id = _dt['id'];
                            CNT.qrCodeVisable = !0;
                        }
                    });
                },
                cancle: function () {
                    this.qrCodeVisable = !1;
                },
                downQr: function (type) {
                    var id = this.act_id;
                    window.location.href = '/bsce/admin/Qr/downActQr?id=' + id + '&type=' + type;
                },
                tolist: function (id) {
                    window.location.href = 'list.html?activityID=' + id;
                },
                delData: function () {

                    if (this.checkedArr.length < 1) {
                        BASE.showAlert('请选择要删除的条目', 'warning');
                        return;
                    }
                    var
                            _dt = {
                                ids: [],
                                picUrl: []
                            };
                    this.checkedArr.forEach(function (idx) {
                        _dt.ids.push(CNT.tableData[idx].id);

                    });
                    var conf = confirm("您确定要删除吗");
                    if (!conf)
                        return false;
                    $.post(DEL_API, _dt, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg, 'warning');
                            return false;
                        }
                        BASE.showConfirm(rst.msg);
                        history.go(0);
                    });
                },
                saveKeyword: function () {
                    this.curPage = 1;
                    this.getData();
                },
                toDesc: function () {
                    window.location.href = 'commondesc.html';
                },
                toEdit: function (id) {
                    var id = id;
                    window.location.href = 'commondesc.html?id=' + id;
                },
                updown: function (idx) {
                    if (idx.state) {
                        this.state = 0;
                    } else
                        this.state = 1;
                    var _dt = {
                        id: idx.id,
                        state: CNT.state
                    };
                    $.post(UPDPWN_APT, _dt, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            CNT.showNotice('warning', rst.msg);
                            return false;
                        }
                        CNT.getData();
                        CNT.showNotice('success', '修改成功');

                    })
                },
                editPoint: function (idx) {
                    var dr = this.tableData[idx];
                    var SceLongitude = dr.SceLongitude;
                    var SceLatitude = dr.SceLatitude;
                    var Longitude = dr.Longitude;
                    var Latitude = dr.Latitude;
                    window.location.href = 'add_point.html?SceLongitude=' + SceLongitude + "&SceLatitude=" + SceLatitude + "&Longitude=" + Longitude + "&Latitude=" + Latitude;
                },
                addPoint: function () {
                    window.location.href = 'add_point.html?Longitude=' + this.Longitude + "&Latitude=" + this.Latitude;
                },
                delPoint: function (idx) {
                    var dr = CNT.tableData[idx];
                    var _dt = {
                        SceLongitude: dr.SceLongitude,
                        SceLatitude: dr.SceLatitude
                    };
                    var conf = confirm("您确定要删除吗");
                    if (!conf)
                        return false;
                    $.post(DEL_API, _dt, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            CNT.showAlert('warning', rst.msg);
                            return false;
                        }
                        CNT.showNotice('success', rst.msg);
                        CNT.getData();
                    });
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
                selFn: function () {
                    this.curPage = 1;
                    this.getData();
                },
                upQr: function () {
                    $('#logo').click();
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
                        this.getData(num, '', _cb(num));
                },
                sceMap: function () {
                    window.location.href = 'scemap.html?Longitude=' + this.Longitude + "&Latitude=" + this.Latitude;
                },
                scePoint: function () {
                    window.location.href = 'viewpoint_list.html?Longitude=' + this.Longitude + "&Latitude=" + this.Latitude;
                },
                sceVideo: function () {
                    window.location.href = 'scevideo.html?Longitude=' + this.Longitude + "&Latitude=" + this.Latitude;
                },
                scePic: function () {
                    window.location.href = 'sceimg.html?Longitude=' + this.Longitude + "&Latitude=" + this.Latitude;
                },
                sceBase: function () {
                    window.location.href = 'resorts_base.html?Longitude=' + this.Longitude + "&Latitude=" + this.Latitude;
                },
                showNotice: function (type, msg) {
                    switch (type) {
                        case 'success':
                            this.notice.type = 'alert-success';
                            break;
                        case 'info':
                            this.notice.type = 'alert-info';
                            break;
                        case 'warning':
                            this.notice.type = 'alert-warning';
                            break;
                        case 'danger':
                            this.notice.type = 'alert-danger';
                            break;
                    }

                    this.notice.msg = msg;
                    this.notice.show = !0;
                    setTimeout(function () {
                        CNT.notice.type = '';
                        CNT.notice.msg = '';
                        CNT.notice.show = !1;
                    }, 3000);
                },
                hideNotice: function () {
                    this.notice.type = '';
                    this.notice.msg = '';
                    this.notice.show = !1;
                },
                showAlert: function (type, msg) {
                    switch (type) {
                        case 'success':
                            this.alert.type = 'alert-success';
                            break;
                        case 'info':
                            this.alert.type = 'alert-info';
                            break;
                        case 'warning':
                            this.alert.type = 'alert-warning';
                            break;
                        case 'danger':
                            this.alert.type = 'alert-danger';
                            break;
                    }

                    this.alert.msg = msg;
                    this.alert.show = !0;
                },
                hideAlert: function () {
                    this.alert.type = '';
                    this.alert.msg = '';
                    this.alert.show = !1;
                }
            }
        });
CNT.init();
$(function () {
    var QrView = new uploadPreview({
        UpBtn: 'logo',
        ImgShow: 'logoView',
        ImgType: ['jpg', 'png'],
        ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种',
        callback: function () {
            $('#uploadQr').ajaxSubmit({
                url: UP_QR_API,
                data: {
                    id: CNT.act_id,
                },
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
                    CNT.QRCodeUrl = rst.data;
                }
            });
        }
    });
    $('#keyWord').bind('keypress', function (event) {
        if (event.keyCode == "13") {
            CNT.getData(1);
        }
    });
});