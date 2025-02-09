<?php
require_once "../users/init.php";

if (!isset($user) || !$user->isLoggedIn()) {
  Redirect::to($us_url_root . 'users/login.php');
}
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';


$method = Input::get('method');
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

  if (file_exists($abs_us_root . $us_url_root . "usersc/plugins/tasks/assets/" . $method . ".php")) {
    include $abs_us_root . $us_url_root . "usersc/plugins/tasks/assets/" . $method . ".php";
  } elseif (file_exists($abs_us_root . $us_url_root . $plg_settings->alternate_location . "assets/" . $method . ".php")) {
    include $abs_us_root . $us_url_root . $plg_settings->alternate_location . "assets/" . $method . ".php";
  } elseif (isset($is_task_admin) && $is_task_admin == true) {
    include $abs_us_root . $us_url_root . "usersc/plugins/tasks/assets/home.php";
  } else {
    include $abs_us_root . $us_url_root . "usersc/plugins/tasks/assets/tasks.php";
  }
  ?>
</div>
<?php
require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>