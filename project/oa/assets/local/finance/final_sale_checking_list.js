/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var FinalListModel = new function () {
    var self_ = this;
    self_.checkList = ko.observableArray([]);
    self_.listCheck = function (data) {
        ycoa.ajaxLoadGet("/api/finance/final_sale_checking_list.php", data, function (results) {
            self_.checkList.removeAll();
            if (results != null || results != "") {
                $.each(results.list, function (index, partnar) {
                    partnar.addtime = (partnar.addtime).split(' ')[0];
                    partnar.department = "售后";
                    self_.checkList.push(partnar);
                });
                if (results.total[0].totalTransferMoney == null || results.total[0].totalTransferMoney == 0 || results.total[0].totalTransferMoney == "")
                {
                    $("#totalTransferMoney").text("￥0 元");
                } else
                {
                    $("#totalTransferMoney").text("￥" + results.total[0].totalTransferMoney + " 元");
                }
            }
        });
    };
}

function reLoadData(data) {
    FinalListModel.listCheck(data);
}

$(function () {
    $("body").on("mouseover", '.date-picker-bind-mouseover', function () {
        $(this).datepicker({autoclose: true});
    });

    /*时间控件选择后加载数据*/
    $("#current_date").change(function () {
        reLoadData({action: 1, currentDate: $("#current_date").val()});
    });

    $("#nextDay").click(function () {
        reLoadData({action: 1, currentDate: operateDate("+", "#current_date")});
    });
    $("#preDay").click(function () {
        reLoadData({action: 1, currentDate: operateDate("-", "#current_date")});
    });

    initServerDate("#current_date");

    $("#dataTable").reLoad(function () {
        reLoadData({action: 1, currentDate: $("#current_date").val()});
    });

    /*给页面绑定数据*/
    ko.applyBindings(FinalListModel, $("#dataTable")[0]);
    reLoadData({action: 1, currentDate: $("#current_date").val()});

    $("#btn_toexcel_primary").click(function () {
        var start_time = $("#start_time").val();
        var end_time = $("#end_time").val();
        if (start_time || end_time) {
            window.location.href = "/api/finance/final_sale_checking_list.php?action=10&start_time=" + start_time + "&end_time=" + end_time;
        }
    });
});

/*
 *日期加减操作 
 */
function operateDate(operate, selector) {
    var dateArray = $(selector).val().split("-");
    var curDate = new Date(dateArray[0], dateArray[1] - 1, dateArray[2]);
    if (operate === "+") {
        curDate.setDate(curDate.getDate() + 1);
    } else {
        curDate.setDate(curDate.getDate() - 1);
    }
    $(selector).val(curDate.format("yyyy-MM-dd"));
    return curDate.format("yyyy-MM-dd");
}
