var
  GET_DATA_API = 'QueryAllbuss',
  SET_API = 'setUp',
  DEL_API = 'delSeller',
  CNT = new Vue({
    el: '#pageWrapper',
    data: {
      keyword: '',
      selSearchType: 0,
      searchType: [{
        id: '0',
        val: '商家名称'
      }, {
        id: '1',
        val: '商家ID'
      }, {
        id: '2',
        val: '商家账号'
      }],
      selBusType: -1,
      busType: [{
        id: -1,
        val: '全部商家类型'
      }, {
        id: 0,
        val: '自营商家'
      }, {
        id: 1,
        val: '景区商家'
      }],
      selBusCate: -1,
      busCata: [{
        id: -1,
        val: '全部商家种类'
      }, {
        id: 0,
        val: '美食'
      }, {
        id: 1,
        val: '住宿'
      }/*, {
        id: 2,
        val: '购物'
      }, {
        id: 3,
        val: '娱乐'
      }*/],
      selBusStatus: -1,
      busStatus: [{
        id: -1,
        val: '全部商家状态'
      }, {
        id: 0,
        val: '已上架'
      }, {
        id: 1,
        val: '未上架'
      }],
      tableData: [],
      pageAllData: [],
      pageShowData: [],
      checkedArr: [],
      showPageNav: !0,
      curPage: 1,
      pageCount: 1,
      alert: {
        show: !1,
        type: '',
        msg: ''
      },
      notice: {
        show: !1,
        type: '',
        msg: ''
      },
    },
    methods: {
      init: function() {
        this.getData();
      },
      getData: function(page, name, cb) {
        var _dt = {
          page: !!page ? page : this.curPage,
          size: '20',
          CommercialTenantType: this.selBusCate,
          CommercialTenantStyle: this.selBusType,
          frameState: this.selBusStatus,
          serchName: this.keyword
        };
        $('#loading').show();
        $.post(GET_DATA_API, _dt, function(rst) {
          $('#loading').hide();
          rst = JSON.parse(rst);
          if (rst.status != 1) {
            BASE.showAlert(rst.msg);
            return false;
          }
          CNT.tableData = rst.data.data;
          if (CNT.curPage == 1)
            CNT.getPageNum(rst.data.num);
          if (cb) {
            cb();
          }

        })
      },
      downfn: function(idx) {
        var _dt = this.tableData[idx];
        var obj = {
          id: _dt.id,
          frameState: 1
        };
        $.post(SET_API, obj, function(rst) {
          rst = JSON.parse(rst);
          if (rst.status == 1) {
            BASE.showAlert(rst.msg);
            CNT.getData();
            return;
          }
          BASE.showAlert(rst.msg);
        });
      },
      upfn: function(idx) {
        var _dt = this.tableData[idx];
        var obj = {
          id: _dt.id,
          frameState: 0
        };
        $.post(SET_API, obj, function(rst) {
          rst = JSON.parse(rst);
          if (rst.status == 1) {
            BASE.showAlert(rst.msg);
            CNT.getData();
            return;
          }
          BASE.showAlert(rst.msg);
        });
      },
      showbus: function(idx) {
        var _dt = this.tableData[idx];
        var id = _dt.id;
        window.location.href = 'base.html?id=' + id;
      },
      downlist: function() {
        window.location.href = 'exportSeller';
      },
      add: function() {
        window.location.href = 'base.html';
      },
      del: function() {
        if (this.checkedArr.length < 1) {
          BASE.showAlert('请先选择要删除的商家');
          return false;
        }
        var _dt = {
          id: this.checkedArr
        };
        BASE.showConfirm('确定删除？', function() {
          $.post(DEL_API, _dt, function(rst, status) {
            if (status == 'success') {
              rst = JSON.parse(rst);
              if (rst.status != 1) {
                BASE.showAlert(rst.msg);
                return false;
              }
              BASE.showAlert(rst.msg);
              CNT.getData();
            } else {
              BASE.showAlert('网络不太好～<br>一会儿再试试？');
            }
          });
        });
      },
      getPageNum: function(num) {
        this.pageAllData = [];
        if (num > 20) {
          for (var i = 0; i < Math.ceil(num / 20); i++) {
            this.pageAllData.push({
              val: i,
              num: i + 1
            });
          }
          this.pageCount = this.pageAllData.length;
          this.pageShowData = this.pageAllData.length > 5 ? [this.pageAllData[0], this.pageAllData[1], this.pageAllData[2], this.pageAllData[3], this.pageAllData[4]] : this.pageAllData;
          this.showPageNav = !0;
        } else {
          this.showPageNav = !1;
          this.pageCount = 1;
        }
      },
      pageNav: function(num) {
        var _cb = function(num) {
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
            } else {
              _arr = [CNT.pageAllData[0], CNT.pageAllData[1], CNT.pageAllData[2], CNT.pageAllData[3], CNT.pageAllData[4]];
            }
          }
          CNT.pageShowData = _arr;
        }
        if (!!num && num <= this.pageAllData.length && num >= 0)
          this.getData(num, '', _cb(num));
      }
    }
  });

$(function() {
  CNT.init();
});