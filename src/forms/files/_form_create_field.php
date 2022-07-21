<form autocomplete="off" class="" action="" method="post">
  <div class="form-group">
    <label for="">Field type</label>
    <select class="form-control" name="field_type" id="field_type" required>
      <option value=""></option>
      <option value="text">text</option>
      <option value="number">whole number</option>
      <option value="money">money(xxx.xx)</option>
      <option value="textarea">textarea</option>
      <option value="dropdown">dropdown</option>
      <option value="checkbox">checkbox</option>
      <option value="radio">radio</option>
      <option value="date">date</option>
      <option value="datetime">datetime</option>
      <option value="time">time</option>
      <option value="timestamp">timestamp</option>
      <option value="hidden">hidden</option>
      <option value="password">password (plain)</option>
      <option value="passwordE">password (encrypted)</option>
      <option value="color">color</option>
      <option value="tinyint">1 digit number</option>
    </select>
  </div>

  <div class="form-group" id="opts" style="display:none;">
    <label for="">How do you want to create <span id="fieldType"></span> options the options for this field?</label>
    <select class="form-control" name="optStyle">
      <option value="" disabled selected="selected">--Select Style--</option>
      <option value="manually">Manually enter these values</option>
      <option value="database">Populate them from a database query</option>
    </select>
  </div>

  <div class="form-group">
    <label for="">Column name in DB</label>
    <input class="form-control"  type="text" name="col" value="" required>
  </div>

  <div class="form-group">
    <label for="">Label when displaying forms</label>
    <input class="form-control"  type="text" name="form_descrip" value="" required>
  </div>

  <div class="form-group">
    <label for="">Label when displaying tables(often shorter)</label>
    <input  class="form-control" type="text" name="table_descrip" value="" required>
  </div>

  <div class="form-group">
    <label for="">Required?</label>
    <select class="form-control" name="required" required>
      <option value="0">No</option>
      <option value="1">Yes</option>
    </select>
  </div>

  <div class="form-group">
    <label for="">Class</label>
    <input  class="form-control" type="text" name="field_class" value="form-control" >
  </div>

  <div class="form-group">
    <label for="">Raw HTML inside input tag</label>
    <textarea class="form-control"  name="input_html" rows="4" cols="120"></textarea>
  </div>

  <div class="form-group">
    <label for="">Order</label>
    <input  class="form-control" type="number" min="0" step ="1" name="ord" value="<?=$lastOrder?>" >
  </div>

  <input type="submit" name="create_field" value="Create Field" class="btn btn-outline-primary">
</form>
<script>
$(document).ready(function () {
  toggleOpts();

  $("#field_type").change(function () {
    toggleOpts();
  });
});

function toggleOpts() {
  if (($("#field_type").val() === "checkbox") || ($("#field_type").val() === "dropdown") || ($("#field_type").val() === "radio"))
  $("#opts").show();
  else
  $("#opts").hide();
}
</script>
