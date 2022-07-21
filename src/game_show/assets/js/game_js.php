<script type="text/javascript">
var sounds = [];
<?php
if($gsettings->play_sounds == 1){
  foreach($sounds as $s){ ?>
    sounds["<?=$s?>"] = new Audio ("<?=$us_url_root?>usersc/plugins/game_show/assets/mp3/<?=$s?>.mp3");
    <?php
  }

}
?>

$( document ).ready(function() {
  var intervalId = window.setInterval(function(){
    checkStatus();
  }, 1000);

  function checkStatus(){
    var formData = {
    };

    $.ajax({
      type 		: 'POST',
      url 		: "<?=$us_url_root?>usersc/plugins/game_show/parsers/checkStatus.php",
      data 		: formData,
      dataType 	: 'json',
    })

    .done(function(data) {

      $.each( data, function( index, row ){
        console.log(row);
        var id = row.id;
        console.log(row.sound);
        $("#time"+id).text(row.time);
        $("#lockicon"+id).attr("src","<?=$us_url_root?>usersc/plugins/game_show/assets/images/"+row.lock);

        $("#counter"+id).text(row.counter);
        <?php if($gsettings->play_sounds == 1){ ?>
          var played = $("#sound"+id).attr("data-sound");
          if(row.sound != "" && played == "false"){

            $("#sound"+id).attr("data-sound","true");
            console.log("would play");
            sounds[row.sound].play();
          }
          <?php } //end of sound disable?>
        });
      })
    }

  });
</script>
