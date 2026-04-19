<?php
 
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();


if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

include('../config/database.php');      // your DB connection
// head (CSS/meta)
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
 

// 🔹 Filters
$selected_month = $_GET['month'] ?? date('Y-m');
$selected_class = $_GET['class_id'] ?? '';

// 🔹 Fetch classes
$classes = $conn->query("SELECT id, class_name FROM classes ORDER BY class_name ASC");

// 🔹 Build conditions
$conditions = [];

if (!empty($selected_month)) {
    $month_start = $selected_month . "-01";
    $month_end = date("Y-m-t", strtotime($month_start));
    $conditions[] = "i.created_at BETWEEN '$month_start' AND '$month_end'";
}

if (!empty($selected_class)) {
    $selected_class = intval($selected_class);
    $conditions[] = "i.class_id = $selected_class";
}

$where = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// 🔹 Summary
// Summary Query
$summary_sql = "
    SELECT 
        COUNT(*) AS total_invoices,
        SUM(total_amount) AS total_invoiced,
        SUM(total_paid) AS total_paid,
        SUM(balance) AS total_due
    FROM invoices
";

$summary_res = $conn->query($summary_sql);
$summary = $summary_res->fetch_assoc();

// Fix NULL values to 0
$summary = array_map(fn($v) => $v ?? 0, $summary);


// 🔹 Chart Data
$chart_sql = "
    SELECT 
        DATE_FORMAT(i.created_at, '%Y-%m') AS month,
        SUM(i.total_amount) AS invoiced,
        SUM(i.total_paid) AS paid,
        SUM(i.balance) AS due
    FROM invoices i
    GROUP BY DATE_FORMAT(i.created_at, '%Y-%m')
    ORDER BY month ASC
";
$chart_res = $conn->query($chart_sql);
$chart_data = [];
while ($row = $chart_res->fetch_assoc()) $chart_data[] = $row;

// 🔹 Invoice list
$list_sql = "
    SELECT 
        i.id, i.invoice_no, i.total_amount, i.total_paid, i.balance, i.status, i.created_at,
        s.first_name, s.last_name, c.class_name
    FROM invoices i
    JOIN students s ON i.student_id = s.id
    JOIN classes c ON i.class_id = c.id
    $where
    ORDER BY i.created_at DESC
";
$list_res = $conn->query($list_sql);
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

                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <h4 class="card-title">Fees & Collection Reports</h4>
                    </div>
                </div>

                <div class="card p-4 shadow-sm">

                    <form method="GET" class="form-inline mb-4">
                        <label class="mr-2 font-weight-bold">Month:</label>
                        <input type="month" name="month" value="<?= htmlspecialchars($selected_month); ?>" class="form-control mr-3">

                        

                        <button class="btn btn-primary">Filter</button>
                    </form>

                    <!-- Summary Cards -->
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6>Total Invoices</h6>
                                    <h3><?= $summary['total_invoices']; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6>Total Invoiced</h6>
                                    <h3>₹<?= number_format($summary['total_invoiced'], 2); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6>Total Paid</h6>
                                    <h3>₹<?= number_format($summary['total_paid'], 2); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h6>Total Due</h6>
                                    <h3>₹<?= number_format($summary['total_due'], 2); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">📊 Month-wise Collection Summary</h5>
                    <canvas id="feesChart" height="120"></canvas>

                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        const chartData = <?= json_encode($chart_data); ?>;
                        const labels = chartData.map(i => i.month);
                        const invoiced = chartData.map(i => parseFloat(i.invoiced));
                        const paid = chartData.map(i => parseFloat(i.paid));
                        const due = chartData.map(i => parseFloat(i.due));

                        new Chart(document.getElementById('feesChart'), {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                        label: 'Invoiced',
                                        data: invoiced,
                                        backgroundColor: 'rgba(54,162,235,0.6)'
                                    },
                                    {
                                        label: 'Paid',
                                        data: paid,
                                        backgroundColor: 'rgba(75,192,192,0.6)'
                                    },
                                    {
                                        label: 'Due',
                                        data: due,
                                        backgroundColor: 'rgba(255,99,132,0.6)'
                                    }
                                ]
                            },
                            options: {
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    </script>

                    

                </div>
            </div>
        </div>

        <?php include "../includes/footer.php"; ?>
    </div>
    <?php include "../includes/js_links.php"; ?>
</body>

</html>