<?php
// AI Prompts plugin — helper functions.
//
// Loaded on every page when the plugin is active. Functions are guarded with
// function_exists() per the plugin best-practices guide.
//
// File layout:
//   usersc/plugins/ai_prompts/prompts/{name}.md.php          # shipped (updates)
//   usersc/plugins/ai_prompts/custom_prompts/{name}.md.php   # local (survives updates)
//
// Each prompt file starts with a one-line PHP wrapper that calls __halt_compiler(),
// followed by markdown. The wrapper prevents direct HTTP access on every web server
// that runs PHP — and __halt_compiler() halts the lexer, so PHP doesn't choke on
// any inline code examples in the markdown.

if (!function_exists('aiPromptsDir')) {
  /** Absolute filesystem path to the shipped prompts folder, with trailing slash. */
  function aiPromptsDir() {
    global $abs_us_root, $us_url_root;
    return $abs_us_root . $us_url_root . 'usersc/plugins/ai_prompts/prompts/';
  }
}

if (!function_exists('aiPromptsCustomDir')) {
  /** Absolute filesystem path to the custom_prompts folder, with trailing slash. */
  function aiPromptsCustomDir() {
    global $abs_us_root, $us_url_root;
    return $abs_us_root . $us_url_root . 'usersc/plugins/ai_prompts/custom_prompts/';
  }
}

if (!function_exists('aiPromptPath')) {
  /**
   * Resolve a prompt name to its file path. custom_prompts/ wins over prompts/.
   * Returns the absolute path string, or false if no matching file exists.
   *
   * @param string $name  Prompt name without extension, e.g. '00_start_here'
   */
  function aiPromptPath($name) {
    // Allow only safe filename characters — no path traversal.
    if (!preg_match('/^[A-Za-z0-9_\-]+$/', $name)) {
      return false;
    }
    $custom  = aiPromptsCustomDir() . $name . '.md.php';
    $shipped = aiPromptsDir()       . $name . '.md.php';
    if (is_file($custom))  { return $custom;  }
    if (is_file($shipped)) { return $shipped; }
    return false;
  }
}

if (!function_exists('aiPromptRead')) {
  /**
   * Read a prompt and return its markdown body (PHP wrapper stripped).
   * Returns false if the prompt doesn't exist.
   */
  function aiPromptRead($name) {
    $path = aiPromptPath($name);
    if ($path === false) { return false; }
    $raw = file_get_contents($path);
    if ($raw === false) { return false; }
    // Strip the leading PHP wrapper. Everything after the first close-tag is markdown.
    $closeTag = '?' . '>';
    $pos = strpos($raw, $closeTag);
    if ($pos === false) { return $raw; }
    return ltrim(substr($raw, $pos + 2));
  }
}

if (!function_exists('aiPromptList')) {
  /**
   * List every available prompt, merging shipped + custom. Custom names
   * override shipped names of the same basename.
   *
   * Returns an associative array keyed by prompt name:
   *   [
   *     '00_start_here' => [
   *        'name'   => '00_start_here',
   *        'path'   => '/abs/path/to/file.md.php',
   *        'source' => 'shipped' | 'custom' | 'overridden',
   *     ],
   *     ...
   *   ]
   *
   * 'overridden' = a custom file exists with the same name as a shipped one
   * (the custom version wins; both files exist on disk).
   */
  function aiPromptList() {
    $out = [];

    foreach (glob(aiPromptsDir() . '*.md.php') ?: [] as $f) {
      $name = basename($f, '.md.php');
      $out[$name] = ['name' => $name, 'path' => $f, 'source' => 'shipped'];
    }

    foreach (glob(aiPromptsCustomDir() . '*.md.php') ?: [] as $f) {
      $name = basename($f, '.md.php');
      $source = isset($out[$name]) ? 'overridden' : 'custom';
      $out[$name] = ['name' => $name, 'path' => $f, 'source' => $source];
    }

    ksort($out);
    return $out;
  }
}
