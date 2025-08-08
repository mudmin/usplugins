<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted

if(file_exists($abs_us_root.$us_url_root.'usersc/plugins/passwordless/assets/custom.php')){
  require_once $abs_us_root.$us_url_root.'usersc/plugins/passwordless/assets/custom.php';
}else{
  require_once $abs_us_root.$us_url_root.'usersc/plugins/passwordless/assets/passwordless.php';
}

?>
