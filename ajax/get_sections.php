<?php
include('../config/database.php');

$class_id = $_POST['class_id'];
$selected_section = $_POST['selected_section'] ?? '';

$q = $conn->query("SELECT * FROM sections WHERE class_id='$class_id' ORDER BY section_name ASC");

echo "<option disabled>Select Section</option>";

while ($r = $q->fetch_assoc()) {
    $sel = ($r['id'] == $selected_section) ? "selected" : "";
    echo "<option value='{$r['id']}' $sel>{$r['section_name']}</option>";
}
