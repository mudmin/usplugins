<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$dlogs = $db->query("SELECT * FROM plg_download_logs ORDER BY id DESC")->results();

?>

<div class="row">
  <div class="col-sm-12">
    <br>
    <h3>Download Logs</h3>
    <table class="table table-striped paginate">
      <thead>
        <tr>
          <th>Location</th>
          <th>Link Type</th>
          <th>Successful?</th>
          <th>Message</th>
          <th>User</th>
          <th>Timestamp</th>
          <th>IP</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($dlogs as $l){?>
          <tr>
            <td>
              <?php if($l->linkmode == 1){
                echo getFileLocationFromDLLink($l->link);
              }elseif($l->linkmode == 2){
              echo getFileLocationFromDLCustomLink($l->link);
              }
              ?>
            </td>
            <td>
              <?php if($l->linkmode == 2){
              echo "Custom Link";
            }else{
              echo "Direct Link";
            }
            ?>
            </td>
            <td><?=bin($l->success);?></td>
            <td><?=$l->message?></td>
            <td>
              <?php if(is_numeric($l->user) && $l->user > 0) {?>
              <a href="admin.php?view=user&id=<?=$l->user?>">
              <?=echouser($l->user)." ($l->user)";?></a>
            <?php }else{ ?>
              <?=echouser($l->user)." ($l->user)";?>
            <?php } ?>
            </td>
            <td><?=$l->ts?></td>
            <td><?=$l->ip?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
    </div> <!-- /.col -->
</div> <!-- /.row -->
