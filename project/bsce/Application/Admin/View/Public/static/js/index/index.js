var 
GET_DATA_API = '',
CNT = new Vue({
  el: '#wrapper',
  data: {
    curPage: 1,
    pageCount: 1,
    editCnrVisible: !1,
    editObj: {}
  },
  methods: {
    init: function() {
      this.getData();
      this.initEditObj();
    },
    initEditObj: function() {
      this.editObj = {

      };
    },
    getData: function(cb) {
      var _dt = {

      };
      /*$.post(GET_DATA_API, _dt, function(rst, status) {
        if (typeof rst != 'object') rst = $.parseJSON(rst);
        if (rst.status == '1') {
          if (rst.data && rst.data.length > 0) {
            CNT.listData(rst.data);
            if (cb) {
              cb();
            }
          }
        } else {
          BASE.showAlert('warning', rst.msg);
        }
      });*/
      
    },
    listData: function(data) {
      
    },
    showEdit: function(idx) {
      var 
      _dt = this.tableData[idx];
      this.editCnrVisible = !0;
    },
    hideEdit: function() {
      this.editCnrVisible = !1;
      this.initEditObj();
      $('#saveEdit').off('click');
    },
    saveEdit: function() {
      var 
      _api = '',
      _dt = {

      };
      /*$.post(_api, _dt, function(rst, status) {
        if (typeof rst != 'object') rst = $.parseJSON(rst);
        if (rst.status == '1') {
          CNT.getData();
          CNT.showAlert('success', '保存成功！');
        } else {
          CNT.showAlert('warning', rst.msg);
        }
      });*/
    }
  }
});
CNT.init();