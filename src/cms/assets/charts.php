<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$action = Input::get('action');
if($action == ""){
  $action = "content";
}

if($action == "content"){
  if(!empty($_POST['deleteme'])){$db->query("DELETE FROM plg_cms_content WHERE id = ?",[Input::get('deleteme')]);}
  $data = $db->query("SELECT id,title,slug FROM plg_cms_content")->results();
}elseif($action == "layout"){
  if(!empty($_POST['deleteme'])){$db->query("DELETE FROM plg_cms_layouts WHERE id = ?",[Input::get('deleteme')]);}
  $data = $db->query("SELECT id,title FROM plg_cms_layouts")->results();
}elseif($action == "widget"){
  if(!empty($_POST['deleteme'])){$db->query("DELETE FROM plg_cms_widgets WHERE id = ?",[Input::get('deleteme')]);}
  $data = $db->query("SELECT id,title FROM plg_cms_widgets")->results();
}



?>

<table class="table table-striped" id="paginate">
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
        <?php if($action == "content"){?>
        <td>
          <button class="btn btn-info" onclick="window.location='<?=$us_url_root?><?=$plg_settings->parser?>?c=<?=$d->slug?>';">
            View</button>
        </td>
      <?php } ?>
        <td>
          <form class="" action="" method="post" onsubmit="return confirm('Do you really want to delete this?');">
            <input type="hidden" name="csrf" value="<?=$token?>">
            <input type="hidden" name="deleteme" value="<?=$d->id?>">
            <input type="submit" name="submit" value="Delete" class="btn btn-danger">
          </form>
        </td>
      </tr>

    <?php } ?>
  </tbody>
</table>
<script type="text/javascript" src="<?=$us_url_root?>users/js/pagination/datatables.min.js"></script>
<script>

$(document).ready(function () {
   $('#paginate').DataTable({"pageLength": 25,"stateSave": true,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, 250, 500]], "aaSorting": []});
  });
</script>
