<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/database.php');
include('../includes/alert_helper.php');

// UPDATE SECTION
if (isset($_POST['update_section'])) {
    $id = intval($_POST['edit_section_id']);
    $class_id = intval($_POST['class_id']);
    $section_name = trim($_POST['section_name']);
    $description = trim($_POST['description']);

    if (!empty($id) && !empty($class_id) && !empty($section_name)) {
        $stmt = $conn->prepare("UPDATE sections SET class_id=?, section_name=?, description=? WHERE id=?");
        if (!$stmt) {
            sweetAlert("Database Error!", $conn->error, "error", "../academics/section_list.php");
            exit;
        }

        $stmt->bind_param("issi", $class_id, $section_name, $description, $id);

        if ($stmt->execute()) {
            sweetAlert("✅ Updated!", "Section updated successfully!", "success", "../academics/section_list.php");
        } else {
            sweetAlert("❌ Error!", "Failed to update section", "error", "../academics/section_list.php");
        }
        $stmt->close();
    } else {
        sweetAlert("⚠️ Warning!", "All fields are required!", "warning", "../academics/section_list.php");
    }
}



// ADD SECTION
if (isset($_POST['add_section'])) {
    $class_id = intval($_POST['class_id']);
    $section_name = trim($_POST['section_name']);
    $description = trim($_POST['description']);

    if (!empty($section_name) && !empty($class_id)) {
        $stmt = $conn->prepare("INSERT INTO sections (class_id, section_name, description) VALUES (?, ?, ?)");
        if (!$stmt) {
            sweetAlert("Database Error!", $conn->error, "error");
            exit;
        }

        $stmt->bind_param("iss", $class_id, $section_name, $description);
        if ($stmt->execute()) {
            sweetAlert("✅ Success!", "Section added successfully!", "success", "../academics/add_section.php");
        } else {
            sweetAlert("❌ Error!", "Failed to add section. Try again.", "error", "../academics/add_section.php");
        }
        $stmt->close();
    } else {
        sweetAlert("⚠️ Warning!", "Please select class & enter section name!", "warning", "../academics/add_section.php");
    }
}


// DELETE SECTION
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM sections WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        sweetAlert("🗑️ Deleted!", "Section deleted successfully!", "success", "../academics/section_list.php");
    } else {
        sweetAlert("❌ Error!", "Failed to delete section.", "error", "../academics/section_list.php");
    }

    $stmt->close();
}
