<?php
//
// //This is the only line you need to authenticate the API call and do the basic data sanitization
// require_once "../assets/auth_and_sanitize.php";
//
// //All your db work goes here
// if($data['request']== 'perms'){
// $info = $db->query("SELECT * FROM permissions")->results();
// }
//
// if($data['request']== 'pages'){
// $info = $db->query("SELECT id,page FROM pages LIMIT 4")->results();
// }
//
// //in our example, we will just return our query
// echo json_encode($info);
//
// //An example call would be
// // {"key":"SOQ6T-SI9GT-K15D4-6DD77-B7A69","request":"pages"}
// // to
// // http://localhost/us5/usersc/plugins/apibuilder/examples/multiple_calls.php
// // or
// // https://example.com/usersc/plugins/apibuilder/examples/multiple_calls.php
// // A sample respose would be
// //[{"id":"1","page":"index.php"},{"id":"2","page":"z_us_root.php"},{"id":"3","page":"users\/account.php"},{"id":"4","page":"users\/admin.php"}]
