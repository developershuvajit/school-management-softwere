<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/database.php');
include('../includes/alert_helper.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_teacher'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $qualification = trim($_POST['qualification']);
    $subject = trim($_POST['subject']);
    $salary = floatval($_POST['salary']);
    $join_date = $_POST['join_date'];

    // Handle photo upload
    $photo_path = NULL;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = 'teacher_' . time() . '.' . $ext;
        $upload_dir = '../public/uploads/teachers/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $dest = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            $photo_path = 'public/uploads/teachers/' . $filename;
        }
    }

    // Handle Aadhar upload
    $aadhar_path = NULL;
    if (isset($_FILES['aadhar']) && $_FILES['aadhar']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['aadhar']['name'], PATHINFO_EXTENSION);
        $filename = 'aadhar_' . time() . '.' . $ext;
        $upload_dir = '../public/uploads/teachers/aadhar/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $dest = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['aadhar']['tmp_name'], $dest)) {
            $aadhar_path = 'public/uploads/teachers/aadhar/' . $filename;
        }
    }

    // Generate a user password
    $plain_password = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

    // Insert into users table
    $stmt1 = $conn->prepare("INSERT INTO users (name,email,phone,password,plain_password,role) VALUES (?,?,?,?,?,?)");
    $role = 'teacher';
    $stmt1->bind_param("ssssss", $name, $email, $phone, $hashed_password, $plain_password, $role);
    if (!$stmt1->execute()) {
        sweetAlert("❌ Error!", "Failed to create user: " . $stmt1->error, "error", "../teachers/teacher_add.php");
        exit;
    }
    $user_id = $stmt1->insert_id;
    $stmt1->close();

    // Generate teacher_code
    $teacher_code = 'TCHR' . str_pad($user_id, 4, '0', STR_PAD_LEFT);

    // Insert into teachers table
    $stmt2 = $conn->prepare("INSERT INTO teachers (user_id, teacher_code, name, email, phone, qualification, subject, salary, join_date, photo, qr_code) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    $qr_code = NULL; // QR will be generated later in list page
    $stmt2->bind_param("issssssdsss", $user_id, $teacher_code, $name, $email, $phone, $qualification, $subject, $salary, $join_date, $photo_path, $qr_code);

    if ($stmt2->execute()) {
        // Optionally store aadhar path in teachers table if needed
        if ($aadhar_path) {
            $teacher_id = $stmt2->insert_id;
            $conn->query("UPDATE teachers SET aadhar='$aadhar_path' WHERE id=$teacher_id");
        }
        sweetAlert("✅ Success!", "Teacher added successfully! Login credentials: Email: $email | Password: $plain_password", "success", "../teachers/teacher_add.php");
    } else {
        sweetAlert("❌ Error!", "Failed to add teacher: " . $stmt2->error, "error", "teacher_add.php");
    }
    $stmt2->close();
} else {
    header("Location: ../teachers/teacher_add.php");
    exit;
}
