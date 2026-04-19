<?php
date_default_timezone_set('Asia/Kolkata');
require_once '../config/database.php';

header('Content-Type: application/json');

/* ==========================
   CHECK DATABASE CONNECTION
========================== */
if (!isset($conn) || $conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

/* ==========================
   READ QR INPUT (JSON / TEXT)
========================== */
$rawData = file_get_contents('php://input');
$input   = json_decode($rawData, true);

/* If JSON invalid, treat as plain teacher_code */
if (!$input) {
    $input = [
        'id' => trim($rawData)
    ];
}

/* Validate ID */
if (empty($input['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid QR data'
    ]);
    exit;
}

$teacher_code = trim($input['id']);
$scanTime     = date('Y-m-d H:i:s');
$currentDate  = date('Y-m-d');

/* ==========================
   FETCH ACTIVE TEACHER
========================== */
$stmt = $conn->prepare("
    SELECT id, name, photo
    FROM teachers
    WHERE teacher_code = ?
    AND status = 1
    LIMIT 1
");

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Prepare failed: ' . $conn->error
    ]);
    exit;
}

$stmt->bind_param("s", $teacher_code);
$stmt->execute();
$result  = $stmt->get_result();
$teacher = $result->fetch_assoc();

if (!$teacher) {
    echo json_encode([
        'success' => false,
        'message' => 'Teacher not found or inactive'
    ]);
    exit;
}

/* ==========================
   CHECK EXISTING RECORD TODAY
========================== */
$check = $conn->prepare("
    SELECT id, in_time, out_time
    FROM teacher_attendance
    WHERE teacher_id = ?
    AND attendance_date = ?
    LIMIT 1
");

$check->bind_param("is", $teacher['id'], $currentDate);
$check->execute();
$existing = $check->get_result()->fetch_assoc();

/* ==========================
   CASE 1 → FIRST SCAN (IN)
========================== */
if (!$existing) {

    // School Start Time (Edit if needed)
    $schoolStart = strtotime($currentDate . " 09:00:00");
    $lateLimit   = $schoolStart + (15 * 60);

    $status = (strtotime($scanTime) > $lateLimit)
        ? 'Late'
        : 'Present';

    $insert = $conn->prepare("
        INSERT INTO teacher_attendance
        (teacher_id, attendance_date, status, method, in_time)
        VALUES (?, ?, ?, 'qr', ?)
    ");

    $insert->bind_param(
        "isss",
        $teacher['id'],
        $currentDate,
        $status,
        $scanTime
    );

    if ($insert->execute()) {

        echo json_encode([
            'success' => true,
            'name'    => $teacher['name'],
            'photo'   => !empty($teacher['photo'])
                ? "../" . $teacher['photo']
                : "../assets/images/default-avatar.png",
            'time'    => date("h:i A", strtotime($scanTime)),
            'message' => "{$teacher['name']} marked IN ({$status})"
        ]);
        exit;
    }

    echo json_encode([
        'success' => false,
        'message' => 'Insert failed: ' . $conn->error
    ]);
    exit;
}

/* ==========================
   CASE 2 → SECOND SCAN (OUT)
========================== */
if ($existing && empty($existing['out_time'])) {

    $update = $conn->prepare("
        UPDATE teacher_attendance
        SET out_time = ?
        WHERE id = ?
    ");

    $update->bind_param(
        "si",
        $scanTime,
        $existing['id']
    );

    if ($update->execute()) {

        echo json_encode([
            'success' => true,
            'name'    => $teacher['name'],
            'photo'   => !empty($teacher['photo'])
                ? "../" . $teacher['photo']
                : "../assets/images/default-avatar.png",
            'time'    => date("h:i A", strtotime($scanTime)),
            'message' => "{$teacher['name']} marked OUT"
        ]);
        exit;
    }

    echo json_encode([
        'success' => false,
        'message' => 'Update failed: ' . $conn->error
    ]);
    exit;
}

/* ==========================
   CASE 3 → ALREADY IN & OUT
========================== */
echo json_encode([
    'success' => false,
    'message' => 'Attendance already completed today'
]);
exit;
