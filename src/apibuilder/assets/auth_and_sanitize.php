<?php
if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
require_once "../../../../users/init.php";
require_once $abs_us_root.$us_url_root."usersc/plugins/apibuilder/assets/apitools.php";
$db = DB::getInstance();
$json = file_get_contents('php://input');
$data = json_decode($json, "true");
foreach($data as $k=>$v){
  $data[$k] = Input::sanitize($v);
}
$auth = apibuilderAuth($data['key']);
if(!$auth){die();}
