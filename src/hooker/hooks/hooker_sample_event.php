<?php
if (count(get_included_files()) == 1) die(); //Direct Access Not Permitted
/*
 * SAMPLE HOOK - events (loginSuccess, loginFail, joinAttempt, logout, ...)
 *
 * Events aren't pages, they're moments in the code, so they only ever use the
 * "body" position. The Hooker configure screen picks that for you when you
 * choose an event instead of a page.
 *
 * This one just writes a log entry. If you'd rather stamp a column on the user,
 * swap in a $db->update() - see hooker_sample_post.php.
 *
 * For the current list of pages, positions and events, see
 * https://userspice.com/plugin-hooks/
 */
global $user;

// On a "fail" event nobody is logged in yet, so fall back to user 0.
$hookerUserId = (!empty($user) && $user->isLoggedIn()) ? $user->data()->id : 0;
logger($hookerUserId, 'Hooker', 'hooker_sample_event.php fired');
