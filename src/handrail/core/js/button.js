/*! Handrail trigger: the floating button and the #handrail links. Everything else loads on first open. */
(function (w, d) {
  var cfg = w.HandrailConfig;
  if (!cfg || w.Handrail) return;

  var loading = null;
  var lastTrigger = null;

  function loadScript(src) {
    return new Promise(function (resolve, reject) {
      // The boot script may already have loaded it.
      var have = d.querySelectorAll('script[src]');
      for (var i = 0; i < have.length; i++) {
        if (have[i].getAttribute('src') !== src) continue;
        if (have[i].hasAttribute('data-failed')) { have[i].parentNode.removeChild(have[i]); break; }
        if (!have[i].hasAttribute('data-loading')) return resolve();
        // Still loading (added by the boot script): wait for it rather than loading it twice.
        have[i].addEventListener('load', function () { resolve(); });
        have[i].addEventListener('error', function () { reject(new Error('Could not load ' + src)); });
        return;
      }
      var s = d.createElement('script');
      s.src = src;
      s.setAttribute('data-loading', '');
      if (cfg.nonce) s.nonce = cfg.nonce;
      s.onload = function () { s.removeAttribute('data-loading'); resolve(); };
      // Remove a failed script so the next click tries again instead of waiting on it forever.
      s.onerror = function () { if (s.parentNode) s.parentNode.removeChild(s); reject(new Error('Could not load ' + src)); };
      d.head.appendChild(s);
    });
  }

  function loadPanel() {
    if (loading) return loading;
    var chain = w.HandrailPrefs ? Promise.resolve() : loadScript(cfg.base + 'js/prefs.js?ver=' + cfg.version);
    (cfg.panelScripts || []).forEach(function (src) {
      chain = chain.then(function () { return loadScript(src); });
    });
    loading = chain.then(function () { return loadScript(cfg.base + 'js/panel.js?ver=' + cfg.version); });
    loading.catch(function () { loading = null; });
    return loading;
  }

  function open(tab, trigger) {
    lastTrigger = trigger || d.activeElement;
    return loadPanel().then(function () { w.HandrailPanel.open(tab, lastTrigger); });
  }

  w.Handrail = {
    config: cfg,
    icons: {},
    open: open,
    close: function () { if (w.HandrailPanel) w.HandrailPanel.close(); },
    loadScript: loadScript
  };

  // The button and panel icon the site chose (core/icons.json holds the same markup for PHP).
  // person: the accessibility figure; handrail: a curved rail on two posts.
  var GLYPHS = {
    'person': '<circle cx="12" cy="3.9" r="2.4" fill="currentColor" stroke="none"/><path d="M3.5 8.3l8.5 1.9 8.5-1.9"/><path d="M12 10.2v4.8"/><path d="M12 15l-4 6.5"/><path d="M12 15l4 6.5"/>',
    'person-circle': '<circle cx="12" cy="12" r="10.4" stroke-width="1.8"/><circle cx="12" cy="6.6" r="1.6" fill="currentColor" stroke="none"/><path d="M6.6 9.6l5.4 1.2 5.4-1.2" stroke-width="1.9"/><path d="M12 10.8v3.4" stroke-width="1.9"/><path d="M12 14.2l-2.6 4.2M12 14.2l2.6 4.2" stroke-width="1.9"/>',
    'handrail': '<path d="M3 10c5-5 13-5 18 0"/><path d="M7 7.2V20"/><path d="M17 7.2V20"/><path d="M3 20h18"/>',
    'sliders': '<path d="M4 6h8"/><path d="M16 6h4"/><circle cx="14" cy="6" r="2"/><path d="M4 12h2"/><path d="M10 12h10"/><circle cx="8" cy="12" r="2"/><path d="M4 18h10"/><path d="M18 18h2"/><circle cx="16" cy="18" r="2"/>',
    'eye': '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="3"/>'
  };
  function svg(inner, size) {
    return '<svg viewBox="0 0 24 24" width="' + size + '" height="' + size + '" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' + inner + '</svg>';
  }
  for (var g in GLYPHS) w.Handrail.icons[g] = svg(GLYPHS[g], 26);
  w.Handrail.icon = w.Handrail.icons[Object.prototype.hasOwnProperty.call(GLYPHS, cfg.icon) ? cfg.icon : 'person'];
  var ICON = w.Handrail.icon;

  // True when an adjustment has color-inverted the whole page (our UI flips itself back).
  w.Handrail.pageInverted = function () {
    var f = w.getComputedStyle(d.documentElement).filter || '';
    return f.indexOf('invert') !== -1;
  };

  // Any link to #handrail (or #handrail-report, #handrail-check) opens the panel.
  // Without JS it still goes where it points: the accessibility statement.
  var LINKS = 'a[href$="#handrail"], a[href$="#handrail-adjust"], a[href$="#handrail-report"], a[href$="#handrail-check"], [data-handrail-open]';
  function tabFrom(hash) {
    var m = /#handrail-(adjust|report|check)$/.exec(hash || '');
    return m ? m[1] : null;
  }
  function upgradeLinks(scope) {
    var links = (scope || d).querySelectorAll(LINKS);
    for (var i = 0; i < links.length; i++) {
      links[i].setAttribute('aria-haspopup', 'dialog');
    }
  }

  d.addEventListener('click', function (e) {
    var t = e.target && e.target.closest ? e.target.closest(LINKS) : null;
    if (!t || e.defaultPrevented || e.ctrlKey || e.metaKey || e.shiftKey || e.button > 0) return;
    e.preventDefault();
    open(t.getAttribute('data-handrail-open') || tabFrom(t.getAttribute('href')), t);
  });

  function floating() {
    var f = cfg.placement && cfg.placement.floating;
    if (!f) return;
    var host = d.createElement('div');
    host.id = 'handrail-button-root';
    var root = host.attachShadow({ mode: 'open' });
    var side = f.corner.charAt(1) === 'l' ? 'left' : 'right';
    var edge = f.corner.charAt(0) === 't' ? 'top' : 'bottom';
    var size = f.size === 'large' ? 60 : (f.size === 'small' ? 44 : 52);
    var ox = isFinite(parseInt(f.offsetX, 10)) ? parseInt(f.offsetX, 10) : 16;
    var oy = isFinite(parseInt(f.offsetY, 10)) ? parseInt(f.offsetY, 10) : 16;
    var accent = cfg.brand && cfg.brand.accent ? cfg.brand.accent : '#0b6e69';
    var accentText = cfg.brand && cfg.brand.accentText ? cfg.brand.accentText : '#ffffff';
    // icon: round button; text: pill with the word; tab: flat on the side edge; handle: tab at the bottom.
    var look = ['icon', 'text', 'tab', 'handle'].indexOf(f.label) !== -1 ? f.label : 'icon';
    var withLabel = look !== 'icon';
    var shape;
    if (look === 'tab') {
      shape = 'top:50%;' + side + ':0;transform:translateY(-50%);flex-direction:column;gap:10px;width:' + size + 'px;min-height:' + (size * 3) + 'px;padding:14px 0;' +
        'border-' + side + ':0;border-radius:' + (side === 'right' ? '12px 0 0 12px' : '0 12px 12px 0') + ';';
    } else if (look === 'handle') {
      shape = 'bottom:0;left:50%;transform:translateX(-50%);height:' + Math.max(44, size - 6) + 'px;padding:0 18px;border-bottom:0;border-radius:12px 12px 0 0;';
    } else {
      shape = edge + ':' + oy + 'px;' + side + ':' + ox + 'px;min-width:' + size + 'px;height:' + size + 'px;padding:0 ' + (withLabel ? '20px 0 14px' : '0') + ';border-radius:' + size + 'px;';
    }
    root.innerHTML =
      '<style>' +
      ':host{all:initial}' +
      'button{position:fixed;z-index:2147483000;' + shape +
      'display:inline-flex;align-items:center;justify-content:center;gap:8px;' +
      'border:2px solid ' + accentText + ';background:' + accent + ';color:' + accentText + ';cursor:pointer;' +
      'box-shadow:0 2px 10px rgba(0,0,0,.35);font:600 16px/1 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}' +
      (look === 'tab' ? 'button span{writing-mode:vertical-rl;transform:rotate(180deg)}' : '') +
      'button:hover{filter:brightness(1.12)}' +
      ':host([data-invert]) button{filter:invert(1) hue-rotate(180deg)}' +
      'button:focus-visible{outline:3px solid ' + accent + ';outline-offset:3px;box-shadow:0 0 0 6px ' + accentText + '}' +
      '@media print{button{display:none}}' +
      (f.hideOnMobile ? '@media (max-width:600px){button{display:none}}' : '') +
      '</style>' +
      '<button type="button" aria-haspopup="dialog"' + (withLabel ? '' : ' aria-label="' + esc(cfg.strings.open) + '"') + '>' +
      ICON + (withLabel ? '<span>' + esc(cfg.strings.open) + '</span>' : '') +
      (look === 'handle' ? '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6 15l6-6 6 6"/></svg>' : '') +
      '</button>';
    var btn = root.querySelector('button');
    btn.addEventListener('click', function () { open(null, btn); });
    // Near the top of the page, so screen readers and the Tab key reach it first instead of
    // last. It's position:fixed, so this doesn't move it on screen. A skip link that is a direct
    // child of <body> stays first; we never go inside a wrapper, since one with a transform
    // would break position:fixed.
    // No box of its own, so it never takes a row in a page that lays out <body> as a grid.
    host.style.display = 'contents';
    var skip = d.body.querySelector('a[href], button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (skip && skip.parentNode === d.body && /^#./.test(skip.getAttribute('href') || '')) d.body.insertBefore(host, skip.nextSibling);
    else d.body.insertBefore(host, d.body.firstChild);
    var sync = function () { host.toggleAttribute('data-invert', w.Handrail.pageInverted()); };
    sync();
    w.addEventListener('handrail:prefs', sync);
    w.addEventListener('load', sync);
  }

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  w.Handrail.esc = esc;

  function ready() {
    upgradeLinks();
    floating();
    if (/^#handrail(-(adjust|report|check))?$/.test(w.location.hash)) open(tabFrom(w.location.hash));
  }
  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', ready);
  else ready();
})(window, document);
