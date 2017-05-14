var
        GET_DATA_API = 'selectQuse',
        DEL_API = 'delQues',
        SAVE_API = 'ansQues',
        CNT = new Vue({
            el: '#wrapper',
            data: {
                editCnrVisible: !1,
                selStatus: 0,
                serchName: '',
                selSortType: 1,
                status: [{id: '0', val: '全部状态'}, {id: '2', val: '待回答'}, {id: '1', val: '已回答'}],
                sortType: [{id: '1', val: '时间最新'}, {id: '2', val: '点赞数最多'}],
                tableData: [],
                pageAllData: [],
                pageShowData: [],
                checkedArr: [],
                showPageNav: !0,
                curPage: 1,
                pageCount: 1,
                editObj: {},
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
                    this.initEditObj();
                    this.getData();
                },
                initEditObj: function () {
                    this.editObj = {
                        question: '',
                        answer: '',
                        time: '',
                        id: '',
                        vote: ''
                    };
                },
                getData: function (page, name, cb) {
                    var _dt = {
                        page: !!page ? page : this.curPage,
                        size: '20',
                        sortType: CNT.selSortType,
                        anser: CNT.selStatus,
                        serchName: CNT.serchName
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
                        if (CNT.curPage == 1)
                            CNT.getPageNum(rst.data.num);
                        if (cb) {
                            cb();
                        }

                    })
                },
                showEdit: function (idx) {

                    var
                            _dt = this.tableData[idx];

                    CNT.editObj.question = _dt.requestContent;
                    CNT.editObj.answer = _dt.requestRepeat;
                    CNT.editObj.time = _dt.createDate;
                    CNT.editObj.id = _dt.id;
                    CNT.editObj.vote = _dt.vote;

                    // this.editObj.cover = _dt.pagepic;
                    this.editCnrVisible = !0;
                },
                hideEdit: function () {
                    this.editCnrVisible = !1;
                    this.initEditObj();
                    $('#saveEdit').off('click');
                },
                saveEdit: function (obj) {

                    if (!obj.answer) {
                        BASE.showAlert('请填写问题答案');
                        return false;
                    }
                    var _dt = {
                        requestRepeat: obj.answer,
                        id: obj.id,
                        vote: obj.vote
                    };
                    $('#loading').show();
                    $.post(SAVE_API, _dt, function (rst) {
                        $('#loading').hide();
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg);
                            return false;
                        }
                        BASE.showAlert('修改成功');
                        CNT.hideEdit();
                        CNT.getData();


                    })

                },
                delQusetion: function () {
                    if (this.checkedArr.length < 1) {
                        this.showAlert('warning', '请先选择要删除的条目！');
                        return;
                    }
                    var
                            _dt = {
                                id: []
                            };
                    this.checkedArr.forEach(function (obj) {
                        _dt.id.push(obj);
                    });
                    var conf = confirm("您确定要删除吗");
                    if (!conf)
                        return false;
                    $('#loading').show();
                    $.post(DEL_API, _dt, function (rst, status) {
                        $('#loading').hide();
                        if (typeof rst != 'object')
                            rst = $.parseJSON(rst);
                        if (rst.status == 1) {
                            CNT.getData();
                            BASE.showAlert('删除成功');
                        } else {
                            BASE.showAlert(rst.msg);
                        }
                    });
                },
                saveSerchName: function () {
                    this.Page = 1;
                    this.getData();
                },
                saveAnser: function () {
                    this.Page = 1;
                    this.getData();
                },
                saveSortType: function () {
                    this.Page = 1;
                    this.getData();
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