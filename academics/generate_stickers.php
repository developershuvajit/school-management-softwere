<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

include('../config/database.php');

// Fetch all classes for the class filter dropdown

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>School India Junior - Student Stickers</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css">
    <!-- SweetAlert2 & FontAwesome -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Custom styling for compact professional table */
        .dataTables_wrapper .dt-buttons {
            margin-bottom: 15px;
        }

        .dataTables_wrapper .dt-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 4px;
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

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .filter-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .filter-section .form-label {
            color: #495057;
            font-weight: 500;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .form-select-sm,
        .form-control-sm {
            font-size: 13px;
            height: calc(1.5em + 0.75rem + 2px);
        }

        .action-buttons .btn {
            padding: 4px 8px;
            font-size: 12px;
            margin: 2px;
        }

        .student-name {
            color: #2c3e50;
            font-weight: 500;
        }

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

        #copiesInput {
            width: 100px;
            display: inline-block;
        }

        .badge-class {
            background-color: #e3f2fd;
            color: #1565c0;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .badge-section {
            background-color: #f3e5f5;
            color: #7b1fa2;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .btn-sticker {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }

        .btn-sticker:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
            color: white;
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
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Tools</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Stickers Generator</a></li>
                        </ol>
                    </div>
                </div>

                <?php require_once __DIR__ . '/../includes/alert_helper.php'; ?>
 

                <div class="row">
                    <?php $classQuery = $conn->query("SELECT id, class_name FROM classes ORDER BY class_name ASC");

// Fetch all sections for the section filter dropdown
$sectionQuery = $conn->query("SELECT DISTINCT section_name FROM sections WHERE section_name IS NOT NULL ORDER BY section_name ASC");?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h4 class="card-title mb-2 mb-sm-0">Student Stickers Generator</h4>
                                <div>
                                    <a href="generate_stickers.php" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-redo"></i> Reset Filter
                                    </a>
                                </div>
                            </div>

                            <div class="card-body text-dark">
                                <!-- Bulk Actions Section -->
                                <div class="filter-section">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label">Bulk Actions - Select Class</label>
                                            <select id="bulkClassSelect" class="form-select form-select-sm">
                                                <option value="">-- Select a Class for Bulk Action --</option>
                                                <?php
                                                if ($classQuery && $classQuery->num_rows > 0) {
                                                    while ($c = $classQuery->fetch_assoc()) {
                                                        echo "<option value='" . htmlspecialchars($c['id']) . "'>" . htmlspecialchars($c['class_name']) . "</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                      


                                        <div class="col-md-3">
                                            <label class="form-label">Copies per Student</label>
                                            <input type="number" id="copiesInput" class="form-control form-control-sm" min="1" max="100" value="1">
                                            <small class="text-muted">1–100 copies</small>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="d-flex gap-2">
                                                <button type="button" id="generateBulk" class="btn btn-success btn-sm flex-grow-1">
                                                    <i class="fas fa-id-card"></i> Generate for Selected Class
                                                </button>
                                                <button type="button" id="generateAll" class="btn btn-primary btn-sm flex-grow-1">
                                                    <i class="fas fa-id-card"></i> Generate All Classes
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Filter Section -->
                                <div class="filter-section">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Filter by Class</label>
                                            <select id="classFilter" class="form-select form-select-sm">
                                                <option value="">All Classes</option>
                                                <?php
                                                $classQuery->data_seek(0); // Reset pointer
                                                while ($c = $classQuery->fetch_assoc()) {
                                                    echo "<option value='" . htmlspecialchars($c['class_name']) . "'>" . htmlspecialchars($c['class_name']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Filter by Section</label>
                                            <select id="sectionFilter" class="form-select form-select-sm">
                                                <option value="">All Sections</option>
                                                <?php
                                                if ($sectionQuery && $sectionQuery->num_rows > 0) {
                                                    while ($sec = $sectionQuery->fetch_assoc()) {
                                                        echo "<option value='" . htmlspecialchars($sec['section_name']) . "'>" . htmlspecialchars($sec['section_name']) . "</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        
                                         <?php $yearQuery = $conn->query("
    SELECT DISTINCT ay.id, ay.academic_year
    FROM students s
    LEFT JOIN academic_years ay ON ay.id = s.academic_year_id
    ORDER BY ay.academic_year DESC
");
?>                                         
                                       <div class="col-md-3">
    <label class="form-label">Admission Year</label>
    <select id="academicYears" class="form-select form-select-sm">
        <option value="">All Years</option>
        <?php
        if ($yearQuery && $yearQuery->num_rows > 0) {
            while ($y = $yearQuery->fetch_assoc()) {
                echo "<option value='{$y['academic_year']}'>
                        {$y['academic_year']}
                      </option>";
            }
        }
        ?>
    </select>
</div>
                                        <div class="col-md-3">
                                            <label class="form-label">&nbsp;</label>
                                            <button id="resetFilters" class="btn btn-outline-secondary btn-sm w-100">
                                                <i class="fas fa-redo"></i> Reset Filters
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Students Table -->
                                <div class="table-responsive">
                                    <table id="studentsTable" class="table table-bordered table-striped table-hover align-middle w-100 table-sm">
                                        <thead class="text-center">
                                            <tr>
                                                <th width="50">#</th>
                                                <th width="80">Photo</th>
                                                <th>Student ID</th>
                                                <th>Full Name</th>
                                                <th>Class</th>
                                                <th>Section</th>
                                                <th>Academic Year</th>

                                                <th width="120">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "
    SELECT 
        s.id,
        s.student_id,
        s.first_name,
        s.last_name,
        s.photo,
        c.class_name,
        sec.section_name,
        ay.academic_year
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    LEFT JOIN sections sec ON s.section_id = sec.id
    LEFT JOIN academic_years ay ON ay.id = s.academic_year_id
    ORDER BY c.class_name ASC, sec.section_name ASC, s.first_name ASC
";


                                            $result = $conn->query($sql);
                                            $i = 1;

                                            if ($result && $result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    $photoPath = "../assets/images/default-avatar.png";
                                                    if (!empty($row['photo'])) {
                                                        $possiblePath = "../" . $row['photo'];
                                                        if (file_exists($possiblePath)) $photoPath = $possiblePath;
                                                    }

                                                    $firstName = $row['first_name'] ?? '';
                                                    $lastName  = $row['last_name'] ?? '';
                                                    $fullName  = trim("$firstName $lastName");
                                                    $studentId = $row['student_id'] ?? '';
                                                    $className = $row['class_name'] ?? '';
                                                    $sectionName = $row['section_name'] ?? '';
                                                    $academicYear = $row['academic_year'] ?? '';

                                                    echo "
                                                    <tr class='text-center'>
                                                        <td class='text-dark'>{$i}</td>
                                                        <td>
                                                            <img src='{$photoPath}' alt='Student Photo' width='45' height='45' class='rounded-circle border'>
                                                        </td>
                                                        <td class='text-dark'>{$studentId}</td>
                                                        <td class='text-start fw-semibold text-dark'>{$fullName}</td>
                                                        <td>{$className}</td>
                                                        <td>{$sectionName}</td>
                                                        <td>{$academicYear}</td>
                                                        <td>
                                                            <button type='button' class='btn btn-sm btn-sticker generateSingle'
                                                                    data-id='" . htmlspecialchars($studentId) . "'
                                                                    data-name='" . htmlspecialchars($fullName) . "'
                                                                    data-class='" . htmlspecialchars($className) . "'
                                                                    data-section='" . htmlspecialchars($sectionName) . "'
                                                                    data-photo='" . htmlspecialchars($photoPath) . "'>
                                                                <i class='fas fa-id-card'></i> Generate
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    ";
                                                    $i++;
                                                }
                                            } else {
                                                echo "<tr><td colspan='7' class='text-center text-muted py-3'>No students found</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
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

    <!-- DataTables & Export Buttons -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <!-- Custom JS -->
    <script>
        $(document).ready(function() {
            // Initialize DataTable with export buttons
            var table = $('#studentsTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [{
                        extend: 'excel',
                        className: 'btn btn-success btn-sm',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        exportOptions: {
                            columns: [0, 2, 3, 4, 5]
                        }
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-primary btn-sm',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        exportOptions: {
                            columns: [0, 2, 3, 4, 5]
                        }
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-danger btn-sm',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        exportOptions: {
                            columns: [0, 2, 3, 4, 5]
                        }
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-secondary btn-sm',
                        text: '<i class="fas fa-print"></i> Print',
                        exportOptions: {
                            columns: [0, 2, 3, 4, 5]
                        }
                    }
                ],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                initComplete: function() {
                    $('.dt-buttons').removeClass('d-none');
                }
            });

            // Apply filter on class select change (without page reload)
            $('#classFilter').on('change', function() {
                if (this.value === '') {
                    table.column(4).search('').draw();
                } else {
                    table.column(4).search(this.value).draw();
                }
            });

            // Apply filter on section select change (without page reload)
            $('#sectionFilter').on('change', function() {
                if (this.value === '') {
                    table.column(5).search('').draw();
                } else {
                    table.column(5).search(this.value).draw();
                }
            });
            
              // Apply filter on academic years select change (without page reload)
             $('#academicYears').on('change', function() {
                if (this.value === '') {
                    table.column(5).search('').draw();
                } else {
                    table.column(5).search(this.value).draw();
                }
            });

            // Apply search on student name/ID (without page reload)
            $('#studentSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Reset all filters
            $('#resetFilters').on('click', function() {
                $('#classFilter, #sectionFilter').val('');
                $('#studentSearch').val('');
                table.search('').columns().search('').draw();
            });

            // ================== ORIGINAL GENERATE STICKERS LOGIC ==================

            // Generate for selected class
            $('#generateBulk').on('click', function() {
                const classId = $('#bulkClassSelect').val();
                let copies = parseInt($('#copiesInput').val() || '1', 10);
                if (isNaN(copies) || copies < 1) copies = 1;
                if (copies > 100) copies = 100;

                if (classId) {
                    const form = $('<form>', {
                        action: '../actions/generate_stickers_action.php',
                        method: 'POST',
                        target: '_blank'
                    }).append($('<input>', {
                        type: 'hidden',
                        name: 'class_id',
                        value: classId
                    })).append($('<input>', {
                        type: 'hidden',
                        name: 'generate_bulk',
                        value: 1
                    })).append($('<input>', {
                        type: 'hidden',
                        name: 'copies',
                        value: copies
                    }));
                    $('body').append(form);
                    form.submit();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Select a class first',
                        text: 'Please select a class from the dropdown to generate stickers for that class.'
                    });
                }
            });

            // Generate for all classes
            $('#generateAll').on('click', function() {
                let copies = parseInt($('#copiesInput').val() || '1', 10);
                if (isNaN(copies) || copies < 1) copies = 1;
                if (copies > 100) copies = 100;

                const form = $('<form>', {
                    action: '../actions/generate_stickers_action.php',
                    method: 'POST',
                    target: '_blank'
                }).append($('<input>', {
                    type: 'hidden',
                    name: 'generate_all',
                    value: 1
                })).append($('<input>', {
                    type: 'hidden',
                    name: 'copies',
                    value: copies
                }));
                $('body').append(form);
                form.submit();
            });

            // Generate for single student
            $(document).on('click', '.generateSingle', function() {
                const studentId = $(this).data('id');
                const studentName = $(this).data('name');

                Swal.fire({
                    title: 'Generate Stickers',
                    html: `How many copies for <b>${studentName}</b>?`,
                    input: 'number',
                    inputValue: 1,
                    inputAttributes: {
                        min: 1,
                        max: 100,
                        step: 1
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Generate',
                    cancelButtonText: 'Cancel',
                    showLoaderOnConfirm: true,
                    preConfirm: (copies) => {
                        if (!copies || copies < 1 || copies > 100) {
                            Swal.showValidationMessage('Please enter a number between 1 and 100');
                        }
                        return copies;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const copies = parseInt(result.value, 10);
                        const form = $('<form>', {
                            action: '../actions/generate_stickers_action.php',
                            method: 'POST',
                            target: '_blank'
                        }).append($('<input>', {
                            type: 'hidden',
                            name: 'student_id',
                            value: studentId
                        })).append($('<input>', {
                            type: 'hidden',
                            name: 'generate_single',
                            value: 1
                        })).append($('<input>', {
                            type: 'hidden',
                            name: 'copies',
                            value: copies
                        }));
                        $('body').append(form);
                        form.submit();
                    }
                });
            });
        });
    </script>

    <?php include "../includes/js_links.php" ?>
</body>

</html>