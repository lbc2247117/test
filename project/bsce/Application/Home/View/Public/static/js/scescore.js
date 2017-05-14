
var
        GET_DATA_API = '../Score/selectScorelist',
        LAIA = new Vue({
            el: '#laiaCnr',
            data: {
                lon: SEARCH_LON,
                lat: SEARCH_LAT,
                mac: localStorage.mac,
                allNum:'',
                allScore:'',
                mj:'',
                server:'',
                fw:'',
                comment:[]

            },
            methods: {
                init: function () {
                    this.getData();

                },
                getData: function () {
                    var _dt = {
                        lon:LAIA.lon,
                        lat:LAIA.lat,
                    };
                    $.post(GET_DATA_API, _dt, function (rst) {
                        if (typeof rst != 'object')
                            rst = $.parseJSON(rst);
                        if (rst.status == '1' && rst.data) {
                            LAIA.listData(rst.data);
                        } else {
                            BASE.showAlert(rst.msg);
                        }
                    });
                },

                listData: function(data) {

                    this.allNum = data.allNum;
                    this.allScore = data.allscore;
                    this.mj = data.mj;
                    this.fw = data.fw;
                    this.comment = data._T;
                    this.server = data.server;
                    LAIA.createStar(LAIA.allScore,'.star');
                    LAIA.createStar(LAIA.fw,'.atmosphere1');
                    LAIA.createStar(LAIA.server,'.atmosphere2');
                    LAIA.createStar(LAIA.mj,'.atmosphere3');
                    setTimeout(function () {
                        LAIA_SCROLL.refresh();
                    }, 200);
                },
                createStar :function(allscoreInt,star){
                    if(allscoreInt%2 == 0){
                        for(var num= 0;num<allscoreInt/2;num++){
                            $(star).append(' <a> <img src="/bsce/Application/Home/View//Public/static/css/img/staryellow.png"> </a>');
                        }
                        for(var num= 0;num<5-allscoreInt/2;num++){
                            $(star).append(' <a> <img src="/bsce/Application/Home/View//Public/static/css/img/stargrey.png"> </a>');
                        }
                    }else{
                        for(var num= 0;num<allscoreInt/2-1;num++){
                            $(star).append(' <a> <img src="/bsce/Application/Home/View//Public/static/css/img/staryellow.png"> </a>');
                        }
                        $(star).append(' <a> <img src="/bsce/Application/Home/View//Public/static/css/img/stargreyandyellow.png"> </a>');
                        for(var num= 0;num<4-allscoreInt/2;num++){
                            $(star).append(' <a> <img src="/bsce/Application/Home/View//Public/static/css/img/stargrey.png"> </a>');
                        }
                    }
                },
                score: function (id) {
                    window.location.href = 'sce_score_desc.html?lon=' + this.lon + '&lat=' + this.lat;
                },
            }
        });

PULL_DOWN_FN = function () {
    clearTimeout(PULL_DOWN_TIMER);
    PULL_DOWN_TIMER = setTimeout(function () {
        $(".star").empty();
        $(".atmosphere1").empty();
        $(".atmosphere2").empty();
        $(".atmosphere3").empty();
        LAIA.init();
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
    $('img').click(function(){
        $('img').css('width',100)
    })
});