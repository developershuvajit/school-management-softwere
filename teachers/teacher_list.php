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
    <title>School India Junior</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.dataTables_wrapper .dt-buttons {
    margin-bottom: 15px;
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
    text-align: center;
}

.table-sm td {
    padding: 8px !important;
    vertical-align: middle;
    text-align: center;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.badge-active {
    background-color: #d4edda;
    color: #155724;
    padding: 4px 8px;
    font-size: 12px;
    border-radius: 4px;
}

.badge-inactive {
    background-color: #f8d7da;
    color: #721c24;
    padding: 4px 8px;
    font-size: 12px;
    border-radius: 4px;
}

.action-buttons .btn {
    padding: 4px 8px;
    font-size: 12px;
    margin: 2px;
}
</style>
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

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h4 class="card-title">Teachers</h4>
                                <a href="teacher_add.php" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Add New Teacher
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <?php require_once __DIR__ . '/../includes/alert_helper.php'; ?>
                                   <table id="teachersTable" class="table table-bordered table-striped table-sm table-hover w-100 text-dark">
    <thead>
        <tr>
            <th>#</th>
            <th>Photo</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Subject</th>
            <th>Salary</th>
            <th>Join Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php
        $sql = "SELECT t.*, u.status as user_status FROM teachers t 
                JOIN users u ON t.user_id = u.id 
                ORDER BY t.id DESC";

        $result = $conn->query($sql);
        $i = 1;

        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $id = (int)$row['id'];
                $photo = !empty($row['photo']) ? '../' . $row['photo'] : '../assets/img/default-user.png';
                $statusText = $row['user_status'] ? 'Active' : 'Inactive';
        ?>
        <tr>
            <td><?= $i++ ?></td>

            <td>
                <img src="<?= $photo ?>" width="40" height="40" class="rounded-circle border">
            </td>

            <td class="text-start"><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['subject']) ?></td>
            <td>₹<?= number_format($row['salary'], 2) ?></td>
            <td><?= date('d M Y', strtotime($row['join_date'])) ?></td>

            <td>
                <span class="badge <?= $statusText === 'Active' ? 'badge-active' : 'badge-inactive' ?>">
                    <?= $statusText ?>
                </span>
            </td>

            <td class="action-buttons">
                <a href="salary_management.php?id=<?= $id ?>" class="btn btn-secondary btn-sm">Pay</a>
                <a href="teacher_view.php?id=<?= $id ?>" class="btn btn-info btn-sm">View</a>
                <a href="teacher_edit.php?id=<?= $id ?>" class="btn btn-warning btn-sm">Edit</a>
                <button onclick="confirmDelete(<?= $id ?>)" class="btn btn-danger btn-sm">Del</button>
            </td>
        </tr>
        <?php endwhile; else: ?>
            <tr>
                <td colspan="10" class="text-muted text-center">No teachers found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

                                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                                    <script>
                                        function confirmDelete(id) {
                                            Swal.fire({
                                                title: "Are you sure?",
                                                text: "This teacher will be permanently deleted!",
                                                icon: "warning",
                                                showCancelButton: true,
                                                confirmButtonColor: "#d33",
                                                cancelButtonColor: "#3085d6",
                                                confirmButtonText: "Yes, delete it!"
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    window.location.href = "../actions/teacher_delete.php?delete=" + id;
                                                }
                                            });
                                        }
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
</body>

</html>