<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/database.php');
include('../includes/alert_helper.php');

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Get student info first
    $stmt = $conn->prepare("SELECT photo, aadhar FROM teachers WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        if (!empty($student['photo']) && file_exists('../' . $student['photo'])) {
            unlink('../' . $student['photo']);
        }
        if (!empty($student['aadhar']) && file_exists('../' . $student['aadhar'])) {
            unlink('../' . $student['aadhar']);
        }
        $del_student = $conn->prepare("DELETE FROM teachers WHERE id=?");
        $del_student->bind_param("i", $id);
        if ($del_student->execute()) {
            SweetAlert("Deleted!", "Teachers deleted successfully!", "success", "../teachers/teacher_list.php");
        } else {
            SweetAlert("❌ Error!", "Failed to delete teacher.", "error", "../teachers/teacher_list.php");
        }

        $del_student->close();
    } else {
        SweetAlert("❌ Not Found!", "Teacher not found!", "error", "../teachers/teacher_list.php");
    }
} else {
    SweetAlert("❌ Invalid!", "Invalid request!", "error", "../teachers/teacher_list.php");
}

$conn->close();
