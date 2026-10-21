/*! Handrail Pro runtime: read aloud, reading guide and reading mask. Loaded at boot when one of them is on. */
(function (w, d) {
  if (w.HandrailProRuntime) return;
  w.HandrailProRuntime = true;

  function whenPrefs(fn) {
    if (w.HandrailPrefs) fn(w.HandrailPrefs);
    else d.addEventListener('DOMContentLoaded', function () { if (w.HandrailPrefs) fn(w.HandrailPrefs); });
  }
  function onBody(fn) {
    if (d.body) fn();
    else d.addEventListener('DOMContentLoaded', fn);
  }
  var S = function (k, fallback) {
    var s = (w.HandrailConfig && w.HandrailConfig.strings) || {};
    return s[k] || fallback;
  };

  // ---------------------------------------------------------------- guide + mask
  // One fixed layer follows the pointer (or the focused element for keyboard users).

  var layer = null, mode = null, maskSize = 1, lastY = null;

  function ensureLayer() {
    if (layer) return layer;
    layer = d.createElement('div');
    layer.id = 'handrail-reading-layer';
    layer.setAttribute('aria-hidden', 'true');
    layer.innerHTML = '<div class="hr-guide"></div><div class="hr-mask-top"></div><div class="hr-mask-bottom"></div>';
    d.body.appendChild(layer);
    return layer;
  }

  function place(y) {
    if (!layer || y == null) return;
    lastY = y;
    if (mode === 'guide') {
      layer.firstChild.style.top = (y + 14) + 'px';
    } else if (mode === 'mask') {
      var half = maskSize === 2 ? 90 : 45;
      layer.children[1].style.height = Math.max(0, y - half) + 'px';
      layer.children[2].style.top = (y + half) + 'px';
    }
  }

  function onMove(e) {
    var y = e.touches ? e.touches[0].clientY : e.clientY;
    place(y);
  }
  function onFocus(e) {
    var t = e.target;
    if (!t || !t.getBoundingClientRect || t === d.body) return;
    var r = t.getBoundingClientRect();
    place(r.top + r.height / 2);
  }
  function onScroll() {
    if (lastY != null) place(lastY);
  }

  function startLayer(which, size) {
    onBody(function () {
      ensureLayer();
      mode = which;
      maskSize = size || 1;
      layer.className = 'hr-' + which;
      d.addEventListener('mousemove', onMove, { passive: true });
      d.addEventListener('touchmove', onMove, { passive: true });
      d.addEventListener('focusin', onFocus);
      w.addEventListener('scroll', onScroll, { passive: true });
      place(lastY != null ? lastY : w.innerHeight / 3);
    });
  }
  function stopLayer(which) {
    if (mode !== which) return;
    mode = null;
    if (layer) layer.className = '';
    d.removeEventListener('mousemove', onMove);
    d.removeEventListener('touchmove', onMove);
    d.removeEventListener('focusin', onFocus);
    w.removeEventListener('scroll', onScroll);
  }

  // ---------------------------------------------------------------- read aloud

  var synth = w.speechSynthesis;
  var bar = null, current = null, reading = false;
  // Each new reading gets a number; callbacks from a cancelled one see a stale number and do nothing.
  var generation = 0;
  var BLOCKS = 'p, li, h1, h2, h3, h4, h5, h6, td, th, dt, dd, blockquote, figcaption, label, caption, summary, legend';

  function voiceFor(lang) {
    var voices = synth.getVoices() || [];
    lang = (lang || d.documentElement.lang || 'en').toLowerCase();
    var base = lang.split('-')[0];
    return voices.filter(function (v) { return v.lang && v.lang.toLowerCase() === lang; })[0] ||
      voices.filter(function (v) { return v.lang && v.lang.toLowerCase().split('-')[0] === base; })[0] || null;
  }

  function mark(el) {
    if (current) current.classList.remove('hr-reading');
    current = el;
    if (el) el.classList.add('hr-reading');
  }

  // Chrome stops an utterance after about 15 seconds, so long blocks are spoken a sentence at a time.
  function sentences(text) {
    var parts = text.match(/[^.!?]+[.!?]+["')\]]*\s*|[^.!?]+$/g) || [text];
    var out = [], buf = '';
    parts.forEach(function (p) {
      if ((buf + p).length > 220 && buf) { out.push(buf); buf = ''; }
      buf += p;
    });
    if (buf.trim()) out.push(buf);
    return out;
  }

  function speakQueue(items) {
    var gen = ++generation;
    synth.cancel();
    reading = true;
    updateBar();
    var queue = [];
    items.forEach(function (item) {
      sentences(item.text.replace(/\s+/g, ' ').trim()).forEach(function (t) {
        if (t.trim()) queue.push({ el: item.el, text: t.trim(), lang: item.lang });
      });
    });
    var i = 0;
    function next() {
      if (gen !== generation) return;
      if (!reading || i >= queue.length) { stop(); return; }
      var item = queue[i++];
      var u = new SpeechSynthesisUtterance(item.text);
      var v = voiceFor(item.lang);
      if (v) u.voice = v;
      u.lang = item.lang || d.documentElement.lang || 'en';
      u.onend = next;
      u.onerror = next;
      mark(item.el);
      synth.speak(u);
    }
    next();
  }

  function langOf(el) {
    var l = el && el.closest ? el.closest('[lang]') : null;
    return l ? l.getAttribute('lang') : '';
  }

  function readEl(el) {
    speakQueue([{ el: el, text: el.innerText || el.textContent || '', lang: langOf(el) }]);
  }

  function readPage() {
    var main = d.querySelector('main, [role=main], #main, .entry-content, .wp-block-post-content') || d.body;
    var els = Array.prototype.filter.call(main.querySelectorAll(BLOCKS), function (el) {
      return el.offsetParent !== null && !el.closest('#handrail-root, #handrail-pro-bar, nav, [aria-hidden="true"]') && !el.parentElement.closest(BLOCKS);
    });
    speakQueue(els.map(function (el) { return { el: el, text: el.innerText || '', lang: langOf(el) }; }));
  }

  function stop() {
    generation++;
    reading = false;
    if (synth) synth.cancel();
    mark(null);
    updateBar();
  }

  function onClick(e) {
    if (e.target.closest('a, button, input, select, textarea, label, [role=button], [contenteditable], #handrail-root, #handrail-button-root, #handrail-pro-bar')) return;
    var sel = w.getSelection && String(w.getSelection());
    if (sel && sel.trim().length > 1) {
      speakQueue([{ el: null, text: sel, lang: langOf(e.target) }]);
      return;
    }
    var el = e.target.closest(BLOCKS);
    if (el) readEl(el);
  }

  function onKey(e) {
    if (e.key === 'Escape' && reading) stop();
  }

  function updateBar() {
    if (!bar) return;
    bar.querySelector('[data-act=page]').hidden = reading;
    bar.querySelector('[data-act=stop]').hidden = !reading;
  }

  function showBar() {
    onBody(function () {
      if (bar) { bar.hidden = false; return; }
      bar = d.createElement('div');
      bar.id = 'handrail-pro-bar';
      bar.setAttribute('role', 'region');
      bar.setAttribute('aria-label', S('readAloud', 'Read aloud'));
      var ok = !!synth;
      bar.innerHTML = ok
        ? '<span class="hr-bar-hint">' + esc(S('readAloudHint', 'Click any text to hear it.')) + '</span>' +
          '<button type="button" data-act="page">' + esc(S('readPage', 'Read this page')) + '</button>' +
          '<button type="button" data-act="stop" hidden>' + esc(S('readStop', 'Stop reading')) + '</button>'
        : '<span class="hr-bar-hint">' + esc(S('readNoVoices', "Read aloud isn't available in this browser.")) + '</span>';
      bar.addEventListener('click', function (e) {
        var b = e.target.closest('button');
        if (!b) return;
        if (b.getAttribute('data-act') === 'page') readPage();
        else stop();
      });
      d.body.appendChild(bar);
      updateBar();
    });
  }

  function esc(s) {
    return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; });
  }

  whenPrefs(function (P) {
    P.register('guide', {
      apply: function () { stopLayer('mask'); startLayer('guide'); },
      clear: function () { stopLayer('guide'); }
    });
    P.register('mask', {
      apply: function (step) { stopLayer('guide'); startLayer('mask', step); },
      clear: function () { stopLayer('mask'); }
    });
    P.register('read-aloud', {
      apply: function () {
        showBar();
        d.addEventListener('click', onClick);
        d.addEventListener('keydown', onKey);
      },
      clear: function () {
        stop();
        if (bar) bar.hidden = true;
        d.removeEventListener('click', onClick);
        d.removeEventListener('keydown', onKey);
      }
    });
  });
})(window, document);
