var
SEARCH_TYPE = URL_PARAM('type'),
SEARCH_KEY = URL_PARAM('key'),
GET_DATA_API = SEARCH_TYPE == '4' ? '../near/searchMap' : '../near/searchCom',
LAIA = new Vue({
  el: '#laiaCnr',
  data: {
    isPlace: SEARCH_TYPE == '4',
    searchType: SEARCH_TYPE,
    dataArr: [],
    pullData: [],
    page: 1,
    first: 1
  },
  methods: {
    init: function () {
      this.first = 1;
      this.page = 1;
      this.getData();
    },
    getData: function () {
      var _dt = {
        searchName: SEARCH_KEY,
        lon: SEARCH_LON,
        lat: SEARCH_LAT,
        page: this.page
      };
      if (!this.isPlace) {
        _dt.type = SEARCH_TYPE;
      }
      $.post(GET_DATA_API, _dt, function (rst, status) {
        if (status == 'success') {
          rst = JSON.parse(rst);
          if (rst.status == '1') {
            if (LAIA.first) {
              LAIA.dataArr = rst.data;
            } else {
              LAIA.pullData = rst.data;
              if (rst.data == null || rst.data == '') {
                LAIA.pullData = null;
                LAIA.page = LAIA.page - 1;
              } else {
                rst.data.forEach(function(obj) {
                  LAIA.dataArr.push(obj);
                });
              }
            }
            setTimeout(function () {
              LAIA_SCROLL.refresh();
            }, 200);
          } else {
            BASE.showConfirm(rst.msg);
          }
        } else {
          BASE.showConfirm('网络有点儿问题');
        }
      });
    },
    bgImg: function (url) {
      return url ? ('url(' + url + ')') : '';
    },
    toBiz: function (id) {
      window.location.href = 'biz.html?id=' + id + '&lat=' + URL_PARAM('lat') + '&lon=' + URL_PARAM('lon');
    },
    toPlace: function (lat, lon) {
      window.location.href = 'place.html?lat=' + lat + '&lon=' + lon;
    }
  }
});

PULL_DOWN_FN = function () {
  clearTimeout(PULL_DOWN_TIMER);
  PULL_DOWN_TIMER = setTimeout(function () {
    LAIA.init();
  }, 1000);
};
PULL_UP_FN = function () {
  clearTimeout(PULL_UP_TIMER);
  PULL_UP_TIMER = setTimeout(function () {
    LAIA.first = 0;
    LAIA.page += 1;
    LAIA.getData();
  }, 1000);
};
LOADED_FN = function () {
  PULL_DOWN_EL = document.getElementById('pullDown');
  PULL_DOWN_OFFSET = PULL_DOWN_EL.offsetHeight;
  PULL_UP_EL = document.getElementById('pullUp');
  PULL_UP_OFFSET = PULL_UP_EL.offsetHeight;

  LAIA_SCROLL = new iScroll('laiaCnr', {
    useTransition: false,
    topOffset: PULL_DOWN_OFFSET,
    onRefresh: function () {
      if (PULL_DOWN_EL.className.match('loading')) {
        PULL_DOWN_EL.className = '';
        PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '下拉刷新';
        PULL_UP_EL.querySelector('.pull-up-label').innerHTML = '上拉加载更多';
      } else if (PULL_UP_EL.className.match('loading')) {
        PULL_UP_EL.className = '';
        if (LAIA.pullData == null) {
          PULL_UP_EL.querySelector('.pull-up-label').innerHTML = '没有更多内容';
        }
        else {
          PULL_UP_EL.querySelector('.pull-up-label').innerHTML = '上拉加载更多';
        }
      }
    },
    onScrollMove: function () {
      if (this.y > 5 && !PULL_DOWN_EL.className.match('flip')) {
        PULL_DOWN_EL.className = 'flip';
        PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '释放更新';
        this.minScrollY = 0;
      } else if (this.y < 5 && PULL_DOWN_EL.className.match('flip')) {
        PULL_DOWN_EL.className = '';
        PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '下拉刷新';
        this.minScrollY = -PULL_DOWN_OFFSET;
      } else if (this.y < (this.maxScrollY - 5) && !PULL_UP_EL.className.match('flip')) {
        PULL_UP_EL.className = 'flip';
        PULL_UP_EL.querySelector('.pull-up-label').innerHTML = '释放更新';
        this.maxScrollY = this.maxScrollY;
      } else if (this.y > (this.maxScrollY + 5) && PULL_UP_EL.className.match('flip')) {
        PULL_UP_EL.className = '';
        PULL_UP_EL.querySelector('.pull-up-label').innerHTML = '上拉加载更多';
        this.maxScrollY = PULL_UP_OFFSET;
      }
    },
    onScrollEnd: function () {
      if (PULL_DOWN_EL.className.match('flip')) {
        PULL_DOWN_EL.className = 'loading';
        PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '加载中';
        PULL_DOWN_FN();
      } else if (PULL_UP_EL.className.match('flip')) {
        PULL_UP_EL.className = 'loading';
        PULL_UP_EL.querySelector('.pull-up-label').innerHTML = '加载中';
        PULL_UP_FN();
      }
    }
  });
  setTimeout(function () {
    document.getElementById('laiaCnr').style.left = '0';
    LAIA_SCROLL.refresh();
  }, 800);
};

$(function () {
  LOADED_FN();
  LAIA.init();
});