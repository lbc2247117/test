var
        GET_DATA_API = '../Live/selectVideo',
        GET_TAG_API = '../Live/selectTag',
        ADD_VIEW_API = 'addClick',
        LAIA = new Vue({
            el: '#laiaCnr',
            data: {
                isloadfirst: 1,
                videoList: [],
                pullData: [],
                first: 1,
                type: 0,
                page: 1,
                size: 3,
                lon: SEARCH_LON,
                lat: SEARCH_LAT,
                videoType: URL_PARAM('videoType'),
                tag: URL_PARAM('tag'),
                tagData: [],
                wapTag: []
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getTag: function () {
                    $.post(GET_TAG_API, '', function (rst) {
                        rst = $.parseJSON(rst);
                        if (rst.status != '1') {
                            BASE.showAlert(rst.msg);
                            return false;
                        } else {
                            LAIA.tagData = rst.data;
                            LAIA.wapTag.push({
                                id: -1,
                                tagName: '全部标签',
                            });
                            for (var i = 0; i < rst.data.length; i++) {
                                LAIA.wapTag.push(rst.data[i]);
                            }
                        }
                    });
                },
                toTag: function (id) {
                    window.location.href = 'video_list.html?tag=' + id + '&lon=' + this.lon + '&lat=' + this.lat + '&videoType=' + this.videoType;
                },
                playEvent: function (idx, e) {
                    var _dt = this.videoList[idx];
                    var id = _dt['id'];
                    var _audioTag = _dt['audioUrl'];
                    var _videoType = _dt['videoType'];
                    //判断是否美景直播
                    if (_videoType != 1)
                        return;
                    //判断是否播放音频,由于微信、安卓自带浏览器对e.muted = true设置并未生效，故暂时去掉该功能
//                    if (_audioTag != 0) {
//                        e.muted = true;
//                        $('#audio-' + id)[0].play();
//                    }
                    $.post(ADD_VIEW_API, {id: id}, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status == '1') {
                            LAIA.videoList[idx]['watchNum'] = LAIA.videoList[idx]['watchNum'] + 1;
                        }
                    });
                },
                videoPause: function (idx) {
                    var _dt = this.videoList[idx];
                    var id = _dt['id'];
                    var _audioTag = _dt['audioUrl'];
                    //判断是否播放音频
                    if (_audioTag != 0) {
                        $('#audio-' + id)[0].pause();
                    }
                },
                getData: function () {
                    var _dt = {
                        type: LAIA.type,
                        page: LAIA.page,
                        size: LAIA.size,
                        lon: LAIA.lon,
                        lat: LAIA.lat,
                        videoType: LAIA.videoType,
                        waptag: LAIA.tag
                    };
                    $.post(GET_DATA_API, _dt, function (rst) {
                        rst = $.parseJSON(rst);
                        if (rst.status != '1') {
                            BASE.showAlert(rst.msg);
                            return false;
                        }
                        if (LAIA.first) {
                            LAIA.listData(rst.data.data);
                        } else {
                            LAIA.pullData = rst.data.data;
                            if (rst.data.data == null || rst.data.data == '') {
                                LAIA.pullData = null;
                                LAIA.page = LAIA.page - 1;
                            }
                            LAIA.pushlistData(rst.data.data);
                        }
                    });
                },
                listData: function (data) {
                    LAIA.videoList = data;
                    setTimeout(function () {
                        LAIA_SCROLL.refresh();
                    }, 200);
                },
                pushlistData: function (data) {
                    if (data != null && data != '')
                    {
                        for (var i = 0; i < data.length; i++) {
                            LAIA.videoList.push(data[i]);
                        }
                    }
                    setTimeout(function () {
                        LAIA_SCROLL.refresh();
                    }, 200);
                }
            }
        });
PULL_DOWN_FN = function () {
    clearTimeout(PULL_DOWN_TIMER);
    PULL_DOWN_TIMER = setTimeout(function () {
        LAIA.first = 1;
        LAIA.page = 1;
        LAIA.init();
    }, 1000);
};
PULL_UP_FN = function () {
    clearTimeout(PULL_UP_TIMER);
    PULL_DOWN_TIMER = setTimeout(function () {
        LAIA.first = 0;
        LAIA.page = LAIA.page + 1;
        LAIA.init();
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
                } else {
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
    }, 800);
};
$(function () {
    LOADED_FN();
    LAIA.getTag();
    LAIA.init();
    if (!SEARCH_INAPP) {
        $('#navBack').off('click');
        $('#navBack').on('click', function () {
            var _search = '?lon=' + SEARCH_LON + '&lat=' + SEARCH_LAT;
            window.location.href = 'sce.html' + _search;
        });
    }
});