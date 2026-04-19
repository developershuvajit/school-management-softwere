<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../vendor/autoload.php');

// Input sanitization
$class_id   = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
$isSingle   = isset($_POST['generate_single']);
$isBulk     = isset($_POST['generate_bulk']);
$isAll      = isset($_POST['generate_all']);
$student_id = isset($_POST['student_id']) ? $conn->real_escape_string($_POST['student_id']) : '';
$copies     = isset($_POST['copies']) ? (int)$_POST['copies'] : 1;

if ($copies < 1) $copies = 1;
if ($copies > 100) $copies = 100;

if ($isSingle && $student_id !== '') {

    $query = "
        SELECT 
            s.*, 
            c.class_name, 
            sec.section_name,
            ay.academic_year
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        LEFT JOIN academic_years ay ON ay.id = s.academic_year_id
        WHERE s.student_id = '$student_id'
        LIMIT 1
    ";

} elseif ($isBulk && $class_id > 0) {

    $query = "
        SELECT 
            s.*, 
            c.class_name, 
            sec.section_name,
            ay.academic_year
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        LEFT JOIN academic_years ay ON ay.id = s.academic_year_id
        WHERE s.class_id = $class_id
        ORDER BY s.first_name ASC
    ";

} elseif ($isAll) {

    $query = "
        SELECT 
            s.*, 
            c.class_name, 
            sec.section_name,
            ay.academic_year
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        LEFT JOIN academic_years ay ON ay.id = s.academic_year_id
        ORDER BY c.class_name ASC, s.first_name ASC
    ";

} else {
    die("<h3 style='text-align:center;color:red;margin-top:50px;'>No students selected for ID generation.</h3>");
}

$res = $conn->query($query);
if (!$res || $res->num_rows === 0) {
    die("<h3 style='text-align:center;color:red;margin-top:50px;'>No students found.</h3>");
}

$students = [];
while ($row = $res->fetch_assoc()) {
    $students[] = $row;
}

// Ensure qrcodes dir exists
$qrDir = __DIR__ . '/../qrcodes';
if (!is_dir($qrDir)) {
    mkdir($qrDir, 0755, true);
}

// Load template
include(__DIR__ . '/../templates/stickers_template.php');
