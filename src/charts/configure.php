  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['update'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
$colors = $_POST['color'];
foreach($colors as $k=>$v){
  if(strlen($v) == 7){
    $db->update('plg_charts_colors',$k,['color'=>$v]);
  }
}

 }
 $token = Token::generate();
 $colors = $db->query("SELECT * FROM plg_charts_colors")->results();
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Configure the Charts Plugin!</h1>
          <p>This plugin creates very basic charts from database queries and other data sets. Typical usage would look like:</p>
          <p style="background-color:black; color:white;">
            $data = $db->query("SELECT username, logins FROM users LIMIT 10 ORDER BY logins DESC")->results();<br>
            createChart($data,['title'=>'Logins Per User','type'=>'bar']);</p>
            <p>Chart types include pie,line,doughnut,bar,polarArea,horizontalBar</p>
            <p>If you don't want to use a db query, you can build your data in a manner similar to the example below. It should be an
            array of objects with exactly 2 keys.</p>
            <p>		$data = [];<br>
            		$data[] = (object)array('username'=>'mudmin','logins'=>27);<br>
            		$data[] = (object)array('username'=>'james','logins'=>12);<br>
            		$data[] = (object)array('username'=>'kim','logins'=>32);</p>

            <p>Other options that can be passed in the $opts array are:<br>
              'title'=>'Chart Title Here'<br>
              'nolegend'=>true (Do not show the legend/key for the pie chart)<br>
              'id'=>'yourIdHere' (custom ID so you can do your own javascript)<br>
              'height'=>'300' <br>
              'width'=>'300' (These probably don't work how you would expect them to)<Br>
            </p>
            <p>Data will appear in the order it is passed to the function. Colors will also. Colors can be reset below.</p>
            <p>This is just a starter plugin and I would love to see people extend its functionality.</p>
          <form class="" action="" method="post"><br>
            <input type="hidden" name="csrf" value="<?=$token?>">
            <input type="submit" name="update" value="Update Colors" class="btn btn-primary btn-block">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>ID</th><th>Color Name</th><th>Color</th><th>Replace With</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($colors as $c){?>
                  <tr>
                    <td><?=$c->id?></td>
                    <td><?=$c->color?></td>
                    <td><div style="background-color:<?=$c->color?>; height:2em;width:6em;" ></div></td>
                    <td>
                      <input type="color" name="color[<?=$c->id?>]" value="<?=$c->color?>">
                      </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
            <input type="submit" name="update" value="Update Colors" class="btn btn-primary btn-block">

          </form>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
