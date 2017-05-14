var
        GET_DATA_API = 'selectScemap',
        DEL_API = '../scemap/delScemap',
        CNT = new Vue({
            el: '#pageWrapper',
            data: {
                serchName: '',
                Longitude: '',
                Latitude: '',
                tableData: [],
                pageAllData: [],
                pageShowData: [],
                checkedArr: [],
                showPageNav: !0,
                curPage: 1,
                size: 20,
                pageCount: 1,
                lon:'',
                lat:'',
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
                    // this.Longitude = getUrlParam('Longitude');
                    // this.Latitude = getUrlParam('Latitude');
                    this.getData();
                },
                saveSerchName: function () {
                    CNT.curPage = 1;
                    CNT.getData();
                },
                getData: function (page, name, cb) {
                    var _dt = {
                        page: this.curPage,
                        size: this.size,
                        serchName: this.serchName
                    };
                    $('#loading').show();
                    $.post(GET_DATA_API, _dt, function (rst) {
                        $('#loading').hide();
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg);
                            return false;
                        }
                        CNT.checkedArr = [];
                        CNT.tableData = rst.data.data;
                        CNT.lon=rst.data.lon,
                        CNT.lat=rst.data.lat
                        if (CNT.curPage == 1)
                            CNT.getPageNum(rst.data.num);
                        if (cb) {
                            cb();
                        }
                    })
                },
                editPoint: function (id) {
                    window.location.href = 'base.html?id=' + id+'&lon='+CNT.lon+'&lat='+CNT.lat;
                },
                addPoint: function () {
                    window.location.href = 'base.html?lon='+CNT.lon+'&lat='+CNT.lat;
                },
                delPoint: function () {
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
                    $('#loading').show();
                    $.post(DEL_API, _dt, function (rst) {
                        $('#loading').hide();
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg, 'warning');
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
            }
        });
CNT.init();
//$(function () {
//    $('#keyWord').bind('keypress', function (event) {
//        if (event.keyCode == "13") {
//            CNT.getData(1);
//        }
//    });
//});