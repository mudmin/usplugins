<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $settings;
if (!isset($GLOBALS['userspice_nonce'])) {
    $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
}
foreach(json_decode($settings->usermod) as $um){?>
  <script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
    $("#<?=$um?>").attr("required",false); //fix this
    $("#<?=$um?>").hide();
  </script>
<?php } ?>
