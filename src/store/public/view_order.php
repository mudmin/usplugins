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
$ord = Input::get('order');
$code = Input::get('code');

if(hasPerm([2],$user->data()->id)){

	$orderQ = $db->query("SELECT * FROM store_orders WHERE id = ?",array($ord));
	$orderC = $orderQ->count();
	if($orderC < 1){
		Redirect::to('store.php?err=Invalid+order+or+code');
	}else{
		$order = $orderQ->first();
		$items = $db->query("SELECT * FROM store_order_items WHERE orderno = ?",array($ord))->results();
	}
	if(!empty($_POST)){
		if(!empty($_POST['pickup'])){
			$db->update('store_orders',$order->id,['status'=>'Picked Up']);
			Redirect::to('search_orders.php');
		}
		if(!empty($_POST['placed'])){
			$db->update('store_orders',$order->id,['status'=>'Order Placed']);
			Redirect::to('search_orders.php');
		}
	}
}else{
$orderQ = $db->query("SELECT * FROM store_orders WHERE id = ? AND code = ?",array($ord,$code));
$orderC = $orderQ->count();
if($orderC < 1){
	Redirect::to('store.php?err=Invalid+order+or+code');
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
			<div class="col-12 col-md-10 offset-md-1">

		<h2 align="center">Your Order (#<?=$ord?>)</h2>

		<h4 align="center">You can return to this page at any time by visiting</h4>
		<h4 align="center">
			<a href="<?=$settings->order_link?>?order=<?=$ord?>&code=<?=$code?>"><?=$settings->order_link?>?order=<?=$ord?>&code=<?=$code?></a> </h4>
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

			<h4>Total: <font color="blue"><?php echo $order->amt_paid;?></font></h4>
			<h4>Status: <font color="blue"><?php echo $order->status;?></font></h4>
			<h4>Customer: <font color="blue"><?php echo $order->fullname;?></font></h4>
			<h4>Email: <font color="blue"><?php echo $order->email;?></font></h4>
			<?php
			if(isset($user) && $user->isLoggedIn() && hasPerm([2],$user->data()->id)){ ?>
			Only Admins and Managers Can See This:<br>
			<h4>Contact: <?=$order->phone;?></h4>
			<h4>Notes: </font></h4><?php echo $order->notes;?>
			<?php }
			if($settings->email_msg != ""){ ?>
			<h3 align="center">Important Message</h3>
			<strong><?=$settings->email_msg?></strong>
			<?php } ?>
			<form><p align="center"><input type="button" value="Print This Page For Your Records" onClick="window.print()"></p></form>
		</div>
		</div>
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php //require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>


<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
