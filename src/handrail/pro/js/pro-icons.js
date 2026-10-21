/*! Handrail Pro: icons for the Pro tools in the panel. */
(function (w) {
  var H = w.Handrail;
  if (!H) return;
  function icon(paths) {
    return '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' + paths + '</svg>';
  }
  var ICONS = {
    'read-aloud': icon('<path d="M4 9v6h4l5 4V5L8 9z"/><path d="M16.5 8.5a5 5 0 0 1 0 7M19 6a8.5 8.5 0 0 1 0 12"/>'),
    guide: icon('<path d="M4 6h16M4 18h10"/><rect x="2" y="10" width="20" height="4" rx="1" fill="currentColor" stroke="none"/>'),
    mask: icon('<rect x="2" y="3" width="20" height="18" rx="2"/><path d="M2 9h20M2 15h20"/><path d="M2 3h20v6H2zM2 15h20v6H2z" fill="currentColor" stroke="none" opacity=".35"/>'),
    'dyslexia-font': icon('<path d="M4 19l5-14 5 14M6 14h6"/><path d="M16 19V9.5a2.5 2.5 0 0 1 5 0V19M16 14h5"/>'),
    cursor: icon('<path d="M5 3l14 7-6 2-2 6z"/>'),
    'color-filter': icon('<circle cx="9" cy="9" r="6"/><circle cx="15" cy="15" r="6"/>'),
    'hide-images': icon('<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 16l5-5 4 4 3-3 6 6M3 3l18 18"/>'),
    'text-align': icon('<path d="M4 6h16M4 10h10M4 14h16M4 18h10"/>')
  };
  for (var k in ICONS) H.icons[k] = ICONS[k];
})(window);
