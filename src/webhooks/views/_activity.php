<?php
if(count(get_included_files()) ==1) die();
?>
<div class="row">
  <div class="col-12">
    <h3>Webhook Activity Log</h3>
    <p>If you see an abusive IP, you may want to consider going to the <a href="admin.php?view=ip">UserSpice IP Manager</a> and blacklisting it. </p>
    <table class="table table-hover table-striped">
      <thead>
        <tr>
          <th>Hook #</th>
          <th>IP</th>
          <th>Timestamp</th>
          <th>Subject</th>
          <th>Log</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($db->query("SELECT * from plg_webhook_activity_logs ORDER BY id DESC LIMIT 1000")->results() as $l){ ?>
          <tr>
            <td><?=$l->id?></td>
            <td><?=$l->ip?></td>
            <td><?=$l->ts?></td>
            <td><?=$l->subject?></td>
            <td><?=$l->log?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
