<?php
//include 'users/init.php';
require_once 'db.class.php';
// if($settings->meekro == 0){
// $mdb = new MeekroDB($config['mysql']['host'], $config['mysql']['username'], $config['mysql']['password'], $config['mysql']['db']);
// }
MDB::$host = 'localhost';
MDB::$user = 'user';
MDB::$password = 'user';
MDB::$dbName = '4400';
MDB::query("SELECT * FROM users");
