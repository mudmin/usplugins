<?php
global $user;
if(hasPerm([2],$user->data()->id)){?>
<button type="button" onclick="window.location.href = 'admin.php';" name="button" class="btn btn-primary">Dashboard</button>
<?php } ?>
