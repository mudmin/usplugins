<?php if (!in_array($user->data()->id, $master_account)) {
  Redirect::to($us_url_root . 'users/admin.php');
} //only allow master accounts to manage plugins!
?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
if (!empty($_POST)) {
  if (!Token::check(Input::get('csrf'))) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
  }

  // Handle ruleset add/edit
  if(Input::get('action') == 'add_ruleset') {
    $fields = array(
      'name' => Input::get('ruleset_name'),
      'going_out_round_1' => Input::get('going_out_round_1'),
      'going_out_round_2' => Input::get('going_out_round_2'),
      'going_out_round_3' => Input::get('going_out_round_3'),
      'going_out_round_4' => Input::get('going_out_round_4'),
      'black_book_points' => Input::get('black_book_points'),
      'red_book_points' => Input::get('red_book_points'),
      'is_default' => Input::get('is_default') == '1' ? 1 : 0
    );

    // If setting as default, unset all other defaults
    if($fields['is_default'] == 1) {
      $db->query("UPDATE plg_handfoot_rulesets SET is_default = 0");
    }

    $db->insert('plg_handfoot_rulesets', $fields);
    if(!$db->error()) {
      $successes[] = "Ruleset added successfully!";
    } else {
      $errors[] = "Failed to add ruleset.";
    }
  }

  if(Input::get('action') == 'edit_ruleset') {
    $ruleset_id = Input::get('ruleset_id');
    $fields = array(
      'name' => Input::get('ruleset_name'),
      'going_out_round_1' => Input::get('going_out_round_1'),
      'going_out_round_2' => Input::get('going_out_round_2'),
      'going_out_round_3' => Input::get('going_out_round_3'),
      'going_out_round_4' => Input::get('going_out_round_4'),
      'black_book_points' => Input::get('black_book_points'),
      'red_book_points' => Input::get('red_book_points'),
      'is_default' => Input::get('is_default') == '1' ? 1 : 0
    );

    // If setting as default, unset all other defaults
    if($fields['is_default'] == 1) {
      $db->query("UPDATE plg_handfoot_rulesets SET is_default = 0");
    }

    $db->update('plg_handfoot_rulesets', $ruleset_id, $fields);
    if(!$db->error()) {
      $successes[] = "Ruleset updated successfully!";
    } else {
      $errors[] = "Failed to update ruleset.";
    }
  }

  if(Input::get('action') == 'delete_ruleset') {
    $ruleset_id = Input::get('ruleset_id');
    $db->query("DELETE FROM plg_handfoot_rulesets WHERE id = ?", array($ruleset_id));
    if(!$db->error()) {
      $successes[] = "Ruleset deleted successfully!";
    } else {
      $errors[] = "Failed to delete ruleset.";
    }
  }
}

// Get all rulesets
$rulesets = $db->query("SELECT * FROM plg_handfoot_rulesets ORDER BY is_default DESC, name ASC")->results();
$editRuleset = null;
if(isset($_GET['edit'])) {
  $editRuleset = $db->query("SELECT * FROM plg_handfoot_rulesets WHERE id = ?", array($_GET['edit']))->first();
}
?>

<div class="content mt-3">
  <div class="row">
    <div class="col-12">
      <a href="<?= $us_url_root ?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
      <h1>Hand and Foot Scoring Plugin</h1>
      <p>Configure rulesets for Hand and Foot scoring.</p>

      <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
          <?php foreach($errors as $error): ?>
            <p><?= $error ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if(!empty($successes)): ?>
        <div class="alert alert-success">
          <?php foreach($successes as $success): ?>
            <p><?= $success ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="row mt-4">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="h5 mb-0"><?= $editRuleset ? 'Edit Ruleset' : 'Add New Ruleset' ?></h3>
            </div>
            <div class="card-body">
              <form method="post">
                <?= tokenHere(); ?>
                <input type="hidden" name="action" value="<?= $editRuleset ? 'edit_ruleset' : 'add_ruleset' ?>">
                <?php if($editRuleset): ?>
                  <input type="hidden" name="ruleset_id" value="<?= $editRuleset->id ?>">
                <?php endif; ?>

                <div class="mb-3">
                  <label for="ruleset_name" class="form-label">Ruleset Name</label>
                  <input type="text" class="form-control" id="ruleset_name" name="ruleset_name"
                         value="<?= $editRuleset ? $editRuleset->name : '' ?>" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Going Out Points by Round</label>
                  <div class="row">
                    <div class="col-6 mb-2">
                      <label for="going_out_round_1" class="form-label">Round 1</label>
                      <input type="number" class="form-control" id="going_out_round_1" name="going_out_round_1"
                             value="<?= $editRuleset ? $editRuleset->going_out_round_1 : 50 ?>" required>
                    </div>
                    <div class="col-6 mb-2">
                      <label for="going_out_round_2" class="form-label">Round 2</label>
                      <input type="number" class="form-control" id="going_out_round_2" name="going_out_round_2"
                             value="<?= $editRuleset ? $editRuleset->going_out_round_2 : 100 ?>" required>
                    </div>
                    <div class="col-6">
                      <label for="going_out_round_3" class="form-label">Round 3</label>
                      <input type="number" class="form-control" id="going_out_round_3" name="going_out_round_3"
                             value="<?= $editRuleset ? $editRuleset->going_out_round_3 : 150 ?>" required>
                    </div>
                    <div class="col-6">
                      <label for="going_out_round_4" class="form-label">Round 4</label>
                      <input type="number" class="form-control" id="going_out_round_4" name="going_out_round_4"
                             value="<?= $editRuleset ? $editRuleset->going_out_round_4 : 200 ?>" required>
                    </div>
                  </div>
                </div>

                <div class="mb-3">
                  <label for="black_book_points" class="form-label">Black Book Points</label>
                  <input type="number" class="form-control" id="black_book_points" name="black_book_points"
                         value="<?= $editRuleset ? $editRuleset->black_book_points : 300 ?>" required>
                </div>

                <div class="mb-3">
                  <label for="red_book_points" class="form-label">Red Book Points</label>
                  <input type="number" class="form-control" id="red_book_points" name="red_book_points"
                         value="<?= $editRuleset ? $editRuleset->red_book_points : 500 ?>" required>
                </div>

                <div class="mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1"
                           <?= ($editRuleset && $editRuleset->is_default) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_default">
                      Set as default ruleset
                    </label>
                  </div>
                </div>

                <button type="submit" class="btn btn-primary">
                  <?= $editRuleset ? 'Update Ruleset' : 'Add Ruleset' ?>
                </button>
                <?php if($editRuleset): ?>
                  <a href="<?= $us_url_root ?>users/admin.php?view=plugins&plugin=handfoot&err=configure"
                     class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
              </form>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="h5 mb-0">Existing Rulesets</h3>
            </div>
            <div class="card-body">
              <?php if(empty($rulesets)): ?>
                <p class="text-muted">No rulesets found.</p>
              <?php else: ?>
                <div class="list-group">
                  <?php foreach($rulesets as $ruleset): ?>
                    <div class="list-group-item">
                      <div class="row">
                        <div class="col">
                          <strong><?= $ruleset->name ?></strong>
                          <?php if($ruleset->is_default): ?>
                            <span class="badge bg-primary">Default</span>
                          <?php endif; ?>
                          <br>
                          <small class="text-muted">
                            Going Out: <?= $ruleset->going_out_round_1 ?>/<?= $ruleset->going_out_round_2 ?>/<?= $ruleset->going_out_round_3 ?>/<?= $ruleset->going_out_round_4 ?>
                            | Black: <?= $ruleset->black_book_points ?> | Red: <?= $ruleset->red_book_points ?>
                          </small>
                        </div>
                        <div class="col-auto">
                          <a href="<?= $us_url_root ?>users/admin.php?view=plugins&plugin=handfoot&err=configure&edit=<?= $ruleset->id ?>"
                             class="btn btn-sm btn-primary">Edit</a>
                          <form method="post" style="display: inline;">
                            <?= tokenHere(); ?>
                            <input type="hidden" name="action" value="delete_ruleset">
                            <input type="hidden" name="ruleset_id" value="<?= $ruleset->id ?>">
                            <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this ruleset?')">Delete</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="h5 mb-0">How to Use</h3>
            </div>
            <div class="card-body">
              <p>To use the Hand and Foot scoring system:</p>
              <ol>
                <li>Create and configure your rulesets above</li>
                <li>Include the game in any page using:
                  <pre class="bg-light p-2 mt-2"><code>&lt;?php
$ruleset = 1; // or the ID of your chosen ruleset
require_once $abs_us_root . $us_url_root . "usersc/plugins/handfoot/game.php";
?&gt;</code></pre>
                </li>
                <li>Players enter their names, and the scorekeeper walks through each round</li>
                <li>Select the winner first, then record everyone's books and card points</li>
              </ol>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Do not close the content mt-3 div in this file -->
