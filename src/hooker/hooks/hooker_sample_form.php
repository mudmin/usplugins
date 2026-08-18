<?php if (count(get_included_files()) == 1) die(); //Direct Access Not Permitted
/*
 * SAMPLE HOOK - position: form
 *
 * "form" puts your field INSIDE the form the page already has, so it gets
 * submitted along with everything else. Pair it with a "post" hook on the same
 * page to actually save the value - see hooker_sample_post.php.
 *
 * To try this pair, register both hooks on user_settings.php and add a column:
 *   ALTER TABLE users ADD nickname VARCHAR(50) NULL;
 *
 * For the current list of pages, positions and events, see
 * https://userspice.com/plugin-hooks/
 */
global $user, $db;

// Nothing to edit if nobody is logged in (this sample belongs on a logged-in page).
if (!$user->isLoggedIn()) return;

if (!$db->columnExists('users', 'nickname')) { ?>
  <div class="alert alert-warning">
    hooker_sample_form.php: run <code>ALTER TABLE users ADD nickname VARCHAR(50) NULL;</code> to try this sample.
  </div>
<?php } else { ?>
  <div class="form-group mb-3">
    <label class="form-label" for="hooker_nickname">Nickname</label>
    <input class="form-control" type="text" id="hooker_nickname" name="hooker_nickname"
           maxlength="50" value="<?= safeReturn($user->data()->nickname ?? '') ?>">
  </div>
<?php }
