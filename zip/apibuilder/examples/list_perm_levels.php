<?php
//
// //This is the only line you need to authenticate the API call and do the basic data sanitization
// require_once "../assets/auth_and_sanitize.php";
//
// //All your db work goes here
// $perms = $db->query("SELECT * FROM permissions")->results();
//
//
//
// //in our example, we will just return our query
// echo json_encode($perms);
//
// //An example call would be
// // {"key":"SOQ6T-SI9GT-K15D4-6DD77-B7A69"}
// // to
// // http://localhost/us5/usersc/plugins/apibuilder/examples/list_perm_levels.php
// // or
// // https://example.com/usersc/plugins/apibuilder/examples/list_perm_levels.php
// // A sample respose would be
// //[{"id":"1","name":"User"},{"id":"2","name":"Administrator"}]
