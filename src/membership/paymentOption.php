<?php
/*
UserSpice 5
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
require_once '../../../users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
include "plugin_info.php";
pluginActive($plugin_name);
if (!hasPerm([2], $user->data()->id) || !(in_array($user->data()->id, $master_account))) {
	die("He' dead, Jim");
}
$edit = Input::get('edit');
$planQ = $db->query("SELECT * FROM plg_mem_cost WHERE id = ?", [$edit]);
$planC = $planQ->count();
if ($planC < 1) {
	Redirect::to($us_url_root . "users/admin.php?view=plugins_config&plugin=membership&err=Not+found");
}
$plan = $planQ->first();

if (!empty($_POST['days'])) {
	$fields = array(
		'cost' => Input::get('cost'),
		'days' => Input::get('days'),
		'descrip' => Input::get('descrip'),
		'disabled' => Input::get('disabled'),
	);
	$db->update('plg_mem_cost', $edit, $fields);
	Redirect::to($us_url_root . "users/admin.php?view=plugins_config&plugin=membership&err=Pricing+option+updated");
}


?>

<div id="page-wrapper">
	<div class="container">
		<div class="row mt-4">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header">
						<h3>Manage Payment Option</h3>
					</div>

					<div class="card-body">
						<h6 class="fw-bold">
							Please note. Do not delete pricing options because it could break things. Use the disable feature. New prices will be used when people renew.
						</h6>
						<form class="" action="" method="post">
							<div class="form-group">
								<label for="days"># of Days</label>
								<input class="form-control" type="number" name="days" value="<?= $plan->days ?>" min="1" step="1" placeholder="30" required>
							</div>

							<div class="form-group">
								<label for="cost">Cost - No Symbols</label>
								<input class="form-control" type="number" name="cost" value="<?= $plan->cost ?>" min=".00" step=".01" placeholder="30.00" required>
							</div>

							<div class="form-group">
								<label for="descrip">Description - We will automatically add the number of days to this description</label>
								<input class="form-control" type="text" name="descrip" value="<?= $plan->descrip ?>" placeholder="1 month" required>
							</div>

							<div class="form-group">
								<label for="disabled">Disable Option?</label>
								<select class="form-control" name="disabled" required>
									<option value="" disabled selected>--Choose--</option>
									<option value="0">Enabled</option>
									<option value="1">Disabled</option>

								</select>
							</div>

							<div class="form-group mt-2">
								<input type="submit" name="plugin_cost" value="Update" class="btn btn-primary">
						</form>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>
</div>

<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>