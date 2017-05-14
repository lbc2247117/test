/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var ComplainTotalModel = new function ()
{
    var self_ = this;
    self_.totalList = ko.observableArray([]);
    self_.listTotal = function (data) {
        ycoa.ajaxLoadGet("/api/complain/complain_total.php", data, function (results) {
            debugger
            self_.totalList.removeAll();
            $.each(results.list, function (index, partnar) {
                debugger
                $("#showDate").val(partnar.DealTime);
                self_.totalList.push(partnar);
            });
        });
    };
}

$(function () {
    $("body").on("mouseover", '.date-picker-bind-mouseover', function () {
        $(this).datepicker({autoclose: true});
    });

    initServerDate("#add_date");

    /*给页面绑定数据*/
    ko.applyBindings(ComplainTotalModel, $("#dataTable")[0]);
    LoadSource({action: 1, currDate: $("#add_date").val()});
    /*时间控件选择后加载数据*/
    $("#add_date").change(function () {
        LoadSource({action: 1, currDate: $("#add_date").val()});
        $("#showDate").html($("#add_date").val());
    });
    /*前一天数据加载*/
    $("#preDay").click(function () {
        var dateText = $("#add_date").val();
        if (dateText == "" || dateText == null)
        {
            alert("日期时间不能为空...");
            return false;
        } else
        {
            var preDate = new Date(new Date(dateText).getTime() - 24 * 60 * 60 * 1000).format("yyyy-MM-dd");
            LoadSource({action: 1, currDate: preDate});
            $("#add_date").val(preDate);
            $("#showDate").html(preDate);
        }
    });
    /*后一天数据加载*/
    $("#nextDay").click(function () {
        var dateText = $("#add_date").val();
        if (dateText == "" || dateText == null)
        {
            alert("日期时间不能为空...");
            return false;
        } else
        {
            var preDate = new Date(new Date(dateText).getTime() + 24 * 60 * 60 * 1000).format("yyyy-MM-dd");
            LoadSource({action: 1, currDate: preDate});
            $("#add_date").val(preDate);
            $("#showDate").html(preDate);
        }
    });
});

/**
 * 向后台发送请求
 * @param {type} data
 * @returns {undefined}
 */
function LoadSource(data)
{
    ComplainTotalModel.listTotal(data);
}

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
    $(selector).val(formatDate(curDate.toLocaleDateString()));
}
