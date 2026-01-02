<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>
<style media="screen">
  .blue{color:blue;}
</style>
<?php
include "plugin_info.php";
pluginActive($plugin_name);

$db = DB::getInstance();
$errors = [];
$successes = [];

// Get cookie expiry setting (default 1 week = 604800 seconds)
$cookie_expiry_seconds = Config::get('remember/cookie_expiry') ?: 604800;
$cookie_expiry_days = round($cookie_expiry_seconds / 86400);

// Handle form submissions
if(!empty($_POST)){
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }

  // Purge expired sessions (older than cookie expiry)
  if(!empty($_POST['purge_expired'])){
    $result = $db->query("DELETE FROM users_session WHERE created_at IS NOT NULL AND created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)", [$cookie_expiry_seconds]);
    if(!$db->error()){
      $affected = $db->count();
      $successes[] = "Purged $affected expired session(s) older than $cookie_expiry_days day(s).";
      logger($user->data()->id, "RememberMe", "Purged $affected expired sessions");
    } else {
      $errors[] = "Error purging expired sessions: " . $db->errorString();
    }
  }

  // Purge sessions older than X days
  if(!empty($_POST['purge_older_than']) && !empty($_POST['purge_days'])){
    $days = (int)$_POST['purge_days'];
    if($days > 0){
      $result = $db->query("DELETE FROM users_session WHERE created_at IS NOT NULL AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)", [$days]);
      if(!$db->error()){
        $affected = $db->count();
        $successes[] = "Purged $affected session(s) older than $days day(s).";
        logger($user->data()->id, "RememberMe", "Purged $affected sessions older than $days days");
      } else {
        $errors[] = "Error purging old sessions: " . $db->errorString();
      }
    } else {
      $errors[] = "Please enter a valid number of days.";
    }
  }

  // Purge orphaned sessions (no timestamp - legacy records)
  if(!empty($_POST['purge_orphaned'])){
    $result = $db->query("DELETE FROM users_session WHERE created_at IS NULL");
    if(!$db->error()){
      $affected = $db->count();
      $successes[] = "Purged $affected orphaned session(s) without timestamps.";
      logger($user->data()->id, "RememberMe", "Purged $affected orphaned sessions without timestamps");
    } else {
      $errors[] = "Error purging orphaned sessions: " . $db->errorString();
    }
  }

  // Purge ALL sessions
  if(!empty($_POST['purge_all'])){
    $result = $db->query("TRUNCATE TABLE users_session");
    if(!$db->error()){
      $successes[] = "Purged ALL remember-me sessions. All users will need to log in again.";
      logger($user->data()->id, "RememberMe", "Purged ALL remember-me sessions");
    } else {
      $errors[] = "Error purging all sessions: " . $db->errorString();
    }
  }
}

// Get session statistics
$total_sessions = $db->query("SELECT COUNT(*) as cnt FROM users_session")->first()->cnt ?? 0;
$orphaned_sessions = $db->query("SELECT COUNT(*) as cnt FROM users_session WHERE created_at IS NULL")->first()->cnt ?? 0;
$expired_sessions = $db->query("SELECT COUNT(*) as cnt FROM users_session WHERE created_at IS NOT NULL AND created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)", [$cookie_expiry_seconds])->first()->cnt ?? 0;
$active_sessions = $total_sessions - $orphaned_sessions - $expired_sessions;

$token = Token::generate();
?>
<div class="content mt-3">
  <div class="row">
    <div class="col-sm-12">
      <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
      <h1>Remember Me Session Management</h1>

      <?php if($errors): ?>
        <div class="alert alert-danger">
          <?php foreach($errors as $error): ?>
            <p><?= $error ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if($successes): ?>
        <div class="alert alert-success">
          <?php foreach($successes as $success): ?>
            <p><?= $success ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="card mb-4">
        <div class="card-header">
          <h5>Session Statistics</h5>
        </div>
        <div class="card-body">
          <table class="table table-bordered">
            <tr>
              <td><strong>Total Sessions</strong></td>
              <td><?= $total_sessions ?></td>
            </tr>
            <tr>
              <td><strong>Active Sessions</strong> <small>(within cookie expiry period)</small></td>
              <td><?= $active_sessions ?></td>
            </tr>
            <tr>
              <td><strong>Expired Sessions</strong> <small>(older than <?= $cookie_expiry_days ?> day(s))</small></td>
              <td><?= $expired_sessions ?></td>
            </tr>
            <tr>
              <td><strong>Orphaned Sessions</strong> <small>(no timestamp - legacy records)</small></td>
              <td><?= $orphaned_sessions ?></td>
            </tr>
          </table>
          <p class="text-muted"><small>Cookie expiry is set to <?= $cookie_expiry_days ?> day(s) (<?= number_format($cookie_expiry_seconds) ?> seconds) in users/init.php</small></p>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header">
          <h5>Purge Options</h5>
        </div>
        <div class="card-body">
          <form method="post">
            <input type="hidden" name="csrf" value="<?= $token ?>">

            <div class="mb-3">
              <button type="submit" name="purge_expired" value="1" class="btn btn-warning" onclick="return confirm('Purge all expired sessions?');">
                Purge Expired Sessions
              </button>
              <small class="text-muted">Remove sessions older than the cookie expiry (<?= $cookie_expiry_days ?> day(s))</small>
            </div>

            <div class="mb-3">
              <button type="submit" name="purge_orphaned" value="1" class="btn btn-warning" onclick="return confirm('Purge all orphaned sessions without timestamps?');">
                Purge Orphaned Sessions
              </button>
              <small class="text-muted">Remove legacy sessions that have no timestamp (created before this update)</small>
            </div>

            <hr>

            <div class="mb-3">
              <div class="input-group" style="max-width: 400px;">
                <input type="number" name="purge_days" class="form-control" placeholder="Days" min="1" value="30">
                <button type="submit" name="purge_older_than" value="1" class="btn btn-warning" onclick="return confirm('Purge sessions older than the specified days?');">
                  Purge Older Than X Days
                </button>
              </div>
            </div>

            <hr>

            <div class="mb-3">
              <button type="submit" name="purge_all" value="1" class="btn btn-danger" onclick="return confirm('WARNING: This will log out ALL users with remembered sessions. Are you sure?');">
                Purge ALL Sessions
              </button>
              <small class="text-danger">Warning: This will force all users to log in again if their session is expired!</small>
            </div>
          </form>
        </div>
      </div>

      <p>If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>.
        <br>Either way, thanks for using UserSpice!</p>
    </div> <!-- /.col -->
  </div> <!-- /.row -->
