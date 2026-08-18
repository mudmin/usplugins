<?php if (count(get_included_files()) == 1) die(); //Direct Access Not Permitted
/*
 * SAMPLE HOOK - position: bottom
 *
 * "bottom" is the last thing on the page, which makes it the right spot for
 * javascript. UserSpice sends a Content Security Policy, so every <script> tag
 * needs the nonce below or the browser will refuse to run it.
 *
 * For the current list of pages, positions and events, see
 * https://userspice.com/plugin-hooks/
 */
// Reuse core's nonce if present; otherwise self-provide one (older UserSpice).
if (!isset($GLOBALS['userspice_nonce'])) {
    $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
}
?>
<script nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
  console.log('Hello from hooker_sample_bottom.php');
</script>
