<?php if (count(get_included_files()) == 1) die(); //Direct Access Not Permitted
require_once $abs_us_root . $us_url_root . 'usersc/plugins/tasks/assets/dynamic.php';

?>
<div class="row">
  <div class="col-12">
    <h3>
      <?php
      echo $plg_settings->plugin_name;

      if ($is_task_admin) {
        $class = ($method == "" || $method == "home") ? "btn-primary" : "btn-outline-primary";
      ?>
        <a href="<?= $basePage ?>" class="btn <?= $class ?> btn-sm mb-2">Home</a>



      <?php }
      //non admin links
      $class = ($method == "tasks") ? "btn-primary" : "btn-outline-primary";
      ?>
      <a href="<?= $basePage ?>method=tasks" class="btn <?= $class ?> btn-sm mb-2">Your <?= $plg_settings->plural_term ?></a>


      <?php
      //note that this is outside of the plugin folder
      if (file_exists($abs_us_root . $us_url_root . $plg_settings->alternate_location . '/includes/custom_menu.php')) { ?>
        <?php include $abs_us_root . $us_url_root . $plg_settings->alternate_location . '/includes/custom_menu.php'; ?>
      <?php }
      //userspice admin 
      if (hasPerm(2)) {
        $class = ($method == "settings") ? "btn-primary" : "btn-outline-primary"; ?>
        <a href="<?= $basePage ?>method=settings" class="btn <?= $class ?> btn-sm mb-2">Settings</a>
        <?php $class = ($method == "docs") ? "btn-primary" : "btn-outline-primary"; ?>
        <a href="<?= $basePage ?>method=docs" class="btn <?= $class ?> btn-sm mb-2">Documentation</a>
      <?php } ?>


    </h3>
  </div>
</div>


<?php
if (isset($_SESSION['launchTaskConfetti'])) {
  echo "<script>$(document).ready(function() {launchTaskConfetti()});</script>";
  unset($_SESSION['launchTaskConfetti']);
}
