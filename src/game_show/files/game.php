<?php
require_once "users/init.php";
if(!pluginActive("game_show",true)){
  die("The game_show plugin is not active");
}

require_once $abs_us_root . $us_url_root . "usersc/plugins/game_show/game_core.php";
