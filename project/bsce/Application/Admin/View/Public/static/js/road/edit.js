var
  GET_DATA_API = '../Roadnew/editRoad',
  GET_PLACES_API = '../scemap/selectScemap',
  GET_BIZ_API = '../business/QueryAllbuss',
  SAVE_API = '../Roadnew/updateRoad',
  DEL_DAY_API = '../Roadnew/delDay',
  DEL_DATA_API = '../Roadnew/delLable',
  DEL_BIZ_API = '../Roadnew/delAttr',
  SORT_DAY_API = '../Roadnew/alertDayturn',
  SORT_DATA_API = '../Roadnew/alertLableturn',
  SORT_BIZ_API = '../Roadnew/alertAttrturn',
  EDIT_DAY_API = '../Roadnew/updateDay',
  EDIT_TAG_API = '../Roadnew/updateLable',
  EDIT_DATA_API = '../Roadnew/updateAttr',
  ADD_DAY_API = '../Roadnew/addDay',
  ADD_DATA_API = '../Roadnew/addLable',
  ADD_BIZ_API = '../Roadnew/addAttr',
  GET_DAY_API = '../Roadnew/getDay',
  CNT = new Vue({
    el: '#pageWrapper',
    data: {
      roadTitle: '',
      roadDsc: '',
      roadPer: '',
      bannerSrc: '',
      coverSrc: '',
      dayArr: [],
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
        this.getData();
      },
      initEditObj: function() {
        this.checkedFood = [];
        this.checkedHotel = [];
        this.checkedPlace = [];
        this.checkedPlaceCur = [];
        this.editObj = {
          id: '',
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
        this.ajaxFn(
          GET_DATA_API, {
            id: SEARCH_ID
          },
          function(rst) {
            CNT.listData(rst.data);
          }
        );
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
      getDay: function(id) {
        this.ajaxFn(
          GET_DAY_API, {
            idDay: id
          },
          function(rst) {
            CNT.listDay(rst.data);
          }
        );
      },
      listDay: function(data) {
        var _data = data.ScenicSpotWayLableAttrVo,
          _o = {
            id: '',
            name: '',
            remark: '',
            info: [],
            places: []
          };
        _o.id = data.id;
        _o.name = data.name;
        _o.remark = data.todayMemorandum;;
        _data.forEach(function(_obj) {
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
                name: _obj.ScenicSpotWayAttrVo[0].resourceMap.name,
                trafficInformation: _obj.ScenicSpotWayAttrVo[0].trafficInformation,
                recommendedReason: _obj.ScenicSpotWayAttrVo[0].recommendedReason
              });
              _o.places.push(_obj.ScenicSpotWayAttrVo[0].resourceMap.id);
              break;
            case '2':
              var __o = {
                type: 2,
                pid: _obj.id,
                id: _obj.id,
                tag: _obj.lableInfor,
                sellers: [],
                foods: []
              };
              _obj.ScenicSpotWayAttrVo.forEach(function(__obj) {
                __o.foods.push(__obj.resourceID);
                __o.sellers.push({
                  id: __obj.id,
                  resourceID: __obj.resourceMap.id,
                  name: __obj.resourceMap.name,
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
                sellers: [],
                hotels: []
              };
              _obj.ScenicSpotWayAttrVo.forEach(function(__obj) {
                __o.hotels.push(__obj.resourceID);
                __o.sellers.push({
                  id: __obj.id,
                  resourceID: __obj.resourceMap.id,
                  name: __obj.resourceMap.name,
                  trafficInformation: __obj.trafficInformation,
                  recommendedReason: __obj.recommendedReason
                });
              });
              _o.info.push(__o);
              break;
          }
        });
        this.dayArr.forEach(function(obj, idx) {
          if (obj.id == _o.id) {
            CNT.dayArr[idx] = _o;
            if (idx == CNT.editDay) CNT.editObj = _o;
            return;
          }
        });
      },
      listData: function(data) {
        var _day = data.ScenicSpotWayDayVo;
        _day.forEach(function(obj) {
          var
            _o = {
              id: '',
              name: '',
              remark: '',
              info: [],
              places: []
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
                  name: _obj.ScenicSpotWayAttrVo[0].resourceMap.name,
                  trafficInformation: _obj.ScenicSpotWayAttrVo[0].trafficInformation,
                  recommendedReason: _obj.ScenicSpotWayAttrVo[0].recommendedReason
                });
                _o.places.push(_obj.ScenicSpotWayAttrVo[0].resourceMap.id);
                break;
              case '2':
                var __o = {
                  type: 2,
                  pid: _obj.id,
                  id: _obj.id,
                  tag: _obj.lableInfor,
                  sellers: [],
                  foods: []
                };
                _obj.ScenicSpotWayAttrVo.forEach(function(__obj) {
                  __o.foods.push(__obj.resourceID);
                  __o.sellers.push({
                    id: __obj.id,
                    resourceID: __obj.resourceMap.id,
                    name: __obj.resourceMap.name,
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
                  sellers: [],
                  hotels: []
                };
                _obj.ScenicSpotWayAttrVo.forEach(function(__obj) {
                  __o.hotels.push(__obj.resourceID);
                  __o.sellers.push({
                    id: __obj.id,
                    resourceID: __obj.resourceMap.id,
                    name: __obj.resourceMap.name,
                    trafficInformation: __obj.trafficInformation,
                    recommendedReason: __obj.recommendedReason
                  });
                });
                _o.info.push(__o);
                break;
            }
          });
          CNT.dayArr.push(_o);
        });
        this.roadTitle = data.name;
        this.roadDsc = data.remark;
        this.roadPer = data.percentNum;
        this.bannerSrc = data.travelPic;
        this.coverSrc = data.url;
        this.editObj = this.dayArr[0];
        this.checkedPlace = this.editObj.places;
        BASE.initTextCount();
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
            this.changeBiz(this.editObj.info[this.editTrafficIdx]);
          } else {
            var _obj = {
              type: 4,
              name: this.trafficTitle,
              trafficInformation: this.trafficDsc
            };
            this.editObj.info.push(_obj);
            if (!!this.editObj.id) this.saveData(_obj);
          }
        } else {
          BASE.showAlert('请完整填写交通及详情～');
          return !1;
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
            }(obj),
            __oobj = {
              type: 5,
              sceMapID: obj,
              name: _obj.name,
              trafficInformation: '',
              recommendedReason: ''
            };
          CNT.checkedPlace.push(obj);
          CNT.editObj.info.push(__oobj);
          if (!!CNT.editObj.id) CNT.saveData(__oobj);
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
              if (!!CNT.editObj.id) CNT.saveBiz(_oii, CNT.editObj.info[CNT.editFoodIdx].pid, 2);
            } else {
              _obj.sellers.push(_oii);
              _obj.foods.push(obj);
            }
          }
        });
        if (this.editFoodIdx < 0) {
          this.editObj.info.push(_obj);
          if (!!this.editObj.id) this.saveData(_obj);
        }
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
              if (!!CNT.editObj.id) CNT.saveBiz(_oii, CNT.editObj.info[CNT.editHotelIdx].pid, 6);
            } else {
              _obj.sellers.push(_oii);
              _obj.hotels.push(obj);
            }
          }
        });
        if (this.editHotelIdx < 0) {
          this.editObj.info.push(_obj);
          if (!!this.editObj.id) this.saveData(_obj);
        }
        this.addCnr = '';
        this.checkedHotel = [];
      },
      daySlt: function(idx) {
        if (!this.editObj.id) this.saveDay(this.editDay);
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
        if (!!this.dayArr[idx].id) {
          this.ajaxFn(
            DEL_DAY_API, {
              idDay: this.dayArr[idx].id
            },
            function(rst) {
              CNT.dayArr.removeByIndex(idx);
              if (idx < CNT.editDay) CNT.editDay -= 1;
            }
          );
        } else {
          this.dayArr.removeByIndex(idx);
          if (idx < this.editDay) this.editDay -= 1;
        }
      },
      dayUp: function(idx) {
        if (idx < 1) return !1;
        var _cid = this.dayArr[idx].id,
          _aid = this.dayArr[idx - 1].id;
        if (!!_cid && _aid) {
          this.ajaxFn(
            SORT_DAY_API, {
              idDay1: _cid,
              idDay2: _aid
            },
            function(rst) {
              CNT.dayArr.up(idx);
              if (idx == CNT.editDay + 1) CNT.editDay += 1;
            }
          );
        } else {
          this.dayArr.up(idx);
          if (idx == this.editDay + 1) this.editDay += 1;
        }

      },
      dayDown: function(idx) {
        if (idx > this.dayArr.length - 2) return !1;
        var _cid = this.dayArr[idx].id,
          _aid = this.dayArr[idx + 1].id;
        if (!!_cid && _aid) {
          this.ajaxFn(
            SORT_DAY_API, {
              idDay1: _cid,
              idDay2: _aid
            },
            function(rst) {
              CNT.dayArr.down(idx);
              if (idx == CNT.editDay - 1) CNT.editDay -= 1;
            }
          );
        } else {
          this.dayArr.down(idx);
          if (idx == this.editDay - 1) this.editDay -= 1;
        }

      },
      sglRm: function(idx, type) {
        if (!!this.editObj.info[idx].pid) {
          this.ajaxFn(
            DEL_DATA_API, {
              idLable: this.editObj.info[idx].pid
            },
            function(rst) {
              if (type == 'place') CNT.editObj.places.remove(CNT.editObj.info[idx].sceMapID);
              CNT.editObj.info.removeByIndex(idx);
            }
          );
        } else {
          if (type == 'place') this.editObj.places.remove(this.editObj.info[idx].sceMapID);
          this.editObj.info.removeByIndex(idx);
        }
      },
      sglUp: function(idx) {
        if (idx < 1) return !1;
        var _cid = this.editObj.info[idx].pid,
          _aid = this.editObj.info[idx - 1].pid;
        if (!!_cid && _aid) {
          this.ajaxFn(
            SORT_DATA_API, {
              idLable1: _cid,
              idLable2: _aid
            },
            function(rst) {
              CNT.editObj.info.up(idx);
            }
          );
        } else {
          this.editObj.info.up(idx);
        }
      },
      sglDown: function(idx) {
        if (idx > this.editObj.info.length - 2) return !1;
        var _cid = this.editObj.info[idx].pid,
          _aid = this.editObj.info[idx + 1].pid;
        if (!!_cid && _aid) {
          this.ajaxFn(
            SORT_DATA_API, {
              idLable1: _cid,
              idLable2: _aid
            },
            function(rst) {
              CNT.editObj.info.down(idx);
            }
          );
        } else {
          this.editObj.info.down(idx);
        }
      },
      sglBizRm: function(idx, iidx, type) {
        var
          _id = this.editObj.info[idx].sellers[iidx].id,
          _rid = this.editObj.info[idx].sellers[iidx].resourceID;
        if (!!_id) {
          this.ajaxFn(
            DEL_BIZ_API, {
              idAttr: _id
            },
            function(rst) {
              if (type == 'food') {
                CNT.editObj.info[idx].foods.remove(_rid);
                CNT.checkedFood.remove(_rid);
              }
              if (type == 'hotel') {
                CNT.editObj.info[idx].hotels.remove(_rid);
                CNT.checkedHotel.remove(_rid);
              }
              CNT.editObj.info[idx].sellers.removeByIndex(iidx);
            }
          );
        } else {
          if (type == 'food') {
            this.editObj.info[idx].foods.remove(_rid);
            this.checkedFood.remove(_rid);
          }
          if (type == 'hotel') {
            this.editObj.info[idx].hotels.remove(_rid);
            this.checkedHotel.remove(_rid);
          }
          this.editObj.info[idx].sellers.removeByIndex(iidx);
        }
      },
      sglBizUp: function(idx, iidx) {
        if (iidx < 1) return !1;
        var _cid = this.editObj.info[idx].sellers[iidx].id,
          _aid = this.editObj.info[idx].sellers[iidx - 1].id;
        if (!!_cid && _aid) {
          this.ajaxFn(
            SORT_BIZ_API, {
              idAttr1: _cid,
              idAttr2: _aid
            },
            function(rst) {
              CNT.editObj.info[idx].sellers.up(iidx);
            }
          );
        } else {
          this.editObj.info[idx].sellers.up(iidx);
        }
      },
      sglBizDown: function(idx, iidx) {
        if (iidx > this.editObj.info[idx].sellers.length - 2) return !1;
        var _cid = this.editObj.info[idx].sellers[iidx].id,
          _aid = this.editObj.info[idx].sellers[iidx + 1].id;
        if (!!_cid && _aid) {
          this.ajaxFn(
            SORT_BIZ_API, {
              idAttr1: _cid,
              idAttr2: _aid
            },
            function(rst) {
              CNT.editObj.info[idx].sellers.down(iidx);
            }
          );
        } else {
          this.editObj.info[idx].sellers.down(iidx);
        }
      },
      changeDay: function() {
        if (!this.editObj.id) return !1;
        this.ajaxFn(
          EDIT_DAY_API, {
            idDay: this.editObj.id,
            name: this.editObj.name,
            msg: this.editObj.remark,
          }
        );
      },
      changeBiz: function(obj) {
        if (!obj.id) return !1;
        this.ajaxFn(
          EDIT_DATA_API, {
            idAttr: obj.id,
            name: obj.name,
            traffic: obj.trafficInformation,
            reason: obj.recommendedReason
          }
        );
      },
      saveDay: function(idx) {
        this.ajaxFn(
          ADD_DAY_API, {
            id: SEARCH_ID,
            infoSpot: this.getDayJson()
          },
          function(rst) {
            CNT.dayArr[idx].id = rst.data;
          }
        );
      },
      saveData: function(obj) {
        var _dayId = this.editObj.id;
        this.ajaxFn(
          ADD_DATA_API, {
            id: SEARCH_ID,
            belongsDay: this.editObj.id,
            infoSpot: JSON.stringify(obj)
          },
          function(rst) {
            CNT.getDay(_dayId);
          }
        );
      },
      saveBiz: function(obj, dataID, type) {
        var _dayId = this.editObj.id;
        this.ajaxFn(
          ADD_BIZ_API, {
            id: SEARCH_ID,
            belongsDay: this.editObj.id,
            lableid: dataID,
            resourceType: type,
            infoSpot: JSON.stringify(obj)
          },
          function(rst) {
            CNT.getDay(_dayId);
          }
        );
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
      changeFoodTag: function(id, value) {
        if (!this.editObj.id) return !1;
        this.ajaxFn(
          EDIT_TAG_API, {
            idLable: id,
            lableInfor: value,
          }
        );
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
      getDayJson: function() {
        var obj = this.editObj,
          _obj = {};
        _obj.name = obj.name;
        _obj.remark = obj.remark;
        _obj.info = obj.info;
        return JSON.stringify(_obj);
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
    if (!CNT.editObj.id) CNT.saveDay(CNT.editDay);
    CNT.editDay = 0;
    CNT.dayArr.forEach(function(obj, idx) {
      if (obj.info.length < 1) CNT.dayRm(idx);
    });
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
    $('#laiaForm').ajaxSubmit({
      url: SAVE_API,
      data: {
        id: SEARCH_ID,
        routeWay: CNT.createLine(),
        percentNum: CNT.roadPer,
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