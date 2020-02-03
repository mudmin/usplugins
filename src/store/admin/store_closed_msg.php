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
if(!empty($_POST)){
	if(!empty($_POST['submitMsg'])){
	$msg = Input::get('msg');
	$db->update('settings',1,['closed_msg'=>$msg]);
	Redirect::to('store_closed_msg.php?err=Closed+message+has+been+updated');
}

if(!empty($_POST['submitClose'])){
$datetime = Input::get('datetime');
$db->update('settings',1,['auto_close'=>$datetime]);
Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=store&err=Auto Closing has been scheduled');
}


}
?>



<div id="page-wrapper">
	<div class="container-fluid">
		<!-- Page Heading -->
		<div class="row">
			<div class="col-12">
				<h1 class="page-header text-center">
					Store Closing Options
				</h1>
			</div>
		</div>
			<div class="row">
				<div class="col-12 col-md-8 offset-md-2">
					<h2>Store Closed Message</h2>
				<p align="center">
				<form class="" action="" method="post">
					<textarea name="msg" rows="8" cols="80"><?=$settings->closed_msg;?></textarea><br>
					<input type="submit" name="submitMsg" value="Update Message" class="btn btn-primary">
				</form>
				</p>
			</div> <!-- /.col -->
		</div>
		<div class="row">
			<div class="col-12 col-md-8 offset-md-2">
				<h2>Close Store Automatically</h2>
			<p>
				The website's time is <strong><?php $date = date("Y-m-d H:i:s"); echo $date;?> </strong>(military time).
			<form class="" action="" method="post">
				<label for="">Choose a new Automatic Store Closing Time</label><br>
				<input type="text" name="datetime" value="<?=$settings->auto_close?>" id="datetimepicker" width="50"><br><br>
				<input type="submit" name="submitClose" value="Setup Auto Store Closing" class="btn btn-primary">
			</form>
			</p>
			</div>
			</div>

						</div>
		</div>
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php //require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>
<script>
  $(function () {
		$('#datetimepicker').datetimepicker({
  dateFormat: "yy-mm-dd",
	stepMinute: 15,
	showSecond: 0,

});
 });
</script>

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
