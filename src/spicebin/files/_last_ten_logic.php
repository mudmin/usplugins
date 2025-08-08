<?php
$col = "lten_view";
if($pset->$col > 0){
  $main = "col-10";
  if($pset->$col == 1){
    $mode = "last";
  }else{
    $mode = "random";
  }
}else{
  $main = "col-12";
}
