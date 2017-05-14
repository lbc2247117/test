var complainVisitListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.complainVisitList = ko.observableArray([]);
    self_.listCompainVisit = function (data) {
        ycoa.ajaxLoadGet("/api/complain/complain_visit.php", data, function (results) {
            self_.complainVisitList.removeAll();
            $.each(results.list, function (index, complainRecord) {
                debugger
                if (complainRecord.reflectPro == "正常")
                {
                    complainRecord.add = ycoa.SESSION.PERMIT.hasPagePermitButton("2070301");
                    complainRecord.edit = false;
                    complainRecord.dele = ycoa.SESSION.PERMIT.hasPagePermitButton("2070303");
                    complainRecord.deal = false;
                    complainRecord.second = false;
                } 
                else if(complainRecord.isDeal == "已处理"){
                    complainRecord.add = ycoa.SESSION.PERMIT.hasPagePermitButton("2070301");
                    complainRecord.edit = false;
                    complainRecord.dele = ycoa.SESSION.PERMIT.hasPagePermitButton("2070303");
                    complainRecord.deal = false;
                    complainRecord.second = ycoa.SESSION.PERMIT.hasPagePermitButton("2070305");
                }
                else if(complainRecord.isDeal == "已二次回访")
                {
                    complainRecord.add = ycoa.SESSION.PERMIT.hasPagePermitButton("2070301");
                    complainRecord.edit = false;
                    complainRecord.dele = ycoa.SESSION.PERMIT.hasPagePermitButton("2070303");
                    complainRecord.deal = false;
                    complainRecord.second = false;
                }
                else
                {
                    complainRecord.add = ycoa.SESSION.PERMIT.hasPagePermitButton("2070301");
                    complainRecord.edit = ycoa.SESSION.PERMIT.hasPagePermitButton("2070302");
                    complainRecord.dele = ycoa.SESSION.PERMIT.hasPagePermitButton("2070303");
                    complainRecord.deal = ycoa.SESSION.PERMIT.hasPagePermitButton("2070304");
                    complainRecord.second = false;
                }
                self_.complainVisitList.push(complainRecord);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                reLoadData({action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: pageSize, searchName: $("#searchName").val()});
            }, function (pageNo) {
                reLoadData({action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(), searchName: $("#searchName").val()});
            });
        });
    };
    self_.delComplain = function (complaintRecord) {
        ycoa.UI.messageBox.confirm('确认删除？', function (del) {
            if (del) {
                complaintRecord.action = 2;
                ycoa.ajaxLoadPost("/api/complain/complain_visit.php", JSON.stringify(complaintRecord), function (result) {
                    debugger
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
    self_.doEditComplain = function (complaintRecord) {
        if (complaintRecord.isDeal == "未处理")
        {
            var formid = "form_" + complaintRecord.id;
            complaintRecord.action = 3;
            var work_kv = new Array();
            $("#" + formid + " .second_table input,#" + formid + " .second_table textarea").each(function () {
                if ($(this).attr("placeholder")) {
                    work_kv.push('"' + ($(this).attr('name')) + '":"' + $(this).attr('placeholder') + '"');
                }
            });
            complaintRecord.key_names = $.parseJSON("{" + work_kv.toString() + "}");
            ycoa.ajaxLoadPost("/api/complain/complain_visit.php", JSON.stringify(complaintRecord), function (result) {
                debugger
                if (result.code == 0) {
                    ycoa.UI.toast.success(result.msg);
                    reLoadData({action: 1});
                } else {
                    ycoa.UI.toast.error(result.msg);
                }
                ycoa.UI.block.hide();
            });
        } else
        {
            ycoa.UI.toast.error("已处理的投诉问题不能修改~");
        }
    };
    self_.editComplain = function (complaintRecord) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + complaintRecord.id).show();
        $("#submit_" + complaintRecord.id).show();
        $("#cancel_" + complaintRecord.id).show();
        $("#tr_" + complaintRecord.id + " input,#tr_" + complaintRecord.id + " textarea").removeAttr("disabled");
    };
    self_.cancelTr = function (complaintRecord) {
        $("#tr_" + complaintRecord.id).hide();
        $("#submit_" + complaintRecord.id).hide();
        $("#cancel_" + complaintRecord.id).hide();
    };
    self_.dealComplain = function (complaintRecord) {
        $("#id").val(complaintRecord.id);
        $("#clientinfo").val(complaintRecord.customerQQ);
        $("#mobile").val(complaintRecord.customerPhone);
    };
    self_.secondVisitComplain = function (complaintRecord) {
        $("#secondVisitId").val(complaintRecord.id);
    };
    
//    self_.showLog = function (complaintRecord) {
//        var w_id = complaintRecord.id;
//        $("#dataLog_detail_" + w_id).animate({opacity: 'show'}, 50, function () {
//            $(this).addClass("data_open");
//        });
//        $("#dataLog_detail_" + w_id).html("<img src='../../assets/global/img/input-spinner.gif' style='margin-top: 130px'>");
//        $.get(ycoa.getNoCacheUrl("/api/sys/dataChangeLog.php"), {action: 11, obj_id: w_id}, function (result) {
//            if (result.list.length > 0 && result.list) {
//                var html = "<ul>";
//                $.each(result.list, function (idnex, d) {
//                    html += "<li>[" + d.addtime + " (" + d.username + ")] <br>" + d.changed_desc + "</li>";
//                });
//                html += "</ul>";
//                $("#dataLog_detail_" + w_id).html(html);
//            } else {
//                $("#dataLog_detail_" + w_id).html("<img src='../../assets/global/img/workflowLog_detail_nodata.png'>");
//            }
//        });
//    };
}();
$(function () {
    ko.applyBindings(complainVisitListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1});
    $("#dataTable").sort(function (data) {
        reLoadData({action: 1, sort: data.sort, sortname: data.sortname, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), searchName: $("#searchName").val()});
    });
    $("#dataTable").reLoad(function () {
        reLoadData({action: 1});
        $('#searchName').val('');
    });

    $("#dataTable").searchUserName(function (name) {
        reLoadData({action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), searchName: name});
    }, '按关键字查找', 'searchName');

    //投诉处理结果
    $("body").on("change", "#dealResult", function () {
        if ($("#dealResult").val() == "协商退款")
        {
            $("#refund").css("display", "block");
        } else
        {
            $("#refund").css("display", "none");
        }
    });

    $("#dataTable thead input[id='checkall']").change(function () {
        if ($(this).prop("checked")) {
            $("#dataTable tbody input[type='checkbox']").prop("checked", "checked");
        } else {
            $("#dataTable tbody input[type='checkbox']").removeAttr("checked");
        }
    });
    $(".date-picker-bind-mouseover").datepicker({autoclose: true});
    $("body").on("click", "#btn_toexcel_primary", function () {
        var start_time = $("#toexcel_form #start_time").val();
        var end_time = $("#toexcel_form #end_time").val();
        if (start_time || end_time) {
            location.href = '/api/complain/complain_visit.php?start_time=' + start_time + '&end_time=' + end_time + '&action=11';
        }
    });
    $("body").on("mouseleave", ".data_open", function () {
        $(this).hide();
        $(this).removeClass("data_open");
    });
});
function reLoadData(data) {
    data.action = 1;
    complainVisitListViewModel.listCompainVisit(data);
}

function forDealSubmit()
{
    if ($("#dealResult").val() == "协商退款" && $("#refundMoney").val() == "")
    {
        ycoa.UI.toast.error("请输入退款金额");
        return false;
    }
    var data = $("#add_deal_form").serializeJson();
    data.action = 4;
    debugger
    ycoa.ajaxLoadPost("/api/complain/complain_visit.php", JSON.stringify(data), function (result) {
        debugger
        if (result.code == 0) {
            $("#btn_close_dealback").click();
            $("#add_deal_form input").val("");
            ycoa.UI.toast.success(result.msg);
            reLoadData({action: 1});
        } else {
            ycoa.UI.toast.error(result.msg);
        }
        ycoa.UI.block.hide();
    });
}

function forSecondVisitSubmit()
{
    if($("#remark").val() == "" || $("#remark").val() == null)
    {
        ycoa.UI.toast.error("请输入备注内容~");
        return false;
    }
    var data = $("#add_secondvisit_form").serializeJson();
    data.action = 5;
    ycoa.ajaxLoadPost("/api/complain/complain_visit.php", JSON.stringify(data), function (result) {
        if (result.code == 0) {
            $("#btn_close_secondvisitback").click();
            $("#add_secondvisit_form input").val("");
            ycoa.UI.toast.success(result.msg);
            reLoadData({action: 1});
        } else {
            ycoa.UI.toast.error(result.msg);
        }
        ycoa.UI.block.hide();
    });
}