<?php if (count(get_included_files()) == 1) die(); //Direct Access Not Permitted
?>
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


$change = Input::get('change');
if($change != "pic"){
  ?>
  <div class="form-group">
<button type="button" onclick="window.location.href = 'account.php?change=pic';" name="button" class="btn btn-primary btn-block">Update Photo</button>
</div>
<?php } 

if ($change == 'pic') {
  if (!empty($_FILES)) {

    $date = date('Y-m-d');
    $prid = $user->data()->id;
    $ds          = '/';  //1

    $storeFolder = "../files/";   //2

    $name = $_FILES["file"]["name"];
    $ext = end((explode(".", $name)));
    $uniq_name = $prid . '-' . $date . '-' . uniqid() . '.' . $ext;

    $tempFile = $_FILES['file']['tmp_name'];          //3
    $targetPath = dirname(__FILE__) . $ds . $storeFolder . $ds;  //4

    $targetFile =  $targetPath . $uniq_name;  //5
    //$targetFile =  $targetPath. $_FILES['file']['name'];  //5

    // 1. Check MIME Type
    $imageInfo = getimagesize($tempFile);
    if ($imageInfo === FALSE) {
      usError("Uploaded file is not a valid image.");
      Redirect::to(currentPage());
    }

    // 2. Check file extension
    $validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $name = $_FILES["file"]["name"];
    $ext = strtolower(end((explode(".", $name))));
    if (!in_array($ext, $validExtensions)) {
      usError("Uploaded file is not a valid image extension.");
      Redirect::to(currentPage());
    }

    // 3. Check File Size (5MB limit)
    $maxSize = 5 * 1024 * 1024; // 5MB in bytes
    if ($_FILES['file']['size'] > $maxSize) {
      usError("Uploaded file is too large. 5MB limit.");
      Redirect::to(currentPage());
    }


    if (move_uploaded_file($tempFile, $targetFile)) { //6
      if ($user->data()->profile_pic != '') {
        unlink($abs_us_root . $us_url_root . "usersc/plugins/profile_pic/files/" . $user->data()->profile_pic);
      }
      $fields = array(
        'profile_pic'   => $uniq_name,
      );
      $db->update('users', $user->data()->id, $fields);
    }
  }
?>
  <form action="<?= $target ?>" id="my-awesome-dropzone" class="dropzone"></form>
  <script type="text/javascript">
    Dropzone.options.myAwesomeDropzone = {
      maxFiles: 1,
      dictDefaultMessage: "Drag a photo here (png,jpg,gif)<br>or click this box to open your file manager.",
      acceptedFiles: "image/jpeg,image/gif,image/png",
      accept: function(file, done) {
        console.log("uploaded");
        done();
        // alert("Uploaded!");
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
  <style media="screen">
  .img-thumbnail{
    max-width:240px !important;
    max-height:310px !important;
  }
</style>

<?php if($user->data()->profile_pic != ''){ ?>
<script type="text/javascript">
  $(document).ready(function() {
    $(".img-thumbnail, .profile-replacer").attr("src", "<?=$us_url_root?>usersc/plugins/profile_pic/files/<?=$user->data()->profile_pic?>");
 });
</script>
<?php } ?>

