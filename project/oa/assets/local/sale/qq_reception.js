var ReceptionListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.receptionList = ko.observableArray([]);
    self_.listReception = function (data) {
        ycoa.ajaxLoadGet("/api/sale/qq_reception.php", data, function (results) {
            self_.receptionList.removeAll();
            $.each(results.list, function (index, reception) {
                reception.dele = ycoa.SESSION.PERMIT.hasPagePermitButton("2011102");
                reception.edit = ycoa.SESSION.PERMIT.hasPagePermitButton("2011103");
                reception.begin = ycoa.SESSION.PERMIT.hasPagePermitButton("2011106") && ((reception.toplimit > reception.finish) && reception.status == 0);
                reception.end = ycoa.SESSION.PERMIT.hasPagePermitButton("2011106") && ((reception.toplimit > reception.finish) && reception.status != 0);
                reception.color_ = reception.status === 0 ? 'color:green' : '';
                self_.receptionList.push(reception);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                reLoadData({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), status: $("#status").val(), searchName: $("#searchUserName").val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName()});
            }, function (pageNo) {
                reLoadData({action: 1, pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(), status: $("#status").val(), searchName: $("#searchUserName").val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName()});
            });
        });
    };
    self_.delReception = function (reception) {
        ycoa.UI.messageBox.confirm('确认删除？', function (del) {
            if (del) {
                reception.action = 3;
                ycoa.ajaxLoadPost("/api/sale/qq_reception.php", JSON.stringify(reception), function (result) {
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
    self_.editReception = function (reception) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + reception.id).show();
        $("#submit_" + reception.id).show();
        $("#cancel_" + reception.id).show();
        if (!$("#form_" + reception.id).attr('autoEditSelecter')) {
            initEditSeleter($("#form_" + reception.id));
        }
    };
    self_.cancelTr = function (reception) {
        $("#tr_" + reception.id).hide();
        $("#submit_" + reception.id).hide();
        $("#cancel_" + reception.id).hide();
    };
    self_.updateStatus = function (reception) {
        var status = reception.status == 1 ? 0 : 1;
        ycoa.ajaxLoadPost("/api/sale/qq_reception.php", JSON.stringify({action: 40, status: status, id: reception.id}), function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({action: 1});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    };
}();
$(function () {
    $("#dataTable").sort(function (data) {
        reLoadData({action: 1, sort: data.sort, sortname: data.sortname, pagesize: ycoa.SESSION.PAGE.getPageSize(), deptid: $('#deptid').val(), searchName: $("#searchUserName").val(), status: $("#status").val()});
    });
    $("#dataTable").reLoad(function () {
        reLoadData({action: 1});
        $('#searchUserName').val('');
    });
    $("#dataTable").searchUserName(function (name) {
        reLoadData({action: 1, searchName: name});
    });
    $('.timepicker-24').timepicker({
        autoclose: true,
        minuteStep: 5,
        showSeconds: false,
        showMeridian: false
    });
    ko.applyBindings(ReceptionListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1});
    $("#dataTable thead input[id='checkall']").change(function () {
        if ($(this).prop("checked")) {
            $("#dataTable tbody input[type='checkbox']").prop("checked", "checked");
        } else {
            $("#dataTable tbody input[type='checkbox']").removeAttr("checked");
        }
    });
    $("#add_reception_form #presales").click(function () {
        ycoa.UI.empSeleter({el: $(this), type: 'only', groupId: [7]}, function (data, el) {
            el.val(data.name);
            $("#add_reception_form #presales_id").val(data.id);
        });
    });
    $("#btn_submit_primary").click(function () {
        $("#add_reception_form").submit();
    });
    $("#open_dialog_btn").click(function () {
        $("#add_reception_form input,#add_reception_form textarea").each(function () {
            if (!$(this).hasClass("not-clear")) {
                $(this).val("");
            }
        });
        $("#add_reception_form input[type='checkbox']").removeAttr("checked");
        $(".has-error,.has-success").each(function () {
            $(this).removeClass("has-error").removeClass("has-success");
        });
        $(".fa-warning,.fa-check").each(function () {
            $(this).removeClass("fa-warning").removeClass("fa-check");
        });
    });
    $(".dept_submit_btn").live("click", function () {
        var formid = "form_" + $(this).attr("val");
        var data = $("#" + formid).serializeJson();
        data.action = 2;
        data = JSON.stringify(data);
        ycoa.ajaxLoadPost("/api/sale/qq_reception.php", data, function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({action: 1});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    });
    $("#action_btn").click(function () {
        var data = {action: 4};
        data = JSON.stringify(data);
        ycoa.ajaxLoadPost("/api/sale/qq_reception.php", data, function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    });

    $("#all_stop_btn").click(function () {
        var data = {action: 41};
        data = JSON.stringify(data);
        ycoa.ajaxLoadPost("/api/sale/qq_reception.php", data, function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    });

    $('.popovers').popover();
});
function reLoadData(data) {
    ReceptionListViewModel.listReception(data);
}


function initEditSeleter(el) {
    $('.timepicker-24', el).timepicker({
        autoclose: true,
        minuteStep: 5,
        showSeconds: false,
        showMeridian: false
    });
    $("#presales", el).click(function () {
        ycoa.UI.empSeleter({el: $(this), type: 'only', groupId: [7]}, function (data, node) {
            node.val(data.name);
            $("#presales_id", el).val(data.id);
        });
    });
    el.attr('autoEditSelecter', 'autoEditSelecter');
}