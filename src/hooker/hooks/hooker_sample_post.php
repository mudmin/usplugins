<?php
if (count(get_included_files()) == 1) die(); //Direct Access Not Permitted
/*
 * SAMPLE HOOK - position: post
 *
 * "post" runs while the page is processing a form submission, after the page has
 * already checked the CSRF token for you. This is where you save the field that
 * hooker_sample_form.php added - one field, one update, nothing fancy.
 *
 * To try this pair, register both hooks on user_settings.php and add a column:
 *   ALTER TABLE users ADD nickname VARCHAR(50) NULL;
 *
 * For the current list of pages, positions and events, see
 * https://userspice.com/plugin-hooks/
 */
global $user, $db;

if ($user->isLoggedIn() && isset($_POST['hooker_nickname']) && $db->columnExists('users', 'nickname')) {
    $hookerNickname = substr(trim(Input::get('hooker_nickname')), 0, 50);

    if ($db->update('users', $user->data()->id, ['nickname' => $hookerNickname])) {
        logger($user->data()->id, 'Hooker', 'Nickname updated by hooker_sample_post.php');
    } else {
        usError('Your nickname could not be saved.');
    }
}
