  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_reports'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
   // Redirect::to('admin.php?err=I+agree!!!');
 }
 $token = Token::generate();
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Configure the Reports Plugin!</h1>
      </div> <!-- /.col -->
 		</div> <!-- /.row -->
    <div class="row">
      <div class="col-12 col-sm-8">
        <h3>Documentation</h3>
        <p>Creating multi-tabbed Excel spreadsheets with your data is as simple as creating an array and giving it some heading titles. In order to create a new custom report, simply make a new report file in the <em>usersc/plugins/reports/reportfiles</em> folder and then call your report from a link (see the list to the right).  You can right click the Generate Report button to grab the link to your report so you can use it somewhere else in your project.</p>
        <p>The reportfiles folder already contains one example to walk you through how to setup your data.  You decide what the spreadsheet looks like and who can generate a report. This is based on <a href="https://phpspreadsheet.readthedocs.io/en/latest/">PhpSpreadsheet</a> and there are an absolutely insane number of options with this library.</p>
        <p>Activating this plugin has basically no impact on site performance since nothing is loaded. There are no functions etc.  You can just include and use this class as you see fit.</p>
        <p>If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!</p>

      </div>
      <div class="col-12 col-sm-4">
        <h3>Your reports</h3>
        <?php
        $files = glob($abs_us_root.$us_url_root."usersc/plugins/reports/reportfiles/*.php");
        ?>
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Report</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($files as $f){
              $f = str_replace($abs_us_root.$us_url_root."usersc/plugins/reports/reportfiles/","",$f);
              $r = str_replace(".php","",$f);
              ?>
              <tr>
                <td><?=$f?></td>
                <td>
                  <a href="<?=$us_url_root?>usersc/plugins/reports/reports.php?report=<?=$r?>" class="btn btn-primary">Generate Report</a>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>

      </div>
    </div>
