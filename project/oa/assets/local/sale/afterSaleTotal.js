var afterSaleTotal = new function () {
    var self_ = this;
    self_.listaftersales = function (data) {
        $.get("/api/sale/afterSaleTotal.php", data, function (results) {
            debugger
            var res = JSON.parse(results);
            $('#timemonth').val(res.timemonth);
            $('#totalfirstmoney').html('当月售前累计完成业绩：￥' + res.totalmoneyfirst);
            $('#totalsecondmoney').html('当月售后累计完成业绩：￥' + res.totalmoneysecond);
            var infofirst = '<thead><tr><th>日期</th><th>流量单数</th><th>完成单数</th><th>完成业绩(日统计)</th></tr></thead><tbody>';
            var listone = res.listfirst;
            for (var i = 0; i < listone.length; i++)
            {
                infofirst += '<tr><td>' + listone[i]['add_day'] + '</td>';
                infofirst += '<td>' + listone[i]['num'] + '</td>';
                infofirst += '<td>' + listone[i]['dealnum'] + '</td>';
                infofirst += '<td>￥' + listone[i]['dealmoney'] + '</td></tr>';
            }
            infofirst += '</tbody>';
            $('#dataTable1').html(infofirst);
            var listtwo = res.listsecond;
            var infotwo = '<thead><tr><th>日期</th><th>补款</th><th>其他</th><th>完成业绩(日统计)</th></tr></thead><tbody>';
            for (var i = 0; i < listtwo.length; i++)
            {
                infotwo += '<tr><td>' + listtwo[i]['add_day'] + '</td>';
                infotwo += '<td>￥' + listtwo[i]['final'] + '</td>';
                infotwo += '<td>￥' + listtwo[i]['other'] + '</td>';
                infotwo += '<td>￥' + listtwo[i]['daymoney'] + '</td></tr>';
            }
            infotwo += '</tbody>';
            $('#dataTable').html(infotwo);
        });
    }
};
$(function () {
    reLoadData({action: 1});
    $('#premonth').click(function () {
        reLoadData({action: 1, timemonth: $('#timemonth').val(), inner: 1});
    });
    $('#nextmonth').click(function () {
        reLoadData({action: 1, timemonth: $('#timemonth').val(), inner: 2});
    });
});
function reLoadData(data) {
    afterSaleTotal.listaftersales(data);

}