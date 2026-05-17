<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
// Reuse core's nonce if present; otherwise self-provide one (older UserSpice).
if (!isset($GLOBALS['userspice_nonce'])) {
    $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
}
?>
<script nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
window.onload = function() {
  document.getElementById("remember").checked = true;
}
</script>
