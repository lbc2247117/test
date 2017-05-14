var
  LAIA = new Vue({
    el: '#laiaCnr',
    data: {
      address: URL_PARAM('adr'),
      bg: URL_PARAM('img')
    },
    methods: {
      bgImg: function(url) {
        return url ? ('url(' + url + ')') : '';
      }
    }
  });