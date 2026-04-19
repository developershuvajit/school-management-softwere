<?php
include('../config/database.php');
include('../includes/alert_helper.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['edit_id'])) {
    sweetAlert("Invalid Request", "", "error");
    exit;
}

$id = intval($_POST['edit_id']);
$parent_phone = $_POST['parent_phone'];
$parent_email = $_POST['parent_email'];
////////////////////////////////////////////////////////////
// ===== Handle Guardian User (EDIT FIXED) =====
$guardian_user_id = NULL;
$password = "12345";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

if (!empty($parent_email)) {

    // Check parent by email + role
    $stmt = $conn->prepare(
        "SELECT id FROM users WHERE email = ? AND role = 'parent' LIMIT 1"
    );
    $stmt->bind_param("s", $parent_email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $guardian_user_id = $res->fetch_assoc()['id'];
    } else {
        // Create new parent
        $parent_name = trim($_POST['father_name'] . " " . $_POST['mother_name']);

        $stmtInsert = $conn->prepare(
            "INSERT INTO users (name, phone, email, password, plain_password, role)
             VALUES (?, ?, ?, ?, ?, 'parent')"
        );
        $stmtInsert->bind_param(
            "sssss",
            $parent_name,
            $parent_phone,
            $parent_email,
            $hashed_password,
            $password
        );

        if ($stmtInsert->execute()) {
            $guardian_user_id = $stmtInsert->insert_id;
        } else {
            sweetAlert("Error", "Failed to create parent account", "error");
            exit;
        }
    }
}
//////////////////////////////////////////////////////////





$sql = "UPDATE students SET 
    roll_number = ?,
    academic_year_id = ?,
    class_id = ?,
    section_id = ?,
    first_name = ?,
    last_name = ?,
    gender = ?,
    dob = ?,
    has_transport = ?,
    vehicle_no = ?,
    pickup_point = ?,
    transport_fee = ?,
    father_name = ?,
    mother_name = ?,
    parent_phone = ?,
    parent_email = ?,
    current_address = ?,
    permanent_address = ?,
    previous_school = ?,
    blood_group = ?,
    guardian_user_id = ?
WHERE id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "iiiissssisssssssssssii",
    $_POST['roll_number'],
    $_POST['academic_year_id'],
    $_POST['class_id'],
    $_POST['section_id'],
    $_POST['first_name'],
    $_POST['last_name'],
    $_POST['gender'],
    $_POST['dob'],
    $_POST['has_transport'],
    $_POST['vehicle_no'],
    $_POST['pickup_point'],
    $_POST['transport_fee'],
    $_POST['father_name'],
    $_POST['mother_name'],
    $_POST['parent_phone'],
    $_POST['parent_email'],
    $_POST['current_address'],
    $_POST['permanent_address'],
    $_POST['previous_school'],
    $_POST['blood_group'],
     $guardian_user_id,
    $id
);

$stmt->execute();
// handle files
if (!empty($_FILES['photo']['name'])) {
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $file = "uploads/academics/photos/" . uniqid() . "." . $ext;
    move_uploaded_file($_FILES['photo']['tmp_name'], "../$file");
    $conn->query("UPDATE students SET photo='$file' WHERE id=$id");
}

if (!empty($_FILES['aadhar']['name'])) {
    $ext = pathinfo($_FILES['aadhar']['name'], PATHINFO_EXTENSION);
    $file = "uploads/academics/aadhar/" . uniqid() . "." . $ext;
    move_uploaded_file($_FILES['aadhar']['tmp_name'], "../$file");
    $conn->query("UPDATE students SET aadhar='$file' WHERE id=$id");
}

sweetAlert("Updated", "Student updated successfully!", "success", "../academics/student_list.php");
?>