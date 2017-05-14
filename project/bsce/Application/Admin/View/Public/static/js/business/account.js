var
        GET_DATA_API = '../scemap/getAllScemap',
        DEL_API = '../scemap/delScemap',
        CNT = new Vue({
            el: '#pageWrapper',
            data: {
                selStatus: 0,
                status: [{id: '0', val: '全部状态'}, {id: '1', val: '已激活'}, {id: '2', val: '未激活'}],
                tableData: [],
                pageAllData: [],
                pageShowData: [],
                checkedArr: [],
                showPageNav: !0,
                curPage: 1,
                pageCount: 1,
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
            },
            methods: {
                init: function () {
//                    this.getData();
                },
                getData: function (page, name, cb) {
                    var _dt = {
                        page: !!page ? page : this.curPage,
                        size: '20',
                        Longitude: this.Longitude,
                        Latitude: this.Latitude,
                        scename: !!name ? name : this.scename
                    };
                    $.post(GET_DATA_API, _dt, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg);
                            return false;
                        }
                        CNT.tableData = rst.data.info;
                        if (CNT.curPage == 1)
                            CNT.getPageNum(rst.data.count);
                        if (cb) {
                            cb();
                        }

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
                            BASE.showAlert(rst.msg);
                            return false;
                        }
                        BASE.showAlert(rst.msg);
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
    $('#keyWord').bind('keypress', function (event) {
        if (event.keyCode == "13") {
            CNT.getData(1);
        }
    });
});