var
        GET_DATA_API = '/Home/Index/get_home_data',
        MET = new Vue({
            el: '#wrapper',
            data: {
                banner: [],
                product: [],
                factory: [],
                dataObj: {},
                now: 0,
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getData: function () {
                    $.post(GET_DATA_API, {}, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status == '1') {
                            MET.setData(rst.data);
                        }
                    });
                },
                setData: function (data) {
                    this.banner = data.banner;
                    this.product = data.product;
                    this.factory = data.factory;
                    this.dataObj = data.setting;
                    this.dataObj.about = this.dataObj.about.replace(/\n/ig, "<br/>");
                    this.dataObj.about = this.dataObj.about.replace(/\s/ig, "&nbsp;");
                    setTimeout('MET.bannerTo()', 50);
                },
                bannerTo: function () {
                    $('#listpoint').children('li').removeClass('active');
                    $($('#listpoint').children('li')[MET.now]).addClass('active');
                    $('.banner-pic').removeClass('active');
                    $('.banner-pic').removeClass('left');
                    $('.banner-pic').removeClass('right');
                    $('.banner-pic').removeClass('next');
                    $('.banner-pic').removeClass('prev');
                    $($('.banner-pic')[MET.now]).addClass('active');

                    setTimeout(function () {
                        var _next = (MET.now + 1) == MET.banner.length ? 0 : MET.now + 1;
                        $($('.banner-pic')[_next]).addClass('next');
                        setTimeout(function () {
                            $($('.banner-pic')[MET.now]).addClass('left');
                            $($('.banner-pic')[_next]).addClass('left');
                            setTimeout(function () {
                                MET.now = _next;
                                MET.bannerTo();
                            }, 600);
                        }, 50);
                    }, 5000);
                },
                jumpGoodDesc: function (id) {
                    window.location.href = '/Home/Index/gooddesc.html?id=' + id;
                },
                moveToPointer: function (idx) {


                    if (idx > MET.now) {
                        $($('.banner-pic')[idx]).addClass('next');
                        setTimeout(function () {
                            $($('.banner-pic')[MET.now]).addClass('left');
                            $($('.banner-pic')[idx]).addClass('left');
                            MET.now = idx;
                            setTimeout(function () {
                                $('#listpoint').children('li').removeClass('active');
                                $($('#listpoint').children('li')[MET.now]).addClass('active');
                                $('.banner-pic').removeClass('active');
                                $('.banner-pic').removeClass('left');
                                $('.banner-pic').removeClass('right');
                                $('.banner-pic').removeClass('next');
                                $('.banner-pic').removeClass('prev');
                                $($('.banner-pic')[MET.now]).addClass('active');
                            }, 600);
                        }, 50);
                    }
                    else {
                        $($('.banner-pic')[idx]).addClass('prev');
                        setTimeout(function () {
                            $($('.banner-pic')[MET.now]).addClass('right');
                            $($('.banner-pic')[idx]).addClass('right');
                            MET.now = idx;
                            setTimeout(function () {
                                $('#listpoint').children('li').removeClass('active');
                                $($('#listpoint').children('li')[MET.now]).addClass('active');
                                $('.banner-pic').removeClass('active');
                                $('.banner-pic').removeClass('left');
                                $('.banner-pic').removeClass('right');
                                $('.banner-pic').removeClass('next');
                                $('.banner-pic').removeClass('prev');
                                $($('.banner-pic')[MET.now]).addClass('active');
                            }, 600);
                        }, 50);
                    }
                },
            },
        });
MET.init();
$(function () {
    $('#home').addClass('active');
});