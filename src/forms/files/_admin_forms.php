
  <?php require_once($abs_us_root.$us_url_root.'usersc/plugins/forms/files/_form_manager_menu.php');?>


    <!-- Existing Forms -->
    <div class="col-lg-6 col-12">
      <?php require_once($abs_us_root.$us_url_root.'usersc/plugins/forms/files/_form_existing_forms.php');?>
    </div>
    <!-- Existing Views -->
    <div class="col-lg-6 col-12">
      <?php require_once($abs_us_root.$us_url_root.'usersc/plugins/forms/files/_form_existing_views.php');?>
    </div>
  </div>
  <br><br>
  If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
  <!-- Modal -->
  <div id="newForm" class="modal" role="dialog">
    <div class="modal-dialog">

      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Create a New Form</h4>
        </div>
        <div class="modal-body">
          <p>Please give the new form a name:</p>
          <div class="form-group">
            <form autocomplete="off" class="inline-form" action="" method="POST" id="newFormForm">
              <input size="50" type="text" name="name" value="" class="form-control" placeholder="Lowercase letters and numbers only"><br />
              <div class="btn-group pull-right"><input class='btn btn-outline-primary' type='submit' name="create_form" value='Create Form' class='submit' /></div><br />
            </form>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
        </div>
      </div>

    </div>
  </div>

  <!-- Modal -->
  <div id="fromDB" class="modal" role="dialog">
    <div class="modal-dialog">
      <?php $tables = getValidTables();?>
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Create a form from an existing DB table</h4>
        </div>
        <div class="modal-body">
          <p>Please choose an existing table:</p>
          <div class="form-group">
            <form autocomplete="off" class="inline-form" action="" method="POST" id="newFormFromDB">
              <select class="form-control" name="dbTable">
                <?php foreach($tables as $t){ ?>
                  <option value="<?=$t?>"><?=$t?></option>
                <?php } ?>
              </select>
              <br />
              <div class="btn-group pull-right"><input class='btn btn-outline-primary' type='submit' name="create_form_from_db" value='Create Form' class='submit' /></div><br />
            </form>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
        </div>
      </div>

    </div>
  </div>

  <!-- Modal -->
  <div id="duplicate" class="modal" role="dialog">
    <div class="modal-dialog">
      <?php $forms = $db->query("SELECT * FROM us_forms")->results();?>
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Duplicate an existing form</h4>
        </div>
        <div class="modal-body">
          <p>Please choose a form to duplicate:</p>
          <div class="form-group">
            <form autocomplete="off" class="inline-form" action="" method="POST" id="duplicate">
              <select class="form-control" name="old">
                <?php foreach($forms as $f){ ?>
                  <option value="<?=$f->form?>"><?=$f->form?></option>
                <?php } ?>
              </select>
              <br />
              New Form Name
              <input type="text" class="form-control" name="new" value="" placeholder="lowercase only, no symbols" required>
              <br />
              <div class="btn-group pull-right"><input class='btn btn-outline-primary' type='submit' name="duplicate" value='Duplicate Form' class='submit' /></div><br />
            </form>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
        </div>
      </div>

    </div>
  </div>

  <!-- Modal -->

  <div id="deleteForm" class="modal" role="dialog">
    <div class="modal-dialog">
      <?php
      $tables = getValidTables();;
      $forms = $db->query("SELECT * FROM us_forms")->results();
      $warnings = 0;
      ?>
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Delete an existing form</h4>
        </div>
        <div class="modal-body">
          <p>Please choose a form to delete:</p>
          <div class="form-group">
            <form autocomplete="off" class="inline-form" action="" method="POST" id="deleteForm" required
            onsubmit="return confirm('Please understand what you are doing. If you choose to delete a form, you will not be able to create a new one with the same name unless you choose the Create Form from an Existing Table option.  If you choose to delete the Form and Related Table, YOU WILL LOSE ALL THE DATA THAT WAS SUBMITTED FROM YOUR FORM and all data entirely if you created the form from an existing table. Use this feature carefully.  You cannot delete the users form.');"
            >
              <select class="form-control" name="deleteThisForm">
                <option disabled selected="selected">--choose a form--</option>
                <?php foreach($forms as $f){
                  if($f->form == "users"){ continue; }
                  ?>
                  <option value="<?=$f->form?>"><?=$f->form?>
                    <?php
                    if(in_array($f->form,$tables)){
                      $warnings = $warnings + 1;
                      echo "***";
                    }
                    ?>

                  </option>
                <?php } ?>
              </select>
              <br />
              <strong>This action <font color="red">cannot</font> be undone!<br>
                <br />
                <div class="btn-group pull-left"><input class='btn btn-outline-danger' type='submit' name="deleteTable" value='Delete the Form AND related DB Table' class='submit' /></div>
                <div class="btn-group pull-right"><input class='btn btn-success' type='submit' name="delete" value='Delete the Form!' class='submit' /></div><br />
              </form><br>
              <?php
              if($warnings > 0){ ?>
                *** Forms with *** after the name were created from UserSpice default database tables. Therefore, although you can delete the form itself, you cannot delete both the form and the data.
              <?php } ?>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
          </div>
        </div>

      </div>
    </div>

</div>

    <script src="../users/js/jwerty.js"></script>
    <script src="<?=$us_url_root?>usersc/plugins/forms/assets/combobox.js"></script>
    <script>
    $(document).ready(function() {
      $('.show-tooltip').tooltip();

      $('.combobox').combobox();

      jwerty.key('ctrl+f1', function () {
        $('.modal').modal('hide');
        $('#newForm').modal();
      });
      jwerty.key('ctrl+f2', function () {
        $('.modal').modal('hide');
        $('#fromDB').modal();
      });

      jwerty.key('esc', function () {
        $('.modal').modal('hide');
      });
      $('.modal').on('shown.bs.modal', function() {
        $('#combobox').focus();
      });
    });
  </script>
