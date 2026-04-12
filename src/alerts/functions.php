<?php
if (!function_exists('alerts_sanitizeMessage')) {
  /**
   * Decode entity-encoded alert text and whitelist a small set of formatting tags
   * (<ul>, <li>, <b>, <strong>, <u>) before it is emitted into a JS string literal.
   * Attributes on allowed tags are stripped so nothing like <b onclick=...> survives.
   *
   * @psalm-taint-escape html
   * @psalm-taint-escape text
   */
  function alerts_sanitizeMessage($msg): string
  {
    $msg = html_entity_decode((string)$msg, ENT_QUOTES, 'UTF-8');
    $msg = strip_tags($msg, '<ul><li><b><strong><u>');
    return preg_replace('#<(/?)(ul|li|b|strong|u)(\s[^>]*)?>#i', '<$1$2>', $msg) ?? '';
  }
}
