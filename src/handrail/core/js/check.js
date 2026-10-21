/*! Handrail check: runs axe-core on this page and says who can fix each problem. Editors only. */
(function (w, d) {
  if (w.HandrailCheck) return;

  var H = w.Handrail;
  var cfg = H.config;
  var C = cfg.check || {};
  var OWN = C.owners || {};
  var el = H.el;

  var DEFAULTS = {
    checkIntro: 'Run an automated accessibility check of this page. Only people who can edit this site see this tab.',
    checkRun: 'Check this page',
    checkAgain: 'Check again',
    checkRunning: 'Checking: {cat}',
    checkDone: 'Check finished.',
    checkFailed: 'The check could not finish.',
    summaryNone: 'The automated check found no problems on this page.',
    summaryOne: '1 problem found.',
    summaryMany: '{n} problems found.',
    summaryReview: '{n} more need a person to look.',
    needsPerson: 'Needs a person to check',
    needsPersonDesc: 'The checker could not decide these. Usually it is text over an image or a gradient. Look at each one.',
    owner_content: 'Posts, pages and the blocks in them. Fix these in the editor.',
    owner_settings: 'Site-wide settings: colors, fonts, logo or menus.',
    owner_theme: "Comes from the theme's design. Look for a theme setting, use a child theme or ask the theme's developer.",
    owner_plugin: 'Comes from this plugin. Check its settings or ask its developer.',
    owner_core: 'Comes from WordPress itself.',
    owner_unknown: 'Look at each one on the page to decide who can fix it.',
    unknownLabel: 'Not sure who can fix this',
    showMe: 'Show me',
    backToResults: 'Back to results',
    andMore: 'and {n} more',
    textColor: 'Text color',
    background: 'Background',
    fromLikely: 'most likely',
    contrastNow: 'Contrast {r}:1, needs {need}:1.',
    contrastTry: 'Try {color} for the text.',
    exportMd: 'Export as Markdown',
    exported: 'Copied to the clipboard and downloaded.',
    passedTitle: 'No problems found in',
    honest: 'Automated checks find about a third of accessibility problems. A clean result is not a compliance certificate: also try the page with a keyboard and a screen reader.',
    iframes: 'Content embedded from other sites, such as videos and maps, is not checked.'
  };
  var S = H.S;
  for (var k in DEFAULTS) if (!(k in S)) S[k] = DEFAULTS[k];
  for (k in (C.strings || {})) S[k] = C.strings[k];

  function fmt(s, vars) { return String(s).replace(/\{(\w+)\}/g, function (m, v) { return vars[v] != null ? vars[v] : m; }); }

  var CATS = {
    'cat.text-alternatives': 'Images and text alternatives',
    'cat.color': 'Color and contrast',
    'cat.forms': 'Forms and labels',
    'cat.keyboard': 'Keyboard access',
    'cat.name-role-value': 'Buttons, links and controls',
    'cat.aria': 'Screen reader attributes',
    'cat.structure': 'Page structure',
    'cat.semantics': 'Headings and landmarks',
    'cat.tables': 'Tables',
    'cat.time-and-media': 'Video, audio and motion',
    'cat.language': 'Language',
    'cat.sensory-and-visual-cues': 'Zoom and visual cues',
    'cat.parsing': 'Markup',
    other: 'Other checks'
  };
  for (k in (C.categories || {})) CATS[k] = C.categories[k];

  // Plain-English titles. Anything missing falls back to axe's own wording.
  var TITLES = {
    'color-contrast': "Text doesn't stand out enough from its background",
    'color-contrast-enhanced': "Text doesn't stand out enough from its background (AAA)",
    'link-in-text-block': 'A link only stands out from the text around it by its color',
    'image-alt': 'An image has no text description',
    'role-img-alt': 'An image has no text description',
    'svg-img-alt': 'An image has no text description',
    'input-image-alt': 'An image button has no text',
    'area-alt': 'An image map area has no text',
    'object-alt': 'An embedded object has no text description',
    'link-name': 'A link has no text a screen reader can announce',
    'button-name': 'A button has no name',
    'input-button-name': 'A button has no text',
    'label': 'A form field has no label',
    'select-name': 'A drop-down has no label',
    'form-field-multiple-labels': 'A form field has more than one label',
    'autocomplete-valid': 'A personal-information field has the wrong autocomplete hint',
    'aria-command-name': 'A control has no name a screen reader can announce',
    'aria-input-field-name': 'A field has no name a screen reader can announce',
    'aria-toggle-field-name': 'A toggle has no name a screen reader can announce',
    'aria-meter-name': 'A meter has no name',
    'aria-progressbar-name': 'A progress bar has no name',
    'aria-tooltip-name': 'A tooltip has no text',
    'aria-dialog-name': 'A dialog has no name',
    'aria-hidden-focus': 'Hidden content can still be reached with the keyboard',
    'aria-hidden-body': 'The whole page is hidden from screen readers',
    'aria-required-attr': 'A screen reader attribute is missing',
    'aria-required-children': 'A component is missing parts screen readers expect',
    'aria-required-parent': 'A component part is outside the component it belongs to',
    'aria-valid-attr': 'A screen reader attribute is misspelled',
    'aria-valid-attr-value': 'A screen reader attribute has a wrong value',
    'aria-allowed-attr': 'A screen reader attribute is not allowed here',
    'aria-roles': 'An element has a role that does not exist',
    'aria-prohibited-attr': 'A screen reader attribute is not allowed on this element',
    'aria-braille-equivalent': 'Braille text has no spoken equivalent',
    'aria-conditional-attr': 'A screen reader attribute is used the wrong way',
    'aria-deprecated-role': 'An element uses an outdated role',
    'document-title': 'The page has no title',
    'html-has-lang': "The page's language is not set",
    'html-lang-valid': "The page's language code is not valid",
    'html-xml-lang-mismatch': 'The page declares two different languages',
    'valid-lang': 'A language code on part of the page is not valid',
    'frame-title': 'An embedded frame has no title',
    'frame-title-unique': 'Two embedded frames have the same title',
    'frame-focusable-content': 'An embedded frame cannot be reached with the keyboard',
    'list': 'A list is built incorrectly',
    'listitem': 'A list item is outside a list',
    'definition-list': 'A definition list is built incorrectly',
    'dlitem': 'A definition list item is outside its list',
    'meta-viewport': 'The page blocks zooming on phones',
    'meta-refresh': 'The page refreshes or redirects on its own',
    'blink': 'Content blinks',
    'marquee': 'Content scrolls on its own',
    'bypass': 'There is no way to skip straight to the main content',
    'duplicate-id-aria': 'Two elements share an ID that is used for labels',
    'nested-interactive': 'A control is nested inside another control',
    'scrollable-region-focusable': 'A scrolling area cannot be reached with the keyboard',
    'target-size': 'A click or tap target is too small',
    'td-headers-attr': 'Table cells point at headers that are not there',
    'th-has-data-cells': 'A table header has no cells under it',
    'video-caption': 'A video has no captions',
    'audio-caption': 'Audio has no captions or transcript',
    'server-side-image-map': 'A server-side image map cannot be used with a keyboard',
    'summary-name': 'An expandable summary has no name',
    'tabindex': 'An element jumps ahead in the keyboard order',
    'no-autoplay-audio': 'Audio plays on its own'
  };

  var root, results, progress, progressBar, status, runBtn, lastScan = null, owners;

  // ------------------------------------------------------------ attribution

  function inContent(node) {
    var c = OWN.content || [];
    for (var i = 0; i < c.length; i++) {
      try { if (node.closest(c[i])) return true; } catch (e) {}
    }
    return false;
  }

  function bySelector(node) {
    var list = OWN.selectors || [];
    for (var i = 0; i < list.length; i++) {
      try { if (node.closest(list[i].s)) return { type: list[i].type, label: list[i].label }; } catch (e) {}
    }
    return null;
  }

  function fallback() {
    return { type: (OWN.fallback && OWN.fallback.type) || 'unknown', label: (OWN.fallback && OWN.fallback.label) || S.unknownLabel, guess: true };
  }

  function contentOwner() {
    return { type: 'content', label: (OWN.contentLabel || 'Your content') };
  }

  function sheetOwner(sheet) {
    var node = sheet.ownerNode;
    var id = node && node.id;
    if (id && OWN.ids && OWN.ids[id]) return OWN.ids[id];
    if (id && OWN.handles) {
      var handle = id.replace(/-(inline-)?css$/, '');
      if (OWN.handles[handle]) return OWN.handles[handle];
    }
    var href = sheet.href || (node && node.href) || '';
    var paths = OWN.paths || [];
    for (var i = 0; i < paths.length; i++) {
      if (href.indexOf(paths[i].p) !== -1) return { type: paths[i].type, label: paths[i].label };
    }
    return null;
  }

  function sheetName(sheet) {
    var node = sheet.ownerNode;
    if (sheet.href) return sheet.href.split('?')[0].split('/').slice(-3).join('/');
    if (node && node.id) return '<style id="' + node.id + '">';
    return '<style>';
  }

  // All style rules that set a color or background, in cascade order.
  function collectRules() {
    var out = [], order = 0;
    function walk(list, sheet, layered) {
      for (var i = 0; i < list.length; i++) {
        var r = list[i];
        if (r.selectorText !== undefined && r.style) {
          var st = r.style;
          if (st.color || st.backgroundColor || st.background || st.backgroundImage) {
            out.push({ rule: r, sheet: sheet, order: order++, layered: layered });
          }
        } else if (r.media && r.cssRules) {
          var mq = r.conditionText || (r.media && r.media.mediaText) || 'all';
          var ok = true;
          try { ok = w.matchMedia(mq).matches; } catch (e) {}
          if (ok) walk(r.cssRules, sheet, layered);
        } else if (r.styleSheet) {
          try { walk(r.styleSheet.cssRules, sheet, layered); } catch (e) {}
        } else if (r.cssRules) {
          // @supports, @container, @layer blocks: assume they apply.
          var isLayer = typeof CSSLayerBlockRule !== 'undefined' && r instanceof CSSLayerBlockRule;
          walk(r.cssRules, sheet, layered || isLayer);
        }
      }
    }
    Array.prototype.forEach.call(d.styleSheets, function (sheet) {
      var rules;
      try { rules = sheet.cssRules; } catch (e) { return; } // cross-origin
      if (rules) walk(rules, sheet, false);
    });
    return out;
  }

  function splitSelectors(text) {
    var parts = [], depth = 0, cur = '';
    for (var i = 0; i < text.length; i++) {
      var ch = text[i];
      if (ch === '(' || ch === '[') depth++;
      else if (ch === ')' || ch === ']') depth--;
      if (ch === ',' && depth === 0) { parts.push(cur.trim()); cur = ''; }
      else cur += ch;
    }
    if (cur.trim()) parts.push(cur.trim());
    return parts;
  }

  function specificity(sel) {
    var s = sel.replace(/:where\((?:[^()]|\([^()]*\))*\)/g, '').replace(/::?(?:is|not|has)\(/g, ' (');
    var a = (s.match(/#[\w-]+/g) || []).length;
    var b = (s.match(/\.[\w-]+|\[[^\]]*\]|:(?!:)[\w-]+/g) || []).length;
    var c = (s.replace(/\[[^\]]*\]/g, '').match(/(^|[\s>+~(])[a-zA-Z][\w-]*|::[\w-]+/g) || []).length;
    return a * 10000 + b * 100 + c;
  }

  function sets(rule, props) {
    for (var i = 0; i < props.length; i++) if (rule.style.getPropertyValue(props[i])) return props[i];
    return null;
  }

  // The rule (or inline style) that wins for any of props on this element.
  function winner(node, props) {
    var best = null;
    for (var i = 0; i < owners.rules.length; i++) {
      var e = owners.rules[i];
      var prop = sets(e.rule, props);
      if (!prop) continue;
      var parts;
      try {
        if (!node.matches(e.rule.selectorText)) continue;
        parts = splitSelectors(e.rule.selectorText);
      } catch (x) { continue; }
      var spec = 0, matched = parts[0];
      for (var j = 0; j < parts.length; j++) {
        try {
          if (node.matches(parts[j])) {
            var sp = specificity(parts[j]);
            if (sp >= spec) { spec = sp; matched = parts[j]; }
          }
        } catch (x) {}
      }
      var imp = e.rule.style.getPropertyPriority(prop) === 'important';
      var score = [imp ? 1 : 0, imp ? (e.layered ? 1 : 0) : (e.layered ? 0 : 1), spec, e.order];
      if (!best || compare(score, best.score) > 0) best = { score: score, entry: e, selector: matched, prop: prop };
    }
    for (var p = 0; p < props.length; p++) {
      if (node.style && node.style.getPropertyValue(props[p])) {
        var inlineImp = node.style.getPropertyPriority(props[p]) === 'important';
        if (!best || !best.score[0] || inlineImp) return { inline: true, el: node, prop: props[p] };
      }
    }
    return best ? { entry: best.entry, selector: best.selector, el: node, prop: best.prop } : null;
  }

  function compare(a, b) {
    for (var i = 0; i < a.length; i++) if (a[i] !== b[i]) return a[i] - b[i];
    return 0;
  }

  function colorOrigin(node) {
    for (var n = node; n && n.nodeType === 1; n = n.parentElement) {
      var win = winner(n, ['color']);
      if (win) return win;
    }
    return null;
  }

  function paints(n) {
    var cs = w.getComputedStyle(n);
    if (cs.backgroundImage && cs.backgroundImage !== 'none') return true;
    var m = cs.backgroundColor.match(/rgba?\(([^)]+)\)/);
    if (!m) return cs.backgroundColor !== 'transparent';
    var parts = m[1].split(/[\s,\/]+/);
    return parts.length < 4 || parseFloat(parts[3]) > 0;
  }

  function bgOrigin(node) {
    for (var n = node; n && n.nodeType === 1; n = n.parentElement) {
      if (paints(n)) return winner(n, ['background-color', 'background', 'background-image']) || { el: n, unknown: true };
    }
    return null;
  }

  function originOwner(origin) {
    if (!origin || origin.unknown) return null;
    var n = origin.el;
    if (origin.inline) return inContent(n) ? contentOwner() : null;
    // A palette color picked on a block shows up as a has-*-color class.
    if (inContent(n) && /(^|\s)has-[\w-]+-(color|background-color)(\s|$)/.test(n.className || '')) return contentOwner();
    return sheetOwner(origin.entry.sheet);
  }

  function originText(origin) {
    if (!origin) return 'browser default';
    if (origin.unknown) return 'unknown';
    if (origin.inline) return 'inline style on <' + origin.el.nodeName.toLowerCase() + '>';
    return origin.selector + ' in ' + sheetName(origin.entry.sheet);
  }

  function attribute(node, ruleId, data) {
    if (ruleId === 'color-contrast' || ruleId === 'color-contrast-enhanced' || ruleId === 'link-in-text-block') {
      var fg = colorOrigin(node), bg = bgOrigin(node);
      var fo = originOwner(fg), bo = originOwner(bg);
      var owner;
      if ((fo && fo.type === 'content') || (bo && bo.type === 'content')) owner = contentOwner();
      else owner = fo || bo || bySelector(node) || fallback();
      return { owner: owner, fg: fg, bg: bg, fgOwner: fo, bgOwner: bo };
    }
    var sel = bySelector(node);
    if (inContent(node)) return { owner: sel && sel.type === 'plugin' ? sel : contentOwner() };
    return { owner: sel || fallback() };
  }

  // ------------------------------------------------------------ contrast fix

  function hexToRgb(hex) {
    var m = /^#?([0-9a-f]{6})$/i.exec(hex || '');
    if (!m) return null;
    var n = parseInt(m[1], 16);
    return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
  }
  function lum(rgb) {
    var c = rgb.map(function (v) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); });
    return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
  }
  function ratio(a, b) {
    var x = lum(a), y = lum(b);
    return (Math.max(x, y) + 0.05) / (Math.min(x, y) + 0.05);
  }
  function toHex(rgb) {
    return '#' + rgb.map(function (v) { return ('0' + Math.round(v).toString(16)).slice(-2); }).join('');
  }
  // Nearest text color (same hue, darker or lighter) that meets the ratio.
  function suggest(fgHex, bgHex, need) {
    var fg = hexToRgb(fgHex), bg = hexToRgb(bgHex);
    if (!fg || !bg) return null;
    var darker = lum(bg) > 0.18;
    for (var t = 0.02; t <= 1.0001; t += 0.02) {
      var c = fg.map(function (v) { return darker ? v * (1 - t) : v + (255 - v) * t; });
      if (ratio(c, bg) >= need + 0.05) return toHex(c);
    }
    return darker ? '#000000' : '#ffffff';
  }

  // ------------------------------------------------------------ running

  function loadAxe() {
    if (w.axe) return Promise.resolve();
    return H.loadScript(cfg.base + 'vendor/axe-core/axe.min.js?ver=' + cfg.version);
  }

  function context() {
    var ex = [['#handrail-root'], ['#handrail-button-root'], ['#handrail-marker']];
    (C.skip || []).forEach(function (s) { ex.push([s]); });
    return { exclude: ex };
  }

  function setHostHidden(hidden) {
    ['handrail-root', 'handrail-button-root', 'handrail-marker'].forEach(function (id) {
      var n = d.getElementById(id);
      if (n) n.style.visibility = hidden ? 'hidden' : '';
    });
  }

  function run() {
    runBtn.disabled = true;
    results.textContent = '';
    progress.hidden = false;
    progressBar.style.width = '0%';
    owners = { rules: collectRules() };

    var tags = C.tags || ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'];
    var byCat = {}, catOrder = [];
    loadAxe().then(function () {
      if (C.locale) { try { w.axe.configure({ locale: C.locale }); } catch (e) {} }
      w.axe.getRules(tags).forEach(function (r) {
        var cat = (r.tags || []).filter(function (t) { return t.indexOf('cat.') === 0; })[0] || 'other';
        if (!byCat[cat]) { byCat[cat] = []; catOrder.push(cat); }
        byCat[cat].push(r.ruleId);
      });
      var all = { violations: [], incomplete: [] }, done = 0;
      var chain = Promise.resolve();
      catOrder.forEach(function (cat) {
        chain = chain.then(function () {
          status.textContent = fmt(S.checkRunning, { cat: CATS[cat] || cat });
          // The panel would sit on top of the page during color checks.
          var hide = cat === 'cat.color';
          if (hide) setHostHidden(true);
          return w.axe.run(context(), {
            runOnly: { type: 'rule', values: byCat[cat] },
            resultTypes: ['violations', 'incomplete'],
            elementRef: true
          }).then(function (res) {
            if (hide) setHostHidden(false);
            res.violations.forEach(function (v) { v.cat = cat; all.violations.push(v); });
            res.incomplete.forEach(function (v) { v.cat = cat; all.incomplete.push(v); });
            done++;
            progressBar.style.width = Math.round(done / catOrder.length * 100) + '%';
          }, function (e) {
            if (hide) setHostHidden(false);
            throw e;
          });
        });
      });
      return chain.then(function () { return all; });
    }).then(function (all) {
      lastScan = { all: all, cats: catOrder, when: new Date(), url: w.location.href.split('#')[0], version: w.axe.version };
      render(lastScan);
      status.textContent = S.checkDone;
      H.lastScanSummary = summary(all);
    }).catch(function (e) {
      setHostHidden(false);
      status.textContent = S.checkFailed;
      if (w.console) console.error('[handrail]', e);
    }).then(function () {
      runBtn.disabled = false;
      runBtn.textContent = S.checkAgain;
      progress.hidden = true;
    });
  }

  function summary(all) {
    var rules = {};
    all.violations.forEach(function (v) { rules[v.id] = v.nodes.length; });
    return { axe: w.axe.version, violations: rules, incomplete: all.incomplete.length };
  }

  // ------------------------------------------------------------ rendering

  var TYPE_ORDER = { content: 0, settings: 1, theme: 2, plugin: 3, core: 4, unknown: 5 };

  function group(list) {
    var groups = {}, keys = [];
    list.forEach(function (v) {
      v.nodes.forEach(function (n) {
        var node = n.element;
        if (!node) return;
        var a = attribute(node, v.id, n);
        var key = a.owner.type + '|' + a.owner.label;
        if (!groups[key]) { groups[key] = { owner: a.owner, rules: {}, order: [], count: 0 }; keys.push(key); }
        var g = groups[key];
        // The heading says "most likely" only when every problem in the group is a guess.
        if (g.owner.guess && !a.owner.guess) g.owner = a.owner;
        if (!g.rules[v.id]) { g.rules[v.id] = { v: v, nodes: [] }; g.order.push(v.id); }
        g.rules[v.id].nodes.push({ n: n, a: a });
        g.count++;
      });
    });
    keys.sort(function (x, y) {
      var a = groups[x].owner, b = groups[y].owner;
      return (TYPE_ORDER[a.type] - TYPE_ORDER[b.type]) || a.label.localeCompare(b.label);
    });
    return keys.map(function (k) { return groups[k]; });
  }

  function title(v) {
    return (C.plain !== false && TITLES[v.id]) || v.help;
  }

  function render(scan) {
    results.textContent = '';
    var vio = scan.all.violations, inc = scan.all.incomplete;
    var total = vio.reduce(function (s, v) { return s + v.nodes.length; }, 0);
    var review = inc.reduce(function (s, v) { return s + v.nodes.length; }, 0);
    var sum = el('div', { className: 'summary', role: 'status' }, [
      el('strong', { text: total === 0 ? S.summaryNone : (total === 1 ? S.summaryOne : fmt(S.summaryMany, { n: total })) }),
      review ? el('div', { text: fmt(S.summaryReview, { n: review }) }) : null
    ]);
    results.appendChild(sum);

    group(vio).forEach(function (g) { results.appendChild(renderGroup(g, false)); });

    if (review) {
      var gi = { owner: { type: 'review', label: S.needsPerson }, rules: {}, order: [], count: review };
      inc.forEach(function (v) {
        gi.rules[v.id] = { v: v, nodes: v.nodes.filter(function (n) { return n.element; }).map(function (n) { return { n: n, a: attribute(n.element, v.id, n) }; }) };
        gi.order.push(v.id);
      });
      results.appendChild(renderGroup(gi, true));
    }

    var bad = {};
    vio.forEach(function (v) { bad[v.cat] = 1; });
    var ok = scan.cats.filter(function (c) { return !bad[c]; });
    if (ok.length) {
      results.appendChild(el('h4', { text: S.passedTitle }));
      results.appendChild(el('ul', { className: 'passed' }, ok.map(function (c) { return el('li', { text: CATS[c] || c }); })));
    }

    if (cfg.tier === 'admin') {
      results.appendChild(el('div', { className: 'actions' }, [
        el('button', { type: 'button', className: 'secondary', text: S.exportMd, onclick: exportMd })
      ]));
    }
    results.appendChild(el('p', { className: 'honest', text: S.honest }));
    if (d.querySelector('iframe')) results.appendChild(el('p', { className: 'honest', text: S.iframes }));
    H.announce ? H.announce(sum.textContent) : null;
  }

  function renderGroup(g, isReview) {
    var desc = isReview ? S.needsPersonDesc : (S['owner_' + g.owner.type] || '');
    var label = g.owner.label + (g.owner.guess ? ' (' + S.fromLikely + ')' : '');
    var box = el('section', { className: 'owner' }, [
      el('h4', {}, [el('span', { text: label }), el('span', { className: 'count', text: String(g.count) })]),
      desc ? el('p', { text: desc }) : null
    ]);
    var ul = el('ul', { className: 'rules' });
    g.order.forEach(function (id) {
      var r = g.rules[id];
      if (!r.nodes.length) return;
      ul.appendChild(renderRule(r, isReview));
    });
    box.appendChild(ul);
    return box;
  }

  function renderRule(r, isReview) {
    var v = r.v, admin = cfg.tier === 'admin';
    var li = el('li', {}, [
      el('div', { className: 'rule-title', text: title(v) + (r.nodes.length > 1 ? ' (' + r.nodes.length + ')' : '') }),
      title(v) !== v.help ? el('div', { className: 'rule-help', text: v.help }) : null
    ]);
    var nodes = el('ul', { className: 'nodes' });
    var limit = admin ? 10 : 5;
    r.nodes.slice(0, limit).forEach(function (x, i) {
      var item = el('li');
      var label = title(v);
      var btn = el('button', {
        type: 'button', className: 'showme', text: S.showMe + (r.nodes.length > 1 ? ' ' + (i + 1) : ''),
        'aria-label': S.showMe + ': ' + label + (r.nodes.length > 1 ? ' ' + (i + 1) : ''),
        onclick: function () {
          H.onUnpeek = function () { btn.focus(); };
          H.peek(x.n.element, label, S.backToResults);
        }
      });
      item.appendChild(btn);
      var cdata = contrastData(x.n);
      if (cdata) {
        var tip = fmt(S.contrastNow, { r: cdata.ratio, need: cdata.need });
        var fix = suggest(cdata.fg, cdata.bg, cdata.need);
        if (fix) tip += ' ' + fmt(S.contrastTry, { color: fix });
        item.appendChild(el('span', { className: 'where', text: tip }));
      }
      if (x.a && (x.a.fgOwner || x.a.bgOwner) && x.a.fgOwner !== x.a.bgOwner) {
        item.appendChild(el('span', { className: 'where', text: S.textColor + ': ' + ((x.a.fgOwner && x.a.fgOwner.label) || '—') + ' · ' + S.background + ': ' + ((x.a.bgOwner && x.a.bgOwner.label) || '—') }));
      }
      if (admin) item.appendChild(el('div', { className: 'tech', text: techText(v, x) }));
      nodes.appendChild(item);
    });
    if (r.nodes.length > limit) nodes.appendChild(el('li', { text: fmt(S.andMore, { n: r.nodes.length - limit }) }));
    li.appendChild(nodes);
    if (admin && v.helpUrl) {
      li.appendChild(el('div', { className: 'tech' }, [el('a', { href: v.helpUrl, target: '_blank', rel: 'noopener', text: v.id + ' (axe rule)' })]));
    }
    return li;
  }

  function contrastData(n) {
    var chk = (n.any || []).concat(n.all || [], n.none || []).filter(function (c) { return c.data && c.data.contrastRatio; })[0];
    if (!chk) return null;
    var dd = chk.data;
    return { ratio: dd.contrastRatio, need: parseFloat(String(dd.expectedContrastRatio).replace(':1', '')) || 4.5, fg: dd.fgColor, bg: dd.bgColor };
  }

  function techText(v, x) {
    var lines = ['Selector: ' + (x.n.target || []).join(' '), 'HTML: ' + String(x.n.html || '').slice(0, 240)];
    var c = contrastData(x.n);
    if (c) {
      lines.push('Colors: ' + c.fg + ' on ' + c.bg + ' = ' + c.ratio + ':1 (needs ' + c.need + ':1)');
      lines.push('Text color from: ' + originText(x.a.fg));
      lines.push('Background from: ' + originText(x.a.bg));
    }
    if (x.n.failureSummary) lines.push(x.n.failureSummary.replace(/\n\s*/g, ' '));
    return lines.join('\n');
  }

  function exportMd() {
    if (!lastScan) return;
    var out = ['# Accessibility check', '',
      '- Page: ' + lastScan.url,
      '- Checked: ' + lastScan.when.toISOString(),
      '- Engine: axe-core ' + lastScan.version + ' (' + (C.tags || []).join(', ') + ')', ''];
    function section(groups, heading) {
      if (!groups.length) return;
      out.push('## ' + heading, '');
      groups.forEach(function (g) {
        out.push('### ' + g.owner.label + (g.owner.guess ? ' (most likely)' : '') + ' — ' + g.count, '');
        g.order.forEach(function (id) {
          var r = g.rules[id];
          out.push('#### ' + title(r.v) + ' (`' + id + '`, ' + r.nodes.length + ')', '', r.v.helpUrl ? r.v.helpUrl : '', '');
          r.nodes.forEach(function (x) {
            out.push('- `' + (x.n.target || []).join(' ') + '`');
            out.push('  - HTML: `' + String(x.n.html || '').replace(/`/g, "'").slice(0, 300) + '`');
            var c = contrastData(x.n);
            if (c) {
              out.push('  - Contrast: ' + c.fg + ' on ' + c.bg + ' = ' + c.ratio + ':1, needs ' + c.need + ':1; nearest passing text color ' + suggest(c.fg, c.bg, c.need));
              out.push('  - Text color from: ' + originText(x.a.fg) + (x.a.fgOwner ? ' (' + x.a.fgOwner.label + ')' : ''));
              out.push('  - Background from: ' + originText(x.a.bg) + (x.a.bgOwner ? ' (' + x.a.bgOwner.label + ')' : ''));
            }
          });
          out.push('');
        });
      });
    }
    section(group(lastScan.all.violations), 'Problems');
    section(group(lastScan.all.incomplete), 'Needs a person to check');
    var text = out.join('\n');
    try { navigator.clipboard.writeText(text); } catch (e) {}
    var a = d.createElement('a');
    a.href = URL.createObjectURL(new Blob([text], { type: 'text/markdown' }));
    a.download = 'accessibility-check-' + new Date().toISOString().slice(0, 10) + '.md';
    d.body.appendChild(a);
    a.click();
    setTimeout(function () { URL.revokeObjectURL(a.href); a.remove(); }, 1000);
    status.textContent = S.exported;
  }

  function mount(p, ctx) {
    root = p;
    H.announce = ctx.announce;
    status = el('p', { className: 'intro', role: 'status' });
    runBtn = el('button', { type: 'button', className: 'primary', text: S.checkRun, onclick: run });
    progressBar = el('span');
    progress = el('div', { className: 'progress', hidden: true, 'aria-hidden': 'true' }, [progressBar]);
    results = el('div', { className: 'results' });
    p.appendChild(el('p', { className: 'intro', text: S.checkIntro }));
    p.appendChild(el('div', { className: 'run' }, [runBtn, progress]));
    p.appendChild(status);
    p.appendChild(results);
    if (lastScan) render(lastScan);
  }

  w.HandrailCheck = { mount: mount };
})(window, document);
