<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();

require_once('../config/database.php');
require_once(__DIR__ . '/../vendor/autoload.php');

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

/* ==============================
   CHECK DB CONNECTION
================================ */
if (!isset($conn) || !$conn) {
    die("Database connection error.");
}

/* ==============================
   POST VARIABLES
================================ */

$teacher_code  = $_POST['teacher_code'] ?? '';
$isSingle      = isset($_POST['generate_single']);
$isAll         = isset($_POST['generate_all']);
$teacher_codes = $_POST['teacher_codes'] ?? [];
$isChecked     = isset($_POST['generate_checked']);

/* ==============================
   BUILD QUERY
================================ */

if ($isSingle && $teacher_code != '') {

    $teacher_code = $conn->real_escape_string($teacher_code);

    $query = "
        SELECT *
        FROM teachers
        WHERE teacher_code = '$teacher_code'
        AND status = 1
    ";

} elseif ($isChecked && !empty($teacher_codes)) {

    $safeCodes = array_map(function ($code) use ($conn) {
        return "'" . $conn->real_escape_string($code) . "'";
    }, $teacher_codes);

    $codeList = implode(',', $safeCodes);

    $query = "
        SELECT *
        FROM teachers
        WHERE teacher_code IN ($codeList)
        AND status = 1
        ORDER BY name ASC
    ";

} elseif ($isAll) {

    $query = "
        SELECT *
        FROM teachers
        WHERE status = 1
        ORDER BY name ASC
    ";

} else {
    die("<h3 style='text-align:center;color:red;margin-top:50px;'>Invalid Request</h3>");
}

/* ==============================
   EXECUTE QUERY
================================ */

$res = $conn->query($query);

if (!$res) {
    die("SQL Error: " . $conn->error);
}

if ($res->num_rows == 0) {
    die("<h3 style='text-align:center;color:red;margin-top:50px;'>No teachers found</h3>");
}

/* ==============================
   QR DIRECTORY
================================ */

$qrDir = __DIR__ . '/../qrcodes';

if (!is_dir($qrDir)) {
    if (!mkdir($qrDir, 0755, true)) {
        die("QR directory creation failed.");
    }
}

/* ==============================
   LOAD TEMPLATE DIRECTLY
   (IMPORTANT FIX)
================================ */

/*
IMPORTANT:
Template-এ $res ব্যবহার হবে
তাই এখানে আর fetch করবো না
*/

include(__DIR__ . '/../templates/teacher_idcard_template.php');

ob_end_flush();
