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
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
include "plugin_info.php";
pluginActive($plugin_name);
if (!securePage($_SERVER['PHP_SELF'])){die();}
$users = $db->query("SELECT * FROM users")->results();
$date = date("Y-m-d");
?>

<div id="page-wrapper">
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>User</th><th>Email</th><th>Plan</th><th>Expires</th><th>Change</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($users as $u){?>
							<tr>
								<td><?=echouser($u->id)?></td>
								<td><?=$u->email?></td>
								<td><?=echoPlanName($u->plg_mem_level);?></td>
								<td><?php if($u->plg_mem_exp < $date){ ?>
									<font color="red">
									<?php }
									echo $u->plg_mem_exp;
									 ?>
									<font color="black">
									</td>
								<td>
									<form class="" action="manage_members_plan.php" method="get">
										<input type="hidden" name="userid" value="<?=$u->id?>">
										<input type="submit" name="method" value="Change">
									</form>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>


<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
