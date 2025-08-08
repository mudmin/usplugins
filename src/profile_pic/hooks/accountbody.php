<?php if (count(get_included_files()) == 1) die(); ?>
<script src="<?= $us_url_root ?>usersc/plugins/profile_pic/assets/js/dropzone.js"></script>
<link href="<?= $us_url_root ?>usersc/plugins/profile_pic/assets/css/dropzone.css" type="text/css" rel="stylesheet" />
<?php
global $user;
$change = Input::get('change');
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];
if (strpos($requestUri, '.php') === false && strpos($scriptName, '.php') !== false) {
  $target = "account?change=pic";
} else {
  $target = "account.php?change=pic";
}

if($change != "pic"){
  ?>
  <div class="form-group">
    <button type="button" onclick="window.location.href = 'account.php?change=pic';" class="btn btn-primary btn-block">Change Photo</button>
  </div>
  <?php
}

if($change == 'pic'){
  // If requesting deletion
  if(Input::get('delete') == 1){
    if(!empty($user->data()->profile_pic)){
      unlink($abs_us_root.$us_url_root."usersc/plugins/profile_pic/files/".$user->data()->profile_pic);
      $db->update('users', $user->data()->id, ['profile_pic'=>'']);
      usSuccess("Profile picture removed.");
    }
    Redirect::to('account.php');
  }

  // If uploading a new file
  if(!empty($_FILES)){
    $date = date('Y-m-d');
    $prid = $user->data()->id;
    $ds   = '/';
    $storeFolder = "../files/";
    $name = $_FILES["file"]["name"];
    $ext  = end((explode(".", $name)));
    $uniq_name = $prid . '-' . $date . '-' . uniqid() . '.' . $ext;
    $tempFile = $_FILES['file']['tmp_name'];
    $targetPath = dirname(__FILE__) . $ds . $storeFolder . $ds;
    $targetFile =  $targetPath . $uniq_name;

    // Check MIME
    $imageInfo = getimagesize($tempFile);
    if($imageInfo === FALSE){
      usError("Uploaded file is not a valid image.");
      Redirect::to(currentPage());
    }

    // Check extension
    $validExtensions = ['jpg','jpeg','png','gif'];
    $ext = strtolower($ext);
    if(!in_array($ext,$validExtensions)){
      usError("Uploaded file is not a valid image extension.");
      Redirect::to(currentPage());
    }

    // Check size
    $maxSize = 5 * 1024 * 1024;
    if ($_FILES['file']['size'] > $maxSize){
      usError("Uploaded file is too large. 5MB limit.");
      Redirect::to(currentPage());
    }

    if(move_uploaded_file($tempFile, $targetFile)){
      if($user->data()->profile_pic != ''){
        unlink($abs_us_root.$us_url_root."usersc/plugins/profile_pic/files/".$user->data()->profile_pic);
      }
      $db->update('users',$user->data()->id,['profile_pic'=>$uniq_name]);
    }
  }
  ?>
  
  <form action="<?= $target ?>" id="my-awesome-dropzone" class="dropzone"></form>
  <button class="btn btn-danger btn-block" style="margin-top:10px;" 
    onclick="if(confirm('Are you sure you want to delete your profile picture?')) {
      window.location.href='account.php?change=pic&delete=1';
    }">
    Delete Photo
  </button>

  <script type="text/javascript">
    Dropzone.options.myAwesomeDropzone = {
      maxFiles: 1,
      dictDefaultMessage: "Drag a photo here (png,jpg,gif)<br>or click this box to open your file manager.",
      acceptedFiles: "image/jpeg,image/gif,image/png",
      accept: function(file, done) {
        done();
      },
      init: function() {
        this.on("maxfilesexceeded", function(file) {
          alert("No more files please!");
        });
        this.on('queuecomplete', function() {
          window.location = window.location.pathname;
        });
      }
    };
  </script>
<?php } ?>
<style>
  .img-thumbnail {
    max-width:240px !important;
    max-height:310px !important;
  }
</style>

<?php if($user->data()->profile_pic != '' && file_exists($abs_us_root . $us_url_root . 'usersc/plugins/profile_pic/files/'.$user->data()->profile_pic)){ ?>
<script type="text/javascript">
  $(document).ready(function() {
    $(".img-thumbnail, .profile-replacer").attr("src", "<?=$us_url_root?>usersc/plugins/profile_pic/files/<?=$user->data()->profile_pic?>");
  });
</script>
<?php } ?>
