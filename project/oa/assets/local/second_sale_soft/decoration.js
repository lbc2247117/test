var rception_array;
var DecorationListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.decorationList = ko.observableArray([]);
    self_.listDecoration = function (data) {
        ycoa.ajaxLoadGet("/api/second_sale_soft/decoration.php", data, function (results) {
            self_.decorationList.removeAll();
            $.each(results.list, function (index, decoration) {
                decoration.isArrears_text = decoration.isArrears === 1 ? "√" : "";
                decoration.dele = ycoa.SESSION.PERMIT.hasPagePermitButton('3060302');
                decoration.edit = ycoa.SESSION.PERMIT.hasPagePermitButton('3060302');
                decoration.show = ycoa.SESSION.PERMIT.hasPagePermitButton('3060302');
                self_.decorationList.push(decoration);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                var data = {
                    action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: pageSize, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(),
                    workName: $("#workName").val(), wwName: $("#wwName").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                };
                if (data.searchStartTime || data.searchEndTime) {
                    data.searchTime = "";
                }
                reLoadData(data);
            }, function (pageNo) {
                var data = {
                    action: 1, pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(),
                    workName: $("#workName").val(), wwName: $("#wwName").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                };
                if (data.searchStartTime || data.searchEndTime) {
                    data.searchTime = "";
                }
                reLoadData(data);
            });
        });
    };
    self_.delDecoration = function (decoration) {
        ycoa.UI.messageBox.confirm('确认删除？', function (del) {
            if (del) {
                decoration.action = 2;
                ycoa.ajaxLoadPost("/api/second_sale_soft/decoration.php", JSON.stringify(decoration), function (result) {
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
    self_.editDecoration = function (decoration) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + decoration.id).show();
        $("#submit_" + decoration.id).show();
        $("#cancel_" + decoration.id).show();
        if (!$("#form_" + decoration.id).attr('autoEditSelecter')) {
            initEditSeleter($("#form_" + decoration.id));
        }
        $("#tr_" + decoration.id + " input,#tr_" + decoration.id + " textarea").removeAttr("disabled");
    };
    self_.showDecoration = function (decoration) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + decoration.id).show();
        $("#cancel_" + decoration.id).show();
        if (!$("#form_" + decoration.id).attr('autoEditSelecter')) {
            initEditSeleter($("#form_" + decoration.id));
        }
        $("#tr_" + decoration.id + " input,#tr_" + decoration.id + " textarea").attr("disabled", "");
    };
    self_.cancelTr = function (decoration) {
        $("#tr_" + decoration.id).hide();
        $("#submit_" + decoration.id).hide();
        $("#cancel_" + decoration.id).hide();
    };
    self_.doEditSubmit = function (decoration) {
        var formid = "form_" + decoration.id;
        var data = $("#" + formid).serializeJson();
        data.remark = $("#" + formid + " #remark_edit").html();
        data.action = 3;
        data = JSON.stringify(data);
        ycoa.ajaxLoadPost("/api/second_sale_soft/decoration.php", data, function (result) {
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
    ko.applyBindings(DecorationListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1});
    $("#dataTable").sort(function (data) {
        var data = {
            action: 1, sort: data.sort, sortname: data.sortname, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $("#workName").val(), wwName: $("#wwName").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    });
    $("#dataTable").reLoad(function () {
        $("#workName").val("");
        $("#wwName").val("");
        $("#searchDateTime").val("");
        $('#searchStartTime').val("");
        $('#searchEndTime').val("");
        reLoadData({action: 1});
    });
    $("#dataTable").searchUserName(function (name) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: name, wwName: $("#wwName").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    }, '售后/接待', 'workName');
    $("#dataTable").searchUserName(function (name) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $("#workName").val(), wwName: name, searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    }, '旺旺名', 'wwName');
    $("#dataTable").searchDateTimeSlot(function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $("#workName").val(), wwName: $("#wwName").val(), searchTime: $('#searchDateTime').val(), searchStartTime: d.start, searchEndTime: d.end
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    });
    $("#dataTable").searchDateTime(function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            workName: $("#workName").val(), wwName: $("#wwName").val(), searchTime: d, searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
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
    });
    $("#open_dialog_btn").click(function () {
        $("#add_decoration_form input,#add_decoration_form textarea").each(function () {
            if (!$(this).hasClass("not-clear")) {
                $(this).val("");
            }
        });
        $(".has-error,.has-success").each(function () {
            $(this).removeClass("has-error").removeClass("has-success");
        });
        $(".fa-warning,.fa-check").each(function () {
            $(this).removeClass("fa-warning").removeClass("fa-check");
        });
        $("#add_decoration_form #customer").val(ycoa.user.username());
        $("#add_decoration_form #customer_id").val(ycoa.user.userid());
    });

    $("#open_dialog_fill_btn").click(function () {
        $("#add_fillarrears_form input").each(function () {
            if (!$(this).hasClass("not-clear")) {
                $(this).val("");
            }
        });
        $("#add_fillarrears_form #remark_fillarrears").val("");
        $(".has-error,.has-success").each(function () {
            $(this).removeClass("has-error").removeClass("has-success");
        });
        $(".fa-warning,.fa-check").each(function () {
            $(this).removeClass("fa-warning").removeClass("fa-check");
        });
    });
    $("#add_fillarrears_form #qq").keypress(function (e) {
        if (e.keyCode == 13) {
            if ($(this).val()) {
                $.get(ycoa.getNoCacheUrl("/api/second_sale_soft/decoration.php"), {action: 5, qq: $(this).val()}, function (res) {
                    if (res.length == 1) {
                        var data = res[0];
                        $("#add_fillarrears_form input").each(function () {
                            var el = $(this);
                            el.val(data[el.attr("id")]);
                        });
                        $("#add_fillarrears_form #parent_id").val(data.id);
                    } else if (res.length > 1) {
                        var html = "";
                        $.each(res, function (index, d) {
                            html += "<div class='auto_tr' v='" + (JSON.stringify(d)) + "'>";
                            html += "<div class='auto_td' title='" + (d.add_time) + "'>" + (d.add_time) + "</div>";
                            html += "<div class='auto_td' title='" + (d.ww) + "'>" + (d.ww) + "</div>";
                            html += "<div class='auto_td' title='" + (d.qq) + "'>" + (d.qq) + "</div>";
                            html += "<div class='auto_td' title='" + (d.phone) + "'>" + (d.phone) + "</div>";
                            html += "<div class='auto_td' title='" + (d.name) + "'>" + (d.name) + "</div>";
                            html += "<div class='auto_td' title='" + (d.customer) + "'>" + (d.customer) + "</div>";
                            html += "<div class='auto_td' title='" + (d.rception) + "'>" + (d.rception) + "</div>";
                            html += "</div>";
                        });
                        $(".auto_tbody").html(html);
                        var y = $(window).height();
                        var x = $(window).width();
                        $(".div_avatar_outer").css({top: ((y - 500) / 2) + 'px', left: ((x - 750) / 2) + 'px'}).show();
                    } else if (res.length == 0) {
                        ycoa.UI.toast.warning("未匹配到相应的数据,请核对后重试~");
                        $("#add_fillarrears_form input").val("");
                        $("#add_fillarrears_form #remark_fillarrears").val("");
                    }
                });
            }
        }
    });
    $(".auto_tr").live("click", function () {
        var json_str = $(this).attr("v");
        json_str = $.parseJSON(json_str);
        $("#add_fillarrears_form input").each(function () {
            var el = $(this);
            el.val(json_str[el.attr("id")]);
        });
        $("#add_fillarrears_form #parent_id").val(json_str.id);
        $(".div_avatar_outer").hide();
    });
    $(".div_avatar_close_btn").click(function () {
        $(".div_avatar_outer").hide();
    });
    $("#btn_submit_primary").click(function () {
        $("#add_decoration_form").submit();
    });
    $("#btn_submit_fill_primary").click(function () {
        $("#add_fillarrears_form").submit();
    });
    $("#btn_toexcel_primary").click(function () {
        var start_time = $("#start_time").val();
        var end_time = $("#end_time").val();
        if (start_time || end_time) {
            window.location.href = "/api/second_sale_soft/decoration.php?action=10&start_time=" + start_time + "&end_time=" + end_time;
        }
    });
    $(".date-picker-bind-mouseover").datepicker({autoclose: true});
    $("#add_decoration_form #isArrears").autoRadio(array['isArrears']);
    $("#add_decoration_form #decoration_packages").autoEditSelecter(array['decoration_packages']);
    $("#add_decoration_form #payment_method").autoEditSelecter(array['payment_method']);
    $("#add_decoration_form #remark").pasteImgEvent();
    $("#add_fillarrears_form #remark_fillarrears").pasteImgEvent();
    $.get(ycoa.getNoCacheUrl("/api/second_sale_soft/customer.php"), {action: 2, type: 3}, function (res) {
        $("#add_decoration_form #rception").autoEditSelecter(res, function (d) {
            $("#add_physica_form #rception_id").val(d.id);
        });
        rception_array = res;
    });
    $("#show_ranking").click(function () {
        ycoa.ajaxLoadGet("/api/second_sale_soft/platform.php", {action: 11, time_unit: 1}, function (result) {
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
                html += "<li><span class='" + clas + "'>" + (index + 1) + "</span><span class='presales'>" + (d.customer) + "</span><span class='count'>" + (d.count) + "</span></li>";
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
                ycoa.ajaxLoadGet("/api/second_sale_soft/platform.php", {action: 11, time_unit: v}, function (result) {
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
                        html += "<li><span class='" + clas + "'>" + (index + 1) + "</span><span class='presales'>" + (d.customer) + "</span><span class='count'>" + (d.count) + "</span></li>";
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
        $('.div_avatar_outer').draggable({handle: ".div_avatar_close_title"});
        $('.auto_tab_main').draggable({handle: ".title"});
    }
    $("#add_decoration_form #quick_write").keyup(function (e) {
        if (e.keyCode == 13 && $(this).val()) {
            ycoa.ajaxLoadGet("/api/second_sale_soft/platform.php", {action: 6, searchName: $(this).val()}, function (result) {
                if (result.code == 0) {
                    if (result.model) {
                        $.each(result.model, function (i, d) {
                            $("#add_decoration_form #" + i).val($.trim(d));
                        });
                    } else {
                        ycoa.UI.toast.warning("暂未查询到相关数据,请手动填写~");
                    }
                } else {
                    ycoa.UI.toast.warning(result.msg);
                }
            });
        }
    });
});
function reLoadData(data) {
    DecorationListViewModel.listDecoration(data);
}

function updateCL(decoration) {
    decoration.action = 2;
    ycoa.ajaxLoadPost("/api/second_sale_soft/decoration.php", JSON.stringify(decoration), function (result) {
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
    decoration_packages: [{id: 'VIP1', text: 'VIP1'}, {id: 'VIP2', text: 'VIP2'}, {id: 'VIP3', text: 'VIP3'}, {id: 'VIP4', text: 'VIP4'}],
    payment_method: [{id: '银行卡转款', text: '银行卡转款'}, {id: '信用卡', text: '信用卡'}, {id: '花呗', text: '花呗'}, {id: '支付宝', text: '支付宝'}, {id: '微信', text: '微信'}, {id: '财付通', text: '财付通'}, {id: 'QQ钱包', text: 'QQ钱包'}]
};

function initEditSeleter(el) {
    $("#isArrears", el).autoRadio(array['isArrears']);
    $("#decoration_packages", el).autoEditSelecter(array['decoration_packages']);
    $("#payment_method", el).autoEditSelecter(array['payment_method']);
    $("#rception", el).autoEditSelecter(rception_array, function (d) {
        $("#rception_id", el).val(d.id);
    });
    $("#remark_edit", el).pasteImgEvent();
    el.attr('autoEditSelecter', 'autoEditSelecter');
}