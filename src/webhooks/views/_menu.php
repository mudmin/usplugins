<?php
if(count(get_included_files()) ==1) die();
?>
<div class="row">
  <div class="col-3 col-sm-2 text-center">
    <a href="admin.php?view=plugins_config&plugin=webhooks" class="btn btn-primary">Home</a>
  </div>
  <div class="col-3 col-sm-2 text-center">
    <a href="admin.php?view=plugins_config&plugin=webhooks&method=docs" class="btn btn-primary">Documentation</a>
  </div>
  <div class="col-3 col-sm-2 text-center">
    <a href="admin.php?view=plugins_config&plugin=webhooks&method=activity" class="btn btn-primary">Activity Logs</a>
  </div>
</div>
