<?php
require '../../../../users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!pluginActive("store", true)) {
	die();
}
$ord = Input::get('order');
$code = Input::get('code');
$pickup = $db->query("SELECT * FROM store_pickup_options WHERE disabled < 1 ORDER BY date")->results();

if(hasPerm([2],$user->data()->id)){

	$orderQ = $db->query("SELECT * FROM store_orders WHERE id = ?",array($ord));
	$orderC = $orderQ->count();
	if($orderC < 1){
		Redirect::to('index.php?err=Invalid+order+or+code');
	}else{
		$order = $orderQ->first();
		$items = $db->query("SELECT * FROM store_order_items WHERE orderno = ?",array($ord))->results();
	}
	if(!empty($_POST)){
		$fields = array(
			'fullname' => Input::get('fullname'),
			'notes' => Input::get('notes'),
			'phone' => Input::get('phone'),
			'email' => Input::get('email'),
			'pickup_date' => Input::get('pickup_date'),
		);
		$db->update('store_orders',$ord,$fields);
		Redirect::to('view_order.php?order='.$ord.'&err=Order Updated');
	}
}else{
$orderQ = $db->query("SELECT * FROM store_orders WHERE id = ? AND code = ?",array($ord,$code));
$orderC = $orderQ->count();
if($orderC < 1){
	Redirect::to('index.php?err=Invalid+order+or+code');
}else{
	$order = $orderQ->first();
	$items = $db->query("SELECT * FROM store_order_items WHERE orderno = ?",array($ord))->results();
}
}
?>


<div id="page-wrapper">
	<div class="container-fluid">
		<!-- Page Heading -->
		<div class="row">
			<div class="col-md-3 d-none d-lg-block"></div>
			<div class="col-lg-6 col-md-12">
<font color = "red">
		<h2 align="center">EDITING ORDER (#<?=$ord?>)</h2>
				<table class="table">
					<thead>
						<tr>
							<th>Item</th><th>Price Each</th><th>Qty</th><th>Total</th>
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
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<form class="" action="" method="post">
				</font>
				<font color="blue">
			<h4>Pickup:
				<select class="form-control" name="pickup_date" id="pickup_date" required>
					<option value="<?=$order->pickup_date?>" selected="selected"><?php parsePickup($order->pickup_date);?></option>
					<?php foreach($pickup as $lo){

						 ?>
				<option value="<?=$lo->id?>"><?php parsePickup($lo->id);?></option>
					<?php } ?></select>
			<h4>Total: <font color="blue"><?php echo money($order->amt_paid/100);?></font></h4>
			<h4>Status: <font color="blue"><?php echo $order->status;?></font></h4>
			<h4>Customer: <input type="text" name="fullname" value="<?=$order->fullname;?>"></h4>
			<h4>Notes:</h4>
			<h4>
				<textarea name="notes" rows="8" cols="80"><?=$order->notes;?></textarea>
			</h4>
			<h4>Phone:
				<input type="text" name="phone" value="<?=$order->phone;?>">
			</h4>
			<h4>Phone:
				<input type="text" name="email" value="<?=$order->email;?>">
			</h4>
			<input type="submit" name="Submit" value="Update Order!" class="btn btn-danger">
		</form>
		</div>
		</div>
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php //require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>


<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
