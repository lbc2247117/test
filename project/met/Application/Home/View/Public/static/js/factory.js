var
        GET_DATA_API = 'getFactory',
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
            },
        });
MET.init();
$(function () {
    $('#factory').addClass('active');
});