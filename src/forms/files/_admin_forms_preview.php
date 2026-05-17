<?php
// Reuse core's nonce if present; otherwise self-provide one (older UserSpice).
if (!isset($GLOBALS['userspice_nonce'])) {
    $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
}
?>
<div class="content mt-3">
  <?php require_once($abs_us_root.$us_url_root.'usersc/plugins/forms/files/_form_manager_menu.php');?>

  <div class="row">
    <div class="col-sm-12">
      <?php
      $toDisplay = Input::get('demo');
      if(is_numeric($toDisplay)){
        displayView($toDisplay,['nosubmit'=>1]);
      }
      ?>
    </div>
  </div>
</div>


    <script nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
    $(document).ready(function() {
    });
  </script>
