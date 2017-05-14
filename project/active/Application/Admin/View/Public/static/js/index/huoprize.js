var
        GET_PRIZE = 'huoPrize',
        SAVE_API = 'savePrize',
        DEL_API = 'delPrize',
        CNT = new Vue({
            el: '#page-wrapper',
            data: {
                checkedArr: [],
                tableData: [],
                pageAllData: [],
                pageShowData: [],
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
                editCnrVisible: !1,
            },
            methods: {
                init: function () {
                    this.getData();
                    this.initEditObj();
                },
                initEditObj: function () {
                    this.editObj = {
                        id: '',
                        prizeName: '',
                        prizeTotal: '',
                        lock: 0,
                    };
                },
                showPrize: function (idx) {
                    this.editCnrVisible = !0;
                    if (idx == -1)
                        return;
                    var _dt = this.tableData[idx];
                    this.editObj.id = _dt['id'];
                    this.editObj.prizeName = _dt['text'];
                    this.editObj.prizeTotal = _dt['total'];
                },
                savePrize: function () {
                    if (this.editObj.lock)
                        return;
                    var _dt = {
                        id: this.editObj.id,
                        text: this.editObj.prizeName,
                        total: this.editObj.prizeTotal,
                    };
                    $.post(SAVE_API, _dt, function (rst) {
                        CNT.editObj.lock = 0;
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            CNT.showAlert('warning', rst.msg);
                            return false;
                        }
                        CNT.showNotice('success', rst.msg);
                        CNT.hideEdit();
                        CNT.getData();
                    });
                },
                getData: function (like, type, sort, page, keyword, asc, cb) {
                    var _dt = {
                        page: !!page ? page : CNT.curPage,
                        size: 20,
                    };
                    $.post(GET_PRIZE, _dt, function (rst, status) {
                        if (typeof rst != 'object')
                            rst = $.parseJSON(rst);
                        if (rst.status == '1') {
                            CNT.listData(rst.data.info);
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
                hideEdit: function () {
                    this.editCnrVisible = !1;
                    this.initEditObj();
                },
                delPrize: function () {
                    if (this.checkedArr.length < 1) {
                        this.showAlert('warning', '请先选择要删除的条目！');
                        return;
                    }
                    var _dt = {
                        id: []
                    };
                    this.checkedArr.forEach(function (obj) {
                        _dt.id.push(obj);
                    });
                    var conf = confirm("您确定要删除吗");
                    if (!conf)
                        return false;
                    $.post(DEL_API, _dt, function (rst, status) {
                        rst = JSON.parse(rst);
                        if (rst.status == 1) {
                            CNT.getData();
                            CNT.showNotice('success', '删除成功！');
                        } else {
                            CNT.showNotice('warning', rst.msg);
                        }
                    });

                },
                listData: function (data) {
                    this.tableData = data;
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
                },
                hideAlert: function () {
                    this.alert.type = '';
                    this.alert.msg = '';
                    this.alert.show = !1;
                }
            }
        });
CNT.init();