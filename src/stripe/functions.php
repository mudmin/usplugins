<?php
//Please don't load functions system-wide if you don't need them system-wide.
// bold("<br>Demo Functions Loaded");

// To make your plugin more efficient on resources, consider only loading resources that need to be loaded when they need to be loaded.
// For instance, you can do
if(!isset($currentPage)){
$currentPage = currentPage();
}

function stripeSafeReturn($string)
{
  return htmlspecialchars($string ?? "", ENT_QUOTES, 'UTF-8');
}

/**
 * Safe redirect to HTTPS - prevents open redirect via Host header injection
 */
function stripeSafeHttpsRedirect()
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    // Strip control chars and dangerous characters
    $host = preg_replace('/[\x00-\x1F\x7F\/\\\\ ]/', '', $host);
    // Remove port if present
    if (($pos = strpos($host, ':')) !== false && strpos($host, ']') === false) {
        $host = substr($host, 0, $pos);
    }
    // Validate hostname format (basic check for valid characters)
    if (!preg_match('/^[a-zA-Z0-9.-]+$/', $host) || $host === '') {
        die("Invalid host");
    }

    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    // Strip control chars
    $uri = preg_replace('/[\x00-\x1F\x7F]/', '', $uri);
    // Ensure starts with /
    if ($uri === '' || $uri[0] !== '/') {
        $uri = '/' . ltrim($uri, '/');
    }
    // Remove CRLF for header injection prevention
    $uri = str_replace(["\r", "\n", "\\"], '', $uri);

    header('Location: https://' . $host . $uri, true, 301);
    die("Your connection is not secure.");
}