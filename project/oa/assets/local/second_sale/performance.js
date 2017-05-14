var rception_array, server_sales_array, headmaster_array;
var PerformanceListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.second_performanceList = ko.observableArray([]);
    self_.listSecond_performance = function (data) {

        ycoa.ajaxLoadGet("/api/second_sale/performance.php", data, function (results) {
            self_.second_performanceList.removeAll();
            $.each(results.list, function (index, generationOperation) {
                // generationOperation.isArrears_text = generationOperation.isArrears === 1 ? "√" : "";
                generationOperation.dele = ycoa.SESSION.PERMIT.hasPagePermitButton('3050604');
                generationOperation.edit = ycoa.SESSION.PERMIT.hasPagePermitButton('3050602');
                generationOperation.show = ycoa.SESSION.PERMIT.hasPagePermitButton('3050603');
                self_.second_performanceList.push(generationOperation);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                var data = {
                    action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: pageSize,
                    workName: $("#workName").val(), customer_type: $("#search_type").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                };
                if (data.searchStartTime || data.searchEndTime) {
                    data.searchTime = "";
                }
                reLoadData(data);
            }, function (pageNo) {
                var data = {
                    action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(),
                    workName: $("#workName").val(), customer_type: $("#search_type").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                };
                if (data.searchStartTime || data.searchEndTime) {
                    data.searchTime = "";
                }
                reLoadData(data);
            });
        });
    };
    self_.delGenerationOperation = function (generationOperation) {
        ycoa.UI.messageBox.confirm('确认删除？', function (del) {
            if (del) {
                generationOperation.action = 2;
                ycoa.ajaxLoadPost("/api/second_sale/performance.php", JSON.stringify(generationOperation), function (result) {
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
    self_.editGenerationOperation = function (generationOperation) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + generationOperation.id).show();
        $("#submit_" + generationOperation.id).show();
        $("#cancel_" + generationOperation.id).show();
        if (!$("#form_" + generationOperation.id).attr('autoEditSelecter')) {
            initEditSeleter($("#form_" + generationOperation.id));
        }
        $("#tr_" + generationOperation.id + " input,#tr_" + generationOperation.id + " textarea").removeAttr("disabled");
        // dateSplit("#form_" + generationOperation.id);
    };
    self_.showGenerationOperation = function (generationOperation) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + generationOperation.id).show();
        $("#cancel_" + generationOperation.id).show();
        if (!$("#form_" + generationOperation.id).attr('autoEditSelecter')) {
            initEditSeleter($("#form_" + generationOperation.id));
        }
        $("#tr_" + generationOperation.id + " input,#tr_" + generationOperation.id + " textarea").attr("disabled", "");
    };
    self_.cancelTr = function (generationOperation) {
        $("#tr_" + generationOperation.id).hide();
        $("#submit_" + generationOperation.id).hide();
        $("#cancel_" + generationOperation.id).hide();
    };
    self_.doEditSubmit = function (generationOperation) {
        var formid = "form_" + generationOperation.id;
        var data = $("#" + formid).serializeJson();
        data.remark = $("#" + formid + " #remark_edit").html();
        data.action = 3;
        data = JSON.stringify(data);
        ycoa.ajaxLoadPost("/api/second_sale/performance.php", data, function (result) {
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
    ko.applyBindings(PerformanceListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1});
    $("#dataTable").sort(function (data) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $("#workName").val(), customer_type: $("#search_type").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    });
    $("#dataTable").reLoad(function () {
        $("#workName").val("");
        $("#search_type").val("");
        $("#searchDateTime").val("");
        $('#searchStartTime').val("");
        $('#searchEndTime').val("");
        reLoadData({action: 1});
    });

    $("body").on("click", '#headmaster', function () {
        ycoa.UI.employeeSeleter({el: $(this), array: headmaster_array}, function (data, el) {
            el.val(data.text);
            el.next("#headmaster_id").val(data.id);
        });
    });

    $("body").on("click", '#platform_rception', function () {
        ycoa.UI.employeeSeleter({el: $(this), array: rception_array}, function (data, el) {
            el.val(data.text);
            el.next("#platform_rception_id").val(data.id);
        });
    });

    $("body").on("click", '#dataTable .checkbox', function () {
        countMoney(5);
    });
    $('#btn_importexcel_primary').click(function () {
        ycoa.UI.block.show();
        $('#importexcel_form').ajaxSubmit({
            type: 'post',
            url: "../../api/second_sale/performance.php?upload=1",
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
                url: '../../api/second_sale/performance.php',
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

    $("#dataTable").searchUserName(function (name) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: name, customer_type: $("#search_type").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    }, '关键字', 'workName');
    $("#dataTable").searchAutoStatus([{id: 1, text: '优程'}, {id: 2, text: '领远'}], function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $("#workName").val(), customer_type: d.id, searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
        $("#search_type").val(d.id);
    }, '按客户类型筛选');
    $("#dataTable").searchDateTimeSlot(function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $("#workName").val(), customer_type: $("#search_type").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    });
    $("#dataTable").searchDateTime(function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $("#workName").val(), customer_type: $("#search_type").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    });
    $("#dataTable thead input[id='checkall']").change(function () {
        if ($(this).prop("checked")) {
            $("#dataTable tbody input[type='checkbox']").prop("checked", "checked");
        } else {
            $("#dataTable tbody input[type='checkbox']").removeAttr("checked");

        }
        countMoney(5);
    });
    $("#open_dialog_btn").click(function () {
        $("#add_generationOperation_form input,#add_generationOperation_form textarea").each(function () {
            if (!$(this).hasClass("not-clear")) {
                if ($(this).attr("id") != "add_time") {
                    $(this).val("");
                }
            }
        });
        $(".has-error,.has-success").each(function () {
            $(this).removeClass("has-error").removeClass("has-success");
        });
        $(".fa-warning,.fa-check").each(function () {
            $(this).removeClass("fa-warning").removeClass("fa-check");
        });
    });
    $("#open_dialog_fill_btn").click(function () {
        $("#add_fillarrears_form input").each(function () {
            if (!$(this).hasClass("not-clear")) {
                $(this).val("");
            }
        });
        $("#add_fillarrears_form #remark_fillarrears").html("");
        $(".has-error,.has-success").each(function () {
            $(this).removeClass("has-error").removeClass("has-success");
        });
        $(".fa-warning,.fa-check").each(function () {
            $(this).removeClass("fa-warning").removeClass("fa-check");
        });
    });
    $("body").on("click", '.auto_tr', function () {
        var json_str = $(this).attr("v");
        json_str = $.parseJSON(json_str);
        $("#add_generationOperation_form input").each(function () {
            var el = $(this);
            el.val(json_str[el.attr("id")]);
        });
        $("#add_generationOperation_form #parent_id").val(json_str.id);
        $("#add_generationOperation_form #platform_rception").val(json_str.platform_sales);
        $("#add_generationOperation_form #platform_rception_id").val(json_str.platform_sales_id);
        var myDate = new Date();
        $("#add_generationOperation_form #add_date").val(myDate.getFullYear() + "-" + (myDate.getMonth() + 1) + "-" + myDate.getDate());
        $("#add_generationOperation_form #add_time").val('');
        $(".div_avatar_outer").hide();
    });
    $('#open_dialog_btn').click(function () {
        var myDate = new Date();
        $("#add_generationOperation_form #add_date").val(myDate.getFullYear() + "-" + (myDate.getMonth() + 1) + "-" + myDate.getDate());
    });
    $(".div_avatar_close_btn").click(function () {
        $(".div_avatar_outer").hide();
    });
    $("#btn_submit_primary").click(function () {
        $("#add_generationOperation_form").submit();
    });
    $("#btn_submit_fill_primary").click(function () {
        $("#add_fillarrears_form").submit();
    });
    $('#add_generationOperation_form #qq').keypress(function (e) {
        if (e.keyCode == 13) {
            if ($(this).val()) {
                $.get("/api/second_sale/generationOperation.php", {action: 5, qq: $(this).val()}, function (data) {
                    if (data.length == 1) {
                        var data = data[0];
                        $('#lastmoney').val(data.final_money);
                        $("#add_generationOperation_form input").each(function () {
                            var el = $(this);
                            if (el.attr("id") == "rception_money" || el.attr("id") == "final_money" || el.attr("id") == "payment_amount") {
                                el.val(0);
                            } else if (el.attr("id") == "add_time") {
                                return true;
                            } else {
                                el.val(data[el.attr("id")]);
                            }
                        });
                        $("#add_generationOperation_form #parent_id").val(data.id);
                        $("#add_generationOperation_form #platform_rception").val(data.platform_sales);
                        $("#add_generationOperation_form #platform_rception_id").val(data.platform_sales_id);
                        var myDate = new Date();
                        $("#add_generationOperation_form #add_date").val(myDate.getFullYear() + "-" + (myDate.getMonth() + 1) + "-" + myDate.getDate());
                        $("#add_generationOperation_form #add_time").val('');
                    } else if (data.length > 1) {
                        var html = "";
                        $.each(data, function (index, d) {
                            html += "<div class='auto_tr' v='" + (JSON.stringify(d)) + "'>";
                            html += "<div class='auto_td' title='" + (d.add_time) + "'>" + (d.add_time) + "</div>";
                            html += "<div class='auto_td' title='" + (d.platform_num) + "'>" + (d.platform_num) + "</div>";
                            html += "<div class='auto_td' title='" + (d.qq) + "'>" + (d.qq) + "</div>";
                            html += "<div class='auto_td' title='" + (d.payment_amount) + "'>" + (d.payment_amount) + "</div>";
                            html += "<div class='auto_td' title='" + (d.customer) + "'>" + (d.customer) + "</div>";
                            html += "<div class='auto_td' title='" + (d.platform_sales) + "'>" + (d.platform_sales) + "</div>";
                            html += "<div class='auto_td' title='" + (d.headmaster) + "'>" + (d.headmaster) + "</div>";
                            html += "</div>";
                            d.platform_rception = d.platform_sales;
                        });
                        $(".auto_tbody").html(html);
                        var y = $(window).height();
                        var x = $(window).width();
                        $(".div_avatar_outer").css({top: ((y - 500) / 2) + 'px', left: ((x - 750) / 2) + 'px'}).show();
                    } else if (data.length == 0) {
                        ycoa.UI.toast.warning("未匹配到相应的数据,请核对后重试~");
                        $("#add_generationOperation_form input").val("");
                        $("#add_generationOperation_form #remark_fillarrears").html("");
                    }
                });
            }
        }
    });
    $("#btn_toexcel_primary").click(function () {
        var start_time = $("#start_time").val();
        var end_time = $("#end_time").val();
        if (start_time || end_time) {
            window.location.href = "/api/second_sale/performance.php?action=10&start_time=" + start_time + "&end_time=" + end_time;
        }
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
    $("#add_generationOperation_form #isArrears").autoRadio(array['isArrears']);
//    $("#add_generationOperation_form #payment_method").autoEditSelecter(array['payment_method']);
    $("#add_generationOperation_form #customer_type").autoEditSelecter(array['customer_type']);
    $("#add_generationOperation_form #second_type").autoEditSelecter(array['second_type']);
    $("#add_generationOperation_form #remark").pasteImgEvent();
    $("#add_fillarrears_form #remark_fillarrears").pasteImgEvent();
    
    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 9}, function (res) {
        $("#add_generationOperation_form #rcept_account").autoEditSelecter(res, function (d) {
            $("#add_generationOperation_form #payment_method").val(d.id);
            $("#add_generationOperation_form #pay_rate").val(d.cv);
        });
        rcept_account = res;
    });
    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 1}, function (res) {
//        $("#add_generationOperation_form #platform_rception").autoEditSelecter(res, function (d) {
//            $("#add_generationOperation_form #platform_rception_id").val(d.id);
//        });
        rception_array = res;
    });
    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 5}, function (res) {
        $("#add_generationOperation_form #headmaster").autoEditSelecter(res, function (d) {
            $("#add_generationOperation_form #headmaster_id").val(d.id);
        });
        headmaster_array = res;
    });

    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 6}, function (res) {
        $("#add_generationOperation_form #server_sales").autoEditSelecter(res, function (d) {
            $("#add_generationOperation_form #server_sales_id").val(d.id);
        });
        server_sales_array = res;
    });

    if (jQuery.ui) {
        $('.div_avatar_outer').draggable({handle: ".div_avatar_close_title"});
    }
});
function reLoadData(data) {
    PerformanceListViewModel.listSecond_performance(data);
}

//日期拆分函数
function dateSplit(object) {
    var date = $(object + " #add_time").val();
    dateArray = date.split(" ");
    $(object + " .input-time").val(dateArray[1]);
    $(object + " .input-date").val(dateArray[0]);
}



function updateCL(second_performance) {
    second_performance.action = 2;
    ycoa.ajaxLoadPost("/api/second_sale/performance.php", JSON.stringify(second_performance), function (result) {
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
    // isArrears: [{id: '1', text: '是'}, {id: '0', text: '否'}],
    payment_method: [{id: '银行卡转款', text: '银行卡转款'}, {id: '信用卡', text: '信用卡'}, {id: '花呗', text: '花呗'}, {id: '支付宝', text: '支付宝'}, {id: '微信', text: '微信'}, {id: '财付通', text: '财付通'}, {id: 'QQ钱包', text: 'QQ钱包'}, {id: '非时序店铺链接', text: '非时序店铺链接'}],
    customer_type: [{id: '1', text: '优程'}, {id: '2', text: '领远'}],
    second_type: [{id: '1', text: '升级'}, {id: '2', text: 'VIP'}, {id: '3', text: '装修'}, {id: '4', text: '课程'}, {id: '5', text: '活动'}, {id: '6', text: '货源'}]
};
function initEditSeleter(el) {
    // $("#isArrears", el).autoRadio(array['isArrears']);
//    $("#payment_method", el).autoEditSelecter(array['payment_method']);
    $("#customer_type", el).autoEditSelecter(array['customer_type']);
    $('#second_type', el).autoEditSelecter(array['second_type']);
//    $("#platform_rception", el).autoEditSelecter(rception_array, function (d) {
//        $("platform_rception", el).val(d.id);
//    });
//    $("#headmaster", el).autoEditSelecter(headmaster_array, function (d) {
//        $("#headmaster_id", el).val(d.id);
//    });
    $("#headmaster", el).autoEditSelecter(headmaster_array, function (d) {
           $("#headmaster_id", el).val(d.id);
       });
    $("#rcept_account", el).autoEditSelecter(rcept_account, function (d) {
        $("#payment_method", el).val(d.id);
        $("#pay_rate",el).val(d.cv);
    });
    $("#remark_edit", el).pasteImgEvent();
    el.attr('autoEditSelecter', 'autoEditSelecter');
}