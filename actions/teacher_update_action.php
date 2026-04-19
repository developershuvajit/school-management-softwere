<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include('../config/database.php');
include('../includes/alert_helper.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_teacher'])) {
    $teacher_id = intval($_POST['teacher_id']);
    $user_id = intval($_POST['user_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $qualification = trim($_POST['qualification']);
    $subject = trim($_POST['subject']);
    $salary = floatval($_POST['salary']);
    $join_date = $_POST['join_date'];

    // Get current file paths
    $current_photo = $_POST['current_photo'];
    $current_aadhar = $_POST['current_aadhar'];

    // Handle photo upload
    $photo_path = $current_photo;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = 'teacher_' . time() . '.' . $ext;
        $upload_dir = '../public/uploads/teachers/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $dest = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            $photo_path = 'public/uploads/teachers/' . $filename;

            // Delete old photo if exists
            if (!empty($current_photo) && file_exists('../' . $current_photo)) {
                unlink('../' . $current_photo);
            }
        }
    }

    // Handle Aadhar upload
    $aadhar_path = $current_aadhar;
    if (isset($_FILES['aadhar']) && $_FILES['aadhar']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['aadhar']['name'], PATHINFO_EXTENSION);
        $filename = 'aadhar_' . time() . '.' . $ext;
        $upload_dir = '../public/uploads/teachers/aadhar/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $dest = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['aadhar']['tmp_name'], $dest)) {
            $aadhar_path = 'public/uploads/teachers/aadhar/' . $filename;

            // Delete old aadhar if exists
            if (!empty($current_aadhar) && file_exists('../' . $current_aadhar)) {
                unlink('../' . $current_aadhar);
            }
        }
    }

    // Check if email already exists for another user
    $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        sweetAlert("❌ Error!", "Email already exists for another user!", "error", "../teachers/teacher_edit.php?id=" . $teacher_id);
        exit;
    }
    $check_stmt->close();

    // Update users table
    $update_user_sql = "UPDATE users SET 
                        name = ?, 
                        email = ?, 
                        phone = ?
                        WHERE id = ?";

    $stmt_user = $conn->prepare($update_user_sql);
    $stmt_user->bind_param("sssi", $name, $email, $phone, $user_id);

    if (!$stmt_user->execute()) {
        sweetAlert("❌ Error!", "Failed to update user: " . $stmt_user->error, "error", "../teachers/teacher_edit.php?id=" . $teacher_id);
        exit;
    }
    $stmt_user->close();

    // Update teachers table - ADD EMAIL FIELD HERE
    $update_teacher_sql = "UPDATE teachers SET 
                           name = ?, 
                           email = ?, 
                           phone = ?,   
                           qualification = ?, 
                           subject = ?, 
                           salary = ?, 
                           join_date = ?, 
                           photo = ?, 
                           aadhar = ?,
                           updated_at = NOW()
                           WHERE user_id = ?";

    $stmt_teacher = $conn->prepare($update_teacher_sql);

    // Bind parameters including email and phone
    if ($stmt_teacher) {
        $stmt_teacher->bind_param(
            "sssssdsssi",  // Changed from "sssdsssi" to include email and phone
            $name,
            $email,
            $phone,        // Add phone if teachers table has it
            $qualification,
            $subject,
            $salary,
            $join_date,
            $photo_path,
            $aadhar_path,
            $user_id
        );

        if ($stmt_teacher->execute()) {
            sweetAlert("✅ Success!", "Teacher updated successfully!", "success", "../teachers/teacher_list.php?id=" . $teacher_id);
        } else {
            sweetAlert("❌ Error!", "Failed to update teacher: " . $stmt_teacher->error, "error", "../teachers/teacher_edit.php?id=" . $teacher_id);
        }
        $stmt_teacher->close();
    } else {
        sweetAlert("❌ Error!", "Failed to prepare teacher update statement: " . $conn->error, "error", "../teachers/teacher_edit.php?id=" . $teacher_id);
    }
} else {
    header("Location: ../teachers/teacher_list.php");
    exit;
}
