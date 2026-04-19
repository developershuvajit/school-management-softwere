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
    <?php include "../includes/preloader.php"; ?>
    <div id="main-wrapper">
        <?php include "../includes/navbar.php"; ?>
        <?php include('../includes/sidebar_logic.php'); ?>

        <div class="content-body">
            <div class="container-fluid">

                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <?php include('../includes/welcome_text.php'); ?>
                    </div>
                </div>

                <!-- 🧾 All Invoices -->
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h4 class="card-title mb-2 mb-sm-0">All Invoices</h4>
                                <a href="create_invoice.php" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Create New Invoice
                                </a>
                            </div>

                            <div class="card-body text-dark">
                                <div class="table-responsive">
                                    <table id="example2" class="display" style="width:100%">
                                        <thead class="">
                                            <tr>
                                                <th>#</th>
                                                <th>Invoice No</th>
                                                <th>Student</th>
                                                <th>Class</th>
                                                <th>Total (₹)</th>
                                                <th>Due (₹)</th>
                                                <th>Status</th>
                                                <th>Issue Date</th>
                                                <th>Due Date</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <style>
                                            table tbody tr {
                                                border-bottom: 1px solid #d1d1d1;
                                            }
                                        </style>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT i.*, 
                                                            CONCAT(s.first_name, ' ', s.last_name) AS student_name, 
                                                            c.class_name,
                                                            s.id AS student_id
                                                        FROM invoices i
                                                        JOIN students s ON i.student_id = s.id
                                                        JOIN classes c ON i.class_id = c.id
                                                        ORDER BY i.id DESC";

                                            $res = $conn->query($sql);
                                            if ($res && $res->num_rows > 0) {
                                                $i = 1;
                                                while ($row = $res->fetch_assoc()) {
                                                    $id = $row['id'];
                                                    $invoice_no = htmlspecialchars($row['invoice_no']);
                                                    $student = htmlspecialchars($row['student_name']);
                                                    $class = htmlspecialchars($row['class_name']);
                                                    $student_id = $row['student_id'];

                                                    $total = number_format($row['total_amount'], 2);
                                                    $due = number_format(($row['total_amount'] - $row['total_paid']), 2);


                                                    if ($row['total_paid'] == 0) {
                                                        $status = "Unpaid";
                                                        $badge = "danger";
                                                    } elseif ($row['total_paid'] < $row['total_amount']) {
                                                        $status = "Partial";
                                                        $badge = "warning";
                                                    } else {
                                                        $status = "Paid";
                                                        $badge = "success";
                                                    }

                                                    $issue_date = date('d M Y', strtotime($row['created_at']));
                                                    $due_date = date('d M Y', strtotime($row['created_at']));

                                                    echo "
                                                            <tr class='text-dark'>
                                                                <td>{$i}</td>
                                                                <td>{$invoice_no}</td>
                                                                <td>{$student}</td>
                                                                <td>{$class}</td>
                                                                <td>₹{$total}</td>
                                                                <td>₹{$due}</td>
                                                                <td><span class='badge badge-{$badge}'>{$status}</span></td>
                                                                <td>{$issue_date}</td>
                                                                <td>{$due_date}</td>
                                                                <td class='text-center'>
                                                                    <div class='btn-group'>

                                                                        <!-- 🔍 View Invoice -->
                                                                        <a href='invoice_view.php?invoice_id={$id}' class='btn btn-sm btn-info' title='View Invoice'>
                                                                            <i class='fa fa-eye'></i>
                                                                        </a>

                                                                         <!-- 💳 Make Payment -->
                                                                            <a href='collect_payment.php?id={$id}' 
                                                                            class='btn btn-sm btn-success' 
                                                                            title='Collect/Due Payment'>
                                                                            <i class='fa fa-credit-card'></i>
                                                                            </a>


                                                                        <!-- 📒 Student Ledger -->
                                                                        <a href='student_ledger.php?student_id={$student_id}' class='btn btn-sm btn-warning' title='View Ledger'>
                                                                            <i class='fa fa-book'></i>
                                                                        </a>

                                                                        <!-- ❌ Delete -->
                                                                        <button class='btn btn-sm btn-danger' onclick='confirmDelete({$id})' title='Delete Invoice'>
                                                                            <i class='fa fa-trash'></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            ";
                                                    $i++;
                                                }
                                            } else {
                                                echo "<tr><td colspan='10' class='text-center text-muted'>No invoices found.</td></tr>";
                                            }
                                            ?>
                                        </tbody>

                                    </table>
                                </div>

                                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                                <script>
                                    function confirmDelete(id) {
                                        Swal.fire({
                                            title: "Are you sure?",
                                            text: "This invoice will be permanently deleted!",
                                            icon: "warning",
                                            showCancelButton: true,
                                            confirmButtonColor: "#d33",
                                            cancelButtonColor: "#3085d6",
                                            confirmButtonText: "Yes, delete it!"
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                window.location.href = "../actions/invoice_actions.php?delete=" + id;
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

        <?php include "../includes/footer.php"; ?>
    </div>
    <?php include "../includes/js_links.php"; ?>
</body>

</html>