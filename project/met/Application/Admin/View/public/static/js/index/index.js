var
        GET_DATA_API = '/Admin/Index/getBaseData',
        SAVE_DATA_API = '/Admin/Index/setBaseData',
        MET = new Vue({
            el: '#wrapper',
            data: {
                info: '',
                isLock: !1,
            },
            methods: {
                init: function () {
                    this.getData();
                },
                getData: function () {
                    $.post(GET_DATA_API, {}, function (rst) {
                        rst = JSON.parse(rst);
                        if (rst.status != 1) {
                            BASE.showAlert(rst.msg);
                            return false;
                        }
                        MET.info = rst.data;
                    });
                },
                saveEdit: function () {
                    if (MET.isLock)
                        return false;
                    if (!MET.info.company.trim()) {
                        BASE.showAlert('请输入公司名称');
                        return false;
                    }
                    if (!MET.info.QQ.trim()) {
                        BASE.showAlert('请输入QQ');
                        return false;
                    }
                    if (!MET.info.mobile.trim()) {
                        BASE.showAlert('请输入手机号');
                        return false;
                    }
                    if (!MET.info.tell.trim()) {
                        BASE.showAlert('请输入座机号码');
                        return false;
                    }
                    if (!MET.info.email.trim()) {
                        BASE.showAlert('请输入email');
                        return false;
                    }
                    if (!MET.info.address.trim()) {
                        BASE.showAlert('请输入地址');
                        return false;
                    }
                    if (!MET.info.about.trim()) {
                        BASE.showAlert('请输入关于我们');
                        return false;
                    }
                    MET.isLock = !0;
                    $('#editform').ajaxSubmit({
                        beforeSubmit: function () {
                            $('#loading').show();
                        },
                        url: SAVE_DATA_API,
                        success: function (rst) {
                            $('#loading').hide();
                            MET.isLock = !1;
                            rst = JSON.parse(rst);
                            if (rst.status != 1) {
                                BASE.showAlert(rst.msg);
                                return false;
                            }
                            BASE.showAlert('保存成功');
                        },
                    });
                }
            },
        });
MET.init();
$(function () {
    $('#base-set').addClass('open');
    $('#base-set').parents('.dropdown').addClass('open');
    $('#qrView').click(function () {
        $('#qr').click();
    });
    $('#headrPicView').click(function () {
        $('#headrPic').click();
    });
    var qrView = new uploadPreview({
        UpBtn: 'qr',
        ImgShow: 'qrView',
        ImgType: ['jpg', 'png'],
        ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种'
    });
    var headrPicView = new uploadPreview({
        UpBtn: 'headrPic',
        ImgShow: 'headrPicView',
        ImgType: ['jpg', 'png'],
        ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种'
    });
});