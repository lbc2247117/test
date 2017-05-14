var add_day = '', rcept_account;
var FinalSaleListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.finalList = ko.observableArray([]);
    self_.listFinal = function (data) {
        ycoa.ajaxLoadGet("/api/finance/final_sale_checking.php", data, function (results) {
            debugger
            self_.finalList.removeAll();
            $('#curTime').val(results.curTime);
            if (results.curTime != "1")
            {
                $('#add_date').val(results.curTime);
                add_day = results.curTime;
            }
            $.each(results.list, function (index, final) {

                final.dele = 0;//审核
                final.edit = 0;//反审核
                final.reject = 0;//驳回
                final.cancel = 0;//取消驳回
                if (final.st_is_approve == 1) {
                    final.dele = 1;
                    final.reject = 1;
                } else if (final.st_is_approve == 2) {
                    final.edit = 1;
                } else {
                    final.dele = 1;
                    final.cancel = 1;
                }
                final.add_time = (final.add_time).split(' ')[0];
                if (final.st_is_approve == 1) {
                    final.statusTxt = '待审核';
                    final.typeCss = 1;
                }
                else if (final.st_is_approve == 2) {
                    final.statusTxt = '已审核';
                    final.typeCss = 0;
                } else if (final.st_is_approve == 3) {
                    final.statusTxt = '已驳回';
                    final.typeCss = 1;
                }
                self_.finalList.push(final);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                var data = {
                    action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: pageSize, curTime: $('#curTime').val(), review: $('#isVerify').val(), payType: $('#payType').val(), workName: $('#workName').val(), account: $('#account').val()
                };
                reLoadData(data);
            }, function (pageNo) {
                var data = {
                    action: 1, pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(), curTime: $('#curTime').val(), review: $('#isVerify').val(), payType: $('#payType').val(), workName: $('#workName').val(), account: $('#account').val()
                };
                reLoadData(data);
            });

        });
    };

    self_.rejectFinal = function (thirdpartnar) {
        $("#genid").val(thirdpartnar.id);
        $("#rejectModal").modal("show");
    };

    self_.delFinal = function (thirdpartnar) {
        var ides = new Array();
        ycoa.UI.messageBox.confirm('确认财务审核吗？', function (btn) {
            if (btn) {
                ides.push(thirdpartnar.id);
                var obj = new Object;
                obj.id = ides;
                //obj.receivMoney=thirdpartnar.payment_amount;
                obj.action = 1;
                obj.add_time = (thirdpartnar.add_time).split(' ')[0];
                ycoa.ajaxLoadPost("/api/finance/final_sale_checking.php", JSON.stringify(obj), function (result) {
                    debugger
                    if (result.code == 0) {
                        ycoa.UI.toast.success(result.msg);
                    } else {
                        ycoa.UI.toast.error(result.msg);
                    }
                    reLoadData({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), curTime: $('#curTime').val(), review: $('#isVerify').val(), payType: $('#payType').val(), workName: $('#workName').val(), account: $('#account').val()});
                });
            }
        });
    };
    self_.editFinal = function (thirdpartnar) {
        var ides = new Array();
        var obj = new Object;
        ides.push(thirdpartnar.id);
        obj.action = 2;
        obj.id = ides;
        obj.qq = thirdpartnar.qq;
        ycoa.ajaxLoadPost("/api/finance/final_sale_checking.php", JSON.stringify(obj), function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success("操作成功~");

            } else {
                ycoa.UI.toast.error(result.msg);
            }
            reLoadData({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), curTime: $('#curTime').val(), review: $('#isVerify').val(), payType: $('#payType').val(), workName: $('#workName').val(), account: $('#account').val()});
        });
    };
    self_.cancelFinal = function (thirdpartnar) {
        var obj = new Object;
        obj.action = 4;
        obj.id = thirdpartnar.id;
        ycoa.ajaxLoadPost("/api/finance/final_sale_checking.php", JSON.stringify(obj), function (result) {
            debugger
            if (result.code == 0) {
                ycoa.UI.toast.success("操作成功~");
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            reLoadData({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), curTime: $('#curTime').val(), review: $('#isVerify').val(), payType: $('#payType').val(), workName: $('#workName').val(), account: $('#account').val()});
        });
    }

};
function reLoadData(data) {
    FinalSaleListViewModel.listFinal(data);
}
$(function () {
    initServerDate("#curTime");
    ko.applyBindings(FinalSaleListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1, review: $('#isVerify').val()});

    $('#preDay').click(function () {
        reLoadData({action: 1, pageno: 1, pagesize: ycoa.SESSION.PAGE.getPageSize(), curTime: $('#curTime').val(), review: $('#isVerify').val(), inner: 1, payType: $('#payType').val(), workName: $('#workName').val(), account: $('#account').val()});
    });
    $('#nextDay').click(function () {
        reLoadData({action: 1, pageno: 1, pagesize: ycoa.SESSION.PAGE.getPageSize(), curTime: $('#curTime').val(), review: $('#isVerify').val(), inner: 2, payType: $('#payType').val(), workName: $('#workName').val(), account: $('#account').val()});
    });
    $("#btn_toexcel_primary").click(function () {
        var start_time = $("#start_time").val();
        var end_time = $("#end_time").val();
        if (start_time || end_time) {
            window.location.href = "/api/finance/final_sale_checking.php?action=10&start_time=" + start_time + "&end_time=" + end_time;
        }
    });


    $("body").on("mouseover", '.date-picker-bind-mouseover', function () {
        $(this).datepicker({autoclose: true});

    });

    $("#dataTable").reLoad(function () {
        reLoadData({action: 1});
        $('#isVerify').val('');
        $('#account').val('');
        $('#payType').val('');
        $('#workName').val('');
        $('#add_date').val('');
    });
    $("#dataTable").searchUserName(function (name) {
        reLoadData({action: 1, pageno: 1, pagesize: ycoa.SESSION.PAGE.getPageSize(), curTime: $('#curTime').val(), review: $('#isVerify').val(), payType: $('#payType').val(), workName: name, account: $('#account').val()});
    }, '关键字', 'workName');
    $("#dataTable").searchAutoStatus([{id: 1, text: '未审'}, {id: 2, text: '已审'}, {id: 3, text: '已驳回'}, {id: 0, text: '全部'}], function (d) {
        $('#isVerify').val(d.id);
    }, '是否审核');
    $("#dataTable").searchAutoStatus([{id: '银行卡转账', text: '银行卡转账'}, {id: '信用卡', text: '信用卡'}, {id: '花呗', text: '花呗'}, {id: '支付宝', text: '支付宝'}, {id: '微信', text: '微信'}, {id: '财付通', text: '财付通'}, {id: '全部', text: '全部'}], function (d) {
        $('#payType').val(d.text);
    }, '支付方式');
    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 9}, function (res) {
        $("#dataTable").searchAutoStatus(res, function (d) {
            $('#account').val(d.text);
        }, '收款账号');
        rcept_account = res;
    });
    $("#btn_verify_primary").click(function () {
        var ides = new Array();
        var obj = new Object;
        ides.push($("#genid").val());
        obj.id = ides;
        obj.content = $("#reject").val();
        obj.action = 3;
        ycoa.ajaxLoadPost("/api/finance/final_sale_checking.php", JSON.stringify(obj), function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            reLoadData({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), curTime: $('#curTime').val(), review: $('#isVerify').val(), payType: $('#payType').val(), workName: $('#workName').val(), account: $('#account').val()});
        });
        $("#rejectModal").modal("hide");
    });


    $('#batchVerify').click(function () {
        var ides = new Array();
        var currentDate = null;
        var flag = true;
        $("#dataTable tbody input[type='checkbox']:checked").each(function (index) {
            if (index == 0) {
                currentDate = $(this).parent().next().next().text();
            } else {
                if (currentDate != $(this).parent().next().next().text()) {
                    ycoa.UI.toast.error("不能勾选两天的时间进行批量审核");
                    flag = false;
                    return false;
                }
            }
            ides.push($(this).val());
        });
        if (flag) {
            if (ides.length > 0)
            {
                var obj = new Object;
                obj.action = 1;
                obj.id = ides;
                obj.add_time = currentDate;
                ycoa.ajaxLoadPost("/api/finance/final_sale_checking.php", JSON.stringify(obj), function (result) {
                    if (result.code == 0) {
                        ycoa.UI.toast.success(result.msg);
                    } else {
                        ycoa.UI.toast.error(result.msg);
                    }
                    reLoadData({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), curTime: $('#curTime').val(), review: $('#isVerify').val(), payType: $('#payType').val(), workName: $('#workName').val(), account: $('#account').val()});
                });
            }else
            {
                ycoa.UI.toast.error("请选择需要审核的数据...");
            }
        }
    });
    $('#add_date').change(function () {
        if (add_day != $('#add_date').val()) {
            add_day = $('#add_date').val();
            reLoadData({action: 1, pageno: 1, pagesize: ycoa.SESSION.PAGE.getPageSize(), curTime: $('#curTime').val(), review: $('#isVerify').val(), payType: $('#payType').val(), workName: $('#workName').val(), account: $('#account').val(), searchTime: $('#add_date').val()});
        }

    });


    $("body").on("click", "#dataTable tbody .checkbox", function () {
        countMoney(8);
    });

    /*
     * 全部选择或者全部取消
     */
    $("#dataTable thead input[id='checkall']").change(function () {
        if ($(this).prop("checked")) {
            $("#dataTable tbody input[type='checkbox']").prop("checked", "checked");
        } else {
            $("#dataTable tbody input[type='checkbox']").removeAttr("checked");
        }
        countMoney(8);
    });
});