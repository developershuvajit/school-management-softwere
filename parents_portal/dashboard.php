<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit();
}

include('../config/database.php');

$student_id = intval($_SESSION['student_id']);
// Fetch student details
$studentQuery = "SELECT s.*, c.class_name, sec.section_name, ay.academic_year
FROM students s
LEFT JOIN classes c ON s.class_id = c.id
LEFT JOIN sections sec ON s.section_id = sec.id
LEFT JOIN academic_years ay ON s.academic_year_id = ay.id
WHERE s.id = $student_id";

$studentRes = $conn->query($studentQuery);
if (!$studentRes || $studentRes->num_rows == 0) {
    die("Invalid Student ID");
}

$student = $studentRes->fetch_assoc();
$class_id = intval($student['class_id']);
$academic_year_id = intval($student['academic_year_id']);
$has_transport = $student['has_transport'] ?? 0;

// Get student admission date
$admission_date = new DateTime($student['created_at']);
$admission_month = $admission_date->format('n'); // 1-12
$admission_year = $admission_date->format('Y');

// Get current month and year
$current_month = date('n');
$current_year = date('Y');

// Get monthly fee amount
$monthly_fee_query = "SELECT amount FROM fee_types WHERE class_id = ? AND frequency = 'monthly' AND academic_year_id = ? LIMIT 1";
$stmt = $conn->prepare($monthly_fee_query);
$stmt->bind_param("ii", $class_id, $academic_year_id);
$stmt->execute();
$monthly_fee_result = $stmt->get_result();
$monthly_fee_data = $monthly_fee_result->fetch_assoc();
$monthly_fee_amount = $monthly_fee_data ? (float)$monthly_fee_data['amount'] : 0;
$stmt->close();

// Get transport fee amount
$transport_fee_amount = $has_transport ? ((float)$student['transport_fee'] ?? 0) : 0;

// Get already paid monthly fees
$paid_monthly_fees_query = "SELECT * FROM monthly_fees WHERE student_id = ? AND status = 'paid'";
$stmt = $conn->prepare($paid_monthly_fees_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$paid_monthly_result = $stmt->get_result();
$paid_monthly_fees = [];
$total_monthly_paid = 0;
$monthly_paid_details = [];
while ($row = $paid_monthly_result->fetch_assoc()) {
    $month_key = $row['year'] . '-' . $row['month'];
    $paid_monthly_fees[$month_key] = true;
    $monthly_paid_details[$month_key] = [
        'amount' => $row['amount'],
        'paid' => $row['month']
    ];
    $total_monthly_paid += $row['month'];
}
$stmt->close();

// Get already paid transport fees
$paid_transport_fees_query = "SELECT * FROM transport_fees WHERE student_id = ? AND status = 'paid'";
$stmt = $conn->prepare($paid_transport_fees_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$paid_transport_result = $stmt->get_result();
$paid_transport_fees = [];
$total_transport_paid = 0;
$transport_paid_details = [];
while ($row = $paid_transport_result->fetch_assoc()) {
    $month_key = $row['year'] . '-' . $row['month'];
    $paid_transport_fees[$month_key] = true;
    $transport_paid_details[$month_key] = [
        'amount' => $row['amount'],
        'paid' => $row['month']
    ];
    $total_transport_paid += $row['month'];
}
$stmt->close();

// Calculate due months and amounts
$month_names = [
    1 => 'January',
    2 => 'February',
    3 => 'March',
    4 => 'April',
    5 => 'May',
    6 => 'June',
    7 => 'July',
    8 => 'August',
    9 => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December'
];

// Generate months from admission to current month
$months = [];
$currentDate = new DateTime($admission_year . '-' . $admission_month . '-01');
$endDate = new DateTime($current_year . '-' . $current_month . '-01');

// Don't charge for current month
$endDate->modify('-1 month');

$monthly_unpaid_months = [];
$transport_unpaid_months = [];
$monthly_due_total = 0;
$transport_due_total = 0;

while ($currentDate <= $endDate) {
    $year = (int)$currentDate->format('Y');
    $month = (int)$currentDate->format('n');
    $month_key = $year . '-' . $month;

    // Check monthly fees
    if (!isset($paid_monthly_fees[$month_key]) && $monthly_fee_amount > 0) {
        $monthly_unpaid_months[] = [
            'year' => $year,
            'month' => $month,
            'month_name' => $month_names[$month] . ' ' . $year
        ];
        $monthly_due_total += $monthly_fee_amount;
    }

    // Check transport fees
    if ($has_transport && !isset($paid_transport_fees[$month_key]) && $transport_fee_amount > 0) {
        $transport_unpaid_months[] = [
            'year' => $year,
            'month' => $month,
            'month_name' => $month_names[$month] . ' ' . $year
        ];
        $transport_due_total += $transport_fee_amount;
    }

    $currentDate->modify('+1 month');
}

// Calculate total due
$total_due = $monthly_due_total + $transport_due_total;
$total_paid = $total_monthly_paid + $total_transport_paid;

// Fetch attendance for current month
$attendance_query = "SELECT 
    COUNT(*) as total_days,
    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days
FROM attendance 
WHERE student_id = ? 
AND MONTH(attendance_date) = MONTH(CURDATE())
AND YEAR(attendance_date) = YEAR(CURDATE())";

$stmt = $conn->prepare($attendance_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$attendance_result = $stmt->get_result();
$attendance = $attendance_result->fetch_assoc() ?? ['total_days' => 0, 'present_days' => 0, 'absent_days' => 0];
$stmt->close();

// Fetch recent exam results (last 5)
$exam_query = "SELECT 
    er.*,
    s.subject_name,
    e.exam_name,
    e.start_date
FROM exam_results er
LEFT JOIN subjects s ON er.subject_id = s.id
LEFT JOIN exams e ON er.exam_id = e.id
WHERE er.student_id = ?
ORDER BY e.start_date DESC
LIMIT 5";

$stmt = $conn->prepare($exam_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$exam_result = $stmt->get_result();
$exam_results = [];
while ($row = $exam_result->fetch_assoc()) {
    $exam_results[] = $row;
}
$stmt->close();

// Fetch recent notices for parents
$notice_query = "SELECT * FROM notices
WHERE start_date <= CURDATE()
AND (end_date IS NULL OR end_date >= CURDATE())
ORDER BY start_date DESC
LIMIT 5;";

$notice_result = $conn->query($notice_query);
$notices = [];
if ($notice_result) {
    while ($row = $notice_result->fetch_assoc()) {
        $notices[] = $row;
    }
}




?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Parent Portal - <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
    <style>
        .student-info-card {
            border-left: 4px solid #007bff;
        }

        .payment-due-card {
            border-left: 4px solid #dc3545;
        }

        .payment-paid-card {
            border-left: 4px solid #28a745;
        }

        .transport-card {
            border-left: 4px solid #ff9800;
        }

        .attendance-card {
            border-left: 4px solid #17a2b8;
        }

        .performance-card {
            border-left: 4px solid #6f42c1;
        }

        .badge-due {
            background-color: #dc3545;
        }

        .badge-paid {
            background-color: #28a745;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .profile-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 3px solid #007bff;
        }

        .month-badge {
            display: inline-block;
            padding: 3px 8px;
            margin: 2px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 12px;
        }

        .month-due {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .month-paid {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .due-list {
            max-height: 150px;
            overflow-y: auto;
        }

        .progress-bar {
            background-color: #28a745;
        }

        .notice-item {
            border-left: 3px solid #007bff;
            padding-left: 10px;
            margin-bottom: 15px;
        }

        .notice-high {
            border-left-color: #dc3545;
        }

        .notice-medium {
            border-left-color: #ffc107;
        }
    </style>
    <style>
        .notice-item {
            transition: all 0.3s ease;
            background: linear-gradient(to right, transparent 0%, rgba(0, 123, 255, 0.03) 100%);
        }

        .notice-item:hover {
            background: linear-gradient(to right, transparent 0%, rgba(0, 123, 255, 0.08) 100%);
            transform: translateX(5px);
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        .read-more-btn {
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .read-more-btn:hover {
            text-decoration: underline !important;
        }

        .card {
            border-radius: 10px;
            overflow: hidden;
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important;
        }

        .badge-pill {
            border-radius: 50px;
        }
    </style>
</head>


<body>
    <div id="main-wrapper">
        <?php include "../includes/navbar.php" ?>
        <?php include('../includes/sidebar_logic.php') ?>

        <div class="content-body text-dark">
            <div class="container-fluid">
                <!-- Welcome Message -->
                <div class="row page-titles mx-0">
                    <div class="col-sm-12 p-md-0">
                        <div class="welcome-text">
                            <h4>Welcome, Parent of <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h4>
                            <p class="mb-0">Student Portal - Monitor your child's academic progress and payments</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Student Profile Card -->
                    <div class="col-lg-4">
                        <div class="card student-info-card">
                            <div class="card-body text-center">
                                <?php if (!empty($student['photo'])): ?>
                                    <img src="../<?php echo htmlspecialchars($student['photo']); ?>"
                                        alt="" class="profile-img rounded-circle mb-3">
                                <?php else: ?>
                                    <img src="../uploads/default/default_student.jpg"
                                        alt="" class="profile-img rounded-circle mb-3">
                                <?php endif; ?>

                                <h4 class="card-title"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h4>
                                <p class="text-muted">Student ID: <?php echo htmlspecialchars($student['student_id']); ?></p>

                                <div class="row text-left mt-4">
                                    <div class="col-12 mb-2">
                                        <strong><i class="ti-book mr-2"></i>Class & Section:</strong>
                                        <span class="float-right"><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?> / <?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <strong><i class="ti-calendar mr-2"></i>Academic Year:</strong>
                                        <span class="float-right"><?php echo htmlspecialchars($student['academic_year']); ?></span>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <strong><i class="ti-user mr-2"></i>Admission Date:</strong>
                                        <span class="float-right"><?php echo date('d/m/Y', strtotime($student['created_at'])); ?></span>
                                    </div>
                                    <div class="col-12">
                                        <strong><i class="ti-bus mr-2"></i>Transport:</strong>
                                        <span class="float-right">
                                            <?php if ($has_transport): ?>
                                                <span class="badge badge-success">Yes - ₹<?php echo number_format($transport_fee_amount, 2); ?>/month</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">No</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary Cards -->
                    <div class="col-lg-8">
                        <div class="row">


                            <!-- Academic Fees Due -->
                            <div class="col-lg-6 col-sm-6 mb-3">
                                <div class="card payment-due-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="stat-icon d-inline-block mr-3">
                                                    <i class="ti-alert text-danger fa-2x"></i>
                                                </div>
                                                <div class="stat-content d-inline-block">
                                                    <div class="stat-text text-muted">Academic Fees Due</div>
                                                    <div class="stat-digit">₹<?php echo number_format($monthly_due_total, 2); ?></div>
                                                    <div class="stat-text small">
                                                        ₹<?php echo number_format($monthly_fee_amount, 2); ?> per month
                                                        <?php if (!empty($monthly_unpaid_months)): ?>
                                                            | <?php echo count($monthly_unpaid_months); ?> month(s) due
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if (!empty($monthly_unpaid_months)): ?>
                                            <div class="due-list mt-3">
                                                <small class="text-muted">Due Months:</small><br>
                                                <?php foreach (array_slice($monthly_unpaid_months, 0, 5) as $month): ?>
                                                    <span class="month-badge month-due"><?php echo $month['month_name']; ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($monthly_unpaid_months) > 5): ?>
                                                    <span class="month-badge">+<?php echo count($monthly_unpaid_months) - 5; ?> more</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="mt-3">
                                                <span class="badge badge-success">No dues</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Transport Fees Due -->
                            <?php if ($has_transport): ?>
                                <div class="col-lg-6 col-sm-6 mb-3">
                                    <div class="card transport-card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="stat-icon d-inline-block mr-3">
                                                        <i class="ti-bus text-warning fa-2x"></i>
                                                    </div>
                                                    <div class="stat-content d-inline-block">
                                                        <div class="stat-text text-muted">Transport Fees Due</div>
                                                        <div class="stat-digit">₹<?php echo number_format($transport_due_total, 2); ?></div>
                                                        <div class="stat-text small">
                                                            ₹<?php echo number_format($transport_fee_amount, 2); ?> per month
                                                            <?php if (!empty($transport_unpaid_months)): ?>
                                                                | <?php echo count($transport_unpaid_months); ?> month(s) due
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if (!empty($transport_unpaid_months)): ?>
                                                <div class="due-list mt-3">
                                                    <small class="text-muted">Due Months:</small><br>
                                                    <?php foreach (array_slice($transport_unpaid_months, 0, 5) as $month): ?>
                                                        <span class="month-badge month-due"><?php echo $month['month_name']; ?></span>
                                                    <?php endforeach; ?>
                                                    <?php if (count($transport_unpaid_months) > 5): ?>
                                                        <span class="month-badge">+<?php echo count($transport_unpaid_months) - 5; ?> more</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="mt-3">
                                                    <span class="badge badge-success">No dues</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Attendance Summary -->
                            <div class="col-lg-6 col-sm-6 mb-3">
                                <div class="card attendance-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="stat-icon d-inline-block mr-3">
                                                    <i class="ti-check-box text-info fa-2x"></i>
                                                </div>
                                                <div class="stat-content d-inline-block">
                                                    <div class="stat-text text-muted">This Month's Attendance</div>
                                                    <div class="stat-digit">
                                                        <?php
                                                        $attendance_percentage = ($attendance['total_days'] > 0)
                                                            ? round(($attendance['present_days'] / $attendance['total_days']) * 100, 1)
                                                            : 0;
                                                        echo $attendance_percentage . '%';
                                                        ?>
                                                    </div>
                                                    <div class="stat-text small">
                                                        <?php echo $attendance['present_days']; ?> Present /
                                                        <?php echo $attendance['absent_days']; ?> Absent
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="progress mt-3" style="height: 8px;">
                                            <div class="progress-bar" role="progressbar"
                                                style="width: <?php echo $attendance_percentage; ?>%;"
                                                aria-valuenow="<?php echo $attendance_percentage; ?>"
                                                aria-valuemin="0"
                                                aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Overall Performance -->
                            <div class="col-lg-6 col-sm-6 mb-3">
                                <div class="card performance-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="stat-icon d-inline-block mr-3">
                                                    <i class="ti-bar-chart text-primary fa-2x"></i>
                                                </div>
                                                <div class="stat-content d-inline-block">
                                                    <div class="stat-text text-muted">Recent Performance</div>
                                                    <div class="stat-digit">
                                                        <?php
                                                        if (!empty($exam_results)) {
                                                            $total_marks = 0;
                                                            $obtained_marks = 0;
                                                            foreach ($exam_results as $result) {
                                                                $total_marks += $result['total_marks'];
                                                                $obtained_marks += $result['obtained_marks'];
                                                            }
                                                            $percentage = ($total_marks > 0) ? round(($obtained_marks / $total_marks) * 100, 1) : 0;
                                                            echo $percentage . '%';
                                                        } else {
                                                            echo 'N/A';
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="stat-text small">
                                                        Based on last <?php echo count($exam_results); ?> exam(s)
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if (!empty($exam_results)): ?>
                                            <div class="mt-3">
                                                <small class="text-muted">Recent Exam: </small>
                                                <?php
                                                $latest_exam = $exam_results[0];
                                                $latest_percentage = ($latest_exam['total_marks'] > 0)
                                                    ? round(($latest_exam['obtained_marks'] / $latest_exam['total_marks']) * 100, 1)
                                                    : 0;
                                                ?>
                                                <span class="badge 
                                                <?php echo $latest_percentage >= 90 ? 'badge-success' : ($latest_percentage >= 75 ? 'badge-primary' : ($latest_percentage >= 60 ? 'badge-warning' : 'badge-danger')); ?>">
                                                    <?php echo htmlspecialchars($latest_exam['exam_name']); ?>:
                                                    <?php echo $latest_percentage; ?>%
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
 
$currentDate = date('Y-m-d');

// Fetch attendance IN time and status
$attStmt = $conn->prepare("
    SELECT att_time, status 
    FROM attendance 
    WHERE student_id = ? AND DATE(att_time) = ?
    ORDER BY att_time ASC LIMIT 1
");
$attStmt->bind_param("is", $student_id, $currentDate);
$attStmt->execute();
$attRes = $attStmt->get_result();
$attendance = $attRes->fetch_assoc();

// Fetch attendance OUT time from attendance_out table
$outStmt = $conn->prepare("
    SELECT out_time 
    FROM attendance_out 
    WHERE student_id = ? AND att_date = ?
    ORDER BY out_time DESC LIMIT 1
");
$outStmt->bind_param("is", $student_id, $currentDate);
$outStmt->execute();
$outRes = $outStmt->get_result();
$outAttendance = $outRes->fetch_assoc();

// Prepare display variables
$inTime = $attendance ? date('h:i A', strtotime($attendance['att_time'])) : 'Not marked';
$status = $attendance ? $attendance['status'] : '-';
$outTime = $outAttendance ? date('h:i A', strtotime($outAttendance['out_time'])) : 'Not marked';




// ================= TODAY PROGRESS DETAILS =================
$today = date('Y-m-d');

$sql = "SELECT pt.name, spd.value
FROM student_progress sp
JOIN student_progress_details spd ON sp.id = spd.progress_id
JOIN progress_types pt ON pt.id = spd.type_id
WHERE sp.student_id=? AND sp.date=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $student_id, $today);
$stmt->execute();
$res = $stmt->get_result();

$t_total = 0;
$t_yes = 0;
$today_items = [];

while($row = $res->fetch_assoc()){
    $t_total++;
    if($row['value'] == 'Yes') $t_yes++;

    $today_items[] = $row; // store items
}

$today_percent = ($t_total > 0) ? round(($t_yes/$t_total)*100) : 0;
$stmt->close();

?>

 <div class="row">

    <!-- Attendance -->
    <div class="col-12 col-md-6 mb-3">
        <div class="card shadow-sm rounded-3 p-3">
            <h5 class="mb-2">Attendance Today (<?= date('d M, Y') ?>)</h5>

            <p><strong>In Time:</strong> <?= htmlspecialchars($inTime) ?></p>
            <p><strong>Out Time:</strong> <?= htmlspecialchars($outTime) ?></p>

            <p><strong>Status:</strong> 
                <?php if ($status === 'Late'): ?>
                    <span class="badge bg-warning text-dark">Late</span>
                <?php elseif ($status === 'Present'): ?>
                    <span class="badge bg-success text-white">Present</span>
                <?php else: ?>
                    <span class="badge bg-secondary text-white">
                        <?= htmlspecialchars($status) ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Today Progress -->
    <div class="col-12 col-md-6 mb-3">
        <div class="card shadow-sm p-3">

            <h5 class="mb-2">📅 Today Progress</h5>

            <?php if($t_total > 0): ?>

                <h4 class="text-success"><?= $today_percent ?>%</h4>

                <div class="progress mb-2" style="height:6px;">
                    <div class="progress-bar bg-success" 
                         style="width:<?= $today_percent ?>%">
                    </div>
                </div>

                <!-- ITEM LIST -->
                <div style="max-height:120px; overflow-y:auto;">
                    <?php foreach($today_items as $item): ?>
                        <div class="d-flex justify-content-between border-bottom py-1">
                            <span><?= $item['name'] ?></span>

                            <span class="badge <?= $item['value']=='Yes' ? 'bg-success':'bg-danger' ?>">
                                <?= $item['value'] ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <p class="text-muted">No progress added today</p>
            <?php endif; ?>

        </div>
    </div>

</div>


                <!-- Notice Board -->
 <?php
include('../config/database.php');

// Fetch all notices
$sql = "SELECT * FROM notices ORDER BY start_date DESC";
$result = $conn->query($sql);

$notices = [];
if ($result && $result->num_rows > 0) {
    $notices = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0 text-white">
                    <i class="ti-bell mr-2"></i> School Notices
                </h4>
                <?php if (!empty($notices)): ?>
                    <span class="badge badge-light badge-pill px-3 py-2">
                        <?php echo count($notices); ?> Notice<?php echo count($notices) > 1 ? 's' : ''; ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="card-body p-0">
                <?php if (empty($notices)): ?>
                    <div class="text-center py-5">
                        <h5 class="text-muted">No notices available</h5>
                    </div>
                <?php else: ?>

                    <?php foreach ($notices as $notice): ?>

                        <?php
                        $noticeType = strtolower($notice['notice_type'] ?? 'general');

                        $typeColors = [
                            'urgent' => ['bg' => 'danger', 'icon' => 'ti-alert'],
                            'important' => ['bg' => 'warning', 'icon' => 'ti-star'],
                            'academic' => ['bg' => 'info', 'icon' => 'ti-book'],
                            'event' => ['bg' => 'success', 'icon' => 'ti-calendar'],
                            'general' => ['bg' => 'secondary', 'icon' => 'ti-bell']
                        ];

                        $typeConfig = $typeColors[$noticeType] ?? $typeColors['general'];
                        ?>

                        <div class="p-4 border-bottom">
                            <div class="d-flex align-items-start">

                                <!-- Type -->
                                <div class="mr-3">
                                    <span class="badge badge-<?php echo $typeConfig['bg']; ?>">
                                        <?php echo ucfirst($noticeType); ?>
                                    </span>
                                </div>

                                <!-- Content -->
                                <div class="flex-grow-1">

                                    <h5 class="font-weight-bold">
                                        <?php echo htmlspecialchars($notice['title']); ?>
                                    </h5>

                                    <p>
                                        <?php echo nl2br(htmlspecialchars($notice['content'])); ?>
                                    </p>

                                    <small class="text-muted">
                                        <i class="ti-calendar"></i>
                                        <?php echo date('F j, Y', strtotime($notice['start_date'])); ?>

                                        <?php if (!empty($notice['end_date'])): ?>
                                            | Ends: <?php echo date('M j, Y', strtotime($notice['end_date'])); ?>
                                        <?php endif; ?>
                                    </small>

                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>
            </div>

        </div>
    </div>
</div>




















                <!-- Exam Results Section -->
                <div class="row mt-4">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Recent Exam Results</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Exam</th>
                                                <th>Subject</th>
                                                <th>Date</th>
                                                <th>Obtained Marks</th>
                                                <th>Total Marks</th>
                                                <th>Percentage</th>
                                                <th>Grade</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($exam_results)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">
                                                        No exam results available
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($exam_results as $result): ?>
                                                    <?php
                                                    $percentage = ($result['total_marks'] > 0)
                                                        ? round(($result['obtained_marks'] / $result['total_marks']) * 100, 1)
                                                        : 0;

                                                    // Determine grade
                                                    if ($percentage >= 90) {
                                                        $grade = 'A+';
                                                        $grade_class = 'badge-success';
                                                    } elseif ($percentage >= 80) {
                                                        $grade = 'A';
                                                        $grade_class = 'badge-primary';
                                                    } elseif ($percentage >= 70) {
                                                        $grade = 'B';
                                                        $grade_class = 'badge-info';
                                                    } elseif ($percentage >= 60) {
                                                        $grade = 'C';
                                                        $grade_class = 'badge-warning';
                                                    } elseif ($percentage >= 50) {
                                                        $grade = 'D';
                                                        $grade_class = 'badge-secondary';
                                                    } else {
                                                        $grade = 'F';
                                                        $grade_class = 'badge-danger';
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($result['exam_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($result['subject_name']); ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($result['start_date'])); ?></td>
                                                        <td><?php echo $result['obtained_marks']; ?></td>
                                                        <td><?php echo $result['total_marks']; ?></td>
                                                        <td><?php echo $percentage; ?>%</td>
                                                        <td><span class="badge <?php echo $grade_class; ?>"><?php echo $grade; ?></span></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
        <?php include "../includes/footer.php" ?>
    </div>
    <?php include "../includes/js_links.php" ?>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>

</html>