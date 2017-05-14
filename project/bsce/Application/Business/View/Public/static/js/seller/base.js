var
  GET_DATA_API = 'queryBuss',
  ADD_IMG_API = 'uploadCover',
  DEL_IMG_API = 'delOnepic',
  SAVE_API = 'alertBuss',
  IMG_LIMIT = 5,
  CNT = new Vue({
    el: '#pageWrapper',
    data: {
      uploaderVisible: !1,
      imgListArr: [{
        id: '11',
        src: '//192.168.0.90/momo.jpg'
      }, {
        id: '11',
        src: '//192.168.0.90/qb.jpg'
      }],
      bizDsc: '',
      bizAddress: '',
      bizCell: '',
      name: '',
      type: ''
    },
    methods: {
      init: function() {
        this.getData();
      },
      ajaxFn: function(api, data, cb, failMsg, errorMsg) {
        $.post(api, data, function(rst, status) {
          if (status == 'success') {
            if (typeof rst != 'object') rst = JSON.parse(rst);
            if (rst.status != '1') BASE.showAlert(!!failMsg ? failMsg : rst.msg);
            if (typeof cb == 'function') cb(rst);
          } else {
            BASE.showAlert(!!errorMsg ? errorMsg : '网络有点儿问题~<br>稍后再试吧~');
          }
        });
      },
      getData: function() {
        var _dt = {};
        this.ajaxFn(
          GET_DATA_API,
          _dt,
          function(rst) {
            if (rst.status == '1') {
              CNT.listData(rst.data);
            }
          },
          '获取数据失败～'
        );
      },
      listData: function(data) {
        this.bizDsc = data.remark;
        this.bizAddress = data.adress;
        this.bizCell = data.tel;
        this.name = data.name;
        this.imgListArr = typeof data.backPic == 'object' ? data.backPic : [];
        IMG_LIMIT = 5 - this.imgListArr.length;
        switch (data.commercialTenantType) {
          case '美食':
            CNT.type = 0;
            break;
          case '住宿':
            CNT.type = 1;
            break;
          case '购物':
            CNT.type = 2;
            break;
          case '娱乐':
            CNT.type = 3;
            break;
          default:
            CNT.type = -1;
        }
        //this.imgListArr = data.imgs;
      },
      showUploader: function() {
        this.uploaderVisible = !0;
        setTimeout(function() {
          uploader.refresh();
        }, 500);
      },
      rmImg: function(idx) {
        if (this.imgListArr.length <= 3) {
          BASE.showAlert('不能再删啦～<br>至少要保留3张图片');
          return !1;
        }
        var _dt = {
          path: idx,
        };
        this.ajaxFn(
          DEL_IMG_API,
          _dt,
          function(rst) {
            if (rst.status == '1') {
              BASE.showAlert('删除成功');
              CNT.getData();
            } else
              BASE.showAlert(rst.msg);
          },
          '获取数据失败～'
        );
      }
    }
  }),
  imgUploadInit = function() {
    window.uploader = new WebUploader.Uploader({
      pick: {
        id: '#filePicker',
        label: '点击选择图片'
      },
      dnd: '#dndArea',
      paste: '#uploader',
      chunked: true,
      chunkSize: 2 * 1024 * 1024,
      sendAsBinary: false,
      server: ADD_IMG_API,
      disableGlobalDnd: true,
      fileNumLimit: 5,
      fileSizeLimit: 200 * 1024 * 1024,
      fileSingleSizeLimit: 10 * 1024 * 1024
    });

    uploader.addButton({
      id: '#filePicker2',
      label: '继续添加'
    });
    uploader.on('beforeFileQueued', function(file) {
      if (uploader.getFiles().length > IMG_LIMIT - 1) {
        BASE.showAlert('最多只能添加5张图片');
        return !1;
      }
    });
    uploader.on('uploadSuccess', function(e, rst) {
      if (!rst.status) {
        BASE.showAlert(rst.msg);
      } else {
        CNT.uploaderVisible = !1;
        BASE.showAlert('上传成功');
        uploader.reset();
      }
      CNT.getData();
    });

    if (!WebUploader.Uploader.support()) {
      alert('Web Uploader 不支持您的浏览器！');
      throw new Error('WebUploader does not support the browser you are using.');
    }
  }();

$(function() {
  CNT.init();
  $('#saveBtn').click(function() {
    if ('' == TRIM(CNT.bizDsc)) {
      BASE.showAlert('请输入商家描述');
      return !1;
    } else if ('' == TRIM(CNT.bizAddress)) {
      BASE.showAlert('请输入商家地址');
      return !1;
    } else if ('' == TRIM(CNT.bizCell)) {
      BASE.showAlert('请输入联系方式');
      return !1;
    }
    $('#laiaForm').ajaxSubmit({
      url: SAVE_API,
      data: {
        type: CNT.type,
        name: CNT.name,
        adress: CNT.bizAddress,
        remark: CNT.bizDsc,
        tel: CNT.bizCell
      },
      success: function(rst, status) {
        if (status == 'success') {
          if (typeof rst != 'object') rst = JSON.parse(rst);
          if (rst.status == '1') {
            BASE.showAlert('保存成功！');
            CNT.getData();
          } else {
            BASE.showAlert(rst.msg);
          }
        } else {
          BASE.showAlert('保存失败~');
        }
      }
    });
  });
});