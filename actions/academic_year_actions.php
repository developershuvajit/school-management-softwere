<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/database.php');
include('../includes/alert_helper.php');

// ✏️ UPDATE ACADEMIC YEAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
    $id = intval($_POST['edit_id']);
    $academic_year = trim($_POST['academic_year']);
    $description = trim($_POST['description']);

    if ($id > 0 && !empty($academic_year)) {

        // Check for duplicate academic year excluding current record
        $chk = $conn->prepare("SELECT id FROM academic_years WHERE academic_year = ? AND id != ?");
        if (!$chk) {
            sweetAlert("Database Error!", $conn->error, "error", "../academics/academic_year_list.php");
            exit;
        }
        $chk->bind_param("si", $academic_year, $id);
        $chk->execute();
        $result = $chk->get_result();

        if ($result->num_rows > 0) {
            sweetAlert("⚠️ Already Exists!", "Another record with this Academic Year already exists!", "warning", "../academics/academic_year_list.php");
            exit;
        }

        $stmt = $conn->prepare("UPDATE academic_years SET academic_year = ?, description = ? WHERE id = ?");
        if (!$stmt) {
            sweetAlert("Database Error!", $conn->error, "error", "../academics/academic_year_list.php");
            exit;
        }

        $stmt->bind_param("ssi", $academic_year, $description, $id);
        if ($stmt->execute()) {
            sweetAlert("✅ Updated!", "Academic Year updated successfully!", "success", "../academics/academic_year_list.php");
        } else {
            sweetAlert("❌ Update Failed!", "Failed to update Academic Year.", "error", "../academics/academic_year_list.php");
        }
        exit;
    } else {
        sweetAlert("⚠️ Invalid!", "Missing Academic Year details.", "warning", "../academics/academic_year_list.php");
        exit;
    }
}

// 🟢 ADD ACADEMIC YEAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_academic_year'])) {
    $academic_year = trim($_POST['academic_year']);
    $description = trim($_POST['description']);

    if (!empty($academic_year)) {

        // Check duplicate
        $chk = $conn->prepare("SELECT id FROM academic_years WHERE academic_year = ?");
        if (!$chk) {
            sweetAlert("Database Error!", $conn->error, "error", "../academics/add_academic_year.php");
            exit;
        }

        $chk->bind_param("s", $academic_year);
        $chk->execute();
        $result = $chk->get_result();

        if ($result->num_rows > 0) {
            sweetAlert("⚠️ Already Exists!", "Academic Year already exists!", "warning", "../academics/add_academic_year.php");
            exit;
        }

        // Insert
        $stmt = $conn->prepare("INSERT INTO academic_years (academic_year, description) VALUES (?, ?)");
        if (!$stmt) {
            sweetAlert("Database Error!", $conn->error, "error", "../academics/add_academic_year.php");
            exit;
        }

        $stmt->bind_param("ss", $academic_year, $description);

        if ($stmt->execute()) {
            sweetAlert("✅ Success!", "Academic Year added successfully!", "success", "../academics/add_academic_year.php");
        } else {
            sweetAlert("❌ Error!", "Failed to add Academic Year.", "error", "../academics/add_academic_year.php");
        }
        exit;
    } else {
        sweetAlert("⚠️ Required!", "Please enter Academic Year!", "warning", "../academics/add_academic_year.php");
        exit;
    }
}

// 🗑️ DELETE ACADEMIC YEAR
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    if ($id > 0) {

        $stmt = $conn->prepare("DELETE FROM academic_years WHERE id = ?");
        if (!$stmt) {
            sweetAlert("Database Error!", $conn->error, "error", "../academics/academic_year_list.php");
            exit;
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            sweetAlert("✅ Deleted!", "Academic Year deleted successfully!", "success", "../academics/academic_year_list.php");
        } else {
            sweetAlert("❌ Delete Failed!", "Failed to delete Academic Year.", "error", "../academics/academic_year_list.php");
        }
        exit;
    } else {
        sweetAlert("⚠️ Invalid!", "Invalid Academic Year ID.", "warning", "../academics/academic_year_list.php");
        exit;
    }
}
