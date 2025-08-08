<?php
if(count(get_included_files()) ==1) die();
$log = Input::get('log');
?>
<div class="row">
  <div class="col-12">
    <h3>Hook Logs for Hook #<?=$log?></h3>
    <br>
    <table class="table table-striped table-hover paginate">
      <thead>
        <tr>
          <th width="20em">Timestamp</th>
          <th width="10em">IP</th>
          <th>Log</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($db->query("SELECT * FROM plg_webhook_data_logs WHERE hook = ? ORDER BY id DESC",[$log])->results() as $l){ ?>
          <tr>
            <td><?=$l->ts?></td>
            <td><?=$l->ip?></td>
            <td>
              <textarea rows="1" class="form-control" disabled><?=$l->log?></textarea>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
