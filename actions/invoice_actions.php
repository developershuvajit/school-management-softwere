<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/database.php');
include('../includes/alert_helper.php');

// 🔴 DELETE INVOICE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    if ($id > 0) {
        // Delete invoice items first
        $conn->query("DELETE FROM invoice_items WHERE invoice_id = $id");

        // Delete invoice
        $stmt = $conn->prepare("DELETE FROM invoices WHERE id = ?");
        if (!$stmt) {
            sweetAlert("Database Error!", $conn->error, "error", "../fees/view_invoices.php");
            exit;
        }

        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            sweetAlert("🗑️ Deleted!", "Invoice deleted successfully!", "success", "../fees/view_invoices.php");
        } else {
            sweetAlert("❌ Error!", "Failed to delete invoice.", "error", "../fees/view_invoices.php");
        }

        $stmt->close();
    } else {
        sweetAlert("⚠️ Invalid!", "Invalid invoice ID.", "warning", "../fees/view_invoices.php");
    }
}
?>
