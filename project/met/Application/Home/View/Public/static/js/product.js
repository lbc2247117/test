var
        GET_DATA_API = 'getProduct',
        MET = new Vue({
            el: "#wrapper",
            data: {
                tableData: [],
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getData: function () {
                    $.post(GET_DATA_API, {}, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status == 1)
                            MET.tableData = rst.data.list;
                    });
                },
                jumpGoodDesc: function (id) {
                    window.location.href = '/Home/Index/gooddesc.html?id=' + id;
                },
            },
        });
MET.init();
$(function () {
    $('#product').addClass('active');
});