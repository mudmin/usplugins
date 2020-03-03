<?php
global $db,$settings;
$community_functions = json_decode($settings->fun);
if($community_functions != ''){
  foreach($community_functions as $cf){
    if(file_exists($abs_us_root.$us_url_root."usersc/plugins/community_functions/assets/".$cf.".php")){
      include $abs_us_root.$us_url_root."usersc/plugins/community_functions/assets/".$cf.".php";
    }
  }
}
