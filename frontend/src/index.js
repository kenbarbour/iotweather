require('./style.scss');
var m = require('mithril');
var CurrentView = require('./views/CurrentView');

m.mount(document.body, CurrentView);
