var interval;
var customer_list;
var isCreate = false;
var FillarrearsListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.fillarrearsList = ko.observableArray([]);
    self_.listFillarrears = function (data) {
        ycoa.ajaxLoadGet("/api/sale_soft/fillarrears.php", data, function (results) {
            self_.fillarrearsList.removeAll();
            customer_list = results.customer_list;
            current_Date = results.current_date;
            $.each(results.list, function (index, fillarrears) {
                fillarrears.dele = ycoa.SESSION.PERMIT.hasPagePermitButton("3030402");
                fillarrears.edit = ycoa.SESSION.PERMIT.hasPagePermitButton("3030403");
                fillarrears.show = ycoa.SESSION.PERMIT.hasPagePermitButton("3030405");
                fillarrears.setcustomer = ycoa.SESSION.PERMIT.hasPagePermitButton("3030406");
                self_.fillarrearsList.push(fillarrears);
            });
            if (!isCreate) {
                $("#add_fill_form #nick_name").autoEditSelecter(customer_list, function (data) {
                    data.el.parent("div").parent("div").find("#customer_id").val(data.id);
                });
                isCreate = true;
            }
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                reLoadData({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize()});
            }, function (pageNo) {
                reLoadData({action: 1, pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize()});
            });
        });
    };
    self_.delFillarrears = function (fillarrears) {
        ycoa.UI.messageBox.confirm('确认删除？', function (del) {
            if (del) {
                fillarrears.action = 3;
                ycoa.ajaxLoadPost("/api/sale_soft/fillarrears.php", JSON.stringify(fillarrears), function (result) {
                    if (result.code == 0) {
                        ycoa.UI.toast.success(result.msg);
                        FillarrearsListViewModel.fillarrearsList.remove(fillarrears);
                        reLoadData();
                    } else {
                        ycoa.UI.toast.error(result.msg);
                    }
                    ycoa.UI.block.hide();
                });

            }
        });
    };
    self_.editFillarrears = function (fillarrears) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + fillarrears.id).show();
        $("#submit_" + fillarrears.id).show();
        $("#cancel_" + fillarrears.id).show();
        if (!$("#form_" + fillarrears.id).attr('autoEditSelecter')) {
            initEditSeleter($("#form_" + fillarrears.id));
        }
        $("#customer_id").val(fillarrears.customer_id2);
        $("#customer_id2").val(fillarrears.customer_id);
        $("#tr_" + fillarrears.id + " input,#tr_" + fillarrears.id + " textarea").removeAttr("disabled");
    };
    self_.cancelTr = function (fillarrears) {
        $("#tr_" + fillarrears.id).hide();
        $("#submit_" + fillarrears.id).hide();
        $("#cancel_" + fillarrears.id).hide();
    };
    self_.showFillarrears = function (fillarrears) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + fillarrears.id).show();
        $("#submit_" + fillarrears.id).show();
        $("#cancel_" + fillarrears.id).show();
        if (!$("#form_" + fillarrears.id).attr('autoEditSelecter')) {
            initEditSeleter($("#form_" + fillarrears.id));
        }
        $("#tr_" + fillarrears.id + " input,#tr_" + fillarrears.id + " textarea").attr("disabled", "");
    };
    self_.setCustomer = function (fillarrears) {
        var html = "<div class='cu_div' id='doback_" + fillarrears.id + "'>";
        html += "<select name='nick_name' id='nick_name' style='width:300px;height:41px; ;float:left' class='form-control'>";
        $.each(customer_list, function (index, d) {
            html += "<option value='" + d.id + "'>" + (d.text) + "</option>";
        });
        html += "</select>";
        html += "<span class='input-group-addon' id='setCustomerOk' val='" + fillarrears.id + "' status='" + fillarrears.status + "' customer_id='" + fillarrears.customer_id + "'><i class='glyphicon glyphicon-ok' title='提交'></i></span>";
        html += "<span class='input-group-addon' id='setCustomerCancel' val='" + fillarrears.id + "' status='" + fillarrears.status + "'><i class='glyphicon glyphicon-remove' title='取消'></i></span>";
        html += "</div>";
        $("#customer_td_" + fillarrears.id).append(html);
        $(".doback_open").animate({opacity: 'toggle', width: '0px'}, 500, function () {
            $(this).hide();
            $(this).removeClass("doback_open");
        });
        $("#doback_" + fillarrears.id).show();
        $("#doback_" + fillarrears.id).animate({width: '382px', opacity: 'show'}, 500, function () {
            $(this).addClass("doback_open");
        });
    };
}();
$(function () {
    ko.applyBindings(FillarrearsListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1});
    $("#dataTable").sort(function (data) {
        reLoadData({action: 1, sort: data.sort, sortname: data.sortname, pagesize: ycoa.SESSION.PAGE.getPageSize(), deptid: $('#deptid').val(), searchName: $("#searchUserName").val(), status: $("#status").val()});
    });
    $("#dataTable").reLoad(function () {
        reLoadData({action: 1});
        $('#searchUserName').val('');
    });

    $("#dataTable").searchUserName(function (name) {
        reLoadData({action: 1, searchName: name});
    });
    $(".date-picker-bind-mouseover").live("mouseover", function () {
        $(this).datepicker({autoclose: true});
    });
    $(".date-time-picker-bind-mouseover").datetimepicker({autoclose: true}, function (d) {
        if (d && current_Date) {
            d = d.split(" ")[0];
            if (d > current_Date) {
                ycoa.UI.toast.warning("添加时间不能大于当前时间~");
            }
        }
    });
    $("#dataTable thead input[id='checkall']").change(function () {
        if ($(this).prop("checked")) {
            $("#dataTable tbody input[type='checkbox']").prop("checked", "checked");
        } else {
            $("#dataTable tbody input[type='checkbox']").removeAttr("checked");
        }
    });
    $("#btn_addfill_primary").click(function () {
        $("#add_fill_form").submit();
    });
    $("#fill").click(function () {
        $("#add_fill_form input[type='text']").val("");
        $("#add_fill_form #attachment").html("");
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
            location.href = '/api/sale_soft/fillarrears.php?start_time=' + start_time + '&end_time=' + end_time + '&action=11';
        }
    });
    $(".dept_submit_btn").live("click", function () {
        var formid = "form_" + $(this).attr("val");
        var data = $("#" + formid).serializeJson();
        data.action = 2;
        data.attachment = $("#" + formid + " #attachment_edit").html();
        data = JSON.stringify(data);
        ycoa.ajaxLoadPost("/api/sale_soft/fillarrears.php", data, function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                $('.cancel_btn').click();
                reLoadData({});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    });
    $("#add_fill_form #att").click(function () {
        if ($("#add_fill_form #att").prop('checked')) {
            $("#add_fill_form #editor").stop().animate({height: 'toggle'});
        } else {
            $("#add_fill_form #editor").stop().animate({height: 'toggle'});
        }
    });
    $("#add_fill_form #payment").autoEditSelecter(array['payment']);
    $("#add_fill_form #channel").autoEditSelecter(array['channel']);
    $("#add_fill_form #attachment").pasteImgEvent();

    $("#setCustomerOk").live("click", function () {
        var id = $(this).attr("val");
        var status = $(this).attr("status");
        var customer_id = $(this).attr("customer_id");
        var v = $("#doback_" + id).find("select").val();
        var t = $("#doback_" + id).find("select").find('option:selected').text();
        t = t.replace(")", "").split("(");
        var nick_name = t[1];
        var customer = t[0];
        updateCL({id: id, status: status, customer_id: customer_id, customer_id2: v, nick_name2: nick_name, customer2: customer});
        $("#doback_" + id).animate({opacity: 'toggle', width: '0px'}, 500, function () {
            $(this).hide();
            $(this).removeClass("doback_open");
            $(this).find("textarea").val("");
        });
    });
    $("#setCustomerCancel").live("click", function () {
        var id = $(this).attr("val");
        $("#doback_" + id).animate({opacity: 'toggle', width: '0px'}, 500, function () {
            $(this).hide();
            $(this).removeClass("doback_open");
            $(this).find("textarea").val("");
        });
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
    $("#add_fill_form #add_name").click(function () {
        ycoa.UI.empSeleter({el: $(this), type: 'only', groupId: [11]}, function (node, el) {
            el.val(node.name);
        });
    });
});
function reLoadData(data) {
    FillarrearsListViewModel.listFillarrears(data);
}
function updateCL(fillarrears) {
    fillarrears.action = 2;
    ycoa.ajaxLoadPost("/api/sale_soft/fillarrears.php", JSON.stringify(fillarrears), function (result) {
        if (result.code == 0) {
            ycoa.UI.toast.success("操作成功~");
            reLoadData({});
        } else {
            ycoa.UI.toast.error("操作失败~");
        }
        ycoa.UI.block.hide();
    });
}
;

function initEditSeleter(el) {
    $("#payment", el).autoEditSelecter(array['payment']);
    $("#channel", el).autoEditSelecter(array['channel']);
    $("#attachment_edit", el).pasteImgEvent();
    $("#nick_name", el).autoEditSelecter(customer_list, function (data) {
        data.el.parent("div").parent("td").find("#customer_id").val(data.id);
    });
    $("#add_name", el).click(function () {
        ycoa.UI.empSeleter({el: $(this), type: 'only', groupId: [11]}, function (node, el) {
            el.val(node.name);
        });
    });
    el.attr('autoEditSelecter', 'autoEditSelecter');
}

var array = {
    payment: [{id: '工行', text: '工行'}, {id: '农行', text: '农行'}, {id: '建行', text: '建行'}, {id: '中行', text: '中行'}, {id: '邮政', text: '邮政'}, {id: '信用社', text: '信用社'}, {id: '支付宝', text: '支付宝'}, {id: '财付通', text: '财付通'}, {id: '微信', text: '微信'}, {id: '淘宝', text: '淘宝', default: true}, {id: '拍拍', text: '拍拍'}],
    channel: [{id: '旺旺', text: '旺旺'}, {id: '百度', text: '百度'}, {id: '360', text: '360'}, {id: 'UC', text: 'UC'}]
};