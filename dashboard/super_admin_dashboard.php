<?php error_reporting(E_ALL);
    ini_set('display_errors', 1);
    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        header('Location: ../login.php');
        exit;
    }

    include('../config/database.php');

    /* ==========================
   DASHBOARD TOTALS
========================== */
    $totals_sql = "SELECT
    (SELECT COUNT(*) FROM students) AS total_students,
    (SELECT COUNT(*) FROM teachers) AS total_teachers,
    (SELECT COUNT(*) FROM classes) AS total_classes,
    COALESCE((SELECT SUM(total_amount) FROM invoices),0) AS total_invoiced,
    COALESCE((SELECT SUM(total_paid) FROM invoices),0) AS total_paid,
    COALESCE((SELECT SUM(balance) FROM invoices),0) AS total_due,
    (SELECT COUNT(*) FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS new_admissions_30d,
    (SELECT COUNT(*) FROM attendance WHERE attendance_date = CURDATE()) AS present_today
";
    $totals = $conn->query($totals_sql)->fetch_assoc();

    $total_students = (int)$totals['total_students'];
    $present_today  = (int)$totals['present_today'];
    $attendance_percentage = $total_students > 0 ? round(($present_today / $total_students) * 100, 1) : 0;

    /* ==========================
   MONTHLY FEES (12 MONTHS)
========================== */
    $monthly_sql = "
SELECT 
    DATE_FORMAT(created_at,'%Y-%m') AS ym,
    DATE_FORMAT(MIN(created_at),'%b %Y') AS label,
    SUM(total_amount) AS invoiced,
    SUM(total_paid) AS paid,
    SUM(balance) AS due
FROM invoices
GROUP BY ym
ORDER BY ym DESC
LIMIT 12
";
    $monthly_res = $conn->query($monthly_sql);
    $monthly = array_reverse($monthly_res->fetch_all(MYSQLI_ASSOC));

    /* ==========================
   ADMISSIONS (12 MONTHS)
========================== */
    $admission_sql = "
SELECT 
    DATE_FORMAT(created_at,'%Y-%m') AS ym,
    DATE_FORMAT(MIN(created_at),'%b %Y') AS label,
    COUNT(*) AS cnt
FROM students
GROUP BY ym
ORDER BY ym DESC
LIMIT 12
";
    $admissions = array_reverse($conn->query($admission_sql)->fetch_all(MYSQLI_ASSOC));

    /* ==========================
   ATTENDANCE (7 DAYS)
========================== */
    $att_sql = "
SELECT attendance_date AS dt, COUNT(*) AS cnt
FROM attendance
WHERE attendance_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
GROUP BY dt
ORDER BY dt
";
    $att_res = $conn->query($att_sql);
    $att_days = $att_counts = [];
    while ($r = $att_res->fetch_assoc()) {
        $att_days[] = $r['dt'];
        $att_counts[] = (int)$r['cnt'];
    }

    /* ==========================
   CLASS WISE DUE (TOP 10)
========================== */
    $class_due_sql = "
SELECT 
    c.id,
    c.class_name,
    COALESCE(SUM(i.balance),0) AS due_total
FROM classes c
LEFT JOIN invoices i ON i.class_id = c.id
GROUP BY c.id, c.class_name
ORDER BY due_total DESC
LIMIT 10
";
    $class_due_res = $conn->query($class_due_sql);
    $class_labels = $class_due_vals = [];
    while ($r = $class_due_res->fetch_assoc()) {
        $class_labels[] = $r['class_name'];
        $class_due_vals[] = (float)$r['due_total'];
    }

    /* ==========================
   RECENT INVOICES
========================== */
    $recent_sql = "
SELECT 
    i.id,i.invoice_no,i.total_amount,
    COALESCE(i.total_paid,0) AS total_paid,
    COALESCE(i.balance,0) AS balance,
    s.first_name,s.last_name,i.created_at
FROM invoices i
LEFT JOIN students s ON s.id=i.student_id
ORDER BY i.created_at DESC
LIMIT 10
";
    $recent_res = $conn->query($recent_sql);

    /* ==========================
   AI INSIGHTS
========================== */
    $insights = [];

    /* LOW ATTENDANCE */
    $low_att_sql = "
SELECT 
    c.id,
    c.class_name,
    ROUND((COUNT(a.id)/NULLIF(COUNT(s.id),0))*100,1) AS att_rate
FROM classes c
LEFT JOIN students s ON s.class_id=c.id
LEFT JOIN attendance a 
    ON a.student_id=s.id 
    AND a.attendance_date>=DATE_SUB(CURDATE(),INTERVAL 6 DAY)
GROUP BY c.id, c.class_name
HAVING att_rate < 70
LIMIT 5
";
    $low_att = $conn->query($low_att_sql);
    while ($r = $low_att->fetch_assoc()) {
        $insights[] = "Low attendance: {$r['class_name']} ({$r['att_rate']}%)";
    }

    /* HIGH DUE CLASSES */
    $high_due_sql = "
SELECT 
    c.class_name,
    COALESCE(SUM(i.balance),0) AS due_total
FROM classes c
LEFT JOIN invoices i ON i.class_id=c.id
GROUP BY c.id, c.class_name
ORDER BY due_total DESC
LIMIT 5
";
    $hd = $conn->query($high_due_sql);
    $tmp = [];
    while ($r = $hd->fetch_assoc()) {
        $tmp[] = "{$r['class_name']} (₹" . number_format($r['due_total'], 2) . ")";
    }
    if ($tmp) $insights[] = "High dues: " . implode(', ', $tmp);

    if (!$insights) $insights[] = "All systems OK — no alerts.";
    ?>

 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="utf-8">
     <meta http-equiv="X-UA-Compatible" content="IE=edge">
     <meta name="viewport" content="width=device-width,initial-scale=1">
     <title>School Management Softwere</title>
     <!-- Favicon icon -->
     <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
     <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
     <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
     <link href="../public/css/style.css" rel="stylesheet">
     <!-- SweetAlert2 CSS & JS -->
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

 </head>

 <body>
     <div id="main-wrapper">
         <?php include "../includes/navbar.php"; ?>
         <?php include('../includes/sidebar_logic.php'); ?>



         <div class="content-body">
             <div class="container-fluid">

                 <!-- Page Header -->
                 <div class="row page-titles mx-0 align-items-center mb-3">
                     <div class="col-sm-6 p-md-0">
                         <h4 class="card-title">Super Admin Dashboard</h4>
                         <small class="text-muted">Overview · Fees · Attendance · Insights</small>
                     </div>
                     <div class="col-sm-6 text-end">
                         <a class="btn btn-outline-secondary btn-sm" href="../fees/view_invoices.php"><i class="fa fa-list"></i> All Invoices</a>
                         <a class="btn btn-outline-primary btn-sm" href="../fees/create_invoice.php"><i class="fa fa-plus"></i> Create Invoice</a>
                     </div>
                 </div>

                 <!-- Cards -->
                 <div class="row g-3 mb-4">
                     <div class="col-xl-3 col-md-6">
                         <div class="card p-3 shadow-sm">
                             <div class="d-flex justify-content-between">
                                 <div>
                                     <small class="text-muted">Total Students</small>
                                     <h3 class="mt-1"><?= number_format($totals['total_students'] ?? 0) ?></h3>
                                 </div>
                                 <div class="text-end">
                                     <i class="fa fa-user fa-2x text-primary"></i>
                                 </div>
                             </div>
                             <div class="mt-2 text-muted small">New (30d): <?= number_format($totals['new_admissions_30d'] ?? 0) ?></div>
                         </div>
                     </div>

                     <div class="col-xl-3 col-md-6">
                         <div class="card p-3 shadow-sm">
                             <div class="d-flex justify-content-between">
                                 <div>
                                     <small class="text-muted">Teachers</small>
                                     <h3 class="mt-1"><?= number_format($totals['total_teachers'] ?? 0) ?></h3>
                                 </div>
                                 <div class="text-end">
                                     <i class="fa fa-id-badge fa-2x text-success"></i>
                                 </div>
                             </div>
                             <div class="mt-2 text-muted small">Classes: <?= number_format($totals['total_classes'] ?? 0) ?></div>
                         </div>
                     </div>

                     <div class="col-xl-3 col-md-6">
                         <div class="card p-3 shadow-sm">
                             <div class="d-flex justify-content-between">
                                 <div>
                                     <small class="text-muted">Fees Collected</small>
                                     <h3 class="mt-1">₹<?= number_format(floatval($totals['total_paid'] ?? 0), 2) ?></h3>
                                 </div>
                                 <div class="text-end">
                                     <i class="fa fa-money fa-2x text-success"></i>
                                 </div>
                             </div>
                             <div class="mt-2 text-muted small">Invoiced: ₹<?= number_format(floatval($totals['total_invoiced'] ?? 0), 2) ?></div>
                         </div>
                     </div>

                     <div class="col-xl-3 col-md-6">
                         <div class="card p-3 shadow-sm">
                             <div class="d-flex justify-content-between">
                                 <div>
                                     <small class="text-muted">Fees Due</small>
                                     <h3 class="mt-1 text-danger">₹<?= number_format(floatval($totals['total_due'] ?? 0), 2) ?></h3>
                                 </div>
                                 <div class="text-end">
                                     <i class="fa fa-exclamation-triangle fa-2x text-danger"></i>
                                 </div>
                             </div>
                             <div class="mt-2 text-muted small">Attendance: <?= $present_today ?> present (<?= $attendance_percentage ?>%)</div>
                         </div>
                     </div>
                 </div>

                 <!-- Charts Row -->
                 <div class="row g-3">
                     <div class="col-xl-6">
                         <div class="card p-3 shadow-sm">
                             <h5 class="mb-3">Monthly Fees — Invoiced vs Paid vs Due</h5>
                             <canvas id="monthlyChart" height="160"></canvas>
                         </div>
                         <div class="card p-3 mt-3 shadow-sm">
                             <h6 class="mb-2">AI Attendance Insight</h6>
                             <div class="small text-muted">
                                 <strong>Attendance Today:</strong> <?= $present_today ?> / <?= $total_students ?> (<?= $attendance_percentage ?>%)
                             </div>
                             <hr>
                             <!-- list insights -->
                             <?php foreach ($insights as $ins): ?>
                                 <div class="mb-1"><i class="fa fa-lightbulb-o text-warning"></i> <?= htmlspecialchars($ins) ?></div>
                             <?php endforeach; ?>
                         </div>
                     </div>

                     <div class="col-xl-3">
                         <div class="card p-3 shadow-sm">
                             <h5 class="mb-3">Paid vs Due</h5>
                             <canvas id="pieChart" height="160"></canvas>
                             <div class="mt-2 small text-muted">Paid: ₹<?= number_format(floatval($totals['total_paid'] ?? 0), 2) ?> · Due: ₹<?= number_format(floatval($totals['total_due'] ?? 0), 2) ?></div>
                         </div>


                     </div>

                     <div class="col-xl-3">
                         <div class="card p-3 shadow-sm">
                             <h5 class="mb-3">Admissions Growth (12 months)</h5>
                             <canvas id="admissionChart" height="140"></canvas>
                         </div>

                         <div class="card p-3 mt-3 shadow-sm">
                             <h6 class="mb-2">Class-wise Due (Top 10)</h6>
                             <canvas id="classDueChart" height="160"></canvas>
                         </div>
                     </div>
                 </div>

                 <!-- Attendance trend + Recent invoices -->
               

             </div> <!-- container-fluid -->
         </div> <!-- content-body -->

         <?php include "../includes/footer.php"; ?>
     </div> <!-- main-wrapper -->

     <?php include "../includes/js_links.php"; ?>
     <!-- Chart.js -->
     <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

     <script>
         // Prepare monthly chart data from PHP
         const monthly = <?= json_encode($monthly, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
         const months = monthly.map(i => i.label || i.ym);
         const invoiced = monthly.map(i => parseFloat(i.invoiced || 0));
         const paid = monthly.map(i => parseFloat(i.paid || 0));
         const due = monthly.map(i => parseFloat(i.due || 0));

         new Chart(document.getElementById('monthlyChart').getContext('2d'), {
             type: 'line',
             data: {
                 labels: months,
                 datasets: [{
                         label: 'Invoiced',
                         data: invoiced,
                         borderColor: '#3b82f6',
                         fill: true,
                         tension: 0.35
                     },
                     {
                         label: 'Paid',
                         data: paid,
                         borderColor: '#10b981',
                         fill: true,
                         tension: 0.35
                     },
                     {
                         label: 'Due',
                         data: due,
                         borderColor: '#ef4444',
                         fill: true,
                         tension: 0.35
                     }
                 ]
             },
             options: {
                 responsive: true,
                 plugins: {
                     legend: {
                         position: 'bottom'
                     }
                 }
             }
         });

         // Paid vs Due pie
         const paidTotal = <?= floatval($totals['total_paid'] ?? 0) ?>;
         const dueTotal = <?= floatval($totals['total_due'] ?? 0) ?>;
         new Chart(document.getElementById('pieChart').getContext('2d'), {
             type: 'doughnut',
             data: {
                 labels: ['Paid', 'Due'],
                 datasets: [{
                     data: [paidTotal, dueTotal],
                     backgroundColor: ['#10b981', '#ef4444']
                 }]
             },
             options: {
                 responsive: true,
                 plugins: {
                     legend: {
                         position: 'bottom'
                     }
                 }
             }
         });

         // Admissions area chart
         const admissions = <?= json_encode($admissions) ?>;
         const admLabels = admissions.map(a => a.label);
         const admVals = admissions.map(a => parseInt(a.cnt || 0));
         new Chart(document.getElementById('admissionChart').getContext('2d'), {
             type: 'bar',
             data: {
                 labels: admLabels,
                 datasets: [{
                     label: 'New Students',
                     data: admVals,
                     backgroundColor: '#7c3aed'
                 }]
             },
             options: {
                 responsive: true,
                 plugins: {
                     legend: {
                         display: false
                     }
                 }
             }
         });

         // Class-wise due chart
         const classLabels = <?= json_encode($class_labels) ?>;
         const classVals = <?= json_encode($class_due_vals) ?>;
         new Chart(document.getElementById('classDueChart').getContext('2d'), {
             type: 'bar',
             data: {
                 labels: classLabels,
                 datasets: [{
                     label: 'Due (₹)',
                     data: classVals,
                     backgroundColor: '#f59e0b'
                 }]
             },
             options: {
                 responsive: true,
                 plugins: {
                     legend: {
                         display: false
                     }
                 },
                 scales: {
                     y: {
                         beginAtZero: true
                     }
                 }
             }
         });

         // Attendance last 7 days
         const attLabels = <?= json_encode($att_days) ?>;
         const attCounts = <?= json_encode($att_counts) ?>;
         new Chart(document.getElementById('attChart').getContext('2d'), {
             type: 'line',
             data: {
                 labels: attLabels,
                 datasets: [{
                     label: 'Present',
                     data: attCounts,
                     borderColor: '#0284c7',
                     fill: false
                 }]
             },
             options: {
                 responsive: true,
                 plugins: {
                     legend: {
                         position: 'bottom'
                     }
                 }
             }
         });
     </script>

     <style>
         /* small cosmetics */
         .card {
             border-radius: 10px;
         }

         .page-titles {
             margin-bottom: 1rem;
         }

         .small {
             font-size: .85rem;
         }
     </style>
 </body>

 </html>