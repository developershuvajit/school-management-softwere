<?php
date_default_timezone_set('Asia/Kolkata');
require_once '../config/database.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['id']) || empty($input['start_time'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid QR data']);
    exit;
}

$student_id = trim($input['id']);
$scannerStartTime = strtotime($input['start_time']);
$scanTime = date('Y-m-d H:i:s');
$currentDate = date('Y-m-d', strtotime($scanTime));

$stmt = $conn->prepare("SELECT id, CONCAT(first_name, ' ', last_name) AS name, photo FROM students WHERE student_id=?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit;
}

$check = $conn->prepare("SELECT id FROM attendance WHERE student_id=? AND attendance_date=?");
$check->bind_param("is", $student['id'], $currentDate);
$check->execute();
$checkRes = $check->get_result();

if ($checkRes->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Already marked today']);
    exit;
}

$lateThreshold = $scannerStartTime + (15 * 60);
$status = (strtotime($scanTime) > $lateThreshold) ? 'Late' : 'Present';

$insert = $conn->prepare("INSERT INTO attendance (student_id, attendance_date, status, method, att_time) VALUES (?, ?, ?, 'qr', ?)");
$insert->bind_param("isss", $student['id'], $currentDate, $status, $scanTime);

if ($insert->execute()) {
    $photoPath = "../" . ($student['photo'] ?: "default.png");

    echo json_encode([
        'success' => true,
        'name' => $student['name'],
        'photo' => $photoPath,
        'status' => $status,
        'time' => $scanTime,
        'message' => "{$student['name']} marked as {$status} at {$scanTime}"
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $conn->error]);
}
