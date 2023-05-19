<?php
require '../../../../users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!pluginActive("store", true)) {
	die();
}
$abanQ = $db->query("SELECT * FROM store_orders WHERE paid < 1");
$abanC = $abanQ->count();
if ($abanC > 0) {
	$aban = $abanQ->results();
}
if (!empty($_POST)) {

	if (!empty($_POST['viewOrder'])) {
		$order = Input::get('order');
		$_SESSION['orderno'] = $order;
		$link = str_replace('view_order.php', 'cart.php', $settings->order_link);
		Redirect::to($link);
	}

	if (!empty($_POST['deleteOrder'])) {
		$order = Input::get('orderno');

		//double check to make sure order is not paid;
		$paid = $db->query("SELECT * FROM store_orders WHERE id = ?", array($order))->first();
		if ($paid->paid == 1) {
			Redirect::to('abandoned.php?err=Sorry+order+has+been+paid+and+cannot+be+deleted');
		}

		$itemsQ = $db->query("SELECT id FROM store_order_items WHERE orderno = ?", array($orderno));
		$itemsC = $itemsQ->count();
		if ($itemsC > 0) {
			$items = $itemsQ->results();
			foreach ($items as $i) {
				$db->query("DELETE FROM store_order_items WHERE id = ?", array($i->id));
			}
		}
		$db->query("DELETE FROM store_orders WHERE id = ?", array($order));
		Redirect::to('abandoned.php?err=Order+deleted');
	}
}
?>



<div id="page-wrapper">
	<div class="container-fluid">
		<!-- Page Heading -->
		<div class="row">
			<div class="col-sm-12">
				<h1 class="page-header">
					Incomplete Orders
				</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<?php if ($abanC > 0) { ?>
					<table class="table">
						<thead>
							<tr>
								<th>Order Number</th>
								<th>Name</th>
								<th>Contact
								<th>Last Update</th>
								<th>Order</th>
								<th>Delete Order</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($aban as $a) {
							?>
								<tr>
									<td>
										<form class="" action="" method="post">
											<input type="hidden" name="order" value="<?= $a->id ?>">
											<input type="submit" name="viewOrder" value="View Order #<?= $a->id ?>" class="btn btn-primary">
										</form>
									</td>
									<td><?= $a->fullname ?></td>
									<td><?= $a->phone ?> <?= $a->email ?></td>
									<td><?= $a->last_update ?></td>
									<td>
										<?php
										$items = $db->query("SELECT * FROM store_order_items WHERE orderno = ?", array($a->id))->results();
										$total = 0;
										foreach ($items as $i) {
											if ($i->qty == 0) {
												continue;
											}
											echoItem($i->item);
											echo " x <font color='red'>" . $i->qty . "</font> = ";
											echo money($i->price_tot);
											echo "<br>";
											$total = $total + $i->price_tot;
										}
										echo "<strong><font color='blue'>";
										echo money($total);
										echo "</font></strong>";
										?>
									</td>
									<td>
										<form class="" action="" method="post">
											<input type="hidden" name="orderno" value="<?= $a->id ?>">
											<input type="submit" name="deleteOrder" value="Delete Order" class="btn btn-danger">
										</form>

									</td>
								</tr>
							<?php	} ?>
						</tbody>
					</table>
				<?php } //End abandoned check
				else {
					echo "<h2 align='center'>You do not have any abandoned orders. Yay!</h2>";
				} ?>
			</div>

		</div>
	</div>
</div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php //require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls 
?>

<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; // currently just the closing /body and /html 
?>