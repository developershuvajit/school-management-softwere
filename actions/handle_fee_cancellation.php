<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

include('../config/database.php');

// Get POST data
$student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
$type = isset($_POST['type']) ? $_POST['type'] : ''; // 'monthly' or 'transport'
$month = isset($_POST['month']) ? intval($_POST['month']) : 0;
$year = isset($_POST['year']) ? intval($_POST['year']) : 0;
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$month_name = isset($_POST['month_name']) ? $_POST['month_name'] : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Validate input
if ($student_id <= 0 || !in_array($type, ['monthly', 'transport']) || $month <= 0 || $year <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    if ($type === 'monthly') {
        // Check if monthly fee already exists
        $check_query = "SELECT id FROM monthly_fees WHERE student_id = ? AND month = ? AND year = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("iii", $student_id, $month, $year);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Update existing record
            $update_query = "UPDATE monthly_fees SET 
                            status = 'cancelled', 
                            amount = ?, 
                            invoice_id = NULL, 
                            invoice_item_id = NULL,
                            updated_at = NOW() 
                            WHERE student_id = ? AND month = ? AND year = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("diii", $amount, $student_id, $month, $year);
        } else {
            // Insert new record - matches your table structure
            $insert_query = "INSERT INTO monthly_fees 
                            (student_id, month, year, amount, fees_types, status, created_at) 
                            VALUES (?, ?, ?, ?, 'monthly', 'cancelled', NOW())";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iiid", $student_id, $month, $year, $amount);
        }
    } elseif ($type === 'transport') {
        // Check if transport fee already exists
        $check_query = "SELECT id FROM transport_fees WHERE student_id = ? AND month = ? AND year = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("iii", $student_id, $month, $year);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Update existing record
            $update_query = "UPDATE transport_fees SET 
                            status = 'cancelled', 
                            amount = ?, 
                            invoice_id = NULL, 
                            invoice_item_id = NULL,
                            created_at = NOW() 
                            WHERE student_id = ? AND month = ? AND year = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("diii", $amount, $student_id, $month, $year);
        } else {
            // Insert new record - matches your table structure
            $insert_query = "INSERT INTO transport_fees 
                            (student_id, month, year, amount, status, created_at) 
                            VALUES (?, ?, ?, ?, 'cancelled', NOW())";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iiid", $student_id, $month, $year, $amount);
        }
    } else {
        throw new Exception("Invalid fee type");
    }

    // Execute the query
    if (!$stmt->execute()) {
        throw new Exception("Failed to save cancellation: " . $stmt->error);
    }

    // Optional: Also insert into invoice_items table if it exists
    try {
        $invoice_item_query = "INSERT INTO invoice_items 
                              (student_id, fee_type, description, amount, status, month, year, created_at) 
                              VALUES (?, ?, ?, 0.00, 'cancelled', ?, ?, NOW())";
        $stmt = $conn->prepare($invoice_item_query);
        $fee_type_desc = $type === 'monthly' ? 'Monthly Fee' : 'Transport Fee';
        $description = $fee_type_desc . ' - ' . $month_name . ' (Cancelled)';
        $stmt->bind_param("issiii", $student_id, $type, $description, $month, $year);

        if (!$stmt->execute()) {
            // Just log the error but don't stop the process
            error_log("Failed to insert invoice item record: " . $stmt->error);
        }
    } catch (Exception $e) {
        // Ignore if invoice_items table doesn't exist
        error_log("Invoice items table might not exist: " . $e->getMessage());
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Fee cancelled successfully',
        'data' => [
            'type' => $type,
            'month' => $month,
            'year' => $year,
            'month_name' => $month_name
        ]
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
