<?php

if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
if(!pluginActive("spicebin",true)){ die ("SpiceBin is disabled");}

global $mode,$pset;
if($pset == ""){
  $pset = $db->query("SELECT * FROM plg_spicebin_settings")->first();
}
$term = "Random";
if($mode != "id RAND()"){
  $mode = "id DESC";
  $term = "Latest";
}
$q = $db->query("SELECT * FROM plg_spicebin WHERE private = 0 ORDER BY $mode LIMIT 10");

$c = $q->count();
$pastes = $q->results();

if($c > 0){
  if($c == 1){
    $title = $c." ".$term." ".$pset->product_single;
  }else{
    $title = $c." ".$term." ".$pset->product_plural;
  }
?>
<h3><?=$title?></h3>

<table class="">
  <tbody>
    <?php foreach($pastes as $p){ ?>
      <tr>
        <td>
          <a href="<?=$us_url_root.$pset->view_page."?".$p->link?>">
          <h5><?=$p->title?></h5>
          </a>
          <p><?=$p->created_on?><br>
            <b><?=$p->lang?></b>
          </p>
          <hr>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>
<?php } ?>
