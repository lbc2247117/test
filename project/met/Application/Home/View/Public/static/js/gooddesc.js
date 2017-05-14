var
        GET_GOOD_API = 'getGoodInfo',
        GET_PARAM_API = 'getGoodParam',
        MET = new Vue({
            el: '#wrapper',
            data: {
                id: URL_PARAM('id'),
                paramList: [],
                sizeList: [],
                selSize: 0,
                goodinfo: '',
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getData: function () {
                    var _dt = {
                        id: this.id,
                    }
                    $.post(GET_GOOD_API, _dt, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg);
                            return false;
                        }
                        MET.goodinfo = rst.data.goodinfo;
                        MET.sizeList = rst.data.goodSize;
                        MET.getParam();
                    })
                },
                getParam: function () {
                    if (MET.sizeList.length == 0)
                        return false;
                    var _dt = MET.sizeList[MET.selSize];
                    var sid = _dt.id;
                    $.post(GET_PARAM_API, {gid: MET.id, sid: sid}, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != 1)
                            return false;
                        MET.paramList = rst.data.goodParam;
                    });
                },
                bgImg: function (url) {
                    return url ? ('url(' + url + ')') : '';
                },
            },
        });
MET.init();
$(function () {
    $('#product').addClass('active');
});