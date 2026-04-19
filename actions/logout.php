<?php
session_start();
session_unset();
session_destroy();
?>
<script>
function playSound(src) {
    var audio = new Audio(src);
    audio.play();
}
playSound('../public/sounds/failed.mp3');
alert('You have successfully logged out.');
window.location.href = '../login.php';
</script>
