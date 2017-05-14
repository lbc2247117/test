var
POST_DATA_API = '../Question/addQuestion',
LAIA = new Vue({
  el: '#laiaCnr',
  data: {
    userID: SEARCH_ID,
    lon: SEARCH_LON,
    lat: SEARCH_LAT,
    question: ''
  },
  methods: {
    postData: function() {
      if (  this.question) {
        BASE.showConfirm('确定提交？',function(){
          $.post(POST_DATA_API, {
            userID: LAIA.userID,
            lon:LAIA.lon,
            lat:LAIA.lat,
            requestContent: LAIA.question
          }, function(rst, status) {
            if (status == 'success') {
              if (typeof rst != 'object') rst = $.parseJSON(rst);
              if (rst.status == '1') {
                BASE.hideConfirm();
                BASE.showAlert('提交成功');
                setTimeout(function(){
                  window.location.href = 'faq?lon=' + LAIA.lon + '&lat=' + LAIA.lat;
                },300)
              } else {
                BASE.showConfirm(rst.msg);
              }
            } else {
              BASE.showConfirm('网络有点儿问题');
            }
          });
        });
      } else {
        BASE.showConfirm('请输入您的问题');
      }
    }
  }
});
$(function () {
  if (!SEARCH_INAPP) {
    $('#laiaCnr').css('marginTop', '40px');
  }
});