var flowListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.flowList = ko.observableArray([]);
    self_.listflow = function (data) {
        ycoa.ajaxLoadGet("/api/second_sale/flow.php", data, function (results) {
            self_.flowList.removeAll();
            $.each(results.list, function (index, flow) {
                flow.dele = ycoa.SESSION.PERMIT.hasPagePermitButton("3050702");
                flow.edit = ycoa.SESSION.PERMIT.hasPagePermitButton("3050703");
                flow.show = flow.is_refund == 1 ? 0 : 1;
                self_.flowList.push(flow);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                var data = {
                    action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: pageSize, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(),
                    workName: $("#workName").val(), isRecept: $("#search_type").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                };
                if (data.searchStartTime || data.searchEndTime) {
                    data.searchTime = "";
                }
                reLoadData(data);
            }, function (pageNo) {
                var data = {
                    action: 1, pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(),
                    workName: $("#workName").val(), isRecept: $("#search_type").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                };
                if (data.searchStartTime || data.searchEndTime) {
                    data.searchTime = "";
                }
                reLoadData(data);
            });
        });
    };
    self_.delflow = function (flow) {
        ycoa.UI.messageBox.confirm('确认删除？', function (del) {
            if (del) {
                flow.action = 2;
                ycoa.ajaxLoadPost("/api/second_sale/flow.php", JSON.stringify(flow), function (result) {
                    if (result.code == 0) {
                        ycoa.UI.toast.success(result.msg);
                        reLoadData({action: 1});
                    } else {
                        ycoa.UI.toast.error(result.msg);
                    }
                    ycoa.UI.block.hide();
                });
            }
        });
    };
    self_.editflow = function (flow) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + flow.id).show();
        $("#submit_" + flow.id).show();
        $("#cancel_" + flow.id).show();
        if (!$("#form_" + flow.id).attr('autoEditSelecter')) {
            initEditSeleter($("#form_" + flow.id));
        }
        $("#tr_" + flow.id + " input,#tr_" + flow.id + " textarea").removeAttr("disabled");
        dateSplit("#form_" + flow.id);
    };
    self_.showflow = function (flow) {
//        $(".second_tr").hide();
//        $(".submit_btn").hide();
//        $(".cancel_btn").hide();
//        $("#tr_" + flow.id).show();
//        $("#cancel_" + flow.id).show();
//        if (!$("#form_" + flow.id).attr('autoEditSelecter')) {
//            initEditSeleter($("#form_" + flow.id));
//        }
//        $("#tr_" + flow.id + " input,#tr_" + flow.id + " textarea").attr("disabled", "");
        var data = new Object;
        data.action = 6;
        data.id = flow.id;
        data = JSON.stringify(data);
        ycoa.ajaxLoadPost("/api/second_sale/flow.php", data, function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({action: 1});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    };
    self_.cancelTr = function (flow) {
        $("#tr_" + flow.id).hide();
        $("#submit_" + flow.id).hide();
        $("#cancel_" + flow.id).hide();
    };
    self_.doEditSubmit = function (flow) {
        var formid = "form_" + flow.id;
        var data = $("#" + formid).serializeJson();
        data.remark = $("#" + formid + " #remark_edit").html();
        data.action = 3;
        data = JSON.stringify(data);
        ycoa.ajaxLoadPost("/api/second_sale/flow.php", data, function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({action: 1});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    };
    self_.doTe = function (flow) {
        flow.isTe = 1;
        updateCL(flow);
    };
}();
$(function () {
    ko.applyBindings(flowListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1});
    $("#dataTable").reLoad(function () {
        $("#workName").val("");
        $("#search_type").val("");
        $("#searchDateTime").val("");
        $('#searchStartTime').val("");
        $('#searchEndTime').val("");
        reLoadData({action: 1});
    });

    $("body").on("click", '#customer', function () {
        ycoa.UI.employeeSeleter({el: $(this), array: customer_array}, function (data, el) {
            el.val(data.text);
            el.next("#customer_id").val(data.id);
        });
    });

    $("body").on("click", '#rception_staff', function () {
        ycoa.UI.employeeSeleter({el: $(this), array: rception_array}, function (data, el) {
            el.val(data.text);
            el.next("#rception_staff_id").val(data.id);
        });
    });

    $("#dataTable").searchUserName(function (name) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: name, isRecept: $("#search_type").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    }, '关键字', 'workName');
    $("#dataTable").searchAutoStatus([{id: 1, text: '已指派'}, {id: 2, text: '未指派'}], function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $("#workName").val(), isRecept: d.id, searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
        $("#search_type").val(d.id);
    }, '按是否指派筛选');
    $("#dataTable").searchDateTimeSlot(function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $("#workName").val(), isRecept: $("#isRecept").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    });
    $("#dataTable").searchDateTime(function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $("#workName").val(), isRecept: $("#isRecept").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    });
    $("body").on("mouseover", '.date-picker-bind-mouseover', function () {
        $(this).datepicker({autoclose: true});
    });
    $('body').on('mouseover', '.timepicker-24', function () {
        $(this).timepicker({
            autoclose: true,
            minuteStep: 5,
//            showSeconds: false,
            showMeridian: false
        });
    });

    //批量删除
    $("#batchDelete").click(function () {
        var id_ary = new Array();
        var num = 0;
        $("#dataTable tbody .checkbox").each(function () {
            if ($(this).attr("checked")) {
                id_ary[num] = $(this).val();
                num++;
            }
        });
        if (id_ary.length == 0) {
            ycoa.UI.toast.error("请勾选多条记录删除！");
        } else {
            ycoa.UI.block.show();
            $.ajax({
                type: 'post',
                data: {'action': '10', 'id': id_ary.join(',')},
                url: '../../api/second_sale/flow.php',
                success: function (result) {
                    ycoa.UI.block.hide();
                    if (result.code == 0) {
                        ycoa.UI.toast.success(result.msg);
                        reLoadData({action: 1});
                    } else {
                        ycoa.UI.toast.error(result.msg);
                    }
                }
            });
        }
    });

    $("#btn_toexcel_primary").click(function () {
        var start_time = $("#start_time").val();
        var end_time = $("#end_time").val();
        if (start_time || end_time) {
            window.location.href = "/api/second_sale/flow.php?action=10&start_time=" + start_time + "&end_time=" + end_time;
        }
    });
    $('#btn_importexcel_primary').click(function () {
        ycoa.UI.block.show();
        $('#importexcel_form').ajaxSubmit({
            type: 'post',
            url: "../../api/second_sale/flow.php?upload=1",
            success: function (result) {
                ycoa.UI.block.hide();
                if (result.code == 0) {
                    $("#btn_close_primary").click();
                    ycoa.UI.toast.success(result.msg);
                    reLoadData({action: 1});
                } else {
                    ycoa.UI.toast.error(result.msg);
                }
                $('#myImportexcelModal').modal('hide');
            }
        });
    });

    $("body").on("click", '#dataTable .checkbox', function () {
        countMoney(5);
    });

    $("#dataTable thead input[id='checkall']").change(function () {
        if ($(this).prop("checked")) {
            $("#dataTable tbody input[type='checkbox']").prop("checked", "checked");
        } else {
            $("#dataTable tbody input[type='checkbox']").removeAttr("checked");
        }
        countMoney(5);
    });
    $("#btn_submit_primary").click(function () {
        $("#add_flow_form").submit();
    });
    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 1}, function (res) {
//        $("#add_flow_form #rception_staff").autoEditSelecter(res, function (d) {
//            $("#add_flow_form #rception_staff_id").val(d.id);
//        });
        rception_array = res;
    });
    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 2}, function (res) {
//        $("#add_flow_form #customer").autoEditSelecter(res, function (d) {
//            $("#add_flow_form #customer_id").val(d.id);
//        });
        customer_array = res;
    });
});
function reLoadData(data) {
    flowListViewModel.listflow(data);
}

//日期拆分函数
function dateSplit(object) {
    var date = $(object + " #add_time").val();
    dateArray = date.split(" ");
    $(object + " .input-time").val(dateArray[1]);
    $(object + " .input-date").val(dateArray[0]);
}

function updateCL(flow) {
    flow.action = 3;
    ycoa.ajaxLoadPost("/api/second_sale/flow.php", JSON.stringify(flow), function (result) {
        if (result.code == 0) {
            ycoa.UI.toast.success("操作成功~");
            reLoadData({action: 1});
        } else {
            ycoa.UI.toast.error("操作失败~");
        }
        ycoa.UI.block.hide();
    });
}
function initEditSeleter(el) {

//    $("#rception_staff", el).autoEditSelecter(rception_array, function (d) {
//        $("#rception_staff_id", el).val(d.id);
//    });
//    $("#customer", el).autoEditSelecter(customer_array, function (d) {
//        $("#customer_id", el).val(d.id);
//    });
}