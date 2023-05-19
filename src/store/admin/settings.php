<?php
require '../../../../users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!pluginActive("store", true)) {
	die();
}

if (!in_array($user->data()->id, $master_account)) {
	die("Master accounts only");
}
if (!empty($_POST['saveSettings'])) {
	$db->update('settings', 1, ['order_link' => Input::get('order_link'), 'ignore_inventory' => Input::get('ignore_inventory')]);
	Redirect::to('settings.php?err=Saved');
}
?>
<div id="page-wrapper">
	<div class="container-fluid">
		<!-- Page Heading -->
		<div class="row">
			<div class="col-md-3 d-none d-lg-block"></div>
			<div class="col-md-12">
				<h2 align="center">Store Settings</h2>
				<form class="" action="" method="post">
					<div class="form-group">
						<label for="">Link to view_order.php (IMPORTANT!) Used for emails, etc</label>
						<input type="text" name="order_link" value="<?= $settings->order_link ?>" class="form-control" placeholder="https://yourdomain.com/usersc/plugins/store/public/view_order.php">
					</div>
					<div class="form-group">
						<label for="">Ignore Inventory both digital and digital items</label>
						<select class="form-control" name="ignore_inventory">
							<option value="0" <?php if ($settings->ignore_inventory == 0) {
													echo "selected='selected'";
												} ?>>No - Manage Inventory</option>
							<option value="1" <?php if ($settings->ignore_inventory == 1) {
													echo "selected='selected'";
												} ?>>Yes - Ignore Inventory</option>
						</select>
					</div>
					<div class="form-group">
						<input type="submit" name="saveSettings" value="Save Settings" class="btn btn-primary">
					</div>
				</form>

			</div>
		</div>
		<div class="row">
			<div class="col-6">
				<h3>Add a Payment Option</h3>
				If you add a payment option to the Payments plugin, it will automatically show up here.
				<?php $dirs = glob($abs_us_root . $us_url_root . 'usersc/plugins/payments/assets/*', GLOB_ONLYDIR);
				foreach ($dirs as $k => $v) {
					$dirs[$k] = str_replace($abs_us_root . $us_url_root . 'usersc/plugins/payments/assets/', '', $v);
					$q = $db->query("SELECT * FROM store_payment_options WHERE opt = ?", [$dirs[$k]]);
					$c = $q->count();
					if ($c > 0) {
						unset($dirs[$k]);
					}
				}

				if (!empty($_POST['delThis'])) {
					$db->query("DELETE FROM store_payment_options WHERE id = ?", [Input::get('delMe')]);
					Redirect::to('settings.php?err=Option+deleted');
				}

				if (!empty($_POST['addOpt'])) {
					$def = Input::get('def');
					if ($def == 1) {
						$old = $db->query("SELECT * FROM store_payment_options WHERE def = 1")->results();
						foreach ($old as $o) {
							$db->update('store_payment_options', $o->id, ['def' => 0]);
						}
					}
					$fields = array(
						'opt' => Input::get('opt'),
						'def' => $def,
						'common' => Input::get('common'),
					);
					$db->insert('store_payment_options', $fields);

					Redirect::to('settings.php?err=Option+added');
				}
				?>
				<form class="" action="" method="post">
					<div class="form-group">
						<label for="">Choose an Option</label>
						<select class="form-control" name="opt" required>
							<option value="" disabled selected="selected">--Choose--</option>
							<?php foreach ($dirs as $o) { ?>
								<option value="<?= $o ?>"><?= ucfirst($o); ?></option>
							<?php } ?>
						</select>

					</div>
					<div class="form-group">
						<label for="">Common Name</label>
						<input type="text" name="common" value="" class="form-control">
					</div>

					<div class="form-group">
						<label for="">Default Payment Option?</label>
						<select class="form-control" name="def" required>
							<option value="" disabled selected="selected">--Choose--</option>
							<option value="0">No</option>
							<option value="1">Yes</option>
						</select>
					</div>
					<input type="submit" name="addOpt" value="Add Option" class="btn btn-primary">
				</form>
			</div>
			<div class="col-6">
				<h3>Configured Payment Options</h3>
				<?php $opts = $db->query("SELECT * FROM store_payment_options ORDER BY def DESC")->results(); ?>
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Option</th>
							<th>Public Name for Option</th>
							<th>Default?</th>
							<th>Delete?</th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($opts as $o) { ?>
							<tr>
								<td><?= $o->opt ?></td>
								<td><?= $o->common ?></td>
								<td><?= bin($o->def); ?></td>
								<td>
									<form class="" action="" method="post">
										<input type="hidden" name="delMe" value="<?= $o->id ?>">
										<input type="submit" name="delThis" value="Delete" class="btn btn-danger">
									</form>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				* If you have more than one payment option available, the user will be given a choice. If you have Stripe, you may want to use a more
				generic name like "Credit Card" to make things clearer to your end user.
			</div>
		</div>
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->

<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; // currently just the closing /body and /html 
?>