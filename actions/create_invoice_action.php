<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/database.php');
include('../includes/alert_helper.php');

if (isset($_POST['create_invoice'])) {
    $student_id = intval($_POST['student_id']);
    $class_id = intval($_POST['class_id']);
    $due_date = $_POST['due_date'];
    $items = json_decode($_POST['items'], true);
    $total_amount = floatval($_POST['total_amount']);

    if (empty($student_id) || empty($class_id) || empty($items)) {
        sweetAlert("⚠️ Missing Info!", "Please fill all required fields and add items.", "warning", "../fees/create_invoice.php");
        exit;
    }

    // Generate unique invoice number
    $invoice_no = "INV-" . date('YmdHis');

    $stmt = $conn->prepare("INSERT INTO invoices (invoice_no, student_id, class_id, total_amount, due_amount, issue_date, due_date) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param("siiids", $invoice_no, $student_id, $class_id, $total_amount, $total_amount, $due_date);

    if ($stmt->execute()) {
        $invoice_id = $stmt->insert_id;
        $stmt->close();

        // Insert items
        $item_stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, fee_type_id, amount) VALUES (?, ?, ?)");
        foreach ($items as $it) {
            $fee_id = intval($it['fee_type_id']);
            $amt = floatval($it['amount']);
            $item_stmt->bind_param("iid", $invoice_id, $fee_id, $amt);
            $item_stmt->execute();
        }
        $item_stmt->close();

        sweetAlert("✅ Success!", "Invoice created successfully!", "success", "../fees/view_invoices.php");
    } else {
        sweetAlert("❌ Error!", "Failed to create invoice. Try again.", "error", "../fees/create_invoice.php");
    }
}
?>
