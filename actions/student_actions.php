<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/database.php');
include('../includes/alert_helper.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ===== Sanitize Inputs =====
    $admission_no      = $conn->real_escape_string($_POST['admission_no']);
    $roll_number       = $conn->real_escape_string($_POST['roll_number']);
    $class_id          = (int)$_POST['class_id'];
    $section_id        = (int)$_POST['section_id'];
    $academic_year_id  = (int)$_POST['academic_year'];
    $first_name        = $conn->real_escape_string($_POST['first_name']);
    $last_name         = $conn->real_escape_string($_POST['last_name']);
    $gender            = $conn->real_escape_string($_POST['gender']);

    // DOB Validate
    $dob_raw = trim($_POST['dob']);
    $dob = date('Y-m-d', strtotime($dob_raw));
    if (!strtotime($dob_raw)) {
        sweetAlert("Error", "❌ Invalid Date of Birth!", "error");
        exit;
    }

    $has_transport     = isset($_POST['has_transport']) ? 1 : 0;
    $vehicle_no        = $conn->real_escape_string($_POST['vehicle_no']);
    $pickup_point      = $conn->real_escape_string($_POST['pickup_point']);
    $transport_fee     = isset($_POST['transport_fee']) ? floatval($_POST['transport_fee']) : 0.00;
    $transport_fees    = isset($_POST['transport_fees']) ? intval($_POST['transport_fees']) : 0;

    $father_name       = $conn->real_escape_string($_POST['father_name']);
    $mother_name       = $conn->real_escape_string($_POST['mother_name']);
    $parent_phone      = $conn->real_escape_string($_POST['parent_phone']);
    $parent_email      = $conn->real_escape_string($_POST['parent_email']);
    $current_address   = $conn->real_escape_string($_POST['current_address']);
    $permanent_address = $conn->real_escape_string($_POST['permanent_address']);
    $previous_school   = $conn->real_escape_string($_POST['previous_school'] ?? '');
    $blood_group       = $conn->real_escape_string($_POST['blood_group'] ?? '');

    if (!$class_id || !$section_id || !$academic_year_id) {
        sweetAlert("Error", "Class / Section / Academic Year Required!", "error");
        exit;
    }

    // File Upload Handler
    function uploadFile($fileInput, $folder)
    {
        if (!empty($_FILES[$fileInput]['name'])) {
            $ext = pathinfo($_FILES[$fileInput]['name'], PATHINFO_EXTENSION);
            $filePath = $folder . uniqid() . "." . $ext;
            if (!is_dir("../" . $folder)) mkdir("../" . $folder, 0777, true);
            move_uploaded_file($_FILES[$fileInput]['tmp_name'], "../" . $filePath);
            return $filePath;
        }
        return '';
    }

    $photo  = uploadFile('photo', 'uploads/academics/photos/');
    $aadhar = uploadFile('aadhar', 'uploads/academics/aadhar/');

    // Generate Student ID
    $year = date('Y');
    $q = $conn->query("SELECT student_id FROM students WHERE student_id LIKE '$year/%' ORDER BY id DESC LIMIT 1");

    if ($q->num_rows > 0) {
        $last = $q->fetch_assoc();
        $newNumber = ((int)substr($last['student_id'], 5)) + 1;
    } else {
        $newNumber = 1;
    }
    $student_id = $year . "/" . $newNumber;

    // ===== Handle Guardian User =====
    $guardian_user_id = "NULL";

    if (!empty($parent_phone) || !empty($parent_email)) {
        // Check if guardian user exists in users table
        $sqlCheckUser = "SELECT id FROM users WHERE phone = '$parent_phone' OR email = '$parent_email' LIMIT 1";
        $result = $conn->query($sqlCheckUser);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $guardian_user_id = $row['id'];
        } else {
            // Insert new guardian user
            $parent_name = trim($father_name . " " . $mother_name);
            $sqlInsertUser = "INSERT INTO users (name, phone, email) VALUES ('$parent_name', '$parent_phone', '$parent_email')";
            if ($conn->query($sqlInsertUser)) {
                $guardian_user_id = $conn->insert_id;
            } else {
                sweetAlert("Error", "Failed to add guardian user: " . $conn->error, "error");
                exit;
            }
        }
    }

    // Prepare guardian_user_id for SQL query
    $guardian_user_id_sql = is_numeric($guardian_user_id) ? $guardian_user_id : "NULL";

    // ================= Insert Student Normally =================
    $sql = "INSERT INTO students (
        student_id, admission_no, roll_number, class_id, section_id, academic_year_id,
        first_name, last_name, gender, dob,
        has_transport, vehicle_no, pickup_point, transport_fee, transport_fees,
        father_name, mother_name, parent_phone, parent_email,
        current_address, permanent_address, previous_school, blood_group,
        photo, aadhar, guardian_user_id
    ) VALUES (
        '$student_id', '$admission_no', '$roll_number', $class_id, $section_id, $academic_year_id,
        '$first_name', '$last_name', '$gender', '$dob',
        $has_transport, '$vehicle_no', '$pickup_point', $transport_fee, $transport_fees,
        '$father_name', '$mother_name', '$parent_phone', '$parent_email',
        '$current_address', '$permanent_address', '$previous_school', '$blood_group',
        '$photo', '$aadhar', $guardian_user_id_sql
    )";

 

    if ($conn->query($sql)) {
        echo "
    <script>
    Swal.fire({
        title: 'Success',
        html: '🎉 Student Added Successfully!<br>Generated ID: $student_id',
        icon: 'success'
    })
    </script>
    ";
        exit;
    } else {
        // ERROR ALERT
        $error = addslashes($conn->error);
        echo "
    <script>
    Swal.fire({
        title: 'Error',
        text: 'Server Error: $error',
        icon: 'error'
    });
    </script>
    ";
        exit;
    }
} else {
    sweetAlert("Error", "Invalid request method", "error");
}
