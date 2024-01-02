  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

 <?php
 include "plugin_info.php";
 pluginActive($plugin_name);
 if(!empty($_POST['changeAct'])){

   $a =Input::get('gdpract');
   if($a == 0 || $a == 1){
     $db->update('settings',1,['gdpract'=>$a]);
   }
   $settings = $db->query("SELECT * FROM settings")->first();
 }

 if(!empty($_POST['newGDPR'])){
   $date = date("Y-m-d H:i:s");
   $fields = array(
       'popup' => htmlentities($_POST['popup']),
       'detail' => htmlentities($_POST['detail']),
       'confirm' => htmlentities($_POST['confirm']),
       'delete' => Input::get('delete'),
       'btn_accept' => Input::get('btn_accept'),
       'btn_more' => Input::get('btn_more'),
       'btn_delete' => Input::get('btn_delete'),
       'btn_confirm_no' => Input::get('btn_confirm_no'),
       'btn_confirm_yes' => Input::get('btn_confirm_yes'),
       'created_on' =>$date,
   );
   $db->insert('us_gdpr',$fields);
   $id = $db->lastId();
   $fields = array(
     'gdprver'=>$id
   );
   $db->update('settings',1,$fields);
 }

$last = $db->query("SELECT * FROM us_gdpr ORDER BY id DESC LIMIT 1")->first();
$token = Token::generate();
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Configure the GDPR Plugin!</h1>
          <form class="" action="admin.php?view=plugins_config&plugin=gdpr" method="post">
            <input type="hidden" name="csrf" value="<?=$token?>" />

            Please note: Every update of the GDPR/Cookie Policy creates a new version of the policy and makes each user re-acknowledge. For this reason, you can disable the notice itself
            below without disabling the whole plugin. Your users will not see the notice until you enable the notice. DO NOT edit the notices in the database itself. Only use this form.
            If you are using the default "More info", be sure to edit it according to your needs and put your contact info at the bottom.
            <br><br>
            <label for="">The GDPR Notice is Currently</label>
            <select class="" name="gdpract">
              <option value="0" <?php if($settings->gdpract == 0){echo "selected";}?> >Not Active</option>
              <option value="1" <?php if($settings->gdpract == 1){echo "selected";}?> >Active</option>
            </select>
            <input type="submit" name="changeAct" value="Update">
          </form>
        <br><strong>This policy was created on <?=$last->created_on?></strong><br>
          <form class="" action="admin.php?view=plugins_config&plugin=gdpr" method="post">
            <input type="hidden" name="csrf" value="<?=$token?>" />
            <table>
              <thead>
                <tr>
                  <th>Button</th><th>Message</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th>Accept</th>
                  <th>
                    <input type="text" name="btn_accept" value="<?=$last->btn_accept?>" required>
                  </th>
                </tr>

                <tr>
                  <th>More Information</th>
                  <th>
                    <input type="text" name="btn_more" value="<?=$last->btn_more?>" required>
                  </th>
                </tr>

                <tr>
                  <th>Delete Account</th>
                  <th>
                    <input type="text" name="btn_delete" value="<?=$last->btn_delete?>" required>
                  </th>
                </tr>

                <tr>
                  <th>Change Mind on Delete</th>
                  <th>
                    <input type="text" name="btn_confirm_no" value="<?=$last->btn_confirm_no?>" required>
                  </th>
                </tr>

                <tr>
                  <th>Yes, Delete Account</th>
                  <th>
                    <input type="text" name="btn_confirm_yes" value="<?=$last->btn_confirm_yes?>" required>
                  </th>
                </tr>
              </tbody>
            </table>
            <br><label>GDPR/Cookie Notice</label><br>
            <textarea name="popup" class="tiny" rows="8" cols=100><?=$last->popup?></textarea>

            <br><label>More Details Notice</label><br>
            <textarea name="detail" class="tiny" rows="8" cols=100><?=$last->detail?></textarea>

            <br><label>Confirmation Message Before Deleting Account</label><br>
            <textarea name="confirm" class="tiny" rows="3" cols=100><?=$last->confirm?></textarea>

            <br><label>Offer the user the option to delete their account?</label><br>
            <select class="" name="delete">
              <option value="0"  <?php if($last->delete == 0){echo "selected";}?>>No</option>
              <option value="1"  <?php if($last->delete == 1){echo "selected";}?>>Yes</option>
            </select>
            <br><br>
            <input type="submit" name="newGDPR" value="Update Policy" class="btn btn-success">
          </form>
          If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.19/dist/summernote.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.19/dist/summernote.min.js"></script>
    <script>
    $(document).ready(function(){
      $('.tiny').summernote();
    });
    </script>
