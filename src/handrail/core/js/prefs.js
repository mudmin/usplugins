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
  //
  // The inline values we replaced live on the element itself, in data-handrail-text, not
  // in a list here: ad and comment scripts clone nodes or rebuild them from innerHTML, and
  // a copy that arrived with our scaled size and no record of the original would be scaled
  // again on every rescan. The attribute is "size|line-height" as we wrote them, then, only
  // when the element had inline values of its own, "|" and those as JSON.

  var FACTORS = [1, 1.15, 1.3, 1.5, 1.75, 2];
  var TEXT_SKIP = { SCRIPT: 1, STYLE: 1, NOSCRIPT: 1, TEMPLATE: 1, HEAD: 1, TITLE: 1, META: 1, LINK: 1, BR: 1, IMG: 1, SVG: 1, PATH: 1, IFRAME: 1, VIDEO: 1, AUDIO: 1, CANVAS: 1, OBJECT: 1 };
  var FIELD = { INPUT: 1, SELECT: 1, TEXTAREA: 1, BUTTON: 1 };
  var MARK = 'data-handrail-text';
  var factor = 1;
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

  // Puts back the inline values we replaced, unless the page has changed them since: then
  // the page's value is the new original.
  function unscaleAll() {
    var els = d.querySelectorAll('[' + MARK + ']');
    for (var i = 0; i < els.length; i++) {
      var el = els[i], v = el.getAttribute(MARK), a = v.indexOf('|'), b = v.indexOf('|', a + 1);
      var o = { fs: '', fsp: '', lh: '', lhp: '' };
      if (b !== -1) { try { o = JSON.parse(v.slice(b + 1)); } catch (e) {} }
      el.removeAttribute(MARK);
      if (a === -1) continue;
      if (el.style.getPropertyValue('font-size') === v.slice(0, a)) el.style.setProperty('font-size', o.fs, o.fsp);
      if (el.style.getPropertyValue('line-height') === (b === -1 ? v.slice(a + 1) : v.slice(a + 1, b))) el.style.setProperty('line-height', o.lh, o.lhp);
    }
  }

  function rescale() {
    queued = false;
    if (factor === 1 || !d.body) return;
    unscaleAll();
    var live = [];
    var all = d.body.getElementsByTagName('*');
    for (var j = 0; j < all.length; j++) {
      if (holdsText(all[j]) && !skip(all[j])) live.push(all[j]);
    }
    // Read everything first, then write, so the page reflows once.
    var sizes = live.map(function (el) {
      var cs = w.getComputedStyle(el);
      return { fs: parseFloat(cs.fontSize), lh: cs.lineHeight };
    });
    live.forEach(function (el, k) {
      var fs = sizes[k].fs;
      if (!(fs > 0)) return;
      var o = {
        fs: el.style.getPropertyValue('font-size'), fsp: el.style.getPropertyPriority('font-size'),
        lh: el.style.getPropertyValue('line-height'), lhp: el.style.getPropertyPriority('line-height')
      };
      var own = o.fs || o.lh;
      el.style.setProperty('font-size', (fs * factor).toFixed(2) + 'px', 'important');
      // A fixed px line height would make bigger lines overlap; keep the same ratio instead.
      // Never !important: that would stop the visitor's own stylesheet changing it (WCAG 1.4.12).
      if (/px$/.test(sizes[k].lh)) {
        var ratio = parseFloat(sizes[k].lh) / fs;
        el.style.setProperty('line-height', Math.max(ratio, 1.2).toFixed(3));
      }
      // What we wrote, as the browser spells it, so unscaleAll can tell if the page changed it.
      el.setAttribute(MARK, el.style.getPropertyValue('font-size') + '|' + el.style.getPropertyValue('line-height') + (own ? '|' + JSON.stringify(o) : ''));
    });
    // Our own style writes aren't the page changing: contrast needn't look at them.
    if (bgObserver) bgObserver.takeRecords();
  }

  // Ads insert scripts, iframes and tracking pixels all the time; only text is worth a rescan.
  function addsText(records) {
    for (var i = 0; i < records.length; i++) {
      var added = records[i].addedNodes;
      for (var j = 0; j < added.length; j++) {
        var n = added[j];
        if (n.nodeType === 3 ? /\S/.test(n.nodeValue) : (n.nodeType === 1 && !TEXT_SKIP[n.nodeName.toUpperCase()])) return true;
      }
    }
    return false;
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
          if (addsText(records)) queue();
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
    }
  });

  // ---------------------------------------------------------------------------
  // Contrast. The CSS paints every element's background dark (or white), which also turns
  // the invisible boxes ads and overlays stretch over the page into solid ones covering it.
  // This marks those data-handrail-clear, and the CSS leaves their background alone: an
  // element positioned fixed or absolute whose own background is see-through, and the
  // see-through elements inside one. Without this script the page still reads correctly;
  // it just keeps those boxes.

  var CLEAR = 'data-handrail-clear';
  var MEASURE = 'data-handrail-measure';  // the CSS skips it, so the page's own background shows
  var BG_SKIP = { IMG: 1, PICTURE: 1, VIDEO: 1, CANVAS: 1, IFRAME: 1, SVG: 1, SCRIPT: 1, STYLE: 1, LINK: 1, META: 1, NOSCRIPT: 1, TEMPLATE: 1, BR: 1 };
  var bgObserver = null;
  var bgRoots = [];
  var bgQueued = false;
  var fullTimer = null;

  // A background color's opacity, 0 to 1: rgba(r, g, b, a), or "… / a)" in modern syntax.
  function alpha(c) {
    if (!c || c === 'transparent') return 0;
    var m = /^rgba\([^,]+,[^,]+,[^,]+,\s*([\d.]+)\)$/.exec(c) || /\/\s*([\d.]+%?)\s*\)$/.exec(c);
    return m ? parseFloat(m[1]) / (/%$/.test(m[1]) ? 100 : 1) : 1;
  }

  // Faint tints count as see-through too: painting them solid is how an overlay becomes a box.
  function seeThrough(el) {
    if (el.hasAttribute(CLEAR)) return alpha(w.getComputedStyle(el).backgroundColor) < 0.5;
    el.setAttribute(MEASURE, '');
    var a = alpha(w.getComputedStyle(el).backgroundColor);
    el.removeAttribute(MEASURE);
    return a < 0.5;
  }

  function checkable(el) {
    return !BG_SKIP[el.nodeName.toUpperCase()] && !(el instanceof SVGElement) && !skip(el);
  }

  // Re-decides every element in the roots' subtrees, in document order so a new overlay is
  // marked before the elements inside it are looked at.
  function markClear(roots) {
    var set = new Set(roots);
    var top = roots.filter(function (r) {
      for (var p = r.parentElement; p; p = p.parentElement) if (set.has(p)) return false;
      return true;
    });
    var any = !!d.querySelector('[' + CLEAR + ']');
    top.forEach(function (root) {
      var all = [root], sub = root.getElementsByTagName('*');
      for (var i = 0; i < sub.length; i++) all.push(sub[i]);
      all.forEach(function (el) {
        if (!checkable(el)) return;
        var pos = w.getComputedStyle(el).position;
        var inside = any && el.parentElement && el.parentElement.closest('[' + CLEAR + ']');
        var want = (pos === 'fixed' || pos === 'absolute' || inside) && seeThrough(el);
        if (want !== el.hasAttribute(CLEAR)) {
          if (want) { el.setAttribute(CLEAR, ''); any = true; } else el.removeAttribute(CLEAR);
        }
      });
    });
  }

  function flushBg() {
    bgQueued = false;
    var roots = bgRoots;
    bgRoots = [];
    if (!d.body) return;
    markClear(roots.filter(function (r) { return r.isConnected && (r === d.body || d.body.contains(r)); }));
  }

  function queueBg(root) {
    // A flood of changes (a big rebuild): one pass over the whole page is cheaper.
    if (bgRoots.length > 500) bgRoots = [d.body || root];
    bgRoots.push(root);
    if (bgQueued) return;
    bgQueued = true;
    // Before the next paint where possible; a hidden tab gets no animation frames.
    if (d.hidden || !w.requestAnimationFrame) setTimeout(flushBg, 100);
    else w.requestAnimationFrame(flushBg);
  }

  // Whole-page passes (a class on <html> or <body>, a resize, a late stylesheet) wait for
  // things to settle: themes toggle body classes while scrolling.
  function rescanBg() {
    clearTimeout(fullTimer);
    fullTimer = setTimeout(function () { if (d.body) queueBg(d.body); }, 300);
  }

  register('contrast', {
    apply: function () {
      if (bgObserver) return;
      bgObserver = new MutationObserver(function (records) {
        for (var i = 0; i < records.length; i++) {
          var r = records[i];
          if (r.type === 'attributes') {
            if (r.target === d.body || r.target === d.documentElement) rescanBg();
            else queueBg(r.target);
            continue;
          }
          for (var j = 0; j < r.addedNodes.length; j++) {
            var n = r.addedNodes[j];
            if (n.nodeType !== 1) continue;
            if (n.nodeName === 'LINK' || n.nodeName === 'STYLE') {
              n.addEventListener('load', rescanBg);
              rescanBg();
            } else {
              queueBg(n);
            }
          }
        }
      });
      bgObserver.observe(d.documentElement, { childList: true, subtree: true, attributes: true, attributeFilter: ['class', 'style'] });
      d.addEventListener('DOMContentLoaded', rescanBg);
      w.addEventListener('load', rescanBg);
      w.addEventListener('resize', rescanBg);
      if (d.body) markClear([d.body]);
    },
    clear: function () {
      if (bgObserver) {
        bgObserver.disconnect();
        bgObserver = null;
      }
      clearTimeout(fullTimer);
      d.removeEventListener('DOMContentLoaded', rescanBg);
      w.removeEventListener('load', rescanBg);
      w.removeEventListener('resize', rescanBg);
      // The marks do nothing with contrast off; turning it back on re-decides them all.
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
