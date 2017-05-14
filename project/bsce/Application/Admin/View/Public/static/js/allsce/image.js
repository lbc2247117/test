var
        GET_IMG_API = 'selectPicture',
        GET_POINT_API = 'selectScemap',
        DEL_IMG_API = 'deletPicture',
        CNT = new Vue({
            el: '#wrapper',
            data: {
                tablePoint: [],
                tableImg: [],
                points: [],
                selPointImg: -1,
                pageAllData: [],
                pageShowData: [],
                imgCheckedArr: [],
                showPageNav: !0,
                curPage: 1,
                size: 20,
                serchType: 0,
                selPoint: '-1,',
                pageCount: 1,
            },
            methods: {
                init: function () {

                    this.getPoint();
                    this.getData();
                },
                addImg: function () {
                    window.location.href = 'addimg.html';
                },
                getPoint: function () {
                    $.post(GET_POINT_API, {}, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status == '1') {
                            CNT.points.push({SceLongitude: -1, SceLatitude: '', SceName: '全部景点'});
                            for (var i = 0; i < rst.data.length; i++) {
                                CNT.points.push(rst.data[i]);
                            }
                            CNT.selPoint = '-1,';
                        } else {
                            BASE.showAlert(rst.msg, 'warning');
                        }
                    });
                },
                getData: function (page, SceLongitude, SceLatitude, cb) {
                    var _lonlat = CNT.selPoint;
                    if (_lonlat == '-1,')
                        _lonlat = -1;
                    var _dt = {
                        lonlat: _lonlat,
                        page: CNT.curPage,
                        size: CNT.size,
                        serchType: CNT.serchType
                    };
                    $('#loading').show();
                    $.post(GET_IMG_API, _dt, function (rst) {
                        $('#loading').hide();
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg);
                            return false;
                        }
                        CNT.imgCheckedArr = [];
                        if (CNT.curPage == 1)
                            CNT.getPageNum(rst.data.num);
                        CNT.tableImg = rst.data.data;
                        if (cb) {
                            cb();
                        }
                    });
                },
                getPointData: function () {
                    this.curPage = 1;
                    this.getData();
                },
                delImg: function () {
                    if (this.imgCheckedArr.length < 1) {
                        BASE.showAlert('请选择要删除的条目', 'warning');
                        return;
                    }
                    var
                            _dt = {
                                ids: [],
                                picUrl: [],
                                shortPic: []
                            };
                    this.imgCheckedArr.forEach(function (idx) {
                        _dt.ids.push(CNT.tableImg[idx].id);
                        _dt.picUrl.push(CNT.tableImg[idx].picPath);
                        _dt.shortPic.push(CNT.tableImg[idx].picShortPath);
                    });
                    var conf = confirm("您确定要删除吗");
                    if (!conf)
                        return false;
                    $('#loading').show();
                    $.post(DEL_IMG_API, _dt, function (rst) {
                        $('#loading').hide();
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg, 'warning');
                            return false;
                        }
                        BASE.showAlert(rst.msg);
                        CNT.getData();
                        CNT.imgCheckedArr = [];
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
                        this.getData(num, '', '', _cb(num));
                },
            }
        });
CNT.init();
