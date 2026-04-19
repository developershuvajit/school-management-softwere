<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/database.php');
include('../includes/alert_helper.php'); // SweetAlert helper

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Get student info first
    $stmt = $conn->prepare("SELECT photo, aadhar, guardian_user_id FROM students WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        $guardian_id = $student['guardian_user_id'];

        // Delete uploaded files
        if (!empty($student['photo']) && file_exists('../' . $student['photo'])) {
            unlink('../' . $student['photo']);
        }
        if (!empty($student['aadhar']) && file_exists('../' . $student['aadhar'])) {
            unlink('../' . $student['aadhar']);
        }

        // Delete student
        $del_student = $conn->prepare("DELETE FROM students WHERE id=?");
        $del_student->bind_param("i", $id);
        if ($del_student->execute()) {

            // Delete parent if not linked to other students
            if (!empty($guardian_id)) {
                $chk_parent = $conn->prepare("SELECT COUNT(*) AS total FROM students WHERE guardian_user_id=?");
                $chk_parent->bind_param("i", $guardian_id);
                $chk_parent->execute();
                $count = $chk_parent->get_result()->fetch_assoc()['total'];

                if ($count == 0) {
                    $del_parent = $conn->prepare("DELETE FROM users WHERE id=?");
                    $del_parent->bind_param("i", $guardian_id);
                    $del_parent->execute();
                }
            }

            SweetAlert("🗑️ Deleted!", "Student and linked parent account deleted successfully!", "success", "../academics/student_list.php");
        } else {
            SweetAlert("❌ Error!", "Failed to delete student.", "error", "../academics/student_list.php");
        }

        $del_student->close();
    } else {
        SweetAlert("❌ Not Found!", "Student not found!", "error", "../academics/student_list.php");
    }
} else {
    SweetAlert("❌ Invalid!", "Invalid request!", "error", "../academics/student_list.php");
}

$conn->close();
?> 

