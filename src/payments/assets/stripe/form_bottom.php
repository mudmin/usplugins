<?php if(count(get_included_files()) ==1) die();
if(haltPayment('stripe')){die("This form of payment is disabled");}
//This is the javascript below the form and the closing form tag
$keys = $db->query("SELECT * FROM `keys`")->first();
?>

</form>


<script>
    // PART 1 - Client Side
    // Create the card token using Stripe.js
    // set Stripe publishable key: remember to change this to your live secret key in production
    // See your keys here https://dashboard.stripe.com/account/apikeys
		<?php //if($settings->stripe_test_mode == 1){ ?>
			  	//Stripe.setPublishableKey('');
		<?php //}else{ ?>
			     Stripe.setPublishableKey("<?=$keys->stripe_lp?>");
	  <?php //} ?>
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

      // alert("Form will submit!\n\nToken ID = " + response.id);
      // uncomment below to actually submit
      paymentForm.submit();
    }
    };
  </script>
