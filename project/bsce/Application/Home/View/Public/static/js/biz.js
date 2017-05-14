var
  GET_DATA_API = '../Near/selInfo?lon=' + SEARCH_LON + '&lat=' + SEARCH_LAT,
  GET_PER_API = '../Near/sellerFree',
  GET_SHOW_API = '../Near/sellerDetail',
  LAIA = new Vue({
    el: '#laiaCnr',
    data: {
      searchKey: '',
      id: SEARCH_ID,
      pullData: [],
      banners: [],
      address: '',
      cell: '',
      tel: '',
      map: '',
      page: 1,
      size: 20,
      dsc: '',
      title: '',
      price: '',
      logo: '',
      first: 1,
      tagArr: [],
      tickets: [],
      showArr: [],
      colHtml: '',
      unique: [],
      rcmd: [],
      browser:[],
      cmt: {},
      callCnrVisible: !1
    },
    methods: {
      init: function() {
        this.getData();
        this.browser = BROWSER.versions;
        //this.getPer();
        //this.getShow();
      },
      getData: function() {
        $.post(GET_DATA_API, {
          id: SEARCH_ID
        }, function(rst, status) {
          if (status == 'success') {
            if (typeof rst != 'object')
              rst = $.parseJSON(rst);
            if (rst.flag == '1' && rst.result) {
              LAIA.listData(rst.result);
            } else {
              BASE.showAlert(rst.result);
            }
          } else {
            BASE.showConfirm('网络有点儿问题');
          }
        });
      },
      getPer: function() {
        var _dt = {
          id: this.id,
          lat: URL_PARAM('lat'),
          lon: URL_PARAM('lon'),
        };
        $.post(GET_PER_API, _dt, function(rst) {
          rst = JSON.parse(rst);
          if (rst.status != 1) {
            BASE.showConfirm(rst.msg);
          } else {
            LAIA.tickets = rst.data.data;
          }
        });
      },
      getShow: function() {
        var _dt = {
          id: this.id,
          lat: URL_PARAM('lat'),
          lon: URL_PARAM('lon'),
          page: this.page,
          size: this.size
        };
        $.post(GET_SHOW_API, _dt, function(rst) {
          rst = JSON.parse(rst);
          if (rst.status != 1) {
            BASE.showConfirm(rst.msg);
            return;
          }
          if (LAIA.first) {
            LAIA.showArr = rst.data;
          } else {
            LAIA.pullData = rst.data;
            if (rst.data == null || rst.data == '') {
              LAIA.pullData = null;
              LAIA.page = LAIA.page - 1;
            } else {
              for (var i = 0; i < rst.data.length; i++) {
                LAIA.showArr.push(rst.data[i]);
              }
            }
          }
        });
      },
      jumptoticket: function(id) {
        window.location.href = 'coupon?id=' + id + '&lat=' + URL_PARAM('lat') + '&lon=' + URL_PARAM('lon');
      },
      listData: function(data) {
        this.cell = data.tel;
        this.tel = data.tel1;
        this.banners = data.backPic;
        this.title = data.name;
        this.address = data.adress;
        this.logo = data.logo;
        this.dsc = data.remark;
        this.tickets = data.CommercialTenantVoucherVo;
        this.colHtml = data.url;
        this.price = data.averPrice;
        this.unique = data.CommercialTenantProductVo;
        this.cmt = data.commercialTenantContentVo;
        this.rcmd = data.commercialTenantVo;
        this.tagArr = !!data.commercialTenantLableID ? data.commercialTenantLableID.split(',') : [];
        this.map = data.mapUrl;
      },
      bgImg: function(url) {
        return url ? ('url(' + url + ')') : '';
      },
      toggle: function(e) {
        if (e.currentTarget.className.indexOf('active') > -1) {
          BASE.removeClass(e, 'active');
        } else {
          BASE.addClass(e, 'active');
        }
      },
      toMap: function() {
        if (!!this.map) {
          window.location.href = 'biz_map.html' + window.location.search + '&adr=' + encodeURIComponent(this.address) + '&img=' + encodeURIComponent(this.map);
        }
      },
      toCmt: function() {
        window.location.href = 'biz_cmt.html' + window.location.search;
      },
      toBook: function() {
        window.location.href = 'biz_book.html' + window.location.search + '&biz=' + encodeURIComponent(this.title);
      },
      toRcmd: function(id) {
        window.location.href = 'biz.html?id=' + id + '&lon=' + SEARCH_LON + '&lat=' + SEARCH_LAT + '&cate=' + URL_PARAM('cate');
      },
      regEnter: function(string) {
        return !!string ? string.replace(/\r\n/g,'<br>') : '';
      },
      showCallCnr: function() {
        this.callCnrVisible = !0;
      },
      hideCallCnr: function() {
        this.callCnrVisible = !1;
      }
    }
  });

$(function() {
  LAIA.init();
  /*$('#navBack').off('click');
  $('#navBack').on('click', function() {
    var _search = '?lon=' + SEARCH_LON + '&lat=' + SEARCH_LAT + '&cate=' + URL_PARAM('cate');
    window.location.href = 'nearby_other.html' + _search;
  });*/
  $('#datetimepicker').datetimepicker();
});