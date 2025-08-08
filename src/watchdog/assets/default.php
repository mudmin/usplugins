<?php
//Declare your functions in this format for each file
//should be an array of arrays
//note that you can also use built in javascript functions as you will see I do with
//the alert function
$availableWatchdogs = [];
$availableWatchdogs["alert"] = [
  "desc"=>"Built in javascript alert",
  "args"=>"Takes a single string of text for the alert"
  ];

$availableWatchdogs["refresh"] = [
  "desc"=>"Refreshes the current page",
  "args"=>"Does not take arguements"
  ];

$availableWatchdogs["redirect"] = [
  "desc"=>"Redirects to another page",
  "args"=>"Requires the full https://yourdomain.com link"
  ];

$availableWatchdogs["bsmessage"] = [
  "desc"=>"Shows a bootstrap message based on a JSON string. Requires a div with an id of messages on the page. class can be warning, success, or danger. Default is success. Timeout is optional, defaults to 15 seconds (15000)",
  "args"=>"{\"class\":\"warning\",\"msg\":\"This is the message\",\"timeout\":15000}"
  ];

?>
<script type="text/javascript">
  function refresh(){
    location.reload();
  }

  function redirect(data){
    window.location.href = data;
  }

  function bsmessage(data) {
    data = JSON.parse(data);
    var to = 15000;
    if(typeof data.timeout !== 'undefined'){
      to = data.timeout;
    }
    $('#messages').removeClass();
    $('#message').text("");
    $('#messages').show();
    if(data.class == "danger"){
      $('#messages').addClass("sufee-alert alert with-close alert-danger alert-dismissible fade show");
    }else if(data.class == "warning"){
      $('#messages').addClass("sufee-alert alert with-close alert-warning alert-dismissible fade show");
    }else{
      $('#messages').addClass("sufee-alert alert with-close alert-success alert-dismissible fade show");
    }
    $('#message').text(data.msg);
    $('#messages').delay(to).fadeOut('slow');
  }
</script>
