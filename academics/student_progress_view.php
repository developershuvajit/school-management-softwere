<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

include('../config/database.php');
include('../includes/alert_helper.php');

if (!isset($_GET['id'])) {
    sweetAlert('Error', 'Student ID missing!', 'error', 'javascript:history.back()');
    exit;
}

$student_id = (int)$_GET['id'];

// student info
$sql = "SELECT s.*, c.class_name, sec.section_name, ay.academic_year
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        LEFT JOIN academic_years ay ON s.academic_year_id = ay.id
        WHERE s.id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    sweetAlert('Error', 'Student not found!', 'error', 'javascript:history.back()');
    exit;
}

$student = $res->fetch_assoc();
$photo = $student['photo'] ? '../' . $student['photo'] : '../assets/img/default-student.png';


// ================= PROGRESS CALCULATION =================

// overall
$total = 0;
$yes = 0;

$q = "SELECT spd.value 
      FROM student_progress_details spd
      JOIN student_progress sp ON sp.id=spd.progress_id
      WHERE sp.student_id='$student_id'";

$r = $conn->query($q);

while($d = $r->fetch_assoc()){
    $total++;
    if($d['value'] == 'Yes') $yes++;
}

$overall_percent = ($total > 0) ? round(($yes/$total)*100) : 0;


// today
$today = date('Y-m-d');

$q2 = "SELECT spd.value 
       FROM student_progress_details spd
       JOIN student_progress sp ON sp.id=spd.progress_id
       WHERE sp.student_id='$student_id' AND sp.date='$today'";

$r2 = $conn->query($q2);

$t_total = 0;
$t_yes = 0;

while($d = $r2->fetch_assoc()){
    $t_total++;
    if($d['value'] == 'Yes') $t_yes++;
}

$today_percent = ($t_total > 0) ? round(($t_yes/$t_total)*100) : 0;


// monthly
$month = date('m');
$year = date('Y');

$q3 = "SELECT spd.value 
       FROM student_progress_details spd
       JOIN student_progress sp ON sp.id=spd.progress_id
       WHERE sp.student_id='$student_id'
       AND MONTH(sp.date)='$month' AND YEAR(sp.date)='$year'";

$r3 = $conn->query($q3);

$m_total = 0;
$m_yes = 0;

while($d = $r3->fetch_assoc()){
    $m_total++;
    if($d['value'] == 'Yes') $m_yes++;
}

$monthly_percent = ($m_total > 0) ? round(($m_yes/$m_total)*100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Progress</title>

<link href="../public/css/style.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css">
    <!-- SweetAlert2 & FontAwesome -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body, p, td, th, h5, h4 {
    color: #000 !important;
    font-weight: 600;
}
</style>

</head>

<body>

<?php include "../includes/preloader.php"; ?>

<div id="main-wrapper">

<?php include "../includes/navbar.php"; ?>
<?php include "../includes/sidebar_logic.php"; ?>

<div class="content-body">
<div class="container my-5">

<div class="card shadow-lg border-0">

<!-- HEADER -->
<div class="card-header bg-primary text-white">
    <h4 class="text-white mb-0">
        <?= $student['first_name'] . ' ' . $student['last_name'] ?>
    </h4>
</div>

<!-- BODY -->
<div class="card-body">

<!-- STUDENT INFO -->
<div class="row mb-4 align-items-center">

    <div class="col-md-3 text-center">
        <img src="<?= $photo ?>" class="img-fluid rounded-circle border shadow-sm"
             style="height:150px;width:150px;object-fit:cover;">
    </div>

    <div class="col-md-9">
        <div class="row g-2">

            <div class="col-md-4">
                <strong>📞 Phone:</strong><br>
                <?= $student['parent_phone'] ?: '-' ?>
            </div>

            <div class="col-md-4">
                <strong>🏫 Class:</strong><br>
                <?= $student['class_name'] ?? '-' ?>
            </div>

            <div class="col-md-4">
                <strong>📚 Section:</strong><br>
                <?= $student['section_name'] ?? '-' ?>
            </div>

            <div class="col-md-4">
                <strong>📅 Academic Year:</strong><br>
                <?= $student['academic_year'] ?? '-' ?>
            </div>

        </div>
    </div>

</div>

<hr>

<!-- PROGRESS CARDS -->
<div class="row text-center mb-4">

    <div class="col-md-4">
        <div class="card border shadow-sm p-3">
            <h6>Today</h6>
            <h3 class="text-primary"><?= $today_percent ?>%</h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border shadow-sm p-3">
            <h6>This Month</h6>
            <h3 class="text-success"><?= $monthly_percent ?>%</h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border shadow-sm p-3">
            <h6>Overall</h6>
            <h3><?= $overall_percent ?>%</h3>
        </div>
    </div>

</div>

<!-- ANALYSIS -->
<h6>
Total: <?= $total ?> | Completed: <?= $yes ?> | Pending: <?= $total - $yes ?>
</h6>

<hr>

<!-- CHART -->
<canvas id="progressChart" height="100"></canvas>

<hr>

<!-- TABLE -->
<h5>Daily Progress</h5>

<div class="table-responsive">
<table class="table table-bordered table-striped">

<thead>
<tr>
    <th>Date</th>
    <th>Type</th>
    <th>Status</th>
</tr>
</thead>

<tbody>
<?php
$sql = "SELECT sp.date, pt.name, spd.value
        FROM student_progress sp
        JOIN student_progress_details spd ON sp.id=spd.progress_id
        JOIN progress_types pt ON pt.id=spd.type_id
        WHERE sp.student_id='$student_id'
        ORDER BY sp.date DESC";

$res = $conn->query($sql);

while($row = $res->fetch_assoc()):
?>
<tr>
    <td><?= $row['date'] ?></td>
    <td><?= $row['name'] ?></td>
    <td>
        <span class="badge <?= $row['value']=='Yes' ? 'bg-success':'bg-danger' ?>">
            <?= $row['value'] ?>
        </span>
    </td>
</tr>
<?php endwhile; ?>
</tbody>

</table>
</div>

</div> <!-- card-body -->
</div> <!-- card -->

</div> <!-- container -->
</div> <!-- content-body -->

<?php include "../includes/footer.php"; ?>

</div> <!-- main-wrapper -->

<?php include "../includes/js_links.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('progressChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Today', 'Monthly', 'Overall'],
        datasets: [{
            label: 'Progress %',
            data: [<?= $today_percent ?>, <?= $monthly_percent ?>, <?= $overall_percent ?>]
        }]
    }
});
</script>

</body>
</html>