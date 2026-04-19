<?php
// includes/alert_helper.php

function sweetAlert($title, $text, $icon, $redirect = null) {
    // Set sound based on alert type
    switch ($icon) {
        case 'success':
            $sound = "../public/sounds/success.mp3";
            break;
        case 'error':
        case 'warning':
            $sound = "../public/sounds/failed.mp3";
            break;
        default:
            $sound = null;
            break;
    }

    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Play sound (if file exists)
        " . ($sound ? "new Audio('$sound').play();" : "") . "
        
        Swal.fire({
            title: '$title',
            text: '$text',
            icon: '$icon',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                " . ($redirect ? "window.location.href='$redirect';" : "") . "
            }
        });
    });
    </script>";
}
?>
