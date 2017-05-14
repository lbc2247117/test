var
  GET_DATA_API = 'queryBuss',
  ADD_API = 'addSeller',
  EDIT_API = 'updateSeller',
  GET_TAG_API = 'lableInfo',
  BANNER_API = 'updateCover',
  ID = SEARCH_ID,
  ISADD = !SEARCH_ID,
  CNT = new Vue({
    el: '#pageWrapper',
    data: {
      isAdd: ISADD,
      id: ID,
      bannerIdx: 0,
      name: '',
      style: 0,
      type: 0,
      tag: [],
      styleArr: [{
        id: 0,
        name: '自营商家'
      }, {
        id: 1,
        name: '景区商家'
      }],
      typeArr: [{
        id: 0,
        name: '美食'
      }, {
        id: 1,
        name: '住宿'
      }],
      tagArr: [],
      remark: '',
      address: '',
      linkman: '',
      cell: '',
      tel: '',
      price: '',
      backPic: [],
      map: '',
      logo: '',
      adrP: '',
      adrC: '',
      adrA: '',
      adrPidx: -1,
      adrCidx: -1,
      adrAidx: -1,
      adrParr: PCA,
      adrCarr: [],
      adrAarr: [],
    },
    methods: {
      init: function() {
        this.getTag();
        if (!ISADD) this.getData();
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
          CNT.setData(rst.data);
        });
      },
      getTag: function() {
        $.post(GET_TAG_API, {
          lableType: this.type
        }, function(rst) {
          rst = JSON.parse(rst);
          if (rst.status != 1) {
            BASE.showAlert(rst.msg);
            return false;
          }
          CNT.tagArr = rst.data.data;
          CNT.initTag();
        });
      },
      initTag: function() { //初始化标签列表
        var _html = '';
        this.tagArr.forEach(function(obj) {
          _html += '<option value="' + obj.id + '">' + obj.lableName + '</option>'; //根据接口返回数据中的Key，修改obj.id和obj.name
        });
        $('#tagPcr').html(_html);
        $('#tagPcr').selectpicker('refresh');
      },
      addUsrTag: function() {
        setTimeout(function() {
          if (DEMO.usrTagArr.indexOf(DEMO.usrTag) < 0) DEMO.usrTagArr.push(DEMO.usrTag);
          DEMO.usrTag = '';
        }, 200);
      },
      selectTag: function() {
        this.sysTagArr = $('#tagPcr').selectpicker('val');
      },
      setData: function(data) {
        this.name = data.name;
        this.style = data.commercialTenantStyle;
        this.type = data.commercialTenantType;
        this.remark = data.remark;
        this.cell = data.tel;
        this.tel = data.tel1;
        this.backPic = data.backPic;
        this.logo = data.logo;
        this.map = data.mapUrl;
        this.price = data.averPrice;
        this.linkman = data.contactPerson;
        this.tagArr.forEach(function(obj, idx) {
          if (data.commercialTenantLableID.split(',').indexOf(obj.lableName) > -1) {
            CNT.tag.push(obj.id);
          }
          BASE.initTextCount();
        });
        $('#tagPcr').selectpicker('val', this.tag);
        var _adrArr = data.adress.split('-');
        this.adrParr.forEach(function(obj, idx) {
          if (_adrArr[0] == obj.name) {
            CNT.adrPidx = idx;
            CNT.adrP = obj.name;
            CNT.adrCarr = obj.list;
            CNT.adrCarr.forEach(function(oobj, iidx) {
              if (_adrArr[1] == oobj.name) {
                CNT.adrCidx = iidx;
                CNT.adrC = oobj.name;
                CNT.adrAarr = oobj.list;
                CNT.adrAarr.forEach(function(ooobj, iiidx) {
                  if (_adrArr[2] == ooobj.name) {
                    CNT.adrAidx = iiidx;
                    CNT.adrA = ooobj.name;
                  }
                });
              }
            });
          }
        });
        this.address = _adrArr[3];
        CNT.getTag();
      },
      adrPchange: function(e) {
        if (parseInt(this.adrPidx) < 0) {
          BASE.showAlert('请选择省份');
        } else {
          this.adrP = this.adrParr[this.adrPidx].name;
          this.adrCarr = this.adrParr[this.adrPidx].list;
          this.adrCidx = -1;
          this.adrAidx = -1;
        }
      },
      adrCchange: function(e) {
        if (parseInt(this.adrCidx) < 0) {
          BASE.showAlert('请选择市');
        } else {
          this.adrC = this.adrCarr[this.adrCidx].name;
          this.adrAarr = this.adrCarr[this.adrCidx].list;
          this.adrAidx = -1;
        }
      },
      adrAchange: function(e) {
        if (parseInt(this.adrAidx) < 0) {
          BASE.showAlert('请选择区/县');
        } else {
          this.adrA = this.adrAarr[this.adrAidx].name;
        }
      },
      cellChange: function() {
        if (!this.checkCell()) {
          this.cell = '';
        }
      },
      telChange: function() {
        if (!this.checkTel()) {
          this.tel = '';
        }
      },
      checkCell: function() {
        var _rst = /^(13[0-9]{9})|(14[0-9]{9})|(15[0-9]{9})|(17[0-9]{9})|(18[0-9]{9})$/.test(this.cell.trim());
        if (!_rst) BASE.showAlert('请填写有效的手机号码');
        return _rst;
      },
      checkTel: function() {
        var _rst = /^((0\d{2,3})-)(\d{7,8})(-(\d{3,}))?$/.test(this.tel.trim());
        if (!_rst && this.tel.trim() != '') BASE.showAlert('请填写有效的座机号码');
        return _rst;
      },
      bannerChange: function(idx) {
        if (ISADD) return false;
        $('#laiaForm').ajaxSubmit({
          url: BANNER_API,
          data: {
            id: ID,
            num: idx
          },
          success: function(rst, status) {
            if (status == 'success') {
              if (typeof rst != 'object') rst = JSON.parse(rst);
              if (rst.status == '1') {
                BASE.showAlert('修改成功！');
              } else {
                BASE.showAlert(rst.msg);
              }
            } else {
              BASE.showAlert('网络有点儿问题~');
            }
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
  });
CNT.init();
$(function() {
  var
    resetBanner = function(id) {
      $('#banner' + id).css({
        backgroundImage: 'url(' + $('#bannerView' + id).attr('src') + ')',
        backgroundSize: 'cover',
        border: '1px solid #eee'
      });
    },
    bannerI = new uploadPreview({
      UpBtn: 'bannerIptI',
      ImgShow: 'bannerViewI',
      ImgType: ['jpg', 'png'],
      ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种',
      callback: function() {
        resetBanner('I');
      }
    }),
    bannerII = new uploadPreview({
      UpBtn: 'bannerIptII',
      ImgShow: 'bannerViewII',
      ImgType: ['jpg', 'png'],
      ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种',
      callback: function() {
        resetBanner('II');
      }
    }),
    bannerIII = new uploadPreview({
      UpBtn: 'bannerIptIII',
      ImgShow: 'bannerViewIII',
      ImgType: ['jpg', 'png'],
      ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种',
      callback: function() {
        resetBanner('III');
      }
    }),
    map = new uploadPreview({
      UpBtn: 'mapIpt',
      ImgShow: 'mapView',
      ImgType: ['jpg', 'png'],
      ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种',
      callback: function() {
        $('#map').css({
          backgroundImage: 'url(' + $('#mapView').attr('src') + ')',
          backgroundSize: 'cover',
          border: '1px solid #eee'
        });
      }
    }),
    logo = new uploadPreview({
      UpBtn: 'logoIpt',
      ImgShow: 'logoView',
      ImgType: ['jpg', 'png'],
      ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种',
      callback: function() {
        $('#logo').css({
          backgroundImage: 'url(' + $('#logoView').attr('src') + ')',
          backgroundSize: 'cover',
          border: '1px solid #eee'
        });
      }
    });
  $('#saveBtn').click(function() {
    var
      _api = ISADD ? ADD_API : EDIT_API,
      _tag = function() {
        var _arr = [];
        if ($('#tagPcr').selectpicker('val') && $('#tagPcr').selectpicker('val').length > 0) {
          CNT.tagArr.forEach(function(obj, idx) {
            if ($('#tagPcr').selectpicker('val').indexOf(obj.id) > -1) {
              _arr.push(obj.lableName);
            }
          });
          return _arr.join(',');
        } else {
          return '';
        }
      }();
    if ('' == TRIM(CNT.name)) {
      BASE.showAlert('请填写商家名称');
      return !1;
    } else if ('' == TRIM(CNT.remark)) {
      BASE.showAlert('请填写商家简介');
      return !1;
    } else if ('' == TRIM(CNT.address) || [CNT.adrPidx, CNT.adrCidx, CNT.adrAidx].indexOf(-1) > -1) {
      BASE.showAlert('请完善商家地址');
      return !1;
    } else if ('' == TRIM(CNT.price)) {
      BASE.showAlert('请填写人均消费');
      return !1;
    }

    if (ISADD) {
      if ('' == TRIM($('#logoIpt').val())) {
        BASE.showAlert('请上传Logo');
        return !1;
      } else if ('' == TRIM($('#mapIpt').val())) {
        BASE.showAlert('请上传位置图片');
        return !1;
      } else if ('' == TRIM($('#bannerIptI').val()) || '' == TRIM($('#bannerIptII').val()) || '' == TRIM($('#bannerIptIII').val())) {
        BASE.showAlert('请上传三张封面图');
        return !1;
      }
    }

    if (!CNT.checkCell()) return !1;

    $('#laiaForm').ajaxSubmit({
      url: _api,
      data: {
        id: ID,
        name: CNT.name,
        remark: CNT.remark,
        address: CNT.adrP + '-' + CNT.adrC + '-' + CNT.adrA + '-' + CNT.address,
        tel: CNT.cell,
        tel1:CNT.tel,
        type: CNT.type,
        style: CNT.style,
        lable: _tag,
        averPrice: CNT.price,
        contactPerson: CNT.linkman
      },
      beforeSubmit: function(a, f, o) {
        console.log('XXX');
      },
      success: function(rst, status) {
        if (status == 'success') {
          if (typeof rst != 'object') rst = JSON.parse(rst);
          if (rst.status == '1') {
            if (ISADD) {
              BASE.showConfirm('新增商家成功！<br>返回商家列表？', function() {
                window.location.href = 'business.html';
              });
            } else {
              BASE.showAlert('保存成功！');
            }
          } else {
            BASE.showAlert(rst.msg);
          }
        } else {
          BASE.showAlert('网络有点儿问题~');
        }
      }
    });
  });
  $('#business').addClass('open');
  $('#business').parents('.dropdown').addClass('open');
});