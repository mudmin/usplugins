<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

  <?php
  include "plugin_info.php";
  pluginActive($plugin_name);
  require_once $abs_us_root.$us_url_root.'usersc/plugins/cronpro/vendor/autoload.php';
  $directory = $abs_us_root.$us_url_root."usersc/plugins/cronpro/assets/";
  $cronFiles = glob($directory . "*.php");
  $upcomingQ = $db->query("SELECT * FROM plg_cronpro_single WHERE complete != 1 ORDER BY go_time");
  $upcomingC = $upcomingQ->count();

  if(!empty($_POST)){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
    if(!empty($_POST['createSingle']) || !empty($_POST['createRecurring']) ){
    $cron_name = Input::get('cron_name');
    $calltype = Input::get('call_type');
    if($calltype == "file"){
      $calldata = $_POST['calldatafile'];
    }else{
      $calldata = $_POST['calldatatext'];
    }
    if($calldata == "" && !isset($_POST['delCron'])){
      Redirect::to("admin.php?view=plugins_config&plugin=cronpro&err=Invalid file or query");
    }
  }

    if(!empty($_POST['createRecurring'])){
      $frequency = Input::get('frequency');

      if(substr($frequency,0,1) != "@"){
        $frequency = $_POST['frequency-manual'];
      }
      $cron = new Cron\CronExpression($frequency);
      if($cron->isValidExpression($frequency)){
        $fields = [
          'cron_name'=>$cron_name,
          'schedule'=>$frequency,
          'calltype'=>$calltype,
          'calldata'=>$calldata,
        ];
        $db->insert("plg_cronpro_recurring",$fields);
        $id = $db->lastId();
        $next = $cron->getNextRunDate(date("Y-m-d H:i:s"))->format('Y-m-d H:i:s');
        $fields = [
          'cron_name'=>$cron_name,
          'recurring'=>$id,
          'go_time'=>$next,
          'calltype'=>$calltype,
          'calldata'=>$calldata,
        ];
        $db->insert("plg_cronpro_single",$fields);
        Redirect::to("admin.php?view=plugins_config&plugin=cronpro&err=Recurring cron job created. Next run: $next");
      }else{
        Redirect::to("admin.php?view=plugins_config&plugin=cronpro&err=Invalid cron frequency");
      }
    }

    if(!empty($_POST['createSingle'])){
      $fields = [
        'cron_name'=>$cron_name,
        'recurring'=>0,
        'go_time'=>Input::get('go_time'),
        'calltype'=>$calltype,
        'calldata'=>$calldata,
      ];
      $db->insert("plg_cronpro_single",$fields);
      Redirect::to("admin.php?view=plugins_config&plugin=cronpro&err=Single cron job created. Next run: ".Input::get('go_time'));
    }

    if(!empty($_POST['delCron'])){
      $grabQ = $db->query("SELECT * FROM plg_cronpro_single WHERE id = ?",[Input::get('cronToDelete')]);
      $grabC = $grabQ->count();
      if($grabC > 0){
        $grab = $grabQ->first();
        $db->query("DELETE FROM plg_cronpro_single WHERE id = ?",[Input::get('cronToDelete')]);
        if($grab->recurring > 0){
          $reQ = $db->query("SELECT * FROM plg_cronpro_recurring WHERE id = ?",[$grab->recurring]);
          $reC = $reQ->count();
          if($reC > 0){
            $db->query("DELETE FROM plg_cronpro_recurring WHERE id = ?",[$grab->recurring]);
          }
        }
      }else{
        Redirect::to("admin.php?view=plugins_config&plugin=cronpro&err=Job not found");
      }
      Redirect::to("admin.php?view=plugins_config&plugin=cronpro&err=Job deleted");
  }
}
  $token = Token::generate();
  ?>
  <div class="content mt-3">
    <div class="row">
      <div class="col-sm-12">
        <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
        <h1>CronPro</h1>
      </div> <!-- /.col -->
    </div> <!-- /.row -->
    <br>
    <div class="row">
      <div class="col-6">
        <h3>Create a new recurring cron job</h3>
        <br>
        <form class="" action="" method="post">
          <input type="hidden" name="csrf" value="<?=$token?>">

          <div class="form-group">
            <label for="">Give this job a name</label>
            <input type="text" name="cron_name" value="" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="">Cron Frequency</label>
            <select class="form-control" name="frequency" id="freqdrop" required>
              <option value="" disabled selected="selected">--Select Frequency--</option>
              <option value="@hourly">Hourly -  Run once an hour, first minute - 0 * * * *</option>
              <option value="@daily">Daily - Run once a day, midnight - 0 0 * * *</option>
              <option value="@weekly">Weekly - Run once a week, midnight on Sun - 0 0 * * 0</option>
              <option value="@monthly">Monthly - Run once a month, midnight, first of month - 0 0 1 * *</option>
              <option value="@yearly">Yearly - Run once a year, midnight, Jan. 1 - 0 0 1 1 *</option>
              <option value="custom">Custom - Specify in Text</option>
            </select>
          </div>

          <div class="form-group" style="display:none;" id="freqtextblock">
            <label for="">Cron Frequency (if specifying a custom frequency)</label>
            <p>Use <a target="_blank" href="http://www.crontabgenerator.com/every-3-hours"><b>this tool</b></a> to make your cron schedule. We just want the simple "5 segments" to paste in here.</p>
            <input type="text" name="frequency-manual" class="form-control" value="" id="freqtext">
          </div>

          <div class="form-group">
            <label for="">What type of call is this?</label>
            <select class="form-control" name="call_type" id="call_type"  required>
              <option value="" disabled selected='selected'>--Select Type--</option>
              <option value="db">Raw Database Query</option>
              <option value="file">Execute a PHP file</option>
            </select>
          </div>

          <div class="form-group" id="query" style="display:none;">
            <label for="">Enter your raw DB query here</label>
            <p>Example:<br>  <b>UPDATE settings SET site_offline = 0 WHERE id = 1</b> </p>
            <textarea name="calldatatext" id="calldatatext" rows="3" class="form-control"></textarea>
          </div>

          <div class="form-group" id="file" style="display:none;">
            <label for="">Select the PHP file you want to execute</label>
            <p>Store a file in usersc/plugins/cronpro/assets/ to have it automatically added.
              Be sure to view the template to make sure you have the formatting right.</p>
              <select class="form-control" name="calldatafile">
                <option value="" disabled selected="selected">--Choose File--</option>
                <?php foreach($cronFiles as $c){
                  $c = str_replace($abs_us_root.$us_url_root."usersc/plugins/cronpro/assets/","",$c);
                  ?>
                  <option value="<?=$c?>"><?=$c?></option>
                <?php } ?>
              </select>
            </div>
            <div class="form-group">
              <input type="submit" name="createRecurring" value="Create Recurring" class="btn btn-primary">
            </div>
          </form>
        </div>

        <div class="col-6">
          <h3>Create a new one time cron job</h3>
          <br>
          <form class="" action="" method="post">
            <input type="hidden" name="csrf" value="<?=$token?>">

            <div class="form-group">
              <label for="">Give this job a name</label>
              <input type="text" name="cron_name" value="" class="form-control" required>
            </div>

            <div class="form-group">
              <label for="">When do you want this task to trigger?</label>
              <input type="datetime-local" name="go_time" value="<?=date("Y-m-d\TH:i:00")?>" class="form-control">
            </div>
            <div class="form-group">
              <label for="">What type of call is this?</label>
              <select class="form-control" name="call_type" id="call_type2"  required>
                <option value="" disabled selected='selected'>--Select Type--</option>
                <option value="db">Raw Database Query</option>
                <option value="file">Execute a PHP file</option>
              </select>
            </div>

            <div class="form-group" id="query2" style="display:none;">
              <label for="">Enter your raw DB query here</label>
              <p>Example:<br> <b>UPDATE settings SET site_offline = 0 WHERE id = 1</b> </p>
              <textarea name="calldatatext" id="calldatatext2" rows="3" class="form-control"></textarea>
            </div>

            <div class="form-group" id="file2" style="display:none;">
              <label for="">Select the PHP file you want to execute</label>
              <p>Store a file in usersc/plugins/cronpro/assets/ to have it automatically added.
                Be sure to view the template to make sure you have the formatting right.</p>
                <select class="form-control" name="calldatafile">
                  <option value="" disabled selected="selected">--Choose File--</option>
                  <?php foreach($cronFiles as $c){
                    $c = str_replace($abs_us_root.$us_url_root."usersc/plugins/cronpro/assets/","",$c);
                    ?>
                    <option value="<?=$c?>"><?=$c?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="form-group">
                <input type="submit" name="createSingle" value="Create Single" class="btn btn-primary">
              </div>
            </form>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            Upcoming Jobs
            <?php
            if($upcomingC < 1){
              echo "<br>No jobs are scheduled";
            }else{
              $upcoming = $upcomingQ->results();
            ?>
                <table class="table table-striped table-hover">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Recurring</th>
                      <th>Call Type</th>
                      <th>Call Data</th>
                      <th>Next Fire</th>
                      <th>Delete</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($upcoming as $u){ ?>
                      <tr>
                        <td><?=$u->cron_name?></td>
                        <td>
                          <?php if(!is_numeric($u->recurring) || $u->recurring < 1){
                            echo "No";
                          }else{
                            $findQ = $db->query("SELECT * FROM plg_cronpro_recurring WHERE id = ?",[$u->recurring]);
                            $findC = $findQ->count();
                            if($findC < 1){
                              echo "Yes";
                            }else{
                              $find = $findQ->first();
                              echo $find->schedule;
                            }
                          }
                          ?>
                        </td>
                        <td><?=$u->calltype?></td>
                        <td><textarea class="form-control" rows="1" readonly><?=$u->calldata?></textarea></td>
                        <td><?=$u->go_time?></td>
                        <td>
                          <form class="" action="" method="post" onsubmit="return confirm('This cannot be undone! Note that deleting a recurring job will delete the job and prevent it from ever happening again.');">
                            <input type="hidden" name="csrf" value="<?=$token?>">
                            <input type="hidden" name="cronToDelete" value="<?=$u->id?>">
                            <input type="submit" name="delCron" value="Delete" class="btn btn-danger">
                          </form>
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              <?php } ?>
            </div>
          </div>

        <div class="row">
          <div class="col-12">
            <h3>Documentation</h3>
            <br>
            <h4>How it Works</h4>
            <p>The CronPro plugin is designed to be a "target" for your server's cron manager.  In other words, you setup your crontab on your server to hit the "target.php" file every so many minutes, hours, days and when it does, the CronPro plugin will go to the database and see what jobs are waiting for it. If there are any jobs, it will loop through and execute those jobs and mark them as "done". If a job is a recurring job, the CronPro plugin will automatically create a new one time job at the proper time for that task. It requires PHP 7.2 or later to work.</p>

            <h4>Understanding Resolution</h4>
            <p>There is nothing magically in PHP that will make this thing work.  You need to setup a cron task on your server and how often you do that, matters. Let's say you have a job in CronPro set to run hourly, but you only set your server's cron manager to run daily.  CronPro will NOT trigger your script 24 times when it finally gets the opportunity.  It will trigger it ONE time and then immediately setup a new task 1 hour into the future.</p>

            <p>In a perfect world, if you want all of your cron jobs to run near the top of the hour, I recommend setting CronPro to run the tasks hourly and then set your server's cron manager to "1 * * * *" so the requests will be made 1 minute after the hour.</p>

            <h4>Types of CronPro Tasks</h4>
            <p>CronPro currently supports two "schedules" of cron tasks.  It can do one time tasks which are great if you want to say run a script to take the site offline and do some server upgrades or automatically launch an event.  It can also do recurring schedules which can be hourly, monthly etc.  Recurring schedules can also use a simple form of the cron syntax with numbers and *.  If you try to create a recurring task with an invalid cron syntax, it will throw an error.</p>

            <p>CronPro also has two different types of cron jobs: file based and simple DB commands.  If you would like to create a full php script that is executed via CronPro, drop that file in usersc/plugins/cronpro/assets.  Be sure to check out the template file to get an idea of the things that need to be at the top of that file. It has access to all UserSpice plugins and functions. Database queries can be complex or a single line.  Just paste them in the text box. </p>

            <h4>Triggering target.php</h4>
            <p>When you setup your server's cron manager, you will need to point it to "hit" the target.php file in this plugin.  The ideal way to do that is with curl.  An example to hit it 1 minute after the hour would be...</p>

            <p><b>1 * * * * curl https://yourdomain.com/usersc/plugins/cronpro/target.php</b></p>

            <p>Note that the first time you hit this, IT WILL MOST LIKELY FAIL! The reason for this is that you do not want any random individual to trigger your cron jobs.  So after the task was supposed to run, visit <a href="admin.php?view=logs"><b>the system logs</b></a> and look for a message that says Cron was denied from a certain IP.  Copy that IP address and paste it in the "Only allow cron jobs from the following IP" on <a href="admin.php?view=general"><b>this screen</b></a>. Everything should work fine after that. In order to not flood the logs, the parser will not log every successful hit of the target.php file. If you want that to happen for testing purposes, fire your curl off to target.php?diag=true .
            </p>
            <p>If you want to just play with this without setting up the cron manager on your server, just use your browser to navigate to https://yourdomain.com/usersc/plugins/cronpro/target.php and follow the same procedure for capturing that IP as in the paragraph above.</p>
          </div>
        </div>
      </div>

        <script type="text/javascript">
        $(document).ready(function() {
          $( "#freqdrop" ).change(function() {
            var value = $(this).val();
            if(value != "custom"){
              $("#freqtext").val("");
              $("#freqtextblock").hide();
            }else{
              $("#freqtextblock").show();
            }
          });

          $( "#call_type" ).change(function() {
            var value = $(this).val();
            console.log(value);
            if(value == "db"){
              $("#query").val("");
              $("#query").show();
              $("#file").val("");
              $("#file").hide();
            }else{
              $("#query").val("");
              $("#query").hide();
              $("#file").val("");
              $("#file").show();
            }
          });

          $( "#call_type2" ).change(function() {
            var value = $(this).val();
            console.log(value);
            if(value == "db"){
              $("#query2").val("");
              $("#query2").show();
              $("#file2").val("");
              $("#file2").hide();
            }else{
              $("#query2").val("");
              $("#query2").hide();
              $("#file2").val("");
              $("#file2").show();
            }
          });


        });
      </script>
