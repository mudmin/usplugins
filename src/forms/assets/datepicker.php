<?php
//this is customized for the form processor, but you can set your own options below
//See http://trentrichardson.com/examples/timepicker/ for documentation
?>
<script src="<?=$us_url_root?>usersc/plugins/forms/assets/jquery-ui.min.js"></script>
<script src="<?=$us_url_root?>usersc/plugins/forms/assets/timepicker.js"></script>

<script>
    $('#<?=$o->col?>').datetimepicker({
      dateFormat: "yy-mm-dd",
      showTimepicker:0
    });
</script>
