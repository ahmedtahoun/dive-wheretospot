// The scroll-to-dive experience on spot pages: scrolling down = descending.
(function () {
  'use strict';
  var dive = document.getElementById('dive');
  if (!dive) return;
  var max = +dive.dataset.max, px = +dive.dataset.px;
  var nEl = dive.querySelector('[data-depth]');
  var lvlEl = dive.querySelector('[data-level]');
  var timeEl = dive.querySelector('[data-time]');
  var bg = dive.querySelector('.dive-bg');
  var prog = dive.querySelector('[data-progress]');
  var xsec = document.getElementById('xsec');
  var diver = xsec && xsec.querySelector('.xsec-diver');
  var cards = Array.prototype.slice.call(dive.querySelectorAll('.dive-card'));
  // With JS, show one card at a time in a fixed slot (no overlapping cards when features are close together).
  var slot = dive.querySelector('.dive-slot');
  var nextEl = dive.querySelector('.dive-next');
  if (slot) {
    dive.classList.add('js-slot');
    cards.forEach(function (c) { slot.appendChild(c); });
  }
  var teachUl = dive.querySelector('[data-teach]');
  var teach = [];
  try { teach = JSON.parse(document.getElementById('teach-data').textContent) || []; } catch (e) {}

  if (teachUl) {
    teach.sort(function (a, b) { return b.d - a.d; });
    teachUl.innerHTML = teach.map(function (t) {
      return '<li data-d="' + t.d + '"><a href="/en/p/' + encodeURIComponent(t.s) + '">' + t.n.replace(/[<>&"]/g, '') + '</a></li>';
    }).join('');
  }
  var teachLis = teachUl ? Array.prototype.slice.call(teachUl.children) : [];

  // Water colour by depth (metres -> rgb).
  var stops = [[0, [103, 232, 249]], [6, [34, 211, 238]], [15, [8, 145, 178]], [30, [14, 79, 128]], [55, [11, 37, 69]], [100, [4, 12, 26]]];
  function colour(d) {
    for (var i = 1; i < stops.length; i++) {
      if (d <= stops[i][0]) {
        var a = stops[i - 1], b = stops[i], t = (d - a[0]) / (b[0] - a[0]);
        return 'rgb(' + [0, 1, 2].map(function (k) { return Math.round(a[1][k] + (b[1][k] - a[1][k]) * t); }).join(',') + ')';
      }
    }
    return 'rgb(4,12,26)';
  }
  function levelName(d) {
    if (d < 1) return 'Surface';
    if (d <= 10) return 'Try-dive depth';
    if (d <= 20) return 'Beginner course zone';
    if (d <= 30) return 'Intermediate zone';
    if (d <= 40) return 'Advanced zone';
    if (d <= 60) return 'Deep: coached training only';
    return 'Very deep: athletes with a safety team';
  }

  var top = +xsec.dataset.top, h = +xsec.dataset.h, ticking = false, last = -1;
  function update() {
    ticking = false;
    var r = dive.getBoundingClientRect();
    var d = Math.max(0, Math.min(max, -r.top / px));
    var whole = Math.round(d);
    if (whole === last) return;
    last = whole;
    nEl.textContent = whole;
    lvlEl.textContent = levelName(whole);
    timeEl.textContent = whole < 1 ? 'Take a breath. Scroll to dive.' : 'A dive to ' + whole + ' m takes about ' + Math.round(whole * 2 / 0.9 + 6) + ' s, down and back up.';
    bg.style.background = colour(d);
    if (diver) diver.style.top = ((d - top) / h * 100) + '%';
    if (prog) prog.style.width = (d / max * 100) + '%';
    var cur = 0;
    cards.forEach(function (c, i) { if (+c.dataset.d <= d + 0.5) cur = i; });
    cards.forEach(function (c, i) { c.classList.toggle('on', i === cur); });
    if (nextEl) {
      var nx = cards[cur + 1];
      nextEl.textContent = nx ? 'Next: ' + nx.querySelector('h3').textContent + ' at ' + nx.dataset.d + ' m ↓' : 'You reached the bottom of this dive. Scroll on ↓';
    }
    teachLis.forEach(function (li) { li.classList.toggle('out', +li.dataset.d < whole); });
  }
  function onScroll() { if (!ticking) { ticking = true; requestAnimationFrame(update); } }
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);
  update();
})();
