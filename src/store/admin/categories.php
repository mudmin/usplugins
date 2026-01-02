<?php
require '../../../../users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
if (!hasPerm(2)) {
	die("You do not have permission to access this page.");
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

	if (!empty($_POST['deleteCategory'])) {
		$catId = Input::get('cat_id');
		if (is_numeric($catId)) {
			// Check if category has items
			$hasItems = $db->query("SELECT id FROM store_inventory WHERE category = ?", [$catId])->count();
			if ($hasItems > 0) {
				Redirect::to('categories.php?err=Cannot+delete+category+with+items.+Remove+or+reassign+items+first.');
			} else {
				// Delete any subcategories first
				$db->query("DELETE FROM store_categories WHERE subcat_of = ?", [$catId]);
				// Delete the category
				$db->query("DELETE FROM store_categories WHERE id = ?", [$catId]);
				Redirect::to('categories.php?err=Category+deleted!');
			}
		}
	}

	if (!empty($_POST['deleteSubCategory'])) {
		$subId = Input::get('subcat_id');
		if (is_numeric($subId)) {
			// Check if subcategory has items
			$hasItems = $db->query("SELECT id FROM store_inventory WHERE category = ?", [$subId])->count();
			if ($hasItems > 0) {
				Redirect::to('categories.php?err=Cannot+delete+subcategory+with+items.+Remove+or+reassign+items+first.');
			} else {
				$db->query("DELETE FROM store_categories WHERE id = ?", [$subId]);
				Redirect::to('categories.php?err=Sub+Category+deleted!');
			}
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
						$prid = !empty($edit) ? $edit : $edits;
						$targetPath = $abs_us_root . $us_url_root . 'usersc/plugins/store/img/';
						$tempFile = $_FILES['file']['tmp_name'];

						$mimeMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
						$fileType = mime_content_type($tempFile);

						if (array_key_exists($fileType, $mimeMap)) {
							$ext = $mimeMap[$fileType];
							$uniq_name = "category-" . $prid . '-' . uniqid() . '.' . $ext;
							$targetFile = $targetPath . $uniq_name;

							if (move_uploaded_file($tempFile, $targetFile)) {
								$fields = ['photo' => $uniq_name];
								$old = $db->query("SELECT photo FROM store_categories WHERE id = ?", [$prid])->first();

								if ($old && !empty($old->photo)) {
									$oldFile = $targetPath . basename($old->photo);
									if (file_exists($oldFile)) {
										unlink($oldFile);
									}
								}

								$db->update('store_categories', $prid, $fields);
							} else {
								logger(1, "photos", "Failed to move photo");
							}
						}
					}
					?>
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
					<div class="mb-2">
						<a href="categories.php?edit=<?= $c->id ?>"><?= $c->cat ?></a>
						<form method="post" action="categories.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this category? This will also delete all subcategories.');">
							<input type="hidden" name="deleteCategory" value="1">
							<input type="hidden" name="cat_id" value="<?= $c->id ?>">
							<button type="submit" class="btn btn-xs btn-danger" title="Delete Category">X</button>
						</form>
					</div>
					<?php
					$others = $db->query("SELECT * FROM store_categories WHERE subcat_of = ? ORDER BY cat", [$c->id])->results();
					foreach ($others as $o) { ?>
						<div class="mb-1 ms-4">
							<a href="categories.php?edits=<?= $o->id ?>">---- <?= $o->cat ?></a>
							<form method="post" action="categories.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this subcategory?');">
								<input type="hidden" name="deleteSubCategory" value="1">
								<input type="hidden" name="subcat_id" value="<?= $o->id ?>">
								<button type="submit" class="btn btn-xs btn-danger" title="Delete Subcategory">X</button>
							</form>
						</div>
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