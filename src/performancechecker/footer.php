<?php
//Please don't load code on the footer of every page if you don't need it on the footer of every page.
//bold("<br>Performance Checker Footer Loaded");
$pluginQueryCounter = $db->getQueryCount();
$mem_usage = memory_get_usage();
$mem_peak = memory_get_peak_usage();
if (!isset($GLOBALS['userspice_nonce'])) {
    $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
}
?>
<script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
window.onload = function () {
	var loadTime = window.performance.timing.domContentLoadedEventEnd-window.performance.timing.navigationStart;
  var pluginQueryCounter = "<?=$pluginQueryCounter?>";
	var peak = "<?=$mem_peak?>";
	var usage = "<?=$mem_usage?>";
	peak = peak/1024;
	peak = peak.toFixed(2);
	usage = usage/1024;
	usage = usage.toFixed(2);
	// console.log(peak);
	// console.log(usage);
  // console.log('Page load time is '+ loadTime);

  $("#pluginPerformanceChecker").html(loadTime+" ms, "+pluginQueryCounter+" db queries. "+usage+"kb Current Memory ("+peak+"kb peak)");
}
</script>
