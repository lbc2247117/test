var rception_array, customer_array, headmaster_array, ly_array, rcept_account;

var GenerationOperationListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.generationOperationList = ko.observableArray([]);
    self_.listGenerationOperation = function (data) {
        ycoa.ajaxLoadGet("/api/second_sale/generationOperation.php", data, function (results) {
            self_.generationOperationList.removeAll();
            $.each(results.list, function (index, generationOperation) {
                generationOperation.isArrears_text = generationOperation.isArrears === 1 ? "√" : "";
                generationOperation.dele = ycoa.SESSION.PERMIT.hasPagePermitButton('3050402');
                generationOperation.edit = ycoa.SESSION.PERMIT.hasPagePermitButton('3050403');
                generationOperation.show = ycoa.SESSION.PERMIT.hasPagePermitButton('3050404');
                generationOperation.repairMoney = generationOperation.payType == 2 ? false : true;
                generationOperation.statusTxt = generationOperation.payType == 2 ? "补款" : "定金";
                generationOperation.typeCss = generationOperation.payType == 2 ? 1 : 0;
                if (generationOperation.customer_type == '领远')
                    generationOperation.customer_type = 2;
                else
                    generationOperation.customer_type = 1;
                self_.generationOperationList.push(generationOperation);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                var data = {
                    action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
                    workName: $('#workName').val(), payType: "(0,2)", searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                };
                if (data.searchStartTime || data.searchEndTime) {
                    data.searchTime = "";
                }
                reLoadData(data);
            }, function (pageNo) {
                var data = {
                    action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(),
                    workName: $('#workName').val(), payType: "(0,2)", searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                };
                if (data.searchStartTime || data.searchEndTime) {
                    data.searchTime = "";
                }
                reLoadData(data);
            });
        });
    };
    self_.delGenerationOperation = function (generationOperation) {
        if ($("#customer_td_" + generationOperation.id + " .st_is_approve").val() == 2) {
            ycoa.UI.toast.error("该业绩财务已经审核，不能删除");
        } else {
            ycoa.UI.messageBox.confirm('确认删除？', function (del) {
                if (del) {
                    generationOperation.action = 2;
                    ycoa.ajaxLoadPost("/api/second_sale/generationOperation.php", JSON.stringify(generationOperation), function (result) {
                        if (result.code == 0) {
                            ycoa.UI.toast.success(result.msg);
                            var data = {
                                action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
                                workName: $('#workName').val(), payType: "(0,2)", searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                            };
                            reLoadData(data);
                        } else {
                            ycoa.UI.toast.error(result.msg);
                        }
                        ycoa.UI.block.hide();
                    });
                }
            });
        }
    };
    self_.editGenerationOperation = function (generationOperation) {
        if ($("#customer_td_" + generationOperation.id + " .st_is_approve").val() == 2) {
            ycoa.UI.toast.error("该业绩财务已经审核，不能再修改");
        } else {
            $("#edit_generationOperation_form #add_time").val(generationOperation.add_time);
            $("#edit_generationOperation_form #platform_num").val(generationOperation.platform_num);
            $("#edit_generationOperation_form #qq").val(generationOperation.qq);
            $("#edit_generationOperation_form #customer_type").val(generationOperation.customer_type);
            $("#edit_generationOperation_form #sales_numbers").val(generationOperation.sales_numbers);
            $("#edit_generationOperation_form #rception_money").val(generationOperation.rception_money);
            $("#edit_generationOperation_form #platform_sales").val(generationOperation.platform_sales);
            $("#edit_generationOperation_form #platform_sales_id").val(generationOperation.platform_sales_id);
            $("#edit_generationOperation_form #customer").val(generationOperation.customer);
            $("#edit_generationOperation_form #customer_id").val(generationOperation.customer_id);
            $("#edit_generationOperation_form #final_money").val(generationOperation.final_money);
            $("#edit_generationOperation_form #rcept_account").val(generationOperation.rcept_account);
            $("#edit_generationOperation_form #headmaster").val(generationOperation.headmaster);
            $("#edit_generationOperation_form #headmaster_id").val(generationOperation.headmaster_id);
            $("#editgenId").val(generationOperation.id);
            $("#ispayment_amount").val(generationOperation.payType);
            generationOperation.action = 9;
            var html = "";
            ycoa.ajaxLoadPost("/api/second_sale/generationOperation.php", JSON.stringify(generationOperation), function (result) {
                if (result.code == 0) {
                    $.each(result.list, function (index, rec) {
                        if (rec.approve_status == 0) {
                            html += "<div class='form-group small look'><label class='control-label col-md-2 small-form' for='payment_amount'>付款金额</label>";
                            html += "<div class='col-md-2 small-form'><span class='form-control payment_amount'>" + rec.pay_money/(1-rec.pay_rate) + "</span><input type='hidden'  name='o_payment_amount' value='" + rec.pay_money + "'/></div><input type='hidden' name='o_pay_rate_each' value=" + rec.pay_rate + ">";
                            html += "<label class='control-label col-md-2 small-form' for='rcept_account'>收款账号</label>";
                            html += "<div class='col-md-2 small-form rcept_account' title=" + rec.pay_account_type + " data-toggle='tooltip' data-placement='bottom'>" + rec.pay_account_type + "</div>";
                            html += "<label class='control-label col-md-2 small-form' for='transfer_time'>到账日期</label>";
                            html += "<div class='col-md-2 small-form transfer_time'><span class='form-control transfer_time'>" + rec.transfer_time.split(' ')[0] + "</span></div>";
                            html += "<label class='control-label col-md-2 small-form' for='pay_user'>付款人</label>"
                            html += "<div class='col-md-2 small-form'><span class='form-control'>" + rec.pay_username + "<span></div>";
                            html += "<span href='javascript:void' class='fa fa-check'></span>";
                            html += "</div>";
                        } else {
                            html += "<div class='form-group small'><label class='control-label col-md-2 small-form' for='payment_amount'>付款金额</label><input type='hidden' id='recid' name='recid' value='" + rec.id + "'>";
                            html += "<div class='col-md-2 small-form'><input type='text' class='form-control payment_amount' name='payment_amount' value='" + rec.pay_money/(1-rec.pay_rate) + "' id='payment_amount' placeholder='付款金额'/></div><input type='hidden' name='pay_rate_each' value=" + rec.pay_rate + ">";
                            html += "<label class='control-label col-md-2 small-form' for='rcept_account'>收款账号</label>";
                            html += "<div class='col-md-2 small-form rcept_account' title=" + rec.pay_account_type + " data-toggle='tooltip' data-placement='bottom'>" + rec.pay_account_type + "</div><input type='hidden' name='rcept_account_each' value='" + rec.pay_account_type + "' /><input type='hidden' name='payment_method_each' value='" + rec.payment_method + "' />";
                            html += "<label class='control-label col-md-2 small-form' for='transfer_time'>到账日期</label>";
                            html += "<div class='col-md-2 small-form transfer_time'><input type='text' name='transfer_time' value='" + rec.transfer_time + "' class='form-control' onfocus='WdatePicker({dateFmt: \"yyyy-MM-dd\"})'/></div>";
                            html += "<label class='control-label col-md-2 small-form' for='pay_user'>付款人</label>";
                            html += "<div class='col-md-2 small-form'><input type='text' class='form-control' name='pay_user' value='" + rec.pay_username + "' id='pay_user' placeholder='付款人'/></div>";
                            html += "<a href='javascript:void' class='addLine minus'>删除</a>";
                            html += "</div>";
                        }
                    })
                }
                $("#edit_generationOperation_form .form-body").after(html);
                $('#editModal').modal('show');
            });
//            $(".second_tr").hide();
//            $(".submit_btn").hide();
//            $(".cancel_btn").hide();
//            $("#tr_" + generationOperation.id).show();
//            $("#submit_" + generationOperation.id).show();
//            $("#cancel_" + generationOperation.id).show();
//            if (!$("#form_" + generationOperation.id).attr('autoEditSelecter')) {
//                initEditSeleter($("#form_" + generationOperation.id));
//            }
//            $("#tr_" + generationOperation.id + " input,#tr_" + generationOperation.id + " textarea").removeAttr("disabled");
            //日期拆分
            // dateSplit("#form_" + generationOperation.id);
        }
    };
    self_.repairGenerationOperation = function (genrationOperation) {
        $("#repair_gen_id").val(genrationOperation.id);
        $('#repairModal').modal('show');
    };
    self_.showGenerationOperation = function (generationOperation) {
        $("#look_generationOperation_form #add_time").text(generationOperation.add_time);
        $("#look_generationOperation_form #platform_num").text(generationOperation.platform_num);
        $("#look_generationOperation_form #qq").text(generationOperation.qq);
        $("#look_generationOperation_form #customer_type").text(generationOperation.customer_type == 1 ? "优程" : "领远");
        $("#look_generationOperation_form #sales_numbers").text(generationOperation.sales_numbers);
        $("#look_generationOperation_form #rception_money").text(generationOperation.rception_money);
        $("#look_generationOperation_form #platform_sales_s").text(generationOperation.platform_sales);
        $("#look_generationOperation_form #customer_s").text(generationOperation.customer);
        $("#look_generationOperation_form #final_money").text(generationOperation.final_money);
        $("#look_generationOperation_form #rcept_account_s").text(generationOperation.rcept_account);
        var html = "";
        generationOperation.action = 9;
        ycoa.ajaxLoadPost("/api/second_sale/generationOperation.php", JSON.stringify(generationOperation), function (result) {
            if (result.code == 0) {
                $.each(result.list, function (index, rec) {
                    var className = ""
                    if (rec.approve_status == 0) {
                        className = "fa-check";
                    } else if (rec.approve_status == 1) {
                        className = "fa-times";
                    } else {
                        className = "fa-minus-circle";
                    }
                    html += "<div class='form-group small look'><label class='control-label col-md-2 small-form' for='payment_amount'>付款金额</label>";
                    html += "<div class='col-md-2 small-form'><span class='form-control payment_amount'>" + rec.pay_money/(1-rec.pay_rate) + "</span></div>";
                    html += "<label class='control-label col-md-2 small-form' for='rcept_account'>收款账号</label>";
                    html += "<div class='col-md-2 small-form rcept_account' title=" + rec.pay_account_type + " data-toggle='tooltip' data-placement='bottom'>" + rec.pay_account_type + "</div>";
                    html += "<label class='control-label col-md-2 small-form' for='transfer_time'>到账日期</label>";
                    html += "<div class='col-md-2 small-form transfer_time'><span class='form-control payment_amount'>" + rec.transfer_time.split(' ')[0] + "</span></div>";
                    html += "<label class='control-label col-md-2 small-form' for='pay_user'>付款人</label>"
                    html += "<div class='col-md-2 small-form'><span class='form-control'>" + rec.pay_username + "<span></div>";
                    html += "<span href='javascript:void' class='fa " + className + "'></span>";
                    html += "</div>";
                })
            }
            $("#look_generationOperation_form .form-body").after(html);
            $("#lookModal").modal('show');
        });

//        $(".second_tr").hide();
//        $(".submit_btn").hide();
//        $(".cancel_btn").hide();
//        $("#tr_" + generationOperation.id).show();
//        $("#cancel_" + generationOperation.id).show();
//        if (!$("#form_" + generationOperation.id).attr('autoEditSelecter')) {
//            initEditSeleter($("#form_" + generationOperation.id));
//        }
//        $("#tr_" + generationOperation.id + " input,#tr_" + generationOperation.id + " textarea").attr("disabled", "");
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
        ycoa.ajaxLoadPost("/api/second_sale/generationOperation.php", data, function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    };
}();
$(function () {
    ko.applyBindings(GenerationOperationListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1, payType: "(0,2)"});
    $("#dataTable").sort(function (data) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $('#workName').val(), payType: "(0,2)", searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    });

    $('#repairModal,#myModal,#editModal,#lookModal').on('hidden.bs.modal', function (e) {
        $(this).find(".form-group.small").remove();
        $("#delArrayId").val("");
        $("#add_repair_time").val("");
    });

    $("body").on("click", "#platform_sales", function () {
        var self = this;
        var iid = $($($($($($(self).parent()).parent()).parent()).parent()).parent()).attr('id');
        if (typeof (iid) == "undefined") {
            ycoa.UI.employeeSeleter({el: $(this), array: rception_array}, function (data, el) {
                el.val(data.text);
                $("#add_generationOperation_form #platform_sales_id").val(data.id);
            });
        } else {
            ycoa.UI.employeeSeleter({el: $(this), array: rception_array}, function (data, el) {
                el.val(data.text);
                $("#platform_sales_id", $('#' + iid)).val(data.id);
            });
        }
    });

//    $("body").on("click", "#rcept_account", function () {
//        var self = this;
//        var iid = $($($($($($(self).parent()).parent()).parent()).parent()).parent()).attr('id');
//        if (typeof (iid) == "undefined") {
//            ycoa.UI.employeeSeleter({el: $(this), array: rcept_account}, function (data, el) {
//                el.val(data.text);
//                $("#add_generationOperation_form #payment_method").val(data.id);
//            });
//        } else {
//            ycoa.UI.employeeSeleter({el: $(this), array: rcept_account}, function (data, el) {
//                el.val(data.text);
//                $("#payment_method", $('#' + iid)).val(data.id);
//            });
//        }
//    });
    $("body").on("click", '#customer', function () {
        var self = this;
        var iid = $($($($($($(self).parent()).parent()).parent()).parent()).parent()).attr('id');
        if (typeof (iid) == "undefined") {
            if ($('#add_generationOperation_form #customer_type').val() == 1) {
                ycoa.UI.employeeSeleter({el: $(self), array: customer_array}, function (data, el) {
                    el.val(data.text);
                    $("#add_generationOperation_form #customer_id").val(data.id);
                });
            }
            else {
                ycoa.UI.employeeSeleter({el: $(self), array: ly_array}, function (data, el) {
                    el.val(data.text);
                    $("#add_generationOperation_form #customer_id").val(data.id);
                });
            }
        } else {
            if ($('#customer_type', $('#' + iid)).val() == 1) {
                ycoa.UI.employeeSeleter({el: $(self), array: customer_array}, function (data, el) {
                    el.val(data.text);
                    $("#customer_id", $('#' + iid)).val(data.id);
                });
            }
            else {
                ycoa.UI.employeeSeleter({el: $(self), array: ly_array}, function (data, el) {
                    el.val(data.text);
                    $("#customer_id", $('#' + iid)).val(data.id);
                });
            }
        }


    });


    $("#importexcelfinal").click(function () {
        $("#btn_importexcel_primary").attr("data-flag", '1');
    });

    $("#importexcel").click(function () {
        $("#btn_importexcel_primary").attr("data-flag", '0');
    })



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
                url: '../../api/second_sale/generationOperation.php',
                success: function (result) {
                    ycoa.UI.block.hide();
                    if (result.code == 0) {
                        ycoa.UI.toast.success(result.msg);
                        var data = {
                            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
                            workName: $('#workName').val(), payType: "(0,2)", searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                        };
                        reLoadData(data);
                    } else {
                        ycoa.UI.toast.error(result.msg);
                    }
                }
            });
        }
    });
    $("#dataTable").reLoad(function () {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $('#workName').val(), payType: "(0,2)", searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        reLoadData(data);
    });
    $('#btn_importexcel_primary').click(function () {
        var flag = 1;
        if ($(this).attr("data-flag") == 1) {
            flag = 2;
        }
        ycoa.UI.block.show();
        $('#importexcel_form').ajaxSubmit({
            type: 'post',
            url: "../../api/second_sale/generationOperation.php?upload=" + flag,
            success: function (result) {
                ycoa.UI.block.hide();
                if (result.code == 0) {
                    ycoa.UI.toast.success(result.msg);
                    reLoadData({action: 1, payType: "(0,2)"});
                } else {
                    ycoa.UI.toast.error(result.msg);
                }
                $('#myimportexcelModal').modal('hide');
            }
        });
    });
    $("#dataTable").searchUserName(function (name) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: name, payType: "(0,2)", searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    }, '关键字', 'workName');
    /*  $("#dataTable").searchAutoStatus([{id: 0, text: '已指派'}, {id: 1, text: '未指派'}], function (d) {
     var data = {
     is_arg: d.id, action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
     workName: $('workName').val(), payType: $('#search_type').val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
     };
     if (data.searchStartTime || data.searchEndTime) {
     data.searchTime = "";
     }
     reLoadData(data);
     $("#is_arg").val(d.id);
     }, '按是否指派筛选');*/
    $("#dataTable").searchDateTimeSlot(function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $('#workName').val(), payType: "(0,2)", searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    });
    $("#dataTable").searchDateTime(function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $('#workName').val(), payType: "(0,2)", searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    });

    $("body").on("click", "#dataTable .checkbox", function () {
        countMoney(6);
    });

    $("#dataTable thead input[id='checkall']").change(function () {
        if ($(this).prop("checked")) {
            $("#dataTable tbody input[type='checkbox']").prop("checked", "checked");
        } else {
            $("#dataTable tbody input[type='checkbox']").removeAttr("checked");
        }
        countMoney(6);
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
    $("#add_generationOperation_form #qq").keypress(function (e) {
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
                        });
                        $(".auto_tbody").html(html);
                        var y = $(window).height();
                        var x = $(window).width();
                        $(".div_avatar_outer").css({top: ((y - 500) / 2) + 'px', left: ((x - 750) / 2) + 'px'}).show();
                    } else if (data.length == 0) {
                        ycoa.UI.toast.warning("未匹配到相应的数据,请核对后重试~");
                        $("#add_fillarrears_form input").val("");
                        $("#add_fillarrears_form #remark_fillarrears").html("");
                    }
                });
            }
        }
    });
    $("body").on("click", ".auto_tr", function () {
        var json_str = $(this).attr("v");
        json_str = $.parseJSON(json_str);
        $('#lastmoney').val(json_str.final_money);
        $("#add_generationOperation_form input").each(function () {
            var el = $(this);
            if (el.attr("id") == "rception_money" || el.attr("id") == "final_money" || el.attr("id") == "payment_amount") {
                el.val(0);
            } else if (el.attr("id") == "add_time") {
                return true;
            } else {
                el.val(json_str[el.attr("id")]);
            }
        });
        $("#add_generationOperation_form #parent_id").val(json_str.id);
        $(".div_avatar_outer").hide();
    });
    $(".div_avatar_close_btn").click(function () {
        $(".div_avatar_outer").hide();
    });
    $("#btn_submit_primary").click(function () {
        var isNull = $("#add_generationOperation_form #add_time").val() == "" || $("#add_generationOperation_form #payment_amount").val() <= 0 || $("#add_generationOperation_form #rcept_account").val() == "" || $("#add_generationOperation_form #pay_user").val() == "";
        if (isNull) {
            ycoa.UI.toast.warning("日期、时间不能为空,、付款金额大于0、收款账号不能为空、付款人不能为空");
        } else {
            $("#add_generationOperation_form").submit();
        }
    });
    $("#btn_submit_fill_primary").click(function () {
        $("#add_fillarrears_form").submit();
    });
    $("#btn_repair_submit_primary").click(function () {
        var data = $("#add_repair_form").serializeJson();
        var time = $("#add_repair_time").val();
        var id = $("#repair_gen_id").val();
        data.addtime = time;
        data.id = id;
        data.action = 4;
        ycoa.ajaxLoadPost("/api/second_sale/generationOperation.php", JSON.stringify(data), function (result) {
            if (result.code == 0) {
                $('#repairModal').modal('hide');
                ycoa.UI.toast.success(result.msg);
                var data = {
                    action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
                    workName: $('#workName').val(), payType: "(0,2)", searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                };
                reLoadData(data);
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    });
    $("#btn_edit_submit_primary").click(function () {
        var data = $("#edit_generationOperation_form").serializeJson();
        var id = $("#editgenId").val();
        data.id = id;
        data.action = 3;
        ycoa.ajaxLoadPost("/api/second_sale/generationOperation.php", JSON.stringify(data), function (result) {
            if (result.code == 0) {
                $('#editModal').modal('hide');
                ycoa.UI.toast.success(result.msg);
                var data = {
                    action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
                    workName: $('#workName').val(), payType: "(0,2)", searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                };
                reLoadData(data);
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    });
    $("#btn_toexcel_primary").click(function () {
        var start_time = $("#start_time").val();
        var end_time = $("#end_time").val();
        if (start_time || end_time) {
            window.location.href = "/api/second_sale/generationOperation.php?action=10&payType=(0,2)&start_time=" + start_time + "&end_time=" + end_time;
        }
    });
    //$(".date-picker-bind-mouseover").datepicker({autoclose: true});


    $("#add_generationOperation_form #isArrears").autoRadio(array['isArrears']);
    $("#add_generationOperation_form #payment_method").autoEditSelecter(array['payment_method']);
    $("#add_generationOperation_form #sales_numbers,#edit_generationOperation_form #sales_numbers").autoEditSelecter(array['sales_numbers']);
    $("#add_generationOperation_form #rception_money,#edit_generationOperation_form #rception_money").autoEditSelecter(array['class_money']);
    //$("#add_generationOperation_form #customer_type").autoEditSelecter(array['customer_type']);
    $("#add_generationOperation_form #remark").pasteImgEvent();
    $("#add_fillarrears_form #remark_fillarrears").pasteImgEvent();

    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 1}, function (res) {
//        $("#add_generationOperation_form #platform_sales").autoEditSelecter(res, function (d) {
//            $("#add_generationOperation_form #platform_sales_id").val(d.id);
//        });
        rception_array = res;
    });
    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 8}, function (res) {
        $("#add_generationOperation_form #rcept_account,#add_repair_form #rcept_account,#edit_generationOperation_form #rcept_account").autoEditSelecter(res, function (d) {
            $("#add_generationOperation_form #payment_method,#add_repair_form #payment_method,#edit_generationOperation_form #payment_method").val(d.id);
            $("#add_generationOperation_form #pay_rate,#add_repair_form #pay_rate,#edit_generationOperation_form #pay_rate").val(d.cv);
        });
        rcept_account = res;
    });

    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 2}, function (res) {
//        $("#add_generationOperation_form #customer").autoEditSelecter(res, function (d) {
//            $("#add_generationOperation_form #customer_id").val(d.id);
//        });
        customer_array = res;
    });
    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 7}, function (res) {
        ly_array = res;
    });
    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 5}, function (res) {
        $("#add_fillarrears_form #headmaster,#edit_generationOperation_form #headmaster").autoEditSelecter(res, function (d) {
            $("#add_fillarrears_form #headmaster_id,#edit_generationOperation_form #headmaster_id").val(d.id);
        });
        headmaster_array = res;
    });

    if (jQuery.ui) {
        $('.div_avatar_outer').draggable({handle: ".div_avatar_close_title"});
    }
    $(".addLine").click(function () {
        var rcept_account = $("#add_generationOperation_form #rcept_account").val() || $("#add_repair_form #rcept_account").val() || $("#edit_generationOperation_form #rcept_account").val();
        var pay_rate = $("#add_generationOperation_form #pay_rate").val() || $("#add_repair_form #pay_rate").val() || $("#edit_generationOperation_form #pay_rate").val();
        var payment_method = $("#add_generationOperation_form #payment_method").val() || $("#add_repair_form #payment_method").val() || $("#edit_generationOperation_form #payment_method").val();
        if (rcept_account == null || rcept_account == "") {
            ycoa.UI.toast.warning("请先选择收款账号，然后再添加");
        } else {
            var date = new Date().format("yyyy-MM-dd");
            addInput($(this), date, rcept_account, pay_rate, payment_method);
        }
    });
    $("body").on("click", ".addLine.minus", function () {
        var ary_id = $("#delArrayId").val();
        var form=$(this).parents("form");
        if ($(this).parents("form").prop("id") == "edit_generationOperation_form") {
            var id = $(this).parents(".form-group.small").find("#recid").val();
            $("#delArrayId").val(ary_id == "" ? id : ary_id + "," + id);
        }
        $(this).parent().remove();
        var payment_amount = 0;
        $(form).find(".payment_amount").each(function () {
            payment_amount += Number($(this).val()) || Number($(this).text());
        });
        // var rception_money=$("#add_generationOperation_form #rception_money,#edit_generationOperation_form #rception_money").val();
        var rception_money = $(form).find("#rception_money").val();
        if ($("#ispayment_amount").val() == 0) {
           $(form).find("#final_money").val(Number(rception_money) - Number(payment_amount));
        }
    });
    $('body').on("mouseover", "[data-toggle='tooltip']", function () {
        $(this).tooltip('show');
    });
    $("body").on("blur", "#add_generationOperation_form .payment_amount,#edit_generationOperation_form .payment_amount", function () {
        var self=this;
        var payment_amount = 0;
        $(self).parents("form").find(".payment_amount").each(function () {
            payment_amount +=Number($(this).val())||Number($(this).text());
        });
        // var rception_money=$("#add_generationOperation_form #rception_money,#edit_generationOperation_form #rception_money").val();
        var rception_money = $(self).parents("form").find("#rception_money").val();
        if( $("#ispayment_amount").val()==0){
             $(self).parents("form").find("#final_money").val(Number(rception_money)-Number(payment_amount));
        }
    });
});
function reLoadData(data) {
    GenerationOperationListViewModel.listGenerationOperation(data);
}

//日期拆分函数
function dateSplit(object) {
    var date = $(object + " #add_time").val();
    dateArray = date.split(" ");
    $(object + " .input-time").val(dateArray[1]);
    $(object + " .input-date").val(dateArray[0]);
}

function updateCL(generationOperation) {
    generationOperation.action = 2;
    ycoa.ajaxLoadPost("/api/second_sale/generationOperation.php", JSON.stringify(generationOperation), function (result) {
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
    isArrears: [{id: '1', text: '是'}, {id: '0', text: '否'}],
    customer_type: [{id: '1', text: '优程'}, {id: '2', text: '领远'}],
    class_money: [{id: '1', text: '3600'}, {id: '2', text: '5600'}, {id: '3', text: '9600'}, {id: '4', text: '15600'}],
    sales_numbers: [{id: '1', text: '新手班'}, {id: '2', text: '入门班'}, {id: '3', text: '专业班'}, {id: '4', text: '精通班'}, {id: '5', text: '全职班'}, {id: '6', text: '兼职班'}, {id: '7', text: '定金'}, {id: '7', text: '其他'}]
};
function initEditSeleter(el) {
    $("#isArrears", el).autoRadio(array['isArrears']);
    $("#rception_money", el).autoEditSelecter(array['class_money']);
    $("#sales_numbers", el).autoEditSelecter(array['sales_numbers']);
    //$("#customer_type", el).autoEditSelecter(array['customer_type']);
    $("#rcept_account", el).autoEditSelecter(rcept_account, function (d) {
        $("#payment_method", el).val(d.id);
        $("#pay_rate", el).val(d.cv);
    });
//    if ($('#customer_type_test', el).val() == 1) {
//        $("#customer", el).autoEditSelecter(rception_array, function (d) {
//            $("#customer_id", el).val(d.id);
//        });
//    } else {
//        $("#customer", el).autoEditSelecter(ly_array, function (d) {
//            $("#customer_id", el).val(d.id);
//        });
//    }

    $("#headmaster", el).autoEditSelecter(headmaster_array, function (d) {
        $("#headmaster_id", el).val(d.id);
    });

    $("#remark_edit", el).pasteImgEvent();
    el.attr('autoEditSelecter', 'autoEditSelecter');
}

function addInput(el, date, rcept_account, pay_rate, payment_method) {
    var html = "<div class='form-group small'><label class='control-label col-md-2 small-form' for='payment_amount'>付款金额</label><input type='hidden' id='recid' name='recid' value=0>";
    html += "<div class='col-md-2 small-form'><input type='text' class='form-control payment_amount' name='payment_amount' id='payment_amount' placeholder='付款金额'/></div><input type='hidden' name='pay_rate_each' value=" + pay_rate + ">";
    html += "<label class='control-label col-md-2 small-form' for='rcept_account'>收款账号</label>";
    html += "<div class='col-md-2 small-form rcept_account' title=" + rcept_account + " data-toggle='tooltip' data-placement='bottom'>" + rcept_account + "</div><input type='hidden' name='rcept_account_each' value='" + rcept_account + "' /><input type='hidden' name='payment_method_each' value='" + payment_method + "' />"
    html += "<label class='control-label col-md-2 small-form' for='transfer_time'>到账日期</label>"
    html += "<div class='col-md-2 small-form transfer_time'><input type='text' name='transfer_time' class='form-control' onfocus='WdatePicker({dateFmt: \"yyyy-MM-dd\"})'/></div>";
    html += "<label class='control-label col-md-2 small-form' for='pay_user'>付款人</label>"
    html += "<div class='col-md-2 small-form'><input type='text' class='form-control' name='pay_user' id='pay_user' placeholder='付款人'/></div>";
    html += "<a href='javascript:void' class='addLine minus'>删除</a>";
    html += "</div>";
    el.parent().after(html);
}