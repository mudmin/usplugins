<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted ?>
<style media="screen">
  p {
    color:black;
  }
</style>
<br>
<div class="row">
  <div class="col-2 mlink">
    <a class="btn btn-primary" href="admin.php?view=plugins_config&plugin=downloads&v=home">Settings</a>
  </div>

  <div class="col-2 mlink">
    <a class="btn btn-primary" href="admin.php?view=plugins_config&plugin=downloads&v=files">Manage Files</a>
  </div>

  <div class="col-2 mlink">
    <a class="btn btn-primary" href="admin.php?view=plugins_config&plugin=downloads&v=links">Manage Links</a>
  </div>

  <div class="col-2 mlink">
    <a class="btn btn-primary" href="admin.php?view=plugins_config&plugin=downloads&v=logs">Download Logs</a>
  </div>

  <div class="col-2 mlink">
    <a class="btn btn-primary" href="admin.php?view=plugins_config&plugin=downloads&v=documentation">Documentation</a>
  </div>
</div>
