var
  GET_DATA_API = 'weatherDesc',
  LAIA = new Vue({
    el: '#laiaCnr',
    data: {
      id: SEARCH_ID,
      dataObj: {
        type: '',
        lon: '',
        lat: '',
        cover: URL_PARAM('bg'),
        curtemp: '',
        wind: '',
        windlevel: '',
        cloth: '',
        weather: '',
        temp: '',
        futurearr: []
      }
    },
    methods: {
      init: function() {
        this.lon = SEARCH_LON;
        this.lat = SEARCH_LAT;
        this.getData();
      },
      getData: function() {
        var _dt = {
          lon: this.lon,
          lat: this.lat
        };
        $.post(GET_DATA_API, _dt, function(rst, status) {
          if (status == 'success') {
            if (typeof rst != 'object')
              rst = $.parseJSON(rst);
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
        this.dataObj.curtemp = data.today.curTemp;
        this.dataObj.wind = data.today.fengxiang;
        this.dataObj.windlevel = data.today.fengli;
        this.dataObj.weather = data.today.weatherPic;
        this.dataObj.cloth = data.today.cy.details;
        this.dataObj.type = data.today.type;
        this.dataObj.temp = data.today.lowtemp + '-' + data.today.hightemp;
        this.dataObj.futurearr = data.forecast;

        /*setTimeout(function () {
          var bodyHeight=document.body.scrollHeight-50;
          var middleCnrHeight=parseInt($('#bottomCnr').css('height')) ;
          var topHead=(bodyHeight-middleCnrHeight-180)/2;
          $('#middleCnr').css('margin-top',topHead);
        }, 200);*/

      },
      bgImg: function(url) {
        return url ? ('url(' + url + ')') : '';
      },
      //weatherBg: function(weather) {
      //  var
      //  _d = new Date(),
      //  _h = _d.getHours(),
      //  _b = (_h < 18 && _h > 5) ? 'day/' : 'night/',
      //  _u;
      //  switch (weather) {
      //  case 'sunny':
      //    _u = '00.png';
      //    break;
      //  case 'windy':
      //    _u = '01.png';
      //    break;
      //  case 'cloudy':
      //    _u = '02.png';
      //    break;
      //  default:
      //    _u = '00.png';
      //  }
      //  return _b + _u;
      //},
      getZhcn: function(en) {
        var
          _cn = '';
        switch (en) {
          case 'sunny':
            _cn = '晴';
            break;
          case 'cloudy':
            _cn = '多云';
            break;
          default:
            _cn = '晴';
        }
        return _cn;
      }
    }
  });

$(function() {


  LAIA.init();

});