<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted Leave this line in place
global $v1;?>
<td><a class="nounderline" href='admin.php?view=user&id=<?=$v1->id?>'>
  <?php
  if($v1->plg_ref_by > 0){
    echouser($v1->plg_ref_by);
  }else{
    echo "none";
  }
?>
</a></td>
