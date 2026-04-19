<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
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

// Process attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_absent'])) {
        $teacher_id = (int)$_POST['teacher_id'];
        $absent_date = $_POST['absent_date'];
        $reason = trim($_POST['reason'] ?? '');
        
        // Validate date
        if (strtotime($absent_date) > strtotime('today')) {
            $_SESSION['error'] = "Cannot mark absence for future dates!";
            header("Location: leave_manage.php");
            exit;
        }
        
        // Check if attendance already exists for this date
        $check_sql = "SELECT id FROM teacher_attendance WHERE teacher_id = ? AND date = ?";
        $check_stmt = $conn->prepare($check_sql);
        if ($check_stmt) {
            $check_stmt->bind_param("is", $teacher_id, $absent_date);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                // Update existing record
                $update_sql = "UPDATE teacher_attendance SET status = 'absent', method = 'manual' WHERE teacher_id = ? AND date = ?";
                $update_stmt = $conn->prepare($update_sql);
                if ($update_stmt) {
                    $update_stmt->bind_param("is", $teacher_id, $absent_date);
                    if ($update_stmt->execute()) {
                        // Record leave for salary calculation
                        recordTeacherLeave($conn, $teacher_id, $absent_date, $reason);
                        $_SESSION['success'] = "Attendance updated to absent for selected date!";
                    }
                    $update_stmt->close();
                }
            } else {
                // Insert new absent record
                $insert_sql = "INSERT INTO teacher_attendance (teacher_id, date, status, method, created_at) 
                              VALUES (?, ?, 'absent', 'manual', NOW())";
                $insert_stmt = $conn->prepare($insert_sql);
                if ($insert_stmt) {
                    $insert_stmt->bind_param("is", $teacher_id, $absent_date);
                    if ($insert_stmt->execute()) {
                        // Record leave for salary calculation
                        recordTeacherLeave($conn, $teacher_id, $absent_date, $reason);
                        $_SESSION['success'] = "Teacher marked as absent for selected date!";
                    }
                    $insert_stmt->close();
                }
            }
            $check_stmt->close();
        }
        
        header("Location: leave_manage.php");
        exit;
        
    } elseif (isset($_POST['mark_present'])) {
        $teacher_id = (int)$_POST['teacher_id'];
        $absent_date = $_POST['absent_date'];
        
        // Update attendance to present
        $update_sql = "UPDATE teacher_attendance SET status = 'present', method = 'manual' WHERE teacher_id = ? AND date = ?";
        $update_stmt = $conn->prepare($update_sql);
        if ($update_stmt) {
            $update_stmt->bind_param("is", $teacher_id, $absent_date);
            if ($update_stmt->execute()) {
                // Remove corresponding leave record if exists
                removeTeacherLeave($conn, $teacher_id, $absent_date);
                $_SESSION['success'] = "Attendance updated to present for selected date!";
            }
            $update_stmt->close();
        }
        
       header("Location: leave_manage.php");
        exit;
    }
}

// Function to record teacher leave for salary calculation
function recordTeacherLeave($conn, $teacher_id, $date, $reason = '') {
    // Check if leave already exists for this date
    $check_sql = "SELECT id FROM teacher_leaves WHERE teacher_id = ? AND from_date = ? AND to_date = ?";
    $check_stmt = $conn->prepare($check_sql);
    if ($check_stmt) {
        $check_stmt->bind_param("iss", $teacher_id, $date, $date);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            // Insert new leave record
            $insert_sql = "INSERT INTO teacher_leaves (teacher_id, leave_type, from_date, to_date, reason, status, created_at) 
                          VALUES (?, 'absent', ?, ?, ?, 'approved', NOW())";
            $insert_stmt = $conn->prepare($insert_sql);
            if ($insert_stmt) {
                $insert_stmt->bind_param("isss", $teacher_id, $date, $date, $reason);
                $insert_stmt->execute();
                $insert_stmt->close();
            }
        }
        $check_stmt->close();
    }
}

// Function to remove teacher leave record
function removeTeacherLeave($conn, $teacher_id, $date) {
    $delete_sql = "DELETE FROM teacher_leaves WHERE teacher_id = ? AND from_date = ? AND to_date = ? AND leave_type = 'absent'";
    $delete_stmt = $conn->prepare($delete_sql);
    if ($delete_stmt) {
        $delete_stmt->bind_param("iss", $teacher_id, $date, $date);
        $delete_stmt->execute();
        $delete_stmt->close();
    }
}

// Get all teachers
$teachers_list = [];
$teachers_sql = "SELECT id, name, teacher_code, subject FROM teachers WHERE status = 1 ORDER BY name";
$teachers_result = $conn->query($teachers_sql);
if ($teachers_result) {
    while ($row = $teachers_result->fetch_assoc()) {
        $teachers_list[] = $row;
    }
}

// Get selected teacher and date for filtering
$selected_teacher_id = isset($_GET['teacher_id']) ? (int)$_GET['teacher_id'] : 0;
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Get attendance records
$attendance = [];
$where_clause = "WHERE 1=1";
$params = [];
$param_types = "";

if ($selected_teacher_id > 0) {
    $where_clause .= " AND ta.teacher_id = ?";
    $params[] = $selected_teacher_id;
    $param_types .= "i";
}

if (!empty($selected_date)) {
    $where_clause .= " AND ta.date = ?";
    $params[] = $selected_date;
    $param_types .= "s";
}

$attendance_sql = "SELECT ta.*, 
                   t.name as teacher_name, 
                   t.teacher_code, 
                   t.subject
                   FROM teacher_attendance ta
                   JOIN teachers t ON ta.teacher_id = t.id
                   $where_clause
                   ORDER BY ta.date DESC, t.name";
                   
$attendance_stmt = $conn->prepare($attendance_sql);
if ($attendance_stmt) {
    if ($params) {
        $attendance_stmt->bind_param($param_types, ...$params);
    }
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();
    while ($row = $attendance_result->fetch_assoc()) {
        $attendance[] = $row;
    }
    $attendance_stmt->close();
}

// Get attendance statistics
$stats_sql = "SELECT 
              COUNT(*) as total_records,
              SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
              SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count
              FROM teacher_attendance";
              
if ($selected_teacher_id > 0) {
    $stats_sql .= " WHERE teacher_id = ?";
    $stats_stmt = $conn->prepare($stats_sql);
    if ($stats_stmt) {
        $stats_stmt->bind_param("i", $selected_teacher_id);
        $stats_stmt->execute();
        $stats_result = $stats_stmt->get_result();
        $stats = $stats_result->fetch_assoc();
        $stats_stmt->close();
    }
} else {
    $stats_result = $conn->query($stats_sql);
    if ($stats_result) {
        $stats = $stats_result->fetch_assoc();
    }
}

$stats = $stats ?? [
    'total_records' => 0,
    'present_count' => 0,
    'absent_count' => 0
];

// Get today's attendance summary
$today = date('Y-m-d');
$today_sql = "SELECT 
              COUNT(*) as total_teachers,
              SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_today,
              SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_today
              FROM teacher_attendance 
              WHERE date = ?";
$today_stmt = $conn->prepare($today_sql);
if ($today_stmt) {
    $today_stmt->bind_param("s", $today);
    $today_stmt->execute();
    $today_result = $today_stmt->get_result();
    $today_stats = $today_result->fetch_assoc();
    $today_stmt->close();
}

$today_stats = $today_stats ?? [
    'total_teachers' => 0,
    'present_today' => 0,
    'absent_today' => 0
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Attendance Management - School India Junior</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .attendance-card {
            border-left: 4px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .attendance-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .attendance-card.present {
            border-left-color: #28a745;
        }
        .attendance-card.absent {
            border-left-color: #dc3545;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-present {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .badge-absent {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .teacher-select-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 10px;
            padding: 20px;
        }
        .stat-box {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            transition: transform 0.2s;
        }
        .stat-box:hover {
            transform: translateY(-3px);
        }
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .action-btn {
            padding: 5px 12px;
            font-size: 0.85rem;
            border-radius: 6px;
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .teacher-photo {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .date-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-block;
        }
        .method-badge {
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 10px;
            background: #e9ecef;
            color: #495057;
        }
        .sms-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            color: white;
            border: none;
        }
        .sms-btn:hover {
            background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
            color: white;
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
                            <li class="breadcrumb-item active">Attendance Management</li>
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

                <!-- Page Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h3 class="mb-1"><i class="fas fa-calendar-check text-primary me-2"></i>Attendance Management</h3>
                                        <p class="text-muted mb-0">Mark and manage teacher attendance</p>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <button class="btn btn-primary" data-toggle="modal" data-target="#markAbsentModal">
                                            <i class="fas fa-user-times me-1"></i> Mark Absent
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Statistics -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="stat-box">
                            <div class="stat-icon text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="text-primary"><?php echo count($teachers_list); ?></h3>
                            <p class="text-muted mb-0">Total Teachers</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="stat-box">
                            <div class="stat-icon text-success">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <h3 class="text-success"><?php echo $today_stats['present_today']; ?></h3>
                            <p class="text-muted mb-0">Present Today</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="stat-box">
                            <div class="stat-icon text-danger">
                                <i class="fas fa-user-times"></i>
                            </div>
                            <h3 class="text-danger"><?php echo $today_stats['absent_today']; ?></h3>
                            <p class="text-muted mb-0">Absent Today</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="stat-box">
                            <div class="stat-icon text-info">
                                <i class="fas fa-database"></i>
                            </div>
                            <h3 class="text-info"><?php echo $stats['total_records']; ?></h3>
                            <p class="text-muted mb-0">Total Records</p>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="filter-card">
                            <form method="GET" class="row">
                                <div class="col-md-4 mb-3">
                                    <label>Select Teacher</label>
                                    <select name="teacher_id" class="form-control" onchange="this.form.submit()">
                                        <option value="">-- All Teachers --</option>
                                        <?php foreach ($teachers_list as $teacher): ?>
                                        <option value="<?php echo $teacher['id']; ?>" <?php echo $selected_teacher_id == $teacher['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($teacher['name']); ?> (<?php echo htmlspecialchars($teacher['teacher_code']); ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Select Date</label>
                                    <input type="date" name="date" class="form-control" value="<?php echo $selected_date; ?>" onchange="this.form.submit()">
                                </div>
                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                    <?php if ($selected_teacher_id || $selected_date != date('Y-m-d')): ?>
                                    <a href="leave_manage.php" class="btn btn-outline-secondary w-100">Clear Filters</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Date Display -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="date-display">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Showing attendance for: 
                            <strong><?php echo date('d M Y', strtotime($selected_date)); ?></strong>
                            <?php if ($selected_teacher_id && isset($teachers_list[$selected_teacher_id-1])): ?>
                            | Teacher: <strong><?php echo htmlspecialchars($teachers_list[$selected_teacher_id-1]['name']); ?></strong>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Attendance Records -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <i class="fas fa-list me-2"></i>
                                    Attendance Records
                                    <span class="badge bg-secondary ms-2"><?php echo count($attendance); ?></span>
                                </h4>
                            </div>
                            <div class="card-body">
                                <?php if (empty($attendance)): ?>
                                    <div class="alert alert-info text-center py-5">
                                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                        <h5 class="mb-2">No Attendance Records Found</h5>
                                        <p class="text-muted">
                                            No attendance records found for the selected criteria.
                                            <?php if (!$selected_teacher_id && $selected_date == date('Y-m-d')): ?>
                                                <br>Try selecting a different date or teacher.
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($attendance as $record): 
                                        $status_class = 'badge-' . $record['status'];
                                        $card_class = $record['status'];
                                    ?>
                                        <div class="attendance-card card mb-3 <?php echo $card_class; ?>">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <!-- Teacher Info -->
                                                    <div class="col-md-2 text-center">
                                                        <?php 
                                                        // Get teacher photo if available
                                                        $teacher_photo_sql = "SELECT photo FROM teachers WHERE id = ?";
                                                        $photo_stmt = $conn->prepare($teacher_photo_sql);
                                                        $photo_path = '../public/images/default-teacher.jpg';
                                                        if ($photo_stmt) {
                                                            $photo_stmt->bind_param("i", $record['teacher_id']);
                                                            $photo_stmt->execute();
                                                            $photo_result = $photo_stmt->get_result();
                                                            if ($photo_row = $photo_result->fetch_assoc()) {
                                                                if (!empty($photo_row['photo'])) {
                                                                    $photo_path = '../' . $photo_row['photo'];
                                                                }
                                                            }
                                                            $photo_stmt->close();
                                                        }
                                                        ?>
                                                        <img src="<?php echo $photo_path; ?>" class="teacher-photo" alt="<?php echo htmlspecialchars($record['teacher_name']); ?>">
                                                    </div>
                                                    
                                                    <!-- Attendance Details -->
                                                    <div class="col-md-6">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($record['teacher_name']); ?></h6>
                                                        <div class="d-flex align-items-center mb-2">
                                                            <small class="text-muted me-3">
                                                                <i class="fas fa-id-badge me-1"></i> <?php echo htmlspecialchars($record['teacher_code']); ?>
                                                            </small>
                                                            <small class="text-muted">
                                                                <i class="fas fa-book me-1"></i> <?php echo htmlspecialchars($record['subject']); ?>
                                                            </small>
                                                        </div>
                                                        
                                                        <div class="d-flex align-items-center">
                                                            <span class="status-badge <?php echo $status_class; ?> me-2">
                                                                <?php echo ucfirst($record['status']); ?>
                                                            </span>
                                                            <span class="method-badge">
                                                                <i class="fas fa-<?php echo $record['method'] == 'manual' ? 'hand-paper' : 'mobile-alt'; ?> me-1"></i>
                                                                <?php echo ucfirst($record['method']); ?>
                                                            </span>
                                                        </div>
                                                        
                                                        <?php if ($record['checkin_time']): ?>
                                                        <small class="text-muted d-block mt-1">
                                                            <i class="fas fa-sign-in-alt me-1"></i>
                                                            Check-in: <?php echo date('h:i A', strtotime($record['checkin_time'])); ?>
                                                        </small>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($record['checkout_time']): ?>
                                                        <small class="text-muted d-block">
                                                            <i class="fas fa-sign-out-alt me-1"></i>
                                                            Check-out: <?php echo date('h:i A', strtotime($record['checkout_time'])); ?>
                                                        </small>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <!-- Date and Actions -->
                                                    <div class="col-md-4 text-right">
                                                        <div class="mb-3">
                                                            <h5 class="text-primary">
                                                                <i class="fas fa-calendar-day me-1"></i>
                                                                <?php echo date('d M Y', strtotime($record['date'])); ?>
                                                            </h5>
                                                            <small class="text-muted">
                                                                Recorded: <?php echo date('h:i A', strtotime($record['created_at'])); ?>
                                                            </small>
                                                        </div>
                                                        
                                                        <div class="btn-group">
                                                            <?php if ($record['status'] === 'present'): ?>
                                                                <button class="btn btn-danger btn-sm action-btn" onclick="markAttendance(<?php echo $record['teacher_id']; ?>, '<?php echo $record['date']; ?>', 'absent')">
                                                                    <i class="fas fa-user-times me-1"></i> Mark Absent
                                                                </button>
                                                            <?php else: ?>
                                                                <button class="btn btn-success btn-sm action-btn" onclick="markAttendance(<?php echo $record['teacher_id']; ?>, '<?php echo $record['date']; ?>', 'present')">
                                                                    <i class="fas fa-user-check me-1"></i> Mark Present
                                                                </button>
                                                            <?php endif; ?>
                                                            <button class="btn btn-sm sms-btn action-btn" onclick="sendSMS(<?php echo $record['teacher_id']; ?>, '<?php echo $record['date']; ?>', '<?php echo $record['status']; ?>')">
                                                                <i class="fas fa-sms me-1"></i> SMS
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php include "../includes/footer.php" ?>
    </div>

    <!-- Mark Absent Modal -->
    <div class="modal fade" id="markAbsentModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" id="markAbsentForm">
                    <input type="hidden" name="mark_absent" value="1">
                    
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-times me-2"></i>Mark Teacher as Absent</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Mark a teacher as absent for a specific date. This will also create a leave record for salary calculation.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Select Teacher <span class="text-danger">*</span></label>
                                <select name="teacher_id" class="form-control" required>
                                    <option value="">-- Select Teacher --</option>
                                    <?php foreach ($teachers_list as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>">
                                        <?php echo htmlspecialchars($teacher['name']); ?> (<?php echo htmlspecialchars($teacher['teacher_code']); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label>Absent Date <span class="text-danger">*</span></label>
                                <input type="date" name="absent_date" class="form-control" id="absentDate" required max="<?php echo date('Y-m-d'); ?>">
                                <small class="text-muted">Cannot select future dates</small>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label>Reason (Optional)</label>
                                <textarea name="reason" class="form-control" rows="2" placeholder="Enter reason for absence..."></textarea>
                            </div>
                            
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    This action will mark the teacher as absent and create a corresponding leave record for salary calculation.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Mark as Absent</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include "../includes/js_links.php" ?>
    <script>
        // Set max date to today for absent date
        document.getElementById('absentDate').max = new Date().toISOString().split('T')[0];
        
        // Mark attendance function
        function markAttendance(teacherId, date, status) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const action = status === 'absent' ? 'mark_absent' : 'mark_present';
            
            const teacherIdInput = document.createElement('input');
            teacherIdInput.type = 'hidden';
            teacherIdInput.name = 'teacher_id';
            teacherIdInput.value = teacherId;
            
            const dateInput = document.createElement('input');
            dateInput.type = 'hidden';
            dateInput.name = 'absent_date';
            dateInput.value = date;
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = action;
            actionInput.value = '1';
            
            form.appendChild(teacherIdInput);
            form.appendChild(dateInput);
            form.appendChild(actionInput);
            document.body.appendChild(form);
            
            const actionText = status === 'absent' ? 'absent' : 'present';
            
            Swal.fire({
                title: `Mark as ${actionText}?`,
                text: `Are you sure you want to mark this teacher as ${actionText} for ${date}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: status === 'absent' ? '#dc3545' : '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, mark as ${actionText}!`,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                } else {
                    document.body.removeChild(form);
                }
            });
        }
        
        // Send SMS function (placeholder)
        function sendSMS(teacherId, date, status) {
            Swal.fire({
                title: 'Send SMS Notification',
                text: 'This feature will send an SMS notification about attendance status. Feature under development.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#4CAF50',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Simulate SMS',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Simulate SMS sending
                    Swal.fire({
                        title: 'SMS Sent!',
                        text: 'SMS notification has been sent successfully.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }
        
        // Form validation for mark absent
        $('#markAbsentForm').on('submit', function(e) {
            const absentDate = $('#absentDate').val();
            const today = new Date().toISOString().split('T')[0];
            
            if (absentDate > today) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date',
                    text: 'Cannot select future dates for absence!',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }
            
            e.preventDefault();
            Swal.fire({
                title: 'Confirm Absence',
                text: 'Are you sure you want to mark this teacher as absent? This will affect their salary calculation.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, mark as absent!',
                cancelButtonText: 'Cancel'
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