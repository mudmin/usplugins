<?php
/*
UserSpice 5
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
require_once '../../../../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])){die();}
if(!pluginActive("stripe",true)){die("Plugin not active");}
// Product Details
// Minimum amount is $0.50 US
$productName = "Donation";
$productID = "$5 Donation";
$productPrice = 0;
$currency = $settings->stripe_currency;
$stripeAmount = round($productPrice*100, 2);
$button_text = "Donate Now";


// Stripe API configuration
define('STRIPE_API_KEY', $settings->stripe_private);
define('STRIPE_PUBLISHABLE_KEY', $settings->stripe_public);
define('STRIPE_SUCCESS_URL', $settings->stripe_url."/usersc/plugins/stripe/files/success.php");
define('STRIPE_CANCEL_URL', $settings->stripe_url."/usersc/plugins/stripe/files/cancel.php");

$checkout_session_id = $user->data()->id;

require_once $abs_us_root.$us_url_root.'usersc/plugins/stripe/assets/v3/stripe-php/init.php';
\Stripe\Stripe::setApiKey($settings->stripe_private);


$currentPage = currentPage();

if(!empty($_POST['checkout'])){
    // Create new Checkout Session for the order
    try {
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'product_data' => [
                        'name' => $productName,
                        'metadata' => [
                            'pro_id' => $productID
                        ]
                    ],
                    'unit_amount' => $stripeAmount,
                    'currency' => $currency,
                ],
                'quantity' => 1,
                'description' => $productName,
            ]],
            'mode' => 'payment',
            'success_url' => $settings->stripe_url."?err=Payment+was+successful",
            'cancel_url' => $settings->stripe_url."?err=Payment+was+cancelled",
        ]);
    }catch(Exception $e) {
        $api_error = $e->getMessage();
    }

    if(empty($api_error) && $session){
        $response = array(
            'status' => 1,
            'message' => 'Checkout Session created successfully!',
            'sessionId' => $session['id']
        );
    }else{
        $response = array(
            'status' => 0,
            'error' => array(
                'message' => 'Checkout Session creation failed! '.$api_error
            )
        );
    }
    $checkout_session_id=$session->id;
}



?>
<script src="https://js.stripe.com/v3"></script>

				<div class="row">
					<div class="col-sm-12" align = "center"><br>
            <!-- Display errors returned by checkout session -->
            <div id="paymentResponse"></div>
            Make a Donation
            <input type="number" name="amount" value="10.00" id="amount">

            <!-- Buy button -->
            <div id="buynow">
                <button class="stripe-button" id="payButton"><?=$button_text?></button>
            </div>

					</div>
				</div>
			</div>
		</div>
<script src="https://js.stripe.com/v3/"></script>

<script>
var buyBtn = document.getElementById('payButton');
var responseContainer = document.getElementById('paymentResponse');

// Create a Checkout Session with the selected product
var createCheckoutSession = function (stripe) {
    return fetch("../assets/stripe_charge_donation.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            checkoutSession: 1,
            amount:$("#amount").val(),
        }),
    }).then(function (result) {
        return result.json();
    });
};

// Handle any errors returned from Checkout
var handleResult = function (result) {
    if (result.error) {
        responseContainer.innerHTML = '<p>'+result.error.message+'</p>';
    }
    buyBtn.disabled = false;
    buyBtn.textContent = '<?=$button_text?>';
};

// Specify Stripe publishable key to initialize Stripe.js
var stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');

buyBtn.addEventListener("click", function (evt) {
    buyBtn.disabled = true;
    buyBtn.textContent = 'Please wait...';

    createCheckoutSession().then(function (data) {
        if(data.sessionId){
            stripe.redirectToCheckout({
                sessionId: data.sessionId,
            }).then(handleResult);
        }else{
            handleResult(data);
        }
    });
});
</script>
<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
