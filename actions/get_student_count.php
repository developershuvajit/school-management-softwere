<?php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $academic_year = $conn->real_escape_string($_POST['academic_year']);
    $class_id = (int)$_POST['class_id'];
    
    $sql = "SELECT COUNT(*) as count FROM students 
            WHERE class_id = $class_id 
            AND academic_year = '$academic_year' 
            AND status = 'active'";
    
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'count' => $row['count']
    ]);
}
?>