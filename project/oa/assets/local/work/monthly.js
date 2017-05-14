var MonthlyListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.monthlyList = ko.observableArray([]);
    self_.listMonthly = function (data) {
        ycoa.ajaxLoadGet("/api/work/monthly.php", data, function (results) {
                        self_.monthlyList.removeAll();
            $.each(results.list, function (index, monthly) {
                 monthly.editMonthly = ycoa.SESSION.PERMIT.hasPagePermitButton('3080203');
                monthly.deleteMonthly = ycoa.SESSION.PERMIT.hasPagePermitButton('3080204');
                monthly.showMonthly = ycoa.SESSION.PERMIT.hasPagePermitButton('3080201');
                monthly.downLoadFile = ycoa.SESSION.PERMIT.hasPagePermitButton('3080206') && monthly.file_name;
                monthly.setTop = ycoa.SESSION.PERMIT.hasPagePermitButton('3080205') && !monthly.top;
                self_.monthlyList.push(monthly);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                reLoadData({pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: pageSize, searchName: $("#searchUserName").val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName()});
            }, function (pageNo) {
                reLoadData({pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(), searchName: $("#searchUserName").val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName()});
            });
        });
    };
    self_.setTop = function (monthly) {
        monthly.action = 4;
        ycoa.ajaxLoadPost("/api/work/monthly.php", JSON.stringify(monthly), function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    };
    self_.editMonthly = function (monthly) {
        $('#edit_monthly_form #id').val(monthly.id);
        $('#edit_monthly_form #monthly_title').val(monthly.monthly_title);
        $('#edit_monthly_form #monthly_content').val(monthly.monthly_content);
        var obj = $('#edit_monthly_form #file');
        obj.outerHTML = obj.outerHTML.replace(/(value=\").+\"/i, "$1\"");
    };
    self_.showMonthly = function (monthly) {
        $("#myzhiModal #monthly_contents").val(monthly.monthly_content);
        $("#cancel_" + monthly.id).show();
    };
    self_.showFile = function (manual) {
        window.open(ycoa.getNoCacheUrl("/api/culture/show_file.php?doc=" + base64encode(manual.file_url)));
    };
    self_.downLoadFile = function (manual) {
        window.location.href = manual.file_url;
    };
    self_.deleteMonthly = function (manual) {
        ycoa.UI.messageBox.confirm("确定要删除该条制度信息吗?删除后不可恢复~", function (btn) {
            if (btn) {
                manual.action = 5;
                ycoa.ajaxLoadPost("/api/work/monthly.php", JSON.stringify(manual), function (result) {
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
}();
$(function () {
    $("#dataTable").reLoad(function () {
        reLoadData({});
        $("#searchUserName").val('');
    });
    $("#dataTable").sort(function (data) {
        reLoadData({sort: data.sort, sortname: data.sortname, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize()});
    });
    $("#dataTable").searchUserName(function (name) {
        reLoadData({searchName: name});
    }, "月度会议标题");
    ko.applyBindings(MonthlyListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize()});
    $('.reload').click(function () {
        reLoadData({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo()});
    });
//
//    $("#clear_fileToUpload").click(function () {
//        $("#add_manual_form #file_path").val("");
//        $("#add_manual_form #pdf_file_name").val("");
//        $(".add_upload_btn span").html("添加附件");
//        $(".add_upload_bar").width(0);
//    });
//    $("#clear_editFileToUpload").click(function () {
//        $("#edit_manual_form #file_path").val("");
//        $("#edit_manual_form #pdf_file_name").val("");
//        $(".edit_upload_btn span").html("添加附件");
//        $(".edit_upload_bar").width(0);
//    });
//
//    $("#dataTable thead input[id='checkall']").change(function () {
//        if ($(this).attr("checked")) {
//            $("#dataTable tbody input[type='checkbox']").attr("checked", "checked");
//        } else {
//            $("#dataTable tbody input[type='checkbox']").removeAttr("checked");
//        }
//    });
//    $("#myModal #btn_submit_primary").click(function () {
//        $("#add_manual_form").submit();
//    });
//    $("#myEditModal #btn_submit_primary").click(function () {
//        $("#edit_manual_form").submit();
//    });

//    $("#open_dialog_btn").click(function () {
//        $(".add_upload_btn span").html("添加附件");
//        $(".add_upload_bar").width(0);
//        $("#add_manual_form input,#add_manual_form textarea").each(function () {
//            if (!$(this).hasClass("not-clear")) {
//                $(this).val("");
//            }
//        });
//        $(".has-error,.has-success").each(function () {
//            $(this).removeClass("has-error").removeClass("has-success");
//        });
//        $(".fa-warning,.fa-check").each(function () {
//            $(this).removeClass("fa-warning").removeClass("fa-check");
//        });
//    });


//    $(".form_submit_btn").live("click", function () {
//        var formid = "form_" + $(this).attr("val");
//        var data = $("#" + formid).serializeJson();
//        data.id = $(this).attr("val");
//        data.action = 2;
//        data = JSON.stringify(data);
//        ycoa.ajaxLoadPost("/api/culture/manual.php", data, function (result) {
//            if (result.code == 0) {
//                ycoa.UI.toast.success(result.msg);
//                reLoadData({});
//            } else {
//                ycoa.UI.toast.error(result.msg);
//            }
//            ycoa.UI.block.hide();
//        });
//    });

//    $("#add_manual_form #sys_class_").change(function () {
//        if ($(this).val() != "其他") {
//            $("#add_manual_form #sys_class").val($(this).val());
//            $("#add_manual_form #sys_class").attr("readonly", "");
//        } else {
//            $("#add_manual_form #sys_class").removeAttr("readonly");
//            $("#add_manual_form #sys_class").val("");
//        }
//    });

    $('#myzhiModal #sys_contents').xheditor({tools: '', skin: 'nostyle', html5Upload: false, width: '300', height: '500'});
    $('#myModal #sys_content').xheditor({tools: 'Cut,Copy,Paste,|,Blocktag,Fontface,FontSize,Bold,Italic,Underline,Strikethrough,FontColor,BackColor,SelectAll,Removeformat,|,Align,List,Outdent,Indent,|Hr,Table,|,Source,Preview', skin: 'nostyle', html5Upload: false, width: '300', height: '350'});
    $('#myEditModal #monthly_content').xheditor({tools: 'Cut,Copy,Paste,|,Blocktag,Fontface,FontSize,Bold,Italic,Underline,Strikethrough,FontColor,BackColor,SelectAll,Removeformat,|,Align,List,Outdent,Indent,|Hr,Table,|,Source,Preview', skin: 'nostyle', html5Upload: false, width: '300', height: '350'});
    $('.popovers').popover();
});
function reLoadData(data) {
    MonthlyListViewModel.listMonthly(data);
}

function forMonthlySubmit() {
    $("#add_monthly_form").ajaxSubmit({
        type: 'post',
        url: "/api/work/monthly.php",
        success: function (data) {
                        if (data.code == 0) {
                $("#add_monthly_form").parent().parent().find('#btn_close_primary').click();
                ycoa.UI.toast.success(data.msg);
                MonthlyListViewModel.listMonthly({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo()});
            } else {
                ycoa.UI.toast.error(data.msg);
            }
            ycoa.UI.block.hide();
        },
        error: function (XmlHttpRequest, textStatus, errorThrown) {
            alert("error");
        }
    });
    return false;
}
function forMonthlyEditSubmit() {
    $("#edit_monthly_form").ajaxSubmit({
        type: 'post',
        url: "/api/work/monthly.php",
        success: function (data) {
                        if (data.code == 0) {
                $("#edit_monthly_form").parent().parent().find('#btn_close_primary').click();
                ycoa.UI.toast.success(data.msg);
                MonthlyListViewModel.listMonthly({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo()});
            } else {
                ycoa.UI.toast.error(data.msg);
            }
            ycoa.UI.block.hide();
        },
        error: function (XmlHttpRequest, textStatus, errorThrown) {
            alert("error");
        }
    });
    return false;
}