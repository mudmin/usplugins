<?php
if (count(get_included_files()) == 1) die(); //Direct Access Not Permitted
/*
 * SAMPLE HOOK - position: pre
 *
 * "pre" runs near the top of the page, before it builds any of its own output.
 * It is a good place to look something up or set up a variable that the page
 * (or one of your other hooks) will use further down.
 *
 * Heads up: on most pages the template header has already been sent by the time
 * "pre" fires, so don't try to redirect or send headers from here.
 *
 * For the current list of pages, positions and events, see
 * https://userspice.com/plugin-hooks/
 */

// Stash a value for hooker_sample_body.php to print later.
$GLOBALS['hooker_sample_greeting'] = 'Set by hooker_sample_pre.php at ' . date('g:i a');
