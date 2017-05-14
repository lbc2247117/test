var
  GET_DATA_API = 'selTIcket',
  SAVE_API = 'addTicket',
  EDIT_API = 'alertTicket',
  POST_API = '',
  ID = SEARCH_ID,
  PID = URL_PARAM('pid'),
  ISEDIT = !!SEARCH_ID,
  CNT = new Vue({
    el: '#pageWrapper',
    data: {
      imgCover: '',
      title: '',
      per: '',
      startTime: '',
      endTime: '',
      rule: '',
      state: ''
    },
    methods: {
      init: function() {
        if (ISEDIT) {
          this.getData();
          POST_API = EDIT_API;
        } else {
          POST_API = SAVE_API;
        }
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
        var _dt = {
          id: ID
        };
        this.ajaxFn(
          GET_DATA_API,
          _dt,
          function(rst) {
            if (rst.status == '1') {
              CNT.listData(rst.data);
              if (rst.status == '1') {
                CNT.listData(rst.data);
              }
            }
          },
          '请求数据失败'
        );
      },
      listData: function(data) {
        this.title = data.voucherName;
        this.per = data.zk;
        this.imgCover = data.picUrl;
        this.startTime = data.useTime[0];
        this.endTime = data.useTime[1];
        this.rule = !!data.remark ? data.remark : '';
        this.state = data.state;
        if (EDITOR_READY) {
          EDITOR.setContent(this.rule);
        } else {
          EDITOR.ready(function() {
            EDITOR.setContent(CNT.rule);
          });
        }
      },
      pickImg: function() {
        $('#coverIpt').click();
      },
      checkPer: function() {
        var _reg = /^\d+(\.\d+)?$/;
        if (!_reg.test(this.per) || parseFloat(this.per) > 10 || parseFloat(this.per) < 0) {
          this.per = isNaN(parseFloat(this.per)) ? 10 : (parseFloat(this.per) > 10 ? 10 : 0);
          BASE.showAlert('折扣为0~10之间数字<br>可保留一位小数～');
        } else {
          this.per = parseFloat(this.per);
        }
      },
      back: function() {
        this.gotoBiz();
      },
      gotoShortBus: function() {
        window.location.href = 'shortbus.html?id=' + PID;
      },
      gotoBiz: function() {
        window.location.href = 'bizlist.html?id=' + PID;
      },
      gotoBusData: function() {
        window.location.href = 'busdata.html?id=' + PID;
      },
      gotoBusiness: function() {
        window.location.href = 'base.html?id=' + PID;
      },
      gotoUnique: function() {
        window.location.href = 'unique.html?id=' + PID;
      }
    }
  }),
  EDITOR_READY = !1,
  EDITOR = UE.getEditor('laiaEditor', {
    toolbars: [
      [
        'undo',
        'redo',
        'bold',
        'italic',
        'underline',
        'fontborder',
        'forecolor',
        'backcolor',
        'fontsize',
        'fontfamily',
        'justifyleft',
        'justifyright',
        'justifycenter',
        'justifyjustify',
        'strikethrough',
        'time',
        'date'
      ]
    ]
  });
EDITOR.ready(function() {
  EDITOR_READY = !0;
});
$(function() {
  CNT.init();
  var coverView = new uploadPreview({
    UpBtn: "coverIpt",
    ImgShow: "coverView",
    ImgType: ["jpg", "png"],
    ErrMsg: "选择文件错误,图片类型必须是(jpg,png)中的一种",
    callback: function() {
      CNT.imgCover = $('#coverView').attr('src');
    }
  });
  $('#saveBtn').click(function() {
    if ('' == TRIM(CNT.title)) {
      BASE.showAlert('请输入标题');
      return !1;
    } else if ('' == TRIM(CNT.per)) {
      BASE.showAlert('请输入折扣信息');
      return !1;
    } else if ('' == TRIM(CNT.imgCover)) {
      BASE.showAlert('请上传优惠券封面');
      return !1;
    }
    if (new Date(CNT.endTime) - new Date(CNT.startTime) < 0) {
      BASE.showAlert('开始时间不能大于结束时间');
      return !1;
    }
    $('#laiaForm').ajaxSubmit({
      url: POST_API,
      data: {
        useTime: CNT.startTime + '~' + CNT.endTime,
        id: ISEDIT ? ID : PID
      },
      success: function(rst, status) {
        if (status == 'success') {
          if (typeof rst != 'object') rst = JSON.parse(rst);
          if (rst.status == '1') {
            BASE.showConfirm('优惠券修改成功啦~<br>将跳转到优惠券列表页', function() {
              CNT.back();
            });
          } else {
            BASE.showAlert(rst.msg);
          }
        } else {
          BASE.showAlert('网络有点儿问题~<br>稍后再试吧~');
        }
      }
    });
  });
  $('#business').addClass('open');
  $('#business').parents('.dropdown').addClass('open');
});