<?php if (count(get_included_files()) == 1) die(); //Direct Access Not Permitted
/*
 * SAMPLE HOOK - position: body
 *
 * "body" drops your output into the visible part of the page, so this is the
 * position you want for anything the user should actually see.
 *
 * Always run your output through safeReturn() before printing it.
 *
 * For the current list of pages, positions and events, see
 * https://userspice.com/plugin-hooks/
 */
$hookerGreeting = $GLOBALS['hooker_sample_greeting'] ?? 'Hello from hooker_sample_body.php!';
?>
<div class="alert alert-info">
  <i class="fa fa-anchor"></i> <?= safeReturn($hookerGreeting) ?>
</div>
