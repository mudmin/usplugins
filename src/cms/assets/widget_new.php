<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
if(!empty($_POST['widget_type'])){
  $fields = array(
    'title'=>Input::get('title'),
    'content'=>trim(Input::get('content')),
    'widget_type'=>Input::get('widget_type'),
    'file'=>Input::get('widget_file'),
  );
  $db->insert('plg_cms_widgets',$fields);
  $id = $db->lastId();
  Redirect::to('admin.php?view=plugins_config&plugin=cms&method=widget_edit&msg=Widget+created&id='.$id);

}
?>
<br>
<form action="" method="post">
  <input type="hidden" name="csrf" value="<?=$token?>">
  <div class="form-group">
    <label for="">Widget Title</label>
    <input class="form-control" type="text" name="title" value="" required>
  </div>

  <div class="form-group">
    <label for="">Widget Type</label>
    <select class="form-control" name="widget_type" required>
      <option value="1">Widget File</option>
      <option selected value="2">HTML Widget</option>
    </select>
  </div>
  <div class="form-group">
    <label for="">Widget File</label><br>
    If this is a file based widget, this box should ONLY contain the file name (no path) of the widget file.<br>
    The file should be placed in <strong>usersc/plugins/cms/widgets</strong><br>
    <input class="form-control" type="text" name="widget_file" value="">
  </div>

  <div class="form-group">
    <label for="">Widget Content</label><br>
    If this is an HTML widget, put whatever you want in the box.
  <textarea name="content" id="editor"></textarea>
  </div>
  <p><input type="submit" value="Save" class="btn btn-outline-primary btn-sm"></p>
</form>
<?php
if(file_exists($abs_us_root.$us_url_root."usersc/includes/cmseditor.php")){
  include $abs_us_root.$us_url_root."usersc/includes/cmseditor.php";
}else{
  include $abs_us_root.$us_url_root."usersc/plugins/cms/assets/cmseditor.php";
}
 ?>
