var GET_DATA_API = 'selectTicket',
        DOWN_SHELF = 'DownShelf',
        CNT = new Vue({
            el: '#wrapper',
            data: {
                tableData: [],
                keyword: '',
                curPage: 1,
                pageAllData: [],
                pageShowData: [],
                showPageNav: !0,
                pageCount: 1,
                status: [{id: '-1', val: '全部状态'}, {id: '1', val: '上架状态'}, {id: '0', val: '下架状态'}],
                selStatus: -1
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getData: function () {
                    var _dt = {
                        page: this.curPage,
                        size: 20,
                        name: this.keyword,
                        state: this.selStatus,
                    };
                    $.post(GET_DATA_API, _dt, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != '1') {
                            BASE.showAlert(rst.msg, 'warning');
                            return false;
                        }
                        CNT.tableData = rst.data.data;
                        CNT.getPageNum(rst.data.num);
                    });
                },
                searchFn: function () {
                    this.curPage = 1;
                    CNT.getData();
                },
                saveKeyword: function () {
                    this.curPage = 1;
                    this.getData();
                },
                toInfo: function (id) {
                    window.location.href = 'ticketinfo.html?id=' + id;
                },
                downShelf: function (id, state) {
                    var _dt = {
                        id: id,
                        state: state
                    };
                    $.post(DOWN_SHELF, _dt, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status == '1') {
                            BASE.showAlert(rst.msg, '修改成功');
                            CNT.getData();
                        }
                    });
                },
                getPageNum: function (num) {
                    this.pageAllData = [];
                    if (num > 20) {
                        for (var i = 0; i < Math.ceil(num / 20); i++) {
                            this.pageAllData.push({
                                val: i,
                                num: i + 1
                            });
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
                            } else {
                                _arr = [CNT.pageAllData[0], CNT.pageAllData[1], CNT.pageAllData[2], CNT.pageAllData[3], CNT.pageAllData[4]];
                            }
                        }
                        CNT.pageShowData = _arr;
                    }
                    if (!!num && num <= this.pageAllData.length && num >= 0)
                        this.getData(num, '', _cb(num));
                },
            },
        });
CNT.init();