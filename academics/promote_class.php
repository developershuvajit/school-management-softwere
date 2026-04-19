<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

include('../config/database.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>School Management Softwere - Students</title>
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

        .badge-transport {
            padding: 4px 8px;
            font-size: 12px;
            border-radius: 4px;
        }

        .badge-yes {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-no {
            background-color: #f8d7da;
            color: #721c24;
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
    </style>
</head>

<body>
    <?php include "../includes/preloader.php" ?>
    <div id="main-wrapper">
        <?php include "../includes/navbar.php" ?>
        <?php include('../includes/sidebar_logic.php') ?>

        <div class="content-body">
            <div class="container-fluid">

                <!-- Page Header -->
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <?php include('../includes/welcome_text.php'); ?>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">Students</li>
                            <li class="breadcrumb-item active">Promote Students</li>
                        </ol>
                    </div>
                </div>

                <?php require_once __DIR__ . '/../includes/alert_helper.php'; ?>

                <!-- ================= FILTER CARD ================= -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-1"></i>
                            Step 1: Select Current Academic Details
                        </h5>
                    </div>

                    <div class="card-body">
                        <form method="GET">

                            <div class="row g-3">
                                <!-- Academic Year -->
                                <div class="col-md-4">
                                    <label class="form-label">Current Academic Year</label>
                                    <select name="fy" class="form-select" required>
                                        <option value="">-- Select Academic Year --</option>
                                        <?php
                                        $ay = $conn->query("SELECT id, academic_year FROM academic_years ORDER BY id DESC");
                                        while ($row = $ay->fetch_assoc()) {
                                            $sel = ($_GET['fy'] ?? '') == $row['id'] ? 'selected' : '';
                                            echo "<option value='{$row['id']}' $sel>{$row['academic_year']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Class -->
                                <div class="col-md-4">
                                    <label class="form-label">Current Class</label>
                                    <select name="fc" class="form-select" required>
                                        <option value="">-- Select Class --</option>
                                        <?php
                                        $cls = $conn->query("SELECT id, class_name FROM classes ORDER BY id");
                                        while ($c = $cls->fetch_assoc()) {
                                            $sel = ($_GET['fc'] ?? '') == $c['id'] ? 'selected' : '';
                                            echo "<option value='{$c['id']}' $sel>{$c['class_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Section -->
                                <div class="col-md-4">
                                    <label class="form-label">Section (Optional)</label>
                                    <select name="fs" class="form-select">
                                        <option value="">All Sections</option>
                                        <?php
                                        $sec = $conn->query("SELECT id, section_name FROM sections ORDER BY section_name");
                                        while ($s = $sec->fetch_assoc()) {
                                            $sel = ($_GET['fs'] ?? '') == $s['id'] ? 'selected' : '';
                                            echo "<option value='{$s['id']}' $sel>{$s['section_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-3 text-end">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Load Students
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

                <!-- ================= STUDENT LIST & PROMOTION ================= -->
                <?php if (!empty($_GET['fy']) && !empty($_GET['fc'])): ?>

                    <form action="../actions/promote_students_action.php" method="POST">

                        <input type="hidden" name="from_academic_year" value="<?= (int)$_GET['fy'] ?>">
                        <input type="hidden" name="from_class" value="<?= (int)$_GET['fc'] ?>">

                        <!-- Promote To Card -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-arrow-up me-1"></i>
                                    Step 2: Promotion Details
                                </h5>
                            </div>

                            <div class="card-body">
                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label class="form-label">Promote To Academic Year</label>
                                        <select name="to_academic_year" class="form-select" required>
                                            <option value="">-- Select Academic Year --</option>
                                            <?php
                                            $ay->data_seek(0);
                                            while ($row = $ay->fetch_assoc()) {
                                                echo "<option value='{$row['id']}'>{$row['academic_year']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Promote To Class</label>
                                        <select name="to_class" class="form-select" required>
                                            <option value="">-- Select Class --</option>
                                            <?php
                                            $cls->data_seek(0);
                                            while ($c = $cls->fetch_assoc()) {
                                                echo "<option value='{$c['id']}'>{$c['class_name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Student Table -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-users me-1"></i>
                                    Step 3: Select Students to Promote
                                </h5>
                            </div>

                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm mb-0">
                                        <thead class="text-center">
                                            <tr>
                                                <th width="40">
                                                    <input type="checkbox" id="selectAll">
                                                </th>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Section</th>
                                                <th>Roll</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            <?php
                                            $fy = (int)$_GET['fy'];
                                            $fc = (int)$_GET['fc'];
                                            $fs = !empty($_GET['fs']) ? "AND s.section_id=" . (int)$_GET['fs'] : "";

                                            $sql = "
                                    SELECT s.id, s.first_name, s.last_name, s.roll_number,
                                           sec.section_name
                                    FROM students s
                                    LEFT JOIN sections sec ON sec.id = s.section_id
                                    WHERE s.academic_year_id = $fy
                                      AND s.class_id = $fc
                                      $fs
                                    ORDER BY s.roll_number ASC
                                ";

                                            $res = $conn->query($sql);
                                            $i = 1;

                                            if ($res->num_rows > 0):
                                                while ($st = $res->fetch_assoc()):
                                            ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" name="student_ids[]" value="<?= $st['id'] ?>" class="studentCheckbox">
                                                        </td>
                                                        <td><?= $i++ ?></td>
                                                        <td class="text-start">
                                                            <?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name']) ?>
                                                        </td>
                                                        <td><?= $st['section_name'] ?></td>
                                                        <td><?= $st['roll_number'] ?></td>
                                                    </tr>
                                                <?php endwhile;
                                            else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-muted py-3">
                                                        No students found for selected criteria
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check-circle"></i>
                                    Promote Selected Students
                                </button>
                            </div>
                        </div>

                    </form>
                <?php endif; ?>

            </div>
        </div>

        <script>
            document.getElementById('selectAll')?.addEventListener('change', function() {
                document.querySelectorAll('.studentCheckbox').forEach(cb => cb.checked = this.checked);
            });
        </script>


        <!-- JS -->
         


        <?php include "../includes/footer.php" ?>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables & Export Buttons -->

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
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-primary btn-sm',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        }
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-danger btn-sm',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        },
                        customize: function(doc) {
                            doc.content[1].table.widths = ['5%', '25%', '10%', '10%', '10%', '10%', '20%'];
                        }
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-secondary btn-sm',
                        text: '<i class="fas fa-print"></i> Print',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6]
                        },
                        customize: function(win) {
                            $(win.document.body).find('table').addClass('table-sm');
                            $(win.document.body).find('h1').css('text-align', 'center');
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
                    // Make buttons visible
                    $('.dt-buttons').removeClass('d-none');
                }
            });

            // Apply filter on class select change
            $('#classFilter').on('change', function() {
                table.column(2).search(this.value).draw();
            });

            // Apply filter on section select change
            $('#sectionFilter').on('change', function() {
                table.column(3).search(this.value).draw();
            });

            // Apply filter on transport select change
            $('#transportFilter').on('change', function() {
                table.column(5).search(this.value).draw();
            });

            // Apply search on student name/roll no
            $('#studentSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Reset all filters
            $('#resetFilters').on('click', function() {
                $('#classFilter, #sectionFilter, #transportFilter').val('');
                $('#studentSearch').val('');
                table.search('').columns().search('').draw();
            });

            // Column-specific search for transport column
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var transport = $('#transportFilter').val();
                    if (!transport) return true;

                    var rowTransport = data[5];
                    var hasTransport = rowTransport.includes('Yes') ? 'Yes' : 'No';
                    return hasTransport === transport;
                }
            );
        });

        // SweetAlert for delete confirmation
        function confirmDelete(id) {
            Swal.fire({
                title: "Are you sure?",
                text: "This student and parent will be permanently deleted!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../actions/student_delete.php?delete=" + id;
                }
            });
        }
    </script>

    <?php include "../includes/js_links.php" ?>
</body>

</html>