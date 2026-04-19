<?php
// config/helpers.php
// ----------------------------
// Common helper functions used across the system
// ----------------------------

//  Redirect to any page
function redirect($url) {
    header("Location: " . $url);
    exit;
}

//  Show a simple JavaScript alert message
function alert($msg) {
    echo "<script>alert('$msg');</script>";
}

//  Show an alert and redirect together
function alertAndRedirect($msg, $url) {
    echo "<script>alert('$msg'); window.location.href='$url';</script>";
    exit;
}

//  Sanitize input (for form data before SQL insert/update)
function sanitize($conn, $data) {
    return htmlspecialchars(mysqli_real_escape_string($conn, trim($data)));
}

//  Check if user is logged in (used in dashboard pages)
function checkLogin() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
}

//  Check role (for role-based access)
function checkRole($allowed_roles = []) {
    session_start();
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
}

//  Format date/time (for reports)
function formatDate($date) {
    return date("d M Y", strtotime($date));
}

//  Flash message system (one-time message between redirects)
function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}
function getFlash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return '';
}

//  Debug helper (for testing arrays)
function debug($data) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}
?>
