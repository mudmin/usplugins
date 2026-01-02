<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$return_url = Input::get('return_url');
$auth_code = Input::get('auth_code');
$request_wp_access = Input::get('request_wp_access');
?>
<input type="hidden" name="request_wp_access" value="<?=$request_wp_access?>">
<input type="hidden" name="return_url" value="<?=$return_url;?>">
<input type="hidden" name="auth_code" value="<?=$auth_code;?>">
