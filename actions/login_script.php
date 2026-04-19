<?php
// actions/login_script.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/database.php';

// ✅ JS Response helper (must be before calling it)
function js_response(string $message, string $sound = null, string $redirect = null, bool $back = false)
{
    $msg = addslashes($message);
    $soundJs = $sound ? "playSound('{$sound}');" : "";
    if ($back) {
        $nav = "window.history.back();";
    } elseif ($redirect) {
        $redir = addslashes($redirect);
        $nav = "window.location.href='{$redir}';";
    } else {
        $nav = '';
    }

    echo "<script>
        function playSound(src){
            try {
                var audio = new Audio(src);
                audio.play().catch(function(e){});
            } catch(e){}
        }
        {$soundJs}
        alert('{$msg}');
        {$nav}
    </script>";
}

// ✅ Allow only POST login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    js_response('Invalid request method!', '../public/sounds/failed.mp3', '../login.php');
    exit;
}

// Get inputs
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    js_response('Please fill in all fields.', '../public/sounds/failed.mp3', null, true);
    exit;
}

// Check DB for user
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");

if (!$stmt) {
    js_response('Server error. Try again later.', '../public/sounds/failed.mp3', '../login.php');
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    js_response('Invalid email or password.', '../public/sounds/failed.mp3', '../login.php');
    exit;
}

$user = $res->fetch_assoc();


$stu_stmt = $conn->prepare("SELECT * FROM students WHERE parent_email = ? LIMIT 1");
$stu_stmt->bind_param('s', $email);
$stu_stmt->execute();
$stu_res = $stu_stmt->get_result();
$student = $stu_res->fetch_assoc();


$teacher_stmt = $conn->prepare("SELECT * FROM teachers WHERE email = ? LIMIT 1");
$teacher_stmt->bind_param('s', $email);
$teacher_stmt->execute();
$teacher_res = $teacher_stmt->get_result();
$teacher = $teacher_res->fetch_assoc();
// ✅ Check account status
if ((int)$user['status'] !== 1) {
    js_response('Account deactivated. Contact admin.', '../public/sounds/failed.mp3', '../login.php');
    exit;
}

// ✅ Verify password
if (!password_verify($password, $user['password'])) {
    js_response('Invalid email or password.', '../public/sounds/failed.mp3', null, true);
    exit;
}

// ✅ Logged in — set session
session_regenerate_id(true);
$_SESSION['user_id']    = $user['id'];
$_SESSION['user_name']  = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role']  = $user['role'];
if ($user['role'] == "teacher" && $teacher) {
    $_SESSION['teacher_id'] = $teacher['id'];
} 

if ($user['role'] == "parent" && $student) {
    $_SESSION['student_id'] = $student['id'];
}
$welcome = addslashes("Welcome, " . $user['name'] . "!");

// ✅ Redirect based on role
switch ($user['role']) {
    case 'super_admin':
        js_response($welcome, '../public/sounds/success.mp3', '../dashboard/super_admin_dashboard.php');
        break;

    case 'teacher':
        js_response($welcome, '../public/sounds/success.mp3', '../teachers_portal/dashboard.php');
        break;

    case 'parent':
        js_response($welcome, '../public/sounds/success.mp3', '../parents_portal/dashboard.php');
        break;

    default:
        session_unset();
        session_destroy();
        js_response('Unknown role detected. Contact admin.', '../public/sounds/failed.mp3', '../login.php');
        break;
}

$stmt->close();
exit;
