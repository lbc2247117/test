var
    GET_DATA_API = '../Setting/selectSet',
    GET_INFO_API = '../Setting/infoSet',

    CNT = new Vue({
        el: '#wrapper',
        data: {
            /* 标签相关变量 开始 */

            tableData: [],
            tableInfo: [],
            points:[],
            selPointVideo:''
        },
        methods: {
            init: function () {
                this.getInfo();
                this.getData();
                CNT.points.push({index: 3, val: 3});
                CNT.selPointVideo=3;
            },
            getData: function () {
                var _dt = {
                };
                $.post(GET_DATA_API, _dt, function (rst) {
                    rst = JSON.parse(rst);
                    if (rst.status != 1) {
                        BASE.showAlert('获取数据失败，请重新操作', 'warning');
                        return false;
                    }
                    CNT.tableData = rst.data;
                });
            },
            isShow: function(obj,name){

            },
            /* 标签相关函数 开始 */
            getInfo: function () { //从接口获取数据后，填充对应变量，然后初始化标签列表。
                $.post(GET_INFO_API, {}, function (rst) {
                    rst = $.parseJSON(rst);
                    if (rst['status'] == '1') {
                        CNT.tableInfo = rst.data;

                    } else {
                        BASE.showAlert('获取数据失败，请重新操作');
                    }
                });
            },
            initTag: function () { //初始化标签列表
                var _html = '';
                this.allSysTagArr.forEach(function (obj) {
                    _html += '<option value="' + obj.id + '">' + obj.typeName + '</option>'; //根据接口返回数据中的Key，修改obj.id和obj.name
                });
                $('#tagPcr').html(_html);
                $('#tagPcr').selectpicker('refresh');
            },
            addUsrTag: function () {
                setTimeout(function () {
                    if (CNT.usrTagArr.indexOf(TRIM(CNT.usrTag)) < 0)
                        CNT.usrTagArr.push(TRIM(CNT.usrTag));
                    CNT.usrTag = '';
                }, 200);
            },
            selectTag: function () {
                this.sysTagArr = $('#tagPcr').selectpicker('val');
                this.resetSysObjArr();
            },
            resetSysObjArr: function () {
                this.sysTagObjArr = [];
                this.allSysTagArr.forEach(function (obj) {
                    if (CNT.sysTagArr.indexOf(obj.id) > -1)
                        CNT.sysTagObjArr.push({id: obj.id, name: obj.typeName});
                });
            },
            removeTag: function (type, idx) {
                if (type == 'usr') {
                    this.usrTagArr.splice(idx, 1);
                } else if (type == 'sys') {
                    this.sysTagArr.splice(idx, 1);
                    $('#tagPcr').selectpicker('val', this.sysTagArr);
                    this.sysTagObjArr.splice(idx, 1);
                }
            },
            postTag: function () { //保存数据时，将标签分别转换为字符串后再保存。
                this.sysTags = this.sysTagArr.join(',');
                this.usrTags = this.usrTagArr.join(',');
            },
            /*标签相关函数 结束*/



            setData: function (rst) {
                CNT.sceName = rst['sceName'];
                CNT.star = rst['star'];
                switch (rst['star'])
                {
                    case 1:
                        CNT.star = 'A';
                        break;
                    case 2:
                        CNT.star = 'AA';
                        break;
                    case 3:
                        CNT.star = 'AAA';
                        break;
                    case 4:
                        CNT.star = 'AAAA';
                        break;
                    case 5:
                        CNT.star = 'AAAAA';
                        break;
                    default:
                        CNT.star = 'S';
                }
                this.PicUrl = rst['backgroundpic'];
                this.SceRemark = rst['sceRemark'];
                this.sysTagArr = rst['sceType'];
                this.usrTagArr = rst['audefinedType'];
                $('#tagPcr').selectpicker('val', this.sysTagArr);
                this.resetSysObjArr();
//                    if (this.sceType.indexOf(',') != -1) {
//                        var arr = this.sceType.split(',');
//                        $('#sceType').selectpicker('val', arr);
//                    } else {
//                        $('#sceType').selectpicker('val', this.sceType);
//                    }

            },
        }
    });
CNT.init();
