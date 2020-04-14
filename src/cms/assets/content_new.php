<?php if(count(get_included_files()) ==1) die("died"); //Direct Access Not Permitted
if(!empty($_POST)){
  $slug = createSlug(Input::get('title'));
  $tags = explode(",",Input::get('tags'));
  $fields = array(
    'author'=>$user->data()->id,
    'title'=>Input::get('title'),
    'slug'=>$slug,
    'category'=>Input::get('category'),
    'status'=>Input::get('status'),
    'content'=>Input::get('content'),
    'layout'=>Input::get('layout'),
    );
    if(Input::get('status') == 1){
      $fields['date_published'] = date("Y-m-d");
    }
    $db->insert('plg_cms_content',$fields);
    $id = $db->lastId();
    foreach($tags as $t){
      if($t != ""){
      $db->insert('plg_cms_tags',['article'=>$db->lastId(),'tag'=>trim($t)]);
      }
    }

    Redirect::to('admin.php?view=plugins_config&plugin=cms&method=content_edit&msg=Created&id='.$id);

}

 ?>
<form action="" method="post" name="new_content">
  <input type="hidden" name="csrf" value="<?=$token?>">
  <p><input type="submit" value="Save"></p>
    <div class="form-group">
      <label for="">Content Title</label>
      <input class="form-control" type="text" name="title" value="" required>
    </div>
    <div class="form-group">
      <label for="">Category</label>
      <select class="form-control" name="category" required>
        <?php cmsCatTree();?>
      </select>
    </div>
    <div class="form-group">
      <label for="">Status</label>
      <select class="form-control" name="status" required>
        <option value="0">Draft</option>
        <option value="1">Published</option>
      </select>
    </div>
    <div class="form-group">
      <label for="">Override default layout for this piece of content (optional)</label>
      <select class="form-control" name="layout">
        <option value="0">--Do not override layout--</option>
        <?php
        $layouts = $db->query("SELECT * FROM plg_cms_layouts ORDER BY title ASC")->results();

        foreach($layouts as $l){?>
          <option value="<?=$l->id?>"><?=$l->title?></option>
        <?php }
        ?>
      </select>
    </div>
    <div class="form-group">
      <label for="">Content Tags (Comma Separated)(Optional)</label>
      <input class="form-control" type="text" name="tags" value="">
    </div>
    <p><input type="submit" value="Create and Edit Content"></p>
</form>
