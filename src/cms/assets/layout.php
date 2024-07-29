<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$action = Input::get('action');
$id = Input::get('id');
if($action == 'edit'){
  $q = $db->query("SELECT * FROM plg_cms_layouts WHERE id = ?",[$id]);
  $c = $q->count();
  if($c > 0){
    $l = $q->first();
  }else{?>
    Please choose a layout to edit.
    <form class="" action="admin.php" method="get">
      <input type="hidden" name="view" value="plugins_config">
      <input type="hidden" name="plugin" value="cms">
      <input type="hidden" name="method" value="layout">
      <input type="hidden" name="action" value="edit">
      <select class="form-control" name="id">
        <option value="" disabled selected>--Choose Layout to Edit</option>
        <?php
          $allLayouts = $db->query("SELECT * FROM plg_cms_layouts")->results();
          foreach ($allLayouts as $a) { ?>
            <option value="<?=$a->id?>"><?=$a->title?></option>
          <?php } ?>
      </select>
      <div class="row">
        <div class="col-6 text-left">
          <input type="submit" name="Submit" value="Edit" class="btn btn-primary">
        </div>
        <div class="col-6 text-right">
          <button class="btn btn-danger" onclick="window.location='<?=$us_url_root?>admin.php?view=plugins_config&plugin=cms';">
            Go Back </button>
        </div>
      </div>
    </form>
  <?php }
}
if(!empty($_POST)){

if(Input::get('def') == 1){
  $db->query("UPDATE plg_cms_layouts SET def = 0");
}
$layout = $_POST['layout'];
$fields = array(
  'title'=>Input::get('title'),
  'layout'=> $_POST['layout'],
  'def'=>Input::get('def'),
);
if($action == 'edit'){
  $db->update('plg_cms_layouts',$id,$fields);
}else{
  $db->insert('plg_cms_layouts',$fields);
  Redirect::to('admin.php?view=plugins_config&plugin=cms&action=edit&method=layout_edit&id='.$db->lastId());
}
}
if($action == "new" || $action == "" || ($action == "edit" && $id > 0)){
 ?>
<form action="" method="post" name="new_content">
  <input type="hidden" name="csrf" value="<?=$token?>">
  <p>Layouts allow you to make templates that change the way your content is displayed.  A layout can have
    any HTML content inside them, but are generally setup as bootstrap grids.  A simple markup language
    allows you to decide what dynamic content goes where.</p>

  <p>Once you get your layout right, simple add the tag <strong>&lt;!>con&lt;!></strong> where you want your actual CMS/Blog
  content to show up on the page.</p>

  <p>To add a widget, simply add the shortcode <strong>&lt;!>wid123&lt;!></strong> where the 123 is the ID of the widget
  that you want to show up in that position.</p>

  <p>Other shortcodes include
  <strong>&lt;!>aut&lt;!></strong>-Author,
  <strong>&lt;!>nam&lt;!></strong>-Article Name,
  <strong>&lt;!>pub&lt;!></strong>-Date Published,
  <strong>&lt;!>mod&lt;!></strong>-Last Modified,
  <strong>&lt;!>cat&lt;!></strong>-Category,
  <strong>&lt;!>sts&lt;!></strong>-Status

</p>

  <p>Every page uses a standard layout by default, but you can create as many layouts as you like and even create your own default layout.</p>

    <div class="form-group">
      <label for="">Layout Title</label>
      <input class="form-control" type="text" name="title" value="<?php if($action == 'edit'){echo $l->title;}?>" required>
    </div>
    <div class="form-group">
      <label for="">Set as Default</label>
      <select class="form-control" name="def" required>
        <option <?php if($action == 'edit' && $l->def == 0){echo "selected";}?> value="0">No</option>
        <option <?php if($action == 'edit' && $l->def == 1){echo "selected";}?> value="1">Yes</option>
      </select>
    </div>

    <textarea name="layout" id="" rows="12" class="form-control" required><?php if($action == 'edit'){echo $l->layout;}?></textarea>
    <p><input type="submit" value="Save" class="btn btn-outline-primary btn-sm"></p>
</form>
<?php }
?>
