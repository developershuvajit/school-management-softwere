<?php
date_default_timezone_set('Asia/Kolkata');
require_once '../config/database.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid QR data']);
    exit;
}

$rawQR = trim($input['id']);

$attDate = date('Y-m-d');
$outTime = date('H:i:s');

// ----------------------------------------
// TRY PARENT QR (JSON FORMAT)
// ----------------------------------------
$type = "student"; // default

$decoded = json_decode($rawQR, true);

if (json_last_error() === JSON_ERROR_NONE && isset($decoded['type']) && $decoded['type'] === 'parent') {

    // Parent card scanned
    $type = "parent";
    $student_code = $decoded['student_id'];

} else {

    // Student card scanned (normal ID)
    $student_code = $rawQR;
}

// ----------------------------------------
// FETCH STUDENT
// ----------------------------------------
$stmt = $conn->prepare("SELECT id, first_name, last_name, photo FROM students WHERE student_id=? LIMIT 1");
$stmt->bind_param("s", $student_code);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
    exit;
}

$student_db_id = $student['id'];
$fullName = trim($student['first_name'] . " " . $student['last_name']);
$photoPath = "../" . ($student['photo'] ?: "default.png");

// ----------------------------------------
// CHECK if OUT already marked
// ----------------------------------------
$check = $conn->prepare("SELECT id FROM attendance_out WHERE student_id=? AND att_date=?");
$check->bind_param("is", $student_db_id, $attDate);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Already marked OUT today'
    ]);
    exit;
}

// ----------------------------------------
// INSERT OUT entry
// ----------------------------------------
$insert = $conn->prepare("
    INSERT INTO attendance_out(student_id, att_date, out_time, method, type)
    VALUES (?, ?, ?, 'qr', ?)
");
$insert->bind_param("isss", $student_db_id, $attDate, $outTime, $type);

if ($insert->execute()) {

    echo json_encode([
        'success' => true,
        'name' => $fullName,
        'photo' => $photoPath,
        'status' => 'OUT',
        'time' => $outTime,
        'type' => $type,
        'message' => "$fullName marked OUT at $outTime"
    ]);

} else {

    echo json_encode([
        'success' => false,
        'message' => 'DB Error: ' . $conn->error
    ]);
}
?>
