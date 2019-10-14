<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$uc = ucfirst(pointsNameReturn());
global $user;
$pntSettings = $db->query("SELECT * FROM plg_points_settings")->first();

if($pntSettings->show_trans_acct == 1){ ?>

  <h4><?=$uc?> Transactions</h4>
  <?php $trans = $db->query("SELECT * FROM plg_points_trans WHERE (trans_from = ? OR trans_to = ?) ORDER BY id DESC",[$user->data()->id,$user->data()->id])->results();
  ?>
  <table class="table table-striped" id="paginatePoints">
    <thead>
      <tr>
        <th>Date</th><th><?=$uc?></th><th>From</th><th>To</th><th>Reason</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($trans as $b){?>
        <tr>
          <td><?=$b->ts?></td>
          <td><?=$b->points?></td>
          <td><?php echouser($b->trans_from);?></td>
          <td><?php echouser($b->trans_to);?></td>
          <td><?=$b->reason?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
  <script type="text/javascript" src="js/pagination/datatables.min.js"></script>
  <script>
  $(document).ready(function() {
    $('#paginatePoints').DataTable({"pageLength": 5,"aLengthMenu": [[5, 10, 50, -1], [5, 10, 50, "All"]], "aaSorting": []});
  } );
</script>
<?php } ?>
