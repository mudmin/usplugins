<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$c = Input::get('id');

if($c != "" && $c > 0){
  $contentQ = $db->query("SELECT * FROM plg_cms_widgets WHERE id = ?",[$c]);
  $contentC = $contentQ->count();
  if($contentC > 0){
    $content = $contentQ->first();
  }
  }

  if(!empty($_POST['content'])){

    $fields = array(
      'title'=>Input::get('title'),
      'content'=>trim(Input::get('content')),
      'widget_type'=>Input::get('widget_type'),
      'file'=>Input::get('widget_file'),
      );
      $db->update('plg_cms_widgets',$c,$fields);

      Redirect::to('admin.php?view=plugins_config&plugin=cms&method=widget_edit&msg=Saved&id='.$content->id);

  }
  if($c != "" && $c > 0){
  ?>
  <br>
  <form action="" method="post">
    <input type="hidden" name="csrf" value="<?=$token?>">
    <div class="form-group">
      <label for="">Widget Title</label>
      <input class="form-control" type="text" name="title" value="<?=$content->title?>" required>
    </div>

    <div class="form-group">
      <label for="">Widget Type</label>
      <select class="form-control" name="widget_type" required>
        <option <?php if ($content->widget_type == 1){echo "selected";}?> value="1">Widget File</option>
        <option <?php if ($content->widget_type == 2){echo "selected";}?> value="2">HTML Widget</option>
      </select>
    </div>
    <div class="form-group">
      <label for="">Widget File</label><br>
      If this is a file based widget, this box should ONLY contain the file name (no path) of the widget file.<br>
      The file should be placed in <strong>usersc/plugins/cms/widgets</strong><br>
      <input class="form-control" type="text" name="widget_file" value="<?=$content->file?>">
    </div>

    <div class="form-group">
      <label for="">Widget Content</label><br>
      If this is an HTML widget, put whatever you want in the box.
      <textarea name="content" id="editor"><?=$content->content?></textarea>
    </div>

      <p><input type="submit" value="Save"></p>
  </form>
  <?php
} //if contnet provided

if($c == ""){
  //no piece of content provided

  ?>
  <form class="" action="admin.php" method="get">
    <input type="hidden" name="view" value="plugins_config">
    <input type="hidden" name="plugin" value="cms">
    <input type="hidden" name="method" value="widget_edit">
    <p><input type="submit" value="Choose"></p>
    <select class="form-control" name="id">
      <option value="" disabled selected>--Choose Widget to Edit</option>
      <?php
        $allContent = $db->query("SELECT * FROM plg_cms_widgets")->results();
        foreach ($allContent as $a) { ?>
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
if(file_exists($abs_us_root.$us_url_root."usersc/includes/cmseditor.php")){
  include $abs_us_root.$us_url_root."usersc/includes/cmseditor.php";
}else{
  include $abs_us_root.$us_url_root."usersc/plugins/cms/assets/cmseditor.php";
}
?>
