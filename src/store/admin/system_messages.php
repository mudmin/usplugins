<?php
require '../../../../users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!pluginActive("store", true)) {
	die();
}
if (!empty($_POST)) {
	if (!empty($_POST['email_msg'])) {
		$msg = Input::get('email_msg');
		$db->update('settings', 1, ['email_msg' => $msg]);
		Redirect::to('system_messages.php?err=Email+message+has+been+updated');
	}

	if (!empty($_POST['checkout_msg'])) {
		$msg = Input::get('checkout_msg');
		$db->update('settings', 1, ['checkout_msg' => $msg]);
		Redirect::to('system_messages.php?err=Checkout+message+has+been+updated');
	}

	if (!empty($_POST['header_msg'])) {
		$msg = Input::get('header_msg');
		$db->update('settings', 1, ['header_msg' => $msg]);
		Redirect::to('system_messages.php?err=Header+message+has+been+updated');
	}
}
?>



<div id="page-wrapper">
	<div class="container-fluid">
		<!-- Page Heading -->
		<div class="row">
			<div class="col-sm-12">
				<h1 class="page-header">
					System Messages
				</h1>

			</div>
		</div>
		<div class="row">
			<div class="col-12">
				<br>
				<h2>Checkout Message</h2>
				<p>
					This message is displayed on the checkout screen.
				<form class="" action="" method="post">
					<textarea name="checkout_msg" rows="8" cols="80"><?= $settings->checkout_msg; ?></textarea><br>
					<input type="submit" name="updatecheckoutMsg" value="Update Message" class="btn btn-primary">
				</form>
				</p>
			</div> <!-- /.col -->

			<div class="col-12">
				<br>
				<h2>Email Message</h2>
				<p>
					This message is emailed to the person who places the order along with other information about their order.It will
					also show up at the bottom of their order confirmation.
				<form class="" action="" method="post">
					<textarea name="email_msg" rows="8" cols="80"><?= $settings->email_msg; ?></textarea><br>
					<input type="submit" name="updateEmailMsg" value="Update Message" class="btn btn-primary">
				</form>
				</p>
			</div> <!-- /.col -->

			<div class="col-12">
				<br>
				<h2>Header Message</h2>
				<p>
					This message is at the top of the order pages. Usually a reminder that you want the user to see while choosing items! Could also be a message about when ordering will close.
				<form class="" action="" method="post">
					<textarea name="header_msg" rows="8" cols="80"><?= $settings->header_msg; ?></textarea><br>
					<input type="submit" name="updateHeaderMsg" value="Update Message" class="btn btn-primary">
				</form>
				</p>
			</div> <!-- /.col -->

		</div>
	</div>
</div>
</div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php //require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls 
?>
<script>
	$(function() {
		$('#datetimepicker').datetimepicker({
			dateFormat: "yy-mm-dd",
			stepMinute: 15,
			showSecond: 0,

		});
	});
</script>

<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; // currently just the closing /body and /html 
?>