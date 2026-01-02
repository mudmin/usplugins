<?php
require_once '../../../../users/init.php';
$db = DB::getInstance();
$settings = $db->query("SELECT * FROM settings")->first();
if(!pluginActive("stripe",true)){die("Plugin not active");}

$productName = "Donation";
$productID = "Donation";
$productPrice = 0;
$currency = $settings->stripe_currency;
$stripeAmount = round($productPrice*100, 2);

// Include Stripe PHP library
require_once $abs_us_root.$us_url_root.'usersc/plugins/stripe/assets/v3/stripe-php/init.php';
// Stripe API configuration
define('STRIPE_API_KEY', $settings->stripe_private);
define('STRIPE_PUBLISHABLE_KEY', $settings->stripe_public);
define('STRIPE_SUCCESS_URL', $settings->stripe_url."/usersc/plugins/stripe/files/success.php");
define('STRIPE_CANCEL_URL', $settings->stripe_url."/usersc/plugins/stripe/files/cancel.php");
// Set API key
\Stripe\Stripe::setApiKey(STRIPE_API_KEY);

$response = array(
    'status' => 0,
    'error' => array(
        'message' => 'Invalid Request!'
    )
);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = file_get_contents('php://input');
    $request = json_decode($input);
}

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode($response);
    exit;
}

$product_price = (float)Input::sanitize($request->amount);

if(is_numeric($product_price) && $product_price > .50){

$stripeAmount = round($product_price*100, 2);

if(!empty($request->checkoutSession)){
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
            'success_url' => STRIPE_SUCCESS_URL.'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => STRIPE_CANCEL_URL,
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
        // Log the actual error server-side
        error_log("Stripe Checkout Session Error: " . $api_error);
        $response = array(
            'status' => 0,
            'error' => array(
                'message' => 'Checkout Session creation failed. Please try again.'
            )
        );
    }
}

}else{
  $response = array(
      'status' => 0,
      'error' => array(
          'message' => 'Amount must be more than .50!'
      )
  );
}

// Return response
echo json_encode($response);
