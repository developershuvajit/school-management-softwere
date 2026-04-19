<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

include('../config/database.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Teacher Attendance OUT</title>

<link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
<link href="../public/css/style.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://unpkg.com/jsqr/dist/jsQR.js"></script>
</head>

<body>

<div class="container mt-4">

<div class="card shadow-lg">
<div class="card-header d-flex justify-content-between align-items-center">
    <h4>👩‍🏫 QR Teacher Attendance OUT</h4>

    <div>
        <button id="startButton" class="btn btn-danger">
            <i class="fa fa-play"></i> Start Scan
        </button>

        <button id="stopButton" class="btn btn-dark">
            <i class="fa fa-stop"></i> Stop
        </button>

        <button id="switchCamera" class="btn btn-secondary">
            <i class="fa fa-exchange-alt"></i>
        </button>
    </div>
</div>

<div class="card-body">

<div id="status" class="alert alert-info text-center">
Click Start to scan QR
</div>

<div id="videoContainer" style="display:none;">
    <video id="video" autoplay playsinline muted style="width:100%; border-radius:10px;"></video>
</div>

<hr>

<h5>Teacher Checkout List</h5>
<div id="attendanceList" style="max-height:300px; overflow-y:auto;">
<p class="text-muted">No checkout yet</p>
</div>

<audio id="successSound" src="../public/sounds/success.mp3"></audio>

</div>
</div>

</div>

<script>

const video = document.getElementById('video');
const statusBox = document.getElementById('status');
const videoContainer = document.getElementById('videoContainer');
const attendanceList = document.getElementById('attendanceList');
const successSound = document.getElementById('successSound');
const startButton = document.getElementById('startButton');
const stopButton = document.getElementById('stopButton');
const switchCameraBtn = document.getElementById('switchCamera');

let stream = null;
let scanning = false;
let currentFacingMode = 'environment';
let startTime = null;
let scannedMap = new Map();

async function startCamera() {

    try {

        if (stream) stream.getTracks().forEach(track => track.stop());

        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: currentFacingMode },
            audio: false
        });

        video.srcObject = stream;
        videoContainer.style.display = 'block';
        scanning = true;
        statusBox.className = "alert alert-success";
        statusBox.innerText = "Camera Ready! Scan QR now.";

        requestAnimationFrame(scanQRCode);

    } catch (error) {
        statusBox.className = "alert alert-danger";
        statusBox.innerText = "Camera access failed!";
        console.error(error);
    }
}

function stopCamera() {
    scanning = false;
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    videoContainer.style.display = 'none';
    startButton.disabled = false;
    statusBox.className = "alert alert-warning";
    statusBox.innerText = "Camera stopped.";
}

startButton.addEventListener('click', () => {
    startButton.disabled = true;
    startTime = new Date();
    startCamera();
});

stopButton.addEventListener('click', stopCamera);

switchCameraBtn.addEventListener('click', () => {
    currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
    if (scanning) startCamera();
});

function scanQRCode() {

    if (!scanning) return;

    if (video.readyState !== video.HAVE_ENOUGH_DATA) {
        requestAnimationFrame(scanQRCode);
        return;
    }

    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');

    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, canvas.width, canvas.height);

    if (code) {

        try {

            const qrData = JSON.parse(code.data);
            const teacherId = qrData.id;

            if (!teacherId) {
                requestAnimationFrame(scanQRCode);
                return;
            }

            const lastScan = scannedMap.get(teacherId) || 0;

            if (Date.now() - lastScan > 10000) {
                scannedMap.set(teacherId, Date.now());
                saveTeacherAttendanceOut(teacherId);
            }

        } catch (e) {
            console.log("Invalid QR JSON");
        }
    }

    requestAnimationFrame(scanQRCode);
}

function saveTeacherAttendanceOut(id) {

fetch('teacher_attendance_save.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        id: id,
        start_time: startTime.toISOString()
    })
})
.then(response => response.json())
.then(data => {

    statusBox.className = data.success ? "alert alert-success" : "alert alert-warning";
    statusBox.innerText = data.message;

    if (data.success) {

        successSound.currentTime = 0;
        successSound.play();

        const item = document.createElement('div');
        item.className = "border-bottom py-2";

        item.innerHTML = `
            <strong>${data.name}</strong><br>
            <small>Checkout Time: ${data.time}</small>
        `;

        attendanceList.appendChild(item);
        attendanceList.scrollTop = attendanceList.scrollHeight;
    }
})
.catch(error => {
    console.error("Fetch error:", error);
});
}

</script>

</body>
</html>
