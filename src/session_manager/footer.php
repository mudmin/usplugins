<?php if($user->isLoggedIn() && $settings->session_manager==1) {?>
<script>
    $(document).ready(function() {
				setInterval(function(){
            $.ajax({
                type: "POST",
                url: "<?=$us_url_root?>usersc/plugins/session_manager/api/",
                data: {
                    action: "checkSessionStatus",
                },
                success: function(result) {
                    var resultO = JSON.parse(result);
                    if(resultO.error){
												window.location.replace("<?=$us_url_root?>users/logout.php");
                    }
                },
            });
        }, 5000);
    });
</script>
<?php } ?>
