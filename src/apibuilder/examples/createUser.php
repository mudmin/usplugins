<!-- <?php
// //DO NOT LEAVE THIS API commented in.  It is just for demo purposes. The only point of this API is to test wheher you can create a new user
//
// //This is the only line you need to authenticate the API call and do the basic data sanitization
// require_once "../assets/auth_and_sanitize.php";
// // dump($data);
// $settings = $db->query("SELECT * FROM settings")->first();
// $failed = 0;
// $return = [];
//
// if(strlen($data['username']) < $settings->min_un || strlen($data['username']) > $settings->max_un){
//   $failed = 1;
//   $return['msg'] = "invalid username";
// }
//
// if(strlen($data['password']) < $settings->min_pw || strlen($data['password']) > $settings->max_pw){
//   $failed = 1;
//   $return['msg'] = "invalid password";
// }
//
// if($data['fname'] == '' || $data['lname'] == ''){
//   $failed = 1;
//   $return['msg'] = "invalid first/last name";
// }
//
// if($data['email'] == ''){ //replace this with a real email check
//   $failed = 1;
//   $return['msg'] = "invalid email";
// }
//
// $check = $db->query("SELECT id FROM users WHERE username = ? OR email = ?",[$data['username'],$data['email']])->count();
// if($check > 0){
//   $failed = 1;
//   $return['msg'] = "Username/Email already in use";
// }
//
// if($failed == 0){
// //add user to the database
// $user = new User();
// $join_date = date("Y-m-d H:i:s");
//
//       $theNewId = $user->create(array(
//                 'username' => $data['username'],
//                 'fname' => ucfirst($data['fname']),
//                 'lname' => ucfirst($data['lname']),
//                 'email' => $data['email'],
//                 'password' => password_hash($data['password'], PASSWORD_BCRYPT, array('cost' => 12)),
//                 'permissions' => 1,
//                 'account_owner' => 1,
//                 'join_date' => $join_date,
//                 'email_verified' => 1,
//                 'active' => 1,
//                 'vericode' => $vericode = randomstring(15),
//                 'vericode_expiry' => date("Y-m-d H:i:s"),
//                 'oauth_tos_accepted' => true
//         ));
//
// include($abs_us_root.$us_url_root.'usersc/scripts/during_user_creation.php');
// logger($theNewId,"User","Registration completed via API.");
// $return['success'] = true;
// $return['msg'] = "New user created";
// $return['id'] = $theNewId;
// }//end failed = 0;
// else{
//   $return['success'] = false;
//   $return['id'] = null;
// }
//
// //in our example, we will just return authentication data
// if($auth['success'] == true){
//   echo json_encode($return);
// }
//
// //
// // //An example call would be
// // //
// // {
// //   "key":"O7KWV-VXKF4-NU5D5-2C2D9-DFEF8",
// //   "username":"Danny",
// //   "fname":"Dan",
// //   "lname":"Hoover",
// //   "email":"Bob@aol.com",
// //   "password":"password"
// // }
// // // to
// // // http://localhost/us5/usersc/plugins/apibuilder/examples/createUser.php
// // // or
// // // https://example.com/usersc/plugins/apibuilder/examples/createUser.php
// // // A sample respose would be
// // // {"success":true,"msg":"New user created","id":"6"} -->
