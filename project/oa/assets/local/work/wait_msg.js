var headmaster_array
var waitListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.waitList = ko.observableArray([]);
    self_.listwait = function (data) {
        ycoa.ajaxLoadGet("/api/work/wait_msg.php", data, function (results) {
            self_.waitList.removeAll();
            $.each(results.list, function (index, wait) {
                if (wait.status == 1)
                {
                    wait.statusvalue = '未处理';
                    wait.send = 1;
                }
                else if (wait.status == 2)
                {
                    wait.statusvalue = '已处理';
                    wait.send = 0;
                }
                if (wait.msgtype == 1)
                    wait.msgtypevalue = '指派班主任';
                if (wait.msgtype == 2)
                    wait.msgtypevalue = '请假';
                if (wait.msgtype == 3)
                    wait.msgtypevalue = '调休';
                if (wait.msgtype == 4)
                    wait.msgtypevalue = '情况说明';
                self_.waitList.push(wait);
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
    self_.editwait = function (wait) {
        $('#tmpid').val(wait.id);
        $('#taskid').val(wait.task_id);
        wait.action = 5;
        ycoa.ajaxLoadPost("/api/work/wait_msg.php", JSON.stringify(wait), function (result) {
            if (result.code == 0) {
                $('#assignService').modal('show');
                $('#add_date').val(result.list[0].add_time.split(' ')[0]);
                $('#add_time').val(result.list[0].add_time.split(' ')[1]);
                $('#platform_num').val(result.list[0].platform_num);
                $('#qq').val(result.list[0].qq);
                $('#customer_type').val(result.list[0].customer_type);
                $('#sales_numbers').val(result.list[0].sales_numbers);
                $('#rception_money').val(result.list[0].rception_money);
                $('#payment_amount').val(result.list[0].payment_amount);
                $('#final_money').val(result.list[0].final_money);
                $('#customer').val(result.list[0].customer);
                $('#platform_sales').val(result.list[0].platform_sales);
                $('#payment_method').val(result.list[0].payment_method);
                $('#share_performance').val(result.list[0].share_performance);
                $('#remark').val(result.list[0].remark);
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });

    };
    self_.cancelTr = function (wait) {
        $("#tr_" + wait.id).hide();
        $("#submit_" + wait.id).hide();
        $("#cancel_" + wait.id).hide();
    };
    self_.doEditSubmit = function (wait) {
        var formid = "form_" + wait.id;
        var data = $("#" + formid).serializeJson();
        data.remark = $("#" + formid + " #remark_edit").html();
        data.action = 3;
        data = JSON.stringify(data);
        ycoa.ajaxLoadPost("/api/work/wait.php", data, function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({action: 1});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    };
    self_.doTe = function (wait) {
        wait.isTe = 1;
        updateCL(wait);
    };
}();
$(function () {
    ko.applyBindings(waitListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1});
    $("#dataTable").reLoad(function () {
        $("#workName").val("");
        $("#search_type").val("");
        $("#searchDateTime").val("");
        $('#searchStartTime').val("");
        $('#searchEndTime').val("");
        reLoadData({action: 1});
    });
    $('#btn_submit_primary').click(function () {
        var data = $("#edit_generationOperation_form").serializeJson();
        data.action = 1;
        data.id = $('#tmpid').val();
        data.task_id = $('#taskid').val();
        ycoa.ajaxLoadPost("/api/work/wait_msg.php", JSON.stringify(data), function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({action: 1});

            } else {
                ycoa.UI.toast.error(result.msg);
            }

            ycoa.UI.block.hide();
            $('#assignService').modal('hide');
        });
    });

    $("body").on("click", '#headmaster', function () {
        ycoa.UI.employeeSeleter({el: $(this), array: headmaster_array}, function (data, el) {
            el.val(data.text);
            el.next("#headmaster_id").val(data.id);
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
    $("#dataTable").searchAutoStatus([{id: 1, text: '未处理'}, {id: 2, text: '已处理'}], function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $("#workName").val(), isRecept: d.id, searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
        $("#search_type").val(d.id);
    }, '按状态筛选');
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
    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 5}, function (res) {
//        $("#edit_generationOperation_form #headmaster").autoEditSelecter(res, function (d) {
//            $("#edit_generationOperation_form #headmaster_id").val(d.id);
//        });
        headmaster_array = res;
    });
    $("#add_wait_form #isfirst").autoRadio(array['isArrears']);


    $("#btn_toexcel_primary").click(function () {
        var start_time = $("#start_time").val();
        var end_time = $("#end_time").val();
        if (start_time || end_time) {
            window.location.href = "/api/work/wait_msg.php?action=10&start_time=" + start_time + "&end_time=" + end_time;
        }
    });
    $('#btn_importexcel_primary').click(function () {
        $('#importexcel_form').ajaxSubmit({
            type: 'post',
            url: "../../api/work/wait_msg.php?upload=1",
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
        $("#add_wait_form").submit();
    });
});
function reLoadData(data) {
    waitListViewModel.listwait(data);
}

function updateCL(wait) {
    wait.action = 2;
    ycoa.ajaxLoadPost("/api/work/wait_msg.php", JSON.stringify(wait), function (result) {
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
    isArrears: [{id: '0', text: '是'}, {id: '1', text: '否'}]
};

function initEditSeleter(el) {
    $("#isArrears", el).autoRadio(array['isArrears']);
//    $("#headmaster", el).autoEditSelecter(headmaster_array, function (d) {
//        $("#headmaster_id", el).val(d.id);
//    });
    $("#remark_edit", el).pasteImgEvent();
    el.attr('autoEditSelecter', 'autoEditSelecter');
}

