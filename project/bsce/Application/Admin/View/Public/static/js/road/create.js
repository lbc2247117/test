var
  GET_PLACES_API = '../scemap/selectScemap',
  GET_BIZ_API = '../business/QueryAllbuss',
  ADD_API = '../Roadnew/addRoad',
  CNT = new Vue({
    el: '#pageWrapper',
    data: {
      roadTitle: '',
      roadDsc: '',
      roadPer: '',
      bannerSrc: '',
      coverSrc: '',
      dayArr: [{}],
      editDay: 0,
      editObj: {},
      addCnr: '',
      arrFood: [],
      arrHotel: [],
      arrPlace: [],
      checkedFood: [],
      checkedHotel: [],
      checkedPlace: [],
      checkedPlaceCur: [],
      bizSearch: '',
      bizStyle: -1,
      trafficTitle: '',
      trafficDsc: '',
      editTrafficIdx: -1,
      editFoodIdx: -1,
      editHotelIdx: -1
    },
    methods: {
      init: function() {
        this.initEditObj();
        this.dayArr[0] = this.editObj;
        this.getData();
      },
      initEditObj: function() {
        this.checkedFood = [];
        this.checkedHotel = [];
        this.checkedPlace = [];
        this.checkedPlaceCur = [];
        this.editObj = {
          name: '',
          remark: '',
          info: [],
          places: []
        }
      },
      ajaxFn: function(api, data, cb, failMsg, errorMsg) {
        $.post(api, data, function(rst, status) {
          if (status == 'success') {
            if (typeof rst != 'object') rst = JSON.parse(rst);
            if (rst.status == '1') {
              if (typeof cb == 'function') cb(rst);
            } else {
              BASE.showAlert(!!failMsg ? failMsg : '操作失败~');
            }
          } else {
            BASE.showAlert(!!errorMsg ? errorMsg : '网络有点儿问题~<br>稍后再试吧~');
          }
        });
      },
      getData: function() {
        this.getPlace();
        this.getFood();
        this.getHotel();
      },
      getPlace: function() {
        this.ajaxFn(
          GET_PLACES_API, {
            page: 1,
            size: 10000,
            serchName: this.bizSearch
          },
          function(rst) {
            CNT.arrPlace = rst.data.data;
          }
        );
      },
      getFood: function() {
        this.ajaxFn(
          GET_BIZ_API, {
            page: 1,
            size: 10000,
            CommercialTenantType: 0,
            CommercialTenantStyle: this.bizStyle,
            frameState: 0,
            serchName: this.bizSearch
          },
          function(rst) {
            CNT.arrFood = rst.data.data;
          }
        );
      },
      getHotel: function() {
        this.ajaxFn(
          GET_BIZ_API, {
            page: 1,
            size: 10000,
            CommercialTenantType: 1,
            CommercialTenantStyle: this.bizStyle,
            frameState: 0,
            serchName: this.bizSearch
          },
          function(rst) {
            CNT.arrHotel = rst.data.data;
          }
        );
      },
      editTraffic: function(idx) {
        this.editTrafficIdx = idx;
        this.trafficTitle = this.editObj.info[this.editTrafficIdx].name;
        this.trafficDsc = this.editObj.info[this.editTrafficIdx].trafficInformation;
        this.addCnr = 'traffic';
      },
      editFood: function(idx) {
        this.editFoodIdx = idx;
        this.checkedFood = this.editObj.info[idx].foods.join(',').split(',');
        this.addCnr = 'food';
      },
      editHotel: function(idx) {
        this.editHotelIdx = idx;
        this.checkedHotel = this.editObj.info[idx].hotels.join(',').split(',');
        this.addCnr = 'hotel';
      },
      confirmTraffic: function() {
        if (TRIM(this.trafficTitle) && TRIM(this.trafficDsc)) {
          if (this.editTrafficIdx > -1) {
            this.editObj.info[this.editTrafficIdx].name = this.trafficTitle;
            this.editObj.info[this.editTrafficIdx].trafficInformation = this.trafficDsc;
          } else {
            this.editObj.info.push({
              type: 4,
              name: this.trafficTitle,
              trafficInformation: this.trafficDsc
            });
          }
        } else {

        }
        this.editTrafficIdx = -1;
        this.trafficTitle = '';
        this.trafficDsc = '';
        this.addCnr = '';
      },
      confirmPlace: function() {
        this.checkedPlaceCur.forEach(function(obj) {
          var _obj = function(id) {
            var _o = {};
            CNT.arrPlace.forEach(function(oobj) {
              if (id == oobj.id) {
                _o = oobj;
                return;
              }
            });
            return _o;
          }(obj);
          CNT.editObj.info.push({
            type: 5,
            sceMapID: obj,
            name: _obj.name,
            trafficInformation: '',
            recommendedReason: ''
          });
          CNT.checkedPlace.push(obj);
        });
        this.editObj.places = this.checkedPlace;
        this.checkedPlaceCur = [];
        this.addCnr = '';
      },
      confirmFood: function() {
        var
          _obj = {
            type: 2,
            tag: '',
            sellers: [],
            foods: []
          };
        this.checkedFood.forEach(function(obj) {
          if (CNT.editFoodIdx < 0 || CNT.editObj.info[CNT.editFoodIdx].foods.indexOf(obj) < 0) {
            var
              _oi = function(id) {
                var __o = {};
                CNT.arrFood.forEach(function(oobj) {
                  if (id == oobj.id) {
                    __o = oobj;
                    return;
                  }
                });
                return __o;
              }(obj),
              _oii = {
                resourceID: obj,
                name: _oi.name,
                trafficInformation: '',
                recommendedReason: ''
              };
            if (CNT.editFoodIdx > -1) {
              CNT.editObj.info[CNT.editFoodIdx].sellers.push(_oii);
              CNT.editObj.info[CNT.editFoodIdx].foods.push(obj);
            } else {
              _obj.sellers.push(_oii);
              _obj.foods.push(obj);
            }
          }
        });
        if (this.editFoodIdx < 0) this.editObj.info.push(_obj);
        this.addCnr = '';
        this.checkedFood = [];
      },
      confirmHotel: function() {
        var
          _obj = {
            type: 6,
            tag: '',
            sellers: [],
            hotels: []
          };
        this.checkedHotel.forEach(function(obj) {
          if (CNT.editHotelIdx < 0 || CNT.editObj.info[CNT.editHotelIdx].hotels.indexOf(obj) < 0) {
            var
              _oi = function(id) {
                var __o = {};
                CNT.arrHotel.forEach(function(oobj) {
                  if (id == oobj.id) {
                    __o = oobj;
                    return;
                  }
                });
                return __o;
              }(obj),
              _oii = {
                resourceID: obj,
                name: _oi.name,
                trafficInformation: '',
                recommendedReason: ''
              };
            if (CNT.editHotelIdx > -1) {
              CNT.editObj.info[CNT.editHotelIdx].sellers.push(_oii);
              CNT.editObj.info[CNT.editHotelIdx].hotels.push(obj);
            } else {
              _obj.sellers.push(_oii);
              _obj.hotels.push(obj);
            }
          }
        });
        if (this.editHotelIdx < 0) this.editObj.info.push(_obj);
        this.addCnr = '';
        this.checkedHotel = [];
      },
      daySlt: function(idx) {
        if (!this.editObj.name) {
          BASE.showAlert('请填写行程标题！');
          return !1;
        }
        this.dayArr[this.editDay] = this.editObj;
        this.editDay = idx;
        this.editObj = this.dayArr[idx];
        this.checkedPlace = this.editObj.places;
        this.checkedFood = [];
        this.checkedHotel = [];
      },
      dayAdd: function() {
        if (!this.editObj.name) {
          BASE.showAlert('请填写行程标题！');
          return !1;
        }
        this.dayArr.push({
          id: '',
          name: '',
          remark: '',
          info: [],
          places: []
        });
        this.daySlt(this.dayArr.length - 1);
      },
      dayRm: function(idx) {
        this.dayArr.removeByIndex(idx);
        if (idx < this.editDay) this.editDay -= 1;
      },
      dayUp: function(idx) {
        this.dayArr.up(idx);
        if (idx == this.editDay + 1) this.editDay += 1;
      },
      dayDown: function(idx) {
        this.dayArr.down(idx);
        if (idx == this.editDay - 1) this.editDay -= 1;
      },
      sglRm: function(idx, type) {
        if (type == 'place') this.editObj.places.remove(this.editObj.info[idx].sceMapID);
        this.editObj.info.removeByIndex(idx);
      },
      sglUp: function(idx) {
        this.editObj.info.up(idx);
      },
      sglDown: function(idx) {
        this.editObj.info.down(idx);
      },
      sglBizRm: function(idx, iidx, type) {
        var _id = this.editObj.info[idx].sellers[iidx].resourceID;
        if (type == 'food') {
          this.editObj.info[idx].foods.remove(_id);
          this.checkedFood.remove(_id);
        }
        if (type == 'hotel') {
          this.editObj.info[idx].hotels.remove(_id);
          this.checkedHotel.remove(_id);
        }
        this.editObj.info[idx].sellers.removeByIndex(iidx);
      },
      sglBizUp: function(idx, iidx) {
        this.editObj.info[idx].sellers.up(iidx);
      },
      sglBizDown: function(idx, iidx) {
        this.editObj.info[idx].sellers.down(iidx);
      },
      roadPerChange: function() {
        if (parseInt(this.roadPer) == '' || parseInt(this.roadPer) == 0 || parseInt(this.roadPer) < 0) {
          this.roadPer = 0;
          BASE.showAlert('推荐数应为0到100之间的整数');
        }
        if (parseInt(this.roadPer) > 100) {
          this.roadPer = 100;
          BASE.showAlert('推荐数应为0到100之间的整数');
        }
        this.roadPer = parseInt(this.roadPer);
      },
      changeBanner: function() {
        $('#uploadBanner').click();
      },
      changeCover: function() {
        $('#uploadCover').click();
      },
      listenBiz: function() {
        if (this.bizChecked.length > 3) this.bizChecked.removeByIndex(0);
      },
      createLine: function() {
        var _str = '';
        this.dayArr.forEach(function(obj, idx) {
          _str += 'Day' + String(idx + 1) + '：';
          if (typeof CNT.dayArr[idx].info == 'object') {
            CNT.dayArr[idx].info.forEach(function(oobj, iidx) {
              if (oobj.type == '5') {
                _str += oobj.name + '-';
              }
            });
          }
          _str += ';';
        });
        return encodeURI(_str);
      },
      getRoadJson: function() {
        this.dayArr[this.editDay] = this.editObj;
        var _arr = [];
        this.dayArr.forEach(function(obj, idx) {
          var __obj = {};
          __obj.name = obj.name;
          __obj.remark = obj.remark;
          __obj.info = obj.info;
          _arr[idx] = __obj;
        });

        return encodeURI(JSON.stringify(_arr));
      },
      addData: function(type) {
        this.addCnr = type;
      }
    }
  });

$(function() {
  CNT.init();
  var
    bannerView = new uploadPreview({
      UpBtn: 'uploadBanner',
      ImgShow: 'bannerView',
      ImgType: ['jpg', 'png'],
      ErrMsg: '选择文件错误,图片类型必须是(jpg,png)中的一种',
      callback: function() {
        CNT.bannerSrc = $('#bannerView').attr('src');
      }
    }),
    coverView = new uploadPreview({
      UpBtn: 'uploadCover',
      ImgShow: 'coverView',
      ImgType: ['jpg', 'png'],
      ErrMsg: '选择文件错误,图片类型必须是(jpg,png)中的一种',
      callback: function() {
        CNT.coverSrc = $('#coverView').attr('src');
      }
    });
  $('#saveBtn').click(function() {
    if ('' == TRIM(CNT.bannerSrc)) {
      BASE.showAlert('请先上传行程推荐封面');
      return !1;
    } else if ('' == TRIM(CNT.coverSrc)) {
      BASE.showAlert('请先上传行程展示图');
      return !1;
    } else if ('' == TRIM(CNT.roadTitle)) {
      BASE.showAlert('请填写行程标题');
      return !1;
    } else if ('' == TRIM(CNT.roadDsc)) {
      BASE.showAlert('请填写行程描述');
      return !1;
    } else if ('' == TRIM(CNT.roadPer)) {
      BASE.showAlert('请填写行程推荐百分比');
      return !1;
    }
    if (typeof CNT.dayArr[0].info == 'object' && CNT.dayArr[0].info.length < 1) {
      BASE.showAlert('请填加行程内容');
      return !1;
    }
    var _roadJson = CNT.getRoadJson();
    $('#laiaForm').ajaxSubmit({
      url: ADD_API,
      data: {
        routeWay: CNT.createLine(),
        percentNum: CNT.roadPer,
        infoSpot: _roadJson,
        state: 0
      },
      success: function(rst, status) {
        if (status == 'success') {
          if (typeof rst != 'object') rst = JSON.parse(rst);
          if (rst.status == '1') {
            BASE.showConfirm('保存成功啦~<br>将跳转到列表页', function() {
              window.location.href = 'road.html';
            });
          } else {
            BASE.showAlert(rst.msg);
          }
        } else {
          BASE.showAlert('保存失败~');
        }
      }
    });
  });
});