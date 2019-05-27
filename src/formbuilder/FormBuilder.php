<?php
require_once '../../../users/init.php';
$db = DB::getInstance();
if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>
<?php
include "plugin_info.php";
pluginActive($plugin_name);
require_once $abs_us_root.$us_url_root.'usersc/plugins/formbuilder/assets/fb_createform.php';?>
<!DOCTYPE html>
<html>
    <head>
        <title>Form Builder - <?=$database?></title>
        <?php
        require_once $abs_us_root.$us_url_root.'usersc/plugins/formbuilder/assets/bootstrap4.php';
        // require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
        ?>
        <script type="text/JavaScript" src="<?=$us_url_root?>usersc/plugins/formbuilder/assist/formbuilder.js"></script>
    </head>
    <body>
        <?php require_once $abs_us_root.$us_url_root.'usersc/plugins/formbuilder/assets/fb_nav_bar.php';?>
        <form name="FieldBuilder" action="<?=$_SERVER['REQUEST_URI']?>" method="POST">
            <input type="hidden" name="csrf" value="<?=$token?>" />
            <h3 class="text-center"><?=$database?></h3>
            <br>
            <div class="container">
                <div class="row">
                    <div class="col">
                        <h4 class="text-center">Form Information</h4>
                        <div class="form-group">
                            <label for="name">Order:</label>
                            <input type="number" name="fb_order" id="fb_order" class="form-control" step="1"<?php if(isset($fb_order)){echo ' value="'.$fb_order.'"';}?> />
                        </div>
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" class="form-control" name="name" id="name" <?php if(isset($name)){echo ' value="'.$name.'"';}?> required />
                        </div>
                        <div class="form-group">
                            <label for="label">Input Type:</label>
                            <select name="field_type" class="form-control" onchange="js_input_type(this.value)">
                                <option selected disabled>--Select One--</option>
                                <option <?php if(isset($field_type)){if($field_type == "text"){echo "selected";}}?> value="text">Text</option>
                                <option <?php if(isset($field_type)){if($field_type == "password"){echo "selected";}}?> value="password">Password</option>
                                <option <?php if(isset($field_type)){if($field_type == "e_password"){echo "selected";}}?> value="password">Encrypted Password</option>
                                <option <?php if(isset($field_type)){if($field_type == "number"){echo "selected";}}?> value="number">Number</option>
                                <option <?php if(isset($field_type)){if($field_type == "tel"){echo "selected";}}?> value="tel">Phone Number</option>
                                <option <?php if(isset($field_type)){if($field_type == "time"){echo "selected";}}?> value="time">Time</option>
                                <option <?php if(isset($field_type)){if($field_type == "date"){echo "selected";}}?> value="date">Date</option>
                                <option <?php if(isset($field_type)){if($field_type == "datetime"){echo "selected";}}?> value="datetime">Datetime</option>
                                <option <?php if(isset($field_type)){if($field_type == "textarea"){echo "selected";}}?> value="textarea">Textarea</option>
                                <option <?php if(isset($field_type)){if($field_type == "dropdown"){echo "selected";}}?> value="dropdown">Dropdown</option>
                                <option <?php if(isset($field_type)){if($field_type == "checkbox"){echo "selected";}}?> value="checkbox">Checkbox</option>
                                <option <?php if(isset($field_type)){if($field_type == "radio"){echo "selected";}}?> value="radio">Radio</option>
                                <option <?php if(isset($field_type)){if($field_type == "hidden"){echo "selected";}}?> value="hidden">Hidden</option>
                                <option <?php if(isset($field_type)){if($field_type == "hidden_timestamp"){echo "selected";}}?> value="hidden_timestamp">TimeStamp (Hidden)</option>
                                <?php
                                /*
                                    ******************************
                                    This option has been disabled.
                                    No Validation created yet.
                                    ******************************

                                echo '<option '.if(isset($field_type)){if($field_type == "file"){echo "selected";}}.' value="file">File</option>';

                                 */
                                ?>
                            </select>
                        </div>
                        <div id="type_insert">
                            <?php
                            if(isset($_GET['id']) || isset($field_type)){
                                if($field_type != 'hidden'){
                                    ?>
                                    <div class="form-group">
                                        <label for="label">Number of Div's?</label>
                                        <select name="div_number" class="form-control" onchange="js_div_number(this.value)">
                                            <option <?php if(isset($div_number)){if($div_number == "1"){echo "selected";}}?> value="1">1</option>
                                            <option <?php if(isset($div_number)){if($div_number == "2"){echo "selected";}}?> value="2">2</option>
                                        </select>
                                    </div>
                                    <div id="div_number">
                                        <?php if($div_number == "2"){ ?>
                                        <div class="form-group">
                                            <label for="label">div Class 1:</label>
                                            <input type="text" class="form-control" name="div_class1" id="div_class1" <?php if(isset($div_class1)){echo 'value="'.$div_class1.'"';}else{'value="form-group"';}?> required />
                                        </div>
                                        <div class="form-group">
                                            <label for="label">div Class 2:</label>
                                            <input type="text" class="form-control" name="div_class2" id="div_class2" <?php if(isset($div_class2)){echo 'value="'.$div_class2.'"';}else{'value="form-group"';}?> required />
                                        </div>
                                        <?php } else { ?>
                                        <div class="form-group">
                                            <label for="label">div Class:</label>
                                            <input type="text" class="form-control" name="div_class2" id="div_class2" <?php if(isset($div_class2)){echo 'value="'.$div_class2.'"';}else{'value="form-group"';}?> required />
                                        </div>
                                        <?php } ?>
                                    </div>
                                    <div class="form-group">
                                        <label for="label">Label:</label>
                                        <input type="text" class="form-control" name="label" id="label" <?php if(isset($label)){echo 'value="'.$label.'"';}?> required />
                                    </div>
                                    <div class="form-group">
                                        <label for="label">Label Class:</label>
                                        <input type="text" class="form-control" name="label_class" id="label_class" <?php if(isset($label_class)){echo 'value="'.$label_class.'"';}else{'value="form-group"';}?> required />
                                    </div>
                                    <div class="form-group">
                                        <label for="label">Input Class:</label>
                                        <input type="text" class="form-control" name="input_class" id="input_class" <?php if(isset($input_class)){echo 'value="'.$input_class.'"';}else{'value="form-control"';}?> required />
                                    </div>
                                    <?php
                                    if($field_type == 'text' || $field_type == 'password' || $field_type == 'e_password' || $field_type == 'time' || $field_type == 'date' || $field_type == 'tel' || $field_type == 'datetime' || $field_type == 'textarea'){
                                        $field_type;
                                        ?>
                                        <div class="form-group">
                                            <label for="label">Type HTML:</label>
                                            <input type="text" class="form-control" name="input_html" id="input_html" <?php if(isset($input_html)){echo 'value="'.$input_html.'"';}else{'value=""';}?> />
                                        </div>
                                        <div class="form-group">
                                            <label for="label">Type Required:</label>
                                            <select name="required" class="form-control" onchange="form(this.value)">
                                                <option value="" selected>No</option>
                                                <option <?php if(isset($required)){if($required=="1"){echo "selected";}}?> value="1">Yes</option>
                                            </select>
                                        </div>
                                        <?php
                                    } elseif ($field_type == 'number') {
                                        ?>
                                        <div class="form-group">
                                            <label for="label">Type Steps:</label>
                                            <input type="number" class="form-control" name="input_step" id="input_step" <?php if(isset($input_step)){echo 'value="'.$input_step.'"';}else{'value=""';}?> />
                                        </div>
                                        <div class="form-group">
                                            <label for="label">Type HTML:</label>
                                            <input type="text" class="form-control" name="input_html" id="input_html" <?php if(isset($input_html)){echo 'value="'.$input_html.'"';}else{'value=""';}?> />
                                        </div>
                                        <div class="form-group">
                                            <label for="label">Type Required:</label>
                                            <select name="required" class="form-control" onchange="form(this.value)">
                                                <option value="" selected>No</option>
                                                <option <?php if(isset($required)){if($required=="1"){echo "selected";}}?> value="1">Yes</option>
                                            </select>
                                        </div>
                                        <?php
                                    } elseif ($field_type == 'dropdown' || $field_type == 'checkbox' || $field_type == 'radio') {
                                        ?>
                                        <div class="form-group">
                                            <label for="label">Type HTML:</label>
                                            <input type="text" class="form-control" name="input_html" id="input_html" <?php if(isset($input_html)){echo 'value="'.$input_html.'"';}else{'value=""';}?> />
                                        </div>
                                        <div class="form-group">
                                            <label for="label">Type Required:</label>
                                            <select name="required" class="form-control" >
                                                <option value="" selected>No</option>
                                                <option <?php if(isset($required)){if($required=="1"){echo "selected";}}?> value="1">Yes</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="label">Style:</label>
                                            <select name="database_type" class="form-control" onchange="js_input_style(this.value)" required>
                                                <option selected disabled>--Select One--</option>
                                                <option <?php if(isset($databasetype)){if($databasetype == 'manual'){echo 'selected';}}?> value="manual">Manual</option>
                                                <option <?php if(isset($databasetype)){if($databasetype == 'database'){echo 'selected';}}?> value="database">Database</option>
                                            </select>
                                        </div>
                                        <div id="js_input_style">
                                            <?php
                                            if($databasetype == 'manual'){
                                            ?>
                                            <table id="table_database">
                                                <thead>
                                                    <tr>
                                                        <td class="text-center">ID</td>
                                                        <td class="text-center">Value</td>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    <?php
                                                    foreach($database_design as $data) {
                                                        ?>
                                                    <tr>
                                                        <td><input type="text" class="form-control" name="databaseid[]" id="databaseid" <?php if(!empty($data->id)){echo 'value="'.$data->id.'"';}?> /></td>
                                                        <td><input type="text" class="form-control" name="databasevalue[]" id="databasevalue" <?php if(!empty($data->value)){echo 'value="'.$data->value.'"';}?> /></td>
                                                        <td><input type="button" class="btn btn-danger" value="Delete" onclick="deleteRow(this)"></td>
                                                    </tr>
                                                    <?php
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                            <br>
                                            <input type="button" value="Add Row" class="btn btn-primary" onclick="database_addrow()">
                                            <br><br>

                                                <?php
                                            }
                                            if($databasetype == 'database'){
                                                ?>
                                                <div class="form-group">
                                                    <label for="label">Database Name:</label>
                                                    <input type="text" class="form-control" name="database_name" id="database_name" <?php if(isset($database_name)){echo 'value="'.$database_name.'"';}?> required />
                                                </div>
                                                <div class="form-group">
                                                    <label for="label">Value:</label>
                                                    <input type="text" class="form-control" name="database_value" id="database_value" <?php if(isset($database_value)){echo 'value="'.$database_value.'"';}?> required />
                                                </div>
                                                <div class="form-group">
                                                    <label for="label">WHERE:</label>
                                                    <input type="text" class="form-control" name="database_where" id="database_where" <?php if(isset($database_where)){echo 'value="'.$database_where.'"';}?> />
                                                </div>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    }
                                } ?>
                                <input type="submit" class="btn btn-primary" value="Submit" />
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col">
                        <div id="type_requirements">
                            <h4 class="text-center">Optional Requirements</h4>
                            <table border="0">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Requirement</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select name="required_min_check">
                                                <option value="false">OFF</option>
                                                <option <?php if(isset($required_min_check)){if($required_min_check=="ON"){echo "selected";}}?> value="ON">ON</option>
                                            </select>
                                        <td>Minimum # of Characters</td>
                                        <td>
                                            <input type="number" name="required_min_value" class="form-control" step="1" <?php if(isset($required_min_value)){echo 'value="'.$required_min_value.'"';}?> />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select name="required_max_check">
                                                    <option value="false">OFF</option>
                                                    <option <?php if(isset($required_max_check)){if($required_max_check=="ON"){echo "selected";}}?> value="ON">ON</option>
                                            </select>
                                        <td>Maximum # of Characters</td>
                                        <td>
                                            <input type="number" name="required_max_value" class="form-control" step="1" <?php if(isset($required_max_value)){echo 'value="'.$required_max_value.'"';}?> />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select name="required_is_numeric_check">
                                                <option value="false">OFF</option>
                                                <option <?php if(isset($required_is_numeric_check)){if($required_is_numeric_check=="ON"){echo "selected";}}?> value="ON">ON</option>
                                            </select>
                                        <td>Must be a number</td>
                                        <td>
                                            <select name="required_is_numeric_value">
                                                <option value="true">TRUE</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select name="required_valid_email_check">
                                                <option value="false">OFF</option>
                                                <option <?php if(isset($required_valid_email_check)){if($required_valid_email_check=="ON"){echo "selected";}}?> value=ON">ON</option>
                                            </select>
                                        <td>Must be a valid email</td>
                                        <td>
                                            <select name="required_valid_email_value">
                                                <option value="true">TRUE</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select name="required_greaterthan_check">
                                                <option value="false">OFF</option>
                                                <option <?php if(isset($required_greaterthan_check)){if($required_greaterthan_check=="ON"){echo "selected";}}?> value="ON">ON</option>
                                            </select>
                                        <td>Must be a number less than</td>
                                        <td>
                                            <input type="number" name="required_greaterthan_value" <?php if(isset($required_greaterthan_value)){echo 'value="'.$required_greaterthan_value.'"';}?> class="form-control" step="0.000001" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select name="required_lessthan_check">
                                                <option value="false">OFF</option>
                                                <option <?php if(isset($required_lessthan_check)){if($required_lessthan_check=="ON"){echo "selected";}}?> value="ON">ON</option>
                                            </select>
                                        <td>Must be a number greater than</td>
                                        <td>
                                            <input type="number" name="required_lessthan_value" <?php if(isset($required_lessthan_value)){echo 'value="'.$required_lessthan_value.'"';}?> class="form-control" step="0.000001" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select name="required_greaterthanequal_check">
                                                <option value="false">OFF</option>
                                                <option <?php if(isset($required_greaterthanequal_check)){if($required_greaterthanequal_check=="ON"){echo "selected";}}?> value="ON">ON</option>
                                            </select>
                                        <td>Must be a number less than or equal to</td>
                                        <td>
                                            <input type="number" name="required_greaterthanequal_value" <?php if(isset($required_greaterthanequal_value)){echo 'value="'.$required_greaterthanequal_value.'"';}?> class="form-control" step="0.000001" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select name="required_lessthanequal_check">
                                                <option value="false">OFF</option>
                                                <option <?php if(isset($required_lessthanequal_check)){if($required_lessthanequal_check=="ON"){echo "selected";}}?> value="ON">ON</option>
                                            </select>
                                        <td>Must be a number greater than or equal to</td>
                                        <td>
                                            <input type="number" name="required_lessthanequal_value" <?php if(isset($required_lessthanequal_value)){echo 'value="'.$required_lessthanequal_value.'"';}?> class="form-control" step="0.000001" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select name="required_notequal_check">
                                                <option value="false">OFF</option>
                                                <option <?php if(isset($required_notequal_check)){if($required_notequal_check=="ON"){echo "selected";}}?> value="ON">ON</option>
                                            </select>
                                        <td>Must no be equal to</td>
                                        <td>
                                            <input type="number" name="required_notequal_value" <?php if(isset($required_notequal_value)){echo 'value="'.$required_notequal_value.'"';}?> class="form-control" step="0.000001" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select name="required_equal_check">
                                                <option value="false">OFF</option>
                                                <option <?php if(isset($required_equal_check)){if($required_equal_check=="ON"){echo "selected";}}?> value="ON">ON</option>
                                            </select>
                                        <td>Must be equal to</td>
                                        <td>
                                            <input type="number" name="required_equal_value" <?php if(isset($required_equal_value)){echo 'value="'.$required_equal_value.'"';}?> class="form-control" step="0.000001" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select name="required_is_integer_check">
                                                <option value="false">OFF</option>
                                                <option <?php if(isset($required_is_integer_check)){if($required_is_integer_check=="ON"){echo "selected";}}?> value="ON">ON</option>
                                            </select>
                                        <td>Must be an integer</td>
                                        <td>
                                            <select name="required_is_integer_value">
                                                <option value="true">TRUE</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select name="required_is_timezone_check">
                                                <option value="false">OFF</option>
                                                <option <?php if(isset($required_is_timezone_check)){if($required_is_timezone_check=="ON"){echo "selected";}}?> value="ON">ON</option>
                                            </select>
                                        <td>Must be a valid timezone name</td>
                                        <td>
                                            <select name="required_is_timezone_value">
                                                <option value="true">TRUE</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select name="required_is_datetime_check">
                                                <option value="false">OFF</option>
                                                <option <?php if(isset($required_is_datetime_check)){if($required_is_datetime_check=="ON"){echo "selected";}}?> value="ON">ON</option>
                                            </select>
                                        <td>Must be a valid DateTime</td>
                                        <td>
                                            <select name="required_is_datetime_value">
                                                <option value="true">TRUE</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select name="is_valid_north_american_phone_check">
                                                <option value="false">OFF</option>
                                                <option <?php if(isset($is_valid_north_american_phone_check)){if($is_valid_north_american_phone_check=="ON"){echo "selected";}}?> value="ON">ON</option>
                                            </select>
                                        <td>Must be a valid Phone Number</td>
                                        <td>
                                            <select name="is_valid_north_american_phone">
                                                <option value="true">TRUE</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="col-sm-10, col-md-10, col-lg-10">
            <br />
            <br />
            <br />
            <br />
        </div>
    </body>
</html>
