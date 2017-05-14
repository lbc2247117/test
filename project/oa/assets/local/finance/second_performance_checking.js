var add_day = '', rcept_account;
var thirdpartnarListViewModel = new function () {

    var self_ = this;

    self_.list = ko.observable("list");
    self_.thirdpartnarList = ko.observableArray([]);
    self_.listthirdpartnar = function (data) {
        ycoa.ajaxLoadGet("/api/finance/second_performance_checking.php", data, function (results) {
            self_.thirdpartnarList.removeAll();
            $('#curTime').val(results.curTime);
            if (results.curTime != "1")
            {
                $('#add_date').val(results.curTime);
                add_day = results.curTime;
            }
            $.each(results.list, function (index, thirdpartnar) {
                thirdpartnar.dele = 0;//审核
                thirdpartnar.edit = 0;//反审核
                thirdpartnar.reject = 0;//驳回
                thirdpartnar.cancel = 0;//取消驳回
                if (thirdpartnar.st_is_approve == 1) {
                    thirdpartnar.dele = 1;
                    thirdpartnar.reject = 1;
                } else if (thirdpartnar.st_is_approve == 2) {
                    thirdpartnar.edit = 1;
                } else {
                    thirdpartnar.dele = 1;
                    thirdpartnar.cancel = 1;
                }
                thirdpartnar.add_time = (thirdpartnar.add_time).split(' ')[0];
                if (thirdpartnar.st_is_approve == 1) {
                    thirdpartnar.statusTxt = '待审核';
                    thirdpartnar.typeCss = 1;
                }
                else if (thirdpartnar.st_is_approve == 2) {
                    thirdpartnar.statusTxt = '已审核';
                    thirdpartnar.typeCss = 0;
                } else if (thirdpartnar.st_is_approve == 3) {
                    thirdpartnar.statusTxt = '已驳回';
                    thirdpartnar.typeCss = 1;
                }
                // thirdpartnar.st_is_approve = thirdpartnar.st_is_approve === 2 ? "<i class='fa fa-check'></i>" : "<i class='fa fa-close'></i>";
                self_.thirdpartnarList.push(thirdpartnar);
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

    self_.rejectthirdpartnar = function (thirdpartnar) {
        $("#genid").val(thirdpartnar.id);
        $("#rejectModal").modal("show");
    };

    self_.delthirdpartnar = function (thirdpartnar) {
        var ides = new Array();
        ycoa.UI.messageBox.confirm('确认财务审核吗？', function (btn) {
            if (btn) {
                ides.push(thirdpartnar.id);
                var obj = new Object;
                obj.id = ides;
                //obj.receivMoney=thirdpartnar.payment_amount;
                obj.action = 1;
                obj.add_time = (thirdpartnar.add_time).split(' ')[0];
                ycoa.ajaxLoadPost("/api/finance/second_performance_checking.php", JSON.stringify(obj), function (result) {
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
    self_.editthirdpartnar = function (thirdpartnar) {
        var ides = new Array();
        var obj = new Object;
        ides.push(thirdpartnar.id);
        obj.action = 2;
        obj.id = ides;
        obj.qq = thirdpartnar.qq;
        ycoa.ajaxLoadPost("/api/finance/second_performance_checking.php", JSON.stringify(obj), function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success("操作成功~");

            } else {
                ycoa.UI.toast.error(result.msg);
            }
            reLoadData({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), curTime: $('#curTime').val(), review: $('#isVerify').val(), payType: $('#payType').val(), workName: $('#workName').val(), account: $('#account').val()});
        });
    };
    self_.cancelthirdpartnar = function (thirdpartnar) {
        var obj = new Object;
        obj.action = 6;
        obj.id = thirdpartnar.id;
        ycoa.ajaxLoadPost("/api/finance/second_performance_checking.php", JSON.stringify(obj), function (result) {
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
    thirdpartnarListViewModel.listthirdpartnar(data);
}
$(function () {
    initServerDate("#curTime");
    ko.applyBindings(thirdpartnarListViewModel, $("#dataTable")[0]);
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
            window.location.href = "/api/finance/second_performance_checking.php?action=10&start_time=" + start_time + "&end_time=" + end_time;
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
        ycoa.ajaxLoadPost("/api/finance/second_performance_checking.php", JSON.stringify(obj), function (result) {
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
            if ($(this).parents("tr").find("td").eq(10).find("span").text() == "已审核") {
                var id = $(this).parents("tr").find("td").eq(1).text();
                ycoa.UI.toast.error("编号" + id + "已经审核通过了，不能再审核了");
                flag = false;
                return false;
            } else {
                if (index == 0) {
                    currentDate = $(this).parents("tr").find("td").eq(2).text();
                } else {
                    if (currentDate != $(this).parents("tr").find("td").eq(2).text()) {
                        ycoa.UI.toast.error("不能勾选两天的时间进行批量审核");
                        flag = false;
                        return false;
                    }
                }
                ides.push($(this).val());
            }
        });
        if (flag) {
            var obj = new Object;
            obj.action = 1;
            obj.id = ides;
            obj.add_time = currentDate;
            ycoa.ajaxLoadPost("/api/finance/second_performance_checking.php", JSON.stringify(obj), function (result) {
                if (result.code == 0) {
                    ycoa.UI.toast.success(result.msg);
                } else {
                    ycoa.UI.toast.error(result.msg);
                }
                reLoadData({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), curTime: $('#curTime').val(), review: $('#isVerify').val(), payType: $('#payType').val(), workName: $('#workName').val(), account: $('#account').val()});
            });
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