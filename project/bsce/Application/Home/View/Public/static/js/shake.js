var
API = 'noddingRes',
lon = SEARCH_LON,
lat = SEARCH_LAT,
TIMER,
TICKET_ID;
$(function() {
  var 
  shakeAudio = new Audio(),
  shakeThreshold = 3000,
  lastUpdate = curX = curY = curZ = lastX = lastY = lastZ = 0,
  getData = function() {
    clearTimeout(TIMER);
    TIMER = setTimeout(function () {
      var _dt = {
        lon: this.lon,
        lat: this.lat
      };
      $.post(API, _dt, function(rst, status) {
        if (status == 'success') {
          if (typeof rst != 'object') rst = $.parseJSON(rst);
          if (rst.status == '1' ) {
            if( rst.data){
              $('#cat').removeClass('shake');
              $('#shadow').removeClass('move');
              $('#biu').show();//中奖
              //$('#biz').text(rst.data.seller.name);
              $('#voucherName').text(rst.data.voucherName);
              $('#zk').text(rst.data.zk);
              $('#img').css('backgroundImage','url('+rst.data.picUrl+')');
              TICKET_ID = rst.data.id;//中奖的奖券ID
            }else{
              $('#cat').removeClass('shake');
              $('#shadow').removeClass('move');
              $('#woo').show();//未中奖
            }
          } else {
            BASE.showAlert(rst.msg);
          }
        } else {
          BASE.showConfirm('网络有点儿问题');
        }
      })
    }, 5000);
  },
  deviceMotionHandler = function(e) {
    var 
    _acceleration = e.accelerationIncludingGravity,
    _curTime = new Date().getTime(),
    _speed,
    _diffTime;
    if ((_curTime - lastUpdate)> 100) {
      _diffTime = _curTime - lastUpdate;
      lastUpdate = _curTime;
      curX = _acceleration.x;
      curY = _acceleration.y;
      curZ = _acceleration.z;
      _speed = parseInt(Math.abs(curX + curY + curZ - lastX - lastY - lastZ).toFixed(4) / _diffTime * 10000);
      if (_speed > shakeThreshold) {
        $('#biu').hide();
        $('#woo').hide();
        $('#biz').text('');
        $('#voucherName').text('');
        $('#zk').text('');
        $('#img').css('backgroundImage','');
        shakeAudio.play();
        $('#cat').addClass('shake');
        $('#shadow').addClass('move');
        getData();
      }
    }
    lastX = curX;
    lastY = curY;
    lastZ = curZ;
  };
  shakeAudio.src = '/bsce/Application/Home/View/Public/static/shake.mp3';
  shakeAudio.setAttribute('preload','auto');
  $('#mainTopCnt,#mainMiddle,#mainBottom').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    window.location.href = 'coupon?id=' + TICKET_ID + '&fromshake=1'+'&lon='+URL_PARAM('lon')+'&lat='+URL_PARAM('lat');
    //window.location.href = 'coupon?id=' + TICKET_ID + '&lon='+URL_PARAM('lon')+'&lat='+URL_PARAM('lat');
  });
  $('#biu').on('click', function(e) {
    $('#biu').hide();
  });
  $('#woo').on('click', function(e) {
    $('#woo').hide();
  });
  $('#back').on('click', function(e) {
    window.location.href = 'sce.html?lon=' + SEARCH_LON + '&lat=' + SEARCH_LAT;
  });
  if (window.DeviceMotionEvent) {
    window.addEventListener('devicemotion', deviceMotionHandler, false);
  } else {
    BASE.showConfirm('您的手机不支持摇一摇诶～');
  }
});