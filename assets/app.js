// Small progressive enhancements. Every page works without JS.
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var store = {
    get: function (k) { try { return JSON.parse(sessionStorage.getItem(k) || 'null'); } catch (e) { return null; } },
    set: function (k, v) { try { sessionStorage.setItem(k, JSON.stringify(v)); } catch (e) {} }
  };

  // ---------- Directory filters ----------
  var list = $('[data-list]');
  if (list) {
    var cards = $$('.pcard', list);
    var state = { level: [], type: [], agency: [], lang: [], spot: [], week: false, verified: false };
    var params = new URLSearchParams(location.search);
    ['level', 'type', 'agency', 'lang', 'spot'].forEach(function (k) {
      var v = params.get(k);
      if (v) state[k] = v.split(',');
    });
    if (params.get('week') === '1') state.week = true;
    if (params.get('verified') === '1') state.verified = true;

    var has = function (csv, v) { return (',' + csv + ',').indexOf(',' + v + ',') !== -1; };
    var anyOf = function (csv, arr) { return !arr.length || arr.some(function (v) { return has(csv, v); }); };

    function matches(c) {
      var d = c.dataset;
      if (state.level.length) {
        var ok = state.level.some(function (v) {
          if (v === 'coach') return has(d.cats, 'coaching') || d.type === 'coach';
          if (v === 'fun') return has(d.cats, 'fun') || has(d.cats, 'guiding');
          return has(d.levels, v);
        });
        if (!ok) return false;
      }
      if (state.type.length && state.type.indexOf(d.type) === -1) return false;
      if (!anyOf(d.agencies, state.agency) || !anyOf(d.langs, state.lang) || !anyOf(d.spots, state.spot)) return false;
      if (state.week && d.week !== '1') return false;
      if (state.verified && d.verified !== '1') return false;
      return true;
    }

    function apply() {
      var n = 0;
      cards.forEach(function (c) { var m = matches(c); c.hidden = !m; if (m) n++; });
      $('[data-count]').textContent = n;
      $('[data-empty]').hidden = n !== 0;
      $$('.chips').forEach(function (g) {
        var k = g.dataset.filter;
        $$('button', g).forEach(function (b) { b.setAttribute('aria-pressed', state[k].indexOf(b.dataset.v) !== -1 ? 'true' : 'false'); });
      });
      $$('[data-flag]').forEach(function (i) { i.checked = !!state[i.dataset.flag]; });
      var q = new URLSearchParams();
      ['level', 'type', 'agency', 'lang', 'spot'].forEach(function (k) { if (state[k].length) q.set(k, state[k].join(',')); });
      if (state.week) q.set('week', '1');
      if (state.verified) q.set('verified', '1');
      var qs = q.toString();
      history.replaceState(null, '', location.pathname + (qs ? '?' + qs : ''));
    }

    $$('.chips').forEach(function (g) {
      g.addEventListener('click', function (e) {
        var b = e.target.closest('button'); if (!b) return;
        var k = g.dataset.filter, v = b.dataset.v, i = state[k].indexOf(v);
        if (g.hasAttribute('data-single')) state[k] = i === -1 ? [v] : [];
        else if (i === -1) state[k].push(v); else state[k].splice(i, 1);
        apply();
      });
    });
    $$('[data-flag]').forEach(function (i) { i.addEventListener('change', function () { state[i.dataset.flag] = i.checked; apply(); }); });
    $$('[data-reset]').forEach(function (b) {
      b.addEventListener('click', function () { state = { level: [], type: [], agency: [], lang: [], spot: [], week: false, verified: false }; apply(); });
    });

    var sortSel = $('[data-sort]');
    var original = cards.slice();
    sortSel.addEventListener('change', function () {
      var k = sortSel.value, arr = original.slice();
      if (k === 'price') arr.sort(function (a, b) { return a.dataset.price - b.dataset.price; });
      if (k === 'ratio') arr.sort(function (a, b) { return a.dataset.ratio - b.dataset.ratio; });
      if (k === 'name') arr.sort(function (a, b) { return a.dataset.name.localeCompare(b.dataset.name); });
      arr.forEach(function (c) { list.appendChild(c); });
    });

    var ft = $('.filters-toggle');
    if (ft) ft.addEventListener('click', function () {
      var f = ft.closest('.filters'); var open = f.classList.toggle('open');
      ft.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    apply();
  }

  // ---------- Compare bar ----------
  var bar = $('[data-cmpbar]');
  if (bar) {
    var picked = store.get('cmp') || [];
    var names = {};
    $$('.pcard').forEach(function (c) { names[c.dataset.slug] = c.dataset.name; });
    picked = picked.filter(function (s) { return names[s]; });
    function sync() {
      $$('input.cmp').forEach(function (i) {
        i.checked = picked.indexOf(i.value) !== -1;
        i.disabled = !i.checked && picked.length >= 4;
      });
      bar.hidden = picked.length === 0;
      $('[data-cmpn]', bar).textContent = picked.length;
      $('[data-cmpnames]', bar).textContent = picked.map(function (s) { return names[s]; }).join(' · ');
      var go = $('[data-cmpgo]', bar);
      go.href = '/en/compare?p=' + picked.join(',');
      go.classList.toggle('is-disabled', picked.length < 2);
      go.textContent = picked.length < 2 ? 'Pick one more' : 'Compare ' + picked.length;
      store.set('cmp', picked);
    }
    document.addEventListener('change', function (e) {
      if (!e.target.matches('input.cmp')) return;
      var v = e.target.value, i = picked.indexOf(v);
      if (e.target.checked && i === -1 && picked.length < 4) picked.push(v);
      if (!e.target.checked && i !== -1) picked.splice(i, 1);
      sync();
    });
    $('[data-cmpgo]', bar).addEventListener('click', function (e) { if (picked.length < 2) e.preventDefault(); });
    $('[data-cmpclear]', bar).addEventListener('click', function () { picked = []; sync(); });
    sync();
  }

  // ---------- Price table: level tabs + sorting ----------
  var ptable = $('table[data-sortable]');
  if (ptable) {
    var rows = $$('tbody tr', ptable);
    $$('.tabs button').forEach(function (t) {
      t.addEventListener('click', function () {
        $$('.tabs button').forEach(function (x) { x.setAttribute('aria-selected', x === t ? 'true' : 'false'); });
        var lvl = t.dataset.lvl;
        rows.forEach(function (r) { r.hidden = lvl !== 'all' && r.dataset.lvl !== lvl; });
      });
    });
    $$('thead button', ptable).forEach(function (b, idx) {
      b.addEventListener('click', function () {
        var th = b.parentNode, col = Array.prototype.indexOf.call(th.parentNode.children, th);
        var asc = th.getAttribute('aria-sort') !== 'ascending';
        $$('thead th', ptable).forEach(function (x) { x.removeAttribute('aria-sort'); });
        th.setAttribute('aria-sort', asc ? 'ascending' : 'descending');
        var num = b.dataset.sort === 'num', tb = ptable.tBodies[0];
        rows.slice().sort(function (a, c) {
          var x = a.children[col].dataset.v, y = c.children[col].dataset.v;
          var r = num ? parseFloat(x) - parseFloat(y) : String(x).localeCompare(String(y));
          return asc ? r : -r;
        }).forEach(function (r) { tb.appendChild(r); });
      });
    });
  }
})();
