<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);

// CSP: reuse core's nonce if present, otherwise self-provide (older UserSpice).
if (!isset($GLOBALS['userspice_nonce'])) {
    $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
}

$hasher_files = glob($abs_us_root.$us_url_root.'/usersc/plugins/hasher/*.zip');
$plugins_files = glob($abs_us_root.$us_url_root.'/usersc/plugins/*.zip');
$files = array_merge($hasher_files, $plugins_files);

if(!empty($_POST)){
  $token = Input::get('csrf');
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }

  if(!empty($_POST['purgeZips'])){
    foreach ($files as $file) {
      if(file_exists($file)){
        unlink($file);
      }
    }
    usSuccess("Zip files purged");
    Redirect::to($us_url_root."users/admin.php?view=plugins_config&plugin=hasher");
  }
}

?>
<div class="content mt-3">
  <div class="row">
    <div class="col-sm-12">
    <form class="" action="" method="post" data-us-confirm="Do you really want to do this? It cannot be undone.">
      <h3>Hasher Plugin
        <?php if(count($files) > 0){ ?>
          <input type="hidden" name="csrf" value="<?=Token::generate();?>">
          <input type="submit" name="purgeZips" value="Purge Zip Files" class="btn btn-sm btn-outline-danger">
        <?php } ?>
      </h3>
    </form>
      <p>The Hasher Plugin scans the 'plugins' and 'plugins/hasher' directories for zip files and generates a hash (specifically, a SHA-256 hash encoded in base64) of each file. This hash serves as a unique fingerprint for the file's contents. Even a minor change of 1 bit inside a file typically results in a dramatically different hash value, often changing about half the bits of the hash.
      </p>
      <p class="mt-3">This functionality allows UserSpice to automatically verify the integrity of downloaded plugins and updates by comparing the generated hash against a known, trusted hash value. This process helps detect any unauthorized modifications or tampering that may have occurred during the download or storage of the plugin files and updates. 
      </p>
      
      <br>
      <?php
      foreach ($files as $file) {
        $zip = new ZipArchive;
        if($zip->open($file) != "true")
        {
          echo "Error :- Unable to open the Zip File";
        }else{
          $newCrc = base64_encode(hash_file("sha256", $file));
          $filename = basename($file);
          $location = strpos($file, '/plugins/hasher/') !== false ? 'plugins/hasher/' : 'plugins/';
          ?>
          <h4><small class="text-muted"><?=$location?></small><?=$filename?>
          <button type="button" class="btn btn-primary btn-sm" data-us-copy="<?=safeReturn($newCrc)?>">Copy</button>
          </h4>
          <p><?=$newCrc?></p>
          <?php
        }
        ?>
        <hr>
        <?php
      }
      ?>
    </div>
  </div>
</div>

<script nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>" type="text/javascript">
function copyStringToClipboard(textToCopy, button) {
  navigator.clipboard.writeText(textToCopy).then(function() {
    // Change button text to indicate success
    var originalText = button.innerHTML;
    button.innerHTML = "Copied!";
    button.classList.add("btn-success");
    button.classList.remove("btn-primary");

    // Revert button text after 2 seconds
    setTimeout(function() {
      button.innerHTML = originalText;
      button.classList.add("btn-primary");
      button.classList.remove("btn-success");
    }, 2000);
  }).catch(function(err) {
    console.error('Could not copy text: ', err);
    // Optionally, provide feedback for failure
    button.innerHTML = "Failed to copy";
    button.classList.add("btn-danger");
    button.classList.remove("btn-primary");
    setTimeout(function() {
      button.innerHTML = "Copy";
      button.classList.add("btn-primary");
      button.classList.remove("btn-danger");
    }, 2000);
  });
}
document.querySelectorAll('[data-us-copy]').forEach(function(btn) {
  btn.addEventListener('click', function() {
    copyStringToClipboard(this.getAttribute('data-us-copy'), this);
  });
});
</script>