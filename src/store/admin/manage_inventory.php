<?php
require '../../../../users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!pluginActive("store", true)) {
	die();
}
?>
<script src="<?= $us_url_root ?>usersc/plugins/store/assets/dropzone.js"></script>
<link href="<?= $us_url_root ?>usersc/plugins/store/assets/dropzone.css" type="text/css" rel="stylesheet" />
<?php
$cats = fetchStoreCats();

$edit = Input::get("edit");
$last = Input::get("last");
if (!empty($_FILES)) {
	$date = date('Y-m-d');
	$prid = $edit;
	$ds          = '/';  //1

	$targetPath = $abs_us_root . $us_url_root . 'usersc/plugins/store/img/';   //2

	$name = $_FILES["file"]["name"];
	$ext = end((explode(".", $name)));
	$uniq_name = "item-" . $prid . '-' . uniqid() . '.' . $ext;

	$tempFile = $_FILES['file']['tmp_name'];          //3

	$targetFile =  $targetPath . $uniq_name;  //5
	//$targetFile =  $targetPath. $_FILES['file']['name'];  //5

	if (move_uploaded_file($tempFile, $targetFile)) { //6

		$fields = array(
			'item'    => $prid,
			'photo'   => $uniq_name,
		);
		$db->insert('store_inventory_photos', $fields);
	} else {
		logger(1, "photos", "Failed to move photo, $ferror");
	}
}
if ($edit != '') {
	$itemQ = $db->query("SELECT * FROM store_inventory WHERE id = ?", array($edit));
	$itemC = $itemQ->count();
	if ($itemC > 0) {
		$item = $itemQ->first();
	} else {
		Redirect::to('inventory.php?err=Item+not+found');
	}
}

if (!empty($_POST)) {
	$fields = [];
	foreach ($_POST as $k => $v) {
		if ($k != "submit") {
			$fields[$k] = $v;
		}
	}
	$id = Input::get('category');
	$id = getTopCat($id);
	$fields['topcat'] = $id;
	if (is_numeric($edit)) {
		$db->update('store_inventory', $edit, $fields);
		Redirect::to('inventory.php?err=Item+updated!');
	} else {
		$db->insert('store_inventory', $fields);

		Redirect::to('manage_inventory.php?err=Item+added!&last=' . $_POST['category']);
	}
}
$delphoto = Input::get('delphoto');
if (is_numeric($delphoto)) {
	$q = $db->query("SELECT * FROM store_inventory_photos WHERE id = ? AND item = ?", [$delphoto, $edit]);
	$c = $q->count();
	if ($c > 0) {
		$f = $q->first();
		$db->query("DELETE FROM store_inventory_photos WHERE id = ? AND item = ?", [$delphoto, $edit]);
		unlink($abs_us_root . $us_url_root . 'usersc/plugins/store/img/' . $f->photo);
		Redirect::to('manage_inventory.php?edit=' . $edit);
	}
}

?>


<div id="page-wrapper">
	<div class="container-fluid">
		<!-- Page Heading -->
		<div class="row">
			<div class="col-12 col-sm-6">
				<h1 class="page-header">
					Manage Inventory (<a href="inventory.php">View</a>)
				</h1>
				<form class="" action="manage_inventory.php?edit=<?= $edit ?>" method="post">
					<label for="">Item Category* (<a href="categories.php">Category Manager</a>)</label>
					<select class="form-control" name="category" required>
						<?php
						if ($edit != '') { ?>
							<option value="<?= $item->category ?>"><?php echoCat($item->category); ?></option>
						<?php } else { ?>
							<option value="" disabled selected="selected">--Choose Category--</option>
						<?php }
						foreach ($cats as $c) {
						?>
							<option <?php if ($last == $c->id) {
										echo "selected";
									} ?> value="<?= $c->id ?>"><?= echoCat($c->id); ?></option>
						<?php } ?>
					</select>

					<label for="">Item Name*</label>
					<input type="text" name="item" value="<?php if ($edit != '') {
																echo $item->item;
															} ?>" class="form-control" required>

					<label for="">Long Item Description</label>
					<textarea name="Description" rows="3" cols="80" class="form-control"><?php if ($edit != '') {
																								echo $item->description;
																							} ?></textarea>

					<label for="">Price*</label>
					<input type="number" name="price" value="<?php if ($edit != '') {
																	echo $item->price;
																} ?>" min="0.00" step="0.01" class="form-control" required>

					<label for="">Quantity on Hand (QOH)*</label>
					<input type="number" name="qoh" value="<?php if ($edit != '') {
																echo $item->qoh;
															} ?>" min="0" step="1" class="form-control" required>

					<!-- <label for="">Cost of Goods</label>
					<input type="number" name="cost" value="<?php //if($edit != ''){ echo $item->cost;}
															?>" min="0.00" step="0.01" class="form-control"> -->
					<label for="">Is this a digital/non-physical item</label>
					<select class="form-control" name="digital" required>
						<option value="0" <?php if (($edit != '') && ($item->digital == 0)) {
												echo "selected";
											} ?>>No</option>
						<option value="1" <?php if (($edit != '') && ($item->digital == 1)) {
												echo "selected";
											} ?>>Yes</option>
					</select>

					<label for="">Disabled</label>
					<select class="form-control" name="disabled" required>
						<option value="0" <?php if (($edit != '') && ($item->disabled == 0)) {
												echo "selected";
											} ?>>No</option>
						<option value="1" <?php if (($edit != '') && ($item->disabled == 1)) {
												echo "selected";
											} ?>>Yes</option>
					</select>
					<!-- <label for="">Photo*</label>
					<input type="text" name="photo" value="" class="form-control"> -->
					<br>
					<?php if ($edit == '') { ?>
						<input type="submit" name="submit" value="Add New Item" class="btn btn-primary">
					<?php } else { ?>
						<input type="submit" name="submit" value="Edit Item" class="btn btn-danger">
					<?php } ?>
				</form>
			</div> <!-- /.col -->
			<?php if ($edit > 0) { ?>
				<div class="col-12 col-sm-6">
					<h3>Manage Photos</h3>
					<meta charset="UTF-8" />
					<h3 align="center">Upload Your Photos</h3>
					<form action="manage_inventory.php?edit=<?= $edit ?>" id="my-awesome-dropzone" class="dropzone"></form>
				</div>
			<?php } ?>
		</div> <!-- /.row -->
		<div class="row">
			<div class="col-12">
				<h3>Item Photos</h3>
				<?php if ($edit > 0) {
					$photos = $db->query("SELECT * FROM store_inventory_photos WHERE item = ?", [$edit])->results();
				?>
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Photo (Click for full)</th>
								<th>Filename</th>
								<th>Delete</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($photos as $p) { ?>
								<tr>
									<td><a href="<?= $us_url_root ?>usersc/plugins/store/img/<?= $p->photo ?>"><img src="<?= $us_url_root ?>usersc/plugins/store/img/<?= $p->photo ?>" alt="" height="75"></a>
									</td>
									<td><?= $p->photo ?></td>
									<td><button type="button" onclick="window.location.href = 'manage_inventory.php?delphoto=<?= $p->id ?>&edit=<?= $edit ?>';" name="button" class="btn btn-danger">Delete Immediately</button></td>
								</tr>
							<?php } ?>
						</tbody>

					</table>
				<?php } ?>
			</div>
		</div>
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php //require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls 
?>

<script type="text/javascript">
	Dropzone.options.myAwesomeDropzone = {
		maxFiles: 10,
		dictDefaultMessage: "Drag up to 10 images (png,jpg) here<br>or click this box to open your file manager.",
		acceptedFiles: ".png,.jpg,.jpeg",
		accept: function(file, done) {
			console.log("uploaded");
			done();
			// alert("Uploaded!");
		},
		init: function() {
			this.on("maxfilesexceeded", function(file) {
				alert("No more files please!");
			});

			this.on('queuecomplete', function() {
				location.reload();
			});

		}
	};
</script>

<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; // currently just the closing /body and /html 
?>