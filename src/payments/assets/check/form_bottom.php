<?php if(count(get_included_files()) ==1) die();
if(haltPayment('check')){die("This form of payment is disabled");}
//This is the javascript below the form and the closing form tag
?>

</form>

<script>
    // grab payment form
    var paymentForm = document.getElementById("payment-form");
    // listen for submit
    paymentForm.addEventListener("submit", processForm, false);
    // process form on submit
    function processForm(evt) {
      paymentForm.submit();
    };

  </script>
