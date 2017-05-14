var resignListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.resignList = ko.observableArray([]);
    self_.listPaidLeave = function (data) {
        ycoa.ajaxLoadGet("/api/attendance/resign.php", data, function (results) {
            self_.resignList.removeAll();
            $.each(results.list, function (index, resign) {
                debugger;
                resign.addtime = resign.addtime.split(' ')[0];
                resign.begin_date = resign.begindate.split(':')[0];
                resign.loginUserid = ycoa.user.userid();
                resign.ok = false; //提交审核按钮
                resign.verify = false; //审核按钮
                resign.del = false; //删除按钮
                resign.edit = false; //编辑按钮
                //resign.read = false;
                switch (resign.permit) {
                    case "0000":
                        break;
                    case "1011":
                        resign.ok = true;
                        resign.del = true;
                        resign.edit = true;
                        break;
                    case "0100":
                        resign.verify = true;
                        break;
                    case "0001":
                        resign.del = true;
                        break;
                    case "0101":
                        resign.verify = true;
                        resign.del = true;
                        break;
                }
                self_.resignList.push(resign);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                reLoadData({action: 1, deptid: $("#deptid").val(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: pageSize, searchName: $("#searchUserName").val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName()});
            }, function (pageNo) {
                reLoadData({action: 1, deptid: $("#deptid").val(), pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(), searchName: $("#searchUserName").val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName()});
            });
        });
    };
    self_.okCauseleave = function (resign) {
        resign.action = 2;
        ycoa.ajaxLoadPost("/api/attendance/resign.php", JSON.stringify(resign), function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success("操作成功~");
                reLoadData({action: 1});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    };

    //审核操作
    self_.verifyCauseleave = function (resign) {
        $("#approveModel").modal('show');
        $("#causeleaveId").val(resign.id);
    }
    self_.selfDelete = function (resign) {
        ycoa.UI.messageBox.confirm("确定删除该条修改补卡(补签)流程信息?", function (btn) {
            if (btn) {
                resign.action = 3;
                ycoa.ajaxLoadPost("/api/attendance/resign.php", JSON.stringify(resign), function (result) {
                    if (result.code == 0) {
                        ycoa.UI.toast.success("操作成功~");
                        reLoadData({action: 1});
                    } else {
                        ycoa.UI.toast.error("操作失败~");
                    }
                    ycoa.UI.block.hide();
                });
            }
        });
    };
    self_.selfEdit = function (resign) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + resign.id).show();
        $("#submit_" + resign.id).show();
        $("#cancel_" + resign.id).show();
        $("#tr_" + resign.id + " input,#tr_" + resign.id + " textarea").removeAttr("disabled");
    };
    self_.cancelTr = function (resign) {
        $("#tr_" + resign.id).hide();
        $("#submit_" + resign.id).hide();
        $("#cancel_" + resign.id).hide();
    };
    self_.read = function (resign) {
        resign.action = 5;
        ycoa.ajaxLoadPost("/api/attendance/resign.php", JSON.stringify(resign), function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({action: 1, deptid: $("#deptid").val(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), searchName: $("#searchUserName").val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName()});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    };
};
$(function () {
    ko.applyBindings(resignListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1});
    $("#dataTable").reLoad(function () {
        reLoadData({action: 1});
    });
    $("#dataTable").searchDept(function (id) {
        reLoadData({action: 1, deptid: id});
    });
    $("#dataTable").searchUserName(function (name) {
        reLoadData({action: 1, searchName: name});
    });
    $("#dataTable").searchDateTime(function (searchdate) {
        reLoadData({action: 1, searchDate: searchdate});
    }, '补卡时间');
    $("#dataTable").sort(function (data) {
        reLoadData({action: 1, deptid: $("#deptid").val(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), searchName: $("#searchUserName").val(), sort: data.sort, sortname: data.sortname, seachDate: $("#searchDateTime").val()});
    });
    $("#dataTable thead input[id='checkall']").change(function () {
        if ($(this).prop("checked")) {
            $("#dataTable tbody input[type='checkbox']").prop("checked", "checked");
        } else {
            $("#dataTable tbody input[type='checkbox']").removeAttr("checked");
        }
    });
    $("#open_dialog_btn").click(function () {
        $("#add_resign_form input[type='text'],#add_resign_form input[type='hidden']").val("");
        $("#add_resign_form #username").val(ycoa.user.username());
        $("#add_resign_form #userid").val(ycoa.user.userid());
        $(".has-error,.has-success").each(function () {
            $(this).removeClass("has-error").removeClass("has-success");
        });
        $(".fa-warning,.fa-check").each(function () {
            $(this).removeClass("fa-warning").removeClass("fa-check");
        });
    });
    $("#open_dialog_btn_dpt").click(function () {
        $("#add_resign_dpt_form input[type='text'],#add_resign_dpt_form input[type='hidden']").val("");
        $("#add_resign_dpt_form #username").val(ycoa.user.dept1_name());
        $("#add_resign_dpt_form #userid").val(ycoa.user.userid());
        $(".has-error,.has-success").each(function () {
            $(this).removeClass("has-error").removeClass("has-success");
        });
        $(".fa-warning,.fa-check").each(function () {
            $(this).removeClass("fa-warning").removeClass("fa-check");
        });
    });
    $('body').on('mouseover', '.date-picker-bind-mouseover', function () {
        $(this).datepicker({autoclose: true});
    });
    $('body').on('mouseover', ".timepicker-24", function () {
        $(this).timepicker({
            autoclose: true,
            minuteStep: 5,
//            showSeconds: false,
            showMeridian: false
        });
    });
    $("body").on("click", ".form_submit_btn", function () {
        var formid = "form_" + $(this).attr("val");
        var data = $("#" + formid).serializeJson();
        data.action = 4;
        ycoa.ajaxLoadPost("/api/attendance/resign.php", JSON.stringify(data), function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success("操作成功~");
                reLoadData({action: 1});
            } else {
                ycoa.UI.toast.error("操作失败~");
            }
            ycoa.UI.block.hide();
        });
    });
    $("#btn_submit_primary").click(function () {
        $("#add_resign_form").submit();
    });
    $('#btn_submit_dpt_primary').click(function () {
        $("#add_resign_dpt_form").submit();
    });
    $("#start_time,#end_time").val(new Date().format("yyyy-MM-dd"));
    $("body").on("click", "#btn_toexcel_primary", function () {
        var start_time = $("#toexcel_form #start_time").val();
        var end_time = $("#toexcel_form #end_time").val();
        if (start_time || end_time) {
            location.href = '/api/attendance/resign.php?start_time=' + start_time + '&end_time=' + end_time + '&action=11';
        }
    });

    $("body").on('click', "#btn_approve_primary", function () {
        if ($("input[type='radio'][name='isArgee']").is(':checked')) {
            var object = new Object();
            object.isAgree = $("input[type='radio'][name='isArgee']:checked").val();
            object.id = $("#causeleaveId").val();
            object.remark = $("#approveRemark").val();
            object.action = 10;
            ycoa.ajaxLoadPost("/api/attendance/resign.php", JSON.stringify(object), function (result) {
                if (result.code == 0) {
                    ycoa.UI.toast.success("操作成功~");
                    reLoadData({action: 1});
                } else {
                    ycoa.UI.toast.error(result.msg);
                }
                ycoa.UI.block.hide();
                $("#approveModel").modal('hide');
            });
        } else {
            ycoa.UI.toast.error("请选择是否同意~");
        }
    });

    $("body").on("mouseenter", "#dataTable .data_list_div", function () {
        var self_ = $(this);
        self_.data('lastEnter', new Date().getTime());
        setTimeout(function () {
            var t1 = new Date().getTime(), t2 = self_.data('lastEnter');
            if (t2 > 0 && t1 - t2 >= 500) {
                var w_id = self_.attr("val");
                $("#workflowLog_detail_" + w_id).animate({opacity: 'show'}, 50, function () {
                    $(this).addClass("workflow_open");
                });
                $("#workflowLog_detail_" + w_id).html("<img src='../../assets/global/img/input-spinner.gif' style='margin-top: 130px'>");
                $.get(ycoa.getNoCacheUrl("/api/sys/workflowLog.php"), {action: 4, workflow_id: w_id}, function (result) {
                    if (result.list.length > 0) {
                        var html = "<ul>";
                        $.each(result.list, function (idnex, d) {
                            html += "<li>[" + d.addtime + " " + d.role_type_name + "(" + d.username + ")]->" + d.opt + "</li>";
                            if (d.comment) {
                                html += "<li>" + d.comment + "</li>";
                            }
                        });
                        html += "</ul>";
                        $("#workflowLog_detail_" + w_id).html(html);
                    } else {
                        $("#workflowLog_detail_" + w_id).html("<img src='../../assets/global/img/workflowLog_detail_nodata.png'>");
                    }
                });
            }
        }, 500);
    }).on('mouseleave', $(this), function () {
        $(this).data('lastEnter', 0);
    });
    $("body").on("mouseleave", ".workflow_open", function () {
        $(this).hide();
        $(this).removeClass("workflow_open");
    });

    $('body').on("mouseover", "[data-toggle='tooltip']", function () {
        $(this).tooltip('show');
    });

    $(document).bind("click", function (e) {
        var target = $(e.target);
        if (target.closest(".doback_open").length == 0) {
            $(".doback_open").animate({opacity: 'toggle', width: '0px'}, 500, function () {
                $(this).hide();
                $(this).removeClass("doback_open");
                $(this).find("textarea").val("");
            });
        }
    });
});

function addInput(el) {
    var html = "<div class='form-group'><label class='control-label col-md-3' for='signdate'>补卡(补签)时间<span style='color: red;'>*</span></label><div class='col-md-5'>";
    html += "<input class='form-control'   type='text' name='signdate' id='signdate' placeholder='日期' onfocus='WdatePicker({dateFmt: \"yyyy-MM-dd HH:mm\"})'/>";
    html += "</div><a href='javascript:void' class='addDate plus'>添加</a></div>";
    if ($("#add_resign_form input[name='signdate']").length > 1) {
        return false;
    } else {
        el.parent().after(html);
        el.removeClass('plus').addClass("minus").html("删除");
    }
}

function reLoadData(data) {
    resignListViewModel.listPaidLeave(data);
}