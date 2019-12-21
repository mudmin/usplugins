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
?>
<?php
require '../../../../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
//require_once $abs_us_root.$us_url_root.'users/includes/navigation.php';
if (!securePage($_SERVER['PHP_SELF'])){die();} $db=DB::getInstance(); if(!pluginActive("store")){die();}
$checkPayment = $db->query("SELECT * FROM us_plugins WHERE plugin = ? AND status = ?",['payments','active'])->count();
if($checkPayment < 1){die("Payment plugin required");}
if(!isset($_SESSION['orderno']) || !is_numeric($_SESSION['orderno'])){
	$string = uniqid(15);
	$db->insert('store_orders',['code'=>$string]);
	$_SESSION['orderno'] = $db->lastId();
}
$order = $_SESSION['orderno'];
$paySet = false;
if(!empty($_POST['paymentMethod'])){
	$sel = Input::get('paymentMethod');
	$check = $db->query("SELECT * FROM store_payment_options WHERE opt = ? AND disabled = 0",[$sel])->count();
	if($check > 0){
		$paySet = $sel;
	}
}

if($paySet == false){
$payOptsQ = $db->query("SELECT * FROM store_payment_options ORDER BY def DESC");
$payOptsC = $payOptsQ->count();
if($payOptsC < 1){die("No payment options are available");}
if($payOptsC == 1){
	$payOpts = $payOptsQ->first();
	$paySet = $payOpts->opt;
}else{
	$payOpts = $payOptsQ->results();
}
}

$itemsQ = $db->query("SELECT * FROM store_order_items WHERE orderno = ?",array($order));
$itemsC = $itemsQ->count();
$qty = 0;
$tot = 0;
if($itemsC > 0){
	$items = $itemsQ->results();

	foreach($items as $i){
		$qty = $qty + $i->qty;
		$tot = $tot + $i->price_tot;
	}
}

	if(!empty($_POST['remove'])){
		$orderCheckQ = $db->query("SELECT * FROM store_orders WHERE id = ?",array($order));
		$orderCheckC = $orderCheckQ->count();
		if($orderCheckC > 0){
			$orderCheck = $orderCheckQ->first();
			if($orderCheck->paid == 0){ //order found and unpaid
				$row = Input::get('remove');
				$getRowQ = $db->query("SELECT * FROM store_order_items WHERE id = ?",array($row));
				$getRowC = $getRowQ->count();
				if($getRowC > 0){
					$getRow = $getRowQ->first();
					if($getRow->orderno == $_SESSION['orderno']){
						$db->query("DELETE FROM store_order_items WHERE id = ?",array($row));
						Redirect::to('cart.php?err=Item+deleted');
					}
				}
			}
		}
	}

$formInfo = [
	'method'		=>$paySet,
	'action'		=>'', 												//can be blank
	'total'			=>$tot,
	'email'			=>Input::get('email'),   //not required, but ideal
	'reason'		=>$settings->site_name,						//not required, but ideal
	'notes' 		=>'these are notes!',					//Stored in plg_payments table
	'processed' =>false, 											//do not change this
	'success'		=>false, 											//do not change this
	'id'				=>null,												//do not change this
	'msg'				=>null,												//do not change this
	'callback'  =>null,												//for future use, a php file for the payment processor to callback
	'redir'			=>null,												//for future use, where to go after success
	'submit'    =>""
	//optional submit button only works for the displayPayment function, not payment1, payment2 etc
];

$check = checkInventory($order);
if($check['success'] == true && $paySet){
$formInfo = payment1($formInfo);
if($formInfo['processed'] == true && $formInfo['success'] == true){
processInventory($order);
$fullname = Input::get('fullname');
	$fields = array(
		'fullname'				 => $fullname,
		'phone'						 => Input::get('phone'),
		'amt_paid'         => $tot,
		'paid'         		 => '1',
		'email'            => $email,
		'status'           => "Order Placed",
		'order_type'			 => 'online',
		'payment_method'	 => 'credit card - '.$formInfo['method'],
		'charge_id'        => $chargeID,
	);
	$db->update('store_orders',$_SESSION['orderno'],$fields);
	logger(1,"User","Online Order Placed - $fullname.");
	$email = Input::get('email');
	$amount = $tot * 100;
	$token = $_POST['stripeToken'];
	$metadata = array(
			"cardholder_name"=>$fullname,
			"email"=>$email
		);

	$ord = $_SESSION['orderno'];
	$ordF = $db->query("SELECT code FROM store_orders WHERE id = ?",array($ord))->first();
	$link = $settings->order_link.'?order='.$ord.'&code='.$ordF->code;
	$_SESSION['orderno']='';
	// $msg_subject = "The ";
	// $params = array(
	// 	'order' => $ord,
	// 	'fullname' => Input::get('fullname'),
	// 	'link' => $link,
	// 	);
	// $to = rawurlencode($email);
	// $body = email_body('_email_order_confirm.php',$params);
	// email($to,$msg_subject,$body);
	Redirect::to($link."&err=Your+order+was+successful");
}elseif($formInfo['processed'] == true && $formInfo['success'] == false){
	if($formInfo['msg'] != ''){
		Redirect::to('cart.php?err='.$formInfo['msg']);
	}else{
		Redirect::to('cart.php?err=Payment+Failed!');
	}
}
}else{//end inventory check passed
echo "<font color='red'>";
foreach($check['fails'] as $c){
	echo "<h3 align='center'>$c</h3>";
}
echo "</font>";
}
?>


<div id="page-wrapper">
	<div class="container-fluid">
		<!-- Page Heading -->
<?php if($order > 0 && $itemsC > 0){ //items found

	?>
		<div class="row">
			<div class="col-12">
				<h1 class="text-center">Checkout</h1>
			</div>
		</div>
			<div class="col-12 text-center">
				<h3>Current Order:
				<?php
				echo money($tot) ." total and ";
				if($qty == 1){
					echo "1 item. ";
				}else{
					echo $qty . " items. ";
				}
				?>
			</h3>
				<h4><a href="store.php"><i class="fa fa-fw fa-heart"></i> Order More Items</a></h4>
				<br>
			</div> <!-- /.col -->
		</div> <!-- /.row -->
		<div class="row">
		<div class="col-12 col-md-6">
			<h2 align="center">Your Order</h2>
					<table class="table">
						<thead>
							<tr>
								<th>Item</th><th>Price Each</th><th>Qty</th><th>Total</th><th>Remove</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($items as $i){
								if($i->qty < 1){continue;}?>
							<tr>
								<td><?php echoItem($i->item);?></td>
								<td><?php echo money($i->price_each);?></td>
								<td><?php echo $i->qty;?></td>
								<td><?php echo money($i->price_tot);?></td>
								<td>
									<form class="" action="" method="post">
										<input type="hidden" name="remove" value="<?=$i->id?>">
										<input type="submit" name="removeBtn" value="Remove" class="btn btn-danger">
									</form>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
			</div>

			<div class="col-12 col-md-6">

<?php
if($paySet){
	$formInfo = payment2($formInfo);
?>	<input type="hidden" name="paymentMethod" value="<?=$paySet?>">
    <div class="form-row" id="paymentEmail">
      <label>
        <span>Customer Email*</span>
        <input class="form-control" id="emailElement" type="text" size="75" name="email" value="" required  />
      </label>
    </div>
		<div class="form-row" id="paymentPhone">
			<label>
				<span>Customer Phone*</span>
				<input class="form-control" id="phoneElement" type="text" size="75" name="phone" value="" required />
			</label>
		</div>
		<button class='btn btn-primary payment-form' type='submit'>Submit Payment</button><br>
		<strong>Your card will be charged <font color="red"><?php echo money($tot);?> </font>today.<strong><br>
			<?=$settings->checkout_msg?><br>
		</form>
	<?php }else{//if $paySet ?>
		<form class="" action="" method="post">
			<label for="">Please select a payment method</label>
			<div class="input-group">
				<select class="form-control" name="paymentMethod" required>
					<option value="" disabled selected="selected">--Choose--</option>
					<?php foreach($payOpts as $p){?>
						<option value="<?=$p->opt?>"><?=$p->common?></option>
					<?php } ?>
					<div class="input-group-append">
						<input type="submit" name="selectPayment" value="Go" class="btn btn-primary">
					</div>
				</select>
			</div>
		</form>
	<?php }
	?>
	</div>
</div>
<?php }else{ ?>
	<h2 align="center">You do not have any items in your cart!</h2>
	<h3 align="center"><a href="store.php">Go order some items!</a></h3>
<?php } ?>
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php //require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
<?php
if($paySet){$formInfo = payment3($formInfo);}
?>
