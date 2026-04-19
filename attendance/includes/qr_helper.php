<?php
// attendance/includes/qr_helper.php
require_once __DIR__ . '/../../vendor/autoload.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../config/config.php'; // for SCHOOL_QR_SECRET

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * Generate and save a QR code for a student.
 * If already exists, return existing path.
 */
function generateStudentQR($studentId, $firstName, $lastName){
    global $conn;

    // Check if already exists
    $check = $conn->prepare("SELECT qr_code FROM student_qr WHERE student_id=? LIMIT 1");
    $check->bind_param("i", $studentId);
    $check->execute();
    $check->bind_result($existingQR);
    if($check->fetch()){
        $check->close();
        return $existingQR;
    }
    $check->close();

    // Make unique token (used for attendance scan validation)
    $token = hash('sha256', $studentId . '_' . SCHOOL_QR_SECRET . '_' . time());

    // QR content (can contain student ID + token)
    $qrData = json_encode([
        'sid' => $studentId,
        'token' => $token,
    ]);

    // QR generation options
    $options = new QROptions([
        'eccLevel' => QRCode::ECC_L,
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'scale' => 6,
    ]);

    // Path to save
    $saveDir = __DIR__ . '/../assets/qrcodes/';
    if(!is_dir($saveDir)) mkdir($saveDir, 0777, true);
    $fileName = 'qr_' . $studentId . '_' . time() . '.png';
    $filePath = $saveDir . $fileName;

    // Generate & save QR
    (new QRCode($options))->render($qrData, $filePath);

    // Save info in DB
    $relPath = 'attendance/assets/qrcodes/' . $fileName;
    $stmt = $conn->prepare("INSERT INTO student_qr (student_id, qr_code, token) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $studentId, $relPath, $token);
    $stmt->execute();
    $stmt->close();

    return $relPath;
}
?>
