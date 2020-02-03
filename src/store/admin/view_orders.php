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
$ordersQ = $db->query("SELECT * FROM store_orders WHERE amt_paid > 0 AND archived < 1");
$ordersC = $ordersQ->count();
if($ordersC > 0){
	$orders = $ordersQ->results();
}
$total = 0;
?>


<div id="page-wrapper">
	<div class="container-fluid">
		<!-- Page Heading -->
		<div class="row">
			<div class="col-md-3 d-none d-lg-block"></div>
			<div class="col-md-12">
<?php if($ordersC > 0){?>
		<h2 align="center">Your Orders ($<span id="totHere"></span>)</h2>
				<table class="table">
					<thead>
						<tr>
							<th>Order Link</th><th>Status</th><th>Type</th><th>Amount Paid</th><th>Name</th><th>Contact</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($orders as $order){ ?>
							<tr>
								<td>
									<a href="<?=$settings->order_link?>?order=<?=$order->id?>&code=<?=$order->code?>">Order <?=$order->id?></a>
								</td>
								<td><?php
								if($order->status == 'Shipped'){
									echo "<font color='red'><strong>Shipped</font></strong>";
								}else{
								echo $order->status;
							}?></td>
								<td><?php
								if($order->order_type == "online"){
									echo $order->order_type;
								}else{
									echo $order->order_type ."-".$order->taken_by;
								}
								?></td>

								<td>
									<?php echo money($order->amt_paid);
								$total = $total + $order->amt_paid;
								?>
								</td>
								<td><?php echo $order->fullname;?></td>
								<td><?=$order->phone;?> <?=$order->email?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="total" value="<?=$total?>">
			
<?php }else{?>
<h2 align="center">You do not have any unarchived orders.</h2>
<?php }
$total = number_format($total,2,'.',',');
?>

<input type="hidden" name="total" value="<?=$total?>">
		</div>
		</div>
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php //require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>
<script>
$(document).ready(function() {
var total = $('input[name=total]').val();
$("#totHere").html(total);

} );
</script>

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
