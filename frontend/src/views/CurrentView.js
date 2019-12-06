const m = require('mithril');
const Weather = require('../models/weather');

function ctof(c) {
  return c * 1.8 + 32;
}

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
      m('h2.subtitle', 'as of ' + Weather.current.local_time),
      m('.level', [
        m('.level-item.has-text-centered', m("div",[
          m('p.heading"', 'Temperature'),
          m('p.title', ctof(Weather.current.temperature) + ' °F')
        ])),
        m('.level-item.has-text-centered', m("div",[
          m('p.heading"', 'Pressure'),
          m('p.title', Weather.current.pressure + ' hPa')
        ])),
        m('.level-item.has-text-centered', m("div",[
          m('p.heading"', 'Humidity'),
          m('p.title', Weather.current.humidity + '%')
        ])),
        (Weather.current.wind_speed ? m('.level-item.has-text-centered', m("div",[
          m('p.heading"', 'Wind Speed'),
          m('p.title', Weather.current.wind_speed + ' m/s')
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
  }
}


module.exports = CurrentView;
