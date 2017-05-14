var
        GET_DATA_API = 'getProductParamList',
        SET_DATA_API = 'setProductParam',
        ADD_DATA_API = 'addProductParam',
        DEL_DATA_API = 'delProductParam',
        GET_PARAM_TYPE_API = 'getParamTypeList',
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
                sid: URL_PARAM('sid'),
                gid: URL_PARAM('id'),
                paramtype: -1,
                paramtypes: [],
                editParamTypes: [],
            },
            methods: {
                init: function () {
                    this.initEditObj();
                    this.getParamType();
                    this.getData();
                },
                initEditObj: function () {
                    this.editobj = {
                        id: '',
                        pid: '',
                        key: '',
                        value: '',
                    };
                },
                getParamType: function () {
                    $.post(GET_PARAM_TYPE_API, {}, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status == 1) {
                            MET.paramtypes.push({id: -1, name: '全部参数类型'});
                            MET.editParamTypes = rst.data.list;
                            for (var i = 0; i < rst.data.list.length; i++) {
                                MET.paramtypes.push({id: rst.data.list[i].id, name: rst.data.list[i].name});
                            }
                        }
                    });
                },
                getData: function (cb) {
                    var _dt = {
                        sid: this.sid,
                        pid: this.paramtype,
                        page: this.curPage,
                        size: this.size,
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
                searchFn: function () {
                    this.curPage = 1;
                    this.checkedArr = [];
                    this.getData();
                },
                goback: function () {
                    history.go(-1);
                },
                showEdit: function (idx) {
                    MET.isEdit = !1;
                    if (idx != -1) {
                        var _dt = this.tableData[idx];
                        this.editobj.id = _dt.id;
                        this.editobj.key = _dt.skey;
                        this.editobj.value = _dt.value;
                        this.editobj.pid = _dt.pid;
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
                    BASE.showConfirm('确定删除选择的条目吗?', function () {
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
                    if (MET.editobj.pid === undefined || MET.editobj.pid.trim() == '') {
                        BASE.showAlert('请选择参数类型');
                        return false;
                    }
                    if (MET.editobj.key.trim() == '') {
                        BASE.showAlert('请填写参数名');
                        return false;
                    }
                    if (MET.editobj.value.trim() == '') {
                        BASE.showAlert('请填写参数值');
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
                            MET.isEdit ? MET.hideEdit() : '';
                            MET.getData();
                        }
                    });
                },
                upProduct: function (id) {
                    $.post(UP_DATA_API, {id: id}, function (rst) {
                        rst = JSON.parse(rst);
                        BASE.showAlert(rst.msg);
                        MET.getData();
                    });
                },
                downProduct: function (id) {
                    $.post(DOWN_DATA_API, {id: id}, function (rst) {
                        rst = JSON.parse(rst);
                        BASE.showAlert(rst.msg);
                        MET.getData();
                    });
                },
                showParam: function (id) {
                    window.location.href = 'product_param.html?id=' + id;
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
    $('#product-style').addClass('open');
    $('#product-style').parents('.dropdown').addClass('open');
});