<?php 
//the goal of this section is to create a list of user ids that the message gets sent to
if(!empty($_POST) && $send_method == "all_users"){
    $send_to = [];
    $to = $db->query("SELECT id FROM users WHERE active = 1")->results();
    foreach($to as $t){
        $send_to[] = $t->id;
    }
}

//Every key should be unique and ideally match the file name. Although they will be loaded in file name order, so you could use the file name to reorder.  The key is used to identify the send method for managing post. The other options determine the dropdown and any options required for the send method.  requires_secondary tells the parser to show more options if this option is selected. 
$send_options["all_users"] =
    [
        'name'=>"All Users",
        'requires_secondary'=>false,
        'select_label'=>"",
        'input' => '',

    ];
    