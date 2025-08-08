<?php 
$modes = [
    ['mode'=>'settings','name'=>'Settings'],
    ['mode'=>'admin_message_send','name'=>'Admin Send'],

];
?>
<div class="row">
    <?php 
    foreach($modes as $k=>$v){ 
        
        if($v['mode'] == Input::get('mode')){
            $class = "btn btn-outline-secondary btn-sm";
        }else{
            $class = "btn btn-outline-primary btn-sm";
        }
        ?>
        <div class="col-12 col-md-3 col-lg-2">
            <a href="admin.php?view=plugins_config&plugin=messaging&mode=<?=$v['mode']?>" class="<?=$class?>"><?=$v['name']?></a>   
        </div>
        <?php } ?>
</div>
