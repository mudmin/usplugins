<?php
require_once "../users/init.php";

if (!isset($user) || !$user->isLoggedIn()) {
  Redirect::to($us_url_root . 'users/login.php');
}
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';


$method = Input::get('method');
$method = basename($method); // prevent path traversal
$method = preg_replace('/[^a-zA-Z0-9_-]/', '', $method); // sanitize to alphanumeric
if (!empty($_POST)) {
  $token = $_POST['csrf'];
  if (!Token::check($token)) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
  }
}
$basePage = "?";
?>

<div class="content mt-3 pt-3">
  <?php
  $plg_settings = $db->query("SELECT * FROM plg_tasks_settings")->first();
  include $abs_us_root . $us_url_root . "usersc/plugins/tasks/assets/menu.php";

  // Hardening: Create a whitelist of existing PHP files
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
<?php
require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>