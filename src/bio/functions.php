<?php
//Please don't load functions system-wide if you don't need them system-wide.
// To make your plugin more efficient on resources, consider only loading resources that need to be loaded when they need to be loaded.
// For instance, you can do
// $currentPage = currentPage();
// if($currentPage == 'admin.php'){ //The administrative dashboard
//   bold("<br>See! I am only loading this when I need it!");
// }
// // Also, please wrap your functions in if(!function_exists())
// if(!function_exists('bioFunction')) {
//   function bioFunction(){ }
// }

if(!function_exists('bioSafeUrl')) {
  /**
   * Returns true if a URL is safe to use in an href/src attribute.
   * Rejects javascript:, vbscript:, data: and file: schemes (including
   * variants obfuscated with embedded control characters/whitespace).
   */
  function bioSafeUrl($url) {
    $stripped = preg_replace('/[\x00-\x20]+/', '', (string)$url);
    if ($stripped === '') {
      return false;
    }
    if (preg_match('/^(javascript|vbscript|data|file):/i', $stripped)) {
      return false;
    }
    // HTML entities can hide a scheme (e.g. &#106;avascript:) — decode and recheck.
    $decoded = html_entity_decode($stripped, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (preg_match('/^(javascript|vbscript|data|file):/i', $decoded)) {
      return false;
    }
    return true;
  }
}

if(!function_exists('bioSafeStyle')) {
  /**
   * Filters an inline style attribute down to a small allowlist of
   * presentational CSS properties. Drops anything that can execute code
   * or load remote content (expression(), url(), @import, etc.).
   */
  function bioSafeStyle($style) {
    $allowedProps = [
      'color', 'background-color', 'background',
      'font-size', 'font-family', 'font-weight', 'font-style',
      'text-align', 'text-decoration', 'line-height', 'width', 'height',
    ];
    $out = [];
    foreach (explode(';', (string)$style) as $decl) {
      if (strpos($decl, ':') === false) {
        continue;
      }
      list($prop, $val) = explode(':', $decl, 2);
      $prop = strtolower(trim($prop));
      $val = trim($val);
      if ($val === '' || !in_array($prop, $allowedProps, true)) {
        continue;
      }
      if (preg_match('/(expression|url\s*\(|javascript:|vbscript:|@import|<|>)/i', $val)) {
        continue;
      }
      $out[] = $prop . ': ' . $val;
    }
    return implode('; ', $out);
  }
}

if(!function_exists('bioSanitizeHtml')) {
  /**
   * Sanitizes rich-text HTML (e.g. a Summernote-edited bio).
   *
   * Keeps safe presentational markup (bold/underline/colors/fonts/lists/
   * tables/links) but strips <script> and other active-content tags,
   * on* event-handler attributes, dangerous URLs and unsafe inline CSS.
   * Parsing is done with DOMDocument (an allowlist walk) rather than
   * regex so malformed/obfuscated markup cannot slip a payload through.
   *
   * @param  string|array $html Raw HTML (or an array of them).
   * @return string|array Sanitized HTML safe to echo directly.
   */
  function bioSanitizeHtml($html) {
    if (is_array($html)) {
      return array_map('bioSanitizeHtml', $html);
    }
    $html = (string)$html;
    if (trim($html) === '') {
      return '';
    }

    // Tags removed entirely, together with everything inside them.
    $kill = [
      'script', 'style', 'iframe', 'object', 'embed', 'form', 'input',
      'button', 'textarea', 'select', 'option', 'link', 'meta', 'base',
      'applet', 'noscript', 'svg', 'math', 'frame', 'frameset', 'title',
    ];

    // Allowed tag => extra attributes permitted on that tag.
    $globalAttrs = ['style', 'class', 'align'];
    $allowed = [
      'p' => [], 'br' => [], 'hr' => [], 'div' => [], 'span' => [],
      'b' => [], 'strong' => [], 'i' => [], 'em' => [], 'u' => [],
      's' => [], 'strike' => [], 'sub' => [], 'sup' => [], 'small' => [],
      'mark' => [], 'abbr' => ['title'],
      'h1' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'h5' => [], 'h6' => [],
      'blockquote' => [], 'pre' => [], 'code' => [],
      'ul' => [], 'ol' => ['start', 'type'], 'li' => [],
      'a' => ['href', 'title', 'target', 'rel', 'name'],
      'font' => ['face', 'color', 'size'],
      'img' => ['src', 'alt', 'width', 'height'],
      'table' => ['border', 'cellpadding', 'cellspacing', 'width'],
      'thead' => [], 'tbody' => [], 'tfoot' => [], 'caption' => [],
      'tr' => [], 'td' => ['colspan', 'rowspan', 'width', 'height'],
      'th' => ['colspan', 'rowspan', 'width', 'height', 'scope'],
    ];

    $dom = new DOMDocument('1.0', 'UTF-8');
    $prevErrors = libxml_use_internal_errors(true);
    // The XML encoding hint keeps UTF-8 intact; LIBXML_NONET blocks network access.
    $loaded = $dom->loadHTML(
      '<?xml encoding="UTF-8">' . $html,
      LIBXML_NOERROR | LIBXML_NONET | LIBXML_NOWARNING
    );
    libxml_clear_errors();
    libxml_use_internal_errors($prevErrors);

    $body = $loaded ? $dom->getElementsByTagName('body')->item(0) : null;
    if (!$body) {
      // Unparseable — fall back to a guaranteed-safe plain-text rendering.
      return htmlspecialchars(strip_tags($html), ENT_QUOTES, 'UTF-8');
    }

    $clean = function ($node) use (&$clean, $kill, $allowed, $globalAttrs) {
      // Snapshot children first; editing the live NodeList while iterating skips nodes.
      $children = [];
      foreach ($node->childNodes as $child) {
        $children[] = $child;
      }
      foreach ($children as $child) {
        if ($child->nodeType === XML_COMMENT_NODE) {
          $node->removeChild($child);
          continue;
        }
        if ($child->nodeType !== XML_ELEMENT_NODE) {
          continue; // Text nodes are escaped on serialization — leave them.
        }
        $tag = strtolower($child->nodeName);
        if (in_array($tag, $kill, true)) {
          $node->removeChild($child);
          continue;
        }
        if (!isset($allowed[$tag])) {
          // Unknown but not actively dangerous: drop the tag, keep its text.
          $clean($child);
          while ($child->firstChild) {
            $node->insertBefore($child->firstChild, $child);
          }
          $node->removeChild($child);
          continue;
        }
        // Allowed tag: strip every attribute not on its allowlist.
        $okAttrs = array_merge($globalAttrs, $allowed[$tag]);
        $attrNames = [];
        foreach ($child->attributes as $attr) {
          $attrNames[] = $attr->nodeName;
        }
        foreach ($attrNames as $attrName) {
          $lower = strtolower($attrName);
          if (substr($lower, 0, 2) === 'on' || !in_array($lower, $okAttrs, true)) {
            $child->removeAttribute($attrName);
            continue;
          }
          $val = $child->getAttribute($attrName);
          if (($lower === 'href' || $lower === 'src') && !bioSafeUrl($val)) {
            $child->removeAttribute($attrName);
            continue;
          }
          if ($lower === 'style') {
            $safe = bioSafeStyle($val);
            if ($safe === '') {
              $child->removeAttribute($attrName);
            } else {
              $child->setAttribute($attrName, $safe);
            }
          }
        }
        // Links that open a new tab must not leak the opener window.
        if ($tag === 'a' && $child->getAttribute('target') !== '') {
          $child->setAttribute('rel', 'noopener noreferrer nofollow');
        }
        $clean($child);
      }
    };
    $clean($body);

    $out = '';
    foreach ($body->childNodes as $child) {
      $out .= $dom->saveHTML($child);
    }
    return trim($out);
  }
}
