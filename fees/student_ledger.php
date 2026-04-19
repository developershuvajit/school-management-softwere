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
<?php



// Initialize
$student = null;
$invoices = [];
$payments = [];

if (isset($_GET['student_id']) && intval($_GET['student_id']) > 0) {
    $sid = intval($_GET['student_id']);

    // Fetch student details
    $student = $conn->query("SELECT s.*, c.class_name, sec.section_name 
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        WHERE s.id = $sid
    ")->fetch_assoc();

    if ($student) {
        // Fetch invoices
        $invoices = $conn->query("SELECT id, invoice_no, total_amount, total_paid, balance, status, created_at 
            FROM invoices 
            WHERE student_id = $sid 
            ORDER BY created_at DESC
        ");

        // Fetch payments
        $payments = $conn->query("SELECT ip.amount, ip.payment_date, i.invoice_no 
            FROM invoice_payments ip
            JOIN invoices i ON ip.invoice_id = i.id
            WHERE i.student_id = $sid
            ORDER BY ip.payment_date DESC
        ");
    }
}

// Fetch all students for dropdown
$students = $conn->query("SELECT id, CONCAT(first_name,' ',last_name) AS name 
    FROM students 
    ORDER BY first_name ASC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>School Management Softwere</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>


<body>
    <?php include "../includes/preloader.php"; ?>
    <div id="main-wrapper">
        <?php include "../includes/navbar.php"; ?>
        <?php include('../includes/sidebar_logic.php'); ?>

        <div class="content-body">
            <div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <h4 class="card-title">Student Ledger</h4>
                    </div>
                </div>

                <div class="card shadow-sm p-4">
                    <form method="get" class="form-inline mb-4">
                        <label class="mr-2 "><strong class="text-dark">Select Student:</strong></label>
                        <select name="student_id" class="form-control mr-2" required>
                            <option value="">-- Choose --</option>
                            <?php while ($row = $students->fetch_assoc()): ?>
                                <option value="<?= $row['id']; ?>" <?= (isset($_GET['student_id']) && $_GET['student_id'] == $row['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($row['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">View Ledger</button>
                    </form>

                    <?php if ($student): ?>
                        <div class="border rounded bg-light p-3 mb-3 text-dark">
                            <h5>Student Info</h5>
                            <p><strong>Name:</strong> <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                            <p><strong>Class:</strong> <?= htmlspecialchars($student['class_name'] . ' - ' . $student['section_name']); ?></p>
                            <p><strong>Parent Phone:</strong> <?= htmlspecialchars($student['parent_phone']); ?></p>
                        </div>

                        <!-- Invoice Table -->
                        <h5 class="mb-2">Invoices</h5>
                        <div class="table-responsive mb-4 text-dark">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Invoice No</th>
                                        <th>Date</th>
                                        <th>Total (₹)</th>
                                        <th>Paid (₹)</th>
                                        <th>Due (₹)</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody class="text-dark">
                                    <?php
                                    $total_due = 0;
                                    $total_amt = 0;
                                    $total_paid = 0;
                                    $count = 1;

                                    if ($invoices && $invoices->num_rows > 0):
                                        while ($inv = $invoices->fetch_assoc()):
                                            $badge = $inv['status'] == 'paid' ? 'success' : ($inv['status'] == 'partial' ? 'warning' : 'danger');
                                            $total_due += $inv['balance'];
                                            $total_amt += $inv['total_amount'];
                                            $total_paid += $inv['total_paid'];
                                    ?>
                                            <tr>
                                                <td><?= $count++; ?></td>
                                                <td><a href="invoice_view.php?invoice_id=<?= $inv['id']; ?>"><?= $inv['invoice_no']; ?></a></td>
                                                <td><?= date('d M Y', strtotime($inv['created_at'])); ?></td>
                                                <td>₹<?= number_format($inv['total_amount'], 2); ?></td>
                                                <td>₹<?= number_format($inv['total_paid'], 2); ?></td>
                                                <td>₹<?= number_format($inv['balance'], 2); ?></td>
                                                <td><span class="badge badge-<?= $badge; ?>"><?= ucfirst($inv['status']); ?></span></td>
                                            </tr>
                                        <?php endwhile;
                                    else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No invoices found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Payment Table -->
                        <h5 class="mb-2">Payments</h5>
                        <div class="table-responsive mb-4 text-dark">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Invoice No</th>
                                        <th>Amount (₹)</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody class="text-dark">
                                    <?php
                                    $count = 1;
                                    if ($payments && $payments->num_rows > 0):
                                        while ($p = $payments->fetch_assoc()):
                                    ?>
                                            <tr class="text-dark">
                                                <td class="text-dark"><?= $count++; ?></td>
                                                <td class="text-dark"><?= htmlspecialchars($p['invoice_no']); ?></td>
                                                <td class="text-dark">₹<?= number_format($p['amount'], 2); ?></td>
                                                <td class="text-dark"><?= date('d M Y', strtotime($p['payment_date'])); ?></td>
                                            </tr>
                                        <?php endwhile;
                                    else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No payments found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary -->
                        <div class="alert alert-secondary">
                            <h5><strong>Summary</strong></h5>
                            <p class="mb-0">
                                <strong>Total Invoiced:</strong> ₹<?= number_format($total_amt, 2); ?> |
                                <strong>Total Paid:</strong> ₹<?= number_format($total_paid, 2); ?> |
                                <strong>Total Due:</strong> ₹<?= number_format($total_due, 2); ?>
                            </p>
                        </div>

                    <?php elseif (isset($_GET['student_id'])): ?>
                        <div class="alert alert-warning">No student found.</div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <?php include "../includes/footer.php"; ?>
    </div>
    <?php include "../includes/js_links.php"; ?>
</body>

</html>