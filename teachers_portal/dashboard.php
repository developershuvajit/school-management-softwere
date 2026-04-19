<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['teacher_id'])) {
    header("Location: ../login.php");
    exit();
}

include('../config/database.php');

$teacher_id = intval($_SESSION['teacher_id']);

// Fetch teacher details
$teacherQuery = "SELECT * FROM teachers WHERE id = $teacher_id";
$teacherRes = $conn->query($teacherQuery);
if (!$teacherRes || $teacherRes->num_rows == 0) {
    die("Invalid Teacher ID");
}

$teacher = $teacherRes->fetch_assoc();

// Get teacher joining date
$joining_date = new DateTime($teacher['join_date']);
$joining_month = $joining_date->format('n'); // 1-12
$joining_year = $joining_date->format('Y');

// Get current month and year
$current_month = date('n');
$current_year = date('Y');

// Get teacher salary from teacher_salary table
$salary_query = "SELECT 
    month_year,
    basic,
    allowance,
    deduction,
    net_salary,
    status,
    paid_on
FROM teacher_salary 
WHERE teacher_id = ? 
AND status = 'paid'
ORDER BY STR_TO_DATE(month_year, '%Y-%m')";

$stmt = $conn->prepare($salary_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$salary_result = $stmt->get_result();

$paid_salaries = [];
$total_salary_paid = 0;
$salary_paid_details = [];
$monthly_salary = 0; // Default salary from teachers table

while ($row = $salary_result->fetch_assoc()) {
    $month_key = $row['month_year']; // Format: YYYY-MM
    $paid_salaries[$month_key] = true;

    $salary_paid_details[$month_key] = [
        'basic' => (float)$row['basic'],
        'allowance' => (float)$row['allowance'],
        'deduction' => (float)$row['deduction'],
        'net_salary' => (float)$row['net_salary'],
        'status' => $row['status'],
        'paid_on' => $row['paid_on']
    ];

    // Only add to total if status is 'paid'
    if ($row['status'] === 'paid') {
        $total_salary_paid += $row['net_salary'];
    }

    // Get monthly salary from the first record (if exists)
    if ($monthly_salary == 0) {
        $monthly_salary = $row['basic'] + $row['allowance'];
    }
}
$stmt->close();

// NEW: Get cancelled months separately for display
$cancelled_query = "SELECT 
    month_year,
    basic,
    allowance,
    deduction,
    net_salary,
    status,
    paid_on
FROM teacher_salary 
WHERE teacher_id = ? 
AND status = 'cancelled'
ORDER BY STR_TO_DATE(month_year, '%Y-%m')";

$stmt = $conn->prepare($cancelled_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$cancelled_result = $stmt->get_result();
$cancelled_details = [];

while ($row = $cancelled_result->fetch_assoc()) {
    $month_key = $row['month_year'];
    $cancelled_details[$month_key] = [
        'basic' => (float)$row['basic'],
        'allowance' => (float)$row['allowance'],
        'deduction' => (float)$row['deduction'],
        'net_salary' => (float)$row['net_salary'],
        'status' => $row['status'],
        'paid_on' => $row['paid_on']
    ];
}
$stmt->close();
// If no salary record found, use salary from teachers table
if ($monthly_salary == 0) {
    $monthly_salary = (float)$teacher['salary'] ?: 0;
}

// Get office leaves
$leave_query = "SELECT 
    leave_type,
    from_date,
    to_date,
    reason,
    status
FROM teacher_leaves 
WHERE teacher_id = ? 
AND YEAR(from_date) = YEAR(CURDATE())
ORDER BY from_date DESC";

$stmt = $conn->prepare($leave_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$leave_result = $stmt->get_result();
$leaves = [];
$current_year_leaves = 0;

while ($row = $leave_result->fetch_assoc()) {
    $leaves[] = $row;

    // Calculate leave days for current year
    $from_date = new DateTime($row['from_date']);
    $to_date = new DateTime($row['to_date']);
    $interval = $from_date->diff($to_date);
    $current_year_leaves += $interval->days + 1; // Inclusive of both dates
}
$stmt->close();

// Fine configuration (you can adjust these values)
$max_free_leaves = 1; // Maximum free leaves per month
$fine_per_day = $monthly_salary / 30; // Fine per day after free limit (same as daily salary)

// Calculate total working days for current month (excluding Sundays)
function getWorkingDaysInMonth($year, $month)
{
    $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $workingDays = 0;

    for ($day = 1; $day <= $totalDays; $day++) {
        $date = new DateTime("$year-$month-$day");
        $dayOfWeek = $date->format('N'); // 1=Monday, 7=Sunday

        // Count only Monday to Saturday as working days
        if ($dayOfWeek <= 6) {
            $workingDays++;
        }
    }

    return $workingDays;
}

// Get current month working days
$current_working_days = getWorkingDaysInMonth($current_year, $current_month);

// Calculate daily salary based on working days
$daily_salary = $monthly_salary / $current_working_days;

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

// Generate months from joining to current month
$unpaid_months = [];
$salary_due_total = 0;
$currentDate = new DateTime($joining_year . '-' . $joining_month . '-01');
$endDate = new DateTime($current_year . '-' . $current_month . '-01');

// Don't include current month for salary calculation
$endDate->modify('-1 month');

$salary_details = [];

while ($currentDate <= $endDate) {
    $year = (int)$currentDate->format('Y');
    $month = (int)$currentDate->format('n');
    $month_key = sprintf('%04d-%02d', $year, $month);
    $month_name = $month_names[$month] . ' ' . $year;

    // Check if salary is paid OR cancelled for this month
    $is_paid = isset($paid_salaries[$month_key]) && $paid_salaries[$month_key] === true;
    $is_cancelled = isset($cancelled_details[$month_key]);

    // Skip if salary is already paid OR cancelled
    if (!$is_paid && !$is_cancelled) {
        // Calculate working days for this month
        $month_working_days = getWorkingDaysInMonth($year, $month);
        $month_daily_salary = $monthly_salary / $month_working_days;

        // ... [rest of your calculation code] ...
    }
    // If cancelled, we skip it - it's not due and not paid
    // If paid, we skip it - it's already paid

    $currentDate->modify('+1 month');
}

// Get current month leaves
$current_month_leaves_query = "SELECT 
    COUNT(*) as total_leaves,
    SUM(CASE WHEN leave_type = 'office_leave' THEN 1 ELSE 0 END) as office_leaves,
    SUM(CASE WHEN leave_type = 'absent' THEN 1 ELSE 0 END) as absents
FROM teacher_leaves 
WHERE teacher_id = ?
AND MONTH(from_date) = MONTH(CURDATE())
AND YEAR(from_date) = YEAR(CURDATE())
AND status = 'approved'";

$stmt = $conn->prepare($current_month_leaves_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$current_month_leaves_result = $stmt->get_result();
$current_month_leaves = $current_month_leaves_result->fetch_assoc() ?? [
    'total_leaves' => 0,
    'office_leaves' => 0,
    'absents' => 0
];
$stmt->close();

// Get recent leaves (last 5)
$recent_leaves_query = "SELECT 
    leave_type,
    from_date,
    to_date,
    reason,
    status,
    created_at
FROM teacher_leaves 
WHERE teacher_id = ?
ORDER BY from_date DESC
LIMIT 5";

$stmt = $conn->prepare($recent_leaves_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$recent_leaves_result = $stmt->get_result();
$recent_leaves = [];
while ($row = $recent_leaves_result->fetch_assoc()) {
    $recent_leaves[] = $row;
}
$stmt->close();

// Get calendar data for current month
$calendar_query = "SELECT 
    from_date,
    to_date,
    leave_type,
    status
FROM teacher_leaves 
WHERE teacher_id = ?
AND MONTH(from_date) = MONTH(CURDATE())
AND YEAR(from_date) = YEAR(CURDATE())
AND status = 'approved'
ORDER BY from_date";

$stmt = $conn->prepare($calendar_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$calendar_result = $stmt->get_result();
$calendar_data = [];
while ($row = $calendar_result->fetch_assoc()) {
    $start = new DateTime($row['from_date']);
    $end = new DateTime($row['to_date']);

    // Add all dates in the range to calendar
    $current = clone $start;
    while ($current <= $end) {
        $date_str = $current->format('Y-m-d');
        $calendar_data[$date_str] = $row['leave_type'];
        $current->modify('+1 day');
    }
}
$stmt->close();

// Get recent notices
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

// Generate month options for filter
$month_options = [];
$temp_date = new DateTime($joining_year . '-' . $joining_month . '-01');
$end_filter_date = new DateTime($current_year . '-' . $current_month . '-01');

while ($temp_date <= $end_filter_date) {
    $year = (int)$temp_date->format('Y');
    $month = (int)$temp_date->format('n');
    $month_key = sprintf('%04d-%02d', $year, $month);
    $month_name = $month_names[$month] . ' ' . $year;

    $month_options[$month_key] = $month_name;
    $temp_date->modify('+1 month');
}

// Get selected month from filter
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Teacher Portal - <?php echo htmlspecialchars($teacher['name']); ?></title>
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/css/style.css" rel="stylesheet">
    <style>
        .teacher-info-card {
            border-left: 4px solid #007bff;
        }

        .salary-due-card {
            border-left: 4px solid #dc3545;
        }

        .salary-paid-card {
            border-left: 4px solid #28a745;
        }

        .leaves-card {
            border-left: 4px solid #17a2b8;
        }

        .calendar-card {
            border-left: 4px solid #6f42c1;
        }

        .notice-card {
            border-left: 4px solid #20c997;
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

        .calendar-day {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 2px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
        }

        .calendar-day.present {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .calendar-day.absent {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .calendar-day.office_leave {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .calendar-day.weekend {
            background-color: #e9ecef;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .calendar-day.future {
            background-color: #f8f9fa;
            color: #adb5bd;
            border: 1px solid #dee2e6;
        }

        .calendar-day.today {
            border: 2px solid #007bff;
            font-weight: bold;
        }

        .calendar-month-header {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            font-weight: bold;
            text-align: center;
        }

        .calendar-days-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
        }

        .calendar-weekday {
            text-align: center;
            font-size: 11px;
            color: #6c757d;
            padding: 5px;
            font-weight: bold;
            background-color: #f8f9fa;
            border-radius: 3px;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            margin-right: 15px;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
            margin-right: 5px;
        }

        .deduction-details {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 10px;
            margin-top: 5px;
            font-size: 12px;
        }

        .deduction-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            padding-bottom: 3px;
            border-bottom: 1px solid #eee;
        }

        .deduction-item:last-child {
            border-bottom: none;
        }

        .leave-badge {
            font-size: 11px;
            padding: 2px 6px;
        }

        .leave-badge.office_leave {
            background-color: #fff3cd;
            color: #856404;
        }

        .leave-badge.absent {
            background-color: #f8d7da;
            color: #721c24;
        }

        .leave-badge.pending {
            background-color: #cce5ff;
            color: #004085;
        }

        .leave-badge.approved {
            background-color: #d4edda;
            color: #155724;
        }

        .leave-badge.rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .notice-item {
            border-left: 3px solid #007bff;
            padding-left: 10px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            background: linear-gradient(to right, transparent 0%, rgba(0, 123, 255, 0.03) 100%);
        }

        .notice-item:hover {
            background: linear-gradient(to right, transparent 0%, rgba(0, 123, 255, 0.08) 100%);
            transform: translateX(5px);
        }

        .salary-breakdown {
            max-height: 300px;
            overflow-y: auto;
        }

        .month-filter {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .month-filter select {
            flex: 1;
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
                            <h4>Welcome, <?php echo htmlspecialchars($teacher['name']); ?></h4>
                            <p class="mb-0">Teacher Portal - Manage your profile, leaves, and salary information</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Teacher Profile Card -->
                    <div class="col-lg-4">
                        <div class="card teacher-info-card">
                            <div class="card-body text-center">
                                <?php if (!empty($teacher['photo'])): ?>
                                    <img src="../<?php echo htmlspecialchars($teacher['photo']); ?>"
                                        alt="Teacher Photo" class="profile-img rounded-circle mb-3">
                                <?php else: ?>
                                    <img src="../uploads/default/default_teacher.jpg"
                                        alt="Default Teacher" class="profile-img rounded-circle mb-3">
                                <?php endif; ?>

                                <h4 class="card-title"><?php echo htmlspecialchars($teacher['name']); ?></h4>
                                <p class="text-muted">Teacher Code: <?php echo htmlspecialchars($teacher['teacher_code']); ?></p>

                                <div class="row text-left mt-4">
                                    <div class="col-12 mb-2">
                                        <strong><i class="ti-email mr-2"></i>Email:</strong>
                                        <span class="float-right"><?php echo htmlspecialchars($teacher['email']); ?></span>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <strong><i class="ti-mobile mr-2"></i>Phone:</strong>
                                        <span class="float-right"><?php echo htmlspecialchars($teacher['phone']); ?></span>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <strong><i class="ti-book mr-2"></i>Qualification:</strong>
                                        <span class="float-right"><?php echo htmlspecialchars($teacher['qualification']); ?></span>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <strong><i class="ti-bookmark mr-2"></i>Subject:</strong>
                                        <span class="float-right"><?php echo htmlspecialchars($teacher['subject']); ?></span>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <strong><i class="ti-user mr-2"></i>Join Date:</strong>
                                        <span class="float-right"><?php echo date('d/m/Y', strtotime($teacher['join_date'])); ?></span>
                                    </div>
                                    <div class="col-12">
                                        <strong><i class="ti-wallet mr-2"></i>Monthly Salary:</strong>
                                        <span class="float-right">
                                            <span class="badge badge-success">₹<?php echo number_format($monthly_salary, 2); ?></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Salary and Leaves Summary Cards -->
                    <div class="col-lg-8">
                        <div class="row">
                            <!-- Salary Due Card -->
                            <div class="col-lg-6 col-sm-6 mb-3">
                                <div class="card salary-due-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="stat-icon d-inline-block mr-3">
                                                    <i class="ti-alert text-danger fa-2x"></i>
                                                </div>
                                                <div class="stat-content d-inline-block">
                                                    <div class="stat-text text-muted">Salary Due</div>
                                                    <div class="stat-digit">₹<?php echo number_format($salary_due_total, 2); ?></div>
                                                    <div class="stat-text small">
                                                        ₹<?php echo number_format($monthly_salary, 2); ?> per month
                                                        <?php if (!empty($unpaid_months)): ?>
                                                            | <?php echo count($unpaid_months); ?> month(s) due
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if (!empty($unpaid_months)): ?>
                                            <div class="mt-3">
                                                <small class="text-muted">Due Months:</small>
                                                <div class="salary-breakdown mt-2">
                                                    <?php foreach (array_slice($unpaid_months, 0, 3) as $month): ?>
                                                        <div class="month-badge month-due mb-1 p-2">
                                                            <strong><?php echo $month['month_name']; ?></strong><br>
                                                            <small>
                                                                Salary: ₹<?php echo number_format($month['monthly_salary'], 2); ?><br>
                                                                Net: ₹<?php echo number_format($month['net_salary'], 2); ?>
                                                                <?php if ($month['deductions'] > 0): ?>
                                                                    <br><span class="text-danger">Deductions: ₹<?php echo number_format($month['deductions'], 2); ?></span>
                                                                <?php endif; ?>
                                                            </small>
                                                        </div>
                                                    <?php endforeach; ?>
                                                    <?php if (count($unpaid_months) > 3): ?>
                                                        <div class="month-badge mt-1">
                                                            +<?php echo count($unpaid_months) - 3; ?> more months
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="mt-3">
                                                <span class="badge badge-success">No salary dues</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Salary Paid Card -->
                            <div class="col-lg-6 col-sm-6 mb-3">
                                <div class="card salary-paid-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="stat-icon d-inline-block mr-3">
                                                    <i class="ti-wallet text-success fa-2x"></i>
                                                </div>
                                                <div class="stat-content d-inline-block">
                                                    <div class="stat-text text-muted">Total Salary Received</div>
                                                    <div class="stat-digit">₹<?php echo number_format($total_salary_paid, 2); ?></div>
                                                    <div class="stat-text small">
                                                        Based on <?php echo count($salary_paid_details); ?> payment(s)
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if (!empty($salary_paid_details)): ?>
                                            <div class="mt-3">
                                                <small class="text-muted">Last 3 Payments:</small>
                                                <div class="mt-2">
                                                    <?php
                                                    $recent_payments = array_slice($salary_paid_details, -3, 3, true);
                                                    foreach ($recent_payments as $month_key => $payment):
                                                        if ($payment['status'] === 'paid'):
                                                    ?>
                                                            <div class="month-badge month-paid mb-1 p-2">
                                                                <strong><?php echo $month_key; ?></strong><br>
                                                                <small>
                                                                    Net: ₹<?php echo number_format($payment['net_salary'], 2); ?><br>
                                                                    Paid: <?php echo date('d/m/Y', strtotime($payment['paid_on'])); ?>
                                                                </small>
                                                            </div>
                                                    <?php
                                                        endif;
                                                    endforeach;
                                                    ?>
                                                </div>
                                                <?php if (!empty($cancelled_details)): ?>
                                                    <div class="mt-3">
                                                        <small class="text-muted text-danger">Cancelled Months (Not Paid):</small>
                                                        <div class="mt-2">
                                                            <?php
                                                            $recent_cancelled = array_slice($cancelled_details, -3, 3, true);
                                                            foreach ($recent_cancelled as $month_key => $cancelled):
                                                            ?>
                                                                <div class="month-badge month-due mb-1 p-2">
                                                                    <strong><?php echo $month_key; ?></strong><br>
                                                                    <small>
                                                                        Status: <span class="text-danger"><?php echo ucfirst($cancelled['status']); ?></span><br>
                                                                        Cancelled on: <?php echo date('d/m/Y', strtotime($cancelled['paid_on'])); ?>
                                                                    </small>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        <?php if (count($cancelled_details) > 3): ?>
                                                            <small class="text-muted">+<?php echo count($cancelled_details) - 3; ?> more cancelled months</small>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="mt-3">
                                                <span class="badge badge-secondary">No payments received yet</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Month Leaves -->
                            <div class="col-lg-6 col-sm-6 mb-3">
                                <div class="card leaves-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="stat-icon d-inline-block mr-3">
                                                    <i class="ti-calendar text-info fa-2x"></i>
                                                </div>
                                                <div class="stat-content d-inline-block">
                                                    <div class="stat-text text-muted">Current Month Leaves</div>
                                                    <div class="stat-digit"><?php echo $current_month_leaves['total_leaves']; ?> days</div>
                                                    <div class="stat-text small">
                                                        Office: <?php echo $current_month_leaves['office_leaves']; ?> |
                                                        Absent: <?php echo $current_month_leaves['absents']; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <div class="progress" style="height: 8px;">
                                                <?php
                                                $leave_percentage = min(100, ($current_month_leaves['total_leaves'] / $current_working_days) * 100);
                                                ?>
                                                <div class="progress-bar bg-warning" role="progressbar"
                                                    style="width: <?php echo $leave_percentage; ?>%"
                                                    aria-valuenow="<?php echo $leave_percentage; ?>"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100">
                                                </div>
                                            </div>
                                            <div class="mt-2 small text-muted">
                                                <?php echo $current_month_leaves['total_leaves']; ?> of <?php echo $current_working_days; ?> working days
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Deductions Info -->
                            <div class="col-lg-6 col-sm-6 mb-3">
                                <div class="card h-100" style="border-left: 4px solid #ff9800;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="stat-icon d-inline-block mr-3">
                                                    <i class="ti-alert-triangle text-warning fa-2x"></i>
                                                </div>
                                                <div class="stat-content d-inline-block">
                                                    <div class="stat-text text-muted">Deductions Policy</div>
                                                    <div class="stat-digit">₹<?php echo number_format($daily_salary, 2); ?></div>
                                                    <div class="stat-text small">
                                                        Fine per day after <?php echo $max_free_leaves; ?> absent days
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <div class="deduction-details">
                                                <div class="deduction-item">
                                                    <span>Monthly Salary:</span>
                                                    <span class="font-weight-bold">₹<?php echo number_format($monthly_salary, 2); ?></span>
                                                </div>
                                                <div class="deduction-item">
                                                    <span>Working Days This Month:</span>
                                                    <span><?php echo $current_working_days; ?> days</span>
                                                </div>
                                                <div class="deduction-item">
                                                    <span>Daily Salary:</span>
                                                    <span>₹<?php echo number_format($daily_salary, 2); ?> per day</span>
                                                </div>
                                                <div class="deduction-item">
                                                    <span>Free Absent Days/Month:</span>
                                                    <span><?php echo $max_free_leaves; ?> days</span>
                                                </div>
                                                <div class="deduction-item">
                                                    <span>Fine per Extra Absent Day:</span>
                                                    <span class="text-danger">₹<?php echo number_format($daily_salary, 2); ?> (Daily Salary)</span>
                                                </div>
                                                <div class="deduction-item">
                                                    <span>Office Leave Deduction:</span>
                                                    <span>₹<?php echo number_format($daily_salary, 2); ?> per day</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar and Recent Leaves Row -->
                <div class="row mt-4">
                    <!-- Attendance Calendar -->
                    <div class="col-lg-8">
                        <div class="card calendar-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Leave Calendar</h4>
                                <div class="month-filter">
                                    <form method="GET" class="d-flex align-items-center">
                                        <select name="month" class="form-control form-control-sm" onchange="this.form.submit()">
                                            <?php foreach ($month_options as $key => $name): ?>
                                                <option value="<?php echo $key; ?>" <?php echo $selected_month == $key ? 'selected' : ''; ?>>
                                                    <?php echo $name; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="legend mb-3">
                                    <div class="legend-item">
                                        <span class="legend-color" style="background-color: #d4edda;"></span>
                                        <span>Present/Working Day</span>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-color" style="background-color: #f8d7da;"></span>
                                        <span>Absent</span>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-color" style="background-color: #fff3cd;"></span>
                                        <span>Office Leave</span>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-color" style="background-color: #e9ecef;"></span>
                                        <span>Weekend</span>
                                    </div>
                                </div>

                                <?php
                                // Parse selected month
                                $selected_year = date('Y', strtotime($selected_month . '-01'));
                                $selected_month_num = date('n', strtotime($selected_month . '-01'));

                                // Get calendar data for selected month
                                $selected_calendar_query = "SELECT 
                                    from_date,
                                    to_date,
                                    leave_type,
                                    status
                                FROM teacher_leaves 
                                WHERE teacher_id = ?
                                AND MONTH(from_date) = ?
                                AND YEAR(from_date) = ?
                                AND status = 'approved'
                                ORDER BY from_date";

                                $stmt = $conn->prepare($selected_calendar_query);
                                $stmt->bind_param("iii", $teacher_id, $selected_month_num, $selected_year);
                                $stmt->execute();
                                $selected_calendar_result = $stmt->get_result();
                                $selected_calendar_data = [];
                                while ($row = $selected_calendar_result->fetch_assoc()) {
                                    $start = new DateTime($row['from_date']);
                                    $end = new DateTime($row['to_date']);

                                    // Add all dates in the range to calendar
                                    $current = clone $start;
                                    while ($current <= $end) {
                                        $date_str = $current->format('Y-m-d');
                                        $selected_calendar_data[$date_str] = $row['leave_type'];
                                        $current->modify('+1 day');
                                    }
                                }
                                $stmt->close();

                                // Generate calendar for selected month
                                $first_day = new DateTime($selected_year . '-' . $selected_month_num . '-01');
                                $days_in_month = (int)$first_day->format('t');
                                $first_day_of_week = (int)$first_day->format('N'); // 1=Monday, 7=Sunday
                                $today = new DateTime();
                                ?>
                                <div class="calendar-month">
                                    <div class="calendar-month-header">
                                        <?php echo $month_names[$selected_month_num] . ' ' . $selected_year; ?>
                                    </div>
                                    <div class="calendar-days-grid">
                                        <!-- Weekday headers -->
                                        <div class="calendar-weekday">Mon</div>
                                        <div class="calendar-weekday">Tue</div>
                                        <div class="calendar-weekday">Wed</div>
                                        <div class="calendar-weekday">Thu</div>
                                        <div class="calendar-weekday">Fri</div>
                                        <div class="calendar-weekday">Sat</div>
                                        <div class="calendar-weekday">Sun</div>

                                        <!-- Empty cells for days before the first day -->
                                        <?php for ($i = 1; $i < $first_day_of_week; $i++): ?>
                                            <div class="calendar-day"></div>
                                        <?php endfor; ?>

                                        <!-- Days of the month -->
                                        <?php for ($day = 1; $day <= $days_in_month; $day++):
                                            $date_str = sprintf('%04d-%02d-%02d', $selected_year, $selected_month_num, $day);
                                            $date_obj = new DateTime($date_str);
                                            $day_of_week = (int)$date_obj->format('N');

                                            // Determine status
                                            $status = 'present';
                                            if (isset($selected_calendar_data[$date_str])) {
                                                $status = $selected_calendar_data[$date_str];
                                            } elseif ($day_of_week == 7) { // Sunday
                                                $status = 'weekend';
                                            }

                                            // Check if today
                                            $is_today = ($date_str == $today->format('Y-m-d'));
                                        ?>
                                            <div class="calendar-day <?php echo $status . ($is_today ? ' today' : ''); ?>"
                                                data-date="<?php echo $date_str; ?>"
                                                data-status="<?php echo $status; ?>"
                                                title="<?php echo date('d M Y', strtotime($date_str)); ?> - <?php echo ucfirst(str_replace('_', ' ', $status)); ?>">
                                                <?php echo $day; ?>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Leaves -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Recent Leaves</h4>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_leaves)): ?>
                                    <div class="text-center text-muted py-3">
                                        <i class="ti-calendar fa-2x mb-2"></i>
                                        <p>No leave records found</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_leaves as $leave):
                                                    $from_date = new DateTime($leave['from_date']);
                                                    $to_date = new DateTime($leave['to_date']);

                                                    // If same day, show single date
                                                    if ($from_date->format('Y-m-d') == $to_date->format('Y-m-d')) {
                                                        $date_display = $from_date->format('d/m');
                                                    } else {
                                                        $date_display = $from_date->format('d/m') . ' - ' . $to_date->format('d/m');
                                                    }
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <span class="leave-badge <?php echo $leave['leave_type']; ?>">
                                                                <?php echo ucfirst(str_replace('_', ' ', $leave['leave_type'])); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo $date_display; ?></td>
                                                        <td>
                                                            <span class="leave-badge <?php echo $leave['status']; ?>">
                                                                <?php echo ucfirst($leave['status']); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">
                                            Total Leaves This Year: <?php echo $current_year_leaves; ?> days
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notice Board -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card notice-card shadow-sm border-0">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0 text-white">
                                    <i class="ti-bell mr-2"></i> School Notices
                                </h4>
                                <?php if (!empty($notices)): ?>
                                    <span class="badge badge-light badge-pill px-3 py-2" style="font-size: 0.9rem;">
                                        <i class="ti-announcement mr-1"></i>
                                        <?php echo count($notices); ?> Active Notice<?php echo count($notices) > 1 ? 's' : ''; ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="card-body p-0">
                                <?php if (empty($notices)): ?>
                                    <div class="text-center py-5">
                                        <div class="mb-3">
                                            <i class="ti-bell text-muted" style="font-size: 48px;"></i>
                                        </div>
                                        <h5 class="text-muted mb-2">No notices available</h5>
                                        <p class="text-muted small">Check back later for updates</p>
                                    </div>
                                <?php else: ?>
                                    <div class="notices-list">
                                        <?php foreach ($notices as $index => $notice): ?>
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
                                            $isNew = strtotime($notice['start_date']) > strtotime('-3 days');
                                            ?>

                                            <div class="notice-item p-4 <?php echo $index !== count($notices) - 1 ? 'border-bottom' : ''; ?>">
                                                <div class="d-flex align-items-start">
                                                    <div class="mr-3">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <span class="badge badge-<?php echo $typeConfig['bg']; ?> badge-pill px-3 py-2 mb-2">
                                                                <i class="<?php echo $typeConfig['icon']; ?> mr-1"></i>
                                                                <?php echo ucfirst($noticeType); ?>
                                                            </span>

                                                            <?php if ($isNew): ?>
                                                                <span class="badge badge-danger badge-pill px-2 py-1" style="font-size: 0.7rem;">
                                                                    NEW
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h5 class="font-weight-bold text-dark mb-1">
                                                                <?php echo htmlspecialchars($notice['title']); ?>
                                                            </h5>

                                                            <?php if (isset($notice['priority']) && $notice['priority'] == 'high'): ?>
                                                                <span class="badge badge-danger ml-2">
                                                                    <i class="ti-flag mr-1"></i> High Priority
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <div class="notice-content mb-3">
                                                            <?php
                                                            $content = htmlspecialchars($notice['content']);
                                                            $truncated = strlen($content) > 200 ? substr($content, 0, 200) . '...' : $content;
                                                            ?>
                                                            <p class="mb-0"><?php echo $truncated; ?></p>

                                                            <?php if (strlen($content) > 200): ?>
                                                                <a href="#" class="text-primary read-more-btn"
                                                                    data-full-content="<?php echo htmlspecialchars($notice['content']); ?>">
                                                                    <i class="ti-more-alt mr-1"></i> Read More
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>

                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div class="text-muted small">
                                                                <span class="mr-3">
                                                                    <i class="ti-calendar mr-1"></i>
                                                                    <?php echo date('F j, Y', strtotime($notice['start_date'])); ?>
                                                                </span>

                                                                <?php if (!empty($notice['end_date'])): ?>
                                                                    <span class="mr-3">
                                                                        <i class="ti-timer mr-1"></i>
                                                                        Ends: <?php echo date('M j', strtotime($notice['end_date'])); ?>
                                                                    </span>
                                                                <?php endif; ?>

                                                                <?php if (!empty($notice['author'])): ?>
                                                                    <span>
                                                                        <i class="ti-user mr-1"></i>
                                                                        <?php echo htmlspecialchars($notice['author']); ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($notices)): ?>
                                <div class="card-footer bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <i class="ti-info-alt mr-1"></i>
                                            Showing <?php echo count($notices); ?> notice<?php echo count($notices) > 1 ? 's' : ''; ?>
                                        </div>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i class="ti-archive mr-1"></i> View All Notices
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
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

            // Read more functionality for notices
            $(document).on('click', '.read-more-btn', function(e) {
                e.preventDefault();
                var fullContent = $(this).data('full-content');
                $(this).parent().find('p').html(fullContent);
                $(this).remove();
            });

            // Initialize salary accordion
            $('.collapse').on('show.bs.collapse', function() {
                $(this).prev('.card-header').find('.btn').addClass('active');
            });

            $('.collapse').on('hide.bs.collapse', function() {
                $(this).prev('.card-header').find('.btn').removeClass('active');
            });

            // Calendar day hover effect
            $('.calendar-day').hover(
                function() {
                    if ($(this).data('date')) {
                        $(this).css('transform', 'scale(1.1)');
                    }
                },
                function() {
                    if ($(this).data('date')) {
                        $(this).css('transform', 'scale(1)');
                    }
                }
            );
        });
    </script>
</body>

</html>