<?php 
//the goal of this section is to create a list of user ids that the message gets sent to
if(!empty($_POST) && $send_method == "system_admins"){
    $send_to = [];
    $to = $db->query("SELECT * FROM user_permission_matches WHERE permission_id = 2")->results();
    foreach($to as $t){
        $send_to[] = $t->user_id;
    }
}
$send_options["system_admins"] =
    [
        'name'=>"System Admins",
        'requires_secondary'=>false,
        'select_label'=>"",
        'input' => '',

    ];
