<?php
//
// //This is the only line you need to authenticate the API call and do the basic data sanitization
// require_once "../assets/auth_and_sanitize.php";
//
// //All your db work goes here
// // Let's show success or fail on this one
// $result = [];
// if(is_numeric($data['user']) && $data['user'] > 0 && $data['note'] != ''){
//
// //Let's check if the user exists.  We normally wouldn't want to return this, but we will do it for testing`
//   $check = $db->query("SELECT id FROM users WHERE id = ?",[$data['user']])->count();
//   if($check < 1){
//     $result['success'] = false;
//     $result['reason'] = "User not found";
//   }else{ //user found
//   //do a mix of getting data yourself (ip/log reason) and receiving the api data (user/note).
//   // Note that the data was already sanitized on line 10
//   logger($data['user'],"API Test",$data['note']);
//   $result['success'] = true;
//   $result['reason'] = "It worked!";
//   //in our example, we will just return our query
// }
//
// }else{ //the data check on line 19 failed
//   $result['success'] = false;
//   $result['reason'] = "Bad data";
// }
//   echo json_encode($result);
//
//
// //An example call would be
// // {"key":"SOQ6T-SI9GT-K15D4-6DD77-B7A69","user":1,"note":"Hello World"}
// // to
// // http://localhost/us5/usersc/plugins/apibuilder/examples/insert_log.php
// // or
// // https://example.com/usersc/plugins/apibuilder/examples/insert_log.php
// // A sample respose would be
// //{"success":true,"reason":"It worked!"}
