<?php
require_once "../../../../users/init.php";
if(!hasPerm(2)){
    die("no permission");
}
$resp = [
    "success" => false,
    "msg" => "invalid column",
    "data" => []
];

$valid_cols = ["column_description", "table_description"];
$col = Input::get("col");
if(!in_array($col, $valid_cols)){
    die(json_encode($resp));
}

$val = Input::get("val");
$id = Input::get("id");

if($col == "column_description"){
    $db->update("plg_db_explainer_columns", $id, [$col => $val]);
}

if($col == "table_description"){
    $db->update("plg_db_explainer_tables", $id, [$col => $val]);
}


$resp["success"] = true;
$resp["msg"] = "success";
die(json_encode($resp));
