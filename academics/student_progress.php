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
    <title>School India Junior - Students</title>
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
                            <li class="breadcrumb-item active">All Students</li>
                        </ol>
                    </div>
                </div>

                <?php require_once __DIR__ . '/../includes/alert_helper.php'; ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            
                             <div class="container-fluid">

            <h4 class="mb-3">Student Progress</h4>

            <!-- FILTERS -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <select id="academicYearFilter" class="form-control form-control-sm">
                        <option value="">All Academic Years</option>
                        <?php
                        $ay = $conn->query("SELECT academic_year FROM academic_years ORDER BY academic_year DESC");
                        while ($r = $ay->fetch_assoc()) {
                            echo "<option>{$r['academic_year']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <select id="classFilter" class="form-control form-control-sm">
                        <option value="">All Classes</option>
                        <?php
                        $cls = $conn->query("SELECT class_name FROM classes ORDER BY class_name");
                        while ($c = $cls->fetch_assoc()) {
                            echo "<option>{$c['class_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <select id="sectionFilter" class="form-control form-control-sm">
                        <option value="">All Sections</option>
                        <?php
                        $sec = $conn->query("SELECT section_name FROM sections GROUP BY section_name");
                        while ($s = $sec->fetch_assoc()) {
                            echo "<option>{$s['section_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <select id="transportFilter" class="form-control form-control-sm">
                        <option value="">All Transport</option>
                        <option value="Yes">With Transport</option>
                        <option value="No">Without Transport</option>
                    </select>
                </div>

                <div class="col-md-9 mt-2">
                    <input type="text" id="studentSearch" class="form-control form-control-sm" placeholder="Search student">
                </div>

                <div class="col-md-3 mt-2">
                    <button id="resetFilters" class="btn btn-secondary btn-sm w-100">Reset</button>
                </div>
            </div>

            <!-- TABLE -->
            <table id="studentsTable" class="table table-bordered table-striped table-sm w-100">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Academic Year</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Roll</th>
                        <th>Transport</th>
                        <th>Parent Phone</th>
                        <th>Photo</th>
                        <th>Action</th>
                        
                    </tr>
                </thead>
                <tbody class="text-center">

                                    <?php
                                    $sql = "
                SELECT s.*, c.class_name, sec.section_name, a.academic_year
                FROM students s
                LEFT JOIN classes c ON c.id=s.class_id
                LEFT JOIN sections sec ON sec.id=s.section_id
                LEFT JOIN academic_years a ON a.id=s.academic_year_id
                ORDER BY s.id ASC";
                    $res = $conn->query($sql);
                    $i = 1;

                    while ($row = $res->fetch_assoc()):
                        $photo = $row['photo'] ? '../' . $row['photo'] : '../assets/img/default-student.png';
                        $transportText = $row['has_transport'] ? 'Yes' : 'No';
                    ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td class="text-start"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td><?= $row['academic_year'] ?></td>
                            <td><?= $row['class_name'] ?></td>
                            <td><?= $row['section_name'] ?></td>
                            <td><?= $row['roll_number'] ?></td>
                            <td>
                                <span class="badge <?= $transportText == 'Yes' ? 'badge-yes' : 'badge-no' ?>">
                                    <?= $transportText ?>
                                </span>
                            </td>
                            <td><?= $row['parent_phone'] ?></td>
                            <td><img src="<?= $photo ?>" width="40" height="40" class="rounded-circle"></td>
                            <td>
    <button class="btn btn-sm btn-primary openModal"
        data-id="<?= $row['id'] ?>">
        Update
    </button>
    <a href="student_progress_view.php?id=<?= $row['id'] ?>"
       class="btn btn-sm btn-success">
       View
    </a>
</td>
                           
                        </tr>
                    <?php endwhile; ?>

                </tbody>
            </table>
<div class="modal fade" id="progressModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5>Update Progress</h5>
      </div>

      <div class="modal-body">

        <form id="progressForm" action="./save_progress.php" method="post">

            <input type="hidden" name="student_id" id="student_id">

            <label>Date</label>
            <input type="date" name="date" class="form-control mb-2" required>

            <?php
            $types = $conn->query("SELECT * FROM progress_types");
            while($t = $types->fetch_assoc()):
            ?>

                <label><?= $t['name'] ?></label>
                <select name="progress[<?= $t['id'] ?>]" class="form-control mb-2">
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>

            <?php endwhile; ?>

            <button type="submit" class="btn btn-success w-100 mt-2">
                Save
            </button>

        </form>

      </div>
    </div>
  </div>
</div>
        </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        

 <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
// open modal
document.querySelectorAll(".openModal").forEach(btn => {
    btn.addEventListener("click", function(){
        document.getElementById("student_id").value = this.dataset.id;
        new bootstrap.Modal(document.getElementById('progressModal')).show();
    });
});

 
</script>
    <script>
        $(document).ready(function() {

            var table = $('#studentsTable').DataTable({
                pageLength: 10
            });

            $('#academicYearFilter').change(() => table.column(2).search($('#academicYearFilter').val()).draw());
            $('#classFilter').change(() => table.column(3).search($('#classFilter').val()).draw());
            $('#sectionFilter').change(() => table.column(4).search($('#sectionFilter').val()).draw());
            $('#transportFilter').change(() => table.column(6).search($('#transportFilter').val()).draw());

            $('#studentSearch').keyup(() => table.search($('#studentSearch').val()).draw());

            $('#resetFilters').click(function() {
                $('select,input').val('');
                table.search('').columns().search('').draw();
            });

        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'Delete student?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33'
            }).then((r) => {
                if (r.isConfirmed) {
                    location.href = '../actions/student_delete.php?delete=' + id;
                }
            });
        }
    </script>
        



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



    <?php include "../includes/js_links.php" ?>
</body>

</html>