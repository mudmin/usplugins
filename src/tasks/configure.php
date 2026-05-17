<?php if (!in_array($user->data()->id, $master_account)) {
  Redirect::to($us_url_root . 'users/admin.php');
} //only allow master accounts to manage plugins! 
include "plugin_info.php";
$method = Input::get('method');
$method = basename($method); // prevent path traversal
$method = preg_replace('/[^a-zA-Z0-9_-]/', '', $method); // sanitize to alphanumeric
$basePage = "admin.php?view=plugins_config&plugin=tasks&";
$plg_settings = $db->query("SELECT * FROM plg_tasks_settings")->first();

pluginActive($plugin_name);
if (!empty($_POST)) {
  if (!Token::check(Input::get('csrf'))) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
  }
}
?>

<div class="content mt-3">
  <div class="alert alert-light border py-2 px-3 small text-muted" role="note">
    <i class="fa fa-info-circle mr-1"></i>
    <strong>CSP note:</strong> some task screens load the Select2 library from <code>https://cdnjs.cloudflare.com</code>. If your site sends a <em>Content-Security-Policy</em> header, add that origin to <code>script-src</code> (and <code>style-src</code>) or those controls will not load.
  </div>
  <?php
  include $abs_us_root . $us_url_root . "usersc/plugins/tasks/assets/menu.php";

  // Hardening: Create a whitelist of existing PHP files in the assets directory
  $assetPath = $abs_us_root . $us_url_root . "usersc/plugins/tasks/assets/";
  $alternatePath = $abs_us_root . $us_url_root . $plg_settings->alternate_location . "assets/";
  
  $allowedAssets = array_map('basename', glob($assetPath . "*.php"));
  $allowedAlternates = array_map('basename', glob($alternatePath . "*.php"));

  $targetFile = $method . ".php";

  if ($method != "" && in_array($targetFile, $allowedAssets)) {
    include $assetPath . $targetFile;
  } elseif ($method != "" && in_array($targetFile, $allowedAlternates)) {
    include $alternatePath . $targetFile;
  } elseif (isset($is_task_admin) && $is_task_admin == true) {
    include $assetPath . "home.php";
  } else {
    include $assetPath . "tasks.php";
  }
  ?>
</div>


<!-- Do not close the content mt-3 div in this file -->