<?php
function getCommunityAssets(){
  global $abs_us_root,$us_url_root;
  $xml = preg_grep('~\.(xml)$~i', scandir($abs_us_root.$us_url_root.'usersc/plugins/community_functions/assets'));
  foreach($xml as $k=>$v){
    $xml[$k] = substr($v,0,-4);
  }
return $xml;
}
