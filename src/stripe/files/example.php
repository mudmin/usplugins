<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


//typical userspice includes
require_once '../../../../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
//This block of code will allow only https connections
include "../plugin_info.php";
if(!pluginActive("stripe",true)){die("Plugin not active");}
$use_sts = true;

// iis sets HTTPS to 'off' for non-SSL requests
if ($use_sts && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
  header('Strict-Transport-Security: max-age=31536000');
} elseif ($use_sts) {
  header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 301);
  // we are in cleartext at the moment, prevent further execution and output
  die("Your connection is not secure.");
}

//end stripe-specific security statements

if (!securePage($_SERVER['PHP_SELF'])){die();}

?>

<!-- The generic stripe javascript hosted on stripe.com and specific jquery -->
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>

<?php
//The PHP class for stripe.com
require_once $abs_us_root.$us_url_root.'usersc/plugins/stripe/assets/stripe-php/init.php';
?>

<div id="page-wrapper">
  <div class="container-fluid">
    <?php
    if ($_POST) {
      $csrf = $_POST['csrf'];

      if(!Token::check($csrf)){
        include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
      }
      $fname = Input::get('fname');
      $lname = Input::get('lname');
      $fullname = $fname." ".$lname;
      $email = Input::get('email');
      $rawAmount = Input::get('amount');
      $amount = $rawAmount * 100; //note that stripe expects the payment amount to be in pennies so we're converting it
      $note = Input::get('note');

      \Stripe\Stripe::setApiKey($settings->stripe_private);

      // Get the credit card details submitted by the form

      $token = $_POST['stripeToken'];
      // Add email address to metadata to make it searchable in the dashboard

      $metadata = array(
        "cardholder_name"=>$fullname,
        "email"=>$email,
        "by"=>$user->data()->id,
        "note"=>$note,
      );


      // Add email address to description for risk scoring
      $description = $settings->site_name;


      // Create the charge on Stripe's servers - this will charge the user's card
      try {
        $charge = \Stripe\Charge::create(array(
          "amount" => $amount, // amount in cents
          "currency" => "usd",
          "source" => $token,
          "description" => $description,
          "metadata" => $metadata,
        ));
        $chargeID = $charge['id']; //from the stripe API

        $fields = array(
          'user'             => $user->data()->id,
          'amount'           => $rawAmount,
          'email'            => $email,
          'notes'            => $note,
          'fname'            => $fname,
          'lname'            => $lname,
          'charge_id'        => $chargeID,
          'card_type'        => Input::get('type'),
        );
        $db->insert('stripe_transactions',$fields);
        logger($user->data()->id,"User","Credit Card - $fullname.");
        bold("Card processed successfully");
      } catch(\Stripe\Error\Card $e) {
        // Since it's a decline, \Stripe\Error\Card will be caught
        $body = $e->getJsonBody();
        $err  = $body['error'];
        print('Status is:' . $e->getHttpStatus() . "\n");
        print('Type is:' . $err['type'] . "\n");
        print('Code is:' . $err['code'] . "\n");
        // param is '' in this case
        print('Param is:' . $err['param'] . "\n");
        print('Message is:' . $err['message'] . "\n");
      } catch (\Stripe\Error\RateLimit $e) {
        // Too many requests made to the API too quickly
      } catch (\Stripe\Error\InvalidRequest $e) {
        // Invalid parameters were supplied to Stripe's API
      } catch (\Stripe\Error\Authentication $e) {
        // Authentication with Stripe's API failed
        // (maybe you changed API keys recently)
      } catch (\Stripe\Error\ApiConnection $e) {
        // Network communication with Stripe failed
      } catch (\Stripe\Error\Base $e) {
        // Display a very generic error to the user, and maybe send
        // yourself an email
      } catch (Exception $e) {
        // Something else happened, completely unrelated to Stripe
      }
    }
    $csrf = Token::generate();

    ?>
    <div class="row">
      <div class="col-12 col-sm-4 offset-sm-4">
        <h3>This is an EXAMPLE Form</h3>
        <strong>It's fine to use it for your project, but it's primarily designed to show you how to use Stripe and to make sure your payments are posting.</strong>
        <form action="" method="POST" id="payment-form">
          <input type="hidden" name="csrf" value="<?=$csrf?>" />
          <span class="payment-errors">
          <div class="form-group">
            <label>Amount to charge</label>
                <input class="form-control" type = 'number' min="0.01" step="0.01" size="10" name="amount" value="" />
          </div>
          <div class="form-group">
            <label>Card Number</label>
              <input class="form-control" type="text" size="20" data-stripe="number" value="" id="account" />

          <label>Card Type</label>
            <select class="form-control" name="type" id="type">
              <option value="">(Select card type)</option>
              <option value="amex">American Express</option>
              <option value="visa">Visa</option>
              <option value="mastercard">MasterCard</option>
              <option value="discover">Discover</option>
            </select>
            </div>
            <div class="form-group">
              <label>
                Expiration Month(MM)
                <input class="form-control"type="text" size="2" data-stripe="exp-month" id="expMonth" value="" />
              </label>
               /
              <label>
                Expiration Year(YY)
                <input class="form-control" type="text" size="2" data-stripe="exp-year" value="" id="expYear" />
              </label>
            </div>
            <div class="input-group">
              <label for="">Customer First Name</label>
              <input type="hidden" class="form-countrol" name="" value="">

              <label for="">Customer Last name Name</label>
              <input type="hidden" class="form-countrol" name="" value="">
            </div>
            <div class="input-group">
                <input class="form-control" type="text" size="50" name="firstname" value="" id="firstName"/>
                <input class="form-control" type="text" size="50" name="lastname" data-stripe="name" value="" id="lastName"/>
            </div>

            <div class="form-group">
              <label><font color="red">CVC</font></label>
                <input class="form-control w-25" type="text" size="4" data-stripe="cvc" value="" />
            </div>

            <div class="form-group">
              <label>Customer Email</label>
                <input type="text" class="form-control" name="email" value="" />
            </div>
            <div class="form-group">
              <label>Notes</label>

                <input type="text" class="form-control" name="notes" value="" />

            </div>

            <button type="submit">Submit Payment</button>
          </form>
        <!-- Content Ends Here -->
      </div> <!-- /.col -->
    </div> <!-- /.row -->
  </div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>
<script>
// PART 1 - Client Side
// Create the card token using Stripe.js
// set Stripe publishable key: remember to change this to your live secret key in production
// See your keys here https://dashboard.stripe.com/account/apikeys
Stripe.setPublishableKey("<?=$settings->stripe_public?>");
// grab payment form
var paymentForm = document.getElementById("payment-form");
// listen for submit
paymentForm.addEventListener("submit", processForm, false);
/* Methods */
// process form on submit
function processForm(evt) {
  // prevent form submission
  evt.preventDefault();
  // create stripe token
  Stripe.card.createToken(paymentForm, stripeResponseHandler);
};
// handle response back from Stripe
function stripeResponseHandler(status, response) {
  // if an error
  if (response.error) {
    // respond in some way
    alert("Error: " + response.error.message);
  }
  // if everything is alright
  else {
    // creates a token input element and add that to the payment form
    var token = document.createElement("input");
    token.name = "stripeToken";
    token.value = response.id; // token value from Stripe.card.createToken
    token.type = "hidden"
    paymentForm.appendChild(token);
    // resubmit form
    //alert("Form will submit!\n\nToken ID = " + response.id);
    // uncomment below to actually submit
    paymentForm.submit();
  }
};
</script>


<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
