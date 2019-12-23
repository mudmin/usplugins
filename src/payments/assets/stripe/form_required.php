<?php if(count(get_included_files()) ==1) die();
if(haltPayment('stripe')){die("This form of payment is disabled");}
//This is the required part of the form. You may add additional form fields as necessary
if(!isset($formInfo)){
  die("The formInfo variable is required.  Please see documentation for an explaination.");
}
?>
<form class="" action="<?=$formInfo['action'];?>" method="post" id="payment-form">
  <input type="hidden" name="processPayment" value="1">
<br>
<span class="payment-errors"></span>
<div class="form-row">
<label>
  <span>Cardholder Name*</span>
  <input class="form-control" type="text" size="50" name="fullname" value="" id="fullName" required />
</label>
</div>

<div class="form-row">
  <label>
    <span>Credit Card Number*</span>
    <input class="form-control" type="text" size="20" data-stripe="number" value="" id="account" />
  </label>
</div>
  <div class="form-row">
  <label>
    <span>Expiration Month(MM)*</span>
    <input class="form-control"type="text" size="2" data-stripe="exp-month" id="expMonth" value=""  required />
  </label>
  <span> / </span>
  <label>
    <span>Expiration Year(YY)*</span>
  <input class="form-control" type="text" size="2" data-stripe="exp-year" value="" id="expYear" required />
  </label>
</div>

<div class="form-row">
  <label>
    <span>CVC (3 or 4 Digit Code)*</span>
    <input class="form-control" type="text" size="4" data-stripe="cvc" value="" required />
  </label>
</div>
