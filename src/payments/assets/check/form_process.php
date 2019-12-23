<?php if(count(get_included_files()) ==1) die();
if(haltPayment('check')){die("This form of payment is disabled");}
//This is the payment form processor. It will be loaded above the form
?>
<strong><font color="red">
  <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<?php
$keys = $db->query("SELECT * FROM `keys`")->first();

if(!empty($_POST['processPayment'])){
  $formInfo['processed'] = false;
  ?>

  <?php

  $amount = $formInfo['total'] * 100;
  if(!isset($user) || !$user->isLoggedIn()){
    $userid = 0;
  }else{
    $userid = $user->data()->id;
  }

  $fields = array(
    'amt_paid'         => $amount,
    'user'             => $userid,
    'dt'               => date("Y-m-d H:i:s"),
    'charge_id'        => "",
    'method'           => "check",
    'notes'            => $formInfo['notes'],
  );
  $db->insert('plg_payments',$fields);
  $formInfo['processed'] = true;
  $formInfo['success'] = true;
  $formInfo['id'] = $db->lastId();
  logger($user->data()->id,"Payments Plugin","CHECK Online Order Placed");
}
?>
</font></strong>
