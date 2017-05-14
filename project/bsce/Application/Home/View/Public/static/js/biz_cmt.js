var
  GET_DATA_API = '../Near/getCommentlist?lon=' + SEARCH_LON + '&lat=' + SEARCH_LAT,
  CMT_API = '../Near/publishComment?lon=' + SEARCH_LON + '&lat=' + SEARCH_LAT,
  LAIA = new Vue({
    el: '#laiaCnr',
    data: {
      scores: 5,
      cmt: [],
      cmtIdx: 0,
      cmtTag: '',
      success: !1,
      error: !1
    },
    methods: {
      init: function() {
        this.getData();
      },
      getData: function() {
        $.post(GET_DATA_API, '', function(rst, status) {
          if (status == 'success') {
            if (typeof rst != 'object') rst = $.parseJSON(rst);
            if (rst.status == '1') {
              LAIA.cmt = rst.data.data;
              LAIA.aim(5);
            } else {
              BASE.showAlert(rst.msg);
            }
          } else {
            BASE.showConfirm('网络有点儿问题');
          }
        });
      },
      aim: function(num) {
        this.scores = num;
        this.cmt.forEach(function(obj, idx) {
          if (obj.star == num) LAIA.cmtIdx = idx;
        });
      },
      toggle: function(e) {
        if (e.currentTarget.className.indexOf('active') > -1) {
          BASE.removeClass(e, 'active');
        } else {
          BASE.addClass(e, 'active');
        }
      },
      toBiz: function() {
        window.location.href = 'biz.html?id=' + SEARCH_ID + '&lon=' + SEARCH_LON + '&lat=' + SEARCH_LAT + '&cate=' + URL_PARAM('cate');
      },
      toCmt: function() {
        this.error = !1;
      },
      cmtConfirm: function() {
        var _arr = [];
        $('.sgl-cmt-tag.active').each(function(idx, obj) {
          _arr.push(obj.innerHTML);
        });
        $.post(CMT_API, {
          id: SEARCH_ID,
          content: LAIA.cmtTag,
          //content: _arr.join(','),
          contentXj: this.cmt[this.cmtIdx].content,
          star: this.cmt[this.cmtIdx].star
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
  LAIA.init();
});