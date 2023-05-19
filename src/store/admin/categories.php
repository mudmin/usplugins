<?php
require '../../../../users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!pluginActive("store", true)) {
	die();
}
$cats = $db->query("SELECT * FROM store_categories WHERE disabled = 0 AND is_subcat = 0 ORDER BY cat ")->results();
$edit = Input::get("edit");
$edits = Input::get("edits");
$last = Input::get("last");
if ($edit != '') {
	$itemQ = $db->query("SELECT * FROM store_categories WHERE id = ?", array($edit));
	$itemC = $itemQ->count();
	if ($itemC > 0) {
		$item = $itemQ->first();
	} else {
		Redirect::to('categories.php?err=Category+not+found');
	}
}

if ($edits != '') {
	$subQ = $db->query("SELECT * FROM store_categories WHERE id = ?", array($edits));
	$subC = $subQ->count();
	if ($subC > 0) {
		$sub = $subQ->first();
	} else {
		Redirect::to('categories.php?err=Sub+Category+not+found');
	}
}

if (!empty($_POST)) {
	if (!empty($_POST['catForm'])) {
		$fields = array(
			'cat' => Input::get('cat'),
			'disabled' => Input::get('disabled'),
		);

		if (is_numeric($edit)) {
			$db->update('store_categories', $edit, $fields);
			Redirect::to('categories.php?err=Category+updated!');
		} else {
			$db->insert('store_categories', $fields);
			Redirect::to('categories.php?err=Category+added!');
		}
	}

	if (!empty($_POST['subcatForm'])) {
		$fields = array(
			'subcat_of' => Input::get('cat'),
			'cat' => Input::get('subcat'),
			'is_subcat' => 1,
			'disabled' => Input::get('disabled'),
		);

		if (is_numeric($edits)) {
			$db->update('store_categories', $edits, $fields);
			Redirect::to('categories.php?err=Sub+Category+updated!');
		} else {
			$db->insert('store_categories', $fields);
			Redirect::to('categories.php?err=Sub+Category+added!');
		}
	}
}
?>

<div id="page-wrapper">
	<div class="container-fluid">
		<!-- Page Heading -->
		<div class="row">
			<div class="col-12">
				<h1 class="page-header">
					Category Manager
				</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">
				<h3><?php
					if ($edit != '') {
						echo "Edit Category";
					} else {
						echo "New Category";
					} ?></h3>
				<form class="" action="categories.php?edit=<?= $edit ?>" method="post">
					<input type="hidden" name="catForm" value="1">
					<label for="">Category Name*</label>
					<input type="text" name="cat" class="form-control" value="<?php if ($edit != '') {
																					echo $item->cat;
																				} ?>">

					<label for="">Category Disabled?</label>
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
					<?php if (isset($item) && $item->photo != '') { ?>
						<img src="<?= $us_url_root . 'usersc/plugins/store/img/' . $item->photo ?>" alt="" height="150px"><br><br>
					<?php } ?>
					<?php if ($edit == '') { ?>
						<input type="submit" name="submit" value="Add New Category" class="btn btn-primary">
					<?php } else { ?>
						<input type="submit" name="submit" value="Edit Category" class="btn btn-danger">
					<?php } ?>
				</form>
			</div> <!-- /.col -->

			<div class="col-sm-6">
				<h3><?php
					if ($edits != '') {
						echo "Edit Sub-Category";
					} else {
						echo "New Sub-Category";
					} ?></h3>
				<form class="" action="categories.php?edits=<?= $edits ?>" method="post">
					<input type="hidden" name="subcatForm" value="1">
					<label for="">Category*</label>
					<select class="form-control" name="cat">
						<option value="">--Choose Category--</option>
						<?php foreach ($cats as $c) { ?>
							<option <?php if (($edits != '') && ($sub->subcat_of == $c->id)) {
										echo "selected";
									} ?> value="<?= $c->id ?>"><?= $c->cat ?></option>
						<?php } ?>
					</select>
					<label for="">Sub Category*</label>
					<input type="text" name="subcat" class="form-control" value="<?php if ($edits != '') {
																						echo $sub->cat;
																					} ?>">

					<label for="">Sub Category Disabled?</label>
					<select class="form-control" name="disabled" required>
						<option value="0" <?php if (($edits != '') && ($sub->disabled == 0)) {
												echo "selected";
											} ?>>No</option>
						<option value="1" <?php if (($edits != '') && ($sub->disabled == 1)) {
												echo "selected";
											} ?>>Yes</option>
					</select>
					<!-- <label for="">Photo*</label>
				<input type="text" name="photo" value="" class="form-control"> -->
					<br>
					<?php if ($edits == '') { ?>
						<input type="submit" name="submit" value="Add New Sub Category" class="btn btn-primary">
					<?php } else { ?>
						<input type="submit" name="submit" value="Edit Sub Category" class="btn btn-danger">
					<?php } ?>
				</form>
			</div> <!-- /.col -->
		</div> <!-- /.row -->
		<?php if ($edit != '') { ?>
			<div class="row">
				<div class="col-6 offset 3">
					<script src="<?= $us_url_root ?>usersc/plugins/store/assets/dropzone.js"></script>
					<link href="<?= $us_url_root ?>usersc/plugins/store/assets/dropzone.css" type="text/css" rel="stylesheet" />
					<h3>Add Photo to the Selected <?php if ($edits != '') {
														echo "Sub-";
													} ?>Category</h3>
					<?php if (!empty($_FILES)) {

						$date = date('Y-m-d');
						$prid = $edit;
						if ($prid == '') {
							$prid == $edits;
						}
						$ds          = '/';  //1

						$targetPath = $abs_us_root . $us_url_root . 'usersc/plugins/store/img/';   //2

						$name = $_FILES["file"]["name"];
						$ext = end((explode(".", $name)));
						$uniq_name = "category-" . $prid . '-' . uniqid() . '.' . $ext;

						$tempFile = $_FILES['file']['tmp_name'];          //3

						$targetFile =  $targetPath . $uniq_name;  //5
						//$targetFile =  $targetPath. $_FILES['file']['name'];  //5

						if (move_uploaded_file($tempFile, $targetFile)) { //6
							$fields = array(
								'photo'   => $uniq_name,
							);
							$old = $db->query("SELECT * FORM store_categories WHERE id = ?", [$prid])->first();
							if (file_exists($abs_us_root . $us_url_root . 'usersc/plugins/store/img/' . $old->photo)) {
								unlink($abs_us_root . $us_url_root . 'usersc/plugins/store/img/' . $old->photo);
							}
							$db->update('store_categories', $prid, $fields);
							logger(1, 'Photo Fail', $db->errorString());
						} else {
							logger(1, "photos", "Failed to move photo, $ferror");
						}
					} ?>
					<meta charset="UTF-8" />
					<form action="categories.php?edit=<?= $edit ?>" id="my-awesome-dropzone" class="dropzone"></form>
					<script type="text/javascript">
						Dropzone.options.myAwesomeDropzone = {
							maxFiles: 1,
							dictDefaultMessage: "Drag an image (png,jpg) here<br>or click this box to open your file manager.",
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

				</div>
			</div><br>
		<?php } ?>

		<div class="row">
			<div class="col-12">
				<h3>Edit Categories (Click to Edit)</h3>
				<?php foreach ($cats as $c) { ?>
					<a href="categories.php?edit=<?= $c->id ?>"><?= $c->cat ?></a><br>
					<?php
					$others = $db->query("SELECT * FROM store_categories WHERE subcat_of = ? ORDER BY cat", [$c->id])->results();
					foreach ($others as $o) { ?>
						<a href="categories.php?edits=<?= $o->id ?>">---- <?= $o->cat ?></a><br>
					<?php } ?>
				<?php } ?>
			</div>

		</div>
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php //require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls 
?>

<!-- Place any per-page javascript here -->

<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; // currently just the closing /body and /html 
?>