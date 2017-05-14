var
        GET_DATA_API = 'getFactoryData',
        SET_DATA_API = 'setFactory',
        ADD_DATA_API = 'addFactory',
        DEL_DATA_API = 'delFactory',
        MET = new Vue({
            el: '#wrapper',
            data: {
                tableData: [],
                pageAllData: [],
                pageShowData: [],
                checkedArr: [],
                showPageNav: !0,
                curPage: 1,
                size: 20,
                pageCount: 1,
                editVisible: !1,
                editobj: {},
                isEdit: !0,
                isLock: !1,
            },
            methods: {
                init: function () {
                    this.initEditObj();
                    this.getData();
                },
                initEditObj: function () {
                    this.editobj = {
                        id: '',
                        name: '',
                        picPath: 'met',
                        sort: '',
                    };
                },
                getData: function (cb) {
                    var _dt = {
                        page: this.curPage,
                        size: this.size
                    };
                    $.post(GET_DATA_API, _dt, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg);
                            return false;
                        }
                        MET.tableData = rst.data.list;
                        if (MET.curPage == 1)
                            MET.getPageNum(rst.data.count);
                        if (cb) {
                            cb();
                        }
                    });
                },
                showEdit: function (idx) {
                    MET.isEdit = !1;
                    if (idx != -1) {
                        var _dt = this.tableData[idx];
                        this.editobj.id = _dt.id;
                        this.editobj.name = _dt.name;
                        this.editobj.picPath = _dt.picPath;
                        this.editobj.sort = _dt.sort;
                        MET.isEdit = !0;
                    }
                    this.editVisible = !0;
                },
                hideEdit: function () {
                    this.initEditObj();
                    this.editVisible = !1;
                },
                delBanner: function () {
                    if (this.checkedArr.length == 0) {
                        BASE.showAlert('请选择要删除的条目');
                        return false;
                    }
                    BASE.showConfirm('确定要删除所选记录吗?', function () {
                        $('#loading').show();
                        $.post(DEL_DATA_API, {ids: MET.checkedArr}, function (rst) {
                            $('#loading').hide();
                            rst = JSON.parse(rst);
                            if (rst.status != 1) {
                                BASE.showAlert(rst.msg);
                                return false;
                            }
                            BASE.showAlert(rst.msg);
                            MET.checkedArr = [];
                            MET.getData();
                        });
                    });
                },
                saveData: function () {
                    if (MET.isLock)
                        return false;
                    if (MET.editobj.name.trim() == '') {
                        BASE.showAlert('请填写名称~');
                        return false;
                    }
                    if (!MET.isEdit && !$('#picPath').val()) {
                        BASE.showAlert('请选择要上传的图片~');
                        return false;
                    }

                    $('#editform').ajaxSubmit({
                        url: MET.isEdit ? SET_DATA_API : ADD_DATA_API,
                        beforeSubmit: function () {
                            MET.isLock = !0;
                            $('#loading').show();
                        },
                        success: function (rst) {
                            MET.isLock = !1;
                            $('#loading').hide();
                            rst = JSON.parse(rst);
                            if (rst.status != 1) {
                                BASE.showAlert(rst.msg);
                                return false;
                            }
                            BASE.showAlert(rst.msg);
                            MET.hideEdit();
                            MET.getData();
                        }
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
                        MET.curPage = num;
                        var _arr = [];
                        if (MET.curPage > 3) {
                            if (MET.curPage < MET.pageAllData.length - 2) {
                                var x = 0;
                                for (var i = MET.curPage - 3; i < MET.curPage + 2; i++) {
                                    _arr.push(MET.pageAllData[i]);
                                    console.log(MET.pageAllData[i].num + ' | ' + i + ' | ' + _arr[x].num);
                                    x++;
                                }
                            } else {
                                var count = 5;
                                if (MET.pageAllData.length < 5)
                                    count = MET.pageAllData.length;
                                for (var i = MET.pageAllData.length - count; i < MET.pageAllData.length; i++) {
                                    _arr.push(MET.pageAllData[i]);
                                }
                            }
                        } else {
                            if (MET.pageAllData.length < 6) {
                                for (var i = 0; i < MET.pageAllData.length; i++) {
                                    _arr.push(MET.pageAllData[i]);
                                }
                            }
                            else {
                                _arr = [MET.pageAllData[0], MET.pageAllData[1], MET.pageAllData[2], MET.pageAllData[3], MET.pageAllData[4]];
                            }
                        }
                        MET.pageShowData = _arr;
                    }
                    if (!!num && num <= this.pageAllData.length && num >= 0)
                        this.getData(_cb(num));
                },
            },
        });
MET.init();
$(function () {
    $('#factory').addClass('open');
    $('#factory').parents('.dropdown').addClass('open');
    $('#picPathView').click(function () {
        $('#picPath').click();
    });
    var picPathView = new uploadPreview({
        UpBtn: 'picPath',
        ImgShow: 'picPathView',
        ImgType: ['jpg', 'png'],
        ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种'
    });
});