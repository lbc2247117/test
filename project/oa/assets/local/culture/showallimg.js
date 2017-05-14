var SystemListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.showAllList = ko.observableArray([]);
    self_.listSystem = function (data) {
        ycoa.ajaxLoadGet("/api/culture/showimg.php", data, function (results) {
            self_.showAllList.removeAll();
            $.each(results.list, function (index, system) {

                system.date = new Date(system.date).format("yyyy-MM-dd");

                self_.showAllList.push(system);
            });
        });
    };

    self_.showImg = function (showId)
    {
        $("#showImages").attr("src", showId);
    }

    self_.downRar = function (rarData)
    {
        window.location.href = rarData;
    }

    self_.DeletePic = function (dataWhere)
    {
        ycoa.UI.messageBox.confirm("确定要删除该图片吗?删除后不可恢复~", function (btn) {
            if (btn) {
                var objData = new Object();
                objData.action = 4;
                objData.picId = dataWhere;
                ycoa.ajaxLoadPost("/api/culture/showimg.php", JSON.stringify(objData), function (result) {
                    if (result.code == 0) {
                        $("#add_causeleave_form").parent().parent().find('#btn_close_primary').click();
                        ycoa.UI.toast.success(result.msg);
                        SystemListViewModel.listSystem({action: 2, showTitle: $("#dic_title").val()});
                    } else {
                        ycoa.UI.toast.error(result.msg);
                    }
                    ycoa.UI.block.hide();
                });
            }
        });
    }
}();
$(function () {
    var reg = new RegExp("(^|&)showtitle=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
    var r = window.location.search.substr(1).match(reg);  //匹配目标参数
    if (r != null)
    {
        $("#dic_title").val(unescape(r[2]));
        $("#pictureName").html(unescape(r[2]));
        reLoadData({action: 2, showTitle: unescape(r[2])});
    }

    $("#dataTable").reLoad(function () {
        $("#searchUserName").val('');
    });

    ko.applyBindings(SystemListViewModel, $("#dataTable")[0]);
//    reLoadData({action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize()});
    $('.reload').click(function () {
        reLoadData({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo()});
    });

    $("#returnNext").click(function () {
        window.location.href = "employeestyle.html";
    });

//    $("#myModal #btn_submit_primary").click(function () {
//        $("#add_img_form").submit();
//    });
    $("#allCheck").click(function () {
        $("#showAllImg input[type='checkbox']").prop("checked", "checked");
    });
    $("body").on("click", "#allNotCheck", function () {
        $("#showAllImg input[type='checkbox']").removeProp("checked");
    });

//    $("body").on("click", "#checkId", function () {
//        if ($("input:checkbox[id='checkId']").is(":checked"))
//        {
//            $("input:checkbox[value='" + $(this).val() + "']").attr("checked", true);
//        } 
//        else
//        {
//            $("input:checkbox[value='" + $(this).val() + "']").removeProp("checked");
//        }
//    });

    $("#deleteCheck").click(function () {
        var allId = "";
        $("input:checkbox[name='fruit']:checked").each(function () {
            allId += $(this).val() + "&";
        });
        if (allId != "" && allId != null)
        {
            var objData = new Object();
            objData.action = 5;
            objData.delId = allId.substr(0, (allId.length - 1));
            ycoa.ajaxLoadPost("/api/culture/showimg.php", JSON.stringify(objData), function (result) {
                if (result.code == 0) {
                    $("#add_causeleave_form").parent().parent().find('#btn_close_primary').click();
                    ycoa.UI.toast.success(result.msg);
                    SystemListViewModel.listSystem({action: 2, showTitle: $("#dic_title").val()});
                } else {
                    ycoa.UI.toast.error(result.msg);
                }
                ycoa.UI.block.hide();
            });
        }
        else
        {
            ycoa.UI.toast.warning('请选中要删除的对象...');
        }
    });
});

function formSubmit() {
    $("#add_img_form").ajaxSubmit({
        type: 'post',
        url: "/api/culture/showimg.php",
        success: function (data) {
            debugger
            if (data.code == 0) {
                $("#add_img_form").parent().parent().find('#btn_close_primary').click();
                ycoa.UI.toast.success(data.msg);
                SystemListViewModel.listSystem({action: 2, showTitle: $("#dic_title").val()});
            } else {
                ycoa.UI.toast.error(data.msg);
            }
            ycoa.UI.block.hide();
        },
        error: function (XmlHttpRequest, textStatus, errorThrown) {
            debugger
            alert("error");
        }
    });
}

function reLoadData(data) {
    SystemListViewModel.listSystem(data);
}