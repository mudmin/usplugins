<?php if(count(get_included_files()) ==1) die();
if(haltPayment('stripe')){die("This form of payment is disabled");}
//This is the payment form processor. It will be loaded above the form

?>
<strong><font color="red">
  <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<?php
$keys = $db->query("SELECT * FROM `keys`")->first();

if(!empty($_POST['processPayment'])){
  logger($user->data()->id,"Payments Plugin","Attempting Stripe Transaction");
  $formInfo['processed'] = false;
  require_once $abs_us_root.$us_url_root.'usersc/plugins/payments/assets/stripe/stripe-php/init.php';
    // Use HTTP Strict Transport Security to force client to use secure connections only

  $use_sts = true;

  // iis sets HTTPS to 'off' for non-SSL requests
  if ($use_sts && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
      header('Strict-Transport-Security: max-age=31536000');
  } elseif ($use_sts) {
      header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 301);
      // we are in cleartext at the moment, prevent further execution and output
      die();
  }
  ?>

  <?php
  $fullname = Input::get('fullname');
  $email = $formInfo['email'];
  $amount = $formInfo['total'] * 100;
  if(!isset($user) || !$user->isLoggedIn()){
    $userid = 0;
  }else{
    $userid = $user->data()->id;
  }

// if($settings->stripe_test_mode == 1){
//   \Stripe\Stripe::setApiKey("sk_test_YOUR_KEY_HERE");
// }else{
   \Stripe\Stripe::setApiKey($keys->stripe_ls);
// }

  // Get the credit card details submitted by the form
  $token = $_POST['stripeToken'];
  // Add email address to metadata to make it searchable in the dashboard
  $metadata = array(
      "cardholder_name"=>$fullname,
      "email"=>$email
    );

  // Add email address to description for risk scoring
  $description = $formInfo['reason'];
  // Create the charge on Stripe's servers - this will charge the user's card
    logger($user->data()->id,"Payments Plugin","About to connect to Stripe");
  try {
    logger($user->data()->id,"Payments Plugin","Stripe try statement");
    $charge = \Stripe\Charge::create(array(
      "amount" => $amount, // amount in cents
      "currency" => $keys->currency,
      "source" => $token,
      "description" => $description,
      "metadata" => $metadata,
      ));
    $chargeID = $charge['id'];

  $fields = array(
    'amt_paid'         => $amount,
    'user'             => $userid,
    'dt'               => date("Y-m-d H:i:s"),
    'charge_id'        => $chargeID,
    'method'           => "stripe",
    'notes'            => $formInfo['notes'],

  );
  $db->insert('plg_payments',$fields);

  $formInfo['processed'] = true;
  $formInfo['success'] = true;
  $formInfo['id'] = $db->lastId();
  logger($user->data()->id,"Payments Plugin","Stripe Online Order Placed");

  } catch(\Stripe\Error\Card $e) {
    // Since it's a decline, \Stripe\Error\Card will be caught
    $body = $e->getJsonBody();
    $err  = $body['error'];
    $msg = $err['message'];
    logger($user->data()->id,'Payments Plugin',"Stripe Says $msg");
    // print('Status is:' . $e->getHttpStatus() . "\n");
    // print('Type is:' . $err['type'] . "\n");
    // print('Code is:' . $err['code'] . "\n");
    // param is '' in this case
    // print('Param is:' . $err['param'] . "\n");
    // print('*****' . $err['message'] . "\n");

  } catch (\Stripe\Error\RateLimit $e) {
    // Too many requests made to the API too quickly
    $body = $e->getJsonBody();
    $err  = $body['error'];
    $msg = $err['message'];
    logger($user->data()->id,'Payments Plugin',"Stripe Says $msg");

    $formInfo['processed'] = true;
    $formInfo['success'] = false;
    $formInfo['msg'] = $msg;
  } catch (\Stripe\Error\InvalidRequest $e) {
    // Invalid parameters were supplied to Stripe's API
    $body = $e->getJsonBody();
    $err  = $body['error'];
    $msg = $err['message'];
    logger($user->data()->id,'Payments Plugin',"Stripe Says $msg");

    $formInfo['processed'] = true;
    $formInfo['success'] = false;
    $formInfo['msg'] = $msg;
  } catch (\Stripe\Error\Authentication $e) {
    $body = $e->getJsonBody();
    $err  = $body['error'];
    $msg = $err['message'];
    logger($user->data()->id,'Payments Plugin',"Stripe Says $msg");

    $formInfo['processed'] = true;
    $formInfo['success'] = false;
    $formInfo['msg'] = $msg;
    // Authentication with Stripe's API failed
    // (maybe you changed API keys recently)
  } catch (\Stripe\Error\ApiConnection $e) {
    $body = $e->getJsonBody();
    $err  = $body['error'];
    $msg = $err['message'];
    logger($user->data()->id,'Payments Plugin',"Stripe Says $msg");
;
    $formInfo['processed'] = true;
    $formInfo['success'] = false;
    $formInfo['msg'] = $msg;
    // Network communication with Stripe failed
  } catch (\Stripe\Error\Base $e) {
    $body = $e->getJsonBody();
    $err  = $body['error'];
    $msg = $err['message'];
    logger($user->data()->id,'Payments Plugin',"Stripe Says $msg");

    $formInfo['processed'] = true;
    $formInfo['success'] = false;
    $formInfo['msg'] = $msg;
    // Display a very generic error to the user, and maybe send
    // yourself an email
  } catch (Exception $e) {
    $body = $e->getJsonBody();
    $err  = $body['error'];
    $msg = $err['message'];
    logger($user->data()->id,'Payments Plugin',"Stripe Says $msg");

    $formInfo['processed'] = true;
    $formInfo['success'] = false;
    $formInfo['msg'] = $msg;
    // Something else happened, completely unrelated to Stripe
  }


}
?>
</font></strong>
