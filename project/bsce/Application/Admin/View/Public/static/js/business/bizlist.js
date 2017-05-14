var
  GET_DATA_API = 'queryTicket',
  DEL_API = 'delTicket',
  ID = SEARCH_ID,
  CNT = new Vue({
    el: '#pageWrapper',
    data: {
      id: '',
      selStatus: 0,
      status: [{
        id: '0',
        val: '有效期内'
      }, {
        id: '1',
        val: '已过期'
      }],
      size: 20,
      tableData: [],
      pageAllData: [],
      pageShowData: [],
      checkedArr: [],
      showPageNav: !0,
      curPage: 1,
      pageCount: 1,
    },
    methods: {
      init: function() {
        ID = URL_PARAM('id');
        this.getData();
      },
      getData: function() {
        var _dt = {
          id: ID,
          page: this.curPage,
          size: this.size
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
          if (CNT.curPage == 1) CNT.getPageNum(rst.data.num);
        });
      },
      add: function() {
        window.location.href = 'coupon_edit.html?pid=' + ID;
      },
      edit: function(id) {
        window.location.href = 'coupon_edit.html?pid=' + ID + '&id=' + id;
      },
      del: function() {
        if (this.checkedArr.length < 1) return !1;
        BASE.showConfirm('确认删除？', function() {
          var _dt = {
            id: CNT.checkedArr
          };
          $.post(DEL_API, _dt, function(rst, status) {
            if (status == 'success') {
              if (typeof rst != 'object') rst = JSON.parse(rst);
              if (rst.status == '1') {
                BASE.showAlert('删除成功~');
                CNT.getData();
              } else {
                BASE.showAlert(rst.msg);
              }
            } else {
              BASE.showAlert('加载数据失败~');
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
      },
      gotoShortBus: function() {
        window.location.href = 'shortbus.html?id=' + ID;
      },
      gotoBiz: function() {
        window.location.href = 'bizlist.html?id=' + ID;
      },
      gotoBusData: function() {
        window.location.href = 'busdata.html?id=' + ID;
      },
      gotoBusiness: function() {
        window.location.href = 'base.html?id=' + ID;
      },
      gotoUnique: function() {
        window.location.href = 'unique.html?id=' + ID;
      }
    },
  });
$(function() {
  CNT.init();
  $('#business').addClass('open');
  $('#business').parents('.dropdown').addClass('open');
});