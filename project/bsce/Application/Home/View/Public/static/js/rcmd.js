var
  GET_DATA_API = '../Recommend/roadInfo',
  LAIA = new Vue({
    el: '#laiaCnr',
    data: {
      id: SEARCH_ID,
      lon: SEARCH_LON,
      lat: SEARCH_LAT,
      rcmd: {},
      dayArr: [],
      title: '',
      dsc: '',
      banner: '',
      roadLine: '',
      urlFix: ''
    },
    methods: {
      init: function() {
        //this.getData();
        this.getData();
      },
      getData: function() {
        $.post(GET_DATA_API, {
          id: SEARCH_ID,
          lon: SEARCH_LON,
          lat: SEARCH_LAT
        }, function(rst, status) {
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
        this.rcmd = data.rcmd;
        this.title = data.name;
        this.dsc = data.remark;
        this.banner = data.url;
        this.urlFix = data.imgpre;
        this.roadLine = data.routeWay.replace(/-;/g, '<br>');
        data.ScenicSpotWayDayVo.forEach(function(obj) {
          var
            _o = {
              id: '',
              name: '',
              remark: '',
              info: []
            };
          _o.id = obj.id;
          _o.name = obj.name;
          _o.remark = obj.todayMemorandum;
          obj.ScenicSpotWayLableAttrVo.forEach(function(_obj) {
            switch (String(_obj.lableType)) {
              case '0':
                _o.info.push({
                  type: 4,
                  pid: _obj.id,
                  id: _obj.ScenicSpotWayAttrVo[0].id,
                  name: _obj.ScenicSpotWayAttrVo[0].name,
                  trafficInformation: _obj.ScenicSpotWayAttrVo[0].trafficInformation
                });
                break;
              case '1':
                _o.info.push({
                  type: 5,
                  pid: _obj.id,
                  id: _obj.ScenicSpotWayAttrVo[0].id,
                  sceMapID: _obj.ScenicSpotWayAttrVo[0].resourceMap.id,
                  resource: _obj.ScenicSpotWayAttrVo[0].resourceMap,
                  name: _obj.ScenicSpotWayAttrVo[0].resourceMap.name,
                  trafficInformation: _obj.ScenicSpotWayAttrVo[0].trafficInformation,
                  recommendedReason: _obj.ScenicSpotWayAttrVo[0].recommendedReason
                });
                break;
              case '2':
                var __o = {
                  type: 2,
                  pid: _obj.id,
                  id: _obj.id,
                  tag: _obj.lableInfor,
                  name: '推荐美食',
                  sellers: [],
                  foods: []
                };
                _obj.ScenicSpotWayAttrVo.forEach(function(__obj) {
                  __o.foods.push(__obj.resourceID);
                  var _r = __obj.resourceMap;
                  _r.tagArr = !!_r.commercialTenantLableID.trim() ? _r.commercialTenantLableID.trim().split(',') : [];
                  __o.sellers.push({
                    id: __obj.id,
                    resourceID: _r.id,
                    resource: _r,
                    name: _r.name,
                    trafficInformation: __obj.trafficInformation,
                    recommendedReason: __obj.recommendedReason
                  });
                });
                _o.info.push(__o);
                break;
              case '3':
                var __o = {
                  type: 6,
                  pid: _obj.id,
                  id: _obj.id,
                  tag: '',
                  name: '推荐住宿',
                  sellers: [],
                  hotels: []
                };
                _obj.ScenicSpotWayAttrVo.forEach(function(__obj) {
                  __o.hotels.push(__obj.resourceID);
                  var _r = __obj.resourceMap;
                  _r.tagArr = !!_r.commercialTenantLableID.trim() ? _r.commercialTenantLableID.trim().split(',') : [];
                  __o.sellers.push({
                    id: __obj.id,
                    resourceID: _r.id,
                    resource: _r,
                    name: _r.name,
                    trafficInformation: __obj.trafficInformation,
                    recommendedReason: __obj.recommendedReason
                  });
                });
                _o.info.push(__o);
                break;
            }
          });
          LAIA.dayArr.push(_o);
        });
        console.log(this.dayArr);
      },
      jumpToplace: function(id, lon, lat) {
        window.location.href = 'place.html?lon=' + this.lon + '&lat=' + this.lat + '&maplat=' + lat + '&maplon=' + lon + '&id=' + id;
      },
      jumpToBiz: function(id) {
        window.location.href = 'biz.html?id=' + id + '&lon=' + this.lon + '&lat=' + this.lat;
      },
      jupmToRcmd_list: function() {
        window.location.href = 'rcmd_list.html?lon=' + this.lon + '&lat=' + this.lat;
      },
      jumpToRcmd: function(id) {
        window.location.href = 'rcmd.html?id=' + id + '&lon=' + this.lon + '&lat=' + this.lat;
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
      gotomap:function(id){
        $("html,body").animate({scrollTop:$("#"+id).offset().top-40},1000);
      },
      showList: function() {
        $('#listCnr').show();
      },
      hideList: function() {
        $('#listCnr').hide();
      }
    }
  });

$(function() {
  if (!SEARCH_INAPP) {
    $('#laiaCnr').css('marginTop', '40px');
    $('#navBack').off('click');
    $('#navBack').on('click', function() {
      var _search = '?lon=' + SEARCH_LON + '&lat=' + SEARCH_LAT;
      window.location.href = 'rcmd_list.html' + _search;
    });
  }
  LAIA.init();
});