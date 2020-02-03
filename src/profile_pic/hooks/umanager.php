<?php if(count(get_included_files()) ==1) die();
?>
<script src="<?=$us_url_root?>usersc/plugins/profile_pic/assets/js/dropzone.js"></script>
<link href="<?=$us_url_root?>usersc/plugins/profile_pic/assets/css/dropzone.css" type="text/css" rel="stylesheet" />
<?php
global $userdetails;
if($userdetails->profile_pic != ''){ ?>
<script type="text/javascript">
  $(".img-thumbnail, .profile-replacer").attr("src", "<?=$us_url_root?>usersc/plugins/profile_pic/files/<?=$userdetails->profile_pic?>");
</script>
<?php } ?>

<?php
if (!empty($_FILES)) {

  $date=date('Y-m-d');
  $prid = $userdetails->id;
  $ds          = '/';  //1

  $storeFolder = "../files/";   //2

  $name = $_FILES["file"]["name"];
  $ext = end((explode(".", $name)));
  $uniq_name = $prid.'-'.$date. '-' . uniqid() . '.' .$ext;

  $tempFile = $_FILES['file']['tmp_name'];          //3
  $targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds;  //4

  $targetFile =  $targetPath. $uniq_name;  //5
  //$targetFile =  $targetPath. $_FILES['file']['name'];  //5

  if(move_uploaded_file($tempFile,$targetFile)){ //6
    if($userdetails->profile_pic != ''){unlink($abs_us_root.$us_url_root."usersc/plugins/profile_pic/files/".$userdetails->profile_pic);}
    $fields = array(
      'profile_pic'   => $uniq_name,
    );
    $db->update('users',$userdetails->id,$fields);
  }
}
?>
<div style="outline:1px,solid,black;">
<h4>Change This User's Profile Pic</h4>
<form action="admin.php?view=user&id=<?=$userdetails->id?>" id="my-awesome-dropzone" class="dropzone"></form>
</div>

<script type="text/javascript">
Dropzone.options.myAwesomeDropzone = {
  maxFiles: 1,
  dictDefaultMessage: "Drag a photo here (png,jpg)<br>or click this box to open your file manager.",
  acceptedFiles: ".png,.jpg,.jpeg",
  accept: function(file, done) {
    console.log("uploaded");
    done();
    // alert("Uploaded!");
  },
  init: function() {
    this.on("maxfilesexceeded", function(file){
      alert("No more files please!");
    });

    this.on('queuecomplete', function () {
      location.reload();
    });

  }
};
</script>
