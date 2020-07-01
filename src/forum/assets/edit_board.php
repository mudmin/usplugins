<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
if(!pluginActive('forum',true)){die;}

$id = Input::get('id');
$cats = $db->query("SELECT * FROM forum_categories ORDER BY category")->results();
$boardQ = $db->query("SELECT * FROM forum_boards WHERE id = ?",[$id]);
$boardC = $boardQ->count();
if($boardC < 1){
  Redirect::to('admin.php?view=plugins_config&plugin=forum&action=manager&err=Board+not+found');
}else{
  $board = $boardQ->first();
  $readable = explode(",",$board->to_read);
  $writeable = explode(",",$board->to_write);
}
$permissions = $db->query("SELECT * FROM permissions")->results();
if(!empty($_POST)){
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }
  $read = Input::get('read');
  $write = Input::get('write');
  $to_read = "";
  $to_write = "";
  foreach($read as $r){
    if($r == -1){$r = 0;}
    $to_read .= $r.",";
  }
  foreach($write as $r){
    if($r == -1){$r = 0;}
    $to_write .= $r.",";
  }
  $fields = array(
    'disabled'=>Input::get('disabled'),
    'board'=>Input::get('board'),
    'descrip'=>Input::get('descrip'),
    'cat'=>Input::get('cat'),
    'to_read'=>$to_read,
    'to_write'=>$to_write,
  );
  $db->update("forum_boards",$id,$fields);
  Redirect::to('admin.php?view=plugins_config&plugin=forum&action=manager&err=Board+updated');
}
?>

<div class="row">
  <div class="col-12 col-sm-6 offset-sm-3">
    <h1 class="text-center">Edit Board</h1>
    <form class="" action="" method="post">
      <input type="hidden" name="csrf" value="<?=Token::generate();?>" />
      <div class="form-group">
        <label for="">Delete/Disable Board</label><br>
        Note: This will not delete your actual content, just delete the ability to view/edit it.<br>
        <select class="form-control" name="disabled">
          <option value="0" <?php if($board->disabled == 0){echo "selected='selected'";}?>>No</option>
          <option value="1" <?php if($board->disabled == 1){echo "selected='selected'";}?>>Yes</option>
        </select>
      </div>
      <div class="form-group">
        <label for="">Board Name</label><br>
        <input type="text" class="form-control" name="board" value="<?=$board->board?>">
      </div>
      <div class="form-group">
        <label for="">Board Description</label><br>
        <input type="text" class="form-control" name="descrip" value="<?=$board->descrip?>">
      </div>
      <div class="form-group">
        <label for="">Category</label><br>
        <select class="form-control" name="cat">
          <?php foreach($cats as $c){?>
            <option value="<?=$c->id?>" <?php if($board->cat == $c->id){echo "selected='selected'";}?>> <?=$c->category?> <?php if($c->deleted == 1){echo "(DELETED!)";}?></option>
          <?php } ?>
        </select>
      </div>
      <div class="form-group">
        <label for="">Permission levels allowed to read this board</label><br>
        <input type="checkbox" name="read[]" value="-1" <?php if(in_array(0,$readable)){echo "checked";}?>> Public
        <?php foreach($permissions as $p){ ?>
          <input type="checkbox" name="read[]" value="<?=$p->id?>" <?php if(in_array($p->id,$readable)){echo "checked";}?>> <?=$p->name?>(<?=$p->id?>)
        <?php } ?>
      </div>
      <div class="form-group">
        <label for="">Permission levels allowed to post to board</label><br>
        <?php foreach($permissions as $p){ ?>
          <input type="checkbox" name="write[]" value="<?=$p->id?>" <?php if(in_array($p->id,$writeable)){echo "checked";}?>> <?=$p->name?>(<?=$p->id?>)
        <?php } ?>
      </div>
      <div class="form-group">
        <input type="submit" name="update" value="Update" class="btn btn-primary">
      </div>
    </form>
  </div>
</div>
