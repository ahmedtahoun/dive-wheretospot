// Match quiz: remember the diver's answers in this browser (a convenience only), and jump to results.
(function () {
  'use strict';
  var form = document.querySelector('[data-quiz]');
  if (!form) return;
  var KEY = 'wts-diver';
  var params = new URLSearchParams(location.search);
  function save() {
    var data = new FormData(form), obj = {};
    data.forEach(function (v, k) { if (k === 'go') return; (obj[k] = obj[k] || []).push(v); });
    try { localStorage.setItem(KEY, JSON.stringify(obj)); } catch (e) {}
  }
  if (!params.has('go')) {
    var saved = null;
    try { saved = JSON.parse(localStorage.getItem(KEY) || 'null'); } catch (e) {}
    if (saved) {
      Object.keys(saved).forEach(function (k) {
        saved[k].forEach(function (v) {
          var el = form.querySelector('[name="' + k + '"][value="' + CSS.escape(v) + '"]');
          if (el && (el.type === 'radio' || el.type === 'checkbox')) el.checked = true;
          var sel = form.querySelector('select[name="' + k + '"]');
          if (sel) sel.value = v;
        });
      });
    }
  }
  form.addEventListener('submit', save);
  if (params.has('go')) {
    var res = document.getElementById('results');
    if (res) setTimeout(function () { res.scrollIntoView(); }, 60);
  }
  // Keep the "Ask all" title in step with the ticked instructors.
  var ask = document.getElementById('ask');
  if (ask) {
    var h = ask.querySelector('h2');
    var boxes = document.querySelectorAll('input[form="ask"][name="to[]"]');
    var sync = function () {
      var n = Array.prototype.filter.call(boxes, function (b) { return b.checked; }).length;
      h.textContent = n === 0 ? 'Tick at least one instructor' : 'Ask ' + (n === 1 ? 'them' : 'all ' + n) + ' in one message';
      ask.querySelector('button[type=submit]').disabled = n === 0;
    };
    Array.prototype.forEach.call(boxes, function (b) { b.addEventListener('change', sync); });
    sync();
  }
})();
