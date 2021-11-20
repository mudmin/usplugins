<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $db,$user;

$remember = (Input::get('remember') === 'on') ? true : false;

if ($remember) {
    $hash = Hash::unique();
    $hashCheck = $db->get('users_session', ['user_id', '=', $user->data()->id]);

    $db->insert('users_session', [
        'user_id' => $user->data()->id,
        'hash' => $hash,
        'uagent' => Session::uagent_no_version(),
    ]);

    Cookie::put(Config::get('remember/cookie_name'), $hash, Config::get('remember/cookie_expiry'));

}
