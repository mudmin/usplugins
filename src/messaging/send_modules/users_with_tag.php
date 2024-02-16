<?php 

//you can set permissions and other parameters at the top and wrap these options in an if statement to only show them to certain users
if(pluginActive("usertags",true)){


    if(!empty($_POST) && $send_method == "users_with_tag"){
        $theTag = Input::get('send_to_user');
        $to = $db->query("SELECT user_id FROM plg_tags_matches WHERE tag_id = ?",[$theTag])->results();
    
        foreach($to as $t){
            $send_to[] = $t->user_id;
        }
    }
    


$tags = $db->query("SELECT * FROM plg_tags ORDER by tag")->results();
$opts = "";
foreach($tags as $c){
    $opts .= "<option value='".$c->id."'>".$c->tag."</option>";
}
$send_options["users_with_tag"] =
    [
        'name'=>"Users with a Tag",
        'requires_secondary'=>true,
        'select_label'=>"Select Tag",
        'input' => '<select name="send_to_user" class="form-control send_to_user send_target_select select2">
        <option value="" disabled selected="selected">-- Select User --</option>'.$opts.'</select>',
   


    ];
}