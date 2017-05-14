
var
        GET_DATA_API = '../Score/selectScore',
        SUBMIT_DATA_API = '../Score/addScore',
        LAIA = new Vue({
            el: '#laiaCnr',
            data: {
                lon: SEARCH_LON,
                lat: SEARCH_LAT,
                mac: localStorage.mac,
                fenwei: '',
                fuwu: '',
                meijing: '',
                allscore: '',
                content: '',
                allcontent: [],
                listContent: []
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getData: function () {
                    var _dt = {
                    };
                    $.post(GET_DATA_API, _dt, function (rst) {
                        if (typeof rst != 'object')
                            rst = $.parseJSON(rst);
                        if (rst.status == '1' && rst.data) {
                            LAIA.allcontent = rst.data.data
                        } else {
                            BASE.showAlert(rst.msg);
                        }
                    });
                },
                listData: function () {
                    LAIA.content = LAIA.allcontent[5 - LAIA.allscore].content;
                    LAIA.listContent = LAIA.allcontent[5 - LAIA.allscore].listContent;
                },
                score: function () {
                    var _dt = {
                        lon: this.lon,
                        lat: this.lat,
                        fwScore: this.fenwei,
                        mjScore: this.meijing,
                        serverScore: this.fuwu,
                        Score: LAIA.allscore,
                        contentXj: LAIA.content,
                        zdContent: LAIA.zdContent
                    };
                    $.post(SUBMIT_DATA_API, _dt, function (rst) {
                        if (typeof rst != 'object')
                            rst = $.parseJSON(rst);
                        if (rst.status == '1') {
                            BASE.showAlert('提交成功');
                            window.location.href = 'scescore.html?lon=' + LAIA.lon + '&lat=' + LAIA.lat;
                        } else {
                            BASE.showAlert(rst.msg);
                        }
                    });
                },
                tag: function (obj, idx) {
                    LAIA.zdContent = obj;
                    $('.scoretag').find('div').css({color: '#666', 'border': '1px solid #e2e2e2'});
                    $($('.scoretag').find('div')[idx]).css({color: '#4BDDCF', 'border': '1px solid #4BDDCF'});
                },
                fw: function (num) {
                    LAIA.fenwei = num;
                    LAIA.Allscore();
                },
                server: function (num) {
                    LAIA.fuwu = num;
                    LAIA.Allscore();
                },
                sce: function (num) {
                    LAIA.meijing = num;
                    LAIA.Allscore();
                },
                Allscore: function () {
                    LAIA.allscore = parseInt((LAIA.fuwu + LAIA.fenwei + LAIA.meijing) / 3);
                    LAIA.listData();
                }

            }
        });

PULL_DOWN_FN = function () {
    clearTimeout(PULL_DOWN_TIMER);
    PULL_DOWN_TIMER = setTimeout(function () {
        LAIA.init();
        LAIA_SCROLL.refresh();
    }, 1000);
};
LOADED_FN = function () {
    PULL_DOWN_EL = document.getElementById('pullDown');
    PULL_DOWN_OFFSET = PULL_DOWN_EL.offsetHeight;

    LAIA_SCROLL = new iScroll('laiaCnr', {
        useTransition: false,
        topOffset: PULL_DOWN_OFFSET,
        onRefresh: function () {
            if (PULL_DOWN_EL.className.match('loading')) {
                PULL_DOWN_EL.className = '';
                PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '下拉刷新';
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
            }
        },
        onScrollEnd: function () {
            if (PULL_DOWN_EL.className.match('flip')) {
                PULL_DOWN_EL.className = 'loading';
                PULL_DOWN_EL.querySelector('.pull-down-label').innerHTML = '加载中';
                PULL_DOWN_FN();
            }
        }
    });
    setTimeout(function () {
        document.getElementById('laiaCnr').style.left = '0';
    }, 800);
};
$(function () {
    LOADED_FN();
    LAIA.init();
    if (!SEARCH_INAPP) {
        $('#navBack').off('click');
        $('#navBack').on('click', function () {
            var _search = '?lon=' + SEARCH_LON + '&lat=' + SEARCH_LAT;
            window.location.href = 'sce.html' + _search;
        });
    }


});