<?php
//Please don't load code on the header of every page if you don't need it on the header of every page.
require_once $abs_us_root.$us_url_root.'usersc/plugins/meekro/assets/meekrodb.2.3.class.php';
// if($settings->meekro == 0){
  $raw_sql_host = explode(";",$config['mysql']['host']);

  $config['mysql']['clean_host'] =  $raw_sql_host[0];

  $mdbfields['port'] = 3306;
  if(isset($raw_sql_host[1])){
    $mdbfields['port'] = $raw_sql_host[1];
    $mdbfields['port'] = str_replace(';', '', $mdbfields['port']);
    $mdbfields['port'] = (int)(trim(str_replace("port=","",$mdbfields['port'])));
  }
  
  $mdb = new MeekroDB($config['mysql']['clean_host'], $config['mysql']['username'], $config['mysql']['password'], $config['mysql']['db'], $mdbfields['port']);
// }

// I'm currently working with the MeekroDB Developer to fix this.  Right now, we cannot use the static method.
// MDB::$host = $config['mysql']['host'];
// MDB::$user = $config['mysql']['username'];
// MDB::$password = $config['mysql']['password'];
// MDB::$dbName = $config['mysql']['db'];
// MDB::query("SELECT * FROM users");

if($settings->meekro == 1){
  // MDB::$host = $config['mysql']['host'];
  // MDB::$user = $config['mysql']['username'];
  // MDB::$password = $config['mysql']['password'];
  // MDB::$dbName = $config['mysql']['db'];
}
?>
