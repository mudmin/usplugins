<?php
//This must be wrapped in some sort of if statement if you want to make it not
//appear on every page.

//You can wrap it in whatever div you want to adjust the size and turn it into a more
//widgit-ish panel on an existing page.

//include with something like

// // // include $abs_us_root.$us_url_root."usersc/plugins/links/assets/mini_table.php";

if(pluginActive("links",true)){
$lsettings = $db->query("SELECT * FROM plg_links_settings WHERE id = 1")->first();
if($lsettings->non_admins_see_all == 1 || hasPerm([2],$user->data()->id)){
  $links = $db->query("SELECT * FROM plg_links")->results();
}else{
  $links = $db->query("SELECT * FROM plg_links WHERE user = ?",[$user->data()->id])->results();
}
?>
<table class="table table-striped paginate">
  <thead>
    <tr class="text-left">
      <th>Link</th>
      <th>Link Name</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($links as $l){?>
      <tr>
        <td>
          <button type="button" class="btn btn-primary" onclick="copyStringToClipboard('<?=generatePluginLink($l->id)?>');">Copy</button>
        </td>
        <td><?=$l->link_name?></td>
      </tr>
    <?php } ?>
  </tbody>
</table>
<script type="text/javascript" src="<?=$us_url_root?>users/js/pagination/datatables.min.js"></script>
<script>

$(document).ready(function () {
   $('.paginate').DataTable({"pageLength": 25,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, 250, 500]], "aaSorting": []});
  });

</script>

<script type="text/javascript">
function copyStringToClipboard (textToCopy) {
  navigator.clipboard.writeText(textToCopy)
}
</script>

<?php } //end if pluginActive
