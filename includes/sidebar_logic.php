<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_role'])) {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit;
}

switch ($_SESSION['user_role']) {
    case 'super_admin':
        $sidebarFile = "../includes/sidebar_super_admin.php";
        break;
    case 'teacher':
        $sidebarFile = "../includes/sidebar_teacher.php";
        break;
    case 'parent':
        $sidebarFile = "../includes/sidebar_parent.php";

        break;
    default:

        session_unset();
        session_destroy();
        header('Location: ../login.php');
        exit;
}


if (file_exists($sidebarFile)) {
    include $sidebarFile;
} else {
    echo "Sidebar file not found!";
    exit;
}
