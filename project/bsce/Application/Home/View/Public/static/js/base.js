var
/* Scroller : instance of iScroll. */
LAIA_SCROLL,
PULL_DOWN_EL, 
PULL_DOWN_OFFSET,
PULL_DOWN_TIMER,
PULL_UP_EL, 
PULL_UP_OFFSET,
PULL_UP_TIMER,
PULL_DOWN_FN = function() {},
PULL_UP_FN = function() {},
LOADED_FN = function() {},

BROWSER = {
  versions: function () {
    var u = navigator.userAgent, app = navigator.appVersion;
    return {
      weibo: u.indexOf('weibo') > -1,
      qq: u.indexOf('QQ/') > -1,
      qqBrowser: u.indexOf('MQQBrowser') > -1,
      weChat: u.indexOf('MicroMessenger') > -1,
      trident: u.indexOf('Trident') > -1,
      presto: u.indexOf('Presto') > -1,
      webKit: u.indexOf('AppleWebKit') > -1,
      gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1,
      mobile: !!u.match(/AppleWebKit.*Mobile.*/) || !!u.match(/Windows Phone/) || !!u.match(/Android/) || !!u.match(/MQQBrowser/),
      ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/),
      android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1,
      iPhone: u.indexOf('iPhone') > -1 || u.indexOf('Mac') > -1,
      iPad: u.indexOf('iPad') > -1,
      webApp: u.indexOf('Safari') == -1
    };
  }(),
  language: (navigator.browserLanguage || navigator.language).toLowerCase()
},
/* Get params from location.search */
URL_PARAM = function(name) {
  var _reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)'), _r = window.location.search.substr(1).match(_reg);
  return !!_r ? decodeURIComponent(_r[2]) : '';
},
/* Math the length of string */
STRING_LENGTH = function(str) {
  var _realLength = 0, _len = str.length, _charCode = -1;
  for (var __i = 0; __i < _len; __i++) {
    _charCode = str.charCodeAt(__i);
    (_charCode >= 0 && _charCode <= 128) ? (_realLength += 1) : (_realLength += 2);
  }
  return _realLength;
},
SEARCH_INAPP = URL_PARAM('isshare') == '1',
SEARCH_ID = URL_PARAM('id'),
SEARCH_LAT = URL_PARAM('lat'),
SEARCH_TITLE = URL_PARAM('title'),
SEARCH_LON = URL_PARAM('lon'),
SEARCH_MAPLON = URL_PARAM('maplon'),
SEARCH_MAPLAT = URL_PARAM('maplat'),
/* Fn for the notice cnr */
BASE = new Vue({
  el: '#noticeCnr',
  data: {consoleMsg: '', alert: {msg: '', type: '', show: !1 }, confirm: {msg: '', confirm: function() {}, cancel: function() {}, show: !1 }},
  methods: {
    showAlert: function (msg, type, auto) {switch (type) {case 'success': type = 'alert-success'; break; case 'warning': type = 'alert-warning'; break; case 'danger': type = 'alert-danger'; break; default : type = ''; } this.alert = {msg: msg, type: type, show: !0 }; setTimeout(function() {BASE.hideAlert(); }, 3000); },
    hideAlert: function () {this.alert = {msg: '', type: '', show: !1 }; },
    showConfirm: function(msg, confirm, cancel) {this.confirm = {msg: msg, confirm: (typeof confirm == 'function') ? confirm : (function() {BASE.hideConfirm();}), cancel: (typeof cancel == 'function') ? cancel : (function() {BASE.hideConfirm();}), show: !0 }; },
    hideConfirm: function() {this.confirm = {msg: '', confirm: function() {}, cancel: function() {}, show: !1 }; },
    console: function(msg) { this.consoleMsg = msg; },
    toTop: function() {$('body').animate({scrollTop: '0px'}, 500); },
    bgImg: function(url) {
      return url ? ('url(' + url + ')') : '';
    },
    addClass: function(e, name) {
      e.currentTarget.className += ' ' + name;
    },
    removeClass: function(e, name) {
      if (e.currentTarget.className.match(new RegExp('(\\s|^)' + name))) {
        e.currentTarget.className = e.currentTarget.className.replace(new RegExp('(\\s|^)' + name), '');
      }
    }
  }
});
if (SEARCH_LAT && SEARCH_LON) {
  window.localStorage.setItem('laiaLon', SEARCH_LON);
  window.localStorage.setItem('laiaLat', SEARCH_LAT);
}
$(function() {
  //if (!BROWSER.versions.mobile) $('body').html('请在手机端打开');
  if (!SEARCH_INAPP) {
    var _search = '?lon=' + (!!SEARCH_LON ? SEARCH_LON : window.localStorage.getItem('laiaLon')) + '&lat=' + (!!SEARCH_LAT ? SEARCH_LAT : window.localStorage.getItem('laiaLat'));
    $('#laiaCnr').css('top', '40px');
    $('#laiaNav').css('display', 'block');
    $('#navHome').attr('href', 'sce.html' + _search);
    $('#navNear').attr('href', 'nearby_place.html' + _search);
    $('#navRcmd').attr('href', 'rcmd_list.html' + _search);
    $('#navLive').attr('href', 'video_list.html' + _search + '&videoType=1');
    $('#navBack').on('click', function() {
      window.history.go(-1);
    });
  }
});