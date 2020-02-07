<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted Leave this line in place
global $user;?>
<td><?=pointsUnitReturn($user->data()->plg_points);?></td>
