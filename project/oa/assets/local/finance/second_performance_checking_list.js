/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var ThirdPartnarListModel = new function () {
    var self_ = this;
    self_.partnarList = ko.observableArray([]);
    self_.listPartnar = function (data) {
        ycoa.ajaxLoadGet("/api/finance/second_performance_checking_list.php", data, function (results) {
            self_.partnarList.removeAll();
            if (results != null || results != "") {
                $.each(results.list, function (index, partnar) {
//                    if (index == results.list.length - 1) {
//                        if (partnar.flag == 0) {
//                            partnar.css = 0;
//                        } else {
//                            partnar.css = 1;
//                        }
//                    } else {
//                        if (results.list[index].gen_id == results.list[index + 1].gen_id) {
//                            partnar.css = 0;
//                        } else {
//                            if (partnar.flag == 0) {
//                                partnar.css = 0;
//                            } else {
//                                partnar.css = 1;
//                            }
//                        }
//                    }

                    partnar.addtime = (partnar.addtime).split(' ')[0];
                    partnar.department = "售后";
                    self_.partnarList.push(partnar);
                });
                $("#totalSubmitMoney").text("￥" + results.total[0].totalSubmitMoney + " 元");
                $("#totalTransferMoney").text("￥" + results.total[0].totalSubmitMoney + " 元");
            }
        });
    };
}

function reLoadData(data) {
    ThirdPartnarListModel.listPartnar(data);
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
    ko.applyBindings(ThirdPartnarListModel, $("#dataTable")[0]);
    reLoadData({action: 1, currentDate: $("#current_date").val()});

    $("#btn_toexcel_primary").click(function () {
        var start_time = $("#start_time").val();
        var end_time = $("#end_time").val();
        if (start_time || end_time) {
            window.location.href = "/api/finance/second_performance_checking_list.php?action=10&start_time=" + start_time + "&end_time=" + end_time;
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
