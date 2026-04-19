<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/database.php');
include('../includes/alert_helper.php');


// 🟡 UPDATE CLASS (RUN FIRST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {

    $id = intval($_POST['edit_id']);
    $class_name = trim($_POST['class_name']);
    $description = trim($_POST['description']);

    if ($id > 0 && !empty($class_name)) {

        $stmt = $conn->prepare("UPDATE classes SET class_name = ?, description = ? WHERE id = ?");
        if (!$stmt) {
            sweetAlert("Database Error!", $conn->error, "error", "../academics/class_list.php");
            exit;
        }

        $stmt->bind_param("ssi", $class_name, $description, $id);
        $stmt->execute();

        sweetAlert("✅ Updated!", "Class updated successfully!", "success", "../academics/class_list.php");
        exit;

    } else {
        sweetAlert("⚠️ Invalid!", "Missing class details.", "warning", "../academics/class_list.php");
        exit;
    }
}



// 🔴 DELETE CLASS
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        sweetAlert("🗑️ Deleted!", "Class deleted successfully!", "success", "../academics/class_list.php");
        exit;

    } else {
        sweetAlert("⚠️ Invalid!", "Invalid class ID.", "warning", "../academics/class_list.php");
        exit;
    }
}



// 🟢 ADD CLASS (Last)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['edit_id']) && isset($_POST['class_name'])) {

    $class_name = trim($_POST['class_name']);
    $description = trim($_POST['description']);

    if (!empty($class_name)) {

        // Check if class already exists
        $check = $conn->prepare("SELECT id FROM classes WHERE class_name = ?");
        $check->bind_param("s", $class_name);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            sweetAlert("⚠️ Duplicate!", "Class name already exists.", "warning", "../academics/add_class.php");
            exit;
        }
        $check->close();

        $stmt = $conn->prepare("INSERT INTO classes (class_name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $class_name, $description);
        $stmt->execute();

        sweetAlert("✅ Success!", "Class added successfully!", "success", "../academics/add_class.php");
        exit;

    } else {
        sweetAlert("⚠️ Warning!", "Please enter a class name!", "warning", "../academics/add_class.php");
        exit;
    }
}
?>
