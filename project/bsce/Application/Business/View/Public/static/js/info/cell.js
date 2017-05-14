var
  GET_DATA_API = 'getTel',
  SAVE_API = 'updateSeller',
  CHECK_VERIFY_API = 'checkCode',
  VERIFY_API = 'sendCode',
  CNT = new Vue({
    el: '#pageWrapper',
    data: {
      stepII: !1,
      preCell: '',
      cell: '',
      cellReg: /^(13[0-9]{9})|(14[0-9]{9})|(15[0-9]{9})|(17[0-9]{9})|(18[0-9]{9})$/,
      preVerify: '',
      verify: '',
      pwd: '',
      repwd: '',
      verifyBtn: '获取验证码',
      resendTimer: ''
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
        this.ajaxFn(
          GET_DATA_API,
          '',
          function(rst) {
            if (rst.status == '1') {
              CNT.preCell = rst.data;
            } else {
              BASE.showAlert(rst.msg);
            }
          }
        );
      },
      verifyFn: function() {
        if (this.checkCell()) {
          var _dt = {
            tel: this.stepII ? this.cell : this.preCell
          };
          this.ajaxFn(
            VERIFY_API,
            _dt,
            function(rst) {
              if (rst.status == '1') {
                BASE.showAlert('验证码发送成功！');
                CNT.countResend();
              } else {
                BASE.showAlert(rst.msg);
              }
            }
          );
        }
      },
      checkVerify: function(cell, verify, cb) {
        this.ajaxFn(
          CHECK_VERIFY_API, {
            code: verify
          },
          function(rst) {
            if (typeof cb == 'function') cb(rst);
          }
        );
      },
      countResend: function() {
        var
          _count = 30,
          _countDsc = '';
        this.resendTimer = setInterval(function() {
          _count--;
          if (_count < 1) {
            clearInterval(CNT.resendTimer);
            _countDsc = '';
          } else {
            _countDsc = '(' + _count + 's)';
          }
          CNT.verifyBtn = '重新发送' + _countDsc;
        }, 1000);
      },
      checkCell: function() {
        var _rst = this.cellReg.test(this.stepII ? this.cell : this.preCell);
        if (!_rst) BASE.showAlert('请填写有效的手机号码');
        return _rst;
      },
      nextStep: function() {
        if ('' == TRIM(CNT.preVerify)) {
          BASE.showAlert('请输入验证码');
          return !1;
        }
        this.checkVerify(this.preCell, this.preVerify, function(rst) {
          if (rst.status == '1') {
            clearInterval(CNT.resendTimer);
            CNT.verifyBtn = '获取验证码';
            CNT.stepII = !0;
          } else {
            BASE.showAlert(rst.msg);
          }
        });
      }
    }
  });
$(function() {
  CNT.init();
  $('#saveBtn').click(function() {
    if (!CNT.stepII) {
      BASE.showAlert('还没完成哦～<br>还有下一步呢～');
      return !1;
    }
    if ('' == TRIM(CNT.cell)) {
      BASE.showAlert('请输入手机号');
      return !1;
    } else if ('' == TRIM(CNT.verify)) {
      BASE.showAlert('请输入验证码');
      return !1;
    }
    if (!CNT.checkCell()) return !1;

    $('#laiaForm').ajaxSubmit({
      url: SAVE_API,
      success: function(rst, status) {
        if (status == 'success') {
          if (typeof rst != 'object') rst = JSON.parse(rst);
          if (rst.status == '1') {
            BASE.showConfirm('手机号修改成功啦~<br>将跳转到基本信息页面', function() {
              window.location.href = '../Order/list.html';
            });
          } else {
            BASE.showConfirm(rst.msg + '<br>重来一遍试试？', function() {
              window.location.reload();
            });
          }
        } else {
          BASE.showAlert('保存失败~<br>一会儿再试试？');
        }
      }
    });
  });
});