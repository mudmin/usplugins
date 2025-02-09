<?php if (!in_array($user->data()->id, $master_account)) {
  Redirect::to($us_url_root . 'users/admin.php');
} //only allow master accounts to manage plugins! 
include "plugin_info.php";
$method = Input::get('method');
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
  <?php
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


<!-- Do not close the content mt-3 div in this file -->