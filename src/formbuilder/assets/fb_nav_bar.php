<?php $token = Token::generate();
$db = DB::getInstance();
?>
<div class="row bg-light">
    <br />    
</div>
<div class="row bg-light justify-content-center">
    <h2>Form Builder</h2>
</div>

<nav class="navbar navbar-expand-sm bg-light justify-content-center">

  <!-- Links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" href="<?=$us_url_root?>usersc/plugins/formbuilder/index.php">Home</a>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">Create New Form</a>
        <div class="dropdown-menu">
            <form action="<?=$_SERVER['REQUEST_URI']?>" method="post">
                <div class="form-group">
                    <input type="hidden" name="csrf" value="<?=$token?>" />
                    <label class="form-text text-center" for="newdatabase">Create New Form</label>
                    <input type="text" name='database' id='database' class="form-control" placeholder="Database Name">
                    <small class="form-text text-muted text-center">Only<br />Lower Case, Numbers<br />and Underscore only</small>
                </div>
                <button type="submit" name='database_submit' class="btn btn-block btn-outline-success">Create Form</button>
            </form>
        </div>
    </li>
    <?php
    $count = $db->query("SELECT form FROM fb_formbuilder")->count();
    if($count > 0){
        $results = $db->results();
    ?>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">Existing Form</a>
        <div class="dropdown-menu">
            <?php foreach($results as $result){ ?>
            <a class="dropdown-item" href="<?=$us_url_root?>usersc/plugins/formbuilder/FormBuilder.php?database=<?=$result->form?>"><?=$result->form?></a>
            <?php } ?>
        </div>
    </li>
    <?php
    }
    ?>
    <li class="nav-item">
        <a class="nav-link" href="<?=$us_url_root?>usersc/plugins/formbuilder/settings.php?id=1">Settings</a>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">Information</a>
        <div class="dropdown-menu">
            <a class="nav-link" href="add_field_type.php">Add Field Type</a>
            <a class="nav-link" href="https://hackerthemes.com/bootstrap-cheatsheet/" target="_blank">Bootstrap Cheat Sheet</a>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="<?=$us_url_root?>users/admin.php?view=plugins">Exit FormBuilder</a>
    </li>
  </ul>

</nav>

<p class="text-center">Please note: While the forms are designed to be filled out by the end user,<br />the forms manager is not designed to be accessible by the public.<br />Please keep it as master account only.</p>
