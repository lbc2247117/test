var
        GET_DATA_API = 'getAbout',
        MET = new Vue({
            el: "#wrapper",
            data: {
                about: '',
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getData: function () {
                    $.post(GET_DATA_API, {}, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status == 1)
                            MET.about = rst.data.about;
                        MET.about = MET.about.replace(/\n/ig, "<br/>");
                        MET.about = MET.about.replace(/\s/ig, "&nbsp;");
                    });
                },
            },
        });
MET.init();
$(function () {
    $('#about').addClass('active');
});