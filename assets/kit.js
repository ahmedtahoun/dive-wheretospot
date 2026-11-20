// Share kit: draws the Instagram story card on a canvas, plus copy buttons.
(function () {
  'use strict';
  var data = {};
  try { data = JSON.parse(document.getElementById('card-data').textContent) || {}; } catch (e) {}
  var cv = document.getElementById('story');

  function wrap(ctx, text, x, y, maxW, lh) {
    var words = String(text).split(' '), line = '', lines = 0;
    for (var i = 0; i < words.length; i++) {
      var t = line + words[i] + ' ';
      if (ctx.measureText(t).width > maxW && line) { ctx.fillText(line.trim(), x, y); line = words[i] + ' '; y += lh; lines++; }
      else line = t;
    }
    ctx.fillText(line.trim(), x, y);
    return y + lh;
  }

  function draw() {
    if (!cv) return;
    var c = cv.getContext('2d'), W = 1080, H = 1920;
    var g = c.createLinearGradient(0, 0, 0, H);
    g.addColorStop(0, '#22d3ee'); g.addColorStop(.35, '#0e7490'); g.addColorStop(.75, '#0b2545'); g.addColorStop(1, '#040c1a');
    c.fillStyle = g; c.fillRect(0, 0, W, H);
    // light rays
    c.globalAlpha = .07; c.fillStyle = '#fff';
    for (var i = 0; i < 6; i++) { c.beginPath(); c.moveTo(120 + i * 170, 0); c.lineTo(220 + i * 170, 0); c.lineTo(i * 220 - 200, H); c.lineTo(i * 220 - 320, H); c.fill(); }
    c.globalAlpha = 1;
    // freediver line
    c.strokeStyle = 'rgba(253,230,138,.8)'; c.lineWidth = 4; c.beginPath(); c.moveTo(900, 0); c.lineTo(900, H); c.stroke();
    c.fillStyle = '#fde68a'; c.beginPath(); c.arc(900, 1180, 18, 0, Math.PI * 2); c.fill();
    var font = function (w, s) { return w + ' ' + s + 'px "Space Grotesk", Inter, Arial, sans-serif'; };
    c.fillStyle = '#ecfeff'; c.font = font(600, 40); c.fillText('THIS WEEK IN DAHAB', 90, 200);
    c.fillStyle = '#fff'; c.font = font(700, 96);
    var y = wrap(c, data.name || '', 90, 330, 760, 104);
    c.font = font(500, 42); c.fillStyle = '#cffafe'; c.fillText((data.type || '') + (data.verified ? '  ·  ✓ Verified' : ''), 90, y + 10);
    y += 120;
    c.fillStyle = 'rgba(255,255,255,.12)'; roundRect(c, 70, y - 70, 760, 150, 30); c.fill();
    c.fillStyle = '#fff'; c.font = font(600, 46); wrap(c, data.note || '', 100, y + 20, 700, 54);
    y += 190;
    (data.items || []).forEach(function (it) {
      c.font = font(500, 44); c.fillStyle = '#e0f2fe'; var ny = wrap(c, it[0], 90, y, 560, 50);
      c.font = font(700, 50); c.fillStyle = '#fde68a'; c.fillText(it[1], 680, y);
      y = Math.max(ny, y + 50) + 34;
    });
    if (data.spots && data.spots.length) { c.font = font(500, 38); c.fillStyle = '#a5f3fc'; c.fillText('📍 ' + data.spots.join(' · '), 90, y + 20); }
    c.fillStyle = '#fff'; c.font = font(700, 48); c.fillText('Book: ' + (data.url || ''), 90, H - 220);
    c.font = font(500, 34); c.fillStyle = '#94a3b8'; c.fillText('Where To Spot · Dive Dahab', 90, H - 160);
  }
  function roundRect(c, x, y, w, h, r) { c.beginPath(); c.moveTo(x + r, y); c.arcTo(x + w, y, x + w, y + h, r); c.arcTo(x + w, y + h, x, y + h, r); c.arcTo(x, y + h, x, y, r); c.arcTo(x, y, x + w, y, r); c.closePath(); }

  if (document.fonts && document.fonts.ready) document.fonts.ready.then(draw); else draw();
  draw();

  var dl = document.querySelector('[data-download]');
  if (dl && cv) dl.addEventListener('click', function () {
    var a = document.createElement('a');
    a.download = 'where-to-spot-story.png';
    a.href = cv.toDataURL('image/png');
    a.click();
  });
  function copy(text, btn) {
    var done = function () { var o = btn.textContent; btn.textContent = 'Copied ✓'; setTimeout(function () { btn.textContent = o; }, 1500); };
    if (navigator.clipboard) navigator.clipboard.writeText(text).then(done, function () {});
    else { var t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); try { document.execCommand('copy'); done(); } catch (e) {} t.remove(); }
  }
  Array.prototype.forEach.call(document.querySelectorAll('[data-copy]'), function (b) { b.addEventListener('click', function () { copy(b.dataset.copy, b); }); });
  Array.prototype.forEach.call(document.querySelectorAll('[data-copyfrom]'), function (b) {
    b.addEventListener('click', function () { var t = b.previousElementSibling.querySelector('textarea'); copy(t.value, b); });
  });
})();
