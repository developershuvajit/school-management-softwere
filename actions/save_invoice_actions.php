<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include('../config/database.php');
include('../includes/alert_helper.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sweetAlert("Invalid Request!", "", "error", "../fees/view_invoices.php");
    exit;
}

/* ------------------ BASIC VALIDATION ------------------ */
$required = ['student_id', 'class_id', 'academic_year_id'];
foreach ($required as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        sweetAlert("Error!", "Missing required field: $field", "error", "../fees/view_invoices.php");
        exit;
    }
}

$student_id = intval($_POST['student_id']);
$class_id = intval($_POST['class_id']);
$academic_year_id = intval($_POST['academic_year_id']);
$total_amount = floatval($_POST['total_amount'] ?? 0);
$total_paid = floatval($_POST['total_paid'] ?? 0);
$balance = $total_amount - $total_paid;

/* ------------------ GET STUDENT TRANSPORT FEE ------------------ */
$student_query = "SELECT transport_fee, has_transport FROM students WHERE id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();
$student_data = $student_result->fetch_assoc();
$transport_fee_amount = $student_data['transport_fee'] ?? 0;
$has_transport = $student_data['has_transport'] ?? 0;
$stmt->close();

$conn->begin_transaction();

try {
    /* ------------------ CREATE INVOICE ------------------ */
    $invoice_no = "INV-" . date("Ymd") . "-" . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

    $insInv = $conn->prepare("INSERT INTO invoices 
        (invoice_no, student_id, class_id, academic_year_id, total_amount, total_paid, balance, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $status = ($balance <= 0) ? 'paid' : 'partial';
    $insInv->bind_param(
        "siiiddds",
        $invoice_no,
        $student_id,
        $class_id,
        $academic_year_id,
        $total_amount,
        $total_paid,
        $balance,
        $status
    );

    $insInv->execute();
    $invoice_id = $conn->insert_id;
    $insInv->close();

    /* ------------------ PREPARED STATEMENTS ------------------ */
    $insItem = $conn->prepare("INSERT INTO invoice_items 
        (invoice_id, fee_type_id, custom_name, amount, paid_amount, particular_name, category_name, frequency)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    /* ------------------ PROCESS REGULAR FEES (From dropdown/manual) ------------------ */
    if (isset($_POST['fees']) && is_array($_POST['fees'])) {
        foreach ($_POST['fees'] as $row_id => $fee_data) {
            $fee_type_id = isset($fee_data['fee_type_id']) ? intval($fee_data['fee_type_id']) : 0;
            $custom_name = $conn->real_escape_string($fee_data['custom_name'] ?? '');
            $amount = floatval($fee_data['amount']);
            $paid = floatval($fee_data['paid']);

            // Get fee type details if it's a product fee
            if ($fee_type_id > 0) {
                $ft_query = "SELECT name, category, frequency FROM fee_types WHERE id = ?";
                $ft_stmt = $conn->prepare($ft_query);
                $ft_stmt->bind_param("i", $fee_type_id);
                $ft_stmt->execute();
                $ft_result = $ft_stmt->get_result();
                $ft_data = $ft_result->fetch_assoc();
                $ft_stmt->close();

                $particular_name = $ft_data['name'] ?? $custom_name;
                $category_name = $ft_data['category'] ?? '';
                $frequency = $ft_data['frequency'] ?? '';
            } else {
                // Manual fee entry
                $particular_name = $custom_name;
                $category_name = $fee_data['category'] ?? '';
                $frequency = $fee_data['frequency'] ?? '';
            }

            // Skip if this is a monthly or transport fee (they're handled separately)
            if (stripos($particular_name, 'monthly') !== false || stripos($particular_name, 'transport') !== false) {
                continue;
            }

            // Insert into invoice_items
            $insItem->bind_param(
                "iisddsss",
                $invoice_id,
                $fee_type_id,
                $custom_name,
                $amount,
                $paid,
                $particular_name,
                $category_name,
                $frequency
            );
            $insItem->execute();
            $item_id = $conn->insert_id;

            // If this fee type should be tracked in invoices.fee_types_id
            if ($fee_type_id > 0) {
                $update_invoice = $conn->prepare("UPDATE invoices SET fee_types_id = ? WHERE id = ?");
                $update_invoice->bind_param("ii", $fee_type_id, $invoice_id);
                $update_invoice->execute();
                $update_invoice->close();
            }
        }
    }

    /* ------------------ PROCESS MONTHLY FEES ------------------ */
    if (isset($_POST['monthly_fees']) && is_array($_POST['monthly_fees'])) {
        // Get monthly fee type ID
        $monthly_fee_query = "SELECT id, name FROM fee_types WHERE class_id = ? AND academic_year_id = ? AND frequency = 'monthly' LIMIT 1";
        $monthly_stmt = $conn->prepare($monthly_fee_query);
        $monthly_stmt->bind_param("ii", $class_id, $academic_year_id);
        $monthly_stmt->execute();
        $monthly_result = $monthly_stmt->get_result();
        $monthly_fee_data = $monthly_result->fetch_assoc();
        $monthly_fee_type_id = $monthly_fee_data['id'] ?? 0;
        $monthly_fee_name = $monthly_fee_data['name'] ?? 'Monthly Fee';
        $monthly_stmt->close();

        foreach ($_POST['monthly_fees'] as $row_id => $monthly_data) {
            $month = intval($monthly_data['month']);
            $year = intval($monthly_data['year']);
            $amount = floatval($monthly_data['amount']);
            $paid = floatval($monthly_data['paid']);

            // Check if already exists
            $checkMonthly = $conn->prepare("SELECT id, paid, amount, status FROM monthly_fees WHERE student_id = ? AND month = ? AND year = ?");
            $checkMonthly->bind_param("iii", $student_id, $month, $year);
            $checkMonthly->execute();
            $checkResult = $checkMonthly->get_result();

            // Create particular name
            $month_name = date("F", mktime(0, 0, 0, $month, 1));
            $particular_name = "Monthly Fee - $month_name $year";

            if ($checkResult->num_rows > 0) {
                // Update existing monthly fee (partial payment)
                $existing = $checkResult->fetch_assoc();
                $existing_paid = floatval($existing['paid']);
                $existing_amount = floatval($existing['amount']);
                $new_paid = $existing_paid + $paid;
                $new_amount = max($existing_amount, $amount); // Use the larger amount

                // Determine status
                if ($new_paid >= $new_amount) {
                    $new_status = 'paid';
                } else {
                    $new_status = 'partial';
                }

                $update_monthly = $conn->prepare("UPDATE monthly_fees 
                    SET amount = ?, paid = ?, status = ?, invoice_id = ?
                    WHERE id = ?");
                $update_monthly->bind_param("ddsii", $new_amount, $new_paid, $new_status, $invoice_id, $existing['id']);
                $update_monthly->execute();
                $update_monthly->close();

                $mon = 'monthly';
                $insItem->bind_param(
                    "iisddsss",
                    $invoice_id,
                    $monthly_fee_type_id,
                    $particular_name,
                    $paid,
                    $paid,
                    $particular_name,
                    $mon,
                    $mon
                );
                $insItem->execute();
            } else {
                // Insert new monthly fee
                if ($paid >= $amount) {
                    $new_status = 'paid';
                } else {
                    $new_status = 'partial';
                }

                $insMonthly = $conn->prepare("INSERT INTO monthly_fees 
                    (student_id, invoice_id, month, year, amount, paid, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $insMonthly->bind_param(
                    "iiiidds",
                    $student_id,
                    $invoice_id,
                    $month,
                    $year,
                    $amount,
                    $paid,
                    $new_status
                );
                $insMonthly->execute();
                $insMonthly->close();


                $mon = 'monthly';
                $insItem->bind_param(
                    "iisddsss",
                    $invoice_id,
                    $monthly_fee_type_id,
                    $particular_name,
                    $amount,
                    $paid,
                    $particular_name,
                    $mon,
                    $mon
                );
                $insItem->execute();
            }
            $checkMonthly->close();
        }
    }

    /* ------------------ PROCESS TRANSPORT FEES ------------------ */
    if ($has_transport && isset($_POST['transport_fees']) && is_array($_POST['transport_fees'])) {
        foreach ($_POST['transport_fees'] as $row_id => $transport_data) {
            $month = intval($transport_data['month']);
            $year = intval($transport_data['year']);
            $amount = floatval($transport_data['amount']);
            $paid = floatval($transport_data['paid']);

            // Check if already exists
            $checkTransport = $conn->prepare("SELECT id, paid, amount, status FROM transport_fees WHERE student_id = ? AND month = ? AND year = ?");
            $checkTransport->bind_param("iii", $student_id, $month, $year);
            $checkTransport->execute();
            $checkResult = $checkTransport->get_result();

            // Create particular name
            $month_name = date("F", mktime(0, 0, 0, $month, 1));
            $particular_name = "Transport Fee - $month_name $year";

            if ($checkResult->num_rows > 0) {
                // Update existing transport fee (partial payment)
                $existing = $checkResult->fetch_assoc();
                $existing_paid = floatval($existing['paid']);
                $existing_amount = floatval($existing['amount']);
                $new_paid = $existing_paid + $paid;
                $new_amount = max($existing_amount, $amount); // Use the larger amount

                // Determine status
                if ($new_paid >= $new_amount) {
                    $new_status = 'paid';
                } else {
                    $new_status = 'partial';
                }

                $update_transport = $conn->prepare("UPDATE transport_fees 
                    SET amount = ?, paid = ?, status = ?, invoice_id = ?
                    WHERE id = ?");
                $update_transport->bind_param("ddsii", $new_amount, $new_paid, $new_status, $invoice_id, $existing['id']);
                $update_transport->execute();
                $update_transport->close();

                $zero = 0;
                $tran = 'Transport';
                $mon = 'monthly';
                $insItem->bind_param(
                    "iisddsss",
                    $invoice_id,
                    $zero,
                    $particular_name,
                    $paid,
                    $paid,
                    $particular_name,
                    $tran,
                    $mon

                );
                $insItem->execute();
            } else {
                // Insert new transport fee
                if ($paid >= $amount) {
                    $new_status = 'paid';
                } else {
                    $new_status = 'partial';
                }

                $insTransport = $conn->prepare("INSERT INTO transport_fees 
                    (student_id, month, year, amount, paid, status, invoice_id, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $insTransport->bind_param(
                    "iiiddsi",
                    $student_id,
                    $month,
                    $year,
                    $amount,
                    $paid,
                    $new_status,
                    $invoice_id
                );
                $insTransport->execute();
                $insTransport->close();

                $zero = 0;
                $tran = 'Transport';
                $mon = 'monthly';
                $insItem->bind_param(
                    "iisddsss",
                    $invoice_id,
                    $zero, // No fee_type_id for transport
                    $particular_name,
                    $amount,
                    $paid,
                    $particular_name,
                    $tran,
                    $mon
                );
                $insItem->execute();
            }
            $checkTransport->close();
        }
    }

    /* ------------------ CLOSE STATEMENTS ------------------ */
    if (isset($insItem)) $insItem->close();

    /* ------------------ SUCCESS ------------------ */
    $conn->commit();

    sweetAlert("Invoice Created", "Successfully created! Invoice No: $invoice_no", "success", "../fees/invoice_view.php?invoice_id=" . $invoice_id);
} catch (Exception $e) {
    $conn->rollback();

    sweetAlert(
        "Error Creating Invoice!",
        "Error: " . $e->getMessage(),
        "error",
        "../fees/create_invoice_form.php?student_id=$student_id&class_id=$class_id&academic_year_id=$academic_year_id"
    );
    exit;
}

$conn->close();
