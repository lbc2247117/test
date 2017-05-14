/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var rception_array, customer_array, headmaster_array, ly_array, rcept_account;
var GenerationOperationListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.generationOperationList = ko.observableArray([]);
    self_.listGenerationOperation = function (data) {
        ycoa.ajaxLoadGet("/api/second_sale/list_general_operator_wait_record.php", data, function (results) {
            self_.generationOperationList.removeAll();
            $.each(results.list, function (index, generationOperation) {
                if (generationOperation.status == 1) {
                    generationOperation.status = "待认领";
                } else if (generationOperation.status == 2) {
                    generationOperation.status = "冻结";
                }
                self_.generationOperationList.push(generationOperation);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                var data = {
                    action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
                    workName: $('workName').val(), payType: 0, searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                };
                if (data.searchStartTime || data.searchEndTime) {
                    data.searchTime = "";
                }
                reLoadData(data);
            }, function (pageNo) {
                var data = {
                    action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(),
                    workName: $('workName').val(), payType: 0, searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                };
                if (data.searchStartTime || data.searchEndTime) {
                    data.searchTime = "";
                }
                reLoadData(data);
            });
        });
    };
    self_.claim = function (generationOperation) {
        $("#add_generationOperation_form #add_time").val(generationOperation.add_time);
        $("#gen_id").val(generationOperation.id);
        $("#claimModal").modal("show");

    };
    self_.freeze = function (generationOperation) {
        ycoa.UI.messageBox.confirm('确认冻结该笔代运营吗？', function (del) {
            if (del) {
                generationOperation.action = 3;
                ycoa.ajaxLoadPost("/api/second_sale/list_general_operator_wait_record.php", JSON.stringify(generationOperation), function (result) {
                    if (result.code == 0) {
                        ycoa.UI.toast.success(result.msg);
                        reLoadData({action: 1, payType: 0});
                    } else {
                        ycoa.UI.toast.error(result.msg);
                    }
                    ycoa.UI.block.hide();
                });
            }
        });
    };

    self_.del = function (generationOperation) {
        ycoa.UI.messageBox.confirm('确认删除？', function (del) {
            if (del) {
                generationOperation.action = 5;
                ycoa.ajaxLoadPost("/api/second_sale/list_general_operator_wait_record.php", JSON.stringify(generationOperation), function (result) {
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

    self_.edit = function (generationOperation) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + generationOperation.id).show();
        $("#submit_" + generationOperation.id).show();
        $("#cancel_" + generationOperation.id).show();

        $("#tr_" + generationOperation.id + " input,#tr_" + generationOperation.id + " textarea").removeAttr("disabled");
        initEditSeleter($("#form_" + generationOperation.id));
    }
    self_.cancelTr = function (generationOperation) {
        $("#tr_" + generationOperation.id).hide();
        $("#submit_" + generationOperation.id).hide();
        $("#cancel_" + generationOperation.id).hide();
    };
    self_.doEditSubmit = function (generationOperation) {
        var formid = "form_" + generationOperation.id;
        var data = $("#" + formid).serializeJson();
        data.action = 4;
        data = JSON.stringify(data);
        ycoa.ajaxLoadPost("/api/second_sale/list_general_operator_wait_record.php", data, function (result) {
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
    reLoadData({action: 1});
    $("#dataTable").reLoad(function () {
        reLoadData({action: 1});
    });
    $("#btn_submit_wait_primary").click(function () {

        $("#add_wait_record_form").submit();
    });
    $("#dataTable").searchUserName(function (name) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: name, payType: 0, searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    }, '关键字', 'workName');
    $("#dataTable").searchDateTimeSlot(function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $('workName').val(), payType: 0, searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    });
    $("#dataTable").searchDateTime(function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $('workName').val(), payType: 0, searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    });

    $("#btn_claim_submit_primary").click(function () {
        $("#add_generationOperation_form").submit();
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
    $("body").on("click", "#customer", function () {
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
    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 8}, function (res) {
        $("#add_wait_record_form #rcept_account").autoEditSelecter(res, function (d) {
            $("#add_wait_record_form #payment_method").val(d.id);
        });
        rcept_account = res;
    });

    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 1}, function (res) {
        $("#add_claim_employee_form  #employeename").autoEditSelecter(res, function (d) {
            $("#add_claim_employee_form  #employeename_id").val(d.id);
        });
        employee_array = res;
    });

    $("#add_generationOperation_form #isArrears").autoRadio(array['isArrears']);
    $("#add_generationOperation_form #payment_method").autoEditSelecter(array['payment_method']);
    $("#add_generationOperation_form #sales_numbers").autoEditSelecter(array['sales_numbers']);
    $("#add_generationOperation_form #rception_money").autoEditSelecter(array['class_money']);
    $("#add_wait_record_form #rcept_account").autoEditSelecter(rcept_account);
    //$("#add_generationOperation_form #customer_type").autoEditSelecter(array['customer_type']);

    $("#headmaster").autoEditSelecter(headmaster_array, function (d) {
        $("#headmaster_id").val(d.id);
    });

    $("#add_generationOperation_form #remark").pasteImgEvent();
    $("#add_fillarrears_form #remark_fillarrears").pasteImgEvent();

    $.get(ycoa.getNoCacheUrl("/api/second_sale/customer.php"), {action: 2, type: 1}, function (res) {
//        $("#add_generationOperation_form #platform_sales").autoEditSelecter(res, function (d) {
//            $("#add_generationOperation_form #platform_sales_id").val(d.id);
//        });
        rception_array = res;
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
        $("#add_fillarrears_form #headmaster").autoEditSelecter(res, function (d) {
            $("#add_fillarrears_form #headmaster_id").val(d.id);
        });
        headmaster_array = res;
    });



    if (jQuery.ui) {
        $('.div_avatar_outer').draggable({handle: ".div_avatar_close_title"});
    }
});

function reLoadData(data) {
    GenerationOperationListViewModel.listGenerationOperation(data);
}

var array = {
    isArrears: [{id: '1', text: '是'}, {id: '0', text: '否'}],
    customer_type: [{id: '1', text: '优程'}, {id: '2', text: '领远'}],
    class_money: [{id: '1', text: '3600'}, {id: '2', text: '5600'}, {id: '3', text: '9600'}, {id: '4', text: '15600'}],
    sales_numbers: [{id: '1', text: '新手班'}, {id: '2', text: '入门班'}, {id: '3', text: '专业班'}, {id: '4', text: '精通班'}, {id: '5', text: '全职班'}, {id: '6', text: '兼职班'}, {id: '7', text: '定金'}, {id: '7', text: '其他'}],
    payment_method: [{id: '银行卡转款', text: '银行卡转款'}, {id: '信用卡', text: '信用卡'}, {id: '花呗', text: '花呗'}, {id: '支付宝', text: '支付宝'}, {id: '微信', text: '微信'}, {id: '财付通', text: '财付通'}, {id: 'QQ钱包', text: 'QQ钱包'}, {id: '非时序店铺链接', text: '非时序店铺链接'}]
};
function initEditSeleter(el) {
    $("#rcept_account", el).autoEditSelecter(rcept_account, function (d) {
        $("#payment_method", el).val(d.id);
    });
}