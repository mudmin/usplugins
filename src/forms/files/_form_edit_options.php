<?php
$fieldQ = $db->query("SELECT * FROM $name WHERE id = ?",array($field));
$fieldC = $fieldQ->count();
if($fieldC > 0){
	$f = $fieldQ->first();
	$current = json_decode($f->select_opts);
}else{
	Redirect::to($us_url_root."admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit_options&edit=".$edit."&err=Field+not+found.");
} ?>

<br>
<h4 style="color:blue;">Edit your <?=$f->field_type?> options for <?=$f->form_descrip?></h4>
<br>
<form class="" action="" method="get" onsubmit="return confirm('Do you really want to do this? This will clear out any saved options you have for this <?=$f->field_type?>!');">
	<input type="hidden" name="view" value="plugins_config">
	<input type="hidden" name="newFormView" value="_admin_forms_edit">
	<input type="hidden" name="edit" value="<?=$edit?>">
	<input type="hidden" name="field" value="<?=$field?>">
	<input type="hidden" name="plugin" value="forms">
	<input type="hidden" name="editOpts" value="true">

	<?php if(isset($current->usformquery)){ ?>
		You are using the <span style="color:red;">database</span> to create options<br>
		<input type="hidden" name="switchto" value="manually">
		<input type="submit" name="submit" value="Switch to Manual Options" class="btn-outline-primary">

	<?php }else{ ?>
		You are <span style="color:red;">manually</span> creating options<br>
		<input type="hidden" name="switchto" value="database">
		<input type="submit" name="submit" value="Switch to Database Options" class="btn-outline-primary">
	<?php } ?>

</form>
<br>
<?php if(isset($current->usformquery)){ ?>
	This option allows you to do a database query to generate options for your form field.  It requires 3 things.
	<li>A database query  all of the raw data in the database</li>
	<li>A key, which is a column of the database.  Most of the time this is the id column, but you may want to store some text value in there, so it can be any column.  This is what will actually be stored in the database when this option is selected in the form</li>
	<li>1 or more values to be shown on the front end of the form.  This can be text or one or more colunmns of the database.  A perfect example would be if you want to show last name, first name. That would actually require 3 values:</li>
	<div style="margin-left:35px;">
		<ul>
			- db column lname<br>
			- string ,(space)<br>
			- db column fname
		</ul>
	</div>
	You can now pass <span class="text-danger">{{{user_id}}}</span> in your query to restrict your db query to only data for a given user.  For example.<br><b><i>SELECT * FROM logs where user_id = {{{user_id}}}</i></b>

	<form autocomplete="off" class="" name="createForm" action="" method="post">
		<input type="hidden" name="editing" value="<?=$field?>">
		<input type="hidden" name="editOpts" value="<?=Input::get('editOpts')?>">
		<table class="table table-striped" id="opts">
			Leave both columns of an option blank to make it go away.
			<thead>
				<tr>
					<th></th>
					<th></th>

				</tr>
			</thead>
			<tbody>

				<tr>
					<td>Enter your raw DB query without quotes such as <span style="color:blue;">SELECT * FROM users</span></td>
					<td>
						<input type="hidden" name="key[]" value="usformquery">
						<textarea name="val[]" class="form-control"><?=$current->usformquery?></textarea>
			

				</tr>
				<tr>
					<td>Enter your DB column to be used as your form "value" such as  <span style="color:blue;">id</span></td>
					<td>
						<input type="hidden" name="key[]" value="key">
						<input type="text" name="val[]" value="<?=$current->key?>">
					</td>

				</tr>
				<tr>
					<td></td>
					<td><a id="add" class="btn" style="outline:1px solid black !important;">Add Another Row</a></td>
				</tr>
				<?php
				if($current->values != []){
					foreach($current->values as $primary){
						foreach($primary as $k=>$v){

						?>
						<tr class='optRow'>
							<td>

							</td>
							<td>
								<select class='' name='schemakey[]'>
									<option <?php if($k == 'col'){echo "selected='selected";}?> value='col'>DB Column</option>
									<option <?php if($k == 'str'){echo "selected='selected";}?> value='str'>String</option>
								</select>
								<input type="text" name="schemaval[]" value="<?=$v?>">
								<button class='removeMe'>remove</button>
							</td>
						</tr>
					<?php }
				}
				}else{ ?>
					<tr class='optRow'>
						<td></td>
						<td>
							<select class='' name='schemakey[]'>
								<option value='col'>DB Column</option>
								<option value='str'>String</option>
							</select>
							<input type='text' name='schemaval[]' value=''>
							<button class='removeMe'>remove</button>
						</td>

					</tr>
				<?php }
				?>
			</tbody>
		</table>
		<input type="submit" name="edit_this_field_options" value="Save Field Settings" class="btn btn-outline-primary">
	</form>
<?php }else{ ?>
	<form autocomplete="off" class="" name="createForm" action="" method="post">
		<input type="hidden" name="editing" value="<?=$field?>">
		<input type="hidden" name="editOpts" value="<?=Input::get('editOpts')?>">
		<table class="table" id="opts">
			Leave both columns of an option blank to make it go away.
			<thead>
				<tr>
					<th></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><a  id="addClassic" class="btn" style="outline:1px solid black !important;">Add Another Option</a></td>
				</tr>
				<?php foreach($current as $k=>$v){ ?>
					<tr class='optRow'>
						<td><input type="text" name="key[]" value="<?=$k?>"></td>
						<td><input type="text" name="val[]" value="<?=$v?>"></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
		<input type="submit" name="edit_this_field_options" value="Save Field Settings" class="btn btn-outline-primary">
	</form>
<?php } ?>

<script type="text/javascript">
$(document).ready(function() {
	$("#add").click(function() {
		var markup = "<tr class='optRow'><td></td><td><select class='' name='schemakey[]'><option value='col'>DB Column</option><option value='str'>String</option></select><input type='text' name='schemaval[]' value=''><button class='removeMe'>remove</button></td></tr>";
		$('#opts').append(markup);
		return false;
	});
	$("#addClassic").click(function() {
		var markup = "<tr class='optRow'><td><input type='text' name='key[]' value=''></td><td><input type='text' name='val[]' value=''><button class='removeMe'>remove</button></td></tr>";
		$('#opts').append(markup);
		return false;
	});

	$(document).on("click", ".removeMe", function(e) {
		 e.preventDefault();
		 console.log("clicked");
 		$(this).closest('.optRow').remove();
});

});
</script>
