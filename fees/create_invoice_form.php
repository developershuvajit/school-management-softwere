<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include('../config/database.php');
if (!isset($_GET['student_id'])) {
    header("Location: invoice_student_select.php");
    exit();
}

$student_id = intval($_GET['student_id']);
$studentQuery = "SELECT s.*, c.class_name, sec.section_name, ay.academic_year
FROM students s
LEFT JOIN classes c ON s.class_id = c.id
LEFT JOIN sections sec ON s.section_id = sec.id
LEFT JOIN academic_years ay ON s.academic_year_id = ay.id
WHERE s.id = $student_id";

$studentRes = $conn->query($studentQuery);
if (!$studentRes || $studentRes->num_rows == 0) {
    die("Invalid Student ID");
}

$student = $studentRes->fetch_assoc();
$class_id = intval($student['class_id']);
$academic_year_id = intval($student['academic_year_id']);

// Get student transport status
$student_query = "SELECT has_transport FROM students WHERE id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();
$student_data = $student_result->fetch_assoc();
$has_transport = $student_data['has_transport'];

// Parse academic year string (e.g., "2026" or "2026-2027")
$academic_year_string = $student['academic_year'];
$year_parts = explode('-', $academic_year_string);

// Use the first year part as the academic year
$academic_year_number = intval($year_parts[0]);

// For single-year format (e.g., "2026"), show all months of that year
// For multi-year format (e.g., "2026-2027"), show all months from start year to end year
if (count($year_parts) == 1) {
    // Single year format: Show all months of that year
    $start_date = new DateTime($academic_year_number . '-01-01');
    $end_date = new DateTime($academic_year_number . '-12-31');
} else {
    // Multi-year format: Show from April of start year to March of end year
    // This is common in Indian academic calendar
    $start_year = intval($year_parts[0]);
    $end_year = intval($year_parts[1]);
    
    // Academic year typically runs from April to March
    $start_date = new DateTime($start_year . '-04-01');
    $end_date = new DateTime($end_year . '-03-31');
}

// Generate months
$months = [];
$currentDate = clone $start_date;

while ($currentDate <= $end_date) {
    $months[] = [
        'month' => (int)$currentDate->format('n'),
        'year' => (int)$currentDate->format('Y'),
        'month_name' => $currentDate->format('F Y')
    ];
    $currentDate->modify('+1 month');
}

// Get already paid monthly fees (with amounts)
$paid_monthly_fees_query = "SELECT month, year, amount, paid, status FROM monthly_fees WHERE student_id = ?";
$stmt = $conn->prepare($paid_monthly_fees_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$paid_monthly_result = $stmt->get_result();
$monthly_fees_status = [];
$monthly_fees_paid = [];
$monthly_fees_amount = [];
while ($row = $paid_monthly_result->fetch_assoc()) {
    $month_key = $row['year'] . '-' . $row['month'];
    $monthly_fees_status[$month_key] = $row['status'];
    $monthly_fees_paid[$month_key] = $row['paid'];
    $monthly_fees_amount[$month_key] = $row['amount'];
}

// Get already paid transport fees (with amounts)
$paid_transport_fees_query = "SELECT month, year, amount, paid, status FROM transport_fees WHERE student_id = ?";
$stmt = $conn->prepare($paid_transport_fees_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$paid_transport_result = $stmt->get_result();
$transport_fees_status = [];
$transport_fees_paid = [];
$transport_fees_amount = [];
while ($row = $paid_transport_result->fetch_assoc()) {
    $month_key = $row['year'] . '-' . $row['month'];
    $transport_fees_status[$month_key] = $row['status'];
    $transport_fees_paid[$month_key] = $row['paid'];
    $transport_fees_amount[$month_key] = $row['amount'];
}

// Get monthly fee amount from fee_types
$monthly_fee_query = "SELECT amount FROM fee_types WHERE class_id = ? AND frequency = 'monthly' AND academic_year_id = ? LIMIT 1";
$stmt = $conn->prepare($monthly_fee_query);
$stmt->bind_param("ii", $class_id, $academic_year_id);
$stmt->execute();
$monthly_fee_result = $stmt->get_result();
$monthly_fee_data = $monthly_fee_result->fetch_assoc();
$monthly_fee_amount = $monthly_fee_data ? $monthly_fee_data['amount'] : 0;

// Get transport fee amount
$transport_fee_query = "SELECT transport_fee FROM students WHERE id = ?";
$stmt = $conn->prepare($transport_fee_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$transport_fee_result = $stmt->get_result();
$transport_fee_data = $transport_fee_result->fetch_assoc();
$transport_fee_amount = $transport_fee_data ? $transport_fee_data['transport_fee'] : 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Create Invoice</title>
    <link rel="stylesheet" href="../public/css/style.css" />
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .invoice-footer h4 {
            margin: 2px 0;
        }

        .month-item {
            display: block;
            margin-bottom: 6px;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important;
        }

        .form-check-label {
            font-weight: 500;
            cursor: pointer;
        }

        .badge {
            font-size: 0.75em;
            margin-left: 5px;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .remove-row {
            padding: 2px 8px;
        }

        .amount-input:readonly {
            background-color: #f8f9fa;
            font-weight: 500;
        }

        /* Status badge colors */
        .badge-paid {
            background-color: #28a745;
        }

        .badge-cancelled {
            background-color: #6c757d;
        }

        .badge-due {
            background-color: #dc3545;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }

        /* Modal styles */
        .modal-content {
            border-radius: 10px;
        }

        .modal-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border-radius: 10px 10px 0 0;
        }

        .modal-footer {
            border-top: 1px solid #dee2e6;
        }

        .btn-pay-fees {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
        }

        .btn-cancel-fees {
            background: linear-gradient(135deg, #6c757d, #adb5bd);
            color: white;
            border: none;
        }

        .btn-pay-fees:hover {
            background: linear-gradient(135deg, #218838, #1aa179);
        }

        .btn-cancel-fees:hover {
            background: linear-gradient(135deg, #5a6268, #9aa1a8);
        }

        .fee-option-btn {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .fee-option-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .col-md-3 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        @media (max-width: 576px) {
            .col-md-3 {
                flex: 0 0 100%;
                max-width: 100%;
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

                <h4>Create Invoice</h4>

                <div class="alert alert-secondary">
                    <strong class="fa-2x"><?= htmlspecialchars($student['first_name'] . " " . $student['last_name']); ?></strong><br />
                    Class: <?= htmlspecialchars($student['class_name'] ?? ""); ?> / <?= htmlspecialchars($student['section_name'] ?? ""); ?><br />
                    Academic Year: <?= htmlspecialchars($student['academic_year']); ?><br />
                    Phone: <?= htmlspecialchars($student['parent_phone']); ?>
                </div>

                <?php
                // Fetch all fee types/products for this class
                $fee_types_query = "SELECT id, name, category, amount, frequency FROM fee_types 
                   WHERE class_id = ? AND academic_year_id = ? 
                   AND frequency != 'monthly'";
                $stmt = $conn->prepare($fee_types_query);
                $stmt->bind_param("ii", $class_id, $academic_year_id);
                $stmt->execute();
                $fee_types_result = $stmt->get_result();
                $fee_types = [];
                while ($row = $fee_types_result->fetch_assoc()) {
                    $fee_types[] = $row;
                }
                ?>

                <!-- Invoice Form -->
                <form id="invoiceForm" method="POST" action="../actions/save_invoice_actions.php">
                    <!-- Monthly Fees Section -->
                    <div class="card mt-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0 text-white"><i class="fas fa-calendar-alt"></i> Monthly Fees</h5>
                        </div>
                        <div class="card-body">
                            <div class="row" id="monthlyFeesContainer">
                                <?php
                                if (empty($months)):
                                ?>
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            No months to display. Please check the academic year and admission date.
                                        </div>
                                    </div>
                                    <?php
                                else:
                                    foreach ($months as $month_data):
                                        $month_key = $month_data['year'] . '-' . $month_data['month'];
                                        $status = $monthly_fees_status[$month_key] ?? 'unpaid';
                                        $paid_amount = $monthly_fees_paid[$month_key] ?? 0;
                                        $existing_amount = $monthly_fees_amount[$month_key] ?? $monthly_fee_amount;

                                        $is_paid = $status === 'paid' && $paid_amount >= $existing_amount;
                                        $is_cancelled = $status === 'cancelled';
                                        $is_partial = ($status === 'partial' || $status === 'paid') && $paid_amount > 0 && $paid_amount < $existing_amount;

                                        $is_disabled = $is_paid || $is_cancelled;
                                        $due_amount = $existing_amount - $paid_amount;
                                        $display_amount = $is_partial ? $due_amount : $monthly_fee_amount;
                                    ?>
                                        <div class="col-md-3 mb-2">
                                            <div class="form-check <?= $is_cancelled ? 'cancelled-checkbox' : '' ?>">
                                                <input type="checkbox"
                                                    class="form-check-input monthly-fee-checkbox"
                                                    id="monthly_<?= $month_key ?>"
                                                    data-month="<?= $month_data['month'] ?>"
                                                    data-year="<?= $month_data['year'] ?>"
                                                    data-month-name="<?= $month_data['month_name'] ?>"
                                                    data-amount="<?= $display_amount ?>"
                                                    data-paid="<?= $paid_amount ?>"
                                                    data-existing-amount="<?= $existing_amount ?>"
                                                    data-type="monthly"
                                                    data-status="<?= $status ?>"
                                                    <?= $is_disabled ? 'disabled' : '' ?>
                                                    <?= $is_paid || $is_cancelled ? 'checked' : '' ?>>
                                                <label class="form-check-label <?= $is_paid || $is_cancelled ? 'text-muted' : 'text-dark' ?>" for="monthly_<?= $month_key ?>">
                                                    <?= $month_data['month_name'] ?>
                                                    <?php if ($is_paid): ?>
                                                        <span class="badge badge-paid text-white">Paid</span>
                                                    <?php elseif ($is_cancelled): ?>
                                                        <span class="badge badge-cancelled text-white">Cancelled</span>
                                                    <?php elseif ($is_partial): ?>
                                                        <span class="badge badge-warning">Partial ₹<?= number_format($paid_amount, 2) ?>/<?= number_format($existing_amount, 2) ?></span>
                                                        <span class="badge badge-due text-white">Due ₹<?= number_format($due_amount, 2) ?></span>
                                                    <?php else: ?>
                                                        <span class="badge badge-due text-white">Due ₹<?= number_format($monthly_fee_amount, 2) ?></span>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        </div>
                                <?php
                                    endforeach;
                                endif;
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Transport Fees Section -->
                    <?php if ($has_transport && $transport_fee_amount > 0): ?>
                        <div class="card mt-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-bus"></i> Transport Fees</h5>
                            </div>
                            <div class="card-body">
                                <div class="row" id="transportFeesContainer">
                                    <?php
                                    if (empty($months)):
                                    ?>
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                No months to display. Please check the academic year and admission date.
                                            </div>
                                        </div>
                                        <?php
                                    else:
                                        foreach ($months as $month_data):
                                            $month_key = $month_data['year'] . '-' . $month_data['month'];
                                            $status = $transport_fees_status[$month_key] ?? 'unpaid';
                                            $paid_amount = $transport_fees_paid[$month_key] ?? 0;
                                            $existing_amount = $transport_fees_amount[$month_key] ?? $transport_fee_amount;

                                            $is_paid = $status === 'paid' && $paid_amount >= $existing_amount;
                                            $is_cancelled = $status === 'cancelled';
                                            $is_partial = ($status === 'partial' || $status === 'paid') && $paid_amount > 0 && $paid_amount < $existing_amount;

                                            $is_disabled = $is_paid || $is_cancelled;
                                            $due_amount = $existing_amount - $paid_amount;
                                            $display_amount = $is_partial ? $due_amount : $transport_fee_amount;
                                        ?>
                                            <div class="col-md-3 mb-2">
                                                <div class="form-check <?= $is_cancelled ? 'cancelled-checkbox' : '' ?>">
                                                    <input type="checkbox"
                                                        class="form-check-input transport-fee-checkbox"
                                                        id="transport_<?= $month_key ?>"
                                                        data-month="<?= $month_data['month'] ?>"
                                                        data-year="<?= $month_data['year'] ?>"
                                                        data-month-name="<?= $month_data['month_name'] ?>"
                                                        data-amount="<?= $display_amount ?>"
                                                        data-paid="<?= $paid_amount ?>"
                                                        data-existing-amount="<?= $existing_amount ?>"
                                                        data-type="transport"
                                                        data-status="<?= $status ?>"
                                                        <?= $is_disabled ? 'disabled' : '' ?>
                                                        <?= $is_paid || $is_cancelled ? 'checked' : '' ?>>
                                                    <label class="form-check-label <?= $is_paid || $is_cancelled ? 'text-muted' : 'text-dark' ?>" for="transport_<?= $month_key ?>">
                                                        <?= $month_data['month_name'] ?>
                                                        <?php if ($is_paid): ?>
                                                            <span class="badge badge-paid text-white">Paid</span>
                                                        <?php elseif ($is_cancelled): ?>
                                                            <span class="badge badge-cancelled text-white">Cancelled</span>
                                                        <?php elseif ($is_partial): ?>
                                                            <span class="badge badge-warning">Partial ₹<?= number_format($paid_amount, 2) ?>/<?= number_format($existing_amount, 2) ?></span>
                                                            <span class="badge badge-due text-white">Due ₹<?= number_format($due_amount, 2) ?></span>
                                                        <?php else: ?>
                                                            <span class="badge badge-due text-white">Due ₹<?= number_format($transport_fee_amount, 2) ?></span>
                                                        <?php endif; ?>
                                                    </label>
                                                </div>
                                            </div>
                                    <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <input type="hidden" name="student_id" value="<?= $student_id ?>" />
                    <input type="hidden" name="class_id" value="<?= $class_id ?>" />
                    <input type="hidden" name="academic_year_id" value="<?= $academic_year_id ?>" />

                    <!-- Product Selection Row -->
                    <div class="mb-3 border">
                        <div class="row px-3">
                            <div class="col-md-6">
                                <label for="feeTypeSelect" class="form-label text-dark">Select Fee Type</label>
                                <select class="form-select" id="feeTypeSelect" style="padding:10px; margin-left: 10px;">
                                    <option value="">-- Select a fee type --</option>
                                    <?php foreach ($fee_types as $fee_type): ?>
                                        <option value="<?= $fee_type['id'] ?>"
                                            data-name="<?= htmlspecialchars($fee_type['name']) ?>"
                                            data-category="<?= htmlspecialchars($fee_type['category']) ?>"
                                            data-frequency="<?= htmlspecialchars($fee_type['frequency']) ?>"
                                            data-amount="<?= $fee_type['amount'] ?>">
                                            <?= htmlspecialchars($fee_type['name']) ?> - ₹<?= number_format($fee_type['amount'], 2) ?> (<?= $fee_type['category'] ?>, <?= $fee_type['frequency'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-center">
                                <button type="button" class="btn btn-success" id="addSelectedFee">
                                    <i class="fas fa-plus"></i> Add Selected
                                </button>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered text-dark">
                        <thead>
                            <tr>
                                <th>Fee Type</th>
                                <th style="width:160px">Amount</th>
                                <th style="width:160px">Paid</th>
                                <th style="width:60px"></th>
                            </tr>
                        </thead>
                        <tbody id="feesBody">
                            <!-- Rows will be added here dynamically -->
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-success btn-sm" id="addRow">
                        <i class="fas fa-plus"></i> Add Fee
                    </button>


                    <hr />
                    <div class="invoice-footer d-flex flex-column align-items-end mt-3" style="margin: 0;">
                        <h4>Total: ₹ <span id="totalAmount">0.00</span></h4>
                        <h4>Paid: ₹ <span id="paidAmount">0.00</span></h4>

                        <input type="hidden" name="total_amount" id="hiddenAmount" />
                        <input type="hidden" name="total_paid" id="hiddenPaid" />

                        <button type="submit" class="btn btn-primary mt-2">
                            <i class="fas fa-save"></i> Save Invoice
                        </button>
                    </div>
                </form>

                <!-- Modal for Fee Action Selection -->
                <div class="modal fade" id="feeActionModal" tabindex="-1" aria-labelledby="feeActionModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-white" id="feeActionModalLabel">Select Action for <span id="modalMonthName"></span></h5>
                                <button type="button" class="btn-close btn-close-white btn text-dark" data-bs-dismiss="modal" aria-label="Close">✖</button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-3">
                                    <h6>Please select how you want to process this month's fee:</h6>
                                </div>

                                <div class="fee-option-btn btn-pay-fees" onclick="selectFeeAction('pay')">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="fas fa-money-check-alt fa-2x me-3"></i>
                                        <div class="text-start">
                                            <h5 class="mb-1">Pay Fees</h5>
                                            <p class="mb-0">Add this fee to invoice for payment</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="fee-option-btn btn-cancel-fees" onclick="selectFeeAction('cancel')">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="fas fa-ban fa-2x me-3"></i>
                                        <div class="text-start">
                                            <h5 class="mb-1">Fee Cancellation</h5>
                                            <p class="mb-0">Mark this month as cancelled (sick leave, etc.)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Template for product fee row -->
                <template id="productRowTemplate">
                    <tr class="product-row" data-row-id="{ROW_ID}">
                        <td>
                            {PRODUCT_NAME}
                            <input type="hidden" name="fees[{ROW_ID}][fee_type_id]" value="{FEE_TYPE_ID}">
                            <input type="hidden" name="fees[{ROW_ID}][custom_name]" value="{PRODUCT_NAME}">
                            <input type="hidden" name="fees[{ROW_ID}][category]" value="{CATEGORY}">
                            <input type="hidden" name="fees[{ROW_ID}][frequency]" value="{FREQUENCY}">
                        </td>
                        <td>
                            <input type="number" class="form-control amount-input"
                                name="fees[{ROW_ID}][amount]"
                                value="{AMOUNT}" step="0.01" min="0">
                        </td>
                        <td>
                            <input type="number" class="form-control paid-input"
                                name="fees[{ROW_ID}][paid]"
                                value="{PAID_AMOUNT}" step="0.01" min="0" max="{AMOUNT}">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm remove-row">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                </template>

                <!-- Template for monthly/transport fee row -->
                <template id="monthlyTransportRowTemplate">
                    <tr class="{TYPE}-row" data-row-id="{ROW_ID}" data-key="{UNIQUE_KEY}">
                        <td>
                            {FEE_NAME} - {MONTH_NAME}
                            <input type="hidden" name="{TYPE}_fees[{ROW_ID}][month]" value="{MONTH}">
                            <input type="hidden" name="{TYPE}_fees[{ROW_ID}][year]" value="{YEAR}">
                            <input type="hidden" name="{TYPE}_fees[{ROW_ID}][type]" value="{TYPE}">
                        </td>
                        <td>
                            <input type="number" class="form-control amount-input"
                                name="{TYPE}_fees[{ROW_ID}][amount]"
                                value="{AMOUNT}" step="0.01" readonly>
                        </td>
                        <td>
                            <input type="number" class="form-control paid-input"
                                name="{TYPE}_fees[{ROW_ID}][paid]"
                                value="{PAID_AMOUNT}" step="0.01" min="0" max="{AMOUNT}">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm remove-row" data-key="{UNIQUE_KEY}">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                </template>

            </div>
        </div>
        <?php include "../includes/footer.php"; ?>
        <?php include "../includes/js_links.php"; ?>
    </div>

    <!-- Bootstrap Modal JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JavaScript for handling fees -->
    <script>
        $(document).ready(function() {
            let rowCounter = 0;
            let activeRows = new Map();
            let currentFeeData = null;
            const modal = new bootstrap.Modal(document.getElementById('feeActionModal'));

            // Store data when checkbox is clicked and show modal
            $(document).on('click', '.monthly-fee-checkbox:not(:disabled), .transport-fee-checkbox:not(:disabled)', function() {
                const isChecked = $(this).is(':checked');
                const type = $(this).data('type');
                const month = $(this).data('month');
                const year = $(this).data('year');
                const monthName = $(this).data('month-name');
                const amount = parseFloat($(this).data('amount'));
                const paid = parseFloat($(this).data('paid')) || 0;
                const existingAmount = parseFloat($(this).data('existing-amount')) || amount;
                const status = $(this).data('status');
                const key = `${type}_${year}_${month}`;
                const isPartial = status === 'partial' || (status === 'paid' && paid > 0 && paid < existingAmount);

                // If unchecking, remove the row
                if (!isChecked) {
                    removeRow(key);
                    updateTotals();
                    return;
                }

                // Store the fee data for later use
                currentFeeData = {
                    type: type,
                    month: month,
                    year: year,
                    monthName: monthName,
                    amount: amount,
                    paid: paid,
                    existingAmount: existingAmount,
                    isPartial: isPartial,
                    key: key,
                    checkbox: $(this)
                };

                // Show modal with month name
                $('#modalMonthName').text(monthName);
                modal.show();
            });

            // Function to handle fee action selection
            window.selectFeeAction = function(action) {
                if (!currentFeeData || !currentFeeData.checkbox) {
                    modal.hide();
                    // Remove modal backdrop manually
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No fee data available. Please select a fee again.'
                    });
                    return;
                }

                if (action === 'pay') {
                    // Add to invoice table
                    addMonthlyTransportRow(
                        currentFeeData.type,
                        currentFeeData.month,
                        currentFeeData.year,
                        currentFeeData.monthName,
                        currentFeeData.amount,
                        currentFeeData.paid,
                        currentFeeData.existingAmount,
                        currentFeeData.isPartial,
                        currentFeeData.key
                    );
                    updateTotals();
                    currentFeeData = null;
                } else if (action === 'cancel') {
                    // Mark as cancelled via AJAX
                    markFeeAsCancelled(
                        currentFeeData.type,
                        currentFeeData.month,
                        currentFeeData.year,
                        currentFeeData.monthName,
                        currentFeeData.amount,
                        currentFeeData.paid
                    );
                }

                modal.hide();
                // Remove modal backdrop manually to prevent black layer
                setTimeout(() => {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                }, 100);
            };

            // Function to mark fee as cancelled via AJAX
            function markFeeAsCancelled(type, month, year, monthName, amount, paid) {
                // Check if currentFeeData still exists
                if (!currentFeeData || !currentFeeData.checkbox) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Session expired. Please select the fee again.'
                    });
                    return;
                }

                const formData = new FormData();
                formData.append('student_id', <?= $student_id ?>);
                formData.append('type', type); // 'monthly' or 'transport'
                formData.append('month', month);
                formData.append('year', year);
                formData.append('amount', amount);
                formData.append('paid', paid);
                formData.append('month_name', monthName);
                formData.append('action', 'cancel');

                // Show loading indicator
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we cancel the fee',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('../actions/handle_fee_cancellation.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.close();

                        if (data.success) {
                            // Store reference to checkbox
                            const checkbox = currentFeeData.checkbox;
                            const label = checkbox.next('label');
                            const badge = label.find('.badge');

                            // Update checkbox status and badge
                            checkbox.prop('checked', true);
                            checkbox.prop('disabled', true);
                            label.addClass('text-muted');
                            label.parent().addClass('cancelled-checkbox');

                            if (badge.length) {
                                badge.removeClass('badge-due badge-warning').addClass('badge-cancelled').text('Cancelled');
                            } else {
                                label.append('<span class="badge badge-cancelled">Cancelled</span>');
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Fee Cancelled',
                                text: `${monthName} has been marked as cancelled.`,
                                timer: 2000,
                                showConfirmButton: false
                            });

                        } else {
                            // Uncheck the checkbox on error
                            if (currentFeeData && currentFeeData.checkbox) {
                                currentFeeData.checkbox.prop('checked', false);
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Failed to cancel fee. Please try again.'
                            });
                        }

                        // Clear currentFeeData after processing
                        currentFeeData = null;
                    })
                    .catch(error => {
                        Swal.close();
                        // Uncheck the checkbox on error
                        if (currentFeeData && currentFeeData.checkbox) {
                            currentFeeData.checkbox.prop('checked', false);
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Failed to connect to server. Please check your internet connection.'
                        });
                        currentFeeData = null;
                    });
            }

            // Function to add monthly/transport row (for PAY action)
            function addMonthlyTransportRow(type, month, year, monthName, amount, paid, existingAmount, isPartial, key) {
                if (activeRows.has(key)) {
                    return;
                }

                rowCounter++;
                const rowId = rowCounter;

                const template = $('#monthlyTransportRowTemplate').html();
                const feeName = type === 'monthly' ? 'Monthly Fee' : 'Transport Fee';
                const displayMonthName = isPartial ? `${monthName} (Balance)` : monthName;
                const paidAmount = isPartial ? amount : existingAmount; // For partial, amount is the due amount

                const rowHtml = template
                    .replace(/{ROW_ID}/g, rowId)
                    .replace(/{TYPE}/g, type)
                    .replace(/{UNIQUE_KEY}/g, key)
                    .replace(/{FEE_NAME}/g, feeName)
                    .replace(/{MONTH}/g, month)
                    .replace(/{YEAR}/g, year)
                    .replace(/{MONTH_NAME}/g, displayMonthName)
                    .replace(/{AMOUNT}/g, amount.toFixed(2))
                    .replace(/{PAID_AMOUNT}/g, paidAmount.toFixed(2));

                $('#feesBody').append(rowHtml);
                activeRows.set(key, rowId);
                updateTotals();
            }

            // Update totals function
            function updateTotals() {
                let totalAmount = 0;
                let totalPaid = 0;

                $('.amount-input').each(function() {
                    const amount = parseFloat($(this).val()) || 0;
                    totalAmount += amount;
                });

                $('.paid-input').each(function() {
                    const paid = parseFloat($(this).val()) || 0;
                    const maxAmount = parseFloat($(this).closest('tr').find('.amount-input').val()) || 0;
                    const actualPaid = Math.min(paid, maxAmount);
                    totalPaid += actualPaid;
                });

                $('#totalAmount').text(totalAmount.toFixed(2));
                $('#paidAmount').text(totalPaid.toFixed(2));
                $('#hiddenAmount').val(totalAmount.toFixed(2));
                $('#hiddenPaid').val(totalPaid.toFixed(2));
            }

            // Reset currentFeeData when modal is closed without selection
            $('#feeActionModal').on('hidden.bs.modal', function() {
                if (currentFeeData && currentFeeData.checkbox) {
                    // Only uncheck if the fee hasn't been processed yet
                    if (!currentFeeData.checkbox.is(':disabled')) {
                        currentFeeData.checkbox.prop('checked', false);
                    }
                }
                currentFeeData = null;

                // Remove modal backdrop manually
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
            });

            // Add fee from dropdown selection
            $('#addSelectedFee').click(function() {
                const selectElement = $('#feeTypeSelect');
                const selectedOption = selectElement.find('option:selected');

                if (!selectedOption.val()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Select Fee Type',
                        text: 'Please select a fee type first'
                    });
                    return;
                }

                const feeTypeId = selectedOption.val();
                const productName = selectedOption.data('name');
                const category = selectedOption.data('category');
                const frequency = selectedOption.data('frequency');
                const amount = parseFloat(selectedOption.data('amount'));
                const key = `product_${feeTypeId}_${Date.now()}`;

                addProductRow(feeTypeId, productName, category, frequency, amount, key);
                selectElement.val('');
            });

            // Add empty fee row (manual entry)
            $('#addRow').click(function() {
                const key = `manual_${Date.now()}`;
                addManualRow(key);
            });

            // Remove row when cross button is clicked
            $(document).on('click', '.remove-row', function() {
                const key = $(this).data('key') || findRowKey($(this).closest('tr'));
                removeRow(key);
                updateTotals();
            });

            // Update totals when paid amount changes
            $(document).on('input', '.paid-input', function() {
                const maxAmount = parseFloat($(this).closest('tr').find('.amount-input').val());
                const paidAmount = parseFloat($(this).val()) || 0;

                if (paidAmount > maxAmount) {
                    $(this).val(maxAmount.toFixed(2));
                }
                updateTotals();
            });

            // Update totals when amount changes
            $(document).on('input', '.amount-input:not([readonly])', function() {
                const amount = parseFloat($(this).val()) || 0;
                const paidInput = $(this).closest('tr').find('.paid-input');
                const currentPaid = parseFloat(paidInput.val()) || 0;

                if (currentPaid > amount) {
                    paidInput.val(amount.toFixed(2));
                }
                updateTotals();
            });

            // Function to add product row
            function addProductRow(feeTypeId, productName, category, frequency, amount, key) {
                if (activeRows.has(key)) {
                    return;
                }

                rowCounter++;
                const rowId = rowCounter;

                const template = $('#productRowTemplate').html();
                const rowHtml = template
                    .replace(/{ROW_ID}/g, rowId)
                    .replace(/{FEE_TYPE_ID}/g, feeTypeId)
                    .replace(/{PRODUCT_NAME}/g, productName)
                    .replace(/{CATEGORY}/g, category)
                    .replace(/{FREQUENCY}/g, frequency)
                    .replace(/{AMOUNT}/g, amount.toFixed(2))
                    .replace(/{PAID_AMOUNT}/g, amount.toFixed(2));

                $('#feesBody').append(rowHtml);

                const row = $(`tr[data-row-id="${rowId}"]`);
                row.attr('data-key', key);
                activeRows.set(key, rowId);
                updateTotals();
            }

            // Function to add manual row
            function addManualRow(key) {
                if (activeRows.has(key)) {
                    return;
                }

                rowCounter++;
                const rowId = rowCounter;

                const rowHtml = `<tr class="manual-row" data-row-id="${rowId}" data-key="${key}">
                <td>
                    <input type="text" class="form-control fee-name-input" 
                        name="fees[${rowId}][custom_name]" 
                        placeholder="Enter fee name" required>
                </td>
                <td>
                    <input type="number" class="form-control amount-input" 
                        name="fees[${rowId}][amount]" 
                        value="" step="0.01" min="0" required>
                </td>
                <td>
                    <input type="number" class="form-control paid-input" 
                        name="fees[${rowId}][paid]" 
                        value="" step="0.01" min="0" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-row" data-key="${key}">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>`;

                $('#feesBody').append(rowHtml);
                activeRows.set(key, rowId);
                updateTotals();
            }

            // Function to remove row by key
            function removeRow(key) {
                if (!activeRows.has(key)) {
                    return;
                }

                const rowId = activeRows.get(key);
                $(`tr[data-row-id="${rowId}"]`).remove();
                activeRows.delete(key);

                // Also uncheck the corresponding checkbox
                if (key.startsWith('monthly_')) {
                    const parts = key.split('_');
                    const checkbox = $(`.monthly-fee-checkbox[data-year="${parts[1]}"][data-month="${parts[2]}"]`);
                    if (!checkbox.is(':disabled')) {
                        checkbox.prop('checked', false);
                    }
                } else if (key.startsWith('transport_')) {
                    const parts = key.split('_');
                    const checkbox = $(`.transport-fee-checkbox[data-year="${parts[1]}"][data-month="${parts[2]}"]`);
                    if (!checkbox.is(':disabled')) {
                        checkbox.prop('checked', false);
                    }
                }
            }

            // Function to find row key
            function findRowKey(rowElement) {
                const key = rowElement.data('key') ||
                    `${rowElement.hasClass('monthly-row') ? 'monthly' : 
                    rowElement.hasClass('transport-row') ? 'transport' : 
                    rowElement.hasClass('product-row') ? 'product' : 
                    'manual'}_${Date.now()}`;
                return key;
            }

            // Form submission validation
            $('#invoiceForm').submit(function(e) {
                e.preventDefault();

                // Check if form is empty
                const totalAmount = parseFloat($('#totalAmount').text()) || 0;
                const hasRows = $('#feesBody tr').length > 0;

                if (!hasRows || totalAmount <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Empty Invoice',
                        text: 'Please add at least one fee item to the invoice.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }

                // Validate each row
                let isValid = true;
                let errorMessages = [];

                $('#feesBody tr').each(function(index) {
                    const rowNumber = index + 1;
                    const amountInput = $(this).find('.amount-input');
                    const paidInput = $(this).find('.paid-input');
                    const feeNameInput = $(this).find('.fee-name-input');

                    const amount = parseFloat(amountInput.val()) || 0;
                    const paid = parseFloat(paidInput.val()) || 0;
                    const feeName = feeNameInput.length > 0 ? feeNameInput.val().trim() : $(this).find('td:first').text().trim();

                    // Check if fee name is empty for manual rows
                    if (feeNameInput.length > 0 && feeName === '') {
                        isValid = false;
                        errorMessages.push(`Row ${rowNumber}: Fee name is required`);
                    }

                    // Check if amount is valid
                    if (amount <= 0) {
                        isValid = false;
                        errorMessages.push(`Row ${rowNumber}: Amount must be greater than 0`);
                    }

                    // Check if paid amount exceeds total amount
                    if (paid > amount) {
                        isValid = false;
                        errorMessages.push(`Row ${rowNumber}: Paid amount cannot exceed total amount`);
                    }

                    // Check for negative values
                    if (amount < 0 || paid < 0) {
                        isValid = false;
                        errorMessages.push(`Row ${rowNumber}: Amounts cannot be negative`);
                    }

                    // Check for NaN values
                    if (isNaN(amount) || isNaN(paid)) {
                        isValid = false;
                        errorMessages.push(`Row ${rowNumber}: Please enter valid numbers`);
                    }
                });

                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: '<div style="text-align: left; max-height: 300px; overflow-y: auto;">' +
                            '<strong>Please fix the following errors:</strong><br><br>' +
                            errorMessages.join('<br>') +
                            '</div>',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                    return false;
                }

                // Show loading before submission
                Swal.fire({
                    title: 'Saving Invoice...',
                    text: 'Please wait while we save your invoice',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit the form
                this.submit();
            });
        });
    </script>
</body>

</html>