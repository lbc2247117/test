var
  GET_DATA_API = 'queryComment',
  DEL_API = 'delComment',
  ADD_API = 'importComment',
  ID = SEARCH_ID,
  CNT = new Vue({
    el: '#pageWrapper',
    data: {
      tableData: [],
      pageAllData: [],
      pageShowData: [],
      checkedArr: [],
      showPageNav: !0,
      searchType: -1,
      searchTypeArr: [{
        id: -1,
        name: '全部'
      }, {
        id: 0,
        name: '待审核'
      }, {
        id: 1,
        name: '已通过'
      }, {
        id: 2,
        name: '未通过'
      }],
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
          id: ID,
          state: this.searchType == -1 ? '' : this.searchType
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
      getState: function(state) {
        var _state = '';
        switch (String(state)) {
          case '2':
            _state = '未通过';
            break;
          case '1':
            _state = '已通过';
            break;
          case '0':
          default:
            _state = '待审核';
            break;
        }
        return _state;
      },
      add: function() {
        $('#upload').click();
      },
      uploadCsv: function() {
        if ($('#upload').val().substr(-4) != '.csv') {
          BASE.showAlert('请选择 .csv 格式的文件');
          return !1;
        }
        $('#csvForm').ajaxSubmit({
          url: ADD_API,
          data: {
            id: ID
          },
          success: function(rst, status) {
            if (status == 'success') {
              if (typeof rst != 'object') rst = JSON.parse(rst);
              if (rst.status == '1') {
                BASE.showAlert('上传成功！');
                CNT.getData(1);
              } else {
                BASE.showAlert(rst.msg);
              }
            } else {
              BASE.showAlert('网络有点儿问题~');
            }
          }
        });
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
        if (!!num && num <= this.pageAllData.length && num >= 0) this.getData(num, _cb(num));
      }
    }
  });
$(function() {
  CNT.init();
  $('#comment').addClass('open');
  $('#comment').parents('.dropdown').addClass('open');
});