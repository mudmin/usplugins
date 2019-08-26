<?php
// //DO NOT LEAVE THIS API commented in.  It is just for demo purposes. The only point of this API is to test wheher you can authenticate

//This is the only line you need to authenticate the API call and do the basic data sanitization
require_once "../assets/auth_and_sanitize.php";

//in our example, we will just return authentication data
echo json_encode($auth);
//
// //An example call would be
// // {"key":"SOQ6T-SI9GT-K15D4-6DD77-B7A69"}
// // to
// // http://localhost/us5/usersc/plugins/apibuilder/examples/auth_only.php
// // or
// // https://example.com/usersc/plugins/apibuilder/examples/auth_only.php
// // A sample respose would be
// // {"success":true,"valtype":"userwithip","user_id":"1","descrip":"admin"}
