<?php
include('../config/database.php');

$student_id = $_POST['student_id'];
$date = $_POST['date'];
$progressData = $_POST['progress'];

// 🔍 check duplicate
$check = $conn->query("SELECT id FROM student_progress 
WHERE student_id='$student_id' AND date='$date'");

if($check->num_rows > 0){
    ?>
    <script>
    alert("⚠️ Already added for this date!");
    window.location.href = "../academics/student_progress.php";
    </script>
    <?php
    exit;
}

// insert
$conn->query("INSERT INTO student_progress (student_id, date)
              VALUES ('$student_id','$date')");

$progress_id = $conn->insert_id;

// details insert
foreach($progressData as $type_id => $value){
    $conn->query("INSERT INTO student_progress_details 
        (progress_id, type_id, value)
        VALUES ('$progress_id','$type_id','$value')");
}
?>

<script>
alert("✅ Progress Saved Successfully!");
window.location.href = "../academics/student_progress.php";
</script>