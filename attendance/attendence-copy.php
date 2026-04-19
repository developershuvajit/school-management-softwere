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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>School Management Softwere</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
<?php include "../includes/preloader.php"; ?>
<div id="main-wrapper">
    <?php include "../includes/navbar.php"; ?>
    <?php include('../includes/sidebar_logic.php'); ?>

    <div class="content-body">
        <div class="container-fluid">
            <div class="row page-titles mx-0">
                <div class="col-sm-6 p-md-0"><?php include('../includes/welcome_text.php'); ?></div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-lg">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                            <h4 class="card-title mb-2 mb-sm-0">🎥 QR Attendance System</h4>
                            <div class="d-flex gap-2">
                                <button id="startButton" class="btn btn-primary">
                                    <i class="fa fa-play-circle"></i> Start Attendance
                                </button>
                                <button id="switchCamera" class="btn btn-secondary">
                                    <i class="fa fa-exchange-alt"></i> Switch Camera
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="status" class="alert alert-info text-center mb-3">
                                Click “Start Attendance” to begin scanning.
                            </div>

                            <div id="videoContainer" class="border rounded p-2 mb-3" style="display:none;">
                                <video id="video" autoplay playsinline muted style="width:100%; border-radius:10px;"></video>
                            </div>

                            <div class="border p-3 rounded bg-light">
                                <h5><i class="fa fa-list"></i> Scanned Students</h5>
                                <div id="attendanceList" style="max-height:300px; overflow-y:auto;">
                                    <p class="text-muted">No attendance yet...</p>
                                </div>
                            </div>

                            <audio id="successSound" src="../public/sounds/success.mp3" preload="auto"></audio>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "../includes/footer.php"; ?>
</div>
<?php include "../includes/js_links.php"; ?>
<script src="https://unpkg.com/jsqr/dist/jsQR.js"></script>

<script>
const video = document.getElementById('video');
const status = document.getElementById('status');
const videoContainer = document.getElementById('videoContainer');
const attendanceList = document.getElementById('attendanceList');
const successSound = document.getElementById('successSound');
const startButton = document.getElementById('startButton');
const switchCameraBtn = document.getElementById('switchCamera');

let scannedIds = new Map();
let scanning = false;
let startTime = null;
let currentFacingMode = 'user';
let stream = null;

async function startCamera(facingMode) {
    if (stream) stream.getTracks().forEach(track => track.stop());

    status.className = "alert alert-warning";
    status.innerText = `Accessing ${facingMode === 'user' ? 'front' : 'rear'} camera...`;

    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { exact: facingMode }},
            audio: false
        });
    } catch {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: facingMode },
            audio: false
        }).catch(err => {
            status.className = "alert alert-danger";
            status.innerText = 'Camera access denied!';
        });
    }

    video.srcObject = stream;
    scanning = true;
    videoContainer.style.display = 'block';
    status.className = "alert alert-success";
    status.innerText = `Camera Ready! Scan now!`;
    requestAnimationFrame(scanQRCode);
}

startButton.addEventListener('click', () => {
    startButton.disabled = true;
    startTime = new Date();
    startCamera(currentFacingMode);
});

switchCameraBtn.addEventListener('click', () => {
    currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
    if (startTime) startCamera(currentFacingMode);
});

function scanQRCode() {
    if (!scanning) return;
    if (video.videoWidth === 0) {
        requestAnimationFrame(scanQRCode);
        return;
    }

    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, canvas.width, canvas.height);

    if (code) {
        let data;
        try { data = JSON.parse(code.data); }
        catch { return requestAnimationFrame(scanQRCode); }

        const lastScan = scannedIds.get(data.id) || 0;
        if (Date.now() - lastScan > 10000) {
            scannedIds.set(data.id, Date.now());
            saveAttendance(data.id);
        }
    }
    requestAnimationFrame(scanQRCode);
}

function saveAttendance(studentId) {
    fetch('attendance_save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: studentId, start_time: startTime.toISOString() })
    })
    .then(res => res.json())
    .then(data => {
        status.className = data.success ? "alert alert-success" : "alert alert-warning";
        status.innerText = data.message;

        if (data.success) {
            successSound.currentTime = 0;
            successSound.play();
            addToList(data);
        }
    });
}

 function addToList(data) {
    const item = document.createElement('div');
    item.className = 'd-flex align-items-center border-bottom py-3';
    item.style.gap = "15px";

    item.innerHTML = `
        <div style="width:120px; height:120px;">
            <img src="${data.photo}" class="rounded-circle border shadow-sm" 
            style="width:100%; height:100%; object-fit:cover;">
        </div>

        <div style="flex-grow:1;">
            <div class="fw-bold text-dark" style="font-size:16px; margin-bottom:2px;">
                ${data.name}
            </div>
            <div style="font-size:13px;">
                <span class="badge bg-${data.status === 'Present' ? 'success' : 'warning'} text-dark">
                    ${data.status}
                </span>
                <span class="text-muted ms-2">
                    ⏱ ${data.time}
                </span>
            </div>
        </div>
    `;

    attendanceList.appendChild(item);
    attendanceList.scrollTop = attendanceList.scrollHeight;
}

</script>
</body>
</html>
