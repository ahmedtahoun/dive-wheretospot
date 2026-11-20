// Spots map (Leaflet). The list next to it works without the map.
(function () {
  'use strict';
  var el = document.getElementById('map');
  if (!el || !window.L) return;
  var pins = [];
  try { pins = JSON.parse(document.getElementById('pins').textContent) || []; } catch (e) {}
  var map = L.map(el, { scrollWheelZoom: false, zoomControl: true }).setView([28.50, 34.50], 11);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 17, attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);
  var bounds = [];
  var markers = {};
  pins.forEach(function (p) {
    var icon = L.divIcon({ className: '', html: '<span class="pin ' + p.st + '"></span>', iconSize: [16, 16], iconAnchor: [8, 8] });
    var m = L.marker([p.lat, p.lon], { icon: icon, title: p.n, keyboard: true }).addTo(map);
    var safe = function (s) { return String(s).replace(/[<>&"]/g, ''); };
    m.bindPopup('<b>' + safe(p.n) + '</b><br>' + safe(p.d) + ' · ' + safe(p.stl) + '<br><a href="/en/spots/' + encodeURIComponent(p.k) + '">Open the dive →</a>');
    m.on('mouseover', function () { hl(p.k, true); });
    m.on('mouseout', function () { hl(p.k, false); });
    markers[p.k] = m;
    bounds.push([p.lat, p.lon]);
  });
  if (bounds.length) map.fitBounds(bounds, { padding: [30, 30] });
  function hl(k, on) {
    var row = document.querySelector('[data-spot="' + k + '"]');
    if (row) row.classList.toggle('is-hl', on);
  }
  Array.prototype.forEach.call(document.querySelectorAll('[data-spot]'), function (row) {
    row.addEventListener('mouseenter', function () { var m = markers[row.dataset.spot]; if (m) m.openPopup(); });
  });
})();
