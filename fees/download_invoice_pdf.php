<?php
include('../config/database.php');

if (!isset($_GET['invoice_id']) || empty($_GET['invoice_id'])) {
    die("Invalid invoice ID");
}

$invoice_id = (int)$_GET['invoice_id'];

$invoice_sql = "
    SELECT i.*, s.first_name, s.last_name, s.parent_phone, 
           c.class_name, sec.section_name
    FROM invoices i
    LEFT JOIN students s ON i.student_id = s.id
    LEFT JOIN classes c ON s.class_id = c.id
    LEFT JOIN sections sec ON s.section_id = sec.id
    WHERE i.id = '$invoice_id'
";
$invoice_result = $conn->query($invoice_sql);
if (!$invoice_result || $invoice_result->num_rows === 0) {
    die("Invoice not found");
}

$invoice = $invoice_result->fetch_assoc();

$items_sql = "
    SELECT ii.*, ft.name, ft.category, ft.frequency
    FROM invoice_items ii
    LEFT JOIN fee_types ft ON ii.fee_type_id = ft.id
    WHERE ii.invoice_id = '$invoice_id'
";
$items = $conn->query($items_sql);

$status = ($invoice['total_amount'] > $invoice['total_paid']) ? 'Due' : 'Paid';
$invoice_no = $invoice['invoice_no'] ?? "INV-" . str_pad($invoice['id'], 5, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice <?= $invoice_no ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding: 20px;
            background: #f9fafc;
        }

        /* Card */
        .invoice-box {
            background: #fff;
            max-width: 900px;
            margin: auto;
            padding: 32px;
            border-radius: 14px;
            position: relative;
            overflow: hidden;
            border: 1px solid #ddd;
        }

        /* Watermark */
        .invoice-box::before {
            content: "School Management Softwere";
            position: absolute;
            font-size: 60px;
            font-weight: 700;
            color: rgba(0, 0, 0, 0.05);
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            pointer-events: none;
            white-space: nowrap;
        }

        /* Heading Row */
        .header-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e91e63;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .school-info h2 {
            color: #e91e63;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .school-info small {
            display: block;
        }

        /* Invoice detail table */
        table th {
            background: #e91e63 !important;
            color: #fff !important;
            font-weight: 600;
        }

        /* Footer signature */
        .sign-area {
            text-align: right;
            margin-top: 40px;
            font-weight: 600;
        }

        .sign-line {
            width: 180px;
            border-bottom: 2px solid #000;
            float: right;
            margin-bottom: 5px;
        }

        .print-btn {
            margin-top: 20px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #fff;
                padding: 0;
            }

            .invoice-box {
                border: none;
                padding: 0;
            }
        }
    </style>

</head>

<body>

    <div class="invoice-box">

        <div class="header-area">
            <div class="school-info">
                <h2>School Management Softwere</h2>
                <small>Kolkata, West Bengal</small>
                <small>Phone: +91 9876543210</small>
            </div>
            <img src="../public/images/logo.png" style="height: 120px;" width="80">
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <b>Invoice To:</b><br>
                <?= $invoice['first_name'] . ' ' . $invoice['last_name']; ?><br>
                Class: <?= $invoice['class_name'] . " - " . $invoice['section_name']; ?><br>
                Parent Mobile: <?= $invoice['parent_phone']; ?>
            </div>
            <div class="col-md-6 text-right">
                <b>Invoice No:</b> <?= $invoice_no ?><br>
                <b>Date:</b> <?= date("d M, Y", strtotime($invoice['created_at'])) ?><br>
                <b>Status:</b> <?= $status ?>
            </div>
        </div>

        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Particulars</th>
                    <th>Category</th>
                    <th>Frequency</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1;
                while ($item = $items->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= $item['name'] ?></td>
                        <td><?= ucfirst($item['category']) ?></td>
                        <td><?= ucfirst(str_replace('_', ' ', $item['frequency'])) ?></td>
                        <td>₹<?= number_format($item['amount'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>

            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">Total</th>
                    <th>₹<?= $invoice['total_amount'] ?></th>
                </tr>
                <tr>
                    <th colspan="4" class="text-right">Paid</th>
                    <th>₹<?= $invoice['total_paid'] ?></th>
                </tr>
                <tr>
                    <th colspan="4" class="text-right">Due</th>
                    <th>₹<?= $invoice['total_amount'] - $invoice['total_paid'] ?></th>
                </tr>
            </tfoot>
        </table>

        <div class="sign-area">
            <div class="sign-line"></div>
            School Administrator
        </div>

        <div class="text-center no-print print-btn">
            <button onclick="window.print()" class="btn btn-primary">🖨 Print / Save</button>
        </div>

    </div>

</body>

</html>