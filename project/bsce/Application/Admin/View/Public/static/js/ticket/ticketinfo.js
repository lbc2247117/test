var
        GET_DATA_API = 'selectTicketById',
        EDIT_API = 'saveTicket',
        ADD_API = 'addTicket',
        CNT = new Vue({
            el: '#pageWrapper',
            data: {
                name: '',
                pic: '/ss',
                remark: '',
                price: '',
                person1: '',
                person2: '',
                rangeRemark: '',
                ruleRemark: '',
                includeRemark: "",
                id: URL_PARAM('id'),
                isloadComplete: !1,
                tel: ''
            },
            methods: {
                init: function () {
                    if (this.id) {
                        this.mars = EDIT_API;
                        this.getData();
                    } else {
                        CNT.isloadComplete = !0;
                        this.mars = ADD_API;
                    }
                },
                getData: function () {
                    var _dt = {
                        id: this.id
                    };
                    if (this.id) {
                        $('#loading').show();
                        $.post(GET_DATA_API, _dt, function (rst) {
                            CNT.isloadComplete = !0;
                            $('#loading').hide();
                            rst = JSON.parse(rst);
                            if (rst.status != 1) {
                                BASE.showAlert('获取数据失败，请重新操作');
                                return false;
                            }
                            CNT.setData(rst.data);
                        });
                    }
                },
                setData: function (rst) {
                    CNT.name = rst['name'];
                    CNT.pic = rst['pic'];
                    CNT.remark = rst['remark'];
                    CNT.price = rst['price'];
                    CNT.person1 = rst['person1'];
                    CNT.person2 = rst['person2'];
                    CNT.rangeRemark = rst['rangeRemark'];
                    CNT.ruleRemark = rst['ruleRemark'];
                    CNT.includeRemark = rst['includeRemark'];
                    CNT.tel = rst['tel'];
                    BASE.initTextCount();
                },
                uploadPic: function () {
                    $('#pic').click();
                },
            }
        });
EDITOR = new UE.ui.Editor();
EDITOR.render("myEditor");
CNT.init();
$(function () {

    var picView = new uploadPreview({
        UpBtn: 'pic',
        ImgShow: 'picView',
        ImgType: ['jpg', 'png', 'gif'],
        ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种'
    });
    $('#btnSubmit').click(function () {
        //判断输入
        if (STRING_LENGTH(TRIM(CNT.name)) == 0) {
            BASE.showAlert('请输入票务名称');
            return;
        }
        if (STRING_LENGTH(TRIM(CNT.name)) > 20) {
            BASE.showAlert('票务名称最多只能输入20个字符');
            return;
        }
        $('#editform').ajaxSubmit({
            url: CNT.mars,
            beforeSubmit: function () {
                $('#loading').show();
            },
            success: function (rst) {
                $('#loading').hide();
                rst = JSON.parse(rst);
                if (rst.status != 1) {
                    BASE.showAlert(rst.msg);
                    return false;
                }
                window.location.href = 'ticketlist.html';
                BASE.showAlert('操作成功');
            }
        });
    });
    $('#ticketlist').addClass('open');
    $('#ticketlist').parents('.dropdown').addClass('open');
    EDITOR.ready(function () {
        while (true) {
            if (CNT.isloadComplete == !0) {
                EDITOR.setContent(CNT.content);
                return;
            }
        }
    });
});