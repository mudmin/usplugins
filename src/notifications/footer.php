<?php
//Please don't load code on the footer of every page if you don't need it on the footer of every page.
//bold("<br>Demo Footer Loaded");
if($settings->notifications == 1){
  if($currentPage != "admin.php"){
  // $getNotifQ = $db->query("SELECT * FROM notifications WHERE user_id = ? AND is_archived = 0 ORDER BY id DESC",[$user->data()->id]);
   $getNotifQ = $db->query("SELECT * FROM notifications WHERE user_id = ? AND is_archived = 0 ORDER BY id DESC",[2]);
  $getNotifC = $getNotifQ->count();
  if($getNotifC > 0){
    $getNotif = $getNotifQ->first();
    if($getNotif->is_read == 0){
      $fields = array(
        'is_read'=>1,
        'date_read'=>date("Y-m-d H:i:s"),
      );
      $db->update("notifications",$getNotif->id,$fields);
    }
    ?>
<script type="text/javascript">
var data = [];
data['class'] = "<?=$getNotif->class?>";
data['msg'] = "<?=$getNotif->message?>";
if(data['class'] == ""){
  data['class'] = "success";
}

messages(data);
function messages(data) {
  $('#messages').removeClass();
  $('#message').text("");
  $('#messages').show();
  $('#messages').addClass("sufee-alert alert with-close alert-dismissible fade show");
  $('#messages').addClass("alert-"+data['class']);
  $('#message').text(data['msg']);

}
$('#messages').on('closed.bs.alert', function () {
  $.ajax({
      type: "POST",
      url: "<?=$us_url_root?>usersc/plugins/notifications/assets/dismiss.php",
      data: {
          notif: "<?=$getNotif->id?>"
      }
  });
})
</script>
<?php
}
}
}
?>
