<?php 
//the goal of this section is to create a list of user ids that the message gets sent to
//use the key you specified at the bottom to allow this module to be the definitive send method
if(!empty($_POST) && $send_method == "one_user"){
    $send_to = [];

    $to = $db->query("SELECT id FROM users WHERE active = 1")->results();
    foreach($to as $t){
        $send_to[] = $t->id;
    }
}

//The goal of this section is to create a sending option

//Every key should be unique and ideally match the file name. Although they will be loaded in file name order, so you could use the file name to reorder.  The key is used to identify the send method for managing post. The other options determine the dropdown and any options required for the send method.  requires_secondary tells the parser to show more options if this option is selected. 


if(!isset($users)){
    $users = $db->query("SELECT id, username, fname, lname, CONCAT(fname, ' ', lname) as thename FROM users WHERE permissions = 1 ORDER BY lname")->results();
}

$opts = "";
foreach($users as $c){
    $opts .= "<option value='".$c->id."'>".$c->thename."</option>";
}

$send_options["one_user"] =
    [
        'name'=>"One User",
        'requires_secondary'=>true,
        'select_label'=>"Send to a user",
        'input' => '<select name="send_to_user" class="form-control send_to_user send_target_select select2">
        <option value="" disabled selected="selected">-- Select User --</option>'.$opts.'</select>',

    ];
    