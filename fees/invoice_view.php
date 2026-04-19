<?php
// invoice_view.php - Professional Print Version
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

include('../config/database.php');

// Helper functions
function money($v)
{
    return '₹' . number_format((float)$v, 2);
}

function h($s)
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function getMonthName($month)
{
    $months = [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December'
    ];
    return $months[$month] ?? 'Unknown';
}

// Validate invoice_id
if (!isset($_GET['invoice_id']) || trim($_GET['invoice_id']) === '') {
    die("<h3 class='text-center text-danger mt-5'>❌ Invalid invoice ID!</h3>");
}
$invoice_id = (int) $_GET['invoice_id'];
if ($invoice_id <= 0) {
    die("<h3 class='text-center text-danger mt-5'>❌ Invalid invoice ID!</h3>");
}

// Fetch invoice header with student, class, and academic year details
$invoice_sql = "SELECT i.*, 
                s.first_name, s.last_name, s.parent_phone, s.admission_no, s.roll_number,
                c.class_name, 
                a.academic_year
                FROM invoices i
                LEFT JOIN students s ON i.student_id = s.id
                LEFT JOIN classes c ON i.class_id = c.id
                LEFT JOIN academic_years a ON i.academic_year_id = a.id
                WHERE i.id = ?
                LIMIT 1";

$stmt = $conn->prepare($invoice_sql);
if (!$stmt) {
    die("<h3 class='text-center text-danger mt-5'>❌ DB prepare error: " . h($conn->error) . "</h3>");
}
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invoice_res = $stmt->get_result();

if (!$invoice_res || $invoice_res->num_rows === 0) {
    die("<h3 class='text-center text-danger mt-5'>❌ Invoice not found!</h3>");
}

$invoice = $invoice_res->fetch_assoc();
$stmt->close();

// Fetch all invoice items
$items_sql = "SELECT 
                ii.id AS invoice_item_id,
                ii.fee_type_id,
                ii.particular_name,
                ii.category_name,
                ii.frequency,
                ii.amount,
                ii.paid_amount,
                ii.custom_name,
                ft.name AS fee_type_name
              FROM invoice_items ii
              LEFT JOIN fee_types ft ON ii.fee_type_id = ft.id
              WHERE ii.invoice_id = ?
              AND (ii.particular_name NOT LIKE '%Monthly Fee%' AND ii.particular_name NOT LIKE '%Transport Fee%')
              ORDER BY ii.id ASC";

$stmt = $conn->prepare($items_sql);
if (!$stmt) {
    die("<h3 class='text-center text-danger mt-5'>❌ DB prepare error: " . h($conn->error) . "</h3>");
}
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$items_res = $stmt->get_result();
$stmt->close();

// Fetch monthly fees for this invoice
$monthly_sql = "SELECT 
                mf.id, mf.month, mf.year, mf.amount, 
                mf.fees_types, mf.status, mf.invoice_item_id,
                ii.particular_name
                FROM monthly_fees mf
                LEFT JOIN invoice_items ii ON mf.invoice_item_id = ii.id
                WHERE mf.invoice_id = ?
                ORDER BY mf.year, mf.month ASC";

$stmt = $conn->prepare($monthly_sql);
if ($stmt) {
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();
    $monthly_res = $stmt->get_result();
    $monthly_fees = [];
    while ($row = $monthly_res->fetch_assoc()) {
        $monthly_fees[] = $row;
    }
    $stmt->close();
} else {
    $monthly_fees = [];
}

// Fetch transport fees for this invoice
$transport_sql = "SELECT 
                  tf.id, tf.month, tf.year, tf.amount, 
                  tf.status, tf.invoice_item_id,
                  ii.particular_name
                  FROM transport_fees tf
                  LEFT JOIN invoice_items ii ON tf.invoice_item_id = ii.id
                  WHERE tf.invoice_id = ?
                  ORDER BY tf.year, tf.month ASC";

$stmt = $conn->prepare($transport_sql);
if ($stmt) {
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();
    $transport_res = $stmt->get_result();
    $transport_fees = [];
    while ($row = $transport_res->fetch_assoc()) {
        $transport_fees[] = $row;
    }
    $stmt->close();
} else {
    $transport_fees = [];
}

// Prepare display data
$display_rows = [];
$row_counter = 1;

// Process invoice items
while ($item = $items_res->fetch_assoc()) {
    $display_rows[] = [
        'id' => $row_counter++,
        'type' => 'fee',
        'label' => $item['particular_name'] ?: $item['custom_name'],
        'details' => $item['category_name'] . ' / ' . $item['frequency'],
        'amount' => (float)$item['amount'],
        'paid' => (float)$item['paid_amount'],
        'due' => (float)($item['amount'] - $item['paid_amount'])
    ];
}

// Process monthly fees
foreach ($monthly_fees as $mf) {
    $month_name = getMonthName($mf['month']);
    $display_rows[] = [
        'id' => $row_counter++,
        'type' => 'monthly',
        'label' => $mf['particular_name'] ?: ('Monthly Fee - ' . $month_name . ' ' . $mf['year']),
        'details' => 'Monthly Fee',
        'amount' => (float)$mf['amount'],
        'paid' => (float)$mf['amount'],
        'due' => 0,
        'month' => $mf['month'],
        'year' => $mf['year']
    ];
}

// Process transport fees
foreach ($transport_fees as $tf) {
    $month_name = getMonthName($tf['month']);
    $display_rows[] = [
        'id' => $row_counter++,
        'type' => 'transport',
        'label' => $tf['particular_name'] ?: ('Transport Fee - ' . $month_name . ' ' . $tf['year']),
        'details' => 'Transport Fee',
        'amount' => (float)$tf['amount'],
        'paid' => (float)$tf['amount'],
        'due' => 0,
        'month' => $tf['month'],
        'year' => $tf['year']
    ];
}

// Calculate totals
$total_amount = array_sum(array_column($display_rows, 'amount'));
$total_paid = array_sum(array_column($display_rows, 'paid'));
$total_due = array_sum(array_column($display_rows, 'due'));

// Use invoice totals if available
$invoice_total = (float)($invoice['total_amount'] ?? $total_amount);
$invoice_paid = (float)($invoice['total_paid'] ?? $total_paid);
$invoice_due = (float)($invoice['balance'] ?? $total_due);

// Get payment status
$status = $invoice['status'] ?? 'pending';
$status_badge = '';
switch ($status) {
    case 'paid':
        $status_badge = '<span class="badge bg-success">Paid</span>';
        break;
    case 'partial':
        $status_badge = '<span class="badge bg-warning">Partially Paid</span>';
        break;
    case 'pending':
    default:
        $status_badge = '<span class="badge bg-danger">Pending</span>';
        break;
}

// Check if print mode
$is_print = isset($_GET['print']) && $_GET['print'] == 'true';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Invoice #<?= h($invoice['invoice_no'] ?? $invoice_id) ?> - School Management System</title>
    <link rel="icon" type="image/png" href="../public/images/favicon.png">
    <link href="../public/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Screen Styles */
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .invoice-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        }

        .invoice-header {
            border-bottom: 3px solid #4e73df;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .school-logo {
            max-height: 120px;
            width: auto;
        }

        .student-info-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 5px solid #4e73df;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .invoice-table th {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border: none;
            padding: 15px 10px;
        }

        .invoice-table td {
            padding: 12px 10px;
            vertical-align: middle;
        }

        .totals-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }

        .amount-cell {
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }

        .status-badge {
            font-size: 0.9em;
            padding: 6px 12px;
            border-radius: 20px;
        }

        .print-btn,
        .back-btn {
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
        }

        .print-btn {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
        }

        .back-btn {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }

        .text-primary-gradient {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .invoice-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #4e73df;
            letter-spacing: 1px;
        }

        .section-title {
            color: #4e73df;
            font-weight: 600;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }

        /* PRINT STYLES - Professional Layout */
        @media print {

            /* Reset everything */
            * {
                margin: 0 !important;
                padding: 0 !important;
                box-sizing: border-box !important;
            }

            body {
                font-family: "Times New Roman", Times, serif !important;
                font-size: 12pt !important;
                line-height: 1.4 !important;
                color: #000 !important;
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Hide all elements except invoice */
            body * {
                visibility: hidden;
            }

            #invoice-print,
            #invoice-print * {
                visibility: visible;
            }

            #invoice-print {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                padding: 15mm !important;
                background: white !important;
            }

            /* Remove all website elements */
            .no-print,
            .navbar,
            .sidebar,
            .footer,
            .page-titles,
            .buttons,
            .print-btn,
            .back-btn,
            .badge,
            .btn,
            .status-badge,
            .fas,
            .fa {
                display: none !important;
            }

            /* Typography */
            h1,
            h2,
            h3,
            h4,
            h5,
            h6 {
                color: #000 !important;
                font-weight: bold !important;
            }

            .text-primary,
            .text-primary-gradient {
                color: #000 !important;
                background: none !important;
                -webkit-text-fill-color: #000 !important;
            }

            /* Header */
            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #000;
                padding-bottom: 15px;
            }

            .print-header h1 {
                font-size: 28pt;
                margin-bottom: 5px;
            }

            .print-header .subtitle {
                font-size: 12pt;
                color: #666;
            }

            .company-info {
                text-align: center;
                margin-bottom: 20px;
                font-size: 11pt;
            }

            /* Invoice Info */
            .invoice-info-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-bottom: 25px;
                border: 1px solid #000;
                padding: 15px;
                background: #f9f9f9;
            }

            .invoice-info-item {
                margin-bottom: 8px;
            }

            .invoice-info-item strong {
                display: inline-block;
                width: 140px;
            }

            /* Table */
            .print-table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin: 20px 0 !important;
                page-break-inside: avoid;
            }

            .print-table th {
                background: #333 !important;
                color: white !important;
                border: 1px solid #000 !important;
                padding: 8px 6px !important;
                font-weight: bold !important;
                text-align: left !important;
                font-size: 11pt !important;
            }

            .print-table td {
                border: 1px solid #000 !important;
                padding: 8px 6px !important;
                vertical-align: top !important;
                font-size: 11pt !important;
            }

            .print-table .amount {
                text-align: right !important;
                font-family: 'Courier New', monospace !important;
                font-weight: bold !important;
            }

            /* Totals */
            .print-totals {
                width: 50%;
                margin-left: auto;
                margin-top: 20px;
                margin-bottom: 30px;
                border: 1px solid #000;
                padding: 15px;
                background: #f9f9f9;
            }

            .total-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 8px;
                padding-bottom: 8px;
                border-bottom: 1px solid #ddd;
            }

            .total-row.total-final {
                border-top: 2px solid #000;
                margin-top: 10px;
                padding-top: 10px;
                font-weight: bold;
                font-size: 13pt;
            }

            /* Footer */
            .print-footer {
                margin-top: 40px;
                border-top: 1px solid #000;
                padding-top: 20px;
                font-size: 10pt;
            }

            .signature-area {
                float: right;
                text-align: center;
                width: 200px;
            }

            .signature-line {
                border-top: 1px solid #000;
                margin-top: 40px;
                padding-top: 5px;
            }

            /* Page setup */
            @page {
                size: A4 portrait;
                margin: 15mm;
            }

            /* Prevent page breaks inside important elements */
            .invoice-info-grid,
            .print-totals {
                page-break-inside: avoid;
            }

            /* Remove URL and page info */
            @page {
                @top-center {
                    content: none;
                }

                @bottom-center {
                    content: none;
                }
            }
        }

        /* Screen only elements */
        @media screen {
            .print-only {
                display: none !important;
            }
        }

        /* Print only elements */
        @media print {
            .screen-only {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }
        }
    </style>
</head>

<body>
    <div id="main-wrapper">
        <?php include "../includes/navbar.php"; ?>
        <?php include "../includes/sidebar_logic.php"; ?>

        <div class="content-body">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="row page-titles mx-0 mb-4 no-print">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4 class="text-primary-gradient">Invoice Details</h4>
                            <p class="mb-0">View and print invoice details</p>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <a href="view_invoices.php" class="back-btn me-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to Invoices
                        </a>
                        <button onclick="printProfessionalInvoice()" class="print-btn me-2">
                            <i class="fas fa-print me-2"></i>Professional Print
                        </button>
                       
                    </div>
                </div>

                <!-- Screen View -->
                <div class="row justify-content-center screen-only">
                    <div class="col-12">
                        <div class="invoice-container" id="invoice-card">
                            <!-- Invoice Header -->
                            <div class="invoice-header">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h1 class="invoice-number mb-2"><?= h($invoice['invoice_no'] ?? 'INV-' . str_pad($invoice_id, 6, '0', STR_PAD_LEFT)) ?></h1>
                                        <div class="d-flex align-items-center">
                                            <?= $status_badge ?>
                                            <span class="ms-3 text-muted">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                <?= h(date('F d, Y', strtotime($invoice['created_at'] ?? date('Y-m-d')))) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <img src="../public/images/logo.png" alt="School Logo" class="school-logo mb-3">
                                        <h3 class="text-primary mb-1">School Management Softwere</h3>
                                        <p class="text-muted mb-0">Kolkata, West Bengal</p>
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-phone me-1"></i> +91 9876543210
                                        </p>
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-envelope me-1"></i> info@schoolindiajunior.edu.in
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Student Information -->
                            <div class="student-info-box">
                                <h5 class="section-title">Student Information</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <strong>Student Name:</strong>
                                            <?= h($invoice['first_name'] . ' ' . $invoice['last_name']) ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Admission No:</strong>
                                            <?= h($invoice['admission_no'] ?? 'N/A') ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Roll Number:</strong>
                                            <?= h($invoice['roll_number'] ?? 'N/A') ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <strong>Class:</strong>
                                            <?= h($invoice['class_name'] ?? 'Not Assigned') ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Academic Year:</strong>
                                            <?= h($invoice['academic_year'] ?? 'N/A') ?>
                                        </p>
                                        <p class="mb-2">
                                            <strong>Parent Contact:</strong>
                                            <i class="fas fa-phone me-1"></i><?= h($invoice['parent_phone'] ?? 'N/A') ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Items Table -->
                            <div class="mt-4">
                                <h5 class="section-title">Invoice Items</h5>
                                <div class="table-responsive">
                                    <table class="table invoice-table table-hover">
                                        <thead>
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="45%">Particulars</th>
                                                <th width="25%">Category / Frequency</th>
                                                <th width="25%" class="text-end">Amount (₹)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($display_rows) > 0): ?>
                                                <?php foreach ($display_rows as $row): ?>
                                                    <tr>
                                                        <td><?= $row['id'] ?></td>
                                                        <td>
                                                            <strong><?= h($row['label']) ?></strong>
                                                            <?php if (isset($row['month']) && isset($row['year'])): ?>
                                                                <br>
                                                                <small class="text-muted">
                                                                    <i class="fas fa-calendar me-1"></i>
                                                                    <?= getMonthName($row['month']) ?> <?= $row['year'] ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= h($row['details']) ?></td>
                                                        <td class="text-end amount-cell"><?= money($row['amount']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center py-4">
                                                        <div class="text-muted">
                                                            <i class="fas fa-receipt fa-2x mb-3"></i>
                                                            <p>No items found for this invoice.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Totals Section -->
                            <div class="totals-box">
                                <div class="row justify-content-end">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tbody>
                                                <tr>
                                                    <td class="text-end"><strong>Total Amount:</strong></td>
                                                    <td class="text-end amount-cell" width="40%"><?= money($invoice_total) ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-end"><strong>Total Paid:</strong></td>
                                                    <td class="text-end amount-cell" width="40%"><?= money($invoice_paid) ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-end"><strong>Balance Due:</strong></td>
                                                    <td class="text-end amount-cell" width="40%">
                                                        <?php if ($invoice_due > 0): ?>
                                                            <span class="text-danger"><?= money($invoice_due) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-success"><?= money($invoice_due) ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Print View (Hidden on screen) -->
                <div id="invoice-print" class="print-only" style="display: none;">
                    <!-- Print Header -->
                    <div class="print-header">
                        <h1> INVOICE</h1>

                    </div>

                    <!-- Company Info -->
                    <div class="company-info">
                        <div style="font-weight: bold; font-size: 14pt;">School Management Softwere</div>
                        <div>Kolkata, West Bengal - 700001</div>
                        <div>Phone: +91 9876543210 | Email: info@schoolindiajunior.edu.in</div>
                        <div style="margin-top: 5px; font-size: 10pt; color: #666;">
                            GSTIN: 19AAACS4114L1ZG | PAN: AAACS4114L
                        </div>
                    </div>

                    <!-- Invoice & Student Info -->
                    <div class="invoice-info-grid">
                        <div>
                            <div class="invoice-info-item">
                                <strong>Invoice No:</strong> <?= h($invoice['invoice_no'] ?? 'INV-' . str_pad($invoice_id, 6, '0', STR_PAD_LEFT)) ?>
                            </div>
                            <div class="invoice-info-item">
                                <strong>Invoice Date:</strong> <?= h(date('d/m/Y', strtotime($invoice['created_at'] ?? date('Y-m-d')))) ?>
                            </div>
                            <div class="invoice-info-item">
                                <strong>Due Date:</strong> <?= h(date('d/m/Y', strtotime('+7 days', strtotime($invoice['created_at'] ?? date('Y-m-d'))))) ?>
                            </div>
                            <div class="invoice-info-item">
                                <strong>Academic Year:</strong> <?= h($invoice['academic_year'] ?? 'N/A') ?>
                            </div>
                        </div>
                        <div>
                            <div class="invoice-info-item">
                                <strong>Student Name:</strong> <?= h($invoice['first_name'] . ' ' . $invoice['last_name']) ?>
                            </div>
                            <div class="invoice-info-item">
                                <strong>Admission No:</strong> <?= h($invoice['admission_no'] ?? 'N/A') ?>
                            </div>
                            <div class="invoice-info-item">
                                <strong>Class:</strong> <?= h($invoice['class_name'] ?? 'Not Assigned') ?>
                            </div>
                            <div class="invoice-info-item">
                                <strong>Roll No:</strong> <?= h($invoice['roll_number'] ?? 'N/A') ?>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <table class="print-table">
                        <thead>
                            <tr>
                                <th width="5%">SR</th>
                                <th width="55%">PARTICULARS</th>
                                <th width="15%">CATEGORY</th>
                                <th width="25%" class="amount">AMOUNT (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($display_rows) > 0): ?>
                                <?php foreach ($display_rows as $row): ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td>
                                            <div style="font-weight: bold;"><?= h($row['label']) ?></div>
                                            <?php if (isset($row['month']) && isset($row['year'])): ?>
                                                <div style="font-size: 9pt; color: #666;">
                                                    For: <?= getMonthName($row['month']) ?> <?= $row['year'] ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= h($row['details']) ?></td>
                                        <td class="amount"><?= money($row['amount']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 20px;">
                                        No items found for this invoice.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Totals -->
                    <div class="print-totals">
                        <div class="total-row">
                            <span>Sub Total:</span>
                            <span style="font-weight: bold;"><?= money($invoice_total) ?></span>
                        </div>

                        <?php if ($invoice_paid > 0 && $invoice_paid < $invoice_total): ?>
                            <div class="total-row">
                                <span>Amount Paid:</span>
                                <span style="color: green;"><?= money($invoice_paid) ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="total-row total-final">
                            <span>
                                <?php if ($invoice_due > 0): ?>
                                    <span style="color: #d32f2f;">Balance Due:</span>
                                <?php else: ?>
                                    <span style="color: #388e3c;">Total Paid:</span>
                                <?php endif; ?>
                            </span>
                            <span>
                                <?php if ($invoice_due > 0): ?>
                                    <span style="color: #d32f2f; font-weight: bold;"><?= money($invoice_due) ?></span>
                                <?php else: ?>
                                    <span style="color: #388e3c; font-weight: bold;"><?= money($invoice_total) ?></span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <?php if ($invoice_due > 0): ?>
                            <div style="margin-top: 10px; font-size: 10pt; color: #d32f2f; text-align: center;">
                                <strong>Payment Status: PENDING</strong>
                            </div>
                        <?php else: ?>
                            <div style="margin-top: 10px; font-size: 10pt; color: #388e3c; text-align: center;">
                                <strong>Payment Status: PAID IN FULL</strong>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Footer -->
                    <div class="print-footer">
                        <div style="float: left; width: 60%;">
                            <div style="font-weight: bold; margin-bottom: 10px;">Terms & Conditions:</div>
                            <ul style="margin: 0 0 10px 20px; font-size: 9pt;">
                                <li>Payment due within 7 days from invoice date</li>
                                <li>Late payments subject to 1.5% monthly interest</li>
                                <li>All fees are non-refundable and non-transferable</li>
                                <li>This is a computer generated invoice</li>
                            </ul>
                            <div style="margin-top: 20px; font-size: 9pt;">
                                <div><strong>Invoice Generated:</strong> <?= date('d/m/Y H:i:s') ?></div>
                                <div><strong>Generated By:</strong> School Management System v2.0</div>
                            </div>
                        </div>

                        <div class="signature-area">
                            <div class="signature-line"></div>
                            <div style="margin-top: 5px; font-weight: bold;">Authorized Signatory</div>
                            <div style="font-size: 9pt; color: #666;">School Management Softwere</div>
                            <div style="margin-top: 10px; font-size: 8pt;">
                                <div>Stamp & Seal</div>
                                <div>Date: ________________</div>
                            </div>
                        </div>

                        <div style="clear: both;"></div>


                    </div>
                </div>
            </div>
        </div>

        <?php include "../includes/footer.php"; ?>
        <?php include "../includes/js_links.php"; ?>

        <script>
            // Simple print function - uses browser's built-in print
            function simplePrint() {
                // Temporarily show print content, print, then hide it again
                const printElement = document.getElementById('invoice-print');
                const originalDisplay = printElement.style.display;

                printElement.style.display = 'block';
                window.print();
                printElement.style.display = originalDisplay;
            }

            // Professional print function - creates a clean print document
            function printProfessionalInvoice() {
                // Get all the data we need
                const invoiceData = {
                    invoice_no: "<?= h($invoice['invoice_no'] ?? 'INV-' . str_pad($invoice_id, 6, '0', STR_PAD_LEFT)) ?>",
                    invoice_date: "<?= h(date('d/m/Y', strtotime($invoice['created_at'] ?? date('Y-m-d')))) ?>",
                    due_date: "<?= h(date('d/m/Y', strtotime('+7 days', strtotime($invoice['created_at'] ?? date('Y-m-d'))))) ?>",
                    student_name: "<?= h($invoice['first_name'] . ' ' . $invoice['last_name']) ?>",
                    admission_no: "<?= h($invoice['admission_no'] ?? 'N/A') ?>",
                    class_name: "<?= h($invoice['class_name'] ?? 'Not Assigned') ?>",
                    roll_number: "<?= h($invoice['roll_number'] ?? 'N/A') ?>",
                    academic_year: "<?= h($invoice['academic_year'] ?? 'N/A') ?>",
                    total_amount: "<?= money($invoice_total) ?>",
                    paid_amount: "<?= money($invoice_paid) ?>",
                    balance_due: "<?= money($invoice_due) ?>",
                    status: "<?= $invoice_due > 0 ? 'PENDING' : 'PAID IN FULL' ?>",
                    items: <?= json_encode($display_rows) ?>
                };

                // Create the print HTML
                const printHTML = createPrintHTML(invoiceData);

                // Open print window
                const printWindow = window.open('', '_blank');

                printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Invoice ${invoiceData.invoice_no}</title>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                    /* Reset and base styles */
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                        font-family: "Times New Roman", Times, serif;
                    }
                    
                    body {
                        font-size: 12pt;
                        line-height: 1.4;
                        color: #000;
                        background: white;
                        padding: 20mm;
                    }
                    
                    /* Header */
                    .print-header {
                        text-align: center;
                        margin-bottom: 10px;
                        border-bottom: 2px solid #000;
                        padding-bottom: 15px;
                    }
                    
                    .print-header h1 {
                        font-size: 28pt;
                        margin-bottom: 5px;
                        font-weight: bold;
                    }
                    
                    .print-header .subtitle {
                        font-size: 12pt;
                        color: #666;
                    }
                    
                    .company-info {
                        text-align: center;
                        margin-bottom: 20px;
                        font-size: 11pt;
                    }
                    
                    /* Invoice Info */
                    .invoice-info-grid {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 20px;
                        margin-bottom: 25px;
                        border: 1px solid #000;
                        padding: 15px;
                        background: #f9f9f9;
                    }
                    
                    .invoice-info-item {
                        margin-bottom: 8px;
                    }
                    
                    .invoice-info-item strong {
                        display: inline-block;
                        width: 140px;
                    }
                    
                    /* Table */
                    .print-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 20px 0;
                        page-break-inside: avoid;
                    }
                    
                    .print-table th {
                        background: #333;
                        color: white;
                        border: 1px solid #000;
                        padding: 8px 6px;
                        font-weight: bold;
                        text-align: left;
                        font-size: 11pt;
                    }
                    
                    .print-table td {
                        border: 1px solid #000;
                        padding: 8px 6px;
                        vertical-align: top;
                        font-size: 11pt;
                    }
                    
                    .print-table .amount {
                        text-align: right;
                        font-family: 'Courier New', monospace;
                        font-weight: bold;
                    }
                    
                    /* Totals */
                    .print-totals {
                        width: 50%;
                        margin-left: auto;
                        margin-top: 20px;
                        margin-bottom: 30px;
                        border: 1px solid #000;
                        padding: 15px;
                        background: #f9f9f9;
                    }
                    
                    .total-row {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 8px;
                        padding-bottom: 8px;
                        border-bottom: 1px solid #ddd;
                    }
                    
                    .total-row.total-final {
                        border-top: 2px solid #000;
                        margin-top: 10px;
                        padding-top: 10px;
                        font-weight: bold;
                        font-size: 13pt;
                    }
                    
                    /* Footer */
                    .print-footer {
                        margin-top: 40px;
                        border-top: 1px solid #000;
                        padding-top: 20px;
                        font-size: 10pt;
                    }
                    
                    .signature-area {
                        float: right;
                        text-align: center;
                        width: 200px;
                    }
                    
                    .signature-line {
                        border-top: 1px solid #000;
                        margin-top: 40px;
                        padding-top: 5px;
                    }
                    
                    /* Page setup */
                    @page {
                        size: A4 portrait;
                        margin: 15mm;
                    }
                    
                    /* Hide URL and page info */
                    @page {
                        @top-center { content: none; }
                        @bottom-center { content: none; }
                    }
                    
                    @media print {
                        body {
                            padding: 15mm !important;
                        }
                        
                        .print-table {
                            page-break-inside: avoid !important;
                        }
                        
                        .invoice-info-grid,
                        .print-totals {
                            page-break-inside: avoid !important;
                        }
                    }
                </style>
            </head>
            <body>
                ${printHTML}
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(function() {
                            window.close();
                        }, 500);
                    };
                <\/script>
            </body>
            </html>
        `);

                printWindow.document.close();
            }

            // Function to create print HTML from data
            function createPrintHTML(data) {
                let itemsHTML = '';

                if (data.items && data.items.length > 0) {
                    data.items.forEach((item, index) => {
                        itemsHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <div style="font-weight: bold;">${escapeHTML(item.label)}</div>
                            ${item.month && item.year ? 
                                `<div style="font-size: 9pt; color: #666;">
                                    For: ${getMonthName(item.month)} ${item.year}
                                </div>` : ''}
                        </td>
                        <td>${escapeHTML(item.details)}</td>
                        <td class="amount">₹${formatNumber(item.amount)}</td>
                    </tr>
                `;
                    });
                } else {
                    itemsHTML = `
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px;">
                        No items found for this invoice.
                    </td>
                </tr>
            `;
                }

                return `
            <!-- Print Header -->
            <div class="print-header">
                <h1> INVOICE</h1>
               
            </div>

            <!-- Company Info -->
            <div class="company-info">
                <div style="font-weight: bold; font-size: 14pt;">School Management Softwere</div>
                <div>Kolkata, West Bengal - 700001</div>
                <div>Phone: +91 9876543210 | Email: info@schoolindiajunior.edu.in</div>
                <div style="margin-top: 5px; font-size: 10pt; color: #666;">
                    GSTIN: 19AAACS4114L1ZG | PAN: AAACS4114L
                </div>
            </div>

            <!-- Invoice & Student Info -->
            <div class="invoice-info-grid">
                <div>
                    <div class="invoice-info-item">
                        <strong>Invoice No:</strong> ${data.invoice_no}
                    </div>
                    <div class="invoice-info-item">
                        <strong>Invoice Date:</strong> ${data.invoice_date}
                    </div>
                    <div class="invoice-info-item">
                        <strong>Due Date:</strong> ${data.due_date}
                    </div>
                    <div class="invoice-info-item">
                        <strong>Academic Year:</strong> ${data.academic_year}
                    </div>
                </div>
                <div>
                    <div class="invoice-info-item">
                        <strong>Student Name:</strong> ${data.student_name}
                    </div>
                    <div class="invoice-info-item">
                        <strong>Admission No:</strong> ${data.admission_no}
                    </div>
                    <div class="invoice-info-item">
                        <strong>Class:</strong> ${data.class_name}
                    </div>
                    <div class="invoice-info-item">
                        <strong>Roll No:</strong> ${data.roll_number}
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="print-table">
                <thead>
                    <tr>
                        <th width="5%">SR</th>
                        <th width="55%">PARTICULARS</th>
                        <th width="15%">CATEGORY</th>
                        <th width="25%" class="amount">AMOUNT (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHTML}
                </tbody>
            </table>

            <!-- Totals -->
            <div class="print-totals">
                <div class="total-row">
                    <span>Sub Total:</span>
                    <span style="font-weight: bold;">${data.total_amount}</span>
                </div>
                
                ${parseFloat(data.paid_amount.replace('₹', '').replace(/,/g, '')) > 0 && 
                  parseFloat(data.paid_amount.replace('₹', '').replace(/,/g, '')) < 
                  parseFloat(data.total_amount.replace('₹', '').replace(/,/g, '')) ? 
                    `<div class="total-row">
                        <span>Amount Paid:</span>
                        <span style="color: green;">${data.paid_amount}</span>
                    </div>` : ''}
                
                <div class="total-row total-final">
                    <span>
                        ${data.status === 'PENDING' ? 
                            '<span style="color: #d32f2f;">Balance Due:</span>' : 
                            '<span style="color: #388e3c;">Total Paid:</span>'}
                    </span>
                    <span>
                        ${data.status === 'PENDING' ? 
                            `<span style="color: #d32f2f; font-weight: bold;">${data.balance_due}</span>` : 
                            `<span style="color: #388e3c; font-weight: bold;">${data.total_amount}</span>`}
                    </span>
                </div>
                
                <div style="margin-top: 10px; font-size: 10pt; color: ${data.status === 'PENDING' ? '#d32f2f' : '#388e3c'}; text-align: center;">
                    <strong>Payment Status: ${data.status}</strong>
                </div>
            </div>

            <!-- Footer -->
            <div class="print-footer">
                <div style="float: left; width: 60%;">
                    <div style="font-weight: bold; margin-bottom: 10px;">Terms & Conditions:</div>
                    <ul style="margin: 0 0 10px 20px; font-size: 9pt;">
                        <li>Payment due within 7 days from invoice date</li>
                        <li>Late payments subject to 1.5% monthly interest</li>
                        <li>All fees are non-refundable and non-transferable</li>
                        <li>This is a computer generated invoice</li>
                    </ul>
                    <div style="margin-top: 20px; font-size: 9pt;">
                        <div><strong>Invoice Generated:</strong> ${new Date().toLocaleDateString('en-GB')} ${new Date().toLocaleTimeString()}</div>
                        <div><strong>Generated By:</strong> School Management System v2.0</div>
                    </div>
                </div>
                
                <div class="signature-area">
                    <div class="signature-line"></div>
                    <div style="margin-top: 5px; font-weight: bold;">Authorized Signatory</div>
                    <div style="font-size: 9pt; color: #666;">School Management Softwere</div>
                    <div style="margin-top: 10px; font-size: 8pt;">
                        <div>Stamp & Seal</div>
                        <div>Date: ________________</div>
                    </div>
                </div>
                
                <div style="clear: both;"></div>
                
              
            </div>
        `;
            }

            // Helper functions for JavaScript
            function escapeHTML(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            function formatNumber(number) {
                return parseFloat(number).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }

            function getMonthName(month) {
                const months = [
                    'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ];
                return months[month - 1] || 'Unknown';
            }

            // Auto print if print parameter is set
            $(document).ready(function() {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('print') === 'true') {
                    // Wait for everything to load, then print
                    setTimeout(function() {
                        printProfessionalInvoice();
                    }, 1000);
                }
            });
        </script>
    </div>
</body>

</html>