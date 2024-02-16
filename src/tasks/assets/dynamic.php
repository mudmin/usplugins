<?php
$admin_methods = ['settings', 'home', 'manage', 'category'];
if (file_exists($abs_us_root . $us_url_root . $plg_settings->alternate_location . '/includes/custom_functions.php')) {
    include $abs_us_root . $us_url_root . $plg_settings->alternate_location . '/includes/custom_functions.php';
}

//these can be overridden with custom functions above
require_once $abs_us_root . $us_url_root . 'usersc/plugins/tasks/assets/functions.php';
$is_task_admin = isTaskAdmin();

?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
    .hideSpan {
        display: none;
    }

    #taskModalImage {
        max-width: 100%;
        /* Limit image width to be within the container */
        max-height: 80vh;
        /* Limit image height to be less than the viewport height */
        margin: auto;
        /* Center the image horizontally */
        display: block;
        /* Ensure the image is block level to accept margin: auto */
    }

    .taskPhotoLink {
        cursor: pointer;
    }

    .slider {
        width: 100%;
        background-color: #ccc;
    }

    .slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        background-color: #007bff;
        border: 2px solid #0056b3;
        border-radius: 50%;
        cursor: pointer;
    }

    .slider::-moz-range-thumb {
        width: 20px;
        height: 20px;
        background-color: #007bff;
        border: 2px solid #0056b3;
        border-radius: 50%;
        cursor: pointer;
    }

    .select2-container .select2-selection--single {
        height: 2.3em !important;
        width: 100% !important;
    }

    .select2 {
        width: 100% !important;
    }

    #confetti-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 9999;
    }

    .confetti-piece {
        position: absolute;
        width: 10px;
        height: 15px;
        background-color: #f0f;
        opacity: 1;
        z-index: 9999;
    }

    /* Animation */
    @keyframes fall {
        to {
            transform: translateY(100vh) rotate(360deg);
            opacity: 0;
        }
    }
</style>

<script>
    function launchTaskConfetti() {
        // Dynamically create the confetti wrapper div and append it to the body
        const confettiWrapper = document.createElement('div');
        confettiWrapper.id = 'confetti-wrapper';
        document.body.appendChild(confettiWrapper);

        const confettiCount = 200; // Increased number of confetti pieces
        const animationDuration = 4000; // Extended duration in milliseconds

        // Create and animate each piece of confetti
        for (let i = 0; i < confettiCount; i++) {
            const confettiPiece = document.createElement('div');
            confettiPiece.classList.add('confetti-piece');
            confettiWrapper.appendChild(confettiPiece);

            // Enhance randomization for wider coverage and varied fall speed
            confettiPiece.style.left = `${Math.random() * 100}%`;
            confettiPiece.style.backgroundColor = `hsl(${Math.random() * 360}, 100%, 50%)`;
            confettiPiece.style.opacity = Math.random(); // Vary the opacity for a more dynamic effect
            confettiPiece.style.transform = `rotate(${Math.random() * 360}deg)`;
            const individualDuration = Math.random() * 4 + 3; // Varying speeds for each confetti piece
            confettiPiece.style.animation = `fall ${individualDuration}s linear`;

            // Clean up confetti piece after its animation ends individually
            // setTimeout(() => {
            //   confettiPiece.remove();
            // }, individualDuration * 1000); // Convert seconds to milliseconds for consistency
        }

        // Optional: Remove all confetti and the wrapper after the overall set duration to clean up
        setTimeout(() => {
            confettiWrapper.remove();
        }, animationDuration);
    }


    $(document).ready(function() {
        $(".select2").select2();

        $(".slider").on("input", function() {
            const targetId = $(this).data("target");
            const targetElement = $("#" + targetId);
            targetElement.html($(this).val());
        });

        $(".slider").each(function() {
            const targetId = $(this).data("target");
            const targetElement = $("#" + targetId);
            targetElement.html($(this).val());
        });
    });
</script>