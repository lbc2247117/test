var
  GET_DATA_API = '../Seller/queryOrder',
  UPDATE_API = '',
  DEL_API = '../Seller/delOrder',
  ACCEPT_API = '../Seller/passOrder',
  REFUSE_API = '../Seller/rejectOrder',
  CNT = new Vue({
    el: '#pageWrapper',
    data: {
      tableData: [],
      pageAllData: [],
      pageShowData: [],
      checkedArr: [],
      searchKey: '',
      searchType: '0',
      searchTypeArr: [{id: '0', name: '全部'}, {id: '1', name: '待处理'}, {id: '2', name: '已接受'}, {id: '3', name: '已拒绝'}],
      showPageNav: !0,
      curPage: 1,
      pageCount: 1
    },
    methods: {
      init: function() {
        this.getData();
      },
      getData: function(page, cb) {
        var _dt = {
          page: !!page ? page : this.curPage,
          size: 20,
          state: this.searchType
        };
        $.post(GET_DATA_API, _dt, function(rst, status) {
          if (status == 'success') {
            if (typeof rst != 'object') rst = JSON.parse(rst);
            if (rst.status == '1') {
              CNT.tableData = rst.data.data;
              if (CNT.curPage == 1) CNT.getPageNum(rst.data.num);
              if (typeof cb == 'function') cb();
            } else {
              BASE.showAlert('加载数据失败~');
            }
          } else {
            BASE.showAlert('网络有点儿问题~');
          }
        });
      },
      accept: function(idx) {
        var _dt = this.tableData[idx];
        var _id = _dt['id'];
        var _tel=_dt['tel'];
        $.post(ACCEPT_API, {id: _id,tel: _tel}, function(rst, status) {
          if (status == 'success') {
            if (typeof rst != 'object') rst = JSON.parse(rst);
            if (rst.status == '1') {
              BASE.showAlert('接单成功！');
              CNT.getData();
            } else {
              BASE.showAlert(rst.msg);
            }
          } else {
            BASE.showAlert('网络有点儿问题~');
          }
        });
      },
      refuse: function(idx) {
        var _dt = this.tableData[idx];
        var _id = _dt['id'];
        var _tel=_dt['tel'];
        $.post(REFUSE_API, {id: _id,tel: _tel}, function(rst, status) {
          if (status == 'success') {
            if (typeof rst != 'object') rst = JSON.parse(rst);
            if (rst.status == '1') {
              BASE.showAlert('拒单成功！');
              CNT.getData();
            } else {
              BASE.showAlert(rst.msg);
            }
          } else {
            BASE.showAlert('网络有点儿问题~');
          }
        });
      },
      changeState: function(id, state) {
        var _dt = {
          id: id,
          state: state == '0' ? '1' : '0'
        };
        $.post(UPDATE_API, _dt, function(rst, status) {
          if (status == 'success') {
            if (typeof rst != 'object') rst = JSON.parse(rst);
            if (rst.status == '1') {
              BASE.showAlert('更新成功~');
              CNT.getData();
            } else {
              BASE.showAlert('加载数据失败~');
            }
          } else {
            BASE.showAlert('加载数据失败~');
          }
        });
      },
      del: function() {
        if (this.checkedArr.length < 1) return !1;
        BASE.showConfirm('确定删除？', function() {
          $.post(DEL_API, {
            id: CNT.checkedArr
          }, function(rst, status) {
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
        if (!!num && num <= this.pageAllData.length && num >= 0) this.getData(num, _cb(num));
      }
    }
  });
$(function() {
  CNT.init();
});