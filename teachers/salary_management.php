<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

$allowed_roles = ['admin', 'accountant', 'super_admin'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header('Location: ../unauthorized.php');
    exit;
}

include('../config/database.php');

if (!$conn) {
    die("Database connection failed!");
}

// Get teacher ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: teacher_list.php?error=Teacher ID missing');
    exit;
}

$teacher_id = (int)$_GET['id'];

// Fetch teacher details
$teacher_sql = "SELECT * FROM teachers WHERE id = ? AND status = 1";
$teacher_stmt = $conn->prepare($teacher_sql);
if (!$teacher_stmt) {
    die("Database query preparation failed!");
}

$teacher_stmt->bind_param("i", $teacher_id);
if (!$teacher_stmt->execute()) {
    die("Failed to execute database query!");
}

$teacher_result = $teacher_stmt->get_result();

if ($teacher_result->num_rows === 0) {
    header('Location: teacher_list.php?error=Teacher not found or inactive');
    exit;
}

$teacher = $teacher_result->fetch_assoc();
$teacher_stmt->close();

// Get selected year from URL or default to current year
$current_year = date('Y');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : $current_year;

// Validate year range (from join year to current year)
$join_year = (int)date('Y', strtotime($teacher['join_date']));
if ($selected_year < $join_year) {
    $selected_year = $join_year;
} elseif ($selected_year > $current_year) {
    $selected_year = $current_year;
}

// Process salary payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['process_salary'])) {
        $month_year = trim($_POST['month_year']);
        $action = $_POST['action']; // 'pay' or 'cancel'
        $deduction = (float)$_POST['deduction'];
        $allowance = (float)$_POST['allowance'];
        
        // Validate month format (MM-YYYY)
        if (!preg_match('/^(0[1-9]|1[0-2])-\d{4}$/', $month_year)) {
            $_SESSION['error'] = "Invalid month format! Please use MM-YYYY format.";
            header("Location: salary_management.php?id=$teacher_id&year=$selected_year");
            exit;
        }
        
        list($month, $year) = explode('-', $month_year);
        
        // Validate month and year are within allowed range
        if ($year < $join_year || $year > $current_year) {
            $_SESSION['error'] = "Invalid year selected!";
            header("Location: salary_management.php?id=$teacher_id&year=$selected_year");
            exit;
        }
        
        // Calculate net salary
        $basic_salary = $teacher['salary'];
        $net_salary = $basic_salary + $allowance - $deduction;
        
        // Check if record already exists
        $check_sql = "SELECT id, status FROM teacher_salary WHERE teacher_id = ? AND month_year = ?";
        $check_stmt = $conn->prepare($check_sql);
        if (!$check_stmt) {
            $_SESSION['error'] = "Database error!";
            header("Location: salary_management.php?id=$teacher_id&year=$selected_year");
            exit;
        }
        
        $check_stmt->bind_param("is", $teacher_id, $month_year);
        if (!$check_stmt->execute()) {
            $_SESSION['error'] = "Database error!";
            header("Location: salary_management.php?id=$teacher_id&year=$selected_year");
            exit;
        }
        
        $check_result = $check_stmt->get_result();
        $existing_record = $check_result->fetch_assoc();
        $check_stmt->close();
        
        if ($action === 'pay') {
            // Check if already paid
            if ($existing_record && $existing_record['status'] === 'paid') {
                $_SESSION['error'] = "Salary for $month_year is already paid!";
                header("Location: salary_management.php?id=$teacher_id&year=$selected_year");
                exit;
            }
            
            if ($existing_record) {
                // Update existing record to paid
                $update_sql = "UPDATE teacher_salary 
                              SET basic = ?, allowance = ?, deduction = ?, net_salary = ?, 
                                  status = 'paid', paid_on = NOW()
                              WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                if (!$update_stmt) {
                    $_SESSION['error'] = "Database error!";
                    header("Location: salary_management.php?id=$teacher_id&year=$selected_year");
                    exit;
                }
                
                $update_stmt->bind_param("dddds", $basic_salary, $allowance, $deduction, $net_salary, $existing_record['id']);
                if (!$update_stmt->execute()) {
                    $_SESSION['error'] = "Failed to update salary!";
                    header("Location: salary_management.php?id=$teacher_id&year=$selected_year");
                    exit;
                }
                $update_stmt->close();
                $action_type = "updated to paid";
            } else {
                // Insert new paid record
                $insert_sql = "INSERT INTO teacher_salary 
                              (teacher_id, month_year, basic, allowance, deduction, net_salary, status, paid_on) 
                              VALUES (?, ?, ?, ?, ?, ?, 'paid', NOW())";
                $insert_stmt = $conn->prepare($insert_sql);
                if (!$insert_stmt) {
                    $_SESSION['error'] = "Database error!";
                    header("Location: salary_management.php?id=$teacher_id&year=$selected_year");
                    exit;
                }
                
                $insert_stmt->bind_param("isdddd", $teacher_id, $month_year, $basic_salary, $allowance, $deduction, $net_salary);
                if (!$insert_stmt->execute()) {
                    $_SESSION['error'] = "Failed to insert salary record!";
                    header("Location: salary_management.php?id=$teacher_id&year=$selected_year");
                    exit;
                }
                $insert_stmt->close();
                $action_type = "paid";
            }
            
            $_SESSION['success'] = "Salary $action_type successfully! Net Amount: ₹" . number_format($net_salary, 2);
            
        } elseif ($action === 'cancel') {
            // Check if already cancelled
            if ($existing_record && $existing_record['status'] === 'cancelled') {
                $_SESSION['error'] = "Salary for $month_year is already cancelled!";
                header("Location: salary_management.php?id=$teacher_id&year=$selected_year");
                exit;
            }
            
            if ($existing_record) {
                // Update existing record to cancelled
                $update_sql = "UPDATE teacher_salary 
                              SET status = 'cancelled', paid_on = NOW()
                              WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                if (!$update_stmt) {
                    $_SESSION['error'] = "Database error!";
                    header("Location: salary_management.php?id=$teacher_id&year=$selected_year");
                    exit;
                }
                
                $update_stmt->bind_param("i", $existing_record['id']);
                if (!$update_stmt->execute()) {
                    $_SESSION['error'] = "Failed to cancel salary!";
                    header("Location: salary_management.php?id=$teacher_id&year=$selected_year");
                    exit;
                }
                $update_stmt->close();
                $action_type = "updated to cancelled";
            } else {
                // Insert new cancelled record
                $insert_sql = "INSERT INTO teacher_salary 
                              (teacher_id, month_year, basic, allowance, deduction, net_salary, status, paid_on) 
                              VALUES (?, ?, ?, ?, ?, ?, 'cancelled', NOW())";
                $insert_stmt = $conn->prepare($insert_sql);
                if (!$insert_stmt) {
                    $_SESSION['error'] = "Database error!";
                    header("Location: salary_management.php?id=$teacher_id&year=$selected_year");
                    exit;
                }
                
                $insert_stmt->bind_param("isdddd", $teacher_id, $month_year, $basic_salary, $allowance, $deduction, $net_salary);
                if (!$insert_stmt->execute()) {
                    $_SESSION['error'] = "Failed to insert cancelled salary record!";
                    header("Location: salary_management.php?id=$teacher_id&year=$selected_year");
                    exit;
                }
                $insert_stmt->close();
                $action_type = "marked as cancelled";
            }
            
            $_SESSION['success'] = "Salary $action_type for $month_year!";
        }
        
        // Log activity
        $log_sql = "INSERT INTO teacher_salary_record (user_id, action, details, ip_address, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
        $log_stmt = $conn->prepare($log_sql);
        if ($log_stmt) {
            $action_text = $action === 'pay' ? 'Salary Paid' : 'Salary Cancelled';
            $details = "Teacher: {$teacher['name']} (ID: $teacher_id) | Month: $month_year | Amount: ₹" . number_format($net_salary, 2);
            $log_stmt->bind_param("isss", $_SESSION['user_id'], $action_text, $details, $_SERVER['REMOTE_ADDR']);
            $log_stmt->execute();
            $log_stmt->close();
        }
        
        header("Location: salary_management.php?id=$teacher_id&year=$selected_year");
        exit;
    }
}

// Generate months for selected year
$months_list = [];
$month_names = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

// Determine start month based on join year
$start_month = ($selected_year == $join_year) ? (int)date('m', strtotime($teacher['join_date'])) : 1;
$end_month = ($selected_year == $current_year) ? (int)date('m') : 12;

// Generate months list
for ($month = $start_month; $month <= $end_month; $month++) {
    $month_year = sprintf('%02d-%d', $month, $selected_year);
    
    // Get existing salary record
    $salary_sql = "SELECT status, net_salary, paid_on, deduction, allowance 
                   FROM teacher_salary 
                   WHERE teacher_id = ? AND month_year = ?";
    $salary_stmt = $conn->prepare($salary_sql);
    if ($salary_stmt) {
        $salary_stmt->bind_param("is", $teacher_id, $month_year);
        if ($salary_stmt->execute()) {
            $salary_result = $salary_stmt->get_result();
            $salary_data = $salary_result->fetch_assoc();
        } else {
            $salary_data = null;
        }
        $salary_stmt->close();
    } else {
        $salary_data = null;
    }
    
    // Get approved leaves for this month
    $absents = 0;
    $office_leaves = 0;
    
    $leaves_sql = "SELECT 
                   SUM(CASE WHEN leave_type = 'absent' THEN 1 ELSE 0 END) as absents,
                   SUM(CASE WHEN leave_type = 'office_leave' THEN 1 ELSE 0 END) as office_leaves
                   FROM teacher_leaves 
                   WHERE teacher_id = ? 
                   AND MONTH(from_date) = ? 
                   AND YEAR(from_date) = ? 
                   AND status = 'approved'";
    
    $leaves_stmt = $conn->prepare($leaves_sql);
    if ($leaves_stmt) {
        $leaves_stmt->bind_param("iii", $teacher_id, $month, $selected_year);
        if ($leaves_stmt->execute()) {
            $leaves_result = $leaves_stmt->get_result();
            $leaves_data = $leaves_result->fetch_assoc();
            $absents = $leaves_data['absents'] ?? 0;
            $office_leaves = $leaves_data['office_leaves'] ?? 0;
        }
        $leaves_stmt->close();
    }
    
    // Calculate working days and deductions
    $working_days = 0;
    $total_days = cal_days_in_month(CAL_GREGORIAN, $month, $selected_year);
    for ($day = 1; $day <= $total_days; $day++) {
        $timestamp = mktime(0, 0, 0, $month, $day, $selected_year);
        if (date('N', $timestamp) <= 5) { // Monday to Friday
            $working_days++;
        }
    }
    
    $daily_salary = $teacher['salary'] / $working_days;
    $absent_deduction = $absents * $daily_salary;
    $office_leave_deduction = $office_leaves * ($daily_salary / 2);
    $calculated_deduction = $absent_deduction + $office_leave_deduction;
    
    // Default values if no salary record exists
    if ($salary_data) {
        $status = $salary_data['status'];
        $net_salary = $salary_data['net_salary'];
        $deduction_amount = $salary_data['deduction'];
        $allowance_amount = $salary_data['allowance'];
        $paid_on = $salary_data['paid_on'];
    } else {
        $status = 'pending';
        $deduction_amount = $calculated_deduction;
        $allowance_amount = 0;
        $net_salary = $teacher['salary'] + $allowance_amount - $deduction_amount;
        $paid_on = null;
    }
    
    $months_list[] = [
        'month_year' => $month_year,
        'display' => $month_names[$month] . ' ' . $selected_year,
        'month' => $month,
        'status' => $status,
        'net_salary' => $net_salary,
        'paid_on' => $paid_on,
        'deduction' => $deduction_amount,
        'allowance' => $allowance_amount,
        'absents' => $absents,
        'office_leaves' => $office_leaves,
        'working_days' => $working_days,
        'daily_salary' => $daily_salary
    ];
}

// Get summary for selected year
$summary_sql = "SELECT 
                COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count,
                SUM(CASE WHEN status = 'paid' THEN net_salary ELSE 0 END) as total_paid
                FROM teacher_salary 
                WHERE teacher_id = ? 
                AND YEAR(STR_TO_DATE(CONCAT('01-', month_year), '%d-%m-%Y')) = ?";
                
$summary_stmt = $conn->prepare($summary_sql);
if ($summary_stmt) {
    $summary_stmt->bind_param("ii", $teacher_id, $selected_year);
    if ($summary_stmt->execute()) {
        $summary_result = $summary_stmt->get_result();
        $summary = $summary_result->fetch_assoc();
    } else {
        $summary = [];
    }
    $summary_stmt->close();
} else {
    $summary = [];
}

// Initialize summary values
$summary = $summary ?: [];
$summary['paid_count'] = $summary['paid_count'] ?? 0;
$summary['cancelled_count'] = $summary['cancelled_count'] ?? 0;
$summary['pending_count'] = count($months_list) - $summary['paid_count'] - $summary['cancelled_count'];
$summary['total_paid'] = $summary['total_paid'] ?? 0;

// Get all payment history
$history_sql = "SELECT * FROM teacher_salary 
                WHERE teacher_id = ? 
                ORDER BY month_year DESC";
$history_stmt = $conn->prepare($history_sql);
if ($history_stmt) {
    $history_stmt->bind_param("i", $teacher_id);
    $history_stmt->execute();
    $history_result = $history_stmt->get_result();
} else {
    $history_result = false;
}

// Generate years list for dropdown
$years_list = [];
for ($year = $join_year; $year <= $current_year; $year++) {
    $years_list[] = $year;
}
rsort($years_list); // Show recent years first
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Salary Management - School India Junior</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .teacher-header {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid #4e73df;
            margin-bottom: 30px;
        }
        .month-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: white;
            transition: all 0.2s ease;
        }
        .month-card:hover {
            border-color: #c3cfe2;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-paid {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .badge-pending {
            background-color: #fff3e0;
            color: #ef6c00;
            border: 1px solid #ffcc80;
        }
        .badge-cancelled {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .salary-amount {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2d3748;
        }
        .btn-action {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 6px;
        }
        .leave-info {
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 0.85rem;
            margin-top: 8px;
        }
        .year-badge {
            background-color: #e3f2fd;
            color: #1565c0;
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: 600;
        }
        .summary-box {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .summary-box h5 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .summary-box small {
            color: #718096;
            font-size: 0.85rem;
        }
    </style>
</head>

<body>
    <?php include "../includes/preloader.php" ?>
    <div id="main-wrapper">
        <?php include "../includes/navbar.php" ?>
        <?php include('../includes/sidebar_logic.php') ?>

        <div class="content-body">
            <div class="container-fluid">

                <!-- Breadcrumb + Title -->
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <?php include('../includes/welcome_text.php'); ?>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="teacher_list.php">Teachers</a></li>
                            <li class="breadcrumb-item"><a href="teacher_view.php?id=<?php echo $teacher_id; ?>"><?php echo htmlspecialchars($teacher['name']); ?></a></li>
                            <li class="breadcrumb-item active">Salary Management</li>
                        </ol>
                    </div>
                </div>

                <!-- Flash Messages -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Teacher Information -->
                <div class="row">
                    <div class="col-12">
                        <div class="teacher-header">
                            <div class="row align-items-center">
                                <div class="col-md-2 col-lg-1 text-center">
                                    <?php 
                                    $photo_path = !empty($teacher['photo']) ? '../' . $teacher['photo'] : '../public/images/default-teacher.jpg';
                                    ?>
                                    <img src="<?php echo $photo_path; ?>"
                                         class="rounded-circle border border-3 border-white"
                                         style="width: 70px; height: 70px; object-fit: cover;"
                                         alt="<?php echo htmlspecialchars($teacher['name']); ?>">
                                </div>
                                <div class="col-md-10 col-lg-11">
                                    <h5 class="mb-2"><?php echo htmlspecialchars($teacher['name']); ?></h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <small class="text-muted"><i class="fas fa-id-badge me-1"></i> <?php echo htmlspecialchars($teacher['teacher_code']); ?></small>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted"><i class="fas fa-book me-1"></i> <?php echo htmlspecialchars($teacher['subject']); ?></small>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted"><i class="fas fa-calendar-day me-1"></i> Joined: <?php echo date('d M Y', strtotime($teacher['join_date'])); ?></small>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted"><i class="fas fa-money-bill me-1"></i> Basic Salary: ₹<?php echo number_format($teacher['salary'], 2); ?>/month</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Year Selection and Summary -->
                <div class="row mb-4">
                    <div class="col-xl-8 col-lg-7">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0"><i class="fas fa-calendar-alt me-2"></i>Select Year</h4>
                                <span class="year-badge"><?php echo $selected_year; ?></span>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($years_list as $year): ?>
                                        <a href="salary_management.php?id=<?php echo $teacher_id; ?>&year=<?php echo $year; ?>"
                                           class="btn btn-sm <?php echo $selected_year == $year ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                            <?php echo $year; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Showing <?php echo count($months_list); ?> months for <?php echo $selected_year; ?>
                                        <?php if ($selected_year == $join_year): ?>
                                            (Teacher joined in <?php echo date('F Y', strtotime($teacher['join_date'])); ?>)
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Year Summary</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4">
                                        <div class="summary-box">
                                            <h5 class="text-primary"><?php echo count($months_list); ?></h5>
                                            <small>Months</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="summary-box">
                                            <h5 class="text-success"><?php echo $summary['paid_count']; ?></h5>
                                            <small>Paid</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="summary-box">
                                            <h5 class="text-warning"><?php echo $summary['pending_count']; ?></h5>
                                            <small>Pending</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-rupee-sign me-1"></i>
                                        Total Paid: ₹<?php echo number_format($summary['total_paid'], 2); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Salary Months Grid -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">
                                    <i class="fas fa-calendar-check me-2"></i>Salary Months - <?php echo $selected_year; ?>
                                    <span class="badge bg-secondary ms-2"><?php echo count($months_list); ?></span>
                                </h4>
                                <div>
                                    <small class="text-muted me-3">
                                        <span class="status-badge badge-paid me-2">Paid</span>
                                        <span class="status-badge badge-pending me-2">Pending</span>
                                        <span class="status-badge badge-cancelled">Cancelled</span>
                                    </small>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (empty($months_list)): ?>
                                    <div class="alert alert-info text-center">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No salary months available for <?php echo $selected_year; ?>.
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($months_list as $month): 
                                            $status = $month['status'];
                                            $status_text = ucfirst($status);
                                            $badge_class = "badge-" . $status;
                                        ?>
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="month-card">
                                                    <!-- Month Header -->
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div>
                                                            <h6 class="mb-1">
                                                                <i class="fas fa-calendar-alt text-primary me-2"></i>
                                                                <?php echo $month['display']; ?>
                                                            </h6>
                                                            <small class="text-muted">Working Days: <?php echo $month['working_days']; ?></small>
                                                        </div>
                                                        <span class="status-badge <?php echo $badge_class; ?>">
                                                            <?php echo $status_text; ?>
                                                        </span>
                                                    </div>
                                                    
                                                    <!-- Salary Amount -->
                                                    <div class="salary-amount mb-3">
                                                        ₹<?php echo number_format($month['net_salary'], 2); ?>
                                                        <?php if ($month['deduction'] > 0): ?>
                                                            <small class="text-danger d-block mt-1">
                                                                <i class="fas fa-minus-circle me-1"></i>
                                                                Deductions: ₹<?php echo number_format($month['deduction'], 2); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                        <?php if ($month['allowance'] > 0): ?>
                                                            <small class="text-success d-block mt-1">
                                                                <i class="fas fa-plus-circle me-1"></i>
                                                                Allowance: ₹<?php echo number_format($month['allowance'], 2); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <!-- Leave Information -->
                                                    <?php if ($month['absents'] > 0 || $month['office_leaves'] > 0): ?>
                                                        <div class="leave-info">
                                                            <div class="d-flex justify-content-between">
                                                                <?php if ($month['absents'] > 0): ?>
                                                                    <small class="text-danger">
                                                                        <i class="fas fa-user-times me-1"></i>
                                                                        <?php echo $month['absents']; ?> absent
                                                                    </small>
                                                                <?php endif; ?>
                                                                <?php if ($month['office_leaves'] > 0): ?>
                                                                    <small class="text-info">
                                                                        <i class="fas fa-briefcase me-1"></i>
                                                                        <?php echo $month['office_leaves']; ?> office leave
                                                                    </small>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Status Details and Actions -->
                                                    <div class="mt-3">
                                                        <?php if ($status === 'paid'): ?>
                                                            <small class="text-muted d-block mb-2">
                                                                <i class="fas fa-check-circle text-success me-1"></i>
                                                                Paid on <?php echo date('d M Y', strtotime($month['paid_on'])); ?>
                                                            </small>
                                                            <button class="btn btn-outline-danger btn-sm btn-action w-100"
                                                                    onclick="openSalaryModal('<?php echo $month['month_year']; ?>', 'cancel', <?php echo $month['net_salary']; ?>)">
                                                                <i class="fas fa-times me-1"></i>Cancel Payment
                                                            </button>
                                                            
                                                        <?php elseif ($status === 'cancelled'): ?>
                                                            <small class="text-danger d-block mb-2">
                                                                <i class="fas fa-times-circle me-1"></i>
                                                                Cancelled on <?php echo date('d M Y', strtotime($month['paid_on'])); ?>
                                                            </small>
                                                            <button class="btn btn-outline-success btn-sm btn-action w-100"
                                                                    onclick="openSalaryModal('<?php echo $month['month_year']; ?>', 'pay', <?php echo $month['net_salary']; ?>)">
                                                                <i class="fas fa-check me-1"></i>Mark as Paid
                                                            </button>
                                                            
                                                        <?php else: ?>
                                                            <div class="d-grid gap-2">
                                                                <button class="btn btn-success btn-sm btn-action"
                                                                        onclick="openSalaryModal('<?php echo $month['month_year']; ?>', 'pay', <?php echo $month['net_salary']; ?>)">
                                                                    <i class="fas fa-check-circle me-1"></i>Pay Salary
                                                                </button>
                                                                <button class="btn btn-outline-danger btn-sm btn-action"
                                                                        onclick="openSalaryModal('<?php echo $month['month_year']; ?>', 'cancel', <?php echo $month['net_salary']; ?>)">
                                                                    <i class="fas fa-times me-1"></i>Mark as Cancelled
                                                                </button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0"><i class="fas fa-history me-2"></i>Payment History</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped verticle-middle text-dark">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>Basic</th>
                                                <th>Allowance</th>
                                                <th>Deduction</th>
                                                <th>Net Salary</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($history_result && $history_result->num_rows > 0): ?>
                                                <?php while ($history = $history_result->fetch_assoc()): 
                                                    $status_badge = $history['status'] === 'paid' ? 'badge-paid' : 'badge-cancelled';
                                                    $month_parts = explode('-', $history['month_year']);
                                                    $month_name = $month_names[(int)$month_parts[0]];
                                                ?>
                                                    <tr>
                                                        <td><strong><?php echo $month_name . ' ' . $month_parts[1]; ?></strong></td>
                                                        <td>₹<?php echo number_format($history['basic'], 2); ?></td>
                                                        <td>₹<?php echo number_format($history['allowance'], 2); ?></td>
                                                        <td class="text-danger">-₹<?php echo number_format($history['deduction'], 2); ?></td>
                                                        <td><strong>₹<?php echo number_format($history['net_salary'], 2); ?></strong></td>
                                                        <td><span class="status-badge <?php echo $status_badge; ?>"><?php echo ucfirst($history['status']); ?></span></td>
                                                        <td><?php echo date('d M Y', strtotime($history['paid_on'])); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="fas fa-history fa-3x mb-3"></i>
                                                            <p class="mb-0">No payment history found</p>
                                                        </div>
                                                    </td>
                                                </tr>
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

    <!-- Salary Processing Modal -->
    <div class="modal fade" id="salaryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="salaryForm" method="POST">
                    <input type="hidden" name="process_salary" value="1">
                    <input type="hidden" id="modalMonthYear" name="month_year">
                    <input type="hidden" id="modalAction" name="action">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Process Salary</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div id="monthInfo" class="alert alert-info mb-4">
                            <!-- Month info will be inserted here -->
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Basic Salary</label>
                                <input type="text" class="form-control" value="₹<?php echo number_format($teacher['salary'], 2); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Net Salary</label>
                                <input type="text" class="form-control" id="netSalaryDisplay" readonly>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Allowance (₹)</label>
                                <input type="number" class="form-control" id="allowance" name="allowance" value="0" min="0" step="0.01" oninput="calculateNetSalary()">
                                <small class="text-muted">Additional amount to add to salary</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Deduction (₹)</label>
                                <input type="number" class="form-control" id="deduction" name="deduction" value="0" min="0" step="0.01" oninput="calculateNetSalary()">
                                <small class="text-muted">Amount to deduct from salary</small>
                            </div>
                            
                            <div class="col-12">
                                <div id="confirmationAlert" class="alert alert-warning">
                                    <!-- Confirmation message will be inserted here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn" id="submitBtn">Process</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include "../includes/js_links.php" ?>
    <script>
        const basicSalary = <?php echo $teacher['salary']; ?>;
        let currentNetSalary = 0;
        
        function openSalaryModal(monthYear, action, netSalary) {
            currentNetSalary = netSalary;
            $('#salaryModal').modal('show');
            
            const [month, year] = monthYear.split('-');
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                              'July', 'August', 'September', 'October', 'November', 'December'];
            const monthName = monthNames[parseInt(month) - 1];
            
            // Set form values
            $('#modalMonthYear').val(monthYear);
            $('#modalAction').val(action);
            
            // Update UI based on action
            if (action === 'pay') {
                $('#modalTitle').text(`Pay Salary - ${monthName} ${year}`);
                $('#submitBtn').removeClass('btn-danger').addClass('btn-success');
                $('#submitBtn').html('<i class="fas fa-check-circle me-1"></i> Pay Salary');
                $('#confirmationAlert').html(`
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirm payment of salary for <strong>${monthName} ${year}</strong>
                `);
            } else {
                $('#modalTitle').text(`Cancel Salary - ${monthName} ${year}`);
                $('#submitBtn').removeClass('btn-success').addClass('btn-danger');
                $('#submitBtn').html('<i class="fas fa-times me-1"></i> Cancel Salary');
                $('#confirmationAlert').html(`
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirm cancellation of salary for <strong>${monthName} ${year}</strong>
                `);
            }
            
            // Update month info
            $('#monthInfo').html(`
                <i class="fas fa-calendar-alt me-2"></i>
                Processing salary for <strong>${monthName} ${year}</strong>
                | Teacher: <strong><?php echo htmlspecialchars($teacher['name']); ?></strong>
            `);
            
            // Reset and set values
            $('#allowance').val(0);
            $('#deduction').val(0);
            updateNetSalaryDisplay(currentNetSalary);
        }
        
        function calculateNetSalary() {
            const allowance = parseFloat($('#allowance').val()) || 0;
            const deduction = parseFloat($('#deduction').val()) || 0;
            const netSalary = basicSalary + allowance - deduction;
            updateNetSalaryDisplay(netSalary);
        }
        
        function updateNetSalaryDisplay(netSalary) {
            $('#netSalaryDisplay').val('₹' + netSalary.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        }
        
        // Form validation
        $('#salaryForm').on('submit', function(e) {
            const allowance = parseFloat($('#allowance').val()) || 0;
            const deduction = parseFloat($('#deduction').val()) || 0;
            
            if (deduction > basicSalary + allowance) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Deduction',
                    text: 'Deduction cannot be greater than total salary!',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }
            
            if (allowance < 0 || deduction < 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Value',
                    text: 'Values cannot be negative!',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }
            
            const action = $('#modalAction').val();
            const actionText = action === 'pay' ? 'pay' : 'cancel';
            
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to ${actionText} this salary?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: action === 'pay' ? '#28a745' : '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, ${actionText} it!`,
                cancelButtonText: 'No, cancel!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
        
        // Prevent form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>