var
        GET_DATA_API = 'getData',
        EDIT_API = 'onOrOff',
        CNT = new Vue({
            el: '#page-wrapper',
            data: {
                clickCount: 0,
                shareCount: 0,
                entryCount: 0,
                sum: 0,
                searchKey: '',
                searchSort: '0',
                searchAsc: '0',
                tableData: [],
                pageAllData: [],
                pageShowData: [],
                sortType: 'desc',
                sortName: 'count',
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
                    this.getData();
                },
                getData: function (like, type, sort, page, keyword, asc, cb) {
                    var _dt = {
                        page: !!page ? page : CNT.curPage,
                        sort: !!sort ? sort : this.sortName,
                        sortType: !!asc ? asc : this.sortType,
                        size: 20,
                        keyWord: !!keyword ? keyword : this.searchKey
                    };
                    $.post(GET_DATA_API, _dt, function (rst, status) {
                        if (typeof rst != 'object')
                            rst = $.parseJSON(rst);
                        if (rst.status == '1') {
                            CNT.listData(rst.data.info);
                            CNT.entryCount = rst.data.entryCount;
                            CNT.sum = rst.data.sum;
                            CNT.clickCount = rst.data.clickCount;
                            CNT.shareCount = rst.data.shareCount;
                            if (CNT.curPage == 1)
                                CNT.getPageNum(rst.data.count);
                            if (cb) {
                                cb();
                            }

                        } else {
                            CNT.showNotice('warning', rst.msg);
                        }
                    });
                },
                listData: function (data) {
                    this.tableData = data;
                },
                order: function (sort) {
                    if (this.sortName != sort) {
                        this.sortType = 'desc';
                        this.sortName = sort;
                    }
                    else {
                        if (this.sortType == 'desc')
                            this.sortType = 'asc';
                        else
                            this.sortType = 'desc';
                    }
                    this.curPage = 1;
                    this.getData();
                },
                onOrOff: function (idx) {
                    var _dt = CNT.tableData[idx];
                    var id = _dt['id'];
                    var _data = {
                        id: id
                    }
                    $.post(EDIT_API, _data, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            CNT.showAlert('warning', rst.msg);
                            return false;
                        }
                        CNT.showNotice('success', rst.msg);
                        CNT.getData();
                    })
                },
                showDetail: function (idx) {
                    var _dt = CNT.tableData[idx];
                    var id = _dt['id'];
                    window.location.href = 'detail.html?id=' + id;
                },
                searchFn: function () {
                    this.curPage == 1;
                    this.getData();
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
                        this.getData('', '', '', num, '', '', _cb(num));
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
                    /*setTimeout(function() {
                     CNT.alert.type = '';
                     CNT.alert.msg = '';
                     CNT.alert.show = !1;
                     }, 3000);*/
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
            CNT.searchFn();
        }
    });
});