<?php
require '../../../../users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!pluginActive("store", true)) {
	die();
}
if (!isset($_SESSION['orderno']) || !is_numeric($_SESSION['orderno'])) {
	$string = uniqid('', true);
	$db->insert('store_orders', ['code' => $string]);
	$_SESSION['orderno'] = $db->lastId();
}
$order = $_SESSION['orderno'];

// Use HTTP Strict Transport Security to force client to use secure connections only
$use_sts = true;

// iis sets HTTPS to 'off' for non-SSL requests
if ($use_sts && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
	header('Strict-Transport-Security: max-age=31536000');
} elseif ($use_sts) {
	header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
	// we are in cleartext at the moment, prevent further execution and output
	die();
}
?>
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<?php
$itemsQ = $db->query("SELECT * FROM store_order_items WHERE orderno = ?", array($order));
$itemsC = $itemsQ->count();
$qty = 0;
$tot = 0;
if ($itemsC > 0) {
	$items = $itemsQ->results();

	foreach ($items as $i) {
		$qty = $qty + $i->qty;
		$tot = $tot + $i->price_tot;
	}
}
$pickup = $db->query("SELECT * FROM store_pickup_options WHERE disabled < 1 ORDER BY date")->results();
if (!empty($_POST)) {
	if (!empty($_POST['remove'])) {

		$orderCheckQ = $db->query("SELECT * FROM store_orders WHERE id = ?", array($order));
		$orderCheckC = $orderCheckQ->count();
		if ($orderCheckC > 0) {
			$orderCheck = $orderCheckQ->first();
			if ($orderCheck->paid == 0) { //order found and unpaid
				$row = Input::get('remove');
				$getRowQ = $db->query("SELECT * FROM store_order_items WHERE id = ?", array($row));
				$getRowC = $getRowQ->count();
				if ($getRowC > 0) {
					$getRow = $getRowQ->first();
					if ($getRow->orderno == $_SESSION['orderno']) {
						$db->query("DELETE FROM store_order_items WHERE id = ?", array($row));
						Redirect::to('store_cart.php?err=Item+deleted');
					}
				}
			}
		}
	}
}

if (!empty($_POST['fullname'])) {
	$fullname = Input::get('fullname');
	$email = Input::get('email');
	$amount = $tot * 100;
	$note = Input::get('notes');
	$fields = array(
		'fullname' => $fullname,
		'email' => Input::get('email'),
		'phone' => Input::get('phone'),
		'pickup_date' => Input::get('pickup_date'),
		'order_type' => 'in store',
		'payment_method' => Input::get('payment_method'),
		'notes' => Input::get('notes'),
		'paid' => 1,
		'amt_paid' => $amount,
		'status' => 'Order Placed',
		'taken_by' => $user->data()->fname . " " . $user->data()->lname,
	);
	$db->update('store_orders', $_SESSION['orderno'], $fields);
	logger($user->data()->id, "User", "Online Order Placed - $fullname.");
	$ord = $_SESSION['orderno'];
	$ordF = $db->query("SELECT code FROM store_orders WHERE id = ?", array($ord))->first();
	$link = $settings->order_link . '?order=' . $ord . '&code=' . $ordF->code;
	$_SESSION['orderno'] = '';
	$msg_subject = "The ";
	$params = array(
		'order' => $ord,
		'fullname' => $fullname,
		'link' => $link,
		'pickup' => Input::get('pickup_date'),
	);
	$to = rawurlencode($email);
	$body = email_body('_email_order_confirm.php', $params);
	email($to, $msg_subject, $body);
	$string = uniqid('', true);
	$db->insert('store_orders', ['code' => $string]);
	$_SESSION['orderno'] = $db->lastId();
	Redirect::to('store_order.php?err=Order+Complete!');
}
?>


<div id="page-wrapper">
	<div class="container-fluid">
		<!-- Page Heading -->
		<div class="row">
			<div class="col-sm-12 text-center">
				<h1>
					Store Checkout
				</h1>
			</div> <!-- /.col -->
			<div class="col-sm-12 text-center">
				<h3>Current Order:
					<?php
					echo money($tot) . " total and ";
					if ($qty == 1) {
						echo "1 item. ";
					} else {
						echo $qty . " items. ";
					}
					?>
				</h3>
				<h3>
					<stong><a href="store_order.php"><i class="fa fa-fw fa-heart"></i> Order More Items</a></strong>
				</h3>
			</div> <!-- /.col -->
		</div> <!-- /.row -->
		<div class="row">
			<div class="col12 col-md-8 offset-md-2">
				<?php if ($order > 0 && $itemsC > 0) { //items found
				?>
					<form class="" action="" method="post" id="payment-form"><br>
						<h5 align="center">Please confirm and enter the information</h5>
						<span class="payment-errors"></span>
						<div class="form-row">

							<label for="c">Pickup Date*</label>

							<select class="form-control" name="pickup_date" id="pickup_date" required>
								<option value="" selected="selected" disabled>--Select a Pickup Date--</option>
								<?php foreach ($pickup as $lo) {

								?>
									<option value="<?= $lo->id ?>" <?php if ($lo->noselect == '1') {
																		echo 'disabled';
																	} ?>><?php parsePickup($lo->id); ?><?php if ($lo->noselect == '1') {
																																						echo "(Not Available)";
																																					} ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="form-row">
							<label>
								<span>Customer Name*</span>
								<input class="form-control" type="text" size="50" name="fullname" value="" id="fullname" required />
							</label>
						</div>
						<div class="form-row">
							<label>
								<span>Customer Email(optional)</span>
								<input class="form-control" type="text" size="75" name="email" value="" />
							</label>
						</div>
						<div class="form-row">
							<label>
								<span>Customer Phone*</span>
								<input class="form-control" type="text" size="75" name="phone" value="" required />
							</label>
						</div>
						<div class="form-row">
							<label>
								<span>If someone else will be picking up the item, please list their name here</span>
								<input class="form-control" type="text" size="75" name="notes" value="" maxlength="50" />
							</label>
						</div>
						<div class="form-row">
							<label>
								<span>How did the customer pay?</span>
								<select class="form-control" name="payment_method" required>
									<option value="" disabled selected="selected">---Select a Payment Method---</option>
									<option value="credit card">Credit Card</option>
									<option value="credit card">Cash</option>
									<option value="credit card">Check</option>
								</select>
							</label>
						</div>
						<strong>You need to collect <font color="red"><?php echo money($tot); ?> </font>today.<strong><br>
								<button class="btn btn-success" type="submit">Submit Payment to Place your Order!</button>
					</form>
			</div>
		</div>
		<div class="row">
			<div class="col-12">
				<h2 align="center">Your Order</h2>
				<table class="table">
					<thead>
						<tr>
							<th>Item</th>
							<th>Price Each</th>
							<th>Qty</th>
							<th>Total</th>
							<th>Remove</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($items as $i) {
							if ($i->qty < 1) {
								continue;
							} ?>
							<tr>
								<td><?php echoItem($i->item); ?></td>
								<td><?php echo money($i->price_each); ?></td>
								<td><?php echo $i->qty; ?></td>
								<td><?php echo money($i->price_tot); ?></td>
								<td>
									<form class="" action="" method="post">
										<input type="hidden" name="remove" value="<?= $i->id ?>">
										<input type="submit" name="removeBtn" value="Remove" class="btn btn-danger">
									</form>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			<?php } else { ?>
				<h2 align="center">You do not have any items in your cart!</h2>
				<h3 align="center"><a href="index.php">Go order some items!</a></h3>
			<?php } ?>
			</div>
		</div>
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php //require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls 
?>


<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; // currently just the closing /body and /html 
?>