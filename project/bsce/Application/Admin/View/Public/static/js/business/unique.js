var
  GET_DATA_API = 'querySpecial',
  DEL_API = 'delSpecial',
  ID = SEARCH_ID,
  CNT = new Vue({
    el: '#pageWrapper',
    data: {
      tableData: [],
      checkedArr: []
    },
    methods: {
      init: function() {
        this.getData();
      },
      getData: function() {
        var _dt = {
          id: ID
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
        });
      },
      add: function() {
        window.location.href = 'unique_edit.html?pid=' + ID;
      },
      edit: function(id) {
        window.location.href = 'unique_edit.html?pid=' + ID + '&id=' + id;
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
      }
    },
  });
$(function() {
  CNT.init();
  $('#business').addClass('open');
  $('#business').parents('.dropdown').addClass('open');
});