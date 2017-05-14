var ManualListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.manualList = ko.observableArray([]);
    self_.listManual = function (data) {
        ycoa.ajaxLoadGet("/api/culture/manual.php", data, function (results) {
            self_.manualList.removeAll();
            $.each(results.list, function (index, manual) {
                manual.date = new Date(manual.date).format("yyyy-MM-dd");
                manual.editManual = ycoa.SESSION.PERMIT.hasPagePermitButton('1050202');
                manual.deleteManual = ycoa.SESSION.PERMIT.hasPagePermitButton('1050203');
                manual.showManual = ycoa.SESSION.PERMIT.hasPagePermitButton('1050204');
                manual.showFile = ycoa.SESSION.PERMIT.hasPagePermitButton('1050205') && manual.pdf_file_name;
                manual.downLoadFile = ycoa.SESSION.PERMIT.hasPagePermitButton('1050206') && manual.pdf_file_name;
                manual.setTop = ycoa.SESSION.PERMIT.hasPagePermitButton('1050207') && !manual.top;
                self_.manualList.push(manual);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                reLoadData({pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: pageSize, searchName: $("#searchUserName").val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName()});
            }, function (pageNo) {
                reLoadData({pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(), searchName: $("#searchUserName").val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName()});
            });
        });
    };
    self_.setTop = function (manual) {
        manual.action = 4;
        ycoa.ajaxLoadPost("/api/culture/manual.php", JSON.stringify(manual), function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    };
    self_.editManual = function (manual) {
        $('#edit_manual_form #id').val(manual.id);
        $('#edit_manual_form #sys_class').val(manual.sys_class);
        $('#edit_manual_form #sys_title').val(manual.sys_title);
        $('#edit_manual_form #sys_content').val(manual.sys_content);
        $('#edit_manual_form #file_path').val(manual.file_path);
        $('#edit_manual_form #pdf_file_name').val(manual.pdf_file_name);
        $(".edit_upload_btn span").html("添加附件");
        $(".edit_upload_bar").width(0);
    };
    self_.showManual = function (manual) {
        $("#myzhiModal #sys_contents").val(manual.sys_content);
        $("#cancel_" + manual.id).show();
    };
    self_.downManDoc = function(data)
    {
        window.location.href = data;
    };
    self_.showFile = function (manual) {
        window.open(ycoa.getNoCacheUrl("/api/culture/show_file.php?doc=" + base64encode(manual.pdf_file_name)));
    };
    self_.downLoadFile = function (manual) {
        window.location.href = manual.file_path;
    };
    self_.deleteManual = function (manual) {
        ycoa.UI.messageBox.confirm("确定要删除该条制度信息吗?删除后不可恢复~", function (btn) {
            if (btn) {
                manual.action = 5;
                ycoa.ajaxLoadPost("/api/culture/manual.php", JSON.stringify(manual), function (result) {
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
    $("#myModal #sys_class").autoEditSelecter([{id: '考勤', text: "考勤"}, {id: '福利', text: "福利", default: true}]);
    $("#myEditModal #sys_class").autoEditSelecter([{id: '考勤', text: "考勤"}, {id: '福利', text: "福利", default: true}]);
    $("#dataTable").reLoad(function () {
        reLoadData({});
        $("#searchUserName").val('');
    });
    $("#dataTable").sort(function (data) {
        reLoadData({sort: data.sort, sortname: data.sortname, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize()});
    });
    $("#dataTable").searchAutoStatus([{id: 0, text: "福利"}, {id: 0, text: "考勤"}, {id: 0, text: "自定义"}, {id: 0, text: "全部"}], function (data) {
        if (data.text != "全部") {
            reLoadData({sys_class: data.text});
        } else {
            reLoadData({});
        }
    }, '手册分类');
    $("#dataTable").searchUserName(function (name) {
        reLoadData({searchName: name});
    }, "标题");
    ko.applyBindings(ManualListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize()});
    $('.reload').click(function () {
        reLoadData({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo()});
    });
});
function reLoadData(data) {
    ManualListViewModel.listManual(data);
}

function forManSubmit() {
    $("#add_manual_form").ajaxSubmit({
        type: 'post',
        url: "/api/culture/manual.php",
        success: function (data) {
            if (data.code == 0) {
                $("#add_manual_form").parent().parent().find('#btn_close_primary').click();
                ycoa.UI.toast.success(data.msg);
                ManualListViewModel.listManual({});
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
function forManEditSubmit() {
    $("#edit_manual_form").ajaxSubmit({
        type: 'post',
        url: "/api/culture/manual.php",
        success: function (data) {
            
            if (data.code == 0) {
                $("#edit_manual_form").parent().parent().find('#btn_close_primary').click();
                ycoa.UI.toast.success(data.msg);
                ManualListViewModel.listManual({});
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