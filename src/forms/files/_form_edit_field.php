<h2>Editing</h2>
<p>Please note that only certain options can be edited once a field is created.  If you need to edit the others,
	delete the field and create a new one or CAREFULLY edit the database.</p>
	<?php
	$fieldQ = $db->query("SELECT * FROM $name WHERE id = ?",array($field));
	$fieldC = $fieldQ->count();
	if($fieldC > 0){
		$f = $fieldQ->first();
	}else{
		Redirect::to($us_url_root."admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&edit=".$edit."&err=Field+not+found.");
	}
	if($f->field_type == "dropdown" || $f->field_type == "radio" || $f->field_type == "checkbox"){ ?>
		<a href="<?=$us_url_root?>users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&edit=<?=$edit?>&field=<?=$field?>&editOpts=<?=$field?>" class="btn btn-outline-primary">Click here to edit your <?=$f->field_type?> options</a>
	<?php } ?>
	<form autocomplete="off" class="" name="createForm" action="" method="post">
		<input type="hidden" name="editing" value="<?=$field?>">
		<div class="form-group">
			<label for="">Label when displaying forms</label>
			<input class="form-control"  type="text" name="form_descrip" value="<?=$f->form_descrip?>" required>
		</div>

		<div class="form-group">
			<label for="">Label when displaying tables(often shorter)</label>
			<input  class="form-control" type="text" name="table_descrip" value="<?=$f->table_descrip?>" required>
		</div>

		<div class="form-group">
			<label for="">Order</label>
			<input  class="form-control" type="number" name="ord" value="<?=$f->ord?>" min="0" step="1" required>
		</div>

		<div class="form-group">
			<label for="">Required?</label>
			<select class="form-control" name="required" required>
				<option value="<?=$f->required?>"><?php bin($f->required);?></option>
				<option value="0">No</option>
				<option value="1">Yes</option>
			</select>
		</div>

		<div class="form-group">
			<label for="">Class</label>
			<input  class="form-control" type="text" name="field_class" value="<?=$f->field_class?>" >
		</div>

		<div class="form-group">
			<label for="">Raw HTML inside input tag</label>
			<textarea class="form-control"  name="input_html" rows="4" cols="120"><?=$f->input_html?></textarea>
		</div>
		<?php require_once($abs_us_root.$us_url_root."usersc/plugins/forms/files/_form_validation_options.php");?>
		<input type="submit" name="edit_this_field" value="Save Field Settings" class="btn btn-outline-primary">
	</form>
	<div class="form-group">
		<?php
		$val = json_decode($f->validation);
		?>
		<table class="table" id="valTable">
			<label for="">Current Validation Options</label>
			<thead>
				<tr>
					<th>Option</th><th>Value</th><th>Delete</th>
				</tr>
			</thead>
			<tbody>

				<?php if($val != ''){
					foreach($val as $k=>$v){ ?>
						<tr>
							<td><?=$k?></td>
							<td><?=$v?></td>
							<td><form autocomplete="off" class="" action="" name="deleteForm" method="post">
								<input type="hidden" name="toDelete" value="<?=$k?>">
								<input type="submit" name="deleteValidation" class="btn" value="Delete This"></form></td>
							</tr>
						<?php }
					}else{
						echo "<br><font color='red'>No validation options are set.</font>";
					} ?>

				</tbody>
			</table>
		</div>

		<script type="text/javascript">
		$(document).ready(function() {
			  console.log("ready2");
			$("#add").click(function() {
				console.log("cliiicked");
				var markup = "<tr><td><input type='text' name='key[]' value=''></td><td><input type='text' name='val[]' value=''></td></tr>";

		    $('#opts').append(markup);
				// $('#opts tbody>tr:last').clone(true).insertAfter('#opts tbody>tr:last');
				// $('#opts tbody>tr:last .clearIt').val('');
				return false;
			});
		});
		</script>
