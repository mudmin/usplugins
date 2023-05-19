<?php
require '../../../../users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!pluginActive("store", true)) {
	die();
}
$inv = $db->query("SELECT * FROM store_inventory ORDER BY category, item")->results();
$new = Input::get('new');
if (!isset($_SESSION['orderno']) || !is_numeric($_SESSION['orderno']) || $new == 1) {
	$string = uniqid('', true);
	$db->insert('store_orders', ['code' => $string]);
	$_SESSION['orderno'] = $db->lastId();
}
$orderQ = $db->query("SELECT * FROM store_orders WHERE id = ?", array($_SESSION['orderno']));
$orderC = $orderQ->count();
if ($orderC < 1) {
	$string = uniqid('', true);
	$db->insert('store_orders', ['code' => $string]);
	$_SESSION['orderno'] = $db->lastId();
	Redirect::to('store_order.php');
} else {
	$order = $orderQ->first();
	$items = $db->query("SELECT * FROM store_order_items WHERE orderno = ?", array($_SESSION['orderno']))->results();
	$qty = 0;
	$tot = 0;
	foreach ($items as $i) {
		$qty = $qty + $i->qty;
		$tot = $tot + $i->price_tot;
	}
}

$cats = $db->query("SELECT * FROM store_categories WHERE disabled = 0")->results();
if (!empty($_POST)) {
	if (!empty($_POST['cancelOrder'])) {
		if ($order->paid > 0) {
			$string = uniqid('', true);
			$db->insert('store_orders', ['code' => $string]);
			$_SESSION['orderno'] = $db->lastId();
			Redirect::to("store_order.php?err=New+Order");
		} else {

			$items = $db->query("SELECT id FROM store_order_items WHERE orderno = ?", array($_SESSION['orderno']))->results();
			foreach ($items as $i) {
				$db->query("DELETE FROM store_order_items WHERE id = ?", array($i->id));
			}
			$db->query("DELETE FROM store_orders WHERE id = ?", array($_SESSION['orderno']));
		}
		Redirect::to('store_order.php');
	}
	if (!empty($_POST['qty'])) {
		$q = $_POST['qty'];

		//loop through existing items in order and update quantities as necessary
		$existingQ = $db->query("SELECT * FROM store_order_items WHERE orderno = ?", array($_SESSION['orderno']));
		$existingC = $existingQ->count();

		if ($existingC > 0) { //items found
			$existing = $existingQ->results();
			foreach ($existing as $e) { //loop through items in order
				foreach ($q as $k => $v) {
					if ($e->item == $k) { //posted item found in order
						$tot = $e->price_each * $v;
						$fields = array(
							'qty' => $v,
							'price_tot' => $tot,
						);
						$db->update('store_order_items', $e->id, $fields);
						unset($q[$k]); //remove from array
					}
				}
			}
		} //end of processing existing items

		foreach ($q as $k => $v) { //process what's left
			if ($v < 1) {
				continue;
			} //ignore 0s
			$price = itemPrice($k);
			if ($price == "x") {
				continue;
			}
			$tot = $price * $v;
			$fields = array(
				'price_each' => $price,
				'orderno' => $_SESSION['orderno'],
				'item' => $k,
				'qty' => $v,
				'price_tot' => $tot,
			);
			$db->insert('store_order_items', $fields);
		}
		Redirect::to('store_order.php?Cart+Updated!');
	}
}
?>
<div id="page-wrapper">
	<div class="container-fluid">
		<!-- Page Heading -->
		<div class="row">
			<div class="col-12">
				<h1>
					Place a store order
				</h1>
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
			</div>
		</div>
		<div class="row">


			<div class="col-12 col-sm-6">
				<form class="" action="" method="post">
					<input type="submit" name="cancelOrder" value="Cancel Order and Start Over" class="btn btn-danger">
				</form>
			</div>
			<div class="col-12 col-sm-6 text-right">
				<button type="button" onclick="window.location.href = 'store_cart.php';" name="button" class="btn btn-primary"><i class="fa fa-fw fa-shopping-cart"></i> View Cart & Checkout</button>
			</div>
		</div>
		<!-- <h4><a href="store_cart.php"><i class="fa fa-fw fa-shopping-cart"></i> View Cart & Checkout</a></h4> -->

	</div> <!-- /.col -->
</div> <!-- /.row -->
<div class="row">
	<div class="col-12 col-md-10 offset-md-1">
		<form class="" action="" method="post">
			<table class="table table-hover">
				<thead>
					<th>Item</th>
					<th>Type</th>
					<th>Price</th>
					<th>Quantity</th>
					<th>Update Cart</th>
				</thead>
				<tbody>

					<?php foreach ($inv as $i) {
						$oiq = 0;
						$orderItemQ = $db->query("SELECT qty FROM store_order_items WHERE orderno = ? AND item = ?", array($_SESSION['orderno'], $i->id));
						$orderItemC = $orderItemQ->count();
						if ($orderItemC > 0) {
							$orderItem = $orderItemQ->first();
							$oiq = $orderItem->qty;
						}
					?>
						<tr>
							<td><?= $i->item ?></td>
							<td><?php echoCat($i->category); ?></td>
							<td><?php echo money($i->price); ?></td>
							<td>
								<div id="<? $i->id ?>">
									<button type="button" id="sub" class="sub">-</button>
									<input type="number" class="qty" name="qty[<?= $i->id ?>]" id="1" value="<?= $oiq ?>" min="0" max="15" />
									<button type="button" id="add" class="add">+</button>
								</div>
							</td>
							<td><input type="submit" class="submit btn btn-primary" name="add" value="Update Cart"></td>
						</tr>
					<?php } ?>
		</form>
		</tbody>
		</table>
	</div>
</div>
</div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php //require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls 
?>

<script>
	$(document).ready(function() {
		// $('#inventory').DataTable({"pageLength": 50,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]], "aaSorting": []});

		$(document).on('touchend click', '.add', function(e) {
			e.stopImmediatePropagation();
			e.preventDefault();
			var handled = false;
			if (e.type == 'touchend' && handled == false) {
				if ($(this).prev().val() < 16) {
					$(this).prev().val(+$(this).prev().val() + 1);
				}
				return false;
			} else if (e.type == 'click' && handled == false) {
				if ($(this).prev().val() < 16) {
					$(this).prev().val(+$(this).prev().val() + 1);
				}
				return false;
			}
		});


		$(document).on('touchend click', '.sub', function(e) {
			e.stopImmediatePropagation();
			e.preventDefault();
			var handled = false;
			if (e.type == 'touchend' && handled == false) {
				if ($(this).next().val() > 0) {
					if ($(this).next().val() > 0) $(this).next().val(+$(this).next().val() - 1);
				}
				return false;
			} else if (e.type == 'click' && handled == false) {
				if ($(this).next().val() > 0) {
					if ($(this).next().val() > 0) $(this).next().val(+$(this).next().val() - 1);
				}
				return false;
			}
		});

	});
</script>
<script src="users/js/pagination/jquery.dataTables.js" type="text/javascript"></script>
<script src="users/js/pagination/dataTables.js" type="text/javascript"></script>

<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; // currently just the closing /body and /html 
?>