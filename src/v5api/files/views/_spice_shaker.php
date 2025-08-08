<?php if(!in_array($user->data()->id,$master_account)){Redirect::to('admin.php');}
$diag = Input::get('diag');
if(empty($_POST)){
  $_POST['type'] = "featured";
}
?>
<div class="col-sm-8">
  <div class="page-header float-right">
    <div class="page-title">
      <ol class="breadcrumb text-right">
        <ol class="breadcrumb text-right">
          <li><a href="<?=$us_url_root?>users/admin.php">Dashboard</a></li>
          <li>Spice Shaker</li>
          <!-- <li class="active">Users</li> -->
        </ol>
      </ol>
    </div>
  </div>
</div>
</div>
</header>

<div class="content mt-3">
  <?php
  if ($settings->spice_api != '' && !preg_match("/^[\w]{5}-[\w]{5}-[\w]{5}-[\w]{5}-[\w]{5}$/",$settings->spice_api)) {
    echo "<h4><font color='red'>The API Key does not appear to be valid.</font> </h4>";
  }
  if($diag){
    echo "<h6>Diagnostic Mode Activated</h6><br>";
    echo "<h6><font color='red'>Please Note:</font> Additional diagnostic info may be <a href='admin.php?view=logs'>located in the logs</a>.</h6><br>";
  }
  $type = Input::get('type');
  if($diag && $type == '' && !isset($_POST['goSearch'])){$_POST['goSearch'] = 1 && $_POST['search'] = 'demo plugin';}
  $api = "https://api.userspice.com/api/v2/";
  // $api = "http://localhost/bugs/api/v2/";

  if($settings->spice_api != ''){
    if($diag){ echo "<h6>API Key found.</h6><br>"; }
    if(!empty($_POST['type'])){
      //create a new cURL resource
      $ch = curl_init($api);
      //setup request to send json via POST
      $data = array(
        'key' => $settings->spice_api,
        'type' => $type,
        'call' => 'loadtype'
      );
      $payload = json_encode($data);

      if($diag){ echo "<h6>Attempting CURL Request. Will show results below if they exist.</h6><br>"; }
      //attach encoded JSON string to the POST fields
      curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
      //set the content type to application/json
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
      //return response instead of outputting
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      //execute the POST request
      $result = curl_exec($ch);
      if($diag){
        echo substr($result,0,150)."<br>";
        $info = curl_getinfo($ch);
        echo 'Took '. $info['total_time']. ' seconds to send a request<br>';
        if(curl_errno($ch))
        {
          echo 'Curl error: ' . curl_error($ch);
        }
      }
      //close cURL resource
      curl_close($ch);

    }
    if(!empty($_POST['goSearch']) || !empty($_GET['search'])){
      $search = Input::get('search');
      // dnd($search);
      //create a new cURL resource
      $ch = curl_init($api);
      //setup request to send json via POST
      $data = array(
        'key' => $settings->spice_api,
        'search' => $search,
        'call' => 'search'
      );
      $payload = json_encode($data);
      if($diag){ echo "<h6>Attempting CURL Request. Will show results below if they exist.</h6><br>"; }
      //attach encoded JSON string to the POST fields
      curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
      //set the content type to application/json
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
      //return response instead of outputting
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      //execute the POST request
      $result = curl_exec($ch);
      if($diag){
        echo substr($result,0,150)."<br>";
        $info = curl_getinfo($ch);
        echo '<h6>Took '. $info['total_time']. ' seconds to send a request</h6><br>';
        if(curl_errno($ch))
        {
          echo 'Curl error: ' . curl_error($ch);
        }
      }
      //close cURL resource
      curl_close($ch);

    }
  }//end if key check
  else{
    if($diag){ echo "<h6>No API Key found.</h6><br>"; }
  }

  ?>

  <h2>Spice Shaker Auto Installer
    <?php if(!$diag){ ?>
      <button type="button" onclick="window.location.href = 'admin.php?view=spice&diag=1';" name="button" class="btn btn-primary">Enter Diagnostic Mode</button>
    <?php }else{ ?>
      <button type="button" onclick="window.location.href = 'admin.php?view=spice';" name="button" class="btn btn-primary">Leave Diagnostic Mode</button>
    <?php } ?>
  </h2>
  <?php if($diag){?>
    <br><h3>Please Install/Update the demo plugin and look at the messages above and in your logs to diagnose API issues.</h3><br>
  <?php } ?>
  Spice Shaker allows you to download and automatically install Updates, Plugins, Templates, Widgets, and Languages for UserSpice.<br>Users with a (free) API key can make 2000 requests a day.<br>
  <?php
  $failed = 0;
  if(!function_exists('curl_version')){
    echo "<font color='red'>We see that you do not have CURL installed on your server.  Please make sure it is enabled.</font><br>";
    $failed = 1;
  }
  if(!is_writable($abs_us_root.$us_url_root.'users/parsers/checkWrite.php')){
    echo "<font color='red'>It appears that you cannot write to the users/parsers folder.  If you cannot download plugins, this is why.</font><br>";
    $failed = 1;
  }

  if($failed == 1){ ?>
    Please check out <a href="https://userspice.com/spice-shaker-problems/">https://userspice.com/spice-shaker-problems/</a> for some tips to fix Spice Shaker on your server.
  <?php } ?>
  <div class="content mt-3">
    <div class="row">
      <div class="col-6">
        <div class="form-group">

          <label for="gid">UserSpice API Key (
            <a href="https://userspice.com/developer-api-keys/">
              <?php if($settings->spice_api == ''){
                echo "<font color='red'><strong>Spice Shaker will not work with out a FREE API Key. Please refresh after pasting your key.</font></strong>";
              }
              ?>
              Get One Here</a>
              )</label>
              <input type="password" autocomplete="new-password" class="form-control ajxtxt" data-desc="UserSpice API Key" name="spice_api" id="spice_api" value="<?=$settings->spice_api?>">
            </div>
          </div>
          <div class="col-4">
            <form class="" action="admin.php?view=spice" method="post">
              <label for="type">Browse</label>
              <div class="d-flex">
                <select class="form-control" name="type">
                  <option value="" disabled <?php if($type ==''){?>selected="Selected"<?php } ?>>--Choose One--</option>
                  <option value="featured" <?php if($type =='featured'){?>selected="Selected"<?php } ?>>Featured</option>
                  <option value="plugin" <?php if($type =='plugin'){?>selected="Selected"<?php } ?>>Plugins</option>
                  <option value="template" <?php if($type =='template'){?>selected="Selected"<?php } ?>>Templates</option>
                  <option value="widget" <?php if($type =='widget'){?>selected="Selected"<?php } ?>>Widgets</option>
                  <option value="translation" <?php if($type =='translation'){?>selected="Selected"<?php } ?>>Languages</option>
                </select>
                <?php if($settings->spice_api != ''){?>
                  <input type="submit" name="go" value="Go">
                <?php } ?>
              </div>
            </form>
          </div>
        </div>
        <form class="" action="" method="post">
         <div class="row mb-5">
           <div class="col-4 offset-2">
               <input type="text" name="search" class="form-control" value="" placeholder="Search all addons" autocomplete="new-password">
           </div>
           <div class="col-3">
               <?php if($settings->spice_api != ''){?>
                 <input type="submit" name="goSearch" value="Search" required>
               <?php } ?>
           </div>

         </div>
         </form>

        <?php if(isset($result)){
          $dev = json_decode($result);
          $counter = 0;
          if(!is_null($dev)){
            foreach($dev as $d){
              if($d->release_type == 1){
                $class = "card-official";
                $text = "Official ";
                $img = $us_url_root."users/images/check.png";
                $warning = "";
              }else{
                $class = "";
                $text = "Community ";
                $img = "";
                $warning = "warnme";
              }
              ?>

              <div class="col-md-6 col-lg-4 pb-3">
                <div class="card card-custom <?=$class?> bg-white border-white border-0" style="height: 450px">
                  <div class="card-custom-img" style="background-image: url(<?php if($d->img != ''){echo $d->img;}else{?>http://res.cloudinary.com/d3/image/upload/c_scale,q_auto:good,w_1110/trianglify-v1-cs85g_cc5d2i.jpg <?php }?>);"></div>
                    <div class="card-custom-avatar">
                      <?php if($d->icon == ''){
                        $src = "https://bugs.userspice.com/usersc/logos/nologo.png";
                      }else{
                        $src = $d->icon;
                      }
                      ?>
                      <img class="img-fluid" src="<?=$src?>" alt="Avatar" />
                    </div>
                    <div class="card-body" style="overflow-y: auto">
                      <h6 class="card-title"><?=$d->project?> v<?=$d->version." (".$d->status.")";?></h6>
                      <p><strong><?=$text?><?=ucfirst($d->category)?></strong>
                        <img src="<?=$img?>" alt="" height="15">
                      </p>
                      <p class="card-text"><?=$d->descrip?></p>
                    </div>
                    <div class="card-footer" style="background: inherit; border-color: inherit;">
                      <a href="#" class="btn btn-default install" style="display:none;">Please Wait</a>
                      <?php
                      if(shakerIsInstalled($d->category,$d->reserved)){
                        ?>
                        <button type="button" name="button" class="btn btn-danger installme <?=$warning?>"  data-res="<?=$d->reserved?>" data-type="<?=$d->category?>" data-url="<?=$d->dd?>" data-hash="<?=$d->hash?>" data-counter="<?=$counter?>">Update</button>
                      <?php }else{ ?>
                        <button type="button" name="button" class="btn btn-primary installme <?=$warning?>"  data-res="<?=$d->reserved?>" data-type="<?=$d->category?>" data-url="<?=$d->dd?>" data-hash="<?=$d->hash?>" data-counter="<?=$counter?>">Download</button>
                      <?php } ?>
                      <a href="https://github.com/<?=$d->repo?>/tree/master/src/<?=$d->reserved?>" class="btn btn-outline-primary" target="_blank">View Source</a>
                      <a href="#" class="btn btn-success visit" target="_blank" style="display:none" id="<?=$counter?>">Check it Out!</a>
                    </div>
                  </div>

                </div>
                <?php
                $counter++;
              }
            }else{
              ?>
              <p align="center"><font color="red"><strong>No results found</font></strong></p>
              <?php
            }
          }

          ?>

        <script type="text/javascript">
        $( ".installme" ).click(function(event) {
          if($(this).hasClass("warnme")){
            if(!confirm("WARNING:  Please understand that this is a community provided addon and the UserSpice developers cannot take any responsibility for any harm it may cause.  Please make sure you understand the risks before using community provided addons")){
              location.reload(forceGet)
            }
          }

          $(".installme").hide();
          $(".install").show();
          var counter = $(this).attr('data-counter');
          var formData = {
            'type' 			:  $(this).attr('data-type'),
            'url' 			:  $(this).attr('data-url'),
            'hash' 			:  $(this).attr('data-hash'),
            'reserved'  :  $(this).attr('data-res'),
            'diag'      :  "<?=$diag?>",
          };

          $.ajax({
            type 		: 'POST',
            url 		: 'parsers/downloader.php',
            data 		: formData,
            dataType 	: 'json',
          })

          .done(function(data) {
            if(data.success == true){
              $("#"+counter).css('display','inline');
              $("a").closest(".visit").attr("href",data.url);
              $(".installme").show();
              $(".install").hide();
            }else{
              alert(data.error);
              $(".installme").show();
              $(".install").hide();
            }

          })
        });
      </script>


    </div>

    <style media="screen">
    .card-custom {
      overflow: hidden;
      min-height: 450px;
      box-shadow: 0 0 15px rgba(10, 10, 10, 0.3);
    }

    .card-official{
      box-shadow: 0 0 10px rgba(0, 255, 0, 0.3);
    }

    .card-custom-img {
      height: 200px;
      min-height: 200px;
      background-repeat: no-repeat;
      background-size: cover;
      background-position: center;
      border-color: inherit;
    }

    /* First border-left-width setting is a fallback */
    .card-custom-img::after {
      position: absolute;
      content: '';
      top: 161px;
      left: 0;
      width: 0;
      height: 0;
      border-style: solid;
      border-top-width: 40px;
      border-right-width: 0;
      border-bottom-width: 0;
      border-left-width: 545px;
      border-left-width: calc(575px - 5vw);
      border-top-color: transparent;
      border-right-color: transparent;
      border-bottom-color: transparent;
      border-left-color: inherit;
    }

    .card-custom-avatar img {
      border-radius: 50%;
      box-shadow: 0 0 15px rgba(10, 10, 10, 0.3);
      position: absolute;
      top: 100px;
      left: 1.25rem;
      width: 100px;
      height: 100px;
    }

    html {
      font-size: 14px;
    }

    .container {
      font-size: 14px;
      color: #666666;
      font-family: "Open Sans";
    }

  </style>
