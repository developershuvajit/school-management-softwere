<?php
// actions/slider_edit.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('../config/database.php');
include('../includes/alert_helper.php');

// Fallback image (local file you uploaded) - will be used only if DB has no image.
$NO_IMAGE_FALLBACK = '/mnt/data/3f93a5b6-3702-47e5-81d0-92fd16820a60.png';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sweetAlert("Error", "Invalid request method.", "error");
    exit;
}

// ===== Sanitize Inputs =====
$slider_id   = isset($_POST['slider_id']) ? (int) $_POST['slider_id'] : 0;
$heading     = isset($_POST['slider_heading']) ? $conn->real_escape_string(trim($_POST['slider_heading'])) : '';
$description = isset($_POST['slider_description']) ? $conn->real_escape_string(trim($_POST['slider_description'])) : '';

if ($slider_id <= 0) {
    sweetAlert("Error", "Invalid slider ID.", "error");
    exit;
}
if ($heading === '') {
    sweetAlert("Error", "Heading cannot be empty.", "error");
    exit;
}

// ===== Helper: upload file =====
function uploadFileInput($fileInputName, $folderRelative)
{
    // Returns new relative path on success, empty string on no upload, or false on failure
    if (empty($_FILES[$fileInputName]['name'])) return '';
    if ($_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) return false;

    $tmpPath = $_FILES[$fileInputName]['tmp_name'];
    $originalName = $_FILES[$fileInputName]['name'];

    // Basic MIME check
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpPath);
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowed)) {
        return false;
    }

    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $filename = 'hero_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $uploadDir = __DIR__ . '/../' . rtrim($folderRelative, '/') . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $dest = $uploadDir . $filename;
    if (!move_uploaded_file($tmpPath, $dest)) {
        return false;
    }

    // return relative path to be stored in DB, e.g. uploads/hero/hero_...jpg
    return rtrim($folderRelative, '/') . '/' . $filename;
}

// ===== Fetch existing slider data =====
$stmt = $conn->prepare("SELECT image FROM hero_sliders WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $slider_id);
$stmt->execute();
$res = $stmt->get_result();
$current = $res->fetch_assoc();
$stmt->close();

$oldImage = $current['image'] ?? '';

// ===== Handle optional image upload =====
$newImagePath = $oldImage; // default keep old

$uploadResult = uploadFileInput('slider_image', 'uploads/hero'); // returns '' | false | 'uploads/hero/xxx.png'

if ($uploadResult === false) {
    sweetAlert("Error", "Image upload failed or invalid file type. Allowed: jpg, png, webp", "error");
    exit;
} elseif ($uploadResult !== '') {
    // Successful new upload: $uploadResult contains relative path
    $newImagePath = $uploadResult;

    // delete old image file if it exists and appears to be in uploads/hero/
    if (!empty($oldImage) && strpos($oldImage, 'uploads/hero/') === 0) {
        $oldFile = __DIR__ . '/../' . $oldImage;
        if (is_file($oldFile)) {
            @unlink($oldFile);
        }
    }
}

// If no image stored in DB and no new uploaded, set fallback (optional)
if (empty($newImagePath)) {
    $newImagePath = $NO_IMAGE_FALLBACK;
}

// ===== Update DB =====
$heading_db = $heading;
$description_db = $description;

$sql = "UPDATE hero_sliders SET title = ?, description = ?, image = ? WHERE id = ?";
$stmt2 = $conn->prepare($sql);
if (!$stmt2) {
    sweetAlert("Error", "Prepare failed: " . $conn->error, "error");
    exit;
}
$stmt2->bind_param('sssi', $heading_db, $description_db, $newImagePath, $slider_id);
$ok = $stmt2->execute();
if ($ok) {
    // success: redirect back to hero section or show alert
    sweetAlert("Success", "Slider updated successfully.", "success", "../website/hero_section.php");
    $stmt2->close();
    exit;
} else {
    $err = $stmt2->error;
    $stmt2->close();
    sweetAlert("Error", "Database update failed: " . addslashes($err), "error");
    exit;
}
