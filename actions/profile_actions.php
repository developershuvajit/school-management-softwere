<?php
session_start();
include("../config/database.php");
include("../includes/alert_helper.php");

// If user not logged in then redirect
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


// -------------------------------------
// 🔹 Update Profile (Name + Email + Phone)
// -------------------------------------
if (isset($_POST['update_profile'])) {

    $name  = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);

    // Check if email already taken by another account
    $check = $conn->query("SELECT id FROM users WHERE email='$email' AND id!=$user_id LIMIT 1");
    if ($check->num_rows > 0) {
        sweetAlert("⚠ Email Already Exists!", "Try a different email address.", "warning", "../dashboard/profile.php");
        exit();
    }

    $update = $conn->query("UPDATE users SET name='$name', email='$email', phone='$phone' WHERE id=$user_id");

    if ($update) {
        sweetAlert("✔ Profile Updated!", "Your profile updated successfully.", "success", "../dashboard/profile.php");
    } else {
        sweetAlert("❌ Update Failed!", "Please try again later!", "error", "../dashboard/profile.php");
    }

    exit();
}



// -------------------------------------
// 🔐 Update Password (Hash + Plain Save)
// -------------------------------------
if (isset($_POST['update_password'])) {

    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_new_password']);

    // Password matching check
    if ($new_password !== $confirm_password) {
        sweetAlert("⚠ Password Mismatch!", "New passwords do not match!", "warning", "../dashboard/profile.php");
        exit();
    }

    if (strlen($new_password) < 4) {
        sweetAlert("⚠ Weak Password!", "Password must be minimum 4 characters!", "warning", "../dashboard/profile.php");
        exit();
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $update_pw = $conn->query("UPDATE users SET password='$hashed_password', plain_password='$new_password' WHERE id=$user_id");

    if ($update_pw) {
        sweetAlert("🔐 Password Updated!", "Your new password saved successfully.", "success", "../dashboard/profile.php");
    } else {
        sweetAlert("❌ Failed!", "Unable to change password!", "error", "../dashboard/profile.php");
    }

    exit();
}


// If no POST action matched
sweetAlert("⚠ Invalid Action!", "Something went wrong!", "warning", "../dashboard/profile.php");
exit();
