<?php
//This file parses all the various messages that are stored in
//$_GET and $_SESSION variables and displays them



//use the default logic if the plugin is deactivated but not uninstalled

if(!pluginActive("alerts",true)){
include $abs_us_root.$us_url_root."users/includes/system_messages_footer.php";
} ?>
<style media="screen">
.d-none {
  display: none!important;
}
</style>
