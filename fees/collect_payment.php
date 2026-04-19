<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/database.php');
include('../includes/alert_helper.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    sweetAlert("⚠️ Invalid Request!", "Invoice ID missing", "warning", "invoice_list.php");
    exit;
}

$invoice_id = intval($_GET['id']);

// Fetch invoice
$invoice = $conn->query("SELECT i.*, s.first_name, s.last_name, c.class_name, sec.section_name
FROM invoices i
JOIN students s ON i.student_id = s.id
JOIN classes c ON i.class_id = c.id
JOIN sections sec ON s.section_id = sec.id
WHERE i.id = '$invoice_id'
")->fetch_assoc();

if (!$invoice) {
    sweetAlert("❌ Not Found!", "Invoice does not exist", "error", "invoice_list.php");
    exit;
}

// Fetch Items
$invoice_items = $conn->query("
SELECT ii.*, ft.name 
FROM invoice_items ii 
JOIN fee_types ft ON ii.fee_type_id = ft.id
WHERE ii.invoice_id = '$invoice_id'
");

// PROCESS PAYMENT
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $totalPay = 0;

    foreach ($_POST['pay'] as $item_id => $amount) {

        $amount = floatval($amount);
        if ($amount <= 0) continue;

        $item = $conn->query("SELECT * FROM invoice_items WHERE id='$item_id'")->fetch_assoc();
        $pending = $item['amount'] - $item['paid_amount'];

        if ($amount > $pending) {
            sweetAlert("⚠️ Invalid Amount!", "You cannot overpay a fee item", "warning", "collect_payment.php?id=$invoice_id");
            exit;
        }

        // Record Payment
        $conn->query("
            INSERT INTO invoice_payments (invoice_id, amount)
            VALUES ('$invoice_id', '$amount')
        ");

        // Update fee item
        $conn->query("
            UPDATE invoice_items 
            SET paid_amount = paid_amount + $amount
            WHERE id='$item_id'
        ");

        $totalPay += $amount;
    }

    if ($totalPay > 0) {
        $new_total_paid = $invoice['total_paid'] + $totalPay;
        $new_balance = $invoice['balance'] - $totalPay;

        if ($new_balance <= 0) {
            $new_balance = 0;
            $new_status = "paid";
        } else {
            $new_status = "partial";
        }

        $conn->query("
            UPDATE invoices
            SET total_paid='$new_total_paid', balance='$new_balance', status='$new_status'
            WHERE id='$invoice_id'
        ");

        sweetAlert("✅ Payment Successful", "Payment recorded successfully!", "success", "collect_payment.php?id=$invoice_id");
        exit;
    } else {
        sweetAlert("⚠️ Empty!", "Please enter payment amounts", "warning", "collect_payment.php?id=$invoice_id");
    }
}

include('../includes/head.php');
?>

<body>
    <div id="main-wrapper">
        <?php include "../includes/navbar.php"; ?>
        <?php include('../includes/sidebar_logic.php'); ?>

        <div class="content-body">
            <div class="container-fluid">

                <div class="card shadow-sm p-4">

                    <h4>Collect Payment</h4>
                    <hr>

                    <!-- Summary -->
                    <div class="p-3 border rounded bg-light mb-3 text-dark">
                        <b>Invoice:</b> <?= $invoice['invoice_no'] ?><br>
                        <b>Student:</b> <?= $invoice['first_name'] . " " . $invoice['last_name'] ?><br>
                        <b>Class:</b> <?= $invoice['class_name'] . " - " . $invoice['section_name'] ?><br><br>

                        <b>Total:</b> ₹<?= $invoice['total_amount'] ?><br>
                        <b>Paid:</b> ₹<?= $invoice['total_paid'] ?><br>
                        <b>Due:</b> <span class="text-danger">₹<?= $invoice['balance'] ?></span><br>
                        <b>Status:</b>
                        <span class="badge badge-<?= $invoice['status'] == 'paid' ? 'success' : ($invoice['status'] == 'partial' ? 'warning' : 'danger') ?>">
                            <?= ucfirst($invoice['status']) ?>
                        </span>
                    </div>

                    <form method="POST">
                        <table class="table table-bordered text-dark">
                            <tr>
                                <th>Fee Type</th>
                                <th>Amt</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Pay Now</th>
                            </tr>

                            <?php while ($row = $invoice_items->fetch_assoc()) {
                                $pending = $row['amount'] - $row['paid_amount'];
                            ?>
                                <tr>
                                    <td><?= $row['name'] ?></td>
                                    <td>₹<?= $row['amount'] ?></td>
                                    <td>₹<?= $row['paid_amount'] ?></td>
                                    <td class="text-danger">₹<?= $pending ?></td>
                                    <td>
                                        <input type="number" name="pay[<?= $row['id'] ?>]"
                                            class="form-control" step="0.01"
                                            max="<?= $pending ?>" min="0" placeholder="0.00">
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>

                        <button class="btn btn-success"><i class="fa fa-check"></i> Submit Payment</button>
                        <a href="view_invoices.php" class="btn btn-secondary">Back</a>
                    </form>

                </div>
            </div>
        </div>

        <?php include "../includes/footer.php"; ?>
        <?php include "../includes/js_links.php"; ?>
    </div>
</body>

</html>