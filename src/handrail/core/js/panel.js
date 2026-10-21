/*! Handrail panel: the dialog, its tabs, the adjustments and the report form. */
(function (w, d) {
  if (w.HandrailPanel) return;

  var H = w.Handrail;
  var cfg = H.config;
  var P = w.HandrailPrefs;

  var DEFAULTS = {
    title: 'Accessibility',
    close: 'Close',
    tabAdjust: 'Adjust the page',
    tabReport: 'Report a problem',
    tabCheck: 'Check this page',
    adjustIntro: 'Change how this site looks and reads for you. Your choices are saved in this browser only.',
    quick: 'Quick settings',
    off: 'Off',
    on: 'On',
    resetAll: 'Reset all',
    resetDone: 'All adjustments are off.',
    stillHard: 'Still hard to use?',
    reportLink: 'Report a problem with this page',
    statement: 'Accessibility statement',
    credit: 'Handrail by MakeWPCompliant',
    disclaimer: "These tools make this site easier to read and use. They don't change how the site is built, and they are not a certification or guarantee of accessibility.",
    reportIntro: "Tell us what got in your way on this page. Someone who looks after this site will read it. Your report includes this page's address, your browser and screen size, and the adjustments you have on.",
    reportWhat: 'What went wrong?',
    reportComments: 'Tell us more',
    reportCommentsHint: 'What were you trying to do? Which part of the page?',
    reportContact: 'Your email',
    reportContactHint: 'Optional. Only if you would like a reply.',
    reportSend: 'Send report',
    reportSending: 'Sending…',
    reportThanks: 'Thank you. Your report was sent.',
    reportError: 'Sorry, the report could not be sent. Please try again later.',
    reportNeedSomething: 'Choose at least one problem or tell us what happened.',
    reportBadEmail: 'That email address does not look right. Fix it or leave it blank.',
    reportCaptcha: 'Please complete the check before sending.',
    reportDone: 'Done',
    reportAnother: 'Send another report',
    loading: 'Loading…',
    checkLoadError: 'The checker could not be loaded.',
    back: 'Back',
    start: 'Start',
    pickIntro: 'Pick what sounds like you, then fine-tune it in the other tabs. Your choices are saved in this browser only.',
    lessOf: 'Decrease {tool}',
    moreOf: 'Increase {tool}'
  };
  var S = {};
  for (var k in DEFAULTS) S[k] = DEFAULTS[k];
  for (k in (cfg.strings || {})) S[k] = cfg.strings[k];
  H.S = S;

  function icon(paths) {
    return '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' + paths + '</svg>';
  }
  var ICONS = {
    'text-size': icon('<path d="M2 19l4-10 4 10M3.5 15.5h5M12 19l5.5-15L23 19M14 14h7"/>'),
    contrast: icon('<circle cx="12" cy="12" r="9"/><path d="M12 3a9 9 0 0 1 0 18z" fill="currentColor"/>'),
    font: icon('<path d="M4 6h11M9.5 6v13M7 19h5M15 12h6M18 12v7M16.5 19h3"/>'),
    'line-height': icon('<path d="M11 6h10M11 12h10M11 18h10M5 4v16M3 6l2-2 2 2M3 18l2 2 2-2"/>'),
    spacing: icon('<path d="M3 12h18M6 9l-3 3 3 3M18 9l3 3-3 3M8 5h8M8 19h8"/>'),
    links: icon('<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>'),
    focus: icon('<path d="M4 9V5a1 1 0 0 1 1-1h4M15 4h4a1 1 0 0 1 1 1v4M20 15v4a1 1 0 0 1-1 1h-4M9 20H5a1 1 0 0 1-1-1v-4"/><circle cx="12" cy="12" r="3"/>'),
    motion: icon('<circle cx="12" cy="12" r="9"/><path d="M10 8.5v7M14 8.5v7"/>')
  };
  for (var ic in ICONS) if (!H.icons[ic]) H.icons[ic] = ICONS[ic];
  H.iconSvg = icon;

  var host, root, sheet, live, tabs = {}, panels = {}, content = {}, current = null, trigger = null, built = false, cssReady = null;
  var headExtra = null, footActions = null;
  // Every control that shows a tool's or preset's state registers here; syncState feeds them.
  var toolViews = {}, presetViews = {};

  // Panel styles: a settings sheet (default), the classic tile grid, and (Pro) a floating card or a dock.
  var STYLES = ['sheet', 'tiles', 'card', 'dock'];
  var style = STYLES.indexOf(cfg.style) !== -1 ? cfg.style : 'sheet';
  var tiles = style === 'tiles';

  // ---------------------------------------------------------------- helpers

  function el(tag, attrs, kids) {
    var n = d.createElement(tag);
    if (attrs) {
      for (var a in attrs) {
        var v = attrs[a];
        if (v == null || v === false) continue;
        if (a === 'text') n.textContent = v;
        else if (a === 'html') n.innerHTML = v;
        else if (a === 'className') n.className = v;
        else if (a.slice(0, 2) === 'on') n.addEventListener(a.slice(2), v);
        else n.setAttribute(a, v === true ? '' : v);
      }
    }
    (kids || []).forEach(function (c) {
      if (c == null) return;
      n.appendChild(typeof c === 'string' ? d.createTextNode(c) : c);
    });
    return n;
  }
  H.el = el;

  function announce(msg) {
    live.textContent = '';
    setTimeout(function () { live.textContent = msg; }, 30);
  }

  function focusables(scope) {
    var list = (scope || sheet).querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    return Array.prototype.filter.call(list, function (n) {
      return !n.disabled && n.offsetParent !== null && !n.closest('[hidden]');
    });
  }

  var tabOrder = ['adjust'];
  if (cfg.mode && cfg.mode.report) tabOrder.push('report');
  if (cfg.mode && cfg.mode.check) tabOrder.push('check');

  // ---------------------------------------------------------------- shell

  function build() {
    host = d.createElement('div');
    host.id = 'handrail-root';
    host.setAttribute('data-style', style);
    var fl = cfg.placement && cfg.placement.floating;
    host.setAttribute('data-corner', fl && /^[tb][lr]$/.test(fl.corner || '') ? fl.corner : 'br');
    root = host.attachShadow({ mode: 'open' });
    d.body.appendChild(host);

    // Hidden until the stylesheet arrives, so the panel never shows unstyled.
    root.appendChild(el('style', { text: ':host{display:none}' }));
    var css = el('link', { rel: 'stylesheet', href: cfg.base + 'css/panel.css?ver=' + cfg.version });
    cssReady = new Promise(function (resolve) { css.onload = resolve; css.onerror = resolve; });
    root.appendChild(css);
    if (cfg.brand && cfg.brand.accent) {
      root.appendChild(el('style', { text: ':host{--hr-accent:' + cfg.brand.accent + ';--hr-accent-text:' + (cfg.brand.accentText || '#fff') + '}' }));
    }

    var title = el('h2', { id: 'hr-title', className: 'title' }, [
      el('span', { className: 'logo', 'aria-hidden': 'true', html: H.icons.handrail }),
      (cfg.brand && cfg.brand.title) || S.title
    ]);
    var close = el('button', { type: 'button', className: 'close', 'aria-label': S.close, onclick: closePanel, html: '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M18.3 5.7a1 1 0 0 0-1.4 0L12 10.6 7.1 5.7a1 1 0 0 0-1.4 1.4l4.9 4.9-4.9 4.9a1 1 0 1 0 1.4 1.4l4.9-4.9 4.9 4.9a1 1 0 0 0 1.4-1.4L13.4 12l4.9-4.9a1 1 0 0 0 0-1.4z"/></svg>' });

    // Only the tile grid has tabs; the other styles reach the report form and the checker
    // from buttons in the footer, and come back with a Back button.
    var tablist = null;
    if (tiles && tabOrder.length > 1) {
      tablist = el('div', { role: 'tablist', 'aria-label': S.title, className: 'tabs' });
      tabOrder.forEach(function (id) {
        var label = { adjust: S.tabAdjust, report: S.tabReport, check: S.tabCheck }[id];
        var t = el('button', {
          type: 'button', role: 'tab', id: 'hr-tab-' + id, 'aria-controls': 'hr-panel-' + id,
          'aria-selected': 'false', tabindex: '-1', text: label,
          onclick: function () { showTab(id, true); }
        });
        tabs[id] = t;
        tablist.appendChild(t);
      });
      tablist.addEventListener('keydown', onTabKey);
    }

    var body = el('div', { className: 'body' });
    tabOrder.forEach(function (id) {
      var p = el('section', { role: tablist ? 'tabpanel' : null, id: 'hr-panel-' + id, 'aria-labelledby': tablist ? 'hr-tab-' + id : 'hr-title', className: 'panel', hidden: true });
      var c = p;
      if (!tiles && id !== 'adjust') {
        p.appendChild(el('button', {
          type: 'button', className: 'back', onclick: function () { goAdjust(); }
        }, [el('span', { 'aria-hidden': 'true', html: '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>' }), S.back]));
        c = el('div', { className: 'view' });
        p.appendChild(c);
      }
      panels[id] = p;
      content[id] = c;
      body.appendChild(p);
    });

    headExtra = el('div', { className: 'head-extra' });
    if (!tiles) {
      footActions = el('div', { className: 'factions' }, [
        el('button', { type: 'button', className: 'secondary', 'data-reset': '', text: S.resetAll, onclick: resetAll }),
        cfg.mode && cfg.mode.report ? el('button', { type: 'button', className: 'primary', 'data-go': 'report', text: S.reportLink, onclick: function () { goView('report'); } }) : null,
        cfg.mode && cfg.mode.check ? el('button', { type: 'button', className: 'secondary', 'data-go': 'check', text: S.tabCheck, onclick: function () { goView('check'); } }) : null
      ]);
    }

    var foot = el('div', { className: 'foot' }, [
      footActions,
      el('p', { className: 'disclaimer', text: S.disclaimer }),
      cfg.statementUrl ? el('a', { href: cfg.statementUrl.replace(/#handrail$/, ''), text: S.statement }) : null,
      cfg.credit ? el('a', { href: cfg.credit, text: S.credit, rel: 'noopener' }) : null
    ]);

    live = el('div', { className: 'sr', 'aria-live': 'polite', role: 'status' });
    var peekBar = el('div', { className: 'peekbar', hidden: true });

    sheet = el('div', { className: 'sheet', role: 'dialog', 'aria-modal': 'true', 'aria-labelledby': 'hr-title', tabindex: '-1' }, [
      el('div', { className: 'head' }, [title, headExtra, close]),
      tablist,
      body,
      foot,
      live
    ]);
    root.appendChild(el('div', { className: 'backdrop', onclick: closePanel }));
    root.appendChild(sheet);
    root.appendChild(peekBar);
    root.appendChild(el('slot', { name: 'captcha', hidden: true }));

    sheet.addEventListener('keydown', onKey);
    peekBar.addEventListener('keydown', function (e) { if (e.key === 'Escape') { e.preventDefault(); H.unpeek(); } });

    buildAdjust(content.adjust);
    built = true;
    w.addEventListener('handrail:prefs', syncState);
    syncScale();
  }

  function onKey(e) {
    if (e.key === 'Escape') {
      e.preventDefault();
      closePanel();
      return;
    }
    if (e.key !== 'Tab') return;
    var f = focusables();
    if (!f.length) return;
    var first = f[0], last = f[f.length - 1], active = root.activeElement;
    if (e.shiftKey && (active === first || active === sheet)) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && active === last) { e.preventDefault(); first.focus(); }
  }

  function onTabKey(e) {
    var i = tabOrder.indexOf(current), n = null;
    if (e.key === 'ArrowRight' || e.key === 'ArrowDown') n = tabOrder[(i + 1) % tabOrder.length];
    else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') n = tabOrder[(i - 1 + tabOrder.length) % tabOrder.length];
    else if (e.key === 'Home') n = tabOrder[0];
    else if (e.key === 'End') n = tabOrder[tabOrder.length - 1];
    if (n) {
      e.preventDefault();
      showTab(n, true);
      tabs[n].focus();
    }
  }

  function showTab(id, remember) {
    if (tabOrder.indexOf(id) === -1) id = 'adjust';
    current = id;
    tabOrder.forEach(function (t) {
      panels[t].hidden = t !== id;
      if (tabs[t]) {
        tabs[t].setAttribute('aria-selected', t === id ? 'true' : 'false');
        tabs[t].tabIndex = t === id ? 0 : -1;
      }
    });
    // The quick settings in the dock's header and the Reset/Report buttons belong to the adjust view.
    if (footActions) footActions.hidden = id !== 'adjust';
    if (headExtra) headExtra.hidden = id !== 'adjust';
    if (remember && tiles) {
      try { w.localStorage.setItem('handrail-tab', id); } catch (e) {}
    }
    if (id === 'report' && !content.report.firstChild) buildReport(content.report);
    if (id === 'check' && !content.check.firstChild) mountCheck(content.check);
  }
  H.showTab = showTab;

  // The styles without tabs: open the report form or the checker, and come back.
  function goView(id) {
    showTab(id, false);
    var b = panels[id].querySelector('.back');
    if (b) b.focus();
  }
  function goAdjust() {
    var from = current;
    showTab('adjust', false);
    var b = footActions && footActions.querySelector('[data-go="' + from + '"]');
    (b || focusables(panels.adjust)[0] || sheet).focus();
  }

  // Text size makes the panel bigger too, up to 150%.
  function syncScale() {
    var step = P.get()['text-size'] || 0;
    var f = Math.min([1, 1.1, 1.2, 1.3, 1.4, 1.5][step] || 1, 1.5);
    host.style.setProperty('--hr-scale', f);
    var p = P.get();
    host.setAttribute('data-theme', p.contrast === 1 ? 'dark' : (p.contrast === 2 ? 'contrast' : 'light'));
    host.toggleAttribute('data-invert', H.pageInverted());
  }

  function openPanel(tab, from) {
    if (!built) build();
    trigger = from || null;
    var want = tab;
    if (!want && tiles) {
      try { want = w.localStorage.getItem('handrail-tab'); } catch (e) {}
    }
    showTab(want || 'adjust', false);
    host.classList.add('open');
    sheet.classList.remove('peek');
    syncState();
    // Focus once the stylesheet has made the sheet visible.
    cssReady.then(function () {
      var t = tabs[current];
      // The dock keeps its quick settings in the header: start there.
      var first = current === 'adjust' && headExtra && focusables(headExtra)[0];
      (t || first || focusables(panels[current])[0] || focusables()[0] || sheet).focus();
    });
  }

  function closePanel() {
    if (!host) return;
    H.unpeek(true);
    host.classList.remove('open');
    if (trigger && trigger.focus) {
      try { trigger.focus(); } catch (e) {}
    }
  }

  // ---------------------------------------------------------------- adjust

  function stepLabel(tool, step) {
    if (!step) return S.off;
    return tool.steps.length === 1 ? S.on : tool.steps[step - 1];
  }

  function onTool(id, fn) { (toolViews[id] = toolViews[id] || []).push(fn); }
  function onPreset(pr, fn) { (presetViews[pr.id] = presetViews[pr.id] || { preset: pr, fns: [] }).fns.push(fn); }

  function presetList() {
    return (cfg.presets || []).filter(function (pr) { return Object.keys(pr.tools).length; });
  }
  function toolGroups() {
    return (cfg.groups || [{ id: 'all', label: '' }]).map(function (g) {
      return { group: g, tools: cfg.tools.filter(function (t) { return (t.group || 'all') === g.id; }) };
    }).filter(function (x) { return x.tools.length; });
  }

  function resetAll() {
    P.reset();
    announce(S.resetDone);
  }

  function buildAdjust(p) {
    if (style === 'tiles') adjustTiles(p);
    else if (style === 'card') adjustCard(p);
    else adjustRows(p, style === 'dock');
  }

  // The classic look: quick settings as cards, tools as a grid of tiles that cycle on click.
  function adjustTiles(p) {
    p.appendChild(el('p', { className: 'intro', text: S.adjustIntro }));

    var presets = presetList();
    if (presets.length) {
      p.appendChild(el('h3', { text: S.quick }));
      var row = el('div', { className: 'presets' });
      presets.forEach(function (pr) {
        var b = el('button', {
          type: 'button', className: 'preset', 'aria-pressed': 'false',
          onclick: function () { togglePreset(pr); }
        }, [
          el('span', { className: 'pname', text: pr.label }),
          pr.desc ? el('span', { className: 'pdesc', text: pr.desc }) : null
        ]);
        onPreset(pr, function (on) { b.setAttribute('aria-pressed', on ? 'true' : 'false'); });
        row.appendChild(b);
      });
      p.appendChild(row);
    }

    toolGroups().forEach(function (x) {
      if (x.group.label) p.appendChild(el('h3', { text: x.group.label }));
      var grid = el('div', { className: 'grid' });
      x.tools.forEach(function (t) {
        var dots = null;
        if (t.steps.length > 1) {
          dots = el('span', { className: 'dots', 'aria-hidden': 'true' });
          for (var i = 0; i < t.steps.length; i++) dots.appendChild(el('i'));
        }
        var state = el('span', { className: 'state' });
        var b = el('button', {
          type: 'button', className: 'tool', 'aria-pressed': 'false', 'data-tool': t.id,
          onclick: function () { cycle(t); }
        }, [
          el('span', { className: 'icon', 'aria-hidden': 'true', html: H.icons[t.icon || t.id] || H.icons.handrail }),
          el('span', { className: 'name', text: t.label }),
          state,
          dots
        ]);
        onTool(t.id, function (step) {
          b.setAttribute('aria-pressed', step ? 'true' : 'false');
          state.textContent = stepLabel(t, step);
          if (dots) Array.prototype.forEach.call(dots.children, function (dot, i) { dot.className = i < step ? 'on' : ''; });
        });
        grid.appendChild(b);
      });
      p.appendChild(grid);
    });

    p.appendChild(el('div', { className: 'actions' }, [
      el('button', { type: 'button', className: 'secondary', 'data-reset': '', text: S.resetAll, onclick: resetAll })
    ]));

    if (cfg.mode && cfg.mode.report) {
      p.appendChild(el('div', { className: 'still' }, [
        el('p', { text: S.stillHard }),
        el('button', { type: 'button', className: 'link', text: S.reportLink, onclick: function () { showTab('report', true); tabs.report ? tabs.report.focus() : null; } })
      ]));
    }
  }

  // Settings sheet and dock: quick settings as chips, each tool one row with a switch or a
  // row of choices. The dock lays the groups out side by side and puts the chips in its header.
  function adjustRows(p, dock) {
    p.appendChild(el('p', { className: 'intro', text: S.adjustIntro }));
    var presets = presetList();
    if (presets.length) {
      if (dock) {
        headExtra.appendChild(chips(presets));
      } else {
        p.appendChild(el('h3', { text: S.quick }));
        p.appendChild(chips(presets));
      }
    }
    var wrap = el('div', { className: 'groups' });
    toolGroups().forEach(function (x) {
      var sec = el('div', { className: 'group' }, [x.group.label ? el('h3', { text: x.group.label }) : null]);
      x.tools.forEach(function (t) {
        sec.appendChild(toolRow(t, t.steps.length > 1 ? choicesFor : switchFor));
      });
      wrap.appendChild(sec);
    });
    p.appendChild(wrap);
  }

  // Floating card: a Start page of quick settings, then a page per group with − and + steppers.
  function adjustCard(p) {
    var presets = presetList();
    var pages = [];
    if (presets.length) pages.push({ id: 'start', label: S.start });
    else p.appendChild(el('p', { className: 'intro', text: S.adjustIntro }));
    toolGroups().forEach(function (x) { pages.push({ id: x.group.id, label: x.group.label || S.tabAdjust, tools: x.tools }); });
    if (!pages.length) return;

    var bar = el('div', { role: 'tablist', className: 'gtabs', 'aria-label': S.tabAdjust });
    var btns = {}, views = {}, ids = [];
    pages.forEach(function (pg) {
      ids.push(pg.id);
      btns[pg.id] = el('button', {
        type: 'button', role: 'tab', id: 'hr-g-' + pg.id, 'aria-controls': 'hr-gp-' + pg.id,
        'aria-selected': 'false', tabindex: '-1', text: pg.label, onclick: function () { pick(pg.id); }
      });
      bar.appendChild(btns[pg.id]);
      var v = el('div', { role: 'tabpanel', id: 'hr-gp-' + pg.id, 'aria-labelledby': 'hr-g-' + pg.id, className: 'gpanel', hidden: true });
      if (pg.id === 'start') {
        v.appendChild(el('p', { className: 'intro', text: S.pickIntro }));
        var list = el('div', { className: 'plist' });
        presets.forEach(function (pr) {
          var b = el('button', { type: 'button', className: 'pitem', 'aria-pressed': 'false', onclick: function () { togglePreset(pr); } }, [
            el('span', { className: 'dot', 'aria-hidden': 'true' }),
            el('span', { className: 'ptext' }, [
              el('span', { className: 'pname', text: pr.label }),
              pr.desc ? el('span', { className: 'pdesc', text: pr.desc }) : null
            ])
          ]);
          onPreset(pr, function (on) { b.setAttribute('aria-pressed', on ? 'true' : 'false'); });
          list.appendChild(b);
        });
        v.appendChild(list);
      } else {
        pg.tools.forEach(function (t) { v.appendChild(toolRow(t, t.steps.length > 1 ? stepperFor : switchFor)); });
      }
      views[pg.id] = v;
    });

    function pick(id, focus) {
      ids.forEach(function (x) {
        var on = x === id;
        btns[x].setAttribute('aria-selected', on ? 'true' : 'false');
        btns[x].tabIndex = on ? 0 : -1;
        views[x].hidden = !on;
      });
      if (focus) btns[id].focus();
    }
    bar.addEventListener('keydown', function (e) {
      var i = -1;
      ids.forEach(function (x, n) { if (btns[x].getAttribute('aria-selected') === 'true') i = n; });
      var n = null;
      if (e.key === 'ArrowRight' || e.key === 'ArrowDown') n = ids[(i + 1) % ids.length];
      else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') n = ids[(i - 1 + ids.length) % ids.length];
      else if (e.key === 'Home') n = ids[0];
      else if (e.key === 'End') n = ids[ids.length - 1];
      if (n) { e.preventDefault(); pick(n, true); }
    });
    p.appendChild(bar);
    ids.forEach(function (x) { p.appendChild(views[x]); });
    pick(ids[0]);
  }

  function chips(list) {
    var row = el('div', { className: 'chips' });
    list.forEach(function (pr) {
      var b = el('button', { type: 'button', className: 'chip', 'aria-pressed': 'false', onclick: function () { togglePreset(pr); } }, [
        pr.label,
        pr.desc ? el('span', { className: 'sr', text: ': ' + pr.desc }) : null
      ]);
      onPreset(pr, function (on) { b.setAttribute('aria-pressed', on ? 'true' : 'false'); });
      row.appendChild(b);
    });
    return row;
  }

  // One tool as a row: its name and description, then the control the style uses.
  function toolRow(t, control) {
    var ids = { name: 'hr-n-' + t.id, desc: t.desc ? 'hr-d-' + t.id : null };
    var txt = el('div', { className: 'txt' }, [
      el('div', { className: 'name', id: ids.name, text: t.label }),
      t.desc ? el('div', { className: 'desc', id: ids.desc, text: t.desc }) : null
    ]);
    var row = el('div', { className: 'row' + (t.steps.length > 1 ? ' multi' : ''), 'data-row': t.id }, [txt]);
    row.appendChild(control(t, ids, txt));
    return row;
  }

  function switchFor(t, ids) {
    var b = el('button', {
      type: 'button', role: 'switch', className: 'switch', 'aria-checked': 'false', 'data-tool': t.id,
      'aria-labelledby': ids.name, 'aria-describedby': ids.desc, onclick: function () { cycle(t); }
    }, [el('span', { className: 'knob', 'aria-hidden': 'true' })]);
    onTool(t.id, function (step) { b.setAttribute('aria-checked', step ? 'true' : 'false'); });
    return b;
  }

  function choicesFor(t, ids) {
    var g = el('div', { role: 'group', className: 'choices', 'data-tool': t.id, 'aria-labelledby': ids.name, 'aria-describedby': ids.desc });
    var btns = [S.off].concat(t.steps).map(function (label, i) {
      var b = el('button', { type: 'button', 'aria-pressed': 'false', 'data-step': String(i), text: label, onclick: function () { setStep(t, i); } });
      g.appendChild(b);
      return b;
    });
    onTool(t.id, function (step) {
      btns.forEach(function (b, i) { b.setAttribute('aria-pressed', i === step ? 'true' : 'false'); });
    });
    return g;
  }

  function stepperFor(t, ids, txt) {
    var valId = 'hr-v-' + t.id;
    var val = el('div', { className: 'val', id: valId });
    txt.appendChild(val);
    var minus = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M5 12h14"/></svg>';
    var plus = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M5 12h14M12 5v14"/></svg>';
    // aria-disabled, not disabled: a disabled button drops keyboard focus to the page.
    var less = el('button', {
      type: 'button', className: 'less', 'aria-label': S.lessOf.replace('{tool}', t.label), 'aria-describedby': valId, html: minus,
      onclick: function () { var s = P.get()[t.id] || 0; if (s > 0) setStep(t, s - 1); }
    });
    var more = el('button', {
      type: 'button', className: 'more', 'data-tool': t.id, 'aria-label': S.moreOf.replace('{tool}', t.label), 'aria-describedby': valId, html: plus,
      onclick: function () { var s = P.get()[t.id] || 0; if (s < t.steps.length) setStep(t, s + 1); }
    });
    onTool(t.id, function (step) {
      val.textContent = stepLabel(t, step);
      less.setAttribute('aria-disabled', step === 0 ? 'true' : 'false');
      more.setAttribute('aria-disabled', step === t.steps.length ? 'true' : 'false');
    });
    return el('div', { className: 'stepper', role: 'group', 'aria-labelledby': ids.name }, [less, more]);
  }

  function setStep(t, next) {
    // Some tools can't run together (the reading guide and the mask): turning one on turns the other off.
    if (next && t.excludes && t.excludes.length) {
      var map = {};
      t.excludes.forEach(function (id) { map[id] = 0; });
      map[t.id] = next;
      P.setMany(map);
    } else {
      P.set(t.id, next);
    }
    announce(t.label + ': ' + stepLabel(t, next));
  }

  function cycle(t) {
    var now = P.get()[t.id] || 0;
    setStep(t, now >= t.steps.length ? 0 : now + 1);
  }

  function presetActive(pr, prefs) {
    for (var id in pr.tools) if ((prefs[id] || 0) !== pr.tools[id]) return false;
    return true;
  }

  function togglePreset(pr) {
    var prefs = P.get();
    var map = {};
    var on = presetActive(pr, prefs);
    for (var id in pr.tools) map[id] = on ? 0 : pr.tools[id];
    // Respect tools that can't run together (the reading guide and mask): turning a preset on
    // switches off whatever its tools exclude, even if the visitor had it on. If a preset itself
    // lists both, the later tool in the panel wins (the mask, matching Pro's settings check).
    if (!on) {
      cfg.tools.slice().reverse().forEach(function (t) {
        if (map[t.id] && t.excludes) t.excludes.forEach(function (x) { map[x] = 0; });
      });
    }
    P.setMany(map);
    announce(pr.label + ': ' + (on ? S.off : S.on));
  }

  function syncState() {
    if (!built) return;
    var prefs = P.get();
    Object.keys(toolViews).forEach(function (id) {
      toolViews[id].forEach(function (fn) { fn(prefs[id] || 0); });
    });
    Object.keys(presetViews).forEach(function (id) {
      var on = presetActive(presetViews[id].preset, prefs);
      presetViews[id].fns.forEach(function (fn) { fn(on); });
    });
    syncScale();
  }

  // ---------------------------------------------------------------- report

  var captcha = { box: null, token: '', widget: null };

  function buildReport(p) {
    var r = cfg.report || {};
    var started = Date.now();
    var err = el('p', { className: 'error', id: 'hr-report-error', role: 'alert', hidden: true });

    var cats = el('fieldset', { className: 'cats' }, [el('legend', { text: S.reportWhat })]);
    (r.categories || []).forEach(function (c) {
      var id = 'hr-cat-' + c.id;
      cats.appendChild(el('div', { className: 'check' }, [
        el('input', { type: 'checkbox', id: id, name: 'categories', value: c.id }),
        el('label', { 'for': id, text: c.label })
      ]));
    });

    var comments = el('textarea', { id: 'hr-comments', name: 'comments', rows: '4', maxlength: '2000', 'aria-describedby': 'hr-comments-hint' });
    var contact = el('input', { type: 'email', id: 'hr-contact', name: 'contact', autocomplete: 'email', maxlength: '190', 'aria-describedby': 'hr-contact-hint' });
    // A field people can't see or reach; bots fill it in.
    var trap = el('div', { className: 'trap', 'aria-hidden': 'true' }, [
      el('label', { 'for': 'hr-website', text: 'Website' }),
      el('input', { type: 'text', id: 'hr-website', name: 'website', tabindex: '-1', autocomplete: 'off' })
    ]);
    var send = el('button', { type: 'submit', className: 'primary', text: S.reportSend });

    var form = el('form', { className: 'report', novalidate: true }, [
      el('p', { className: 'intro', text: S.reportIntro }),
      err,
      cats,
      el('div', { className: 'field' }, [
        el('label', { 'for': 'hr-comments', text: S.reportComments }),
        el('p', { className: 'hint', id: 'hr-comments-hint', text: S.reportCommentsHint }),
        comments
      ]),
      r.contact === false ? null : el('div', { className: 'field' }, [
        el('label', { 'for': 'hr-contact', text: S.reportContact }),
        el('p', { className: 'hint', id: 'hr-contact-hint', text: S.reportContactHint }),
        contact
      ]),
      trap,
      r.captcha ? el('div', { className: 'captcha' }, [el('slot', { name: 'captcha' })]) : null,
      el('div', { className: 'actions' }, [send])
    ]);

    var done = el('div', { className: 'done', hidden: true, tabindex: '-1' }, [
      el('p', { className: 'ok', text: S.reportThanks }),
      el('div', { className: 'actions' }, [
        el('button', { type: 'button', className: 'primary', text: S.reportDone, onclick: closePanel }),
        el('button', {
          type: 'button', className: 'secondary', text: S.reportAnother,
          onclick: function () {
            form.reset();
            done.hidden = true;
            form.hidden = false;
            started = Date.now();
            resetCaptcha();
            comments.focus();
          }
        })
      ])
    ]);

    var invalid = null;
    function fail(msg, focusEl) {
      err.textContent = msg;
      err.hidden = false;
      // Tie the message to the field that needs fixing (not the Send button).
      if (focusEl && focusEl !== send) {
        invalid = focusEl;
        focusEl.setAttribute('aria-invalid', 'true');
        focusEl.setAttribute('aria-describedby', (focusEl.getAttribute('aria-describedby') || '').replace('hr-report-error', '').trim() + ' hr-report-error');
      }
      if (focusEl) {
        if (focusEl.disabled) focusEl.disabled = false;
        focusEl.focus();
      }
    }
    function clearInvalid() {
      if (!invalid) return;
      invalid.removeAttribute('aria-invalid');
      var rest = (invalid.getAttribute('aria-describedby') || '').replace('hr-report-error', '').trim();
      if (rest) invalid.setAttribute('aria-describedby', rest); else invalid.removeAttribute('aria-describedby');
      invalid = null;
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      err.hidden = true;
      clearInvalid();
      var chosen = Array.prototype.map.call(form.querySelectorAll('input[name=categories]:checked'), function (i) { return i.value; });
      if (!chosen.length && !comments.value.trim()) return fail(S.reportNeedSomething, form.querySelector('input[name=categories]') || comments);
      if (contact.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(contact.value.trim())) return fail(S.reportBadEmail, contact);

      var tokenPromise = r.captcha ? captchaToken() : Promise.resolve('');
      send.disabled = true;
      send.textContent = S.reportSending;
      // The server ignores reports sent within 3 seconds of opening the form (a bot signal),
      // so a quick person waits out the rest instead of losing their report.
      var wait = Math.max(0, 3200 - (Date.now() - started));
      tokenPromise.then(function (token) {
        return new Promise(function (res) { setTimeout(function () { res(token); }, wait); });
      }).then(function (token) {
        if (r.captcha && !token) throw { msg: S.reportCaptcha };
        var payload = {
          page_url: w.location.href.split('#')[0],
          page_title: d.title,
          categories: chosen,
          comments: comments.value.trim(),
          contact: contact.value.trim(),
          website: form.querySelector('#hr-website').value,
          elapsed: Math.floor((Date.now() - started) / 1000),
          adjustments: P.get(),
          viewport: w.innerWidth + 'x' + w.innerHeight,
          zoom: Math.round((w.devicePixelRatio || 1) * 100),
          lang: d.documentElement.lang || '',
          captcha_token: token,
          scan: H.lastScanSummary || null
        };
        var headers = { 'Content-Type': 'application/json' };
        for (var h in (r.headers || {})) headers[h] = r.headers[h];
        return fetch(r.url, { method: 'POST', headers: headers, body: JSON.stringify(payload), credentials: 'same-origin' });
      }).then(function (res) {
        return res.json().catch(function () { return {}; }).then(function (body) {
          if (!res.ok) throw { msg: body && body.message ? body.message : S.reportError };
          form.hidden = true;
          done.hidden = false;
          done.focus();
          announce(S.reportThanks);
        });
      }).catch(function (x) {
        send.disabled = false;
        send.textContent = S.reportSend;
        fail(x && x.msg ? x.msg : S.reportError, send);
        resetCaptcha();
      }).then(function () {
        send.disabled = false;
        send.textContent = S.reportSend;
      });
    });

    p.appendChild(form);
    p.appendChild(done);
    if (r.captcha) mountCaptcha(r.captcha);
  }

  // Provider widgets break inside a shadow root, so the captcha lives in the page's DOM
  // (inside our host element) and is slotted into the form.
  function mountCaptcha(c) {
    if (captcha.box) return;
    captcha.box = d.createElement('div');
    captcha.box.slot = 'captcha';
    captcha.box.id = 'handrail-captcha';
    host.appendChild(captcha.box);
    var cb = 'handrailCaptchaReady' + Math.floor(Math.random() * 1e6);
    var src;
    w[cb] = function () { renderCaptcha(c); };
    if (c.provider === 'turnstile') src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=' + cb;
    else if (c.provider === 'hcaptcha') src = 'https://js.hcaptcha.com/1/api.js?render=explicit&onload=' + cb;
    else if (c.provider === 'recaptcha_v3') src = 'https://www.google.com/recaptcha/api.js?render=' + encodeURIComponent(c.sitekey) + '&onload=' + cb;
    else src = 'https://www.google.com/recaptcha/api.js?render=explicit&onload=' + cb;
    var s = d.createElement('script');
    s.src = src;
    s.async = true;
    d.head.appendChild(s);
  }

  function renderCaptcha(c) {
    var opts = { sitekey: c.sitekey, callback: function (t) { captcha.token = t; } };
    if (c.provider === 'turnstile') {
      opts.action = 'handrail_report';
      opts['expired-callback'] = function () { captcha.token = ''; };
      captcha.widget = w.turnstile.render(captcha.box, opts);
    } else if (c.provider === 'hcaptcha') {
      opts['expired-callback'] = function () { captcha.token = ''; };
      captcha.widget = w.hcaptcha.render(captcha.box, opts);
    } else if (c.provider === 'recaptcha_v2') {
      opts['expired-callback'] = function () { captcha.token = ''; };
      captcha.widget = w.grecaptcha.render(captcha.box, opts);
    }
  }

  function captchaToken() {
    var c = cfg.report.captcha;
    if (c.provider === 'recaptcha_v3') {
      return new Promise(function (resolve) {
        if (!w.grecaptcha) return resolve('');
        w.grecaptcha.ready(function () {
          w.grecaptcha.execute(c.sitekey, { action: 'handrail_report' }).then(resolve, function () { resolve(''); });
        });
      });
    }
    return Promise.resolve(captcha.token);
  }

  function resetCaptcha() {
    var c = cfg.report && cfg.report.captcha;
    if (!c || captcha.widget == null) return;
    captcha.token = '';
    try {
      if (c.provider === 'turnstile') w.turnstile.reset(captcha.widget);
      else if (c.provider === 'hcaptcha') w.hcaptcha.reset(captcha.widget);
      else if (c.provider === 'recaptcha_v2') w.grecaptcha.reset(captcha.widget);
    } catch (e) {}
  }

  // ---------------------------------------------------------------- check

  function mountCheck(p) {
    p.appendChild(el('p', { className: 'intro', text: S.loading }));
    var chain = w.HandrailCheck ? Promise.resolve() : H.loadScript(cfg.base + 'js/check.js?ver=' + cfg.version);
    chain.then(function () {
      p.textContent = '';
      w.HandrailCheck.mount(p, { root: root, sheet: sheet, announce: announce });
    }, function () {
      p.textContent = '';
      p.appendChild(el('p', { className: 'error', text: S.checkLoadError }));
    });
  }

  // "Show me": shrink the panel to a bar and outline the element on the page.
  var marker = null, peekTarget = null;
  function placeMarker() {
    if (!marker || !peekTarget) return;
    var r = peekTarget.getBoundingClientRect();
    marker.style.top = (r.top - 4) + 'px';
    marker.style.left = (r.left - 4) + 'px';
    marker.style.width = (r.width + 8) + 'px';
    marker.style.height = (r.height + 8) + 'px';
  }
  H.peek = function (node, label, back) {
    if (!node) return;
    peekTarget = node;
    if (!marker) {
      marker = d.createElement('div');
      marker.id = 'handrail-marker';
      marker.setAttribute('aria-hidden', 'true');
      marker.style.cssText = 'position:fixed;z-index:2147483001;pointer-events:none;border:4px solid #d4351c;box-shadow:0 0 0 4px #fff,0 0 0 8px #d4351c;border-radius:4px;transition:none';
      d.body.appendChild(marker);
      w.addEventListener('scroll', placeMarker, true);
      w.addEventListener('resize', placeMarker);
    }
    marker.hidden = false;
    node.scrollIntoView({ block: 'center', behavior: 'auto' });
    placeMarker();
    var bar = root.querySelector('.peekbar');
    bar.textContent = '';
    bar.appendChild(el('span', { className: 'what', text: label || '' }));
    bar.appendChild(el('button', { type: 'button', className: 'primary', text: back, onclick: function () { H.unpeek(); } }));
    sheet.classList.add('peek');
    host.classList.add('peeking');
    bar.hidden = false;
    bar.querySelector('button').focus();
    H.peekReturn = root.activeElement;
  };
  H.unpeek = function (silent) {
    if (!host || !host.classList.contains('peeking')) return;
    host.classList.remove('peeking');
    sheet.classList.remove('peek');
    root.querySelector('.peekbar').hidden = true;
    if (marker) marker.hidden = true;
    peekTarget = null;
    if (!silent && H.onUnpeek) H.onUnpeek();
  };

  w.HandrailPanel = { open: openPanel, close: closePanel, showTab: showTab };
})(window, document);
