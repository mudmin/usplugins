<?php if(count(get_included_files()) ==1) die();
if(haltPayment('check')){die("This form of payment is disabled");}
global $user;
if(isset($user) && $user->isLoggedIn()){
  $fn = $user->data()->fname." ".$user->data()->lname;
}else{
  $fn = "";
}
//This is the required part of the form. You may add additional form fields as necessary
if(!isset($formInfo)){
  die("The formInfo variable is required.  Please see documentation for an explaination.");
}
?>
<form class="" action="<?=$formInfo['action'];?>" method="post" id="payment-form">
<input type="hidden" name="processPayment" value="1">
<br>
<span class="payment-errors"></span>
<?php include 'custom_instructions.php';?>
<div class="form-row">
<label>
  <span>Full Name*</span>
  <input class="form-control" type="text" size="50" name="fullname" value="<?=$fn?>" id="fullName" required />
</label>
</div>
<?php
