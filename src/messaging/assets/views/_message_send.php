<?php
$msgSettings = $db->query("SELECT * FROM plg_msg_settings")->first();
$send_method = Input::get('send_to');
if($msgSettings->alerts == 0 && $msgSettings->messages == 0 && $msgSettings->notifications == 0){ ?>
    <h6 class="text-center text-danger">You have disabled all 3 message types, so you cannot send any messages.</h6>
    <?php
    }
if(file_exists($abs_us_root . $us_url_root . 'usersc/plugins/messaging/send_variable_overrides.php')){
    include $abs_us_root . $us_url_root . 'usersc/plugins/messaging/send_variable_overrides.php';
}


$qs = http_build_query($_GET);
$send_options = [];
$optsDir = $abs_us_root . $us_url_root . 'usersc/plugins/messaging/send_modules/';
$send_options = [];
$php_files = glob($optsDir . "*.php");
$items = scandir($optsDir);
$folders = array();
foreach ($items as $k=>$item) {
    if ($item == "." || $item == "..") {
        unset($items[$k]);
    }else{
        include $optsDir.$item;
    }
}


if(!empty($_POST['send_new_direct_message_hook'])){

    $msg_type = Input::get('msg_type');
    $expires = Input::get('msg_expires_on');
    $title = Input::get('title');
    $message = Input::get('msg');

    if($expires != "" && $expires < date("Y-m-d")){
        usError("You must set the message expiration date to the future");
    }elseif(!isset($send_to) || $send_to == []){
        usError("There were no users found that met this criteria so no message was sent");
    }else{
        //sendPlgMessage($user_to, $title, $message, $user_from = 0, $type = 1, $expires = "")
        sendPlgMessage($send_to, $title, $message, $user_from = 0, $msg_type, $expires, $send_method);
        usSuccess("Message sent");
        Redirect::to(currentPage() . "?" . $qs);
    }

}

?>
    <form action="" method="post">
    <?=tokenHere();?>
        <input type="hidden" name="send_new_direct_message_hook" value="1">
        <div class="card">
            <div class="card-header"><b>Send a new message</b></div>
            <div class="card-body">
                <div class="form-group mb-3">
                    <label for="message">Send a new...</label>
                    <select name="msg_type" id="" class="form-control msg_type" required>
                        <option value="" disabled>-- Select Type --</option>
                        <?php if($msgSettings->alerts == "1"){ ?>
                        <option value="1">Alert</option>
                        <?php } 
                        if($msgSettings->notifications == "1"){
                        ?>
                        <option value="2" selected="selected">Notification</option>
                        <?php } 
                        if($msgSettings->messages == "1"){ 
                        ?>
                        <option value="3">Message</option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="message">Send To</label>
                    <select name="send_to" class="form-control send_to" required>
                        <option value="" disabled selected="selected">-- Select Recipient(s) --</option>
                        <?php foreach($send_options as $k=>$v){ 
                            if(isset($v['requires_secondary']) && $v['requires_secondary'] == true){
                                $secondary = "true";
                            }else{
                                $secondary = "false";
                            }
                            ?>
                            <option value="<?=$k?>" 
                                data-secondary="<?=$secondary?>"
                            ><?=$v['name']?></option>
                        
                        <?php } ?>
                    </select>

                </div>

                <?php foreach($send_options as $k=>$v){ ?>
                    <div class="form-group mb-3 customers send_target <?=$k?>" style="display:none;">
                        <label for="message"><?=$v['select_label']?></label><br> 
                        <?=$v['input']?>
                    </div>
                        
                        <?php } ?>


                <div class="form-group mb-3">
                    <label for="msg_expires_on">Message Expires if Unread (Optional)</label>
                    <input type="date" name="msg_expires_on" class="form-control">
                </div>

                <div class="form-group mb-3">
                    <label for="title">Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="form-group mb-3">
                    <label for="msg">Message</label>
                    <textarea name="msg" id="" cols="30" rows="8" class="form-control summernote" required></textarea>
                </div>

                <input type="submit" class="col-12 btn btn-primary">

            </div>
        </div>
        </form>
 


<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
  .select2-container .select2-selection--single {
    height:2.3em !important;
    width: 100% !important;
  }

  .select2 {
  width: 100% !important;
}

</style>


<script>
$(document).ready(function() {

    $(".select2").select2();
});
</script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote.min.js"></script>
<script>
    $(document).ready(function() {
        $('.summernote').summernote({
            
            toolbar: [
      ['font', ['bold', 'italic', 'clear', 'underline']],
      ['color', ['color', 'background-color']],
    ], 
    height:200,                 
    minHeight: 200,             

  });
        // Add a custom click event handler to close dropdowns outside the toolbar
        $(document).on('click', function(event) {
            var noteBar = $('.note-editor .note-toolbar');
            if (!noteBar.is(event.target) && noteBar.has(event.target).length === 0) {
                // Close all open dropdowns in the toolbar
                noteBar.find('.dropdown-menu.show').removeClass('show');
            }
        });
        var noteBar = $('.note-toolbar');
        noteBar.find('[data-toggle]').each(function() {
            $(this).attr('data-bs-toggle', $(this).attr('data-toggle')).removeAttr('data-toggle');
       });
       
        $('.send_to').on('change', function() {

            // Unset the value of all inputs with a class of send_target_select
            $('.send_target_select').val('');
            $('.send_target').hide();

            var selectedValue = $(this).val();
            console.log(selectedValue);
            //check if this option requires a secondary selection
            var secondary = $(this).find(':selected').data('secondary');
            console.log(secondary);
            if(secondary == true){
    
                $('.' + selectedValue).show();
            }else{
                
            }
            // Show the corresponding div based on the selected value
            // if (selectedValue === 'one_user' || selectedValue === 'all_users_cust' || selectedValue === 'all_users_group') {
               
            // }
        });
    });
</script>

