<?php
// This is a user-facing page

require_once '../users/init.php';
if (!securePage($_SERVER['PHP_SELF'])){die();}
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
$hooks =  getMyHooks();
includeHook($hooks,'pre');
?>
<?php
//PHP Goes Here!
$validation = new Validate();
$userID = $user->data()->id;
$grav = get_gravatar(strtolower(trim($user->data()->email)));
$profileQ = $db->query("SELECT * FROM profiles WHERE user_id = ?",array($userID));
$thisProfile = $profileQ->first();
$id = $thisProfile->id;
//Uncomment out the 2 lines below to see what's available to you.
// dump($user);
// dump($thisProfile);

//Forms posted
if(!empty($_POST)) {
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    die('Token doesn\'t match!');
  }else {
    if ($thisProfile->bio != $_POST['bio']){
      $newBio = $_POST['bio'];
      $fields=array('bio'=>$newBio);
      $validation->check($_POST,array(
        'bio' => array(
          'display' => 'Bio',
          'required' => true
        )
      ));
      if($validation->passed()){
        $db->update('profiles',$id,$fields);
        Redirect::to($us_url_root.'users/profile.php?id='.$userID);
      }
    }
  }
}
?>

<div id="page-wrapper">

  <div class="container">

    <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="well">
      <div class="row">
        <div class="col-12 col-md-2">
          <p>
            <?php
            if(pluginActive('profile_pic',true) && $user->data()->profile_pic != ''){ ?>
              <img src="<?=$us_url_root?>usersc/plugins/profile_pic/files/<?=$user->data()->profile_pic?>" class="img-thumbnail">
            <?php }else{
              $grav = get_gravatar(strtolower(trim($user->data()->email)));
              $useravatar = '<img src="'.$grav.'" class="img-thumbnail" alt="'.$user->data()->username.'">';
              echo $useravatar;
            } ?>
          </p>
        </div>
        <div class="col-12 col-md-10">
          <h1><?=ucfirst($user->data()->username)?>'s Profile</h1>

          <h2>Bio</h2>
          <form name="update_bio" action="edit_profile.php" method="post">
            <div align="center"><textarea rows="20" cols="80"  id="mytextarea" name="bio" ><?=$thisProfile->bio;?></textarea></div>
            <input type="hidden" name="csrf" value="<?=Token::generate();?>" >
          </p>
          <p>
            <button type="submit" class="btn btn-primary" name="update_bio">Update Bio</button>
            <a class="btn btn-info" href="<?=$us_url_root?>users/profile.php?id=<?php echo $userID;?>">Cancel</a>

          </p>

        </form>

      </div>
    </div>
  </div>


</div> <!-- /container -->

</div> <!-- /#page-wrapper -->

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.19/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.19/dist/summernote.min.js"></script>
<script>
$(document).ready(function(){
  $('#mytextarea').summernote({ height: 300});
});


</script>

<!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>
