<?php
$overrideCheck = true;
$processed = false;
$email = Input::get('email');

//check if email is in the system AND verified
$check = $db->query("SELECT id FROM users WHERE email = ? AND email_verified = 1",[$email])->count();
if($check > 0){
  //original logic
  $fuser = new User($email);
  $validate = new Validate();
  $validation = $validate->check($_POST,array(
  'email' => array(
    'display' => lang("GEN_EMAIL"),
    'valid_email' => true,
    'required' => true,
  ),
  ));
  $processed = true;
}

if(!$processed){
  //let's find if the user is in the system but NOT verified
  $check = $db->query("SELECT id FROM users WHERE email = ?",[$email])->count();
  if($check > 0){
    //original logic
    $fuser = new User($email);
    $validate = new Validate();
    $validation = $validate->check($_POST,array(
    'email' => array(
      'display' => lang("GEN_EMAIL"),
      'valid_email' => true,
      'required' => true,
    ),
    ));
    $processed = true;
    //set check back to zero so we know the user isn't validated
    $check = 0;
  }
}

if(!$processed){
  //let's find if the hash of the user is in the db
  $hash = hashEmailAddress($email);
  $check = $db->query("SELECT id FROM users WHERE email = ?",[$hash])->count();
  if($check > 0){
    //original logic
    $fuser = new User($hash);
    $validate = new Validate();
    $validation = $validate->check($_POST,array(
    'email' => array(
      'display' => lang("GEN_EMAIL"),
      'required' => true,
    ),
    ));
    $processed = true;
    //set check back to zero so we know the user isn't validated
    $check = 0;
  }
}else{
  $validate = new Validate();
}
