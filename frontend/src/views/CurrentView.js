const m = require('mithril');
const Weather = require('../models/weather');

function ctof(c) {
  return c * 1.8 + 32;
}

var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

var CurrentView = {

  intervalID: null,
  autorefresh_enabled: true,
  oninit: function() {
    CurrentView.intervalID = setInterval(CurrentView.autorefresh, 30000);
    return Weather.loadCurrent();
  },
  view: function() {
    return m('div.section', [m('div.container',
      (Weather.current == null) ? [m('.notification', 'Loading current conditions...')] : [
      m('h1.title', 'Current Conditions'),
      m('h2.subtitle', 'as of ' + CurrentView.formatted_local_time()),
      m('.level', [
        m('.level-item.has-text-centered', m("div",[
          m('p.heading"', 'Temperature'),
          m('p.title', Number(ctof(Weather.current.temperature)).toFixed(1) + ' °F')
        ])),
        m('.level-item.has-text-centered', m("div",[
          m('p.heading"', 'Pressure'),
          m('p.title', Number(Weather.current.pressure).toFixed(0) + ' hPa')
        ])),
        m('.level-item.has-text-centered', m("div",[
          m('p.heading"', 'Humidity'),
          m('p.title', Number(Weather.current.humidity).toPrecision(2) + '%')
        ])),
        (Weather.current.wind_speed ? m('.level-item.has-text-centered', m("div",[
          m('p.heading"', 'Wind Speed'),
          m('p.title', Number(Weather.current.wind_speed).toFixed(1) + ' m/s')
        ])) : null),
      ]),
      m('.field.is-grouped', [
        m('.control', m('button.button.is-small', {
          disabled: CurrentView.autorefresh_enabled,
          onclick: CurrentView.refresh
        }, 'Refresh')),
        m('.control', m('label.checkbox', [ m('input[type=checkbox]',{
          checked: CurrentView.autorefresh_enabled,
          onchange: function(e) { CurrentView.autorefresh_enabled = e.target.checked; }
        }), " Auto"]))
      ])
    ])]);
  },

  autorefresh: function() {
    if (CurrentView.autorefresh_enabled) CurrentView.refresh();
  },

  refresh: function() {
    Weather.loadCurrent();
  },

  formatted_local_time: function() {
		console.log("Local time from server:",Weather.current.local_time);
		var date = new Date(Weather.current.local_time);
		console.log(date);
		var month = months[date.getMonth()];
		var hour = date.getHours() % 12;
		console.log(date.getHours());
		var is_pm = (date.getHours() >= 12);
		var tz_offset = '<span class="has-text-grey-lighter">UTC ' + (date.getTimezoneOffset() / -60 * 100) + '</span>';

		return "" + month + " " + date.getDate() + ", " +
			hour + ":" + date.getMinutes() + " " +(is_pm ? 'PM' : 'AM') +
			" " //+ tz_offset 
			;
  }
}


module.exports = CurrentView;
