<?php if (count(get_included_files()) == 1) die(); ?>
<script src="<?= $us_url_root ?>usersc/plugins/profile_pic/assets/js/dropzone.js"></script>
<link href="<?= $us_url_root ?>usersc/plugins/profile_pic/assets/css/dropzone.css" type="text/css" rel="stylesheet" />
<?php
if (!isset($GLOBALS['userspice_nonce'])) {
    $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
}
global $user;
$change = Input::get('change');
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];
if (strpos($requestUri, '.php') === false && strpos($scriptName, '.php') !== false) {
  $target = "account?change=pic";
} else {
  $target = "account.php?change=pic";
}

if ($change != "pic") {
?>
  <div class="form-group">
    <a href="account.php?change=pic" class="btn btn-primary btn-block" role="button">Change Photo</a>
  </div>
<?php
}

if ($change == 'pic') {
  // If requesting deletion
  if (Input::get('delete') == 1) {
    if (!empty($user->data()->profile_pic)) {
      $safe_pic = basename($user->data()->profile_pic);
      unlink($abs_us_root . $us_url_root . "usersc/plugins/profile_pic/files/" . $safe_pic);
      $db->update('users', $user->data()->id, ['profile_pic' => '']);
      usSuccess("Profile picture removed.");
    }
    Redirect::to('account.php');
  }

  // If uploading a new file
  if (!empty($_FILES)) {
    $date = date('Y-m-d');
    $prid = $user->data()->id;
    $tempFile = $_FILES['file']['tmp_name'];

    $mimeMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
    $fileType = mime_content_type($tempFile);

    if (!array_key_exists($fileType, $mimeMap)) {
      usError("Uploaded file is not a valid image.");
      Redirect::to(currentPage());
    }

    $maxSize = 5 * 1024 * 1024;
    if ($_FILES['file']['size'] > $maxSize) {
      usError("Uploaded file is too large. 5MB limit.");
      Redirect::to(currentPage());
    }

    $ext = $mimeMap[$fileType];
    $uniq_name = $prid . '-' . $date . '-' . uniqid() . '.' . $ext;
    $targetPath = $abs_us_root . $us_url_root . "usersc/plugins/profile_pic/files/";
    $targetFile = $targetPath . $uniq_name;

    if (move_uploaded_file($tempFile, $targetFile)) {
      if (!empty($user->data()->profile_pic)) {
        $old_pic = basename($user->data()->profile_pic);
        if (file_exists($targetPath . $old_pic)) {
          unlink($targetPath . $old_pic);
        }
      }
      $db->update('users', $user->data()->id, ['profile_pic' => $uniq_name]);
    }
  }
?>

  <form action="<?= $target ?>" id="my-awesome-dropzone" class="dropzone"></form>
  <button type="button" class="btn btn-danger btn-block" style="margin-top:10px;"
    data-us-confirm="Are you sure you want to delete your profile picture?"
    data-href="account.php?change=pic&delete=1">
    Delete Photo
  </button>

  <script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
    document.addEventListener('click', function (e) {
      var btn = e.target.closest && e.target.closest('[data-us-confirm][data-href]');
      if (btn) {
        e.preventDefault();
        if (window.confirm((btn.getAttribute('data-us-confirm') || '').replace(/\\n/g, '\n'))) {
          window.location.href = btn.getAttribute('data-href');
        }
      }
    }, true);
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
    max-width: 240px !important;
    max-height: 310px !important;
  }
</style>

<?php if ($user->data()->profile_pic != '' && file_exists($abs_us_root . $us_url_root . 'usersc/plugins/profile_pic/files/' . $user->data()->profile_pic)) { ?>
  <script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
    $(document).ready(function() {
      $(".img-thumbnail, .profile-replacer").attr("src", "<?= $us_url_root ?>usersc/plugins/profile_pic/files/<?= $user->data()->profile_pic ?>");
    });
  </script>
<?php } ?>