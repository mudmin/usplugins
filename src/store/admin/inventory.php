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
$inv = $db->query("SELECT * FROM store_inventory ORDER BY category, item")->results();
?>
<?php if (!securePage($_SERVER['PHP_SELF'])){die();}  $db=DB::getInstance(); if(!pluginActive("store")){die();}?>

<div id="page-wrapper">
	<div class="container-fluid">
		<!-- Page Heading -->
		<div class="row">
			<div class="col-sm-12">
				<h1 class="page-header">
					Your Inventory (<a href="manage_inventory.php">Add</a>)
				</h1>
				<table class="table" id="inventory">
					<thead>
						<th>Category</th>
						<th>Item</th>
						<th>Price</th>
						<th>QOH</th>
						<th>Digital</th>
						<th>Edit</th>
					</thead>
					<tbody>
						<?php foreach($inv as $i){ ?>
							<tr>
								<td><?php echoCat($i->category);?></td>
								<td><?=$i->item?>
									<?php if($i->disabled == 1){echo " (Sold Out)";} ?>
								</td>
								<td><?php echo money($i->price);?></td>
								<td><?php echo $i->qoh;?></td>
								<td><?php echo bin($i->digital);?></td>
																<td>
									<a href="manage_inventory.php?edit=<?=$i->id?>">
										Edit Item / Add Photos
									</a>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div> <!-- /.col -->
		</div> <!-- /.row -->
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php //require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<script>
$(document).ready(function() {
		// $('#inventory').DataTable({"pageLength": 25,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]], "aaSorting": []});
} );
</script>
<script src="users/js/pagination/jquery.dataTables.js" type="text/javascript"></script>
<script src="users/js/pagination/dataTables.js" type="text/javascript"></script>

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
