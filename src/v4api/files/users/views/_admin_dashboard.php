<div class="col-sm-8">
  <div class="page-header float-right">
    <div class="page-title">
      <ol class="breadcrumb text-right">
        <li class="active">Dashboard</li>
      </ol>
    </div>
  </div>
</div>
</div>
</header><!-- /header -->
<script src="js/Chart.bundle.js"></script>
<div class="content mt-3">
  <div class="row">
    <div class="col-12">
          <p align="right"><a href="https://bugs.userspice.com/usersc/addons.php">Get more dashboard widgets here</a></p>
    </div>
  </div>


<!-- Begin Widgets -->
<!-- <div class="col-sm-12 mb-6"> -->
  <?php
  $widgets = glob($abs_us_root.$us_url_root.'usersc/widgets/*' , GLOB_ONLYDIR);
  foreach($widgets as $w){
    include($w.'/widget.php');
  }
  ?>
<!-- </div> -->

<!-- End Widgets -->
<!-- admin_panel_buttons.php-->
<?php if(file_exists($abs_us_root.$us_url_root.'usersc/includes/admin_panel_buttons.php')){ ?>
  <div class="col-sm-12 mb-6">
    <?php include($abs_us_root.$us_url_root.'usersc/includes/admin_panel_buttons.php');?>
  </div>
<?php } ?>
<!-- End admin_panel_buttons.php -->
<?php
// UserSpice Announcements
$filename= 'https://rss.userspice.com/rss.xml';
$file_headers = @get_headers($filename);
if(($file_headers[0] != 'HTTP/1.1 200 OK') && ($file_headers[1] != 'HTTP/1.1 200 OK')){
  //logger($user->data()->id,"Errors","UserSpice Announcements feed not found. Please tell UserSpice!");
} else {
  $limit = 0;
  $dis = $db->query("SELECT * FROM us_announcements")->results();
  $dismissed = [];
  foreach($dis as $d){
    $dismissed[] = $d->dismissed;
  }
  $xmlDoc = new DOMDocument();
  $xmlDoc->load('https://rss.userspice.com/rss.xml');
  $x=$xmlDoc->getElementsByTagName('item');
  for ($i=0; $i<=2; $i++) {
    if($limit == 1){
      continue;
    }

    $dis=$x->item($i)->getElementsByTagName('dis')
    ->item(0)->childNodes->item(0)->nodeValue;

    if(!in_array($dis,$dismissed) && $dis != 0){
      $limit = 1;
      $ignore=$x->item($i)->getElementsByTagName('ignore')
      ->item(0)->childNodes->item(0)->nodeValue;
      $title=$x->item($i)->getElementsByTagName('title')
      ->item(0)->childNodes->item(0)->nodeValue;
      $class=$x->item($i)->getElementsByTagName('class')
      ->item(0)->childNodes->item(0)->nodeValue;
      $link=$x->item($i)->getElementsByTagName('link')
      ->item(0)->childNodes->item(0)->nodeValue;
      $message=$x->item($i)->getElementsByTagName('message')
      ->item(0)->childNodes->item(0)->nodeValue;
      if(version_compare($ignore, $user_spice_ver) !=  1){
        continue;
      }

  }
}
}
?>
<div class="row">
<div class="col-12">

  <?php if(isset($message) && $message != ''){  ?>
  <div class="sufee-alert alert with-close alert-<?=$class?> alert-dismissible fade show">
    <span class="badge badge-pill badge-<?=$class?>"><?php echo htmlspecialchars($title);?></span> <a href="<?php echo htmlspecialchars($link);?>"><?php echo htmlspecialchars($message);?></a>
    <button type="button" class="close dismiss-announcement" data-dismiss="alert"
    data-dis="<?=$dis?>"
    data-ignore="<?=$ignore?>"
    data-title="<?=$title?>"
    data-class="<?=$class?>"
    data-link="<?=$link?>"
    data-message="<?=$message?>"
    aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<?php } ?>
</div>
</div>
  <script type="text/javascript">
  $(document).ready(function() {
    //dismiss notifications
    $(".dismiss-announcement").click(function(event) {
      event.preventDefault();
      console.log("clicked");

      var formData = {
        'dismissed' 					: $(this).attr("data-dis"),
        'link' 					: $(this).attr("data-link"),
        'title' 					: $(this).attr("data-title"),
        'class' 					: $(this).attr("data-class"),
        'message' 					: $(this).attr("data-message"),
        'ignore' 					: $(this).attr("data-ignore")
      };
      //
      $.ajax({
        type 		: 'POST',
        url 		: 'parsers/dismiss_announcements.php',
        data 		: formData,
        dataType 	: 'json',
        encode 		: true
      })
    });


  }); //End DocReady
</script>
