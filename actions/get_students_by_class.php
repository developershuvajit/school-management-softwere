<?php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $academic_year = $conn->real_escape_string($_POST['academic_year']);
    $class_id = (int)$_POST['class_id'];
    
    $sql = "SELECT 
                s.id,
                s.student_id,
                s.first_name,
                s.last_name,
                s.roll_number,
                s.photo,
                c.class_name,
                sec.section_name
            FROM students s
            LEFT JOIN classes c ON c.id = s.class_id
            LEFT JOIN sections sec ON sec.id = s.section_id
            WHERE s.class_id = $class_id 
            AND s.academic_year = '$academic_year'
            AND s.status = 'active'
            ORDER BY s.first_name ASC";
    
    $result = $conn->query($sql);
    $students = [];
    
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'students' => $students
    ]);
}
?>