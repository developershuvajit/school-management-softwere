<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();


if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

include('../config/database.php');      // your DB connection
// head (CSS/meta)
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>School India Junior</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<?php include('../config/database.php'); ?>


<body>
    <?php include "../includes/preloader.php" ?>
    <div id="main-wrapper">
        <?php include "../includes/navbar.php" ?>
        <?php include('../includes/sidebar_logic.php') ?>
        <div class="content-body">
            <div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <?php include('../includes/welcome_text.php'); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12 col-xxl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Edit Students</h4>

                            </div>
                            <?php
                            include('../config/database.php');

                            if (!isset($_GET['id']) || empty($_GET['id'])) {
                                echo "Invalid Request";
                                exit;
                            }

                            $id = intval($_GET['id']);
                            $student = $conn->query("SELECT s.*, 
                                                            ay.academic_year,
                                                            c.class_name,
                                                            sec.section_name
                                                        FROM students s
                                                        LEFT JOIN academic_years ay ON s.academic_year_id = ay.id
                                                        LEFT JOIN classes c ON s.class_id = c.id
                                                        LEFT JOIN sections sec ON s.section_id = sec.id
                                                        WHERE s.id = $id")->fetch_assoc();
                            if (!$student) {
                                echo "Student not found";
                                exit;
                            }
                            ?>

                            <div class="card-body">

                                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

                                <form method="POST" action="../actions/student_edit.php" enctype="multipart/form-data">

                                    <input type="hidden" name="edit_id" value="<?php echo $student['id']; ?>">

                                    <h5 class="mb-3">Admission Details</h5>
                                    <div class="form-row">
                                        <div class="form-group col-md-3">
                                            <label>Admission No *</label>
                                            <input type="text" class="form-control" name="admission_no"
                                                value="<?php echo $student['admission_no']; ?>" readonly>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Roll Number</label>
                                            <input type="text" class="form-control" name="roll_number"
                                                value="<?php echo $student['roll_number']; ?>">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Academic Year *</label>
                                            <select class="form-control" name="academic_year_id" required>
                                                <option disabled>Select Academic Year</option>
                                                <?php
                                                $ay = $conn->query("SELECT * FROM academic_years ORDER BY id DESC");
                                                while ($y = $ay->fetch_assoc()) {
                                                    $sel = ($student['academic_year_id'] == $y['id']) ? "selected" : "";
                                                    echo "<option value='{$y['id']}' $sel>{$y['academic_year']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Class *</label>
                                            <select class="form-control" name="class_id" id="class_id" required>
                                                <option disabled>Select Class</option>
                                                <?php
                                                $class = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");
                                                while ($c = $class->fetch_assoc()) {
                                                    $sel = ($student['class_id'] == $c['id']) ? "selected" : "";
                                                    echo "<option value='{$c['id']}' $sel>{$c['class_name']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3 mt-3">
                                            <label>Section *</label>
                                            <select class="form-control" name="section_id" id="section_id" required>
                                                <option>Loading...</option>
                                            </select>
                                        </div>
                                    </div>

                                    <h5 class="mt-4">Personal Information</h5>
                                    <div class="form-row">
                                        <div class="form-group col-md-3">
                                            <label>First Name *</label>
                                            <input type="text" class="form-control" name="first_name" required
                                                value="<?php echo $student['first_name']; ?>">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Last Name</label>
                                            <input type="text" class="form-control" name="last_name"
                                                value="<?php echo $student['last_name']; ?>">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Gender *</label>
                                            <select class="form-control" name="gender" required>
                                                <option <?php echo ($student['gender'] == "Male") ? "selected" : ""; ?>>Male</option>
                                                <option <?php echo ($student['gender'] == "Female") ? "selected" : ""; ?>>Female</option>
                                                <option <?php echo ($student['gender'] == "Other") ? "selected" : ""; ?>>Other</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Date Of Birth *</label>
                                            <input type="date" class="form-control" name="dob"
                                                value="<?php echo $student['dob']; ?>" required>
                                        </div>
                                    </div>

                                    <h5 class="mt-4">Documents</h5>
                                    <div class="form-row">
                                        <div class="form-group col-md-3">
                                            <label>Student Photo</label>
                                            <input type="file" class="form-control" name="photo">
                                            <?php if ($student['photo']) {
                                                echo "<img src='../{$student['photo']}' width='70'>";
                                            } ?>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Aadhar Card</label>
                                            <input type="file" class="form-control" name="aadhar">
                                            <?php if ($student['aadhar']) {
                                                echo "<a href='../{$student['aadhar']}' target='_blank'>View</a>";
                                            } ?>
                                        </div>
                                    </div>

                                    <h5 class="mt-4">Transport Details</h5>
                                    <div class="form-row">
                                        <div class="form-group col-md-3">
                                            <label>Has Transport?</label><br>
                                            <input type="radio" name="has_transport" value="1" <?php echo ($student['has_transport'] == 1) ? "checked" : ""; ?>> Yes
                                            <input type="radio" name="has_transport" value="0" <?php echo ($student['has_transport'] == 0) ? "checked" : ""; ?>> No
                                        </div>

                                        <div class="form-group col-md-3 transport-field <?php echo ($student['has_transport'] == 1) ? "" : "d-none"; ?>">
                                            <label>Vehicle Number</label>
                                            <input type="text" class="form-control" name="vehicle_no" value="<?php echo $student['vehicle_no']; ?>">
                                        </div>

                                        <div class="form-group col-md-3 transport-field <?php echo ($student['has_transport'] == 1) ? "" : "d-none"; ?>">
                                            <label>Pickup Point</label>
                                            <input type="text" class="form-control" name="pickup_point" value="<?php echo $student['pickup_point']; ?>">
                                        </div>

                                        <div class="form-group col-md-3 transport-field <?php echo ($student['has_transport'] == 1) ? "" : "d-none"; ?>">
                                            <label>Transport Fee</label>
                                            <input type="number" class="form-control" name="transport_fee" value="<?php echo $student['transport_fee']; ?>">
                                        </div>
                                    </div>

                                    <h5 class="mt-4">Parent / Guardian Details</h5>
                                    <div class="form-row">
                                        <div class="form-group col-md-3">
                                            <label>Father Name</label>
                                            <input type="text" class="form-control" name="father_name" value="<?php echo $student['father_name']; ?>">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Mother Name</label>
                                            <input type="text" class="form-control" name="mother_name" value="<?php echo $student['mother_name']; ?>">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Parent Phone *</label>
                                            <input type="text" class="form-control" name="parent_phone" value="<?php echo $student['parent_phone']; ?>">
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Parent Email</label>
                                            <input type="email" class="form-control" name="parent_email" value="<?php echo $student['parent_email']; ?>">
                                        </div>
                                    </div>

                                    <h5 class="mt-4">Address</h5>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Current Address</label>
                                            <textarea class="form-control" name="current_address"><?php echo $student['current_address']; ?></textarea>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Permanent Address</label>
                                            <textarea class="form-control" name="permanent_address"><?php echo $student['permanent_address']; ?></textarea>
                                        </div>
                                    </div>

                                    <h5 class="mt-4">Additional Info</h5>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Previous School</label>
                                            <textarea class="form-control" name="previous_school"><?php echo $student['previous_school']; ?></textarea>
                                        </div>

                                        <div class="form-group col-md-3">
                                            <label>Blood Group</label>
                                            <select class="form-control" name="blood_group">
                                                <option <?php echo ($student['blood_group'] == "A+") ? "selected" : ""; ?>>A+</option>
                                                <option <?php echo ($student['blood_group'] == "A-") ? "selected" : ""; ?>>A-</option>
                                                <option <?php echo ($student['blood_group'] == "B+") ? "selected" : ""; ?>>B+</option>
                                                <option <?php echo ($student['blood_group'] == "B-") ? "selected" : ""; ?>>B-</option>
                                                <option <?php echo ($student['blood_group'] == "AB+") ? "selected" : ""; ?>>AB+</option>
                                                <option <?php echo ($student['blood_group'] == "AB-") ? "selected" : ""; ?>>AB-</option>
                                                <option <?php echo ($student['blood_group'] == "O+") ? "selected" : ""; ?>>O+</option>
                                                <option <?php echo ($student['blood_group'] == "O-") ? "selected" : ""; ?>>O-</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <button type="submit" class="btn btn-success mt-3">Update Student</button>
                                    </div>
                                </form>

                                <script>
                                    $(document).on("change", "#class_id", function() {
                                        loadSection();
                                    });

                                    function loadSection() {
                                        let classId = $("#class_id").val();

                                        $.post("../ajax/get_sections.php", {
                                            class_id: classId,
                                            selected_section: "<?php echo $student['section_id']; ?>"
                                        }, function(data) {
                                            $("#section_id").html(data);
                                        });
                                    }
                                    loadSection();

                                    $("input[name='has_transport']").change(function() {
                                        if ($(this).val() == "1") $(".transport-field").removeClass("d-none");
                                        else $(".transport-field").addClass("d-none");
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "../includes/footer.php" ?>
    </div>
    <?php include "../includes/js_links.php" ?>