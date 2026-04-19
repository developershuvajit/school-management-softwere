<?php
include('../config/database.php');

header('Content-Type: application/json');

if(!isset($_GET['class_id']) || $_GET['class_id'] == ""){
    echo json_encode([]);
    exit;
}

$class_id = intval($_GET['class_id']);

$q = $conn->query("SELECT id, section_name FROM sections WHERE class_id='$class_id' ORDER BY section_name ASC");

$data = [];
while($r = $q->fetch_assoc()){
    $data[] = $r;
}

echo json_encode($data);
?>
