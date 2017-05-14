var aftersalesListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.aftersaleslist = ko.observableArray([]);
    self_.listaftersales = function (data) {
        ycoa.ajaxLoadGet("/api/sale/aftersales.php", data, function (results) {
            debugger
            $('#timemonth').val(results.timemonth);
            $('#timeyear').val(results.timeyear);
            $("#startDate").val(results.searchStartTime);
            $("#endDate").val(results.searchEndTime);
            $("#searchStartTime").val(results.searchStartTime);
            $("#searchEndTime").val(results.searchEndTime);
            self_.aftersaleslist.removeAll();
            $.each(results.list, function (index, aftersales) {
                //generationOperation.show = ycoa.SESSION.PERMIT.hasPagePermitButton('3050404');
                self_.aftersaleslist.push(aftersales);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            results.nototal=1;
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                var data = {
                    timemonth: $("#timemonth").val(), timeyear: $('#timeyear').val(), action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                };
                if (data.searchStartTime || data.searchEndTime) {
                    data.searchTime = "";
                }
                reLoadData(data);
            }, function (pageNo) {
                var data = {
                    timemonth: $("#timemonth").val(), timeyear: $('#timeyear').val(), action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                };
                if (data.searchStartTime || data.searchEndTime) {
                    data.searchTime = "";
                }
                reLoadData(data);
            });
            $("#page_size").val(results.page_size);
            totalMoney();
        });
    };
}();
$(function () {
    ko.applyBindings(aftersalesListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1});
    $("#dataTable").reLoad(function () {
        $("#workName").val("");
        $("#ptqqName").val("");
        $("#searchDateTime").val("");
        $('#searchStartTime').val("");
        $('#searchEndTime').val("");
        reLoadData({action: 1});
    });
    $("#dataTable").searchDateTimeSlot(function () {
        var data = {
            timemonth: $("#timemonth").val(), timeyear: $('#timeyear').val(), action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    });
    $("#dataTable").searchDateTime(function () {
        var data = {
            timemonth: $("#timemonth").val(), timeyear: $('#timeyear').val(), action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    });
    $('#preyear').click(function () {
        var data = {
            inneraction: '1', timemonth: $("#timemonth").val(), timeyear: $('#timeyear').val(), action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        reLoadData(data);
    });
    $('#nextyear').click(function () {
        var data = {
            inneraction: '4', timemonth: $("#timemonth").val(), timeyear: $('#timeyear').val(), action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        reLoadData(data);
    });
    $('#premonth').click(function () {
        var data = {
            inneraction: '2', timemonth: $("#timemonth").val(), timeyear: $('#timeyear').val(), action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        reLoadData(data);
    });
    $('#nextmonth').click(function () {
        var data = {
            inneraction: '3', timemonth: $("#timemonth").val(), timeyear: $('#timeyear').val(), action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        reLoadData(data);
    });

    $("#ExportToExcel").click(function () {
        window.location.href = "/api/sale/aftersales.php?action=2";
    });
});
function reLoadData(data) {
    aftersalesListViewModel.listaftersales(data);
}

//计算所有金额列合计
function totalMoney() {
    var receptTotal = 0;
    var fillTotal = 0;
    var afterTotal = 0;
    var totalmoneyTotal = 0;
    var receptNum = 0;
    var zxTotal = 0;
    var kcTotal = 0;
    var hdTotal = 0;
    var hyTotal = 0;
    var vipTotal = 0;
    var hisTotal = 0;
    var hisMoney = 0;
    $("#dataTable tbody tr").each(function () {
        receptNum += Number($(this).find("td").eq(2).text());
        receptTotal += Number($(this).find("td").eq(3).text());
        fillTotal += Number($(this).find("td").eq(4).text());
        afterTotal += Number($(this).find("td").eq(5).text());
        zxTotal += Number($(this).find("td").eq(6).text());
        kcTotal += Number($(this).find("td").eq(7).text());
        hdTotal += Number($(this).find("td").eq(8).text());
        hyTotal += Number($(this).find("td").eq(9).text());
        vipTotal += Number($(this).find("td").eq(10).text());
        totalmoneyTotal += Number($(this).find("td").eq(11).text());
        hisTotal += Number($(this).find("td").eq(14).text());
        hisMoney += Number($(this).find("td").eq(15).text());
    });
    $("#dataTable thead tr.total td").eq(2).text(receptNum);
    $("#dataTable thead tr.total td").eq(3).text(receptTotal);
    $("#dataTable thead tr.total td").eq(4).text(fillTotal.toFixed(2));
    $("#dataTable thead tr.total td").eq(5).text(afterTotal);
    $("#dataTable thead tr.total td").eq(6).text(zxTotal);
    $("#dataTable thead tr.total td").eq(7).text(kcTotal);
    $("#dataTable thead tr.total td").eq(8).text(hdTotal);
    $("#dataTable thead tr.total td").eq(9).text(hyTotal);
    $("#dataTable thead tr.total td").eq(10).text(vipTotal);
    $("#dataTable thead tr.total td").eq(11).text(totalmoneyTotal);
    $("#dataTable thead tr.total td").eq(14).text(hisTotal);
    $("#dataTable thead tr.total td").eq(15).text(hisMoney);
}

