/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var array = {
    isVerify: [{id: '1', text: '是'}, {id: '0', text: '否'}],
    isArrears: [{id: '1', text: '定金'}, {id: '0', text: '补款'}],
    payment_method: [{id: '银行卡转款', text: '银行卡转款'}, {id: '信用卡', text: '信用卡'}, {id: '花呗', text: '花呗'}, {id: '支付宝', text: '支付宝'}, {id: '微信', text: '微信'}, {id: '财付通', text: '财付通'}]
};

var ThirdPartnarModel = new function ()
{
    var self_ = this;
    self_.partnarList = ko.observableArray([]);
    self_.listPartnar = function (data) {
        ycoa.ajaxLoadGet("/api/finance/thirdpartnar.php", data, function (results) {
            self_.partnarList.removeAll();
            $('#totalmoney').html('月汇总金额:' + results.totalmoney + '元');
            $.each(results.list, function (index, partnar) {
                partnar.addtime = new Date(partnar.addtime).format("yyyy-MM-dd");
                self_.partnarList.push(partnar);
            });
        });
    };
}

$(function () {
    $("#thirdpartnar_checking_form #isArrears").autoRadio(array['isArrears']);
    $("#thirdpartnar_checking_form #isVerify").autoRadio(array['isVerify']);
    $("#thirdpartnar_checking_form #payment_method").autoRadio(array['payment_method']);

    $("body").on("mouseover", '.date-picker-bind-mouseover', function () {
        $(this).datepicker({autoclose: true});
    });

    initServerDate("#add_date");

    /*给页面绑定数据*/
    ko.applyBindings(ThirdPartnarModel, $("#dataTable")[0]);
    LoadSource({action: 1, currDate: $("#add_date").val()});
    /*
     * 点击支付宝选项时,顶部上传支付宝账单按钮才会显示出来
     */
    $("body").on("click", '.auto_radio_ul li', function () {
        if ($(this).text() == "支付宝") {
            $("#uploadAliPay").show();
        } else {
            $("#uploadAliPay").hide();
        }
    });
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
    ThirdPartnarModel.listPartnar(data);
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
