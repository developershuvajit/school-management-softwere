<?php
include('../config/database.php');

$class_id = $_GET['class_id'];

$q = $conn->query("SELECT section_name FROM sections WHERE class_id = $class_id ORDER BY section_name ASC");

$sections = [];
while ($r = $q->fetch_assoc()) {
    $sections[] = $r['section_name'];
}

echo json_encode($sections);
?>
