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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>School India Junior</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<?php include('../config/database.php'); ?>

<body class="bg-light text-dark">
    <?php include "../includes/preloader.php"; ?>
    <div id="main-wrapper">
        <?php include "../includes/navbar.php"; ?>
        <?php include('../includes/sidebar_logic.php'); ?>

        <div class="content-body">
            <div class="container-fluid py-4">
                <div class="row page-titles mx-0 mb-3">
                    <div class="col-sm-6 p-md-0">
                        <?php include('../includes/welcome_text.php'); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-lg border-0 rounded-3">
                            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center flex-wrap">
                                <h4 class="card-title mb-2 mb-sm-0 fw-bold text-dark">
                                    <i class="fa fa-calendar-check me-2 text-primary"></i> Attendance Report
                                </h4>
                                <a href="attendance.php" class="btn btn-primary btn-sm shadow-sm">
                                    <i class="fa fa-camera me-1"></i> Take Live Attendance
                                </a>
                            </div>

                            <div class="card-body">
                                <?php
                                $selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
                                $selectedClass = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
                                $classQuery = $conn->query("SELECT id, class_name FROM classes ORDER BY class_name ASC");
                                ?>

                                <form method="GET" class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-dark">Select Date</label>
                                        <input type="date" name="date" class="form-control border-secondary text-dark fw-medium" value="<?= htmlspecialchars($selectedDate) ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-dark">Select Class</label>
                                        <select name="class_id" class="form-select border-secondary text-dark fw-medium">
                                            <option value="">-- All Classes --</option>
                                            <?php while ($c = $classQuery->fetch_assoc()): ?>
                                                <option value="<?= $c['id'] ?>" <?= ($selectedClass === (int)$c['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($c['class_name']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-success w-100 fw-semibold shadow-sm">
                                            <i class="fa fa-filter me-1"></i> Filter
                                        </button>
                                    </div>
                                </form>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle text-center shadow-sm">
    <thead class="bg-light text-dark fw-semibold">
        <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Class</th>
            <th>Roll</th>
            <th>Phone</th>
            <th>In Time</th>
            <th>Out Time</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // WHERE condition
        $where = "WHERE DATE(a.att_time) = '" . $conn->real_escape_string($selectedDate) . "'";
        if ($selectedClass > 0) {
            $where .= " AND s.class_id = " . (int)$selectedClass;
        }

        // QUERY
         $query = "
    SELECT 
        s.first_name,
        s.last_name,
        s.roll_number,
        s.parent_phone,
        c.class_name,
        a.att_time,
        ao.out_time,
        a.status
    FROM attendance a
    INNER JOIN students s ON a.student_id = s.id
    LEFT JOIN classes c ON s.class_id = c.id
    LEFT JOIN attendance_out ao 
        ON ao.student_id = a.student_id 
        AND ao.att_date = DATE(a.att_time)
    $where
    ORDER BY 
        c.class_name ASC,
        CAST(s.roll_number AS UNSIGNED) ASC
";


        $res = $conn->query($query);

        if ($res && $res->num_rows > 0) {
            $i = 1;
            while ($row = $res->fetch_assoc()) {

                $fullName     = htmlspecialchars(trim($row['first_name'] . ' ' . $row['last_name']));
                $rollNumber   = htmlspecialchars($row['roll_number']);
                $parentPhone  = htmlspecialchars($row['parent_phone']);
                $className    = htmlspecialchars($row['class_name']);

                $inTime  = date('h:i A', strtotime($row['att_time']));
                $outTime = !empty($row['out_time'])
                    ? date('h:i A', strtotime($row['out_time']))
                    : "<span class='text-danger fw-semibold'>Not Yet</span>";

                $status = htmlspecialchars($row['status']);
                $badgeClass = ($status === "Late")
                    ? "bg-warning text-dark"
                    : "bg-success text-white";

                echo "
                    <tr>
                        <td>{$i}</td>
                        <td class='text-capitalize fw-medium'>{$fullName}</td>
                        <td>{$className}</td>
                        <td>{$rollNumber}</td>
                        <td>
                            <a href='tel:{$parentPhone}' class='text-decoration-none'>
                                {$parentPhone}
                            </a>
                        </td>
                        <td>{$inTime}</td>
                        <td>{$outTime}</td>
                        <td>
                            <span class='badge rounded-pill px-3 py-2 {$badgeClass}'>
                                {$status}
                            </span>
                        </td>
                    </tr>
                ";
                $i++;
            }
        } else {
            echo "
                <tr>
                    <td colspan='8' class='text-danger fw-semibold py-3'>
                        No attendance records found.
                    </td>
                </tr>
            ";
        }
        ?>
    </tbody>
</table>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include "../includes/footer.php"; ?>
    </div>
    <?php include "../includes/js_links.php"; ?>
</body>
</html>
