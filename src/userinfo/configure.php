  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST)){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
  $fields = array(
    'fname'=>Input::get('fname'),
    'lname'=>Input::get('lname'),
    'uname'=>Input::get('uname'),
    'domain'=>Input::get('domain'),
  );
  $db->update('plg_userinfo',1,$fields);
  Redirect::to('admin.php?view=plugins_config&plugin=userinfo');
 }
 $token = Token::generate();
 $e = $db->query("SELECT * FROM plg_userinfo")->first();
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">

          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
        <h3>Add Additional User Fields</h3>
          <p>If you would like to add additional user form fields that will show up on the join, edit user, and new user (admin)
          pages, please install the Official UserSpice Form Builder from Spice Shaker or manually from <a href="https://github.com/mudmin/usplugins/raw/master/zip/formbuilder.zip">here</a>.</p>
          <p>There will be a form called "users" in that plugin that will allow you to add your fields.</p>
         <h3>Hide Standard User Fields</h3>
         <p>Due to all the validations in UserSpice, we cannot simply "delete" user information fields,
           however we can hide them.  Please note that hiding a field will result in "dummy" information being
           put in those fields in the database for compatibility reasons. We will explain that below.
         </p>
         <form class="" action="" method="post">
           <input type="hidden" name="csrf" value="<?=$token?>">

           <label for="">Hide First Name (Username/Email will be used instead)</label>
           <select class="form-control" name="fname">
             <option value="0" <?php if($e->fname == 0){echo "selected";}?>>No</option>
             <option value="1" <?php if($e->fname == 1){echo "selected";}?>>Yes</option>
           </select>

           <label for="">Hide Last Name (Username/Email will be used instead)</label>
           <select class="form-control" name="lname">
             <option value="0" <?php if($e->lname == 0){echo "selected";}?>>No</option>
             <option value="1" <?php if($e->lname == 1){echo "selected";}?>>Yes</option>
           </select>

           <label for="">Username/Email (Note that hiding email will prevent account confirmation/password resets. Email becomes username or Username becomes email in the db.)</label>
           <select class="form-control" name="uname">
             <option value="0" <?php if($e->uname == 0){echo "selected";}?>>Ask for Username and Email</option>
             <option value="1" <?php if($e->uname == 1){echo "selected";}?>>Ask for only Username</option>
             <option value="2" <?php if($e->uname == 2){echo "selected";}?>>Ask for only Email</option>
           </select>
           <label for="">Domain for dummy email addresses. Should either be a domain you control or userspice.com. Do not use an @ symbol.</label>
           <input type="text" name="domain" value="<?=$e->domain?>" class="form-control">
         <input type="submit" name="submit" value="Save">
         </form>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
