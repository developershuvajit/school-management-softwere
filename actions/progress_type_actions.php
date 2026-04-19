<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/database.php');
include('../includes/alert_helper.php');


// 🟡 UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['edit_id'])) {

    $id = intval($_POST['edit_id']);
    $name = trim($_POST['name']);

    if ($id > 0 && !empty($name)) {

        $stmt = $conn->prepare("UPDATE progress_types SET name=? WHERE id=?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();

        sweetAlert("✅ Updated!", "Type updated successfully!", "success", "../academics/progress_type_list.php");
        exit;

    } else {
        sweetAlert("⚠️ Error!", "Invalid data!", "warning", "../academics/progress_type_list.php");
        exit;
    }
}


// 🔴 DELETE
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    if ($id > 0) {

        $stmt = $conn->prepare("DELETE FROM progress_types WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        sweetAlert("🗑️ Deleted!", "Type deleted successfully!", "success", "../academics/progress_type_list.php");
        exit;

    } else {
        sweetAlert("⚠️ Error!", "Invalid ID!", "warning", "../academics/progress_type_list.php");
        exit;
    }
}


// 🟢 ADD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['edit_id'])) {

    $name = trim($_POST['name']);

    if (!empty($name)) {

        // duplicate check
        $check = $conn->prepare("SELECT id FROM progress_types WHERE name=?");
        $check->bind_param("s", $name);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            sweetAlert("⚠️ Duplicate!", "Type already exists!", "warning", "../academics/progress_item.php");
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO progress_types (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();

        sweetAlert("✅ Success!", "Type added successfully!", "success", "../academics/progress_item.php");
        exit;

    } else {
        sweetAlert("⚠️ Warning!", "Enter type name!", "warning", "../academics/progress_item.php");
        exit;
    }
}
?>