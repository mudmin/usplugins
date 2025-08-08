  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
if(!isset($settings->site_url)){
  $db->query("ALTER TABLE settings ADD COLUMN site_url varchar(255)");
  $settings = $db->query("SELECT * FROM settings")->first();
}
if($settings->site_url == ""){
  bold("PLEASE make sure to setup your site url (https://mydomain.com)");
}
$u1Q = $db->query("SELECT plg_ref FROM users WHERE id = 1");
if($u1Q->count() < 1){
  $nou1 = true;
}else{
  $nou1 = false;
  $u1 = $u1Q->first();
  if($u1->plg_ref == ""){
    $random = randomstring(6);
    $db->update('users',1,['plg_ref'=>$random]);
    $u1->plg_ref = $random;
  }
}

if(!empty($_POST['updateSettings'])){
  $fields = array(
    'only_refer'=>Input::get('only_refer'),
    'allow_un'=>Input::get('allow_un'),
    'show_acct'=>Input::get('show_acct'),
    'ref_string'=>Input::get('ref_string'),
    'ref_notice'=>Input::get('ref_notice'),
  );
$db->update('plg_refer_settings',1,$fields);
if($nou1 == false){
  $db->update('users',1,['plg_ref'=>Input::get('user1')]);
}
$link = Input::get('site_url');
$seven = strtolower(substr($link,0,7));
$eight = strtolower(substr($link,0,8));

if($seven != "http://" && $eight != "https://"){
  Redirect::to("admin.php?view=plugins_config&plugin=refer&err=Site URL");
}else{
  if(substr($link, -1)== "/"){
    $link = substr($link, 0, -1);
  }
  $db->update('settings',1,["site_url"=>$link]);
}
Redirect::to('admin.php?view=plugins_config&plugin=refer&err=Settings+saved');
}

 $token = Token::generate();
 $refSettings = $db->query("SELECT * FROM plg_refer_settings")->first();

 ?>
<div class="content mt-3">
  <div class="row">
    <div class="col-12">
      <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
      <h1>Configure the Referral Plugin!</h1>
    </div>
  </div>
 		<div class="row">
 			<div class="col-12">
        <br>
          <form class="" action="" method="post">
            <input type="hidden" name="csrf" value="<?=$token?>" />
            <div class="form-group">
              <label for="">Enter the string you want shown on your join form</label>
              <input type="text" name="ref_string" value="<?=$refSettings->ref_string?>" class="form-control">
            </div>
            <div class="form-group">
              <label for="">Enter the string you want to show to let people know they must have a valid code</label>
              <input type="text" name="ref_notice" value="<?=$refSettings->ref_notice?>" class="form-control">
            </div>
            <div class="form-group">
              <label for="">Your site's home url (https://mydomain.com)</label>
              <input type="text" name="site_url" value="<?=$settings->site_url?>" class="form-control">
            </div>
            <?php if($nou1 == true){?>
              <div class="form-group">
                <label for="">Since you don't have a user with an id of 1, you cannot use the single referral link feature</label>
              </div>
            <?php }else{ ?>
            <div class="form-group">
              <label for="">If you only want to use one referral link for the whole site, you can use the link
              of user #1, whose code is...</label>
              <input type="text" name="user1" value="<?=$u1->plg_ref?>" class="form-control">
              <strong>Your referral link is: <font color="red"><?=$settings->site_url?>/users/join.php?ref=<?=$u1->plg_ref?></font></strong>
            </div>
          <?php } ?>
            <div class="form-group">
              <label for="">Allow ONLY registrations with valid referrals?</label>
              <select class="form-control" name="only_refer">
                <option <?php if($refSettings->only_refer== 0) {echo "selected='selected'";}?> value="0">No</option>
                <option <?php if($refSettings->only_refer== 1) {echo "selected='selected'";}?> value="1">Yes</option>
              </select>
            </div>

            <div class="form-group">
              <label for="">Allow referals by username?</label>
              <select class="form-control" name="allow_un">
                <option <?php if($refSettings->allow_un== 0) {echo "selected='selected'";}?> value="0">No</option>
                <option <?php if($refSettings->allow_un== 1) {echo "selected='selected'";}?> value="1">Yes</option>
              </select>
            </div>

            <div class="form-group">
              <label for="">Show referral link on account page?</label>
              <select class="form-control" name="show_acct">
                <option <?php if($refSettings->show_acct== 0) {echo "selected='selected'";}?> value="0">No</option>
                <option <?php if($refSettings->show_acct== 1) {echo "selected='selected'";}?> value="1">Yes</option>
              </select>
            </div>
            <div class="form-group">
              <input type="submit" name="updateSettings" value="Update Settings" class="btn btn-primary">
            </div>
          </form>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
    <div class="row">
      <div class="col-12 col-sm-6">
        <h4>Last 20 Bad Referrals</h4>
        <?php $badRef = $db->query("SELECT * FROM logs WHERE logtype = 'bad_refer' ORDER BY id DESC LIMIT 20")->results();?>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Date</th><th>Details</th><th>IP</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($badRef as $b){?>
              <tr>
                <td><?=$b->logdate?></td>
                <td><?=$b->lognote?></td>
                <td><?=$b->ip?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      <div class="col-12 col-sm-6">
        <h4>Last 20 Good Referrals</h4>
        <?php $goodRef = $db->query("SELECT * FROM logs WHERE logtype = 'good_refer' ORDER BY id DESC LIMIT 20")->results();?>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Date</th><th>New User</th><th>Ref By</th><th>IP</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($goodRef as $b){?>
              <tr>
                <td><?=$b->logdate?></td>
                <td><?php echouser($b->user_id);?></td>
                <td><?php echouser($b->lognote);?></td>
                <td><?=$b->ip?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
<div class="row">
  <div class="col-12">
    <strong>How do I do something when a referral is successful?</strong><br>
    Create a file called usersc/plugins/refer/success_script.php
    This script will ONLY be called on a successful referral, not all new
    registrations in the event that you do not have "Allow ONLY registrations with valid referrals" set to yes.
    This is a great place if you want to give the person who referred the new user points or something like that.

    <br><br><strong>What if I automatically want to create a referral link when anyone joins?</strong><br>
    In usersc/scripts/during_user_creation.php add the line
    $db->update("users",$theNewId,['plg_ref'=>uniqid()]);

    <br><br><strong>What do I need to know about usernames in referral links?</strong><br>
    Technically having a username as a referral link COULD make it easier for someone to hack your site.  In order
    for someone to "brute force" login your site (try every combination) they need to know a username and a password.
    By seeing if a referral code is false, they could figure out if a username is valid on your site and then they
    only need to worry about the password.  Due to UserSpice's password strength and intentionally slow password
    decryption, UserSpice is VERY resistant to this type of attack, but you should understand the risks.

  </div>

</div>
