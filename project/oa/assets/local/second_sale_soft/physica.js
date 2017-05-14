var rception_array;
var PhysicaListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.physicaList = ko.observableArray([]);
    self_.listPhysica = function (data) {
        ycoa.ajaxLoadGet("/api/second_sale_soft/physica.php", data, function (results) {
            self_.physicaList.removeAll();
            $.each(results.list, function (index, physica) {
                physica.isTeaching_text = physica.isTeaching === 1 ? "√" : "";
                physica.isArrears_text = physica.isArrears === 1 ? "√" : "";
                physica.dele = ycoa.SESSION.PERMIT.hasPagePermitButton('3060202');
                physica.edit = ycoa.SESSION.PERMIT.hasPagePermitButton('3060203');
                physica.show = ycoa.SESSION.PERMIT.hasPagePermitButton('3060204');
                physica.te = ycoa.SESSION.PERMIT.hasPagePermitButton('3060206') && physica.isTe === 0;
                self_.physicaList.push(physica);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                var data = {
                    action: 1, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: pageSize, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(),
                    keyWord: $("#searchUserName").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                };
                if (data.searchStartTime || data.searchEndTime) {
                    data.searchTime = "";
                }
                reLoadData(data);
            }, function (pageNo) {
                var data = {
                    action: 1, pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(),
                    keyWord: $("#searchUserName").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
                };
                if (data.searchStartTime || data.searchEndTime) {
                    data.searchTime = "";
                }
                reLoadData(data);
            });
        });
    };
    self_.delPhysica = function (physica) {
        ycoa.UI.messageBox.confirm('确认删除？', function (del) {
            if (del) {
                physica.action = 2;
                ycoa.ajaxLoadPost("/api/second_sale_soft/physica.php", JSON.stringify(physica), function (result) {
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
    self_.editPhysica = function (physica) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + physica.id).show();
        $("#submit_" + physica.id).show();
        $("#cancel_" + physica.id).show();
        if (!$("#form_" + physica.id).attr('autoEditSelecter')) {
            initEditSeleter($("#form_" + physica.id));
        }
        $("#tr_" + physica.id + " input,#tr_" + physica.id + " textarea").removeAttr("disabled");
    };
    self_.showPhysica = function (physica) {
        $(".second_tr").hide();
        $(".submit_btn").hide();
        $(".cancel_btn").hide();
        $("#tr_" + physica.id).show();
        $("#cancel_" + physica.id).show();
        if (!$("#form_" + physica.id).attr('autoEditSelecter')) {
            initEditSeleter($("#form_" + physica.id));
        }
        $("#tr_" + physica.id + " input,#tr_" + physica.id + " textarea").attr("disabled", "");
    };
    self_.cancelTr = function (physica) {
        $("#tr_" + physica.id).hide();
        $("#submit_" + physica.id).hide();
        $("#cancel_" + physica.id).hide();
    };
    self_.doEditSubmit = function (physica) {
        var formid = "form_" + physica.id;
        var data = $("#" + formid).serializeJson();
        data.remark = $("#" + formid + " #remark_edit").html();
        data.action = 3;

        var agent_price = data.all_price;
        agent_price == "" ? agent_price = 0 : agent_price = parseInt(agent_price);
        if (parseInt(data.free_decoration) > 2) {
            if ((agent_price * 0.8) < 2000) {
                ycoa.UI.toast.warning("代理金额少于2000，最高只能选择2次");
                $("#" + formid + " #free_decoration").val(0);
                return;
            }
        }
        data = JSON.stringify(data);
        ycoa.ajaxLoadPost("/api/second_sale_soft/physica.php", data, function (result) {
            if (result.code == 0) {
                ycoa.UI.toast.success(result.msg);
                reLoadData({action: 1});
            } else {
                ycoa.UI.toast.error(result.msg);
            }
            ycoa.UI.block.hide();
        });
    };
    self_.doTe = function (physica) {
        physica.isTe = 1;
        updateCL(physica);
    };
    self_.change_free_decoration = function (physica) {
        console.log(physica);
    };
}();
$(function () {
    ko.applyBindings(PhysicaListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1});
    $("#dataTable").sort(function (data) {
        var data = {
            action: 1, sort: data.sort, sortname: data.sortname, pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            keyWord: $("#searchUserName").val(), searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    });
    $("#dataTable").reLoad(function () {
        $("#searchUserName").val("");
        $("#searchDateTime").val("");
        $('#searchStartTime').val("");
        $('#searchEndTime').val("");
        reLoadData({action: 1});
    });
    $("#dataTable").searchUserName(function (name) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            keyWord: name, searchTime: $("#searchDateTime").val(), searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    }, '关键字查找');
    $("#dataTable").searchDateTimeSlot(function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            keyWord: $("#searchUserName").val(), searchTime: $('#searchDateTime').val(), searchStartTime: d.start, searchEndTime: d.end
        };
        if (data.searchStartTime || data.searchEndTime) {
            data.searchTime = "";
        }
        reLoadData(data);
    });
    $("#dataTable").searchDateTime(function (d) {
        var data = {
            action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize(),
            keyWord: $("#searchUserName").val(), searchTime: d, searchStartTime: $('#searchStartTime').val(), searchEndTime: $('#searchEndTime').val()
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
        $("#add_physica_form input,#add_physica_form textarea").each(function () {
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
        $("#add_physica_form #customer").val(ycoa.user.username());
        $("#add_physica_form #customer_id").val(ycoa.user.userid());
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
                $.get(ycoa.getNoCacheUrl("/api/second_sale_soft/physica.php"), {action: 5, qq: $(this).val()}, function (res) {
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
        $("#add_physica_form").submit();
    });
    $("#btn_submit_fill_primary").click(function () {
        $("#add_fillarrears_form").submit();
    });
    $("#btn_toexcel_primary").click(function () {
        var start_time = $("#start_time").val();
        var end_time = $("#end_time").val();
        if (start_time || end_time) {
            window.location.href = "/api/second_sale_soft/physica.php?action=10&start_time=" + start_time + "&end_time=" + end_time;
        }
    });
    $(".date-picker-bind-mouseover").datepicker({autoclose: true});
    $("#add_physica_form #isTeaching").autoRadio(array['isTeachingArrears']);
    $("#add_physica_form #isArrears").autoRadio(array['isTeachingArrears']);
    $("#add_physica_form #free_decoration").change(function () {
        var agent_price = $("#add_physica_form #all_price").val();
        agent_price == "" ? agent_price = 0 : agent_price = parseInt(agent_price);
        if (parseInt($(this).val()) > 2) {
            if ((agent_price * 0.8) < 2000) {
                ycoa.UI.toast.warning("代理金额少于2000，最高只能选择2次");
                $("#add_physica_form #free_decoration").val(0);
            }
        }
    });
    $("#add_physica_form #payment_method").autoEditSelecter(array['payment_method']);
    $("#add_physica_form #remark").pasteImgEvent();
    $("#add_fillarrears_form #remark_fillarrears").pasteImgEvent();

    $.get(ycoa.getNoCacheUrl("/api/second_sale_soft/customer.php"), {action: 2, type: 2}, function (res) {
        $("#add_physica_form #rception").autoEditSelecter(res, function (d) {
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
    $("#add_physica_form #quick_write").keyup(function (e) {
        if (e.keyCode == 13 && $(this).val()) {
            ycoa.ajaxLoadGet("/api/second_sale_soft/platform.php", {action: 6, searchName: $(this).val()}, function (result) {
                if (result.code == 0) {
                    if (result.model) {
                        $.each(result.model, function (i, d) {
                            $("#add_physica_form #" + i).val($.trim(d));
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
    PhysicaListViewModel.listPhysica(data);
}

function updateCL(physica) {
    physica.action = 3;
    ycoa.ajaxLoadPost("/api/second_sale_soft/physica.php", JSON.stringify(physica), function (result) {
        if (result.code == 0) {
            ycoa.UI.toast.success("操作成功~");
            reLoadData({action: 1});
        } else {
            ycoa.UI.toast.error("操作失败~");
        }
        ycoa.UI.block.hide();
    });
}

var array = {
    isTeachingArrears: [{id: '1', text: '是'}, {id: '0', text: '否'}],
    payment_method: [{id: '银行卡转款', text: '银行卡转款'}, {id: '信用卡', text: '信用卡'}, {id: '花呗', text: '花呗'}, {id: '支付宝', text: '支付宝'}, {id: '微信', text: '微信'}, {id: '财付通', text: '财付通'}, {id: 'QQ钱包', text: 'QQ钱包'}]
};

function initEditSeleter(el) {
    $("#isTeaching", el).autoRadio(array['isTeachingArrears']);
    $("#isArrears", el).autoRadio(array['isTeachingArrears']);
//    $("#free_decoration", el).autoEditSelecter(array['free_decoration'], function (d) {
//        var agent_price = $("#agent_price", el).val();
//        agent_price == "" ? agent_price = 0 : agent_price = parseInt(agent_price);
//        if (parseInt(d.id) > 2) {
//            if (agent_price < 2000) {
//                ycoa.UI.toast.warning("代理金额少于2000，最高只能选择2次");
//                $("#free_decoration", el).val("");
//            }
//        }
//    });
    $("#payment_method", el).autoEditSelecter(array['payment_method']);
    $("#rception", el).autoEditSelecter(rception_array, function (d) {
        $("#rception_id", el).val(d.id);
    });
    $("#remark_edit", el).pasteImgEvent();
    el.attr('autoEditSelecter', 'autoEditSelecter');
}