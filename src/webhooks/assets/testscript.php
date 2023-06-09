<?php
//DO NOT EDIT THIS SECTION unless you know what you're doing
if(count(get_included_files()) ==1) die();

//Note that you have access to all of the $_GET,$_POST, and $json data either as using those varaibles
//or all brought together in the $data (array) variable.


//Write your script below this line
dump("This is GET");
dump($_GET);

dump("This is POST");
dump($_POST);

dump("This is JSON");
dump($json);

$db->insert("logs",["logtype"=>"script test","lognote"=>"Did it work?"]);
dnd($data);

//If you set $return_object as an object or string then this will be returned
//instead of {msg:"success"}
