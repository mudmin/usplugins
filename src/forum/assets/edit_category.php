<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
if(!pluginActive('forum',true)){die;}

$id = Input::get('id');
$catQ = $db->query("SELECT * FROM forum_categories WHERE id = ?",[$id]);
$catC = $catQ->count();
if($catC < 1){
  Redirect::to('admin.php?view=plugins_config&plugin=forum&action=manager&err=Category+not+found');
}else{
  $cat = $catQ->first();
}
if(!empty($_POST)){
  $token = $_POST['csrf'];
if(!Token::check($token)){
 include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
$fields = array(
  'deleted'=>Input::get('deleted'),
  'category'=>Input::get('category'),
);
$db->update("forum_categories",$id,$fields);
Redirect::to('admin.php?view=plugins_config&plugin=forum&action=manager&err=Category+updated');
}
?>

<div class="row">
  <div class="col-12 col-sm-6 offset-sm-3">
    <h1 class="text-center">Edit Category</h1>
<form class="" action="" method="post">
  <input type="hidden" name="csrf" value="<?=Token::generate();?>" />
  <div class="form-group">
    <label for="">Delete/Disable Category</label><br>
    Note: This will not delete your actual content, just delete the ability to view/edit it.<br>
    <select class="form-control" name="deleted">
      <option value="0" <?php if($cat->deleted == 0){echo "selected='selected'";}?>>No</option>
      <option value="1" <?php if($cat->deleted == 1){echo "selected='selected'";}?>>Yes</option>
    </select>
  </div>
  <div class="form-group">
    <label for="">Category Name</label><br>
    <input type="text" class="form-control" name="category" value="<?=$cat->category?>">
  </div>
  <div class="form-group">
    <input type="submit" name="update" value="Update" class="btn btn-primary">
  </div>
</form>
</div>
</div>
