var
    GET_DATA='signupList',

    CNT =new Vue({
        el :'#pageWrapper',
        data:{
            tableData:'',
            tableData1:'',
            activityID:URL_PARAM('activityID'),
            curPage:'',
            size:'',
            pageAllData: [],
            pageShowData: [],
            showPageNav: !0,
            pageCount: 1,
        },
        methods:{
            init:function(){
                CNT.getData();
            },
            getData:function(){
                var  _dt={
                    activityID: CNT.activityID,
                    page:CNT.curPage,
                    size:CNT.size
                };
                $.post(GET_DATA,_dt,function(rst){
                    rst = JSON.parse(rst);
                    if (rst.status != 1) {
                        CNT.showNotice('warning', rst.msg);
                        return false;
                    }
                    CNT.tableData = rst.data.data1;
                    CNT.tableData1 = rst.data.data2;
                    if (CNT.curPage == 1)
                        CNT.getPageNum(rst.data.num);
                });
            },
            getPageNum: function (num) {
                this.pageAllData = [];
                if (num > 20) {
                    for (var i = 0; i < Math.ceil(num / 20); i++) {
                        this.pageAllData.push({val: i, num: i + 1});
                    }
                    this.pageCount = this.pageAllData.length;
                    this.pageShowData = this.pageAllData.length > 5 ? [this.pageAllData[0], this.pageAllData[1], this.pageAllData[2], this.pageAllData[3], this.pageAllData[4]] : this.pageAllData;
                    this.showPageNav = !0;
                } else {
                    this.showPageNav = !1;
                    this.pageCount = 1;
                }
            },
            downlist: function() {
                window.location.href = 'exportSignUp?activityID='+CNT.activityID;
            },
            pageNav: function (num) {
                var _cb = function (num) {
                    CNT.curPage = num;
                    var _arr = [];
                    if (CNT.curPage > 3) {
                        if (CNT.curPage < CNT.pageAllData.length - 2) {
                            var x = 0;
                            for (var i = CNT.curPage - 3; i < CNT.curPage + 2; i++) {
                                _arr.push(CNT.pageAllData[i]);
                                console.log(CNT.pageAllData[i].num + ' | ' + i + ' | ' + _arr[x].num);
                                x++;
                            }
                        } else {
                            var count = 5;
                            if (CNT.pageAllData.length < 5)
                                count = CNT.pageAllData.length;
                            for (var i = CNT.pageAllData.length - count; i < CNT.pageAllData.length; i++) {
                                _arr.push(CNT.pageAllData[i]);
                            }
                        }
                    } else {
                        if (CNT.pageAllData.length < 6) {
                            for (var i = 0; i < CNT.pageAllData.length; i++) {
                                _arr.push(CNT.pageAllData[i]);
                            }
                        }
                        else {
                            _arr = [CNT.pageAllData[0], CNT.pageAllData[1], CNT.pageAllData[2], CNT.pageAllData[3], CNT.pageAllData[4]];
                        }
                    }
                    CNT.pageShowData = _arr;
                }
                if (!!num && num <= this.pageAllData.length && num >= 0)
                    this.getData(num, '', _cb(num));
            },
        }
    });

CNT.init();
$('#commonact').addClass('open');
$('#commonact').parents('.dropdown').addClass('open');
