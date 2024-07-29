<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$c = Input::get('id');

if($c != "" && $c > 0){
  $contentQ = $db->query("SELECT * FROM plg_cms_content WHERE id = ? OR slug = ?",[$c,$c]);
  $contentC = $contentQ->count();
  if($contentC > 0){
    $content = $contentQ->first();
  }
  }

  if(!empty($_POST['content'])){
    $slug = createSlug(Input::get('title'));
    $tags = explode(",",Input::get('tags'));
    $fields = array(
      'title'=>Input::get('title'),
      'slug'=>$slug,
      'category'=>Input::get('category'),
      'status'=>Input::get('status'),
      'content'=>Input::get('content'),
      'layout'=>Input::get('layout'),
      );
      if(Input::get('status') == 1 && $content->status == 0){
        $fields['date_published'] = date("Y-m-d");
      }
      $db->update('plg_cms_content',$c,$fields);
      $db->query("DELETE FROM plg_cms_tags WHERE article = ?",[$c]);
      foreach($tags as $t){
        if($t != ""){
        $db->insert('plg_cms_tags',['article'=>$c,'tag'=>trim($t)]);
        }
      }

      Redirect::to('admin.php?view=plugins_config&plugin=cms&method=content_edit&msg=Saved&id='.$content->id);

  }
  if($c != "" && $c > 0){
  ?>
  <br>
  <a class="btn btn-dark" href="<?=$us_url_root?><?=$plg_settings->parser?>?c=<?=$content->slug?>">
    View in Frontend</a>
  <form action="" method="post">
    <input type="hidden" name="csrf" value="<?=$token?>">
    <div class="form-group">
      <label for="">Content Title</label>
      <input class="form-control" type="text" name="title" value="<?=$content->title?>" required>
    </div>

      <textarea name="content" id="editor"><?=$content->content?></textarea>
      <p><input type="submit" value="Save" class="btn btn-outline-primary btn-sm"></p>

      <div class="form-group">
        <label for="">Category</label>
        Currently visible to: <?php echoCatPerms($content->category);?>
        <select class="form-control" name="category" required>
          <?php cmsCatTree(0,"",$content->category);?>
        </select>

      </div>

      <div class="form-group">
        <label for="">Status</label>
        <select class="form-control" name="status" required>
          <option <?php if ($content->status == 0){echo "selected";}?> value="0">Draft</option>
          <option <?php if ($content->status == 1){echo "selected";}?> value="1">Published</option>
        </select>
      </div>

      <div class="form-group">
        <label for="">Override default layout for this piece of content (optional)</label>
        <select class="form-control" name="layout">
          <option value="0">--Do not override layout--</option>
          <?php
          $layouts = $db->query("SELECT * FROM plg_cms_layouts ORDER BY title ASC")->results();

          foreach($layouts as $l){?>
            <option <?php if ($content->layout == $l->id){echo "selected";}?>  value="<?=$l->id?>"><?=$l->title?></option>
          <?php }
          ?>
        </select>
      </div>
      <div class="form-group">
        <label for="">Content Tags (Comma Separated)(Optional)</label>
        <?php
         $tagsQ = $db->query("SELECT * FROM plg_cms_tags WHERE article = ?",[$c]);
         $tagsC = $tagsQ->count();
         $tagstring = '';
         if($tagsC > 0){
           $tags = $tagsQ->results();
           $tagstring = '';
           foreach($tags as $t){
             $tagstring .= $t->tag.", ";
           }
         }
         ?>
        <input class="form-control" type="text" name="tags" value="<?=$tagstring?>">
      </div>
      <p><input type="submit" value="Save" class="btn btn-outline-primary btn-sm"></p>
  </form>
  <?php
} //if contnet provided

if($c == ""){
  //no piece of content provided

  ?>
  <form class="" action="admin.php" method="get">
    <input type="hidden" name="view" value="plugins_config">
    <input type="hidden" name="plugin" value="cms">
    <input type="hidden" name="method" value="content_edit">
    <p><input type="submit" value="Choose"></p>
    <select class="form-control" name="id">
      <option value="" disabled selected>--Choose Content to Edit</option>
      <?php
        $allContent = $db->query("SELECT * FROM plg_cms_content")->results();
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
