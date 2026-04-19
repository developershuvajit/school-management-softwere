<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

include('../config/database.php');

// Handle promotion request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promote_students'])) {
    $current_year_id = (int)$_POST['current_year_id'];
    $current_class_id = (int)$_POST['current_class_id'];
    $next_year_id = (int)$_POST['next_year_id'];
    $next_class_id = (int)$_POST['next_class_id'];

    // Get academic year names for display
    $current_year_sql = "SELECT academic_year FROM academic_years WHERE id = $current_year_id";
    $next_year_sql = "SELECT academic_year FROM academic_years WHERE id = $next_year_id";
    $current_year_result = $conn->query($current_year_sql);
    $next_year_result = $conn->query($next_year_sql);
    $current_year_name = $current_year_result->fetch_assoc()['academic_year'];
    $next_year_name = $next_year_result->fetch_assoc()['academic_year'];

    // Get all students from current class and year
    $students_sql = "SELECT id FROM students WHERE class_id = $current_class_id AND academic_year_id = $current_year_id AND status = 'active'";
    $students_result = $conn->query($students_sql);

    $promoted_count = 0;
    $error_count = 0;

    while ($student = $students_result->fetch_assoc()) {
        $student_id = (int)$student['id'];

        // Check if student already exists in next class/year
        $check_sql = "SELECT id FROM students WHERE id = $student_id AND academic_year_id = $next_year_id AND class_id = $next_class_id";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows == 0) {
            // Promote student to next class/year
            $promote_sql = "UPDATE students SET 
                           class_id = $next_class_id,
                           academic_year_id = $next_year_id,
                           roll_number = NULL,  // Reset roll number for reassignment
                           section_id = NULL    // Reset section for reassignment
                           WHERE id = $student_id";

            if ($conn->query($promote_sql)) {
                $promoted_count++;
            } else {
                $error_count++;
                error_log("Promotion error for student $student_id: " . $conn->error);
            }
        }
    }

    // Store results in session for display
    $_SESSION['promotion_result'] = [
        'success' => true,
        'promoted_count' => $promoted_count,
        'error_count' => $error_count,
        'current_year' => $current_year_name,
        'next_year' => $next_year_name,
        'current_class_name' => $_POST['current_class_name'],
        'next_class_name' => $_POST['next_class_name']
    ];

    // Redirect to same page to avoid form resubmission
    header('Location: promote_class.php');
    exit;
}

// Get academic years from database
$years_sql = "SELECT id, academic_year FROM academic_years ORDER BY academic_year DESC";
$years_result = $conn->query($years_sql);
$academic_years = [];
while ($year = $years_result->fetch_assoc()) {
    $academic_years[] = $year;
}

// If no academic years found, create default
if (empty($academic_years)) {
    $current_year = date('Y');
    $insert_sql = "INSERT INTO academic_years (academic_year, description) VALUES ('$current_year', 'Default academic year')";
    $conn->query($insert_sql);
    $default_year_id = $conn->insert_id;
    $academic_years = [['id' => $default_year_id, 'academic_year' => $current_year]];
}

// Get classes
$classes_sql = "SELECT id, class_name FROM classes ORDER BY class_name";
$classes_result = $conn->query($classes_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>School India Junior - Promote Class</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <!-- SweetAlert2 & FontAwesome -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .card {
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
        }

        .card-title {
            color: #2c3e50;
            font-weight: 600;
            margin: 0;
        }

        .form-label {
            color: #495057;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .promote-arrow {
            font-size: 24px;
            color: #28a745;
            text-align: center;
            margin: 20px 0;
        }

        .student-count-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .student-count-number {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .student-count-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .promotion-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .btn-promote {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            font-size: 16px;
        }

        .btn-promote:hover {
            background: linear-gradient(135deg, #218838 0%, #1aa179 100%);
            color: white;
        }

        .table-sm {
            font-size: 13px;
        }

        .table-sm th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            padding: 10px 8px !important;
        }

        .table-sm td {
            padding: 8px !important;
            color: #495057;
            vertical-align: middle;
        }
    </style>
</head>

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
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Students</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Promote Class</a></li>
                        </ol>
                    </div>
                </div>

                <?php
                // Show promotion result if available
                if (isset($_SESSION['promotion_result'])) {
                    $result = $_SESSION['promotion_result'];
                    unset($_SESSION['promotion_result']);

                    if ($result['success']) {
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> Successfully promoted ' . $result['promoted_count'] . ' students from ' .
                            $result['current_class_name'] . ' (' . $result['current_year'] . ') to ' .
                            $result['next_class_name'] . ' (' . $result['next_year'] . ')' .
                            ($result['error_count'] > 0 ? '<br><small>' . $result['error_count'] . ' students could not be promoted.</small>' : '') . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
                    }
                }

                require_once __DIR__ . '/../includes/alert_helper.php';
                ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h4 class="card-title mb-2 mb-sm-0">Promote Students to Next Class</h4>
                                <a href="manage_promoted.php" class="btn btn-info btn-sm">
                                    <i class="fas fa-users"></i> Manage Promoted Students
                                </a>
                            </div>

                            <div class="card-body">
                                <form id="promotionForm" method="POST" action="promote_class.php">
                                    <div class="row">
                                        <!-- Current Academic Year & Class -->
                                        <div class="col-md-5">
                                            <div class="promotion-info">
                                                <h5 class="mb-3" style="color: #2c3e50;">
                                                    <i class="fas fa-graduation-cap text-primary"></i> Current Class
                                                </h5>

                                                <div class="mb-3">
                                                    <label class="form-label">Academic Year</label>
                                                    <select name="current_year_id" id="currentYear" class="form-select" required>
                                                        <option value="">Select Academic Year</option>
                                                        <?php foreach ($academic_years as $year): ?>
                                                            <option value="<?= $year['id'] ?>">
                                                                <?= htmlspecialchars($year['academic_year']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Select Class</label>
                                                    <select name="current_class_id" id="currentClass" class="form-select" required>
                                                        <option value="">Select Current Class</option>
                                                        <?php
                                                        $classes_result->data_seek(0);
                                                        while ($class = $classes_result->fetch_assoc()):
                                                        ?>
                                                            <option value="<?= $class['id'] ?>" data-name="<?= htmlspecialchars($class['class_name']) ?>">
                                                                <?= htmlspecialchars($class['class_name']) ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                    <input type="hidden" name="current_class_name" id="currentClassName">
                                                </div>

                                                <!-- Student Count -->
                                                <div id="studentCountContainer" class="d-none">
                                                    <div class="student-count-card">
                                                        <div class="student-count-number" id="studentCount">0</div>
                                                        <div class="student-count-label">Students Found</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Arrow -->
                                        <div class="col-md-2 d-flex align-items-center justify-content-center">
                                            <div class="promote-arrow">
                                                <i class="fas fa-long-arrow-alt-right"></i>
                                            </div>
                                        </div>

                                        <!-- Next Academic Year & Class -->
                                        <div class="col-md-5">
                                            <div class="promotion-info">
                                                <h5 class="mb-3" style="color: #2c3e50;">
                                                    <i class="fas fa-arrow-up text-success"></i> Next Class
                                                </h5>

                                                <div class="mb-3">
                                                    <label class="form-label">Next Academic Year</label>
                                                    <select name="next_year_id" id="nextYear" class="form-select" required>
                                                        <option value="">Select Next Academic Year</option>
                                                        <?php
                                                        // Get next academic years (create if not exists)
                                                        $next_years_sql = "SELECT id, academic_year FROM academic_years ORDER BY academic_year ASC";
                                                        $next_years_result = $conn->query($next_years_sql);
                                                        while ($next_year = $next_years_result->fetch_assoc()):
                                                        ?>
                                                            <option value="<?= $next_year['id'] ?>">
                                                                <?= htmlspecialchars($next_year['academic_year']) ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Select Next Class</label>
                                                    <select name="next_class_id" id="nextClass" class="form-select" required>
                                                        <option value="">Select Next Class</option>
                                                        <?php
                                                        $classes_result->data_seek(0);
                                                        while ($class = $classes_result->fetch_assoc()):
                                                        ?>
                                                            <option value="<?= $class['id'] ?>" data-name="<?= htmlspecialchars($class['class_name']) ?>">
                                                                <?= htmlspecialchars($class['class_name']) ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                    <input type="hidden" name="next_class_name" id="nextClassName">
                                                </div>

                                                <!-- Student Preview -->
                                                <div id="studentPreview" class="d-none">
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle"></i>
                                                        <span id="previewText">Students will be promoted as shown</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Preview Students Table -->
                                    <div class="row mt-4 d-none" id="studentsTableContainer">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">Students to be Promoted</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table id="previewTable" class="table table-bordered table-striped table-hover align-middle w-100 table-sm">
                                                            <thead class="text-center">
                                                                <tr>
                                                                    <th width="50">#</th>
                                                                    <th>Student ID</th>
                                                                    <th>Student Name</th>
                                                                    <th>Current Class</th>
                                                                    <th>Current Section</th>
                                                                    <th>Roll No</th>
                                                                    <th>Next Class</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <!-- Will be populated by AJAX -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="row mt-4">
                                        <div class="col-12 text-center">
                                            <button type="button" id="previewBtn" class="btn btn-primary btn-lg me-3">
                                                <i class="fas fa-eye"></i> Preview Students
                                            </button>
                                            <button type="submit" name="promote_students" id="promoteBtn" class="btn btn-promote btn-lg d-none">
                                                <i class="fas fa-graduation-cap"></i> Promote Students
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include "../includes/footer.php" ?>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            // Store class names for reference
            var classNames = {};
            <?php
            $classes_result->data_seek(0);
            while ($class = $classes_result->fetch_assoc()):
            ?>
                classNames[<?= $class['id'] ?>] = "<?= addslashes($class['class_name']) ?>";
            <?php endwhile; ?>

            // Update hidden inputs with class names
            $('#currentClass').on('change', function() {
                var selected = $(this).find('option:selected');
                var className = selected.data('name');
                $('#currentClassName').val(className);
                loadStudentCount();
            });

            $('#nextClass').on('change', function() {
                var selected = $(this).find('option:selected');
                var className = selected.data('name');
                $('#nextClassName').val(className);
                updatePreviewText();
            });

            // Load student count for selected class/year
            function loadStudentCount() {
                var currentYearId = $('#currentYear').val();
                var currentClassId = $('#currentClass').val();

                if (currentYearId && currentClassId) {
                    $.ajax({
                        url: '../actions/get_student_count.php',
                        method: 'POST',
                        data: {
                            academic_year_id: currentYearId,
                            class_id: currentClassId
                        },
                        success: function(response) {
                            var data = JSON.parse(response);
                            if (data.success) {
                                $('#studentCount').text(data.count);
                                $('#studentCountContainer').removeClass('d-none');
                            }
                        }
                    });
                } else {
                    $('#studentCountContainer').addClass('d-none');
                }
            }

            // Update preview text
            function updatePreviewText() {
                var currentClass = $('#currentClass option:selected').data('name');
                var nextClass = $('#nextClass option:selected').data('name');
                var currentYearText = $('#currentYear option:selected').text();
                var nextYearText = $('#nextYear option:selected').text();

                if (currentClass && nextClass && currentYearText && nextYearText) {
                    var text = 'From ' + currentClass + ' (' + currentYearText + ') to ' + nextClass + ' (' + nextYearText + ')';
                    $('#previewText').text(text);
                    $('#studentPreview').removeClass('d-none');
                } else {
                    $('#studentPreview').addClass('d-none');
                }
            }

            // Preview students button
            $('#previewBtn').on('click', function() {
                var currentYearId = $('#currentYear').val();
                var currentClassId = $('#currentClass').val();
                var nextYearId = $('#nextYear').val();
                var nextClassId = $('#nextClass').val();

                if (!currentYearId || !currentClassId || !nextYearId || !nextClassId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Information',
                        text: 'Please select all fields before previewing students.'
                    });
                    return;
                }

                // Show loading
                $(this).html('<i class="fas fa-spinner fa-spin"></i> Loading...').prop('disabled', true);

                $.ajax({
                    url: '../actions/get_students_by_class.php',
                    method: 'POST',
                    data: {
                        academic_year_id: currentYearId,
                        class_id: currentClassId
                    },
                    success: function(response) {
                        $('#previewBtn').html('<i class="fas fa-eye"></i> Preview Students').prop('disabled', false);

                        var data = JSON.parse(response);
                        if (data.success && data.students.length > 0) {
                            // Populate table
                            var tbody = $('#previewTable tbody');
                            tbody.empty();

                            $.each(data.students, function(index, student) {
                                var row = '<tr>' +
                                    '<td class="text-center">' + (index + 1) + '</td>' +
                                    '<td>' + student.student_id + '</td>' +
                                    '<td>' + student.first_name + ' ' + student.last_name + '</td>' +
                                    '<td>' + student.class_name + '</td>' +
                                    '<td>' + (student.section_name || '-') + '</td>' +
                                    '<td>' + (student.roll_number || '-') + '</td>' +
                                    '<td>' + classNames[nextClassId] + '</td>' +
                                    '</tr>';
                                tbody.append(row);
                            });

                            // Show table and promote button
                            $('#studentsTableContainer').removeClass('d-none');
                            $('#promoteBtn').removeClass('d-none');

                            // Initialize DataTable
                            $('#previewTable').DataTable({
                                responsive: true,
                                pageLength: 10,
                                lengthMenu: [10, 25, 50],
                                searching: false,
                                ordering: false,
                                info: false
                            });

                            // Scroll to table
                            $('html, body').animate({
                                scrollTop: $('#studentsTableContainer').offset().top - 100
                            }, 500);

                        } else {
                            Swal.fire({
                                icon: 'info',
                                title: 'No Students Found',
                                text: 'No students found in the selected class and academic year.'
                            });
                        }
                    },
                    error: function() {
                        $('#previewBtn').html('<i class="fas fa-eye"></i> Preview Students').prop('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load students. Please try again.'
                        });
                    }
                });
            });

            // Form submission confirmation
            $('#promotionForm').on('submit', function(e) {
                var currentClass = $('#currentClass option:selected').data('name');
                var nextClass = $('#nextClass option:selected').data('name');
                var studentCount = $('#studentCount').text();

                e.preventDefault();

                Swal.fire({
                    title: 'Confirm Promotion',
                    html: '<div class="text-start">' +
                        '<p>Are you sure you want to promote <strong>' + studentCount + ' students</strong>?</p>' +
                        '<div class="alert alert-warning">' +
                        '<i class="fas fa-exclamation-triangle"></i> ' +
                        '<strong>Important:</strong> This action cannot be undone. Roll numbers and sections will be reset and need to be reassigned.' +
                        '</div>' +
                        '</div>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Promote Students',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Promoting Students...',
                            text: 'Please wait while we promote the students.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Submit the form
                        this.submit();
                    }
                });
            });

            // Auto-update preview text when fields change
            $('#currentYear, #currentClass, #nextYear, #nextClass').on('change', function() {
                updatePreviewText();
                // Hide table if fields change
                $('#studentsTableContainer').addClass('d-none');
                $('#promoteBtn').addClass('d-none');
            });
        });
    </script>

    <?php include "../includes/js_links.php" ?>
</body>

</html>