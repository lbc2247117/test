var SaleStatisticsDayViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.saleStatisticsDayList = ko.observableArray([]);
    self_.listSaleStatisticsDay = function (data) {
        ycoa.ajaxLoadGet("/api/sale_soft/saleStatisticsDay.php", data, function (results) {
            self_.saleStatisticsDayList.removeAll();
            $.each(results.list, function (index, saleStatistics) {
                saleStatistics.edit = ycoa.SESSION.PERMIT.hasPagePermitButton("3030603") && saleStatistics.edit;
                saleStatistics.dele = ycoa.SESSION.PERMIT.hasPagePermitButton("3030602") && saleStatistics.dele;
                saleStatistics.show = ycoa.SESSION.PERMIT.hasPagePermitButton("3030604");
                saleStatistics.bgcolor = '';
                saleStatistics.index = (index + 1);
                self_.saleStatisticsDayList.push(saleStatistics);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                reLoadData({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: pageSize, searchTime: $('#searchTime').val(), searchName: $("#searchUserName").val(), searchChannel: $("#searchChannel").val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName()});
            }, function (pageNo) {
                reLoadData({action: 1, pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(), searchTime: $('#searchTime').val(), searchChannel: $("#searchChannel").val(), searchName: $("#searchUserName").val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName()});
            });
            if (results.is_manager) {
                create_total_tr_data(results.total_month_count);
            }
        });
    };
    self_.delSaleStatistics = function (saleStatistics) {
        ycoa.UI.messageBox.confirm('确认删除？', function (del) {
            if (del) {
                saleStatistics.action = 2;
                ycoa.ajaxLoadPost("/api/sale_soft/saleStatisticsDay.php", JSON.stringify(saleStatistics), function (result) {
                    if (result.code == 0) {
                        ycoa.UI.toast.success(result.msg);
                        reLoadData({});
                    } else {
                        ycoa.UI.toast.error(result.msg);
                    }
                    ycoa.UI.block.hide();
                });

            }
        });
    };
    self_.editSaleStatistics = function (saleStatistics) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + saleStatistics.id).show();
        $("#submit_" + saleStatistics.id).show();
        $("#cancel_" + saleStatistics.id).show();
        $("#tr_" + saleStatistics.id + " input,#tr_" + saleStatistics.id + " textarea").removeAttr("disabled");
    };
    self_.cancelTr = function (saleStatistics) {
        $("#tr_" + saleStatistics.id).hide();
        $("#submit_" + saleStatistics.id).hide();
        $("#cancel_" + saleStatistics.id).hide();
    };
    self_.showSaleStatistics = function (saleStatistics) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + saleStatistics.id).show();
        $("#cancel_" + saleStatistics.id).show();
        $("#tr_" + saleStatistics.id + " .imgarea").html(saleStatistics.attachment);
        $("#tr_" + saleStatistics.id + " input,#tr_" + saleStatistics.id + " textarea").attr("disabled", "");
    };
}();
$(function () {
    $("#dataTable").sort(function (data) {
        reLoadData({action: 1, sort: data.sort, sortname: data.sortname, pagesize: ycoa.SESSION.PAGE.getPageSize(), pageno: ycoa.SESSION.PAGE.getPageNo(), searchTime: $('#searchTime').val(), searchName: $("#searchUserName").val(), searchChannel: $("#searchChannel").val()});
    });
    $("#dataTable").reLoad(function () {
        reLoadData({action: 1});
        $('#searchUserName').val('');
        $("#searchChannel").val("");
        $('#searchTime').val('');
    });
    if (ycoa.SESSION.PERMIT.hasPagePermitButton("3030605")) {
        $("#dataTable").searchAutoStatus(array['channel'], function (d) {
            reLoadData({action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pagesize: ycoa.SESSION.PAGE.getPageSize(), pageno: ycoa.SESSION.PAGE.getPageNo(), searchChannel: d.id, searchTime: $('#searchTime').val(), searchName: $("#searchName").val()});
            $("#searchChannel").val(d.id);
        }, '按渠道筛选');
    }
    $("#dataTable").searchUserName(function (name) {
        reLoadData({action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pagesize: ycoa.SESSION.PAGE.getPageSize(), pageno: ycoa.SESSION.PAGE.getPageNo(), searchChannel: $("#searchChannel").val(), searchTime: $('#searchTime').val(), searchName: name});
    }, '按名称查找');
    $("#dataTable").searchDateTime(function (time) {
        reLoadData({action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pagesize: ycoa.SESSION.PAGE.getPageSize(), pageno: ycoa.SESSION.PAGE.getPageNo(), searchChannel: $("#searchChannel").val(), searchTime: time, searchName: $("#searchName").val()});
        $("#searchTime").val(time);
    }, '按照日期查询');
    ko.applyBindings(SaleStatisticsDayViewModel, $("#dataTable")[0]);
    reLoadData({action: 1});
    $(".date-picker-bind-mouseover").datepicker({autoclose: true});
    $("#dataTable thead input[id='checkall']").change(function () {
        if ($(this).prop("checked")) {
            $("#dataTable tbody input[type='checkbox']").prop("checked", "checked");
        } else {
            $("#dataTable tbody input[type='checkbox']").removeAttr("checked");
        }
    });
    $("#btn_submit_primary").click(function () {
        $("#add_sale_statistics_day_form").submit();
    });
    $("#open_dialog_btn").click(function () {
        $("#add_sale_statistics_day_form input,#add_sale_statistics_day_form textarea").each(function () {
            if (!$(this).hasClass("not-clear")) {
                $(this).val("");
            }
        });
        $(".has-error,.has-success").each(function () {
            $(this).removeClass("has-error").removeClass("has-success");
        });
        $(".fa-warning,.fa-check").each(function () {
            $(this).removeClass("fa-warning").removeClass("fa-check");
        });
    });
    $("#start_time,#end_time").val(new Date().format("yyyy-MM-dd"));
    $("#btn_toexcel_primary").live("click", function () {
        var start_time = $("#toexcel_form #start_time").val();
        var end_time = $("#toexcel_form #end_time").val();
        var channel = $("#toexcel_form #channel").val();
        if (start_time || end_time) {
            location.href = "/api/sale_soft/saleStatisticsDay.php?channel=" + channel + "&start_time=" + start_time + "&end_time=" + end_time + "&action=11";
        }
    });
    $(".sale_statistics_submit_btn").live("click", function () {
        var formid = "form_" + $(this).attr("val");
        var data = $("#" + formid).serializeJson();
        data.action = 3;
        data = JSON.stringify(data);
        ycoa.ajaxLoadPost("/api/sale_soft/saleStatisticsDay.php", data, function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({action: 1});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    });
    $('.popovers').popover();
    $("#add_sale_statistics_day_form #userid").val(ycoa.user.userid());
    $("#add_sale_statistics_day_form #username").val(ycoa.user.username());
});
function reLoadData(data) {
    data.action = 1;
    SaleStatisticsDayViewModel.listSaleStatisticsDay(data);
}

function create_total_tr_data(total_month_count) {
    $("#saleStatisticsDayList").find("#total_tr_data").remove();
    var html = "<tr style='background:#ffff99' id='total_tr_data'>"
    html += "<td></td>";
    html += "<td>0</td>";
    html += "<td>" + (total_month_count.addtime) + "</td>";
    html += "<td><span style='color:red;'>当日统计<\span></td>";
    html += "<td></td>";
    html += "<td>" + (total_month_count.into_count) + "</td>";
    html += "<td>" + (total_month_count.accept_count) + "</td>";
    html += "<td>" + (total_month_count.deal_count) + "</td>";
    html += "<td>" + (total_month_count.elderly_deal_count) + "</td>";
    html += "<td>" + (total_month_count.timely_count) + "</td>";
    html += "<td>" + (total_month_count.amount) + "</td>";
    html += "<td style='color:red;'>" + (total_month_count.commission) + "</td>";
    html += "<td>" + (total_month_count.loss_number) + "</td>";
    html += "<td style='color:blue;'>" + (total_month_count.loss_rate) + "</td>";
    html += "<td style='color:green;'>" + (total_month_count.timely_rate) + "</td>";
    html += "<td style='color:green;'>" + (total_month_count.timely_turnover_ratio) + "</td>";
    html += "<td style='color:red;'>" + (total_month_count.conversion_rate) + "</td>";
    html += "<td>" + (total_month_count.average_price) + "</td>";
    html += "<td></td>";
    html += "</tr>";
    $("#saleStatisticsDayList").prepend(html);
}

var array = {
    channel: [{id: '旺旺', text: '旺旺'}, {id: '百度', text: '百度'}, {id: '360', text: '360'}, {id: 'UC神马', text: 'UC神马'}, {id: '搜狗', text: '搜狗'}, {id: '优化站', text: '优化站'}]
};