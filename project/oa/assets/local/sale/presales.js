var presalesListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.presalesList = ko.observableArray([]);
    self_.listPresales = function (data) {
        ycoa.ajaxLoadGet("/api/sale/presales.php", data, function (results) {
            debugger
            $('#timemonth').val(results.timemonth);
            $('#timeyear').val(results.timeyear);
            $("#startDate").val(results.searchStartTime);
            $("#endDate").val(results.searchEndTime);
            $("#searchStartTime").val(results.searchStartTime);
            $("#searchEndTime").val(results.searchEndTime);
            self_.presalesList.removeAll();
            $.each(results.list, function (index, presales) {
                //generationOperation.show = ycoa.SESSION.PERMIT.hasPagePermitButton('3050404');
                self_.presalesList.push(presales);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            results.nototal = 1;
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                var data = {
                    timemonth: $("#timemonth").val(), timeyear: $('#timeyear').val(), action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: pageSize, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), workName: $("#workName").val(), ptqqName: $("#ptqqName").val()};

                reLoadData(data);
            }, function (pageNo) {
                var data = {
                    timemonth: $("#timemonth").val(), timeyear: $('#timeyear').val(), action: 1, pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), workName: $("#workName").val(), ptqqName: $("#ptqqName").val()};

                reLoadData(data);
            });
            $("#page_size").val(results.page_size);
            totalMoney();
        });
    };
}();
$(function () {
    ko.applyBindings(presalesListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1});
    $("#dataTable").reLoad(function () {
        $("#workName").val("");
        $("#ptqqName").val("");
        $("#searchDateTime").val("");
        $('#searchStartTime').val("");
        $('#searchEndTime').val("");
        reLoadData({action: 1});
    });
    $("#dataTable").searchDateTimeSlot(function (d) {
        var data = {
            timemonth: $("#timemonth").val(), timeyear: $('#timeyear').val(), action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize()};
        reLoadData(data);
    });
    $("#dataTable").searchDateTime(function (d) {
        var data = {
            timemonth: $("#timemonth").val(), timeyear: $('#timeyear').val(), action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize()};
        reLoadData(data);
    });
//    $('#preyear').click(function () {
//        var data = {
//            inneraction: '1', timemonth: $("#timemonth").val(), timeyear: $('#timeyear').val(), action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize()};
//        reLoadData(data);
//    });
//    $('#nextyear').click(function () {
//        var data = {
//            inneraction: '4', timemonth: $("#timemonth").val(), timeyear: $('#timeyear').val(), action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize()};
//        reLoadData(data);
//    });
    $('#premonth').click(function () {
        var data = {
            inneraction: '2', timemonth: $("#timemonth").val(), timeyear: $('#timeyear').val(), action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize()};
        reLoadData(data);
    });
    $('#nextmonth').click(function () {
        var data = {
            inneraction: '3', timemonth: $("#timemonth").val(), timeyear: $('#timeyear').val(), action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize()};
        reLoadData(data);
    });
});
function reLoadData(data) {
    presalesListViewModel.listPresales(data);

}
//计算所有金额列合计
function totalMoney() {
    var dingjin = 0;
    var bukuan = 0;
    var jiedai = 0;
    var jiedaishu = 0;
    var chengjiao = 0;
    var zhuanhualv = 0;
    var second = 0;
    var lastZhuangHuaLv = "";
    $("#dataTable tbody tr").each(function () {
        jiedai += Number($(this).find("td").eq(2).text());
        dingjin += Number($(this).find("td").eq(3).text());
        bukuan += Number($(this).find("td").eq(4).text());
        second += Number($(this).find("td").eq(5).text());
        jiedaishu += Number($(this).find("td").eq(6).text());
        chengjiao += Number($(this).find("td").eq(7).text());
    });
    $("#dataTable thead tr.total td").eq(2).text(jiedai);
    $("#dataTable thead tr.total td").eq(3).text(dingjin);
    $("#dataTable thead tr.total td").eq(4).text(bukuan);
    $("#dataTable thead tr.total td").eq(5).text(second);
    $("#dataTable thead tr.total td").eq(6).text(jiedaishu);
    $("#dataTable thead tr.total td").eq(7).text(chengjiao);
    if (chengjiao == 0)
    {
        lastZhuangHuaLv = "0%";
    } else
    {
        lastZhuangHuaLv = (parseFloat((chengjiao / jiedaishu).toFixed(4)) * 100).toFixed(2) + "%";
    }
    $("#dataTable thead tr.total td").eq(8).text(lastZhuangHuaLv);
}