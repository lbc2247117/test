var
  GET_DATA_API = 'querySellerxiu',
  SAVE_API = 'alertSellerxiu',
  CNT = new Vue({
    el: '#pageWrapper',
    data: {
      showCnt: '',
      mobileView: '',
      previewMaskVisible: !1,
      startY: '',
      slideTop: 0
    },
    methods: {
      init: function() {
        this.getData();
      },
      ajaxFn: function(api, data, cb, failMsg, errorMsg) {
        $.post(api, data, function(rst, status) {
          if (status == 'success') {
            if (typeof rst != 'object') rst = JSON.parse(rst);
            if (rst.status != '1') BASE.showAlert(!!failMsg ? failMsg : '操作失败~');
            if (typeof cb == 'function') cb(rst);
          } else {
            BASE.showAlert(!!errorMsg ? errorMsg : '网络有点儿问题~<br>稍后再试吧~');
          }
        });
      },
      getData: function() {
        var _dt = {};
        this.ajaxFn(
          GET_DATA_API,
          _dt,
          function(rst) {
            if (rst.status == '1') {
              CNT.showCnt = rst.data;
              if (EDITOR_READY) {
                EDITOR.setContent(CNT.showCnt);
              } else {
                EDITOR.ready(function() {
                  EDITOR.setContent(CNT.showCnt);
                });
              }
            }
          },
          '请求数据失败'
        );
      },
      showView: function(idx) {
        this.mobileView = EDITOR.getContent();
        this.previewMaskVisible = !0;
      },
      hideView: function() {
        this.mobileView = '';
        this.previewMaskVisible = !1;
      },
      mousedown: function(e) {
        e.preventDefault();
        e.currentTarget.addEventListener('mousemove', CNT.mousemove);
        this.startY = e.clientY;
        this.slideTop = parseFloat($('#slideCnt').css('top'));
      },
      mouseup: function(e) {
        e.preventDefault();
        e.currentTarget.removeEventListener('mousemove', CNT.mousemove);
      },
      mouseenter: function(e) {
        e.currentTarget.addEventListener('mousedown', CNT.mousedown);
        e.currentTarget.addEventListener('mouseup', CNT.mouseup);
      },
      mouseleave: function(e) {
        e.currentTarget.removeEventListener('mousemove', CNT.mousemove);
        e.currentTarget.removeEventListener('mousedown', CNT.mousedown);
        e.currentTarget.removeEventListener('mouseup', CNT.mouseup);
      },
      mousemove: function(e) {
        e.preventDefault();
        var
          _maxOff = parseFloat($('#slideCnr').height() - $('#slideCnt').height()),
          _top = parseFloat(this.slideTop + e.clientY - this.startY);
        if (_top > 0 || _maxOff > 0) _top = 0;
        if (_maxOff < 0 && _top < _maxOff) _top = _maxOff;
        $('#slideCnt').css('top', String(_top) + 'px');
      }
    }
  }),
  EDITOR_READY = !1,
  EDITOR = UE.getEditor('laiaEditor', {});
EDITOR.ready(function() {
  EDITOR_READY = !0;
});

$(function() {
  CNT.init();

  $('#saveBtn').click(function() {
    $('#laiaForm').ajaxSubmit({
      url: SAVE_API,
      success: function(rst, status) {
        if (status == 'success') {
          if (typeof rst != 'object') rst = JSON.parse(rst);
          if (rst.status == '1') {
            BASE.showAlert('修改成功啦~');
          } else {
            BASE.showAlert(rst.msg);
          }
        } else {
          BASE.showAlert('网络有点儿问题~<br>稍后再试吧~');
        }
      }
    });
  });
});