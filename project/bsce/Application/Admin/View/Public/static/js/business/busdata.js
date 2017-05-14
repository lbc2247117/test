var GET_DATA_API = 'querySellercard',
        CNT = new Vue({
            el: '#pageWrapper',
            data: {
                id: '',
                card: '',
                service: ''
            },
            methods: {
                init: function () {
                    this.id = URL_PARAM('id');
                    this.getData();
                },
                getData: function () {
                    var _dt = {
                        id: this.id
                    };
                    $('#loading').show();
                    $.post(GET_DATA_API, _dt, function (rst) {
                        $('#loading').hide();
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg);
                            return;
                        }
                        CNT.card = rst.data.card;
                        CNT.service = rst.data.service;
                    });
                },
                gotoShortBus: function () {
                    window.location.href = 'shortbus.html?id=' + this.id;
                },
                gotoBiz: function () {
                    window.location.href = 'bizlist.html?id=' + this.id;
                },
                gotoBusData: function () {
                    window.location.href = 'busdata.html?id=' + this.id;
                },
                gotoBusiness: function () {
                    window.location.href = 'base.html?id=' + this.id;
                }
            },
        });
CNT.init();
$(function () {
    $('#business').addClass('open');
    $('#business').parents('.dropdown').addClass('open');
});