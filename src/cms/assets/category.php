<?php if(count(get_included_files()) ==1) die("died"); //Direct Access Not Permitted
$action = Input::get('action');
$id = Input::get('id');
if($action == 'edit'){
  $e = true;

  $existQ = $db->query("SELECT * FROM plg_cms_categories WHERE id = ?",[$id]);
  $existC = $existQ->count();
  if($existC < 1){
    Redirect::to("admin.php?view=plugins_config&plugin=cms&method=category&err=Category+not+found");
  }else{
    $exist = $existQ->first();
  }
}else{
  $e = false;
}

if($action == ""){ //default table
$cats = $db->query("SELECT * FROM plg_cms_categories ORDER BY subcat_of,category")->results();
?>
<br>

<a class="btn btn-dark" href="admin.php?view=plugins_config&plugin=cms&method=category&action=new">New Category</a><br>
Permission 0 is wide open to the public and overrides the other permissions.
Permission 2 (Admin) can see all content. Permissions do not "cascade," meaning a subcategory does not automatically
inherit the permissions of the parent category.
<table class="table table-striped paginate">
  <thead>
    <tr>
      <th>id</th>
      <th>Category</th>
      <th>Subcategory Of</th>
      <th>Permissions</th>
      <th>Edit</th>
      <th>Delete</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($cats as $c){?>
      <tr>
        <td><?=$c->id?></td>
        <td><?=$c->category?></td>
        <td><?php echoCMSCat($c->subcat_of);?></td>
        <td><?=$c->perms?></td>
        <td><a class="btn btn-success" href="admin.php?view=plugins_config&plugin=cms&method=category&action=edit&id=<?=$c->id?>">Edit</a></td>
        <td><a class="btn btn-danger" href="admin.php?view=plugins_config&plugin=cms&method=category&action=delete&id=<?=$c->id?>">Delete</a></td>
      </tr>
    <?php } ?>
  </tbody>
</table>
<?php }//end no action

if($action == "new" || $e){
  if(!empty($_POST)){
    $perms = Input::get('perms');
    $perms = implode(",",$perms);

    $fields = array(
      'category'=>Input::get('category'),
      'subcat_of'=>Input::get('subcat_of'),
      'perms'=>$perms,
    );
    if(!empty($_POST['createCat']) && !$e){
      $db->insert('plg_cms_categories',$fields);
      Redirect::to('admin.php?view=plugins_config&plugin=cms&method=category&err=Category+created');
    }elseif(!empty($_POST['createCat']) && $e){
      $db->update('plg_cms_categories',$id,$fields);
      Redirect::to('admin.php?view=plugins_config&plugin=cms&method=category&err=Category+edited');
    }
  }
  ?>
  <form class="" action="" method="post">
    <input type="hidden" name="csrf" value="<?=$token?>">
    <div class="form-group">
      <label for="">Category Name</label>
      <input class="form-control" type="text" name="category" value="<?php if($e){echo $exist->category;}?>" required>
    </div>
    <div class="form-group">
      <label for="">Subcategory Of</label>
      <select class="form-control" name="subcat_of" required>
        <?php
        if($e){
          $selected = explode(",",$exist->perms);
          ?>
          <option value="<?=$exist->subcat_of?>"><?php echoCMSCat($exist->subcat_of);?></option>
        <?php }
        cmsCatTree();?>
      </select>
    </div>
    <div class="form-group">
      <label for="">Permissions</label><br>
      <div class="col-4">
        <input type="checkbox" name="perms[]" value="0"
        <?php if($e && in_array(0,$selected)){echo "checked";} ?>
        > Open to Public
      </div>
      <?php $perms = $db->query("SELECT * FROM permissions")->results();
      foreach($perms as $p){
        if($p->id == 2){continue;}
        ?>
        <div class="col-4">
          <input type="checkbox" name="perms[]" value="<?=$p->id?>"
          <?php if($e && in_array($p->id,$selected)){echo "checked";} ?>
          > <?=$p->name?>
        </div>
      <?php } ?>
    </div>
    <?php if($e){ ?>
      <input type="submit" name="createCat" value="Edit Category">
    <?php }else{ ?>
      <input type="submit" name="createCat" value="Create Category">
    <?php } ?>

  </form>
  <h3>Content in this category</h3>
  <?php   $data = $db->query("SELECT id,title,slug FROM plg_cms_content WHERE category = ?",[$id])->results();?>
  <table class="table table-striped">
    <thead>
      <th>ID</th><th>Name</th><th>Edit</th>
      <?php if($action == "content"){?>
      <th>View</th>
    <?php } ?>
      <th>Delete</th>
    </thead>
    <tbody>
      <?php
      foreach($data as $d){?>
        <tr>
          <td><?=$d->id?></td>
          <td><?=$d->title?></td>
          <td><button class="btn btn-success" onclick="window.location='<?=$us_url_root?>users/admin.php?view=plugins_config&plugin=cms&action=edit&method=<?=$action?>_edit&id=<?=$d->id?>';">
              Edit</button>
          </td>

          <td>
            <button class="btn btn-info" onclick="window.location='<?=$us_url_root?><?=$plg_settings->parser?>?c=<?=$d->slug?>';">
              View</button>
          </td>
        </tr>

      <?php } ?>
    </tbody>
  </table>

<?php } //end new/edit

if($action == "delete"){
  if(!empty($_POST['delAndTransfer'])){
    $tt = Input::get('transfer_to');
    $check = $db->query("SELECT id FROM plg_cms_categories WHERE id = ?",[$tt])->count();
    if($check > 0){
      $old = $db->query("SELECT * FROM plg_cms_content WHERE category = ?",[$id])->results();
      foreach($old as $o){
        $db->update('plg_cms_content',$o->id,['category'=>$tt]);

      }
      $db->query("DELETE FROM plg_cms_categories WHERE id = ?",[$id]);
      Redirect::to('admin.php?view=plugins_config&plugin=cms&method=category&err=Deleted');
    }else{
      Redirect::to('admin.php?view=plugins_config&plugin=cms&method=category&err=Transfer+category+not+found');
    }

  }
  ?>
  <form class="" action="" method="post">
    <input type="hidden" name="csrf" value="<?=$token?>">
    <div class="form-group">
      <label for="">Transfer all content from this category to the category below</label>
      <select class="form-control" name="transfer_to" required>
        <?php
        cmsCatTree();?>
      </select>
      <input type="submit" name="delAndTransfer" value="Delete and Transfer" >
    </div>
  </form>
  <?php

}
?>
<script type="text/javascript" src="<?=$us_url_root?>users/js/pagination/datatables.min.js"></script>
<script>

$(document).ready(function () {
   $('#paginate').DataTable({"pageLength": 25,"stateSave": true,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, 250, 500]], "aaSorting": []});
  });
