<?php if(count(get_included_files()) ==1) die();?>
<h3>Send a Check Payment Option</h3>
<p>We don't REALLY expect you to take payments via check. Of course you can, but in general, this payment method is available
so you can test plugins that require the payments plugin without setting up external accounts and entering credit card info.</p>
<p>There are no options to configure for this payment method, but there is a file
  called custom_instructions.php (usersc/plugins/payments/assets/check/) which should include all
  of your payment instructions. Because this is a php file you can do more than just put a text message in there.
  You can do all sorts of logic.</p>
