<div class="content mt-3">
  <?php require_once($abs_us_root.$us_url_root.'usersc/plugins/forms/files/_form_manager_menu.php');?>

  <div class="row">
    <div class="col-sm-12">
      <?php
      $toDisplay = Input::get('demo');
      if(is_numeric($toDisplay)){
        displayView($toDisplay,['nosubmit'=>1]);
      }
      ?>
    </div>
  </div>
</div>


    <script>
    $(document).ready(function() {
    });
  </script>
