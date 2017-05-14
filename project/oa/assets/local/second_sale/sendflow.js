var rception_array, serviceEmployee_array
var sendflowListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.sendflowList = ko.observableArray([]);
    self_.listsendflow = function (data) {
        ycoa.ajaxLoadGet("/api/second_sale/sendflow.php", data, function (results) {
            self_.sendflowList.removeAll();
            $.each(results.list, function (index, sendflow) {                sendflow.isfirst_text = sendflow.isfirst === 0 ? "主推" : "副推";
                sendflow.dele = ycoa.SESSION.PERMIT.hasPagePermitButton("3050802");
                sendflow.edit = ycoa.SESSION.PERMIT.hasPagePermitButton("3050803");
                sendflow.send = ycoa.SESSION.PERMIT.hasPagePermitButton("3050804");
                sendflow.back = ycoa.SESSION.PERMIT.hasPagePermitButton("3050805");
                self_.sendflowList.push(sendflow);
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
    self_.delsendflow = function (sendflow) {
        ycoa.UI.messageBox.confirm('确认删除？', function (del) {
            if (del) {
                                sendflow.action = 2;
                ycoa.ajaxLoadPost("/api/second_sale/sendflow.php", JSON.stringify(sendflow), function (result) {
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
    self_.editsendflow = function (sendflow) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + sendflow.id).show();
        $("#submit_" + sendflow.id).show();
        $("#cancel_" + sendflow.id).show();
        if (!$("#form_" + sendflow.id).attr('autoEditSelecter')) {
            initEditSeleter($("#form_" + sendflow.id));
        }
        $("#tr_" + sendflow.id + " input,#tr_" + sendflow.id + " textarea").removeAttr("disabled");
    };
    self_.showsendflow = function (sendflow) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + sendflow.id).show();
        $("#cancel_" + sendflow.id).show();
        if (!$("#form_" + sendflow.id).attr('autoEditSelecter')) {
            initEditSeleter($("#form_" + sendflow.id));
        }
        $("#tr_" + sendflow.id + " input,#tr_" + sendflow.id + " textarea").attr("disabled", "");
    };
    self_.cancelTr = function (sendflow) {
        $("#tr_" + sendflow.id).hide();
        $("#submit_" + sendflow.id).hide();
        $("#cancel_" + sendflow.id).hide();
    };
    self_.doEditSubmit = function (sendflow) {
        var formid = "form_" + sendflow.id;

        var data = $("#" + formid).serializeJson();
        data.remark = $("#" + formid + " #remark_edit").html();
        data.action = 3;
        data = JSON.stringify(data);
        ycoa.ajaxLoadPost("/api/second_sale/sendflow.php", data, function (result) {
                        if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({action: 1});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    };
    self_.doTe = function (sendflow) {
        sendflow.isTe = 1;
        updateCL(sendflow);
    };
}();
$(function () {
    ko.applyBindings(sendflowListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1});
    $("#dataTable").reLoad(function () {
        $("#workName").val("");
        $("#search_type").val("");
        $("#searchDateTime").val("");
        $('#searchStartTime').val("");
        $('#searchEndTime').val("");
        reLoadData({action: 1});
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
//    $("#dataTable").searchAutoStatus([{id: 0, text: '未指派'}, {id: 1, text: '处理中'}, {id: 2, text: '已完成'}, {id: 1, text: '已退回'}], function (d) {
//        var data = {
//            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
//            workName: $("#workName").val(), isRecept: d.id, searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
//        };
//        if (data.searchStartTime || data.searchEndTime) {
//            data.searchTime = "";
//        }
//        reLoadData(data);
//        $("#search_type").val(d.id);
//    }, '按状态筛选');
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
    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 1}, function (res) {
        $("#add_sendflow_form #rception_staff").autoEditSelecter(res, function (d) {
            $("#add_sendflow_form #rception_staff_id").val(d.id);
        });
        rception_array = res;
    });

    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 3}, function (res) {
        $("#add_sendflow_form #serviceEmployee").autoEditSelecter(res, function (d) {
            $("#add_sendflow_form #serviceEmployee_id").val(d.id);
        });
        serviceEmployee_array = res;
    });

    $("#add_sendflow_form #isfirst").autoRadio(array['isArrears']);
    $("#add_sendflow_form #level").autoEditSelecter(array['level']);
    $("#add_sendflow_form #goodsbase").autoEditSelecter(array['goodsSource']);
    $("#btn_toexcel_primary").click(function () {
        var start_time = $("#start_time").val();
        var end_time = $("#end_time").val();
        if (start_time || end_time) {
            window.location.href = "/api/second_sale/flow.php?action=10&start_time=" + start_time + "&end_time=" + end_time;
        }
    });
    $('#btn_importexcel_primary').click(function () {
        $('#importexcel_form').ajaxSubmit({
            type: 'post',
            url: "../../api/second_sale/flow.php?upload=1",
            success: function (result) {
                if (result == 1) {
                    $('#myImportexcelModal').modal('hide');
                    reLoadData({action: 1});
                }
            }
        });
    });
    $("#dataTable thead input[id='checkall']").change(function () {
        if ($(this).prop("checked")) {
            $("#dataTable tbody input[type='checkbox']").prop("checked", "checked");
        } else {
            $("#dataTable tbody input[type='checkbox']").removeAttr("checked");
        }
    });
    $("#btn_submit_primary").click(function () {
        $("#add_sendflow_form").submit();
    });
    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 1}, function (res) {
        $("#add_flow_form #rception_staff").autoEditSelecter(res, function (d) {
            $("#add_flow_form #rception_staff_id").val(d.id);
        });
        rception_array = res;
    });
    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 2}, function (res) {
        $("#add_flow_form #customer").autoEditSelecter(res, function (d) {
            $("#add_flow_form #customer_id").val(d.id);
        });
        customer_array = res;
    });
    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 3}, function (res) {
        $("#add_flow_form #serviceEmployee").autoEditSelecter(res, function (d) {
            $("#add_flow_form #serviceEmployee_id").val(d.id);
        });
        serviceEmployee_array = res;
    });
    $("#add_sendflow_form #isfirst_text").autoRadio(array['isfirst_text']);
});
function reLoadData(data) {
    sendflowListViewModel.listsendflow(data);
}

function updateCL(sendflow) {
    sendflow.action = 2;
    ycoa.ajaxLoadPost("/api/second_sale/sendflow.php", JSON.stringify(sendflow), function (result) {
        if (result.code == 0) {
            ycoa.UI.toast.success("操作成功~");
            reLoadData({});
        } else {
            ycoa.UI.toast.error("操作失败~");
        }
        ycoa.UI.block.hide();
    });
}

var array = {
    isArrears: [{id: '0', text: '主推'}, {id: '1', text: '副推'}],
    level: [{id: '1', text: "一个月"}, {id: '2', text: "二个月"}, {id: '3', text: "三个月"}, {id: '4', text: "四个月"}, {id: '5', text: "五个月"}],
    goodsSource: [{id: '1', text: "淘货源"}, {id: '2', text: "店宝宝"}, {id: '3', text: "供销平台"}, {id: '4', text: "17做网店"}, {id: '5', text: "其他"}]

};

function initEditSeleter(el) {
    $("#isfirst", el).autoRadio(array['isArrears']);
    $('#level', el).autoEditSelecter(array['level']);
    $('#goodsbase', el).autoEditSelecter(array['goodsSource']);
    $("#rception_staff", el).autoEditSelecter(rception_array, function (d) {
        $("#rception_staff_id", el).val(d.id);
    });
    $("#serviceEmployee", el).autoEditSelecter(serviceEmployee_array, function (d) {
        $("#serviceEmployee_id", el).val(d.id);
    });
    $("#remark_edit", el).pasteImgEvent();
    el.attr('autoEditSelecter', 'autoEditSelecter');
}

