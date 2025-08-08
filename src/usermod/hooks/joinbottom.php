<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $settings;
foreach(json_decode($settings->usermod) as $um){?>
  <script type="text/javascript">
    $("#<?=$um?>").attr("required",false); //fix this
    $("#<?=$um?>").hide();
  </script>
<?php } ?>
