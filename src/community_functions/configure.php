  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
$xml = getCommunityAssets();
$v = Input::get('v');
$enabled = json_decode($settings->fun ?? "");
if($enabled == ''){$enabled = [];}
 if(!empty($_POST)){
   $token = $_POST['csrf'];
    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
    $en = Input::get('enable');
    $new = [];
    foreach($xml as $x){
      if(in_array($x,$en)){
        $new[] = $x;
      }
    }
    $db->update('settings',1,['fun'=>json_encode($new)]);
    Redirect::to('admin.php?view=plugins_config&plugin=community_functions&err=Saved!');
 }
 $token = Token::generate();
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Community Functions</h1>
          <?php if($v == ''){  //If we are not in the documentation section, we will show all function sets ?>
          <p>Below is a list of community provided curated functions to make your development easier.  You can enable them by category.
            Click on each category to get a list of what's inside and a brief description of how to use it.</p>
            <table class="table table-striped">
              <form class="" action="" method="post" name="plugin_community_functions">
                <input type="hidden" name="csrf" value="<?=$token?>" />
              <thead>
                <tr>
                  <th>Category</th><th>Description</th><th>Author(s)</th><th>Enable/Disable
                  <input type="submit" name="submit" value="Save" class="btn btn-primary">
                  </th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($xml as $x){
                  $xmlDoc = new DOMDocument();
                  $xmlDoc->load($abs_us_root.$us_url_root.'usersc/plugins/community_functions/assets/'.$x.".xml");
                  $package=$xmlDoc->getElementsByTagName('package');
                  ?>
                  <tr>
                    <td><a href="admin.php?view=plugins_config&plugin=community_functions&v=<?=$x?>"><?=$package->item(0)->getElementsByTagName('name')->item(0)->childNodes->item(0)->nodeValue;?></a></td>
                    <td><?=$package->item(0)->getElementsByTagName('description')->item(0)->childNodes->item(0)->nodeValue;?></td>
                    <td><?=$package->item(0)->getElementsByTagName('author')->item(0)->childNodes->item(0)->nodeValue;?></td>
                    <td><input type="checkbox" name="enable[]" value="<?=$x?>" <?php if(in_array($x,$enabled)){echo "checked";} ?>>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
              </form>
            </table>
          <?php }else{ //Begin Documentation Section

            $xmlDoc = new DOMDocument();
            $xmlDoc->load($abs_us_root.$us_url_root.'usersc/plugins/community_functions/assets/'.$v.".xml");
            $items=$xmlDoc->getElementsByTagName('function');
            ?>
            <br>
            <h3><?=ucFirst($v)?> Documentation (<a href="admin.php?view=plugins_config&plugin=community_functions">Return to Plugin</a>)</h3>
          Functions in this file.<br>
          <?php for ($i=0; $i < $items->length ; $i++) { ?>
            <strong><a href="#<?=$items->item($i)->getElementsByTagName('name')->item(0)->childNodes->item(0)->nodeValue;?>">
              <?=$items->item($i)->getElementsByTagName('name')->item(0)->childNodes->item(0)->nodeValue;?>
                    </a>
            </strong><br>
          <?php } ?>
          <?php for ($i=0; $i < $items->length ; $i++) { ?>
            <div class="card" id="<?=$items->item($i)->getElementsByTagName('name')->item(0)->childNodes->item(0)->nodeValue;?>">
              <div class="card-header"><strong>function <?=$items->item($i)->getElementsByTagName('name')->item(0)->childNodes->item(0)->nodeValue;?></strong></div>
              <div class="card-body">Description: <?=$items->item($i)->getElementsByTagName('description')->item(0)->childNodes->item(0)->nodeValue;?></strong></div>
              <div class="card-footer">Usage: <font color="blue"><?=$items->item($i)->getElementsByTagName('usage')->item(0)->childNodes->item(0)->nodeValue;?></font></div>
            </div>
          <?php }
        } //End of Documentation Section ?>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
