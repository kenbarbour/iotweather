const m = require("mithril");

const Weather = {
  current: null,
  loadCurrent: function() {
    return m.request({
      method: "GET",
      url: "current"
    }).then(function(result){
      Weather.current = result;
    });
  }
};

module.exports = Weather;
