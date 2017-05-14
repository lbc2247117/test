var
        GET_VIDEO_API = '../Allsce/selectVideo',
        TO_TOP_API = '../Allsce/topVideo',
        CNT = new Vue({
            el: '#wrapper',
            data: {
                id: URL_PARAM('id'),
                tableVideo: [],
                pageAllData: [],
                pageShowData: [],
                checkedArr: [],
                showPageNav: !0,
                curPage: 1,
                pageCount: 1,
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getData: function (cb) {
                    var _dt = {
                        videoType: 1,
                        lonlat: localStorage.getItem('lonlat'),
                        page: CNT.curPage,
                        size: 20
                    }
                    $('#loading').show();
                    $.post(GET_VIDEO_API, _dt, function (rst) {
                        $('#loading').hide();
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg, 'warning');
                            return false;
                        }
                        CNT.tableVideo = rst.data.data;
                        if (CNT.curPage == 1)
                            CNT.getPageNum(rst.data.num);
                        if (cb) {
                            cb();
                        }
                    });
                },
                showEdit: function (idx) {
                    localStorage.setItem('livedesc', JSON.stringify(CNT.tableVideo[idx]));
                    window.location.href = 'livedesc.html?id=' + this.id;
                },
                toTop: function () {
                    if (this.checkedArr.length < 1) {
                        BASE.showAlert('请先选择要置顶的条目！', 'warning');
                        return;
                    }
                    var
                            _dt = {
                                ids: []
                            };
                    this.checkedArr.forEach(function (idx) {
                        _dt.ids.push(CNT.tableVideo[idx].id);
                    });
                    var conf = confirm("您确定要置顶吗");
                    if (!conf)
                        return false;
                    $('#loading').show();
                    $.post(TO_TOP_API, _dt, function (rst) {
                        $('#loading').hide();
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg);
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
                        this.getData(_cb(num));
                },
                gotobase: function () {
                    window.location.href = 'base.html?id=' + CNT.id;
                },
                gotovideo: function () {
                    window.location.href = 'video.html?id=' + CNT.id;
                },
                gotolive: function () {
                    window.location.href = 'live.html?id=' + CNT.id;
                },
                gotoimg: function () {
                    window.location.href = 'image.html?id=' + CNT.id;
                },
                gotocoordinate: function(){
                    window.location.href = 'coordinate.html?id=' + CNT.id;
                }
            }
        });
CNT.init();
$(function () {
    $('#scemap').addClass('open');
    $('#scemap').parents('.dropdown').addClass('open');
});