var
  GET_DATA_API = '../Near/selInfo?lon=' + SEARCH_LON + '&lat=' + SEARCH_LAT,
  VERIFY_API = '../Near/getCode',
  BOOK_API = '../Near/addOrder',
  LAIA = new Vue({
    el: '#laiaCnr',
    data: {
      id: SEARCH_ID,
      biz: URL_PARAM('biz'),
      usr: '',
      tel: '',
      bookDate: '',
      bookCount: 1,
      verify: '',
      verifyBtn: '发送验证码',
      verifyBlock: !1,
      resendTimer: '',
      help: '',
      success: !1,
      error: !1
    },
    methods: {
      countChange: function() {
        if (this.bookCount > 99) {
          this.bookCount = 99;
          BASE.showAlert('最多可预约99人');
        }
      },
      countMinus: function() {
        this.bookCount = parseInt(this.bookCount) > 1 ? (parseInt(this.bookCount) - 1) : parseInt(this.bookCount);
      },
      countPlus: function() {
        this.bookCount = parseInt(this.bookCount) < 99 ? (parseInt(this.bookCount) + 1) : parseInt(this.bookCount);
      },
      verifyFn: function() {
        if (this.verifyBlock) {
          BASE.showAlert('验证码没收到？等下再试试~');
          return !1;
        }
        if (this.checkCell()) {
          $.post(VERIFY_API, {
            tel: this.tel
          }, function(rst, status) {
            if (status == 'success') {
              if (typeof rst != 'object') rst = $.parseJSON(rst);
              if (rst.status == '1') {
                BASE.showAlert('验证码发送成功！');
                LAIA.countResend();
              } else {
                BASE.showAlert(rst.msg);
              }
            } else {
              BASE.showConfirm('网络有点儿问题');
            }
          });
        }
      },
      countResend: function() {
        var
          _count = 30,
          _countDsc = '';
        this.verifyBlock = !0;
        this.resendTimer = setInterval(function() {
          _count--;
          if (_count < 1) {
            clearInterval(LAIA.resendTimer);
            LAIA.verifyBlock = !1;
            _countDsc = '';
          } else {
            _countDsc = '(' + _count + 's)';
          }
          LAIA.verifyBtn = '重新发送' + _countDsc;
        }, 1000);
      },
      checkCell: function() {
        var _rst = /^(13[0-9]{9})|(14[0-9]{9})|(15[0-9]{9})|(17[0-9]{9})|(18[0-9]{9})$/.test(this.tel);
        if (!_rst) BASE.showAlert('请填写有效的手机号码');
        return _rst;
      },
      showHelp: function(msg) {
        this.help = msg;
      },
      toBiz: function() {
        window.location.href = 'biz.html?id=' + SEARCH_ID + '&lon=' + SEARCH_LON + '&lat=' + SEARCH_LAT + '&cate=' + URL_PARAM('cate');
      },
      toBook: function() {
        this.error = !1;
      },
      gotoCurLocation: function (e) {
        setTimeout(function () {
         $("html,body").animate({scrollTop: $(e).offset().top}, 500);
        }, 500);
      },
      bookConfirm: function() {
        var
          _help = '请完整填写：',
          _continue;

        _help += !this.bookDate ? ' 预约时间' : '';
        _help += !this.bookCount ? ' 预约人数' : '';
        _help += !this.usr ? ' 联系人姓名' : '';
        _help += !this.checkCell() ? ' 手机号' : '';
        _help += !this.verify ? ' 验证码' : '';

        _continue = !!this.bookDate && !!this.bookCount && !!this.usr && this.checkCell() && !!this.verify;

        if (!_continue) {
          this.showHelp(_help);
          return !1;
        }
        $.post(BOOK_API, {
          id: SEARCH_ID,
          time: this.bookDate,
          personNum: this.bookCount,
          name: this.usr,
          tel: this.tel,
          code: this.verify
        }, function(rst, status) {
          if (status == 'success') {
            if (typeof rst != 'object') rst = $.parseJSON(rst);
            if (rst.status == '1') {
              LAIA.success = !0;
            } else {
              BASE.showAlert(rst.msg);
            }
          } else {
            LAIA.error = !0;
          }
        });
      }
    }
  });

$(function() {
  $('#datetimepicker').datetimepicker('setStartDate', new Date());
});