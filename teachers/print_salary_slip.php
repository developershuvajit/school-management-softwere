<?php
session_start();
require_once('../config/database.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['teacher_id']) || !isset($_GET['month_year'])) {
    echo json_encode(['success' => false, 'message' => 'Teacher ID and Month required']);
    exit;
}

$teacher_id = (int)$_GET['teacher_id'];
$month_year = $_GET['month_year'];

$sql = "SELECT * FROM teacher_salary 
        WHERE teacher_id = ? AND month_year = ? 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $teacher_id, $month_year);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $salary = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'basic' => (float)$salary['basic'],
        'allowance' => (float)$salary['allowance'],
        'deduction' => (float)$salary['deduction'],
        'net_salary' => (float)$salary['net_salary'],
        'status' => $salary['status'],
        'paid_on' => $salary['paid_on']
    ]);
} else {
    // Return projected salary if not yet processed
    $teacher_sql = "SELECT salary FROM teachers WHERE id = ?";
    $teacher_stmt = $conn->prepare($teacher_sql);
    $teacher_stmt->bind_param("i", $teacher_id);
    $teacher_stmt->execute();
    $teacher_result = $teacher_stmt->get_result();
    $teacher = $teacher_result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'basic' => (float)$teacher['salary'],
        'allowance' => 0,
        'deduction' => 0,
        'net_salary' => (float)$teacher['salary'],
        'status' => 'pending',
        'paid_on' => null
    ]);
}
