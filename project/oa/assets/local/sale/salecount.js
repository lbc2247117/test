var customer_list;
var current_Date;
var SalecountListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.salecountList = ko.observableArray([]);
    self_.listSalecount = function (data) {
        ycoa.ajaxLoadGet("/api/sale/salecount.php", data, function (results) {
            self_.salecountList.removeAll();
            var currentDate = results.currentDate;
            current_Date = results.currentDate;
            $.each(results.list, function (index, salecount) {
                var addTime = salecount.addtime.split(" ")[0];
                salecount.dele = ycoa.SESSION.PERMIT.hasPagePermitButton("2010302");
                salecount.edit = ycoa.SESSION.PERMIT.hasPagePermitButton("2010303") && results.is_manager ? true : ((ycoa.user.userid() == salecount.presales_id) && (currentDate == addTime));
                salecount.show = ycoa.SESSION.PERMIT.hasPagePermitButton("2010304");
                salecount.reSetSalecount = ycoa.SESSION.PERMIT.hasPagePermitButton("2010306") && (salecount.status !== 2);
                salecount.isTimely_txt = salecount.isTimely === 1 ? '√' : '';
                salecount.isQQTeach_txt = salecount.isQQTeach === 1 ? '√' : '';
                salecount.isTmallTeach_qj_txt = salecount.isTmallTeach_qj === 1 ? '√' : '';
                salecount.isTmallTeach_zy_txt = salecount.isTmallTeach_zy === 1 ? '√' : '';
//              salecount.color_ = (salecount.status === 0 || salecount.status === 2) ? "color:#F00" : ((salecount.status === 3) ? "color:rgb(64, 142, 71)" : ((salecount.status === 4) ? "color:rgb(207, 182, 11)" : ""));
//              salecount.color_ = (salecount.status === 0 || salecount.status === 2) ? "color:#F00" : "";
                salecount.color_ = (salecount.status === 0 || salecount.status === 2) ? ((salecount.conflictWith === 0) ? "" : "color:#F00") : "";
                salecount.refund = ycoa.SESSION.PERMIT.hasPagePermitButton("2010307") && salecount.status !== 2;
                salecount.setcustomer = ycoa.SESSION.PERMIT.hasPagePermitButton("2010308") && (salecount.status !== 2) && (salecount.customer_id !== 0);
                salecount.cashback = ycoa.SESSION.PERMIT.hasPagePermitButton("2010309") && (salecount.status !== 2) && (salecount.customer_id !== 0);
                salecount.goPass = ycoa.SESSION.PERMIT.hasPagePermitButton("2010310") && (salecount.status === 2);
                salecount.hasPraise = ycoa.SESSION.PERMIT.hasPagePermitButton("2010311") && (salecount.presales_id != ycoa.user.userid()) && (salecount.customer_id != ycoa.user.userid());
                salecount.hasAddFine = ycoa.SESSION.PERMIT.hasPagePermitButton("2010312") && (salecount.presales_id == ycoa.user.userid()) && salecount.status == 1;
                salecount.passAddFine = ycoa.SESSION.PERMIT.hasPagePermitButton("2010313") && results.is_manager && salecount.status == 4;
                salecount.ather = function () {
                    var array_ = new Array();
                    if (salecount.isTimely === 1) {
                        array_.push('isTimely');
                    }
                    if (salecount.isQQTeach === 1) {
                        array_.push('isQQTeach');
                    }
                    if (salecount.isTmallTeach_qj === 1) {
                        array_.push('isTmallTeach_qj');
                    }
                    if (salecount.isTmallTeach_zy === 1) {
                        array_.push('isTmallTeach_zy');
                    }
                    if (salecount.scheduledPackage === 1) {
                        array_.push('scheduledPackage');
                    }
                    return array_.toString();
                }();
                self_.salecountList.push(salecount);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                var data = {
                    action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: pageSize, channel: $("#channel").val(), searchName: $("#searchUserName").val(),
                    searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(),
                    searchMouth: $("#searchMouth").val(), searchSetmeal: $('#searchSetmeal').val(), status: $("#data_status").val()
                };
                if (data.searchStartTime || data.searchEndTime) {
                    data.searchMouth = "";
                }
                reLoadData(data);
            }, function (pageNo) {
                var data = {
                    action: 1, pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(), channel: $("#channel").val(),
                    searchName: $("#searchUserName").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val(),
                    sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), searchMouth: $("#searchMouth").val(),
                    searchSetmeal: $('#searchSetmeal').val(), status: $("#data_status").val()
                };
                if (data.searchStartTime || data.searchEndTime) {
                    data.searchMouth = "";
                }
                reLoadData(data);
            });
        });
    };

    self_.delSalecount = function (salecount) {
        ycoa.UI.messageBox.confirm('确认删除？', function (del) {
            if (del) {
                salecount.action = 3;
                ycoa.ajaxLoadPost("/api/sale/salecount.php", JSON.stringify(salecount), function (result) {
                    if (result.code == 0) {
                        if (result.conflict_id !== 0) {
                            $.post(ycoa.getNoCacheUrl("/api/sale/salecount.php"), {sale_id: result.conflict_id, action: 4});
                        }
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
    self_.setCustomer = function (salecount) {
        var html = "<div class='cu_div' id='doback_" + salecount.id + "'>";
        html += "<select name='nick_name' id='nick_name' style='width:300px;height:41px; ;float:left' class='form-control'>";
        $.each(customer_list, function (index, d) {
            html += "<option value='" + d.id + "'>" + (d.text) + "</option>";
        });
        html += "</select>";
        html += "<span class='input-group-addon' id='setCustomerOk' isTimely='" + (salecount.isTimely) + "' isQQTeach='" + (salecount.isQQTeach) + "' isTmallTeach_qj='" + (salecount.isTmallTeach_qj) + "' isTmallTeach_zy='" + (salecount.isTmallTeach_zy) + "' scheduledPackage='" + (salecount.scheduledPackage) + "' val='" + salecount.id + "' status='" + salecount.status + "' customer_id='" + salecount.customer_id + "'><i class='glyphicon glyphicon-ok' title='提交'></i></span>";
        html += "<span class='input-group-addon' id='setCancel' val='" + salecount.id + "' status='" + salecount.status + "'><i class='glyphicon glyphicon-remove' title='取消'></i></span>";
        html += "</div>";
        $("#customer_td_" + salecount.id).append(html);
        $(".doback_open").animate({opacity: 'toggle', width: '0px'}, 500, function () {
            $(this).hide();
            $(this).removeClass("doback_open");
        });
        $("#doback_" + salecount.id).show();
        $("#doback_" + salecount.id).animate({width: '382px', opacity: 'show'}, 500, function () {
            $(this).addClass("doback_open");
        });
    };

    self_.setSalecount = function (salecount) {
        ycoa.ajaxLoadPost("/api/sale/salecount.php", {sale_id: salecount.id, action: 4, status: salecount.status, isTmallTeach_qj: salecount.isTmallTeach_qj, isTmallTeach_zy: salecount.isTmallTeach_zy}, function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({action: 1});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    };
    self_.editSalecount = function (salecount) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + salecount.id).show();
        $("#submit_" + salecount.id).show();
        $("#cancel_" + salecount.id).show();
        if (!$("#form_" + salecount.id).attr('autoEditSelecter')) {
            initEditSeleter($("#form_" + salecount.id));
        }
        $("#customer_id").val(salecount.customer_id2);
        $("#customer_id2").val(salecount.customer_id);
        $("#tr_" + salecount.id + " input,#tr_" + salecount.id + " textarea").removeAttr("disabled");
    };
    self_.cancelTr = function (salecount) {
        $("#tr_" + salecount.id).hide();
        $("#submit_" + salecount.id).hide();
        $("#cancel_" + salecount.id).hide();
    };
    self_.showSalecount = function (salecount) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + salecount.id).show();
        $("#cancel_" + salecount.id).show();
        if (!$("#form_" + salecount.id).attr('autoEditSelecter')) {
            initEditSeleter($("#form_" + salecount.id));
        }
        $("#tr_" + salecount.id + " input,#tr_" + salecount.id + " textarea").attr("disabled", "");
    };
    self_.refund = function (salecount) {
        $('#add_refund_form #presale').val(salecount.presales);
        $('#add_refund_form #customer').val(salecount.customer2 ? salecount.customer2 : salecount.customer);
        $('#add_refund_form #s_id').val(salecount.id);
        $('#add_refund_form #name').val(salecount.name);
        $('#add_refund_form #ww').val(salecount.ww);
        $('#add_refund_form #totalmoney').val(salecount.money);
    };
    self_.cashback = function (salecount) {
        $('#add_cashback_form #presale').val(salecount.presales);
        $('#add_cashback_form #customer').val(salecount.customer2 ? salecount.customer2 : salecount.customer);
        $('#add_cashback_form #s_id').val(salecount.id);
        $('#add_cashback_form #name').val(salecount.name);
        $('#add_cashback_form #buydate').val(salecount.addtime);
        $('#add_cashback_form #ww').val(salecount.ww);
        $('#add_cashback_form #channel').val(salecount.channel);
        $('#add_cashback_form #money').val(salecount.money);
    };
    self_.goPass = function (salecount) {
        updateCL({status: 0, id: salecount.id, goPass: true});
    };
    self_.doPraise = function (salecount) {
        ycoa.ajaxLoadPost("/api/sale/salecount.php", {sale_id: salecount.id, action: 5}, function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                var data = {
                    action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
                    channel: $("#channel").val(), searchName: $("#searchUserName").val(), searchStartTime: $('#searchStartTime').val(),
                    searchEndTime: $('#searchEndTime').val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(),
                    searchMouth: $("#searchMouth").val(), searchSetmeal: $('#searchSetmeal').val(), status: $("#data_status").val()
                };
                reLoadData(data);
            } else {
                ycoa.UI.toast.error(result.msg);
            }
        });
    };
    self_.doAddFine = function (salecount) {
        salecount.status = 4;
        updateCL(salecount);
    };
    self_.doPassAddFine = function (salecount) {
        salecount.status = 3;
        updateCL(salecount);
    };

}();
$(function () {
    get_customer_list();
    createTodayTotals();
    ko.applyBindings(SalecountListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1});
    $("#dataTable").sort(function (data) {
        var data = {
            action: 1, sort: data.sort, sortname: data.sortname, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val(), searchName: $("#searchUserName").val(),
            searchMouth: $("#searchMouth").val(), channel: $("#channel").val(), searchSetmeal: $('#searchSetmeal').val(), status: $("#data_status").val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchMouth = "";
        }
        reLoadData(data);
    });
    $("#dataTable").reLoad(function () {
        reLoadData({action: 1});
        $('#searchUserName').val('');
        $("#searchMouth").val('');
        $('#searchSetmeal').val('');
        $('#searchStartTime').val('');
        $('#searchEndTime').val('');
        $('#channel').val('');
        $("#data_status").val('');
        $(".portlet-title .filter-option").html("不限");
        createTodayTotals();
    });
    $("#dataTable").searchUserName(function (name) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), searchStartTime: $('#searchStartTime').val(), channel: $("#channel").val(),
            searchEndTime: $('#searchEndTime').val(), searchName: name, searchMouth: $("#searchMouth").val(), searchSetmeal: $('#searchSetmeal').val(), status: $("#data_status").val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchMouth = "";
        }
        reLoadData(data);
    });
    $("#dataTable").searchAutoStatus(array['mouth'], function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), channel: $("#channel").val(), sortname: ycoa.SESSION.SORT.getSortName(),
            searchName: $("#searchUserName").val(), searchMouth: d.id, searchSetmeal: $('#searchSetmeal').val(), status: $("#data_status").val()
        };
        reLoadData(data);
        $("#searchMouth").val(d.id);
    }, '月份');
    $("#dataTable").searchAutoStatus([{id: '', text: '不限'}, {id: '普通', text: '普通'}, {id: '创业', text: '创业'}, {id: '财富', text: '财富'}], function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), searchStartTime: $('#searchStartTime').val(), channel: $("#channel").val(),
            searchEndTime: $('#searchEndTime').val(), searchName: $("#searchUserName").val(), searchMouth: $("#searchMouth").val(), searchSetmeal: d.id, status: $("#data_status").val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchMouth = "";
        }
        reLoadData(data);
        $('#searchSetmeal').val(d.id);
    }, "按照版本搜索");
    $("#dataTable").searchAutoStatus(array['channel'], function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), searchStartTime: $('#searchStartTime').val(), channel: d.id,
            searchEndTime: $('#searchEndTime').val(), searchName: $("#searchUserName").val(), searchMouth: $("#searchMouth").val(), searchSetmeal: $("#searchSetmeal").val(), status: $("#data_status").val()
        };
        reLoadData(data);
        $('#channel').val(d.id);
    }, '接入渠道');
    $("#dataTable").searchAutoStatus([{id: 0, text: '全部'}, {id: 1, text: '正常'}, {id: 2, text: '冲突'}, {id: 3, text: '未分配售后'}], function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), searchStartTime: $('#searchStartTime').val(), channel: $("#channel").val(),
            searchEndTime: $('#searchEndTime').val(), searchName: $("#searchUserName").val(), searchMouth: $("#searchMouth").val(), searchSetmeal: $("#searchSetmeal").val(), status: d.id
        };
        reLoadData(data);
        $("#data_status").val(d.id);
    }, "状态");
    $("#dataTable").searchDateTimeSlot(function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            searchStartTime: d.start, searchEndTime: d.end, searchName: $("#searchUserName").val(), channel: $("#channel").val(), searchSetmeal: $('#searchSetmeal').val(), status: $("#data_status").val()
        };
        reLoadData(data);
    });
    $(".date-picker-bind-mouseover").datepicker({autoclose: true});
    $("#dataTable thead input[id='checkall']").change(function () {
        if ($(this).prop("checked")) {
            $("#dataTable tbody input[type='checkbox']").prop("checked", "checked");
        } else {
            $("#dataTable tbody input[type='checkbox']").removeAttr("checked");
        }
    });
    $("#btn_submit_primary").click(function () {
        $("#add_salecount_form").submit();
    });
    $("#btn_refund_primary").click(function () {
        $("#add_refund_form").submit();
    });
    $("#add_refund_form #money").blur(function () {
        $("#retrieve").val($("#totalmoney").val() - $(this).val());
    })
    $("#btn_cashback_primary").click(function () {
        $("#add_cashback_form").submit();
    });
    $("#open_dialog_btn").click(function () {
        $("#add_salecount_form input,#add_salecount_form textarea").each(function () {
            if (!$(this).hasClass("not-clear")) {
                $(this).val("");
            }
        });
        $("#add_salecount_form #attachment").html("");
        if (ycoa.user.dept1_id() !== 4) {
            $("#add_salecount_form #presales").val(ycoa.user.username());
            $("#add_salecount_form #presales_id").val(ycoa.user.userid());
//            $("#add_salecount_form #addtime").val(new Date().format("yyyy-MM-dd hh:mm:ss"));
        }
        $("#add_salecount_form input[type='checkbox']").removeAttr("checked");
        $(".has-error,.has-success").each(function () {
            $(this).removeClass("has-error").removeClass("has-success");
        });
        $(".fa-warning,.fa-check").each(function () {
            $(this).removeClass("fa-warning").removeClass("fa-check");
        });
    });
    $("#start_time,#end_time").val(new Date().format("yyyy-MM-dd"));
    $("#btn_toexcel_primary").live("click", function () {
        var start_time = $("#toexcel_form #start_time").val();
        var end_time = $("#toexcel_form #end_time").val();
        if (start_time || end_time) {
            location.href = '/api/sale/salecount.php?start_time=' + start_time + '&end_time=' + end_time + '&action=11';
        }
    });
    $(".dept_submit_btn").live("click", function () {
        var formid = "form_" + $(this).attr("val");
        var data = $("#" + formid).serializeJson();
        data.isTimely = 0;
        data.isQQTeach = 0;
        data.isTmallTeach_qj = 0;
        data.isTmallTeach_zy = 0;
        data.scheduledPackage = 0;
        var ather = (data.ather).split(",");
        if (ather.indexOf("isTimely") !== -1) {
            data.isTimely = 1;
        }
        if (ather.indexOf("isQQTeach") !== -1) {
            data.isQQTeach = 1;
        }
        if (ather.indexOf("isTmallTeach_qj") !== -1) {
            data.isTmallTeach_qj = 1;
        }
        if (ather.indexOf("isTmallTeach_zy") !== -1) {
            data.isTmallTeach_zy = 1;
        }
        if (ather.indexOf("scheduledPackage") !== -1) {
            data.scheduledPackage = 1;
        }
        if (data.addtime) {
            var d = (data.addtime).split(" ")[0];
            if (d > current_Date) {
                ycoa.UI.toast.warning("添加时间不能大于当前时间~");
                return;
            }
        }
        data.action = 2;
        data.attachment = $("#" + formid + " #attachment_edit").html();
        var work_kv = new Array();
        $("#" + formid + " .second_table input,#" + formid + " .second_table textarea").each(function () {
            if ($(this).attr("placeholder")) {
                work_kv.push('"' + ($(this).attr('name')) + '":"' + $(this).attr('placeholder') + '"');
            }
        });
        work_kv.push('"attachment":"附件"');
        data.key_names = $.parseJSON("{" + work_kv.toString() + "}");
        data = JSON.stringify(data);
        ycoa.ajaxLoadPost("/api/sale/salecount.php", data, function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({action: 1});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    });
    $("#add_salecount_form #money").autoEditSelecter(array["money"], function (data) {
        var str = data.text;
        switch (str) {
            case "300":
                $("#add_salecount_form #setmeal").val("普通");
                break;
            case "500":
                $("#add_salecount_form #setmeal").val("创业");
                break;
            case "800":
                $("#add_salecount_form #setmeal").val("财富");
                break;
        }
    });
    $("#add_salecount_form #ww").autoEditSelecter(array["ww"]);
    $("#add_salecount_form #setmeal").autoEditSelecter(array["setmeal"]);
    $("#add_salecount_form #payment").autoEditSelecter(array["payment"]);
    $("#add_salecount_form #channel").autoEditSelecter(array["channel"]);
    $("#add_salecount_form #province").autoEditSelecter(array["province"]);
    $("#add_salecount_form #ather").autoRadio(array["ather"]);

    $("#add_refund_form #refund_type").autoRadio(array["refund_type"]);
    $("#add_refund_form #refund_rate").autoRadio(array["refund_rate"]);
    $("#add_refund_form #ind_refund_rate").autoRadio(array["ind_refund_rate"]);
    //$("#add_refund_form #controlman").autoRadio(array["controlman"]);
    $("#add_refund_form #duty").autoRadio(array["duty"]);
    $("#add_refund_form #status").autoRadio(array["status"]);
    $("#add_refund_form #is_logoff_dbb").autoRadio(array["bool"]);
    $("#add_refund_form #reason").autoRadio(array["reason"]);

    $("#add_salecount_form #arrearsbox").click(function () {
        if ($("#add_salecount_form #arrearsbox").prop('checked')) {
            $("#add_salecount_form #arrears").show();
        } else {
            $("#add_salecount_form #arrears").hide();
        }
    });
    $("#add_salecount_form #att").click(function () {
        if ($("#add_salecount_form #att").prop('checked')) {
            $("#add_salecount_form #editor").stop().animate({height: 'toggle'});
        } else {
            $("#add_salecount_form #editor").stop().animate({height: 'toggle'});
        }
    });
    $("#add_salecount_form #attachment").pasteImgEvent();
    $("#btn_close_primary").click(function () {
        $("#add_salecount_form #editor").hide();
    });
    $("#quikWrite").blur(function () {
        var self = this;
        var context = $(self).val();
        if (context.trim()) {
            context = context.trim();
            context = context.split(",");
            if (context.length == 3) {
                var wwName = context[0].split(" ");
                if (wwName.length == 2) {
                    $("#add_salecount_form #ww").val(wwName[0].trim());
                    $("#add_salecount_form #name").val(wwName[1].trim());
                    $("#add_salecount_form #mobile").val(context[1].trim());
                    $("#add_salecount_form #province").val(context[2].trim());
                } else {
                    ycoa.UI.toast.warning("参数格式不正确，检查后重新再试~");
                }
            } else {
                ycoa.UI.toast.warning("参数格式不正确，检查后重新再试~");
            }
        }
    });
    $("#setCustomerOk").live("click", function () {
        var id = $(this).attr("val");
        var status = $(this).attr("status");
        var customer_id = $(this).attr("customer_id");
        var v = $("#doback_" + id).find("select").val();
        var t = $("#doback_" + id).find("select").find('option:selected').text();
        t = t.replace(")", "").split("(");
        var nick_name = t[1];
        var customer = t[0];
        if (customer_id == 0) {
            ycoa.UI.toast.warning("操作提示", "该条数据未分配售后,暂无法更换老师~");
        } else {
            updateCL({id: id, status: status, customer_id: customer_id, customer_id2: v, nick_name2: nick_name, customer2: customer, isTimely: $(this).attr('isTimely'), isQQTeach: $(this).attr('isQQTeach'), isTmallTeach_qj: $(this).attr('isTmallTeach_qj'), isTmallTeach_zy: $(this).attr('isTmallTeach_zy'), scheduledPackage: $(this).attr('scheduledPackage')});
            $("#doback_" + id).animate({opacity: 'toggle', width: '0px'}, 500, function () {
                $(this).hide();
                $(this).removeClass("doback_open");
            });
        }
    });
    $("#setCancel").live("click", function () {
        $(this).parent(".cu_div").animate({opacity: 'toggle', width: '0px'}, 500, function () {
            $(this).hide();
            $(this).removeClass("doback_open");
        });
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
                $.get(ycoa.getNoCacheUrl("/api/sys/dataChangeLog.php"), {action: 2, obj_id: w_id}, function (result) {
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

    $(document).bind("click", function (e) {
        var target = $(e.target);
        if (target.closest(".doback_open").length == 0) {
            $(".doback_open").animate({opacity: 'toggle', width: '0px'}, 500, function () {
                $(this).hide();
                $(this).removeClass("doback_open");
            });
        }
    });

    $("#show_ranking").click(function () {
        ycoa.ajaxLoadGet("/api/sale/salecount.php", {action: 2, time_unit: 1}, function (result) {
            var html = "<ul>";
            $.each(result, function (index, d) {
                var clas = "num";
                if (index == 0) {
                    clas = "num_1";
                } else if (index == 1) {
                    clas = "num_2";
                } else if (index == 2) {
                    clas = "num_3";
                }
                html += "<li><span class='" + clas + "'>" + (index + 1) + "</span><span class='presales'>" + (d.presales) + "</span><span class='count'>" + (d.sale_count) + "</span><span class='money'>" + (Math.ceil(d.money)) + "元</span></li>";
            });
            html += "</ul>";
            $(".auto_tab_main .auto_tab_context div[var='1']").html(html);
            var width = $(window).width();
            var height = $(window).height();
            $(".auto_tab_main").animate({left: ((width - 450) / 2) + "px", top: ((height - 540) / 2) + "px"}, 500);
        });
    });

    $(".auto_tab_main .auto_tab_title li").click(function () {
        var self = $(this);
        var data = new Date().getTime();
        if (!self.hasClass("select")) {
            if ((data - parseInt($(".auto_tab_main").attr("t"))) > 500) {
                var v = self.attr("var");
                ycoa.ajaxLoadGet("/api/sale/salecount.php", {action: 2, time_unit: v}, function (result) {
                    var html = "<ul>";
                    $.each(result, function (index, d) {
                        var clas = "num";
                        if (index == 0) {
                            clas = "num_1";
                        } else if (index == 1) {
                            clas = "num_2";
                        } else if (index == 2) {
                            clas = "num_3";
                        }
                        html += "<li><span class='" + clas + "'>" + (index + 1) + "</span><span class='presales'>" + (d.presales) + "</span><span class='count'>" + (d.sale_count) + "</span><span class='money'>" + (Math.ceil(d.money)) + "元</span></li>";
                    });
                    html += "</ul>";
                    $(".auto_tab_main .auto_tab_context div[var='" + v + "']").html(html);
                });
                $(".auto_tab_main").attr("t", data);
                $(".auto_tab_main li").removeClass("select");
                $(this).addClass("select");
                $(".auto_tab_context .open").animate({height: '0px'}, 300, function () {
                    $(this).removeClass("open");
                    $(this).hide();
                });
                $(".auto_tab_context div[var='" + v + "']").show();
                $(".auto_tab_context div[var='" + v + "']").animate({height: '458px'}, 300, function () {
                    $(this).addClass("open");
                });
            }
        }
    });
    $(".auto_tab_close .close_btn").click(function () {
        $(".auto_tab_main").animate({left: (($(window).width() + 500) * 1) + "px", top: "-600px"}, 500);
        $(".auto_tab_main").animate({left: '-500px', top: '-600px'});
    });
    if (jQuery.ui) {
        $('.auto_tab_main').draggable({handle: ".title"});
    }

    if (ycoa.user.dept1_id() === 4) {
        $("#add_salecount_form #presales").click(function () {
            ycoa.UI.empSeleter({el: $(this), type: 'only', groupId: [7]}, function (node, el) {
                el.val(node.name);
                $("#add_salecount_form #presales_id").val(node.id);
            });
        });
    }
    if (ycoa.user.dept1_id() === 4) {
        $("#add_salecount_form .date-time-picker-bind-mouseover").datetimepicker({autoclose: true}, function (d) {
            if (d && current_Date) {
                d = d.split(" ")[0];
                if (d > current_Date) {
                    ycoa.UI.toast.warning("添加时间不能大于当前时间~");
                    $("#add_salecount_form #addtime").val("");
                }
            }
        });
    }
});

function reLoadData(data) {
    SalecountListViewModel.listSalecount(data);
}

function updateCL(salecount) {
    salecount.action = 2;
    ycoa.ajaxLoadPost("/api/sale/salecount.php", JSON.stringify(salecount), function (result) {
        if (result.code == 0) {
            ycoa.UI.toast.success("操作成功~");
            reLoadData({});
        } else {
            ycoa.UI.toast.error("操作失败~");
        }
        ycoa.UI.block.hide();
    });
}
function createTodayTotals() {
    $.get(ycoa.getNoCacheUrl("/api/sale/salecount.php"), {action: 4}, function (results) {
        var TodayTotals = results;

        var TodayBaiduPCTotals = TodayTotals.TodayBaiduPCTotals;
        var TodayBaiduMTotals = TodayTotals.TodayBaiduMTotals;
        var Today360Totals = TodayTotals.Today360Totals;
        var TodaySogouTotals = TodayTotals.TodaySogouTotals;

        var TodayBaiduPCTimelyTotals = TodayTotals.TodayBaiduPCTimelyTotals;
        var TodayBaiduMTimelyTotals = TodayTotals.TodayBaiduMTimelyTotals;
        var Today360TimelyTotals = TodayTotals.Today360TimelyTotals;
        var TodaySogouTimelyTotals = TodayTotals.TodaySogouTimelyTotals;

        var TodayTotalsHtml = "<span style='color: rgb(194, 22, 22);'>百度PC:YD(" + TodayBaiduPCTotals + ":" + TodayBaiduMTotals + ")&nbsp;及时PC:YD(" + TodayBaiduPCTimelyTotals + ":" + TodayBaiduMTimelyTotals + ")&nbsp;合计&nbsp;" + (TodayBaiduPCTotals + TodayBaiduMTotals) + "单&nbsp;及时&nbsp;" + (TodayBaiduPCTimelyTotals + TodayBaiduMTimelyTotals) + "单&nbsp;</span>";
        TodayTotalsHtml += "<span style='color:rgb(158, 158, 21);'>360:" + Today360Totals + "单(及时:" + Today360TimelyTotals + "单)&nbsp;</span>";
        TodayTotalsHtml += "<span style='color:rgb(26, 26, 187);'>搜狗:" + TodaySogouTotals + "单(及时:" + TodaySogouTimelyTotals + "单)&nbsp;</span>";
        TodayTotalsHtml += "<span style='color:rgb(8, 105, 8);'>共:" + (TodayBaiduPCTotals + TodayBaiduMTotals + Today360Totals + TodaySogouTotals) + "单(及时:" + (TodayBaiduPCTimelyTotals + TodayBaiduMTimelyTotals + Today360TimelyTotals + TodaySogouTimelyTotals) + "单)</span>";
        if (!isNaN(TodayBaiduPCTotals) && !isNaN(TodayBaiduMTotals) && !isNaN(Today360Totals) && !isNaN(TodaySogouTotals)) {
            $('.TodayTotals').html(TodayTotalsHtml);
        }
        $("#sum_todayTotals").remove();
    });
}
function get_customer_list() {
    $.get(ycoa.getNoCacheUrl("/api/sale/salecount.php"), {action: 10}, function (results) {
        if (results.code === 0) {
            customer_list = results.customer_list;
        }
    });
}
function initEditSeleter(el) {
    $("#money", el).autoEditSelecter(array["money"], function (data) {
        var str = data.text;
        var self = data.el;
        switch (str) {
            case "300":
                self.parent('div').parent('td').parent('tr').find('#setmeal').val("普通");
                break;
            case "500":
                self.parent('div').parent('td').parent('tr').find('#setmeal').val("创业");
                break;
            case "800":
                self.parent('div').parent('td').parent('tr').find('#setmeal').val("财富");
                break;
        }
    });
    $("#setmeal", el).autoEditSelecter(array["setmeal"]);
    $("#payment", el).autoEditSelecter(array["payment"]);
    $("#channel", el).autoEditSelecter(array["channel"]);
    $("#province", el).autoEditSelecter(array["province"]);
    $("#nick_name", el).autoEditSelecter(customer_list, function (data) {
        data.el.parent("div").parent("td").find("#customer_id").val(data.id);
    });
    $("#ather", el).autoRadio(array["ather"]);
    $("#attachment_edit", el).pasteImgEvent();
    $("#addtime", el).datetimepicker({autoclose: true}, function (d) {
        if (d && current_Date) {
            d = d.split(" ")[0];
            if (d > current_Date) {
                ycoa.UI.toast.warning("添加时间不能大于当前时间~");
            }
        }
    });
    el.attr('autoEditSelecter', 'autoEditSelecter');
}

var array = {
    ww: [{id: '无', text: '无', default: true}],
    money: [{id: 300, text: 300}, {id: 500, text: 500}, {id: 800, text: 800}],
    setmeal: [{id: '普通', text: '普通'}, {id: '创业', text: '创业'}, {id: '财富', text: '财富'}],
    payment: [{id: '微店渠道', text: '微店渠道'}, {id: '工行', text: '工行'}, {id: '农行', text: '农行'}, {id: '建行', text: '建行'}, {id: '中行', text: '中行'}, {id: '邮政', text: '邮政'}, {id: '信用社', text: '信用社'}, {id: '支付宝', text: '支付宝'}, {id: '财付通', text: '财付通'}, {id: '微信', text: '微信'}, {id: '淘宝', text: '淘宝'}, {id: '拍拍', text: '拍拍'}, {id: '花呗', text: '花呗'}, {id: '专营店', text: '专营店'}, {id: '旗舰店', text: '旗舰店'}],
    channel: [{id: '百度(PC端)', text: '百度(PC端)'}, {id: '百度(YD端)', text: '百度(YD端)'}, {id: '360', text: '360'}, {id: '搜狗', text: '搜狗'}, {id: '百度直通车', text: '百度直通车'}, {id: '360直通车', text: '360直通车'}, {id: '搜狗直通车', text: '搜狗直通车'}],
    province: [{id: '河北省', text: '河北省', default: true}, {id: '山西省', text: '山西省'}, {id: '辽宁省', text: '辽宁省'}, {id: '吉林省', text: '吉林省'}, {id: '黑龙江省', text: '黑龙江省'}, {id: '江苏省', text: '江苏省'}, {id: '浙江省', text: '浙江省'}, {id: '安徽省', text: '安徽省'}, {id: '福建省', text: '福建省'}, {id: '江西省', text: '江西省'}, {id: '山东省', text: '山东省'}, {id: '河南省', text: '河南省'}, {id: '湖北省', text: '湖北省'}, {id: '湖南省', text: '湖南省'}, {id: '广东省', text: '广东省'}, {id: '海南省', text: '海南省'}, {id: '四川省', text: '四川省'}, {id: '贵州省', text: '贵州省'}, {id: '云南省', text: '云南省'}, {id: '陕西省', text: '陕西省'}, {id: '甘肃省', text: '甘肃省'}, {id: '青海省', text: '青海省'}, {id: '台湾省', text: '台湾省'}, {id: '北京市', text: '北京市'}, {id: '天津市', text: '天津市'}, {id: '上海市', text: '上海市'}, {id: '重庆市', text: '重庆市'}, {id: '广西自治区', text: '广西自治区'}, {id: '内蒙古自治区', text: '内蒙古自治区'}, {id: '宁夏自治区', text: '宁夏自治区'}, {id: '新疆自治区', text: '新疆自治区'}],
    mouth: [{id: '', text: '不限'}, {id: '01', text: '一月'}, {id: '02', text: '二月'}, {id: '03', text: '三月'}, {id: '04', text: '四月'}, {id: '05', text: '五月'}, {id: '06', text: '六月'}, {id: '07', text: '七月'}, {id: '08', text: '八月'}, {id: '09', text: '九月'}, {id: '10', text: '十月'}, {id: '11', text: '十一月'}, {id: '12', text: '十二月'}],
    refund_type: [{id: '部分退款', text: '部分退款'}, {id: '全额退款', text: '全额退款'}, {id: '未退款', text: '未退款'}],
    refund_rate: [{id: '0.00', text: '0.00'}, {id: '0.50', text: '0.50'}, {id: '1.00', text: '1.00'}],
    ind_refund_rate: [{id: '0.00', text: '0.00'}, {id: '0.50', text: '0.50'}, {id: '1.00', text: '1.00'}],
    //controlman: [{id: '退款专员', text: '退款专员'}, {id: '售后老师', text: '售后老师'}],
    duty: [{id: '售前退款', text: '售前退款'}, {id: '售后退款', text: '售后退款'}, {id: '售后部门退款', text: '售后部门退款'}],
    status: [{id: '转账', text: '转账'}, {id: '店铺', text: '店铺'}],
    bool: [{id: '是', text: '是'}, {id: '否', text: '否'}],
    reason: [{id: '个人', text: '个人'}, {id: '货源', text: '货源'}, {id: '二销', text: '二销'}, {id: '软件', text: '软件'}, {id: '服务', text: '服务'}, {id: '收货', text: '收货'}, {id: '其他', text: '其他'}],
    ather: [{id: 'isTimely', text: '及时'}, {id: 'isQQTeach', text: 'QQ教学'}, {id: 'isTmallTeach_qj', text: '旗舰店'}, {id: 'isTmallTeach_zy', text: '专营店'}, {id: 'scheduledPackage', text: '礼包预定'}]
};