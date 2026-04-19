<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/database.php');
include('../includes/alert_helper.php');

if (isset($_POST['save_fee'])) {

    $id         = $_POST['id'] ?? null;
    $name       = trim($_POST['name'] ?? '');
    $category   = $_POST['category'] ?? '';
    $frequency  = $_POST['frequency'] ?? '';
    $year_id    = intval($_POST['academic_year_id'] ?? 0);
    $class_id   = intval($_POST['class_id'] ?? 0);
    $amount     = floatval($_POST['amount'] ?? 0);

    // 🔴 Basic validation
    if (!$name || !$category || !$frequency || !$year_id || !$class_id || $amount <= 0) {
        SweetAlert("Error!", "All fields are required!", "error", "../fees/fee_types.php");
        exit;
    }

    if ($id) {
        // ✅ UPDATE
        $stmt = $conn->prepare("
            UPDATE fee_types 
            SET name=?, category=?, frequency=?, academic_year_id=?, class_id=?, amount=? 
            WHERE id=?
        ");

        // s s s i i d i = 7 values
        $stmt->bind_param(
            "sssiidi",
            $name,
            $category,
            $frequency,
            $year_id,
            $class_id,
            $amount,
            $id
        );

        $msg = "Fee Updated!";
    } else {

        // ✅ DUPLICATE CHECK
        $check_stmt = $conn->prepare("
            SELECT id FROM fee_types 
            WHERE name=? AND academic_year_id=? AND class_id=?
        ");
        $check_stmt->bind_param("sii", $name, $year_id, $class_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            SweetAlert("Error!", "Fee already exists!", "error", "../fees/fee_types.php");
            exit;
        }

        // ✅ INSERT
        $stmt = $conn->prepare("
            INSERT INTO fee_types 
            (name, category, frequency, academic_year_id, class_id, amount)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        // s s s i i d = 6 values
        $stmt->bind_param(
            "sssiid",
            $name,
            $category,
            $frequency,
            $year_id,
            $class_id,
            $amount
        );

        $msg = "Fee Added!";
    }

    // ✅ Execute
    if ($stmt->execute()) {
        SweetAlert("Success!", $msg, "success", "../fees/fee_types.php");
    } else {
        SweetAlert("Error!", "Database Failed!", "error", "../fees/fee_types.php");
    }
    exit;
}

// ✅ DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM fee_types WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        SweetAlert("Deleted!", "Fee removed successfully!", "success", "../fees/fee_types.php");
    } else {
        SweetAlert("Error!", "Delete failed!", "error", "../fees/fee_types.php");
    }
    exit;
}
