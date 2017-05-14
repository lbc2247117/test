var
        GET_DATA_API = 'editActivity',
        EDIT_API = 'updateActivity',
        ADD_API = 'addActivity',
        CNT = new Vue({
            el: '#pageWrapper',
            data: {
                titleName: '',
                logoUrl: '',
                startTime: '',
                endTime: '',
                acTitle: '',
                name: '',
                acRuke: '',
                acjp: '',
                content: "",
                id: URL_PARAM('id'),
                isloadComplete:!1,
                signUp:''
            },
            methods: {
                init: function () {
                    if (this.id){
                        this.mars = EDIT_API;
                        this.getData();
                     }else{
                         CNT.isloadComplete=!0;
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
                            CNT.isloadComplete=!0;
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
                    CNT.titleName = rst['titleName'];
                    CNT.content = rst['content'];
                    CNT.logoUrl = rst['url'];
                    CNT.startTime = rst['startTime'];
                    CNT.endTime = rst['endTime'];
                    CNT.acTitle = rst['acTitle'];
                    CNT.name = rst['name'];
                    CNT.acRuke = rst['acRuke'];
                    CNT.acjp = rst['acjp'];
                    CNT.signUp = rst['signUp'];
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
        ImgType: ['jpg', 'png'],
        ErrMsg: '选择文件错误,图片类型必须是(png,jpg)中的一种'
    });
    $('#btnSubmit').click(function () {
        //判断输入
        if (STRING_LENGTH(TRIM(CNT.titleName)) == 0) {
            BASE.showAlert('请输入活动标题');
            return;
        }
        if (STRING_LENGTH(TRIM(CNT.titleName)) > 32) {
            BASE.showAlert('活动标题最多只能输入32个字符');
            return;
        }
        var _time = new Date(CNT.endTime) - new Date(CNT.startTime);
        if (_time < 0) {
            BASE.showAlert('活动开始时间不能大于活动结束时间');
            return;
        }
        $('#editform').ajaxSubmit({
            url:CNT.mars,
            beforeSubmit: function () {
                $('#loading').show();
            },
            success: function (rst) {
                $('#loading').hide();
                rst = JSON.parse(rst);
                if (rst.status != 1) {
                    CNT.showAlert('warning', rst.msg);
                    return false;
                }
                window.location.href = 'common.html';
                CNT.showAlert('success', '操作成功');
            }
        });
    });
    $('#commonact').addClass('open');
    $('#commonact').parents('.dropdown').addClass('open');
    EDITOR.ready(function(){
            while(true){
            if(CNT.isloadComplete==!0){
                EDITOR.setContent(CNT.content);
                return;
            }
        }
    });
});