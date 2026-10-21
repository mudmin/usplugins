/*! Handrail boot: printed inline in <head>. Applies saved adjustments before first paint. */
(function (w, d) {
  var cfg = w.HandrailBoot, saved, id, step, i, needJs = false, any = false, root = d.documentElement;
  if (!cfg) return;
  try { saved = JSON.parse(w.localStorage.getItem('handrail-prefs') || 'null'); } catch (e) { saved = null; }
  if (!saved || typeof saved.t !== 'object') return;
  for (id in saved.t) {
    if (!Object.prototype.hasOwnProperty.call(saved.t, id)) continue;
    step = parseInt(saved.t[id], 10);
    if (!(step > 0) || !Object.prototype.hasOwnProperty.call(cfg.tools, id) || step > cfg.tools[id]) continue;
    root.className += ' handrail-' + id + '-' + step;
    any = true;
    if (cfg.jsTools.indexOf(id) !== -1) needJs = true;
  }
  if (!any) return;
  root.className += ' handrail-on';
  var nonce = cfg.nonce ? ' nonce="' + cfg.nonce + '"' : '';
  // document.write keeps these ahead of first paint, but only while the page is still being
  // parsed: run later (script delayers, PJAX themes) it would replace the whole page.
  if (d.readyState === 'loading') {
    for (i = 0; i < cfg.css.length; i++) d.write('<link rel="stylesheet" id="handrail-prefs-css-' + i + '" href="' + cfg.css[i] + '">');
    if (needJs) for (i = 0; i < cfg.js.length; i++) d.write('<script src="' + cfg.js[i] + '"' + nonce + '><\/script>');
  } else {
    for (i = 0; i < cfg.css.length; i++) {
      var l = d.createElement('link');
      l.rel = 'stylesheet'; l.id = 'handrail-prefs-css-' + i; l.href = cfg.css[i];
      d.head.appendChild(l);
    }
    if (needJs) for (i = 0; i < cfg.js.length; i++) {
      var s = d.createElement('script');
      s.src = cfg.js[i]; s.async = false; if (cfg.nonce) s.nonce = cfg.nonce;
      // button.js waits for scripts marked data-loading instead of assuming they've run.
      s.setAttribute('data-loading', '');
      s.onload = function () { this.removeAttribute('data-loading'); };
      s.onerror = function () { this.removeAttribute('data-loading'); this.setAttribute('data-failed', ''); };
      d.head.appendChild(s);
    }
  }
})(window, document);
