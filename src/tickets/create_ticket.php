<?php
$directAccess = 0;
if(count(get_included_files()) ==1){
  require_once "../../../users/init.php";
  require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
  $directAccess = 1;
}
if(!pluginActive("tickets",true)){ die("Tickets plugin not active");}
if(isset($user) && $user->isLoggedIn()){
$ticstatus = $db->query("SELECT * FROM plg_tickets_status")->first();
$ticcat = $db->query("SELECT * FROM plg_tickets_cats")->first();

$options = array(
  'submit'=>'submit',
  'class'=>'btn btn-primary',
  'value'=>'Create Ticket',
);

if(!empty($_POST['issue'])){
  $response = preProcessForm();
  if($response['form_valid'] == true){
    //let's store some other information
    $response['fields']['user']=$user->data()->id;
    $response['fields']['status']=$ticstatus->status;
    $response['fields']['category']=$ticcat->cat;
    $response['fields']['created']=date("Y-m-d H:i:s");
    $response['fields']['last_updated']=date("Y-m-d H:i:s");
    postProcessForm($response);
    $ticSettings = $db->query("SELECT * FROM plg_tickets_settings")->first();
    if($ticSettings->email_new != ""){
      $to = explode (",", $ticSettings->email_new);
      foreach($to as $t){
        $t = trim($t);
        if (filter_var($t, FILTER_VALIDATE_EMAIL)) {
        $body = $response['fields']['subject']."<br><br>".$response['fields']['issue'];
        email($t,"New Ticket Submitted",$body);
        }
      }
    }
    $lastTicket = $db->query("SELECT id FROM plg_tickets WHERE user = ? ORDER BY ID DESC LIMIT 1",[$user->data()->id])->first();
    $lastTicket = $lastTicket->id;
    $_GET['err'] = "Ticket created";
    $str = http_build_query($_GET, '', '&');
    $cp = $us_url_root.$ticSettings->single_view;
    Redirect::to($cp."?id=".$lastTicket."&".$str);
  }
}


displayForm("plg_tickets",$options);
}//end logged in

if($directAccess ==1){
require $abs_us_root . $us_url_root . 'users/includes/html_footer.php';
}

?>
