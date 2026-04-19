<?php
session_start();
require_once '../config/database.php';

/* ---------- VALIDATION ---------- */
if (
    empty($_POST['student_ids']) ||
    empty($_POST['from_academic_year']) ||
    empty($_POST['to_academic_year']) ||
    empty($_POST['to_class'])
) {
    $_SESSION['error'] = "Invalid promotion request!";
    header("Location: ../academics/promote_class.php");
    exit;
}

$studentIds = $_POST['student_ids'];
$fromYear   = (int) $_POST['from_academic_year'];
$toYear     = (int) $_POST['to_academic_year'];
$toClass    = (int) $_POST['to_class'];
$adminId    = $_SESSION['user_id'] ?? null;

/* Prevent same year promotion */
if ($fromYear === $toYear) {
    $_SESSION['error'] = "From Academic Year and To Academic Year cannot be same!";
    header("Location: ../academics/promote_class.php");
    exit;
}

$conn->begin_transaction();

try {

    /* Prepared Statements */
    $getStudent = $conn->prepare(
        "SELECT academic_year_id, class_id FROM students WHERE id = ?"
    );

    $updateStudent = $conn->prepare(
        "UPDATE students 
         SET academic_year_id = ?, class_id = ?
         WHERE id = ?"
    );

    $insertHistory = $conn->prepare(
        "INSERT INTO student_promotions
        (student_id, from_academic_year_id, to_academic_year_id, from_class_id, to_class_id, promoted_by)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    foreach ($studentIds as $studentId) {

        $studentId = (int)$studentId;

        /* Fetch current details */
        $getStudent->bind_param("i", $studentId);
        $getStudent->execute();
        $result = $getStudent->get_result();

        if ($result->num_rows === 0) {
            continue;
        }

        $st = $result->fetch_assoc();

        /* Update student */
        $updateStudent->bind_param("iii", $toYear, $toClass, $studentId);
        $updateStudent->execute();

        /* Insert promotion history */
        $insertHistory->bind_param(
            "iiiiii",
            $studentId,
            $st['academic_year_id'],
            $toYear,
            $st['class_id'],
            $toClass,
            $adminId
        );
        $insertHistory->execute();
    }

    $conn->commit();

    /* ---------- SWEET ALERT SUCCESS ---------- */
    $_SESSION['swal_success'] = true;

} catch (Exception $e) {

    $conn->rollback();

    /* ---------- SWEET ALERT ERROR ---------- */
    $_SESSION['swal_error'] = "Promotion failed! Please try again.";
}

header("Location: ../academics/promote_class.php");
exit;
