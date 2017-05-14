
var
        GET_DATA_API = '../Score/selectTicketById',
        ADD_TICKET = '../Score/addTicket',
        LAIA = new Vue({
            el: '#scroller',
            data: {
                lon: SEARCH_LON,
                lat: SEARCH_LAT,
                id: SEARCH_ID,
                pic: '',
                name: '',
                price: '',
                remark: '',
                comment: '',
                person2: '',
                person1: '',
                ruleRemark: '',
                rangeRemark: '',
                includeRemark: '',
                tel: '',
                browser: [],
            },
            methods: {
                init: function () {
                    this.getData();
                    this.browser = BROWSER.versions;
                },
                getData: function () {
                    var _dt = {
                        lon: LAIA.lon,
                        lat: LAIA.lat,
                        id: LAIA.id,
                    };
                    $.post(GET_DATA_API, _dt, function (rst) {
                        if (typeof rst != 'object')
                            rst = $.parseJSON(rst);
                        if (rst.status == '1' && rst.data) {
                            LAIA.listData(rst.data);
                        } else {
                            BASE.showAlert(rst.msg);
                        }
                    });
                },
                listData: function (data) {
                    LAIA.pic = data.pic;
                    LAIA.name = data.name;
                    LAIA.price = data.price;
                    LAIA.person1 = data.person1;
                    LAIA.person2 = data.person2;
                    LAIA.remark = data.remark;
                    LAIA.ruleRemark = data.ruleRemark;
                    LAIA.rangeRemark = data.rangeRemark;
                    LAIA.includeRemark = data.includeRemark;
                    LAIA.id = data.id;
                    LAIA.tel = data.tel;
                    this.remark = this.remark.replace(/\n/ig, "<br/>");
                    this.remark = this.remark.replace(/\s/ig, "&nbsp;");
                    this.ruleRemark = this.ruleRemark.replace(/\n/ig, "<br/>");
                    this.ruleRemark = this.ruleRemark.replace(/\s/ig, "&nbsp;");
                    this.rangeRemark = this.rangeRemark.replace(/\n/ig, "<br/>");
                    this.rangeRemark = this.rangeRemark.replace(/\s/ig, "&nbsp;");
                    this.includeRemark = this.includeRemark.replace(/\n/ig, "<br/>");
                    this.includeRemark = this.includeRemark.replace(/\s/ig, "&nbsp;");

                },
                bgImg: function (url) {
                    return url ? ('url(' + url + ')') : '';
                },
                seekBuy: function () {
                    var _dt = {
                        id: LAIA.id,
                        type: 1
                    };
                    $.post(ADD_TICKET, _dt, function (rst) {

                    });
                }
            }
        });
$(function () {
    LAIA.init();
    if (!SEARCH_INAPP) {
        $('#navBack').off('click');
        $('#navBack').on('click', function () {
            var _search = '?lon=' + SEARCH_LON + '&lat=' + SEARCH_LAT;
            window.location.href = 'sce.html' + _search;
        });
    }
});