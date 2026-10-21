/*! Handrail prefs runtime: stores visitor adjustments and applies the ones CSS can't do alone. */
(function (w, d) {
  if (w.HandrailPrefs) return;

  var KEY = 'handrail-prefs';
  var cfg = w.HandrailBoot || { tools: {}, css: [], js: [], jsTools: [] };
  var runtimes = {};
  var current = read();

  function read() {
    var saved = null;
    try { saved = JSON.parse(w.localStorage.getItem(KEY) || 'null'); } catch (e) {}
    var out = {};
    if (saved && typeof saved.t === 'object') {
      for (var id in saved.t) {
        var step = parseInt(saved.t[id], 10);
        if (step > 0 && Object.prototype.hasOwnProperty.call(cfg.tools, id) && step <= cfg.tools[id]) out[id] = step;
      }
    }
    return out;
  }

  function write() {
    try {
      if (isEmpty(current)) w.localStorage.removeItem(KEY);
      else w.localStorage.setItem(KEY, JSON.stringify({ v: 1, t: current }));
    } catch (e) {}
  }

  function isEmpty(o) { for (var k in o) return false; return true; }

  function ensureCss() {
    for (var i = 0; i < cfg.css.length; i++) {
      if (d.getElementById('handrail-prefs-css-' + i)) continue;
      var l = d.createElement('link');
      l.rel = 'stylesheet';
      l.id = 'handrail-prefs-css-' + i;
      l.href = cfg.css[i];
      d.head.appendChild(l);
    }
  }

  function apply() {
    var root = d.documentElement;
    var keep = [];
    root.className.split(/\s+/).forEach(function (c) {
      if (c && !/^handrail-[a-z0-9-]+-\d+$/.test(c) && c !== 'handrail-on') keep.push(c);
    });
    for (var id in current) keep.push('handrail-' + id + '-' + current[id]);
    if (!isEmpty(current)) {
      keep.push('handrail-on');
      ensureCss();
    }
    root.className = keep.join(' ');
    for (var r in runtimes) {
      try {
        if (current[r]) runtimes[r].apply(current[r]);
        else runtimes[r].clear();
      } catch (e) {}
    }
    try { w.dispatchEvent(new CustomEvent('handrail:prefs', { detail: get() })); } catch (e) {}
  }

  function get() {
    var copy = {};
    for (var k in current) copy[k] = current[k];
    return copy;
  }

  function set(id, step) {
    step = parseInt(step, 10) || 0;
    if (step > 0 && Object.prototype.hasOwnProperty.call(cfg.tools, id) && step <= cfg.tools[id]) current[id] = step;
    else delete current[id];
    write();
    apply();
  }

  function setMany(map) {
    for (var id in map) {
      var step = parseInt(map[id], 10) || 0;
      if (step > 0 && Object.prototype.hasOwnProperty.call(cfg.tools, id) && step <= cfg.tools[id]) current[id] = step;
      else delete current[id];
    }
    write();
    apply();
  }

  function reset() {
    current = {};
    write();
    apply();
  }

  // A runtime is { apply(step), clear() } for tools that need script, not just CSS.
  function register(id, runtime) {
    runtimes[id] = runtime;
    if (current[id]) {
      try { runtime.apply(current[id]); } catch (e) {}
    }
  }

  // Other tabs on the same site change prefs too.
  w.addEventListener('storage', function (e) {
    if (e.key === KEY) {
      current = read();
      apply();
    }
  });

  w.HandrailPrefs = { get: get, set: set, setMany: setMany, reset: reset, register: register, apply: apply, ensureCss: ensureCss };

  // ---------------------------------------------------------------------------
  // Text size. CSS can't multiply font sizes it doesn't know, so every element that
  // holds text gets an inline size of (original size × factor). Originals are always
  // measured with all scaling removed, so nested em/% sizes never compound.

  var FACTORS = [1, 1.15, 1.3, 1.5, 1.75, 2];
  var TEXT_SKIP = { SCRIPT: 1, STYLE: 1, NOSCRIPT: 1, TEMPLATE: 1, HEAD: 1, TITLE: 1, META: 1, LINK: 1, BR: 1, IMG: 1, SVG: 1, PATH: 1, IFRAME: 1, VIDEO: 1, AUDIO: 1, CANVAS: 1, OBJECT: 1 };
  var FIELD = { INPUT: 1, SELECT: 1, TEXTAREA: 1, BUTTON: 1 };
  var factor = 1;
  var scaled = [];   // [{ el, fs, fsp, lh, lhp }] originals of the inline values we replaced
  var observer = null;
  var queued = false;

  function holdsText(el) {
    if (TEXT_SKIP[el.nodeName.toUpperCase()]) return false;
    if (FIELD[el.nodeName]) return true;
    for (var n = el.firstChild; n; n = n.nextSibling) {
      if (n.nodeType === 3 && /\S/.test(n.nodeValue)) return true;
    }
    return false;
  }

  function skip(el) {
    return el.id === 'handrail-root' || el.id === 'handrail-button-root' || el.id === 'wpadminbar' || (el.closest && el.closest('#wpadminbar'));
  }

  function unscaleAll() {
    for (var i = 0; i < scaled.length; i++) {
      var s = scaled[i];
      s.el.style.setProperty('font-size', s.fs, s.fsp);
      s.el.style.setProperty('line-height', s.lh, s.lhp);
    }
  }

  function rescale() {
    queued = false;
    if (factor === 1 || !d.body) return;
    unscaleAll();
    var live = [];
    for (var i = 0; i < scaled.length; i++) if (scaled[i].el.isConnected) live.push(scaled[i]);
    var known = new Set(live.map(function (s) { return s.el; }));
    var all = d.body.getElementsByTagName('*');
    for (var j = 0; j < all.length; j++) {
      var el = all[j];
      if (!known.has(el) && holdsText(el) && !skip(el)) {
        live.push({
          el: el,
          fs: el.style.getPropertyValue('font-size'), fsp: el.style.getPropertyPriority('font-size'),
          lh: el.style.getPropertyValue('line-height'), lhp: el.style.getPropertyPriority('line-height')
        });
      }
    }
    // Read everything first, then write, so the page reflows once.
    var sizes = live.map(function (s) {
      var cs = w.getComputedStyle(s.el);
      return { fs: parseFloat(cs.fontSize), lh: cs.lineHeight };
    });
    live.forEach(function (s, k) {
      var fs = sizes[k].fs;
      if (!(fs > 0)) return;
      s.el.style.setProperty('font-size', (fs * factor).toFixed(2) + 'px', 'important');
      // A fixed px line height would make bigger lines overlap; keep the same ratio instead.
      // Never !important: that would stop the visitor's own stylesheet changing it (WCAG 1.4.12).
      if (/px$/.test(sizes[k].lh)) {
        var ratio = parseFloat(sizes[k].lh) / fs;
        s.el.style.setProperty('line-height', Math.max(ratio, 1.2).toFixed(3));
      }
    });
    scaled = live;
  }

  // While the page loads, rescale every frame so text never shows at the old size. After that,
  // pages that change constantly (tickers, carousels) get at most one rescan every 400ms.
  function queue() {
    if (queued) return;
    queued = true;
    if (d.readyState === 'complete') setTimeout(rescale, 400);
    else (w.requestAnimationFrame || w.setTimeout)(rescale);
  }

  var resizeTimer = null;
  function onResize() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(queue, 150);
  }

  register('text-size', {
    apply: function (step) {
      var changed = factor !== (FACTORS[step] || 1);
      factor = FACTORS[step] || 1;
      if (changed && d.body) {
        // The visitor just changed it (or the page is loading): do it now, not after the throttle.
        queued = false;
        rescale();
      }
      if (!observer) {
        observer = new MutationObserver(function (records) {
          for (var i = 0; i < records.length; i++) {
            if (records[i].addedNodes.length) { queue(); return; }
          }
        });
        observer.observe(d.documentElement, { childList: true, subtree: true });
        w.addEventListener('resize', onResize);
        d.addEventListener('DOMContentLoaded', queue);
        w.addEventListener('load', queue);
      }
      queue();
    },
    clear: function () {
      factor = 1;
      if (observer) {
        observer.disconnect();
        observer = null;
        w.removeEventListener('resize', onResize);
      }
      unscaleAll();
      scaled = [];
    }
  });

  // ---------------------------------------------------------------------------
  // Stop animations: CSS stops CSS motion; video has to be paused from script.

  var paused = [];
  function pauseVideos() {
    var vids = d.querySelectorAll('video');
    for (var i = 0; i < vids.length; i++) {
      var v = vids[i];
      if (!v.paused && (v.autoplay || v.loop || v.muted)) {
        try { v.pause(); paused.push(v); } catch (e) {}
      }
      v.autoplay = false;
    }
  }
  register('motion', {
    apply: function () {
      if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', pauseVideos);
      else pauseVideos();
    },
    clear: function () {
      paused.forEach(function (v) { try { v.play(); } catch (e) {} });
      paused = [];
    }
  });

  apply();
})(window, document);
