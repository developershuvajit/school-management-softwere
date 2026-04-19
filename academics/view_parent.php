<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

include('../config/database.php');

/* =====================
   FILTER VARIABLES
===================== */
$academic_year_id = isset($_GET['academic_year_id']) ? (int)$_GET['academic_year_id'] : 0;
$class_id         = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

$whereArr = [];

if ($academic_year_id > 0) {
    $whereArr[] = "s.academic_year_id = $academic_year_id";
}
if ($class_id > 0) {
    $whereArr[] = "s.class_id = $class_id";
}

$where = '';
if (!empty($whereArr)) {
    $where = 'WHERE ' . implode(' AND ', $whereArr);
}

/* =====================
   DROPDOWN DATA
===================== */
$classQuery = $conn->query("SELECT id, class_name FROM classes ORDER BY class_name ASC");
$yearQuery  = $conn->query("SELECT id, academic_year FROM academic_years ORDER BY academic_year DESC");

/* =====================
   MAIN QUERY
===================== */
$sql = "SELECT 
            s.id,
            s.student_id,
            s.first_name,
            s.last_name,
            s.father_name,
            s.mother_name,
            s.photo,
            ay.academic_year,
            c.class_name,
            sec.section_name
        FROM students s
        LEFT JOIN academic_years ay ON ay.id = s.academic_year_id
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        $where
        ORDER BY ay.academic_year DESC, c.class_name ASC, sec.section_name ASC, s.first_name ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>School Management Softwere</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
<div id="main-wrapper">

<?php include('../includes/navbar.php'); ?>
<?php include('../includes/sidebar_logic.php'); ?>

<div class="content-body">
<div class="container-fluid">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <?php include('../includes/welcome_text.php'); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="card-title mb-2 mb-sm-0">Student ID Card Generator</h4>
                    <a href="generate_idcard.php" class="btn btn-secondary btn-sm">
                        <i class="fa fa-refresh"></i> Reset Filter
                    </a>
                    <div class="input-group my-2 py-2">
                     <span class="input-group-text "><i class="fas fa-search"></i></span>
                      <input type="text" id="searchInput" class="form-control" placeholder="Search by student name or ID...">
                    </div>
                    
                </div>

                <div class="card-body">

                    <!-- FILTER FORM -->
                    <form method="GET" id="filterForm" class="mb-4">
                        <div class="row align-items-end">

                            <div class="col-md-3 input-group text-dark">
                                <label class="form-label fw-bold mr-2">Academic Year - </label>
                                <select name="academic_year_id" id="academicYear" class="form-select">
                                    <option value="">-- All Academic Years --</option>
                                    <?php while ($y = $yearQuery->fetch_assoc()) { ?>
                                        <option value="<?= $y['id']; ?>" <?= ($academic_year_id == $y['id']) ? 'selected' : ''; ?>>
                                            <?= $y['academic_year']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-3 input-group text-dark">
                                <label class="form-label fw-bold mr-2">Class - </label>
                                <select name="class_id" id="classSelect" class="form-select">
                                    <option value="">-- All Classes --</option>
                                    <?php while ($c = $classQuery->fetch_assoc()) { ?>
                                        <option value="<?= $c['id']; ?>" <?= ($class_id == $c['id']) ? 'selected' : ''; ?>>
                                            <?= $c['class_name']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-6 text-end mt-3 mt-md-0">
                                 <button type="button" id="generateChecked" class="btn btn-warning me-2">
                                    <i class="fa fa-id-card"></i> Generate Selected IDs
                                </button>
                                <button type="button" id="generateFiltered" class="btn btn-success me-2">
                                    <i class="fa fa-id-card"></i> Generate Filtered IDs
                                </button>
                                <button type="button" id="generateAll" class="btn btn-primary">
                                    <i class="fa fa-id-card"></i> Generate All IDs
                                </button>
                            </div>

                        </div>
                    </form>

                    <!-- TABLE -->
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-striped align-middle text-dark">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Photo</th>
                                    <th>Student ID</th>
                                    <th>Full Name</th>
                                    <th>Father Name</th>
                                    <th>Mother Name</th>
                                    <th>Academic Year</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            <?php
                            if ($result && $result->num_rows > 0) {
                                $i = 1;
                                while ($row = $result->fetch_assoc()) {

                                    $photoPath = "../assets/images/default-avatar.png";
                                    if (!empty($row['photo'])) {
                                        $p = "../" . $row['photo'];
                                        if (file_exists($p)) $photoPath = $p;
                                    }

                                    $fullName = trim($row['first_name'] . ' ' . $row['last_name']);
                                    ?>

                                    <tr class="text-center">
                                        <td><input type="checkbox" class="student-check" value="<?= $row['student_id']; ?>"></td>
                                        <td>
                                            <img src="<?= $photoPath; ?>" style="width:45px;height:45px;border-radius:50%;object-fit:cover;">
                                        </td>
                                        <td><?= $row['student_id']; ?></td>
                                        <td><?= $fullName; ?></td>
                                        <td><?= $row['father_name']; ?></td>
                                        <td><?= $row['mother_name']; ?></td>
                                        <td><?= $row['academic_year']; ?></td>
                                        <td><?= $row['class_name']; ?></td>
                                        <td><?= $row['section_name']; ?></td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-sm btn-primary generateSingle"
                                                    data-id="<?= $row['student_id']; ?>">
                                                <i class="fa fa-id-card"></i> Generate
                                            </button>
                                        </td>
                                    </tr>

                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="8" class="text-center text-danger">
                                        No students found.
                                    </td>
                                </tr>
                            <?php } ?>

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
</div>

</div>

<?php include('../includes/footer.php'); ?>
<?php include('../includes/js_links.php'); ?>

 

<script>
$(document).ready(function () {

    $('#academicYear, #classSelect').on('change', function () {
        $('#filterForm').submit();
    });

    $('#generateFiltered').on('click', function () {

        const ay = $('#academicYear').val();
        if (!ay) {
            Swal.fire('Error', 'Please select Academic Year', 'warning');
            return;
        }

        let form = $('<form>', {
            method: 'POST',
            action: '../actions/generate_parent_idcard_action.php',
            target: '_blank'
        });

        form.append($('<input>', { type: 'hidden', name: 'academic_year_id', value: ay }));
        form.append($('<input>', { type: 'hidden', name: 'class_id', value: $('#classSelect').val() }));
        form.append($('<input>', { type: 'hidden', name: 'generate_filtered', value: 1 }));

        $('body').append(form);
        form[0].submit();
    });

    $('#generateAll').on('click', function () {

        let form = $('<form>', {
            method: 'POST',
            action: '../actions/generate_parent_idcard_action.php',
            target: '_blank'
        });

        form.append($('<input>', { type: 'hidden', name: 'generate_all', value: 1 }));

        $('body').append(form);
        form[0].submit();
    });

    $('.generateSingle').on('click', function () {

        let studentId = $(this).data('id');

        let form = $('<form>', {
            method: 'POST',
            action: '../actions/generate_parent_idcard_action.php',
            target: '_blank'
        });

        form.append($('<input>', { type: 'hidden', name: 'student_id', value: studentId }));
        form.append($('<input>', { type: 'hidden', name: 'generate_single', value: 1 }));

        $('body').append(form);
        form[0].submit();
    });
    
    $('#generateChecked').on('click', function () {
    
        let selectedStudents = [];
    
        $('.student-check:checked').each(function () {
            selectedStudents.push($(this).val());
        });
    
        if (selectedStudents.length === 0) {
            Swal.fire('Warning', 'Please select at least one student', 'warning');
            return;
        }
    
        let form = $('<form>', {
            method: 'POST',
            action: '../actions/generate_parent_idcard_action.php',
            target: '_blank'
        });
    
        // send selected student IDs as array
        selectedStudents.forEach(function (id) {
            form.append(
                $('<input>', {
                    type: 'hidden',
                    name: 'student_ids[]',
                    value: id
                })
            );
        });
    
        form.append(
            $('<input>', {
                type: 'hidden',
                name: 'generate_checked',
                value: 1
            })
        );
    
        $('body').append(form);
        form[0].submit();
    });
    
    $('#searchInput').on('keyup', function () {
    let value = $(this).val().toLowerCase();

    $('table tbody tr').filter(function () {
        $(this).toggle(
            $(this).text().toLowerCase().indexOf(value) > -1
        );
    });
});
});



</script>


</body>
</html>
