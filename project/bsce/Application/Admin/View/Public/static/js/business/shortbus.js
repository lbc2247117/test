var
  GET_DATA_API = 'querySellerxiu',
  SAVE_API = 'alertSellerxiu',
  ID = SEARCH_ID,
  CNT = new Vue({
    el: '#pageWrapper',
    data: {
      content: ''
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
            return;
          }
          CNT.content = rst.data;
          if (EDITOR_READY) {
            EDITOR.setContent(CNT.content);
          } else {
            EDITOR.ready(function() {
              EDITOR.setContent(CNT.content);
            });
          }
        });
      },
      save: function() {
        var _dt = {
          id: ID,
          msg: EDITOR.getContent()
        };
        $('#loading').show();
        $.post(SAVE_API, _dt, function(rst, status) {
          if (status == 'success') {
            $('#loading').hide();
            rst = JSON.parse(rst);
            if (rst.status != 1) {
              BASE.showAlert(rst.msg);
            } else {
              BASE.showAlert('修改成功！');
            }
          } else {
            BASE.showAlert('网络出了点儿问题～');
          }
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
      },
      gotoUnique: function() {
        window.location.href = 'unique.html?id=' + ID;
      }
    },
  }),
  EDITOR_READY = !1,
  EDITOR = UE.getEditor('myEditor', {});
EDITOR.ready(function() {
  EDITOR_READY = !0;
});
$(function() {
  CNT.init();
  $('#business').addClass('open');
  $('#business').parents('.dropdown').addClass('open');

});