var AccessListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.accessList = ko.observableArray([]);
    self_.listAccess = function (data) {
        ycoa.ajaxLoadGet("/api/sale/qq_access.php", data, function (results) {
            self_.accessList.removeAll();
            $.each(results.list, function (index, access) {
                access.dele = ycoa.SESSION.PERMIT.hasPagePermitButton("2011002");
                access.edit = ycoa.SESSION.PERMIT.hasPagePermitButton("2011003");
                access.show = ycoa.SESSION.PERMIT.hasPagePermitButton("2011004");
                access.can_access = ycoa.SESSION.PERMIT.hasPagePermitButton("2011005") && (!access.access_time) ? true : false;
                access.type = (access.access_time) ? 1 : 0;
                self_.accessList.push(access);
            });

            var TodayTotals = results.TodayTotals;

            var TodayBaiduPCTotals = TodayTotals.TodayBaiduPCTotals;
            var TodayBaiduMTotals = TodayTotals.TodayBaiduMTotals;
            var Today360Totals = TodayTotals.Today360Totals;
            var TodaySogouTotals = TodayTotals.TodaySogouTotals;

            var TodayTotalsHtml = "<span style='color: rgb(194, 22, 22);'>百度PC:YD(" + TodayBaiduPCTotals + ":" + TodayBaiduMTotals + ")&nbsp;合计&nbsp;" + (TodayBaiduPCTotals + TodayBaiduMTotals) + "单&nbsp;</span>";
            TodayTotalsHtml += "<span style='color:rgb(158, 158, 21);'>360:" + Today360Totals + "&nbsp;</span>";
            TodayTotalsHtml += "<span style='color:rgb(26, 26, 187);'>搜狗:" + TodaySogouTotals + "&nbsp;</span>";
            TodayTotalsHtml += "<span style='color:rgb(8, 105, 8);'>总数:" + (TodayBaiduPCTotals + TodayBaiduMTotals + Today360Totals + TodaySogouTotals) + "</span>";
            $('.TodayTotals').html(TodayTotalsHtml);

            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                reLoadData({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(), status: $("#status").val(), searchName: $("#searchUserName").val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName()});
            }, function (pageNo) {
                reLoadData({action: 1, pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(), status: $("#status").val(), searchName: $("#searchUserName").val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName()});
            });
        });
    };
    self_.delAccess = function (access) {
        ycoa.UI.messageBox.confirm('确认删除？', function (del) {
            if (del) {
                access.action = 3;
                ycoa.ajaxLoadPost("/api/sale/qq_access.php", JSON.stringify(access), function (result) {
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
    self_.editAccess = function (access) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + access.id).show();
        $("#submit_" + access.id).show();
        $("#cancel_" + access.id).show();
        $("#tr_" + access.id + " input,#tr_" + access.id + " textarea").removeAttr("disabled");
    };
    self_.doAccess = function (access) {
        access.action = 2;
        access.do_access = '我干';
        ycoa.ajaxLoadPost("/api/sale/qq_access.php", JSON.stringify(access), function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success('确认成功~');
                reLoadData({action: 1});
            } else {
                ycoa.UI.toast.error('确认失败,请稍后重试~');
            }
            ycoa.UI.block.hide();
        });
    };
    self_.cancelTr = function (access) {
        $("#tr_" + access.id).hide();
        $("#submit_" + access.id).hide();
        $("#cancel_" + access.id).hide();
    };
    self_.showAccess = function (access) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + access.id).show();
        $("#cancel_" + access.id).show();
        $("#tr_" + access.id + " input,#tr_" + access.id + " textarea").attr("disabled", "");
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
    }, '按关键字搜索');
    ko.applyBindings(AccessListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1});
    $("#dataTable thead input[id='checkall']").change(function () {
        if ($(this).prop("checked")) {
            $("#dataTable tbody input[type='checkbox']").prop("checked", "checked");
        } else {
            $("#dataTable tbody input[type='checkbox']").removeAttr("checked");
        }
    });
    $("#btn_submit_primary").click(function () {
        $("#add_access_form").submit();
    });
    $("#open_dialog_btn").click(function () {
        $("#add_access_form input,#add_access_form textarea").each(function () {
            if (!$(this).hasClass("not-clear")) {
                $(this).val("");
            }
        });
        $("#add_access_form input[type='checkbox']").removeAttr("checked");
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
        var work_kv = new Array();
        $("#" + formid + " .second_table input,#" + formid + " .second_table textarea").each(function () {
            if ($(this).attr("placeholder")) {
                work_kv.push('"' + ($(this).attr('name')) + '":"' + $(this).attr('placeholder') + '"');
            }
        });
        data.key_names = $.parseJSON("{" + work_kv.toString() + "}");
        data = JSON.stringify(data);
        ycoa.ajaxLoadPost("/api/sale/qq_access.php", data, function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({action: 1});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    });

    $("#quik_write").keypress(function (e) {
        if (e.keyCode == 13) {
            var str = $(this).val().trim();
            if (!str || (str.indexOf("客人") === -1)) {
                ycoa.UI.toast.warning("请复制正确格式的数据进入文本框~");
            } else {
                str = str.replace(/\n/g, "_");
                str = str.replace(/ /g, '_');
                str = str.replace(/客人/g, "_");
                str = str.replace("__", "_");
                str = str.split("_");
                $("#add_access_form #customer_address").val(str[0]);
                $("#add_access_form #customer_num").val(str[1]);
                $("#add_access_form #qq_num").val(str[4]);
            }
            return false;
        }
    });
    $(".data_list_div").live("mouseenter", function () {
        var self_ = $(this);
        self_.data('lastEnter', new Date().getTime());
        setTimeout(function () {
            var t1 = new Date().getTime(), t2 = self_.data('lastEnter');
            if (t2 > 0 && t1 - t2 >= 500) {
                var w_id = self_.attr("val");
                $("#dataLog_detail_" + w_id).animate({opacity: 'show'}, 50, function () {
                    $(this).addClass("data_open");
                });
                $("#dataLog_detail_" + w_id).html("<img src='../../assets/global/img/input-spinner.gif' style='margin-top: 130px'>");
                $.get(ycoa.getNoCacheUrl("/api/sys/dataChangeLog.php"), {action: 10, obj_id: w_id}, function (result) {
                    if (result.list.length > 0 && result.list) {
                        var html = "<ul>";
                        $.each(result.list, function (idnex, d) {
                            html += "<li>[" + d.addtime + " (" + d.username + ")] <br>" + d.changed_desc + "</li>";
                        });
                        html += "</ul>";
                        $("#dataLog_detail_" + w_id).html(html);
                    } else {
                        $("#dataLog_detail_" + w_id).html("<img src='../../assets/global/img/workflowLog_detail_nodata.png'>");
                    }
                });
            }
        }, 500);
    }).live('mouseleave', function () {
        $(this).data('lastEnter', 0);
    });
    $(".data_open").live("mouseleave", function () {
        $(this).hide();
        $(this).removeClass("data_open");
    });
});
function reLoadData(data) {
    AccessListViewModel.listAccess(data);
}