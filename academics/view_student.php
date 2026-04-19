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
            <?php
            include('../config/database.php');
            include('../includes/alert_helper.php');

            if (!isset($_GET['id']) || empty($_GET['id'])) {
                sweetAlert('Error', 'Student ID missing!', 'error', 'javascript:history.back()');
                exit;
            }

            $id = (int)$_GET['id'];

            $sql = "SELECT s.*, c.class_name, sec.section_name, ay.academic_year
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        LEFT JOIN academic_years ay ON s.academic_year_id = ay.id
        WHERE s.id = ? 
        LIMIT 1";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                sweetAlert('Error', 'Student not found!', 'error', 'javascript:history.back()');
                exit;
            }

            $student = $result->fetch_assoc();
            $photo = !empty($student['photo']) ? '../' . $student['photo'] : '../assets/img/default-student.png';
            $aadhar = !empty($student['aadhar']) ? '../' . $student['aadhar'] : null;

            function displayYesNo($val)
            {
                return $val ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
            }

            // Fetch Parent Login Credentials
            $parent_email = '-';
            $parent_plain_password = '';
            $parent_user_id = null;

            if (!empty($student['guardian_user_id'])) {
                $u_stmt = $conn->prepare("SELECT id, email, plain_password FROM users WHERE id=? AND role='parent' LIMIT 1");
                $u_stmt->bind_param("i", $student['guardian_user_id']);
                $u_stmt->execute();
                $u_res = $u_stmt->get_result();

                if ($u_res && $u_res->num_rows > 0) {
                    $parent = $u_res->fetch_assoc();
                    $parent_email = $parent['email'];
                    $parent_plain_password = $parent['plain_password'] ?? '';
                    $parent_user_id = $parent['id'];
                }
                $u_stmt->close();
            }
            
            
            // ================= ATTENDANCE SUMMARY =================
$student_id = $student['id'];

// Monthly Attendance (Current Month)
$month = date('m');
$year  = date('Y');

$monthly_sql = "
SELECT 
    COUNT(*) as total_days,
    SUM(status='Present') as present_days,
    SUM(status='Late') as late_days
FROM attendance
WHERE student_id=? AND MONTH(attendance_date)=? AND YEAR(attendance_date)=?
";
$m_stmt = $conn->prepare($monthly_sql);
$m_stmt->bind_param("iii", $student_id, $month, $year);
$m_stmt->execute();
$monthly = $m_stmt->get_result()->fetch_assoc();
$m_stmt->close();

// Yearly Attendance
$yearly_sql = "
SELECT 
    COUNT(*) as total_days,
    SUM(status='Present') as present_days,
    SUM(status='Late') as late_days
FROM attendance
WHERE student_id=? AND YEAR(attendance_date)=?
";
$y_stmt = $conn->prepare($yearly_sql);
$y_stmt->bind_param("ii", $student_id, $year);
$y_stmt->execute();
$yearly = $y_stmt->get_result()->fetch_assoc();
$y_stmt->close();

            ?>

            <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


            <div class="container my-5">
                <div class="card shadow-lg border-0">

                    <!-- Header -->
                    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center bg-primary text-white">
                        <h4 class="mb-2 mb-md-0 text-white">
                            <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                        </h4>

                        <div class="d-flex flex-column flex-md-row gap-2">
                            <a href="edit_student.php?id=<?= $student['id'] ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                           
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="card-body p-4">

                        <!-- Photo + Admission -->
                        <div class="row mb-4">
                            <div class="col-12 col-md-3 text-center mb-3 mb-md-0">
                                <img src="<?= $photo ?>" class="img-fluid rounded-circle shadow-sm border" style="max-height:200px;">
                            </div>

                            <div class="col-12 col-md-9">
                                <h5 class="fw-bold text-secondary mb-3">Admission Details</h5>
                                <div class="row g-2">
                                    <div class="col-6 col-md-4"><strong>Student ID:</strong> <?= htmlspecialchars($student['student_id']) ?></div>
                                    <div class="col-6 col-md-4"><strong>Admission No:</strong> <?= htmlspecialchars($student['admission_no']) ?></div>
                                    <div class="col-6 col-md-4"><strong>Roll No:</strong> <?= htmlspecialchars($student['roll_number']) ?></div>
                                    <div class="col-6 col-md-4"><strong>Class:</strong> <?= htmlspecialchars($student['class_name']) ?></div>
                                    <div class="col-6 col-md-4"><strong>Section:</strong> <?= htmlspecialchars($student['section_name']) ?></div>
                                    <div class="col-6 col-md-4"><strong>Academic Year:</strong> <?= htmlspecialchars($student['academic_year']  ?? "") ?></div>
                                    <div class="col-6 col-md-4"><strong>DOB:</strong> <?= htmlspecialchars($student['dob']) ?></div>
                                    <div class="col-6 col-md-4"><strong>Gender:</strong> <?= htmlspecialchars($student['gender']) ?></div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Parent Info -->
                        <h5 class="fw-bold text-secondary mb-3">Parent / Guardian Info</h5>
                        <div class="row mb-4 g-4">
                            <div class="col-12 col-md-6 p-3 border rounded shadow-sm bg-light-subtle">
                                <h6 class="fw-bold mb-2">Father</h6>
                                <p><strong>Name:</strong> <?= htmlspecialchars($student['father_name']) ?: '-' ?></p>
                                <p><strong>Phone:</strong> <?= htmlspecialchars($student['parent_phone']) ?: '-' ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($student['parent_email']) ?: '-' ?></p>
                            </div>
                            <div class="col-12 col-md-6 p-3 border rounded shadow-sm bg-light-subtle">
                                <h6 class="fw-bold mb-2">Mother</h6>
                                <p><strong>Name:</strong> <?= htmlspecialchars($student['mother_name']) ?: '-' ?></p>
                                <p><strong>Phone:</strong> <?= htmlspecialchars($student['parent_phone']) ?: '-' ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($student['parent_email']) ?: '-' ?></p>
                            </div>
                        </div>

                        <hr>

                        <!-- Parent Login -->
                        <h5 class="fw-bold text-secondary mb-3">Parent Login Credentials</h5>
                        <div class="row mb-3 g-3 align-items-center">
                            <div class="col-md-5 p-3 border rounded shadow-sm">
                                <strong>Email (Login ID):</strong><br><?= htmlspecialchars($parent_email) ?: '-' ?>
                            </div>

                            <div class="col-md-5 p-3 border rounded shadow-sm">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Password:</strong><br>
                                        <span id="maskedPwd" style="letter-spacing:3px;">
                                            <?= $parent_plain_password ? str_repeat('•', strlen($parent_plain_password)) : '-' ?>
                                        </span>
                                    </div>
                                    <?php if ($parent_plain_password): ?>
                                        <button id="togglePwdBtn" class="btn btn-sm btn-outline-secondary ms-2">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" id="plainPassword" value="<?= htmlspecialchars($parent_plain_password) ?>">
                            </div>

                            <div class="col-md-2">
                                <button id="shareBtn" class="btn btn-primary w-100">
                                    <i class="bi bi-share"></i> Share
                                </button>
                            </div>
                        </div>

                        <hr>

                        <!-- Address -->
                        <h5 class="fw-bold text-secondary mb-3">Address</h5>
                        <div class="row mb-3 g-3">
                            <div class="col-md-6 p-3 border rounded shadow-sm">
                                <strong>Current Address:</strong><br>
                                <?= nl2br(htmlspecialchars($student['current_address'])) ?: '-' ?>
                            </div>
                            <div class="col-md-6 p-3 border rounded shadow-sm">
                                <strong>Permanent Address:</strong><br>
                                <?= nl2br(htmlspecialchars($student['permanent_address'])) ?: '-' ?>
                            </div>
                        </div>

                        <hr>

                        <!-- Transport -->
                        <h5 class="fw-bold text-secondary mb-3">Transport Details</h5>
                        <div class="row mb-3 g-2">
                            <div class="col-md-3"><strong>Has Transport:</strong> <?= displayYesNo($student['has_transport']) ?></div>
                            <div class="col-md-3"><strong>Vehicle No:</strong> <?= htmlspecialchars($student['vehicle_no']) ?: '-' ?></div>
                            <div class="col-md-3"><strong>Pickup Point:</strong> <?= htmlspecialchars($student['pickup_point']) ?: '-' ?></div>
                            <div class="col-md-3"><strong>Transport Fee:</strong> ₹<?= htmlspecialchars($student['transport_fee']) ?: '0' ?></div>
                        </div>

                        <hr>

                        <!-- Extra Info -->
                        <h5 class="fw-bold text-secondary mb-3">Other Info</h5>
                        <div class="row mb-3 g-3">
                            <div class="col-md-4 p-3 border rounded shadow-sm">
                                <strong>Previous School:</strong><br><?= nl2br(htmlspecialchars($student['previous_school'])) ?: '-' ?>
                            </div>
                            <div class="col-md-4 p-3 border rounded shadow-sm">
                                <strong>Blood Group:</strong><br><?= htmlspecialchars($student['blood_group']) ?: '-' ?>
                            </div>
                            <div class="col-md-4 p-3 border rounded shadow-sm">
                                <strong>Admission Year:</strong><br><?= htmlspecialchars($student['academic_year'] ?? "") ?>
                            </div>
                        </div>
                        
                        <hr>

<h5 class="fw-bold text-secondary mb-3">Attendance Report</h5>

<div class="row g-4 mb-4">

    <!-- Monthly Attendance -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-info text-white fw-bold">
                Monthly Attendance (<?= date('F Y') ?>)
            </div>
            <div class="card-body">
                <table class="table table-sm table-bordered mb-0">
                    <tr>
                        <th>Total Days</th>
                        <td><?= $monthly['total_days'] ?? 0 ?></td>
                    </tr>
                    <tr>
                        <th>Present</th>
                        <td class="text-success fw-bold"><?= $monthly['present_days'] ?? 0 ?></td>
                    </tr>
                    <tr>
                        <th>Late</th>
                        <td class="text-warning fw-bold"><?= $monthly['late_days'] ?? 0 ?></td>
                    </tr>
                    <tr>
                        <th>Absent</th>
                        <td class="text-danger fw-bold">
                            <?= max(0, ($monthly['total_days'] ?? 0) - (($monthly['present_days'] ?? 0) + ($monthly['late_days'] ?? 0))) ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Yearly Attendance -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white fw-bold">
                Yearly Attendance (<?= $year ?>)
            </div>
            <div class="card-body">
                <table class="table table-sm table-bordered mb-0">
                    <tr>
                        <th>Total Days</th>
                        <td><?= $yearly['total_days'] ?? 0 ?></td>
                    </tr>
                    <tr>
                        <th>Present</th>
                        <td class="text-success fw-bold"><?= $yearly['present_days'] ?? 0 ?></td>
                    </tr>
                    <tr>
                        <th>Late</th>
                        <td class="text-warning fw-bold"><?= $yearly['late_days'] ?? 0 ?></td>
                    </tr>
                    <tr>
                        <th>Absent</th>
                        <td class="text-danger fw-bold">
                            <?= max(0, ($yearly['total_days'] ?? 0) - (($yearly['present_days'] ?? 0) + ($yearly['late_days'] ?? 0))) ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

</div>


                    </div>
                </div>
            </div>

            <script>
                document.getElementById('togglePwdBtn')?.addEventListener('click', function() {
                    const mask = document.getElementById('maskedPwd');
                    const plain = document.getElementById('plainPassword').value;
                    const icon = this.querySelector('i');
                    if (mask.textContent.includes('•')) {
                        mask.textContent = plain;
                        icon.classList.replace('bi-eye', 'bi-eye-slash');
                    } else {
                        mask.textContent = '•'.repeat(plain.length);
                        icon.classList.replace('bi-eye-slash', 'bi-eye');
                    }
                });

                document.getElementById('shareBtn').addEventListener('click', () => {
                    var msg = `Login Email: <?= $parent_email ?>\nPassword: <?= $parent_plain_password ?>`;
                    navigator.clipboard.writeText(msg).then(() => {
                        Swal.fire("Copied!", "Credentials copied to clipboard", "success");
                    });
                });
            </script>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){
    $('#classSelect').on('change', function(){
        $('#filterForm').submit();
    });

    $('#generateBulk').on('click', function(){
        const classId = $('#classSelect').val();
        if(classId){
            const form = $('<form>', {
                action: '../actions/generate_idcard_action.php',
                method: 'POST',
                target: '_blank'
            }).append($('<input>', {type:'hidden', name:'class_id', value:classId}))
              .append($('<input>', {type:'hidden', name:'generate_bulk', value:1}));
            $('body').append(form);
            form.submit();
        } else {
            alert('Please select a class first.');
        }
    });

    $('#generateAll').on('click', function(){
        const form = $('<form>', {
            action: '../actions/generate_idcard_action.php',
            method: 'POST',
            target: '_blank'
        }).append($('<input>', {type:'hidden', name:'generate_all', value:1}));
        $('body').append(form);
        form.submit();
    });

    $('.generateSingle').on('click', function(){
        const studentId = $(this).data('id');
        const form = $('<form>', {
            action: '../actions/generate_idcard_action.php',
            method: 'POST',
            target: '_blank'
        }).append($('<input>', {type:'hidden', name:'student_id', value:studentId}))
          .append($('<input>', {type:'hidden', name:'generate_single', value:1}));
        $('body').append(form);
        form.submit();
    });
});
</script>




            <style>
                h4,
                h5,
                h6 {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                }

                .fw-bold {
                    font-weight: 600;
                }

                .card-body p {
                    margin-bottom: 0.25rem;
                }

                .bg-light-subtle {
                    background-color: #f9fafb !important;
                }
            </style>
            <style>
                .card-body,
                .card-body p,
                .card-body div,
                .card-body strong,
                .card-body span,
                .card-body h6 {
                    color: #000 !important;
                }
            </style>

        </div>

        <?php include "../includes/footer.php" ?>
    </div>
    <?php include "../includes/js_links.php" ?>