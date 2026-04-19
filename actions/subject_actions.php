<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/database.php');
include('../includes/alert_helper.php');

// UPDATE SUBJECT
if (isset($_POST['update_subject'])) {
    $id = intval($_POST['edit_subject_id']);
    $class_id = intval($_POST['class_id']);
    $subject_name = trim($_POST['subject_name']);
    $subject_code = trim($_POST['subject_code']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("UPDATE subjects SET class_id=?, subject_name=?, subject_code=?, description=? WHERE id=?");
    $stmt->bind_param("isssi", $class_id, $subject_name, $subject_code, $description, $id);

    if ($stmt->execute()) {
        sweetAlert("✅ Updated!", "Subject updated successfully!", "success", "../academics/subject_list.php");
    } else {
        sweetAlert("❌ Error!", "Failed to update subject.", "error", "../academics/subject_list.php");
    }
    $stmt->close();
}


// ADD SUBJECT
if (isset($_POST['add_subject'])) {
    $class_id = intval($_POST['class_id']);
    $subject_name = trim($_POST['subject_name']);
    $subject_code = trim($_POST['subject_code']);
    $description = trim($_POST['description']);

    if (!empty($class_id) && !empty($subject_name)) {
        $stmt = $conn->prepare("INSERT INTO subjects (class_id, subject_name, subject_code, description) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            sweetAlert("Database Error!", $conn->error, "error");
            exit;
        }

        $stmt->bind_param("isss", $class_id, $subject_name, $subject_code, $description);

        if ($stmt->execute()) {
            sweetAlert("✅ Success!", "Subject added successfully!", "success", "../academics/add_subject.php");
        } else {
            sweetAlert("❌ Error!", "Failed to add subject. Try again.", "error", "../academics/add_subject.php");
        }

        $stmt->close();
    } else {
        sweetAlert("⚠️ Warning!", "Please select class & enter subject name!", "warning", "../academics/add_subject.php");
    }
}


// DELETE SUBJECT
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        sweetAlert("🗑️ Deleted!", "Subject deleted successfully!", "success", "../academics/subject_list.php");
    } else {
        sweetAlert("❌ Error!", "Failed to delete subject.", "error", "../academics/subject_list.php");
    }

    $stmt->close();
}
