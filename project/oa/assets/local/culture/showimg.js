var SystemListViewModel = new function () {
    var self_ = this;
    self_.list = ko.observable("list");
    self_.showList = ko.observableArray([]);
    self_.listSystem = function (data) {
        ycoa.ajaxLoadGet("/api/culture/showimg.php", data, function (results) {
            self_.showList.removeAll();
            $.each(results.list, function (index, system) {
                system.date = new Date(system.date).format("yyyy-MM-dd");

                self_.showList.push(system);
            });
            ycoa.SESSION.PAGE.setPageNo(results.page_no);
            ycoa.initPagingContainers($("#paging-container"), results, function (pageSize) {
                reLoadData({pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: pageSize, searchName: $("#searchUserName").val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName()});
            }, function (pageNo) {
                reLoadData({pageno: pageNo, pagesize: ycoa.SESSION.PAGE.getPageSize(), searchName: $("#searchUserName").val(), sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName()});
            });
        });
    };

    self_.hrefImg = function (data)
    {
        window.location.href = "employeeStyleItem.html?showtitle=" + escape(data);
    }

    self_.DeleteImg = function (data)
    {
        ycoa.UI.messageBox.confirm("确定要删除该相册信息吗?删除后不可恢复~", function (btn) {
            if (btn) {
                var objData = new Object();
                objData.action = 3;
                objData.dic_title = data;
                ycoa.ajaxLoadPost("/api/culture/showimg.php", JSON.stringify(objData), function (result) {
                    if (result.code == 0) {
                        $("#add_causeleave_form").parent().parent().find('#btn_close_primary').click();
                        ycoa.UI.toast.success(result.msg);
                        SystemListViewModel.listSystem({action: 1});
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
    $("#dataTable").reLoad(function () {
        reLoadData({});
        $("#searchUserName").val('');
    });

    ko.applyBindings(SystemListViewModel, $("#dataTable")[0]);
    reLoadData({action: 1, sort: ycoa.SESSION.SORT.getSort(), sortname: ycoa.SESSION.SORT.getSortName(), pageno: ycoa.SESSION.PAGE.getPageNo(), pagesize: ycoa.SESSION.PAGE.getPageSize()});
    $('.reload').click(function () {
        reLoadData({action: 1, pageno: ycoa.SESSION.PAGE.getPageNo()});
    });

    $("#createAlbum #btn_submit_primary").click(function () {
        $("#add_causeleave_form").submit();
    });
});
function reLoadData(data) {
    SystemListViewModel.listSystem(data);
}