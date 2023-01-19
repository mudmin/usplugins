<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
include($abs_us_root.$us_url_root.'usersc/plugins/cms/assets/backend_functions.php');
?>

<style media="screen">
hr {
border: 0;
clear:both;
display:block;
width: 96%;
background-color:#FFFF00;
height: 1px;
}
/* .dropdown-menu{margin-top:2.2em !important;} */
</style>

<div class="row">
  <div class="col-12 btn-group">

    <div class="dropdown">
      <button class="btn btn-primary dropdown-toggle" type="button" id="contentdropdownMenuButton" data-toggle="dropdown" data-bs-toggle="dropdown"" aria-haspopup="true" aria-expanded="false">
        Content
      </button>
      <div class="dropdown-menu" aria-labelledby="contentdropdownMenuButton">
        <a class="dropdown-item" href="admin.php?view=plugins_config&plugin=cms&method=content_new">New Content</a>
        <a class="dropdown-item" href="admin.php?view=plugins_config&plugin=cms&method=content_edit">Edit Content</a>
        <a class="dropdown-item" href="admin.php?view=plugins_config&plugin=cms&method=charts&action=content">All Content</a>
      </div>
    </div>

    <div class="dropdown">
      <button class="btn btn-secondary dropdown-toggle" type="button" id="layoutdropdownMenuButton" data-toggle="dropdown" data-bs-toggle="dropdown"" aria-haspopup="true" aria-expanded="false">
        Layouts
      </button>
      <div class="dropdown-menu" aria-labelledby="layoutdropdownMenuButton">
        <a class="dropdown-item" href="admin.php?view=plugins_config&plugin=cms&method=layout&action=new">New Layout</a>
        <a class="dropdown-item" href="admin.php?view=plugins_config&plugin=cms&method=layout&action=edit">Edit Layout</a>
        <a class="dropdown-item" href="admin.php?view=plugins_config&plugin=cms&method=charts&action=layout">All Layouts</a>
      </div>
    </div>
    <div class="dropdown">
      <button class="btn btn-warning dropdown-toggle" type="button" id="widgetdropdownMenuButton" data-toggle="dropdown" data-bs-toggle="dropdown"" aria-haspopup="true" aria-expanded="false">
        Widgets
      </button>
      <div class="dropdown-menu" aria-labelledby="widgetdropdownMenuButton">
        <a class="dropdown-item" href="admin.php?view=plugins_config&plugin=cms&method=widget_new&action=new">New Widget</a>
        <a class="dropdown-item" href="admin.php?view=plugins_config&plugin=cms&method=widget_edit&action=edit">Edit Widget</a>
        <a class="dropdown-item" href="admin.php?view=plugins_config&plugin=cms&method=charts&action=widget">All Widgets</a>
      </div>
    </div>
    <div class="dropdown">
      <button class="btn btn-success dropdown-toggle" type="button" id="categorydropdownMenuButton" data-toggle="dropdown" data-bs-toggle="dropdown"" aria-haspopup="true" aria-expanded="false">
        Categories
      </button>
      <div class="dropdown-menu" aria-labelledby="categorydropdownMenuButton">
        <a class="dropdown-item" href="admin.php?view=plugins_config&plugin=cms&method=category">Manage Categories</a>
      </div>
    </div>
    <div class="dropdown">
      <button class="btn btn-info dropdown-toggle" type="button" id="settingsdropdownMenuButton" data-toggle="dropdown" data-bs-toggle="dropdown"" aria-haspopup="true" aria-expanded="false">
        Settings
      </button>
      <div class="dropdown-menu" aria-labelledby="settingsdropdownMenuButton">
        <a class="dropdown-item" href="admin.php?view=plugins_config&plugin=cms&method=settings">Plugin Settings</a>
        </div>
    </div>
  </div>
</div>
