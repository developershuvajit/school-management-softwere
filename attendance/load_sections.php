<?php
include('../config/database.php');
if(!isset($_POST['class_id'])) exit;

$class_id = (int)$_POST['class_id'];
$q = $conn->query("SELECT * FROM sections WHERE class_id=$class_id ORDER BY name ASC");
if($q->num_rows > 0){
    echo '<option value="">-- Select Section --</option>';
    while($r = $q->fetch_assoc()){
        echo "<option value='{$r['id']}'>{$r['name']}</option>";
    }
} else {
    echo '<option value="">(No sections)</option>';
}
?>
