var
GET_DATA_API = '',
LAIA = new Vue({
  el: '#laiaCnr',
  data: {
    id: SEARCH_ID,
    headObj: {
      cover: '',
      src: '',
      title: '',
      time: '',
      count: ''
    },
    cmtArr: []
  },
  methods: {
    init: function() {
      //this.getData();
      this.listData();
    },
    getData: function() {
      var _dt = {
        searchVal: this.searchKey
      };
      $.post(GET_DATA_API, _dt, function(rst, status) {
        if (status == 'success') {
          if (typeof rst != 'object') rst = $.parseJSON(rst);
          if (rst.status == '1' && rst.data) {
            LAIA.listData(rst.data);
          } else {
            BASE.showAlert(rst.msg);
          }
        } else {
          BASE.showConfirm('网络有点儿问题');
        }
      });
    },
    listData: function(data) {
      //this.dataObj = data;
    },
    bgImg: function(url) {
      return url ? ('url(' + url + ')') : '';
    },
    toalbum: function(id) {
      window.location.href = 'album?id=' + id;
    },
    toggle: function(e) {
      if (e.currentTarget.className.indexOf('active') > -1) {
        BASE.removeClass(e, 'active');
      } else {
        BASE.addClass(e, 'active');
      }
    }
  }
});
$(function () {
  LAIA.init();
});