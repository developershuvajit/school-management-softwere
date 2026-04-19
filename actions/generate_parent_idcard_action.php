<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('../config/database.php');
require_once(__DIR__ . '/../vendor/autoload.php');

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

$class_id         = (int)($_POST['class_id'] ?? 0);
$academic_year_id = (int)($_POST['academic_year_id'] ?? 0);
$student_id       = $_POST['student_id'] ?? '';

$isSingle   = isset($_POST['generate_single']);
$isFiltered = isset($_POST['generate_filtered']);
$isAll      = isset($_POST['generate_all']);
$isChecked  = isset($_POST['generate_checked']);
$student_ids = $_POST['student_ids'] ?? [];

/* ==============================
   BUILD QUERY
================================ */

if ($isSingle && $student_id != '') {

    $student_id = $conn->real_escape_string($student_id);

    $query = "
        SELECT s.*, c.class_name, sec.section_name, ay.academic_year
        FROM students s
        LEFT JOIN classes c ON c.id = s.class_id
        LEFT JOIN sections sec ON sec.id = s.section_id
        LEFT JOIN academic_years ay ON ay.id = s.academic_year_id
        WHERE s.student_id = '$student_id'
    ";

} elseif ($isChecked && !empty($student_ids)) {

    $ids = array_map(function ($id) use ($conn) {
        return "'" . $conn->real_escape_string($id) . "'";
    }, $student_ids);

    $idList = implode(',', $ids);

    $query = "
        SELECT s.*, c.class_name, sec.section_name, ay.academic_year
        FROM students s
        LEFT JOIN classes c ON c.id = s.class_id
        LEFT JOIN sections sec ON sec.id = s.section_id
        LEFT JOIN academic_years ay ON ay.id = s.academic_year_id
        WHERE s.student_id IN ($idList)
        ORDER BY c.class_name ASC, s.first_name ASC
    ";

} elseif ($isFiltered && $academic_year_id > 0) {

    $where = "WHERE s.academic_year_id = $academic_year_id";

    if ($class_id > 0) {
        $where .= " AND s.class_id = $class_id";
    }

    $query = "
        SELECT s.*, c.class_name, sec.section_name, ay.academic_year
        FROM students s
        LEFT JOIN classes c ON c.id = s.class_id
        LEFT JOIN sections sec ON sec.id = s.section_id
        LEFT JOIN academic_years ay ON ay.id = s.academic_year_id
        $where
        ORDER BY c.class_name ASC, s.first_name ASC
    ";

} elseif ($isAll) {

    $query = "
        SELECT s.*, c.class_name, sec.section_name, ay.academic_year
        FROM students s
        LEFT JOIN classes c ON c.id = s.class_id
        LEFT JOIN sections sec ON sec.id = s.section_id
        LEFT JOIN academic_years ay ON ay.id = s.academic_year_id
        ORDER BY ay.academic_year DESC, c.class_name ASC, s.first_name ASC
    ";

} else {
    die("<h3 style='text-align:center;color:red;margin-top:50px;'>Invalid Request</h3>");
}

/* ==============================
   EXECUTE QUERY
================================ */

$res = $conn->query($query);

if (!$res || $res->num_rows == 0) {
    die("<h3 style='text-align:center;color:red;margin-top:50px;'>No students found</h3>");
}

/* ==============================
   QR DIRECTORY
================================ */

$qrDir = __DIR__ . '/../qrcodes';
if (!is_dir($qrDir)) {
    mkdir($qrDir, 0755, true);
}

/* ==============================
   PRINT TEMPLATE
================================ */

include(__DIR__ . '/../templates/parent_idcard_template.php');
