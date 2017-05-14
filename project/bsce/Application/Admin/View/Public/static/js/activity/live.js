var
        GET_DATA_API = 'getMoveVideoData',
        GET_QR_API = '../Qr/getLiveqr',
        UP_QR_API = '../Qr/createLiveqr',
        CNT = new Vue({
            el: '#wrapper',
            data: {
                searchKey: '',
                status: [{'id': 0, 'val': '全部状态'}, {'id': 1, 'val': '预约'}, {'id': 2, 'val': '直播'}, {'id': 3, 'val': '回放'}],
                selStatus: 0,
                tableData: [],
                pageAllData: [],
                pageShowData: [],
                order: 'desc',
                sort: 'createTime',
                showPageNav: !0,
                curPage: 1,
                pageCount: 1,
                firstDevice: '',
                secondDevice: '',
                BakPath: '',
                curId: '',
                QRCodeUrl: '',
                jumpurl: '',
                titleName: '',
                act_id: '',
                js2dCode: '',
                qrCodeVisable: !1,
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getData: function (cb) {
                    var _dt = {
                        page: CNT.curPage,
                        size: 20,
                        sort: this.sort,
                        order: this.order == 'desc' ? 0 : 1,
                        search: CNT.searchKey,
                        type: 'name',
                        status: CNT.selStatus
                    };
                    $.post(GET_DATA_API, _dt, function (rst, status) {
                        if (typeof rst != 'object')
                            rst = $.parseJSON(rst);
                        if (rst.status == '1') {
                            if (rst.data && rst.data.list && rst.data.list.length > 0) {
                                CNT.listData(rst.data.list);
                                if (CNT.curPage == 1)
                                    CNT.getPageNum(rst.data.count);
                                if (cb) {
                                    cb();
                                }
                            }
                        } else {
                            BASE.showAlert(rst.msg, 'warning');
                        }
                    });
                },
                showQr: function (idx) {
                    var _dt = this.tableData[idx];
                    var id = _dt['id'];
                    CNT.js2dCode = _dt['js2dCode'];
                    $.post(GET_QR_API, {id: id, js2dCode: CNT.js2dCode}, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status == 1) {
                            CNT.QRCodeUrl = rst.data.small;
                            CNT.titleName = _dt['name'];
                            CNT.jumpurl = _dt['jumpurl'];
                            CNT.act_id = _dt['id'];
                            CNT.js2dCode = JSON.stringify(rst.data);
                            CNT.qrCodeVisable = !0;
                        }
                    });
                },
                cancle: function () {
                    this.qrCodeVisable = !1;
                    CNT.getData();
                },
                downQr: function (type) {
                    var _Qr = JSON.parse(CNT.js2dCode);
                    type == 1 ? window.location.href = '/bsce/admin/Qr/downLiveQr?path=' + _Qr.small : window.location.href = '/bsce/admin/Qr/downLiveQr?path=' + _Qr.big;
                },
                upQr: function () {
                    $('#logo').click();
                },
                listData: function (data) {
                    if (data.length > 0) {
                        for (var i = 0; i < data.length; i++) {
                            for (var j = 0; j < data[i].device.length; j++) {
                                if (data[i].mainID == data[i].device[j].channelID) {
                                    data[i]['firstDevice'] = data[i].device[j].PullHls;
                                } else {
                                    data[i]['secondDevice'] = data[i].device[j].PullHls;
                                }
                            }
                        }
                    }
                    this.tableData = data;
                },
                orderby: function (sort) {
                    if (this.sort != sort) {
                        this.order = 'desc';
                        this.sort = sort;
                    }
                    else {
                        if (this.order == 'desc')
                            this.order = 'asc';
                        else
                            this.order = 'desc';
                    }
                    this.curPage = 1;
                    this.getData();
                },
                searchFn: function () {
                    this.curPage = 1;
                    this.getData();
                },
                showLive: function (idx, type) {
                    var _dt = this.tableData[idx];
                    type == 'first' ? localStorage.setItem('liveurl', _dt['firstDevice']) : localStorage.setItem('liveurl', _dt['bakPath']);
                    localStorage.setItem('livecover', _dt['cover']);
                    window.location.href = 'liveplay.html';
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
                    CNT.QRCodeUrl = rst.data.small;
                    CNT.js2dCode = JSON.stringify(rst.data);
                }
            });
        }
    });
});