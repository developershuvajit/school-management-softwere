<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

include('../config/database.php');

// Check if teacher ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Teacher ID missing!',
        }).then(() => {
            window.location.href = 'teacher_list.php';
        });
    </script>";
    exit;
}

$id = (int)$_GET['id'];

// Fetch teacher data from database
$sql = "SELECT 
        t.*, 
        u.email as user_email, 
        u.phone as user_phone,
        u.plain_password,
        u.created_at as user_created_at
        FROM teachers t
        LEFT JOIN users u ON t.user_id = u.id
        WHERE t.id = ? 
        LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Not Found',
            text: 'Teacher not found!',
        }).then(() => {
            window.location.href = 'teacher_list.php';
        });
    </script>";
    exit;
}

$teacher = $result->fetch_assoc();
$stmt->close();

// Process data
$photo = !empty($teacher['photo']) ? '../' . $teacher['photo'] : '';
$aadhar = !empty($teacher['aadhar']) ? '../' . $teacher['aadhar'] : null;

// Format dates
$join_date = !empty($teacher['join_date']) ? date('d/m/Y', strtotime($teacher['join_date'])) : '-';
$created_at = !empty($teacher['user_created_at']) ? date('d/m/Y', strtotime($teacher['user_created_at'])) : '-';

// Try to get teacher assignments (if table exists)
$assignments = [];
try {
    // First check if the table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'teacher_assignments'");
    if ($table_check && $table_check->num_rows > 0) {
        $assign_sql = "SELECT 
                      c.class_name, 
                      s.section_name,
                      sub.subject_name
                      FROM teacher_assignments ta
                      LEFT JOIN classes c ON ta.class_id = c.id
                      LEFT JOIN sections s ON ta.section_id = s.id
                      LEFT JOIN subjects sub ON ta.subject_id = sub.id
                      WHERE ta.teacher_id = ?";

        $assign_stmt = $conn->prepare($assign_sql);
        if ($assign_stmt) {
            $assign_stmt->bind_param("i", $id);
            $assign_stmt->execute();
            $assign_result = $assign_stmt->get_result();
            $assignments = $assign_result->fetch_all(MYSQLI_ASSOC);
            $assign_stmt->close();
        }
    }
} catch (Exception $e) {
    // Silently fail if table doesn't exist
    $assignments = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>School India Junior - Teacher Details</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        h4,
        h5,
        h6 {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .fw-bold {
            font-weight: 600;
        }

        .card-body p {
            margin-bottom: 0.25rem;
        }

        .bg-light-subtle {
            background-color: #f9fafb !important;
        }

        .card-body,
        .card-body p,
        .card-body div,
        .card-body strong,
        .card-body span,
        .card-body h6 {
            color: #000 !important;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .img-fluid {
            max-width: 100%;
            height: auto;
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
                <!-- Breadcrumb -->
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <?php include('../includes/welcome_text.php'); ?>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="teacher_list.php">Teachers</a></li>
                            <li class="breadcrumb-item active">View Teacher</li>
                        </ol>
                    </div>
                </div>

                <div class="container my-5">
                    <div class="card shadow-lg border-0">

                        <!-- Header -->
                        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center bg-primary text-white">
                            <h4 class="mb-2 mb-md-0 text-white">
                                <?php echo htmlspecialchars($teacher['name']); ?>
                            </h4>

                            <div class="d-flex flex-column flex-md-row gap-2">
                                <a href="teacher_edit.php?id=<?php echo $teacher['id']; ?>" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <a href="teacher_list.php" class="btn btn-light btn-sm">
                                    <i class="bi bi-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="card-body p-4">

                            <!-- Photo + Basic Info -->
                            <div class="row mb-4">
                                <div class="col-12 col-md-3 text-center mb-3 mb-md-0">
                                    <img src="<?php echo $photo; ?>" class="img-fluid rounded-circle shadow-sm border" style="max-height:200px; object-fit: cover; width:200px; height:200px;">
                                </div>

                                <div class="col-12 col-md-9">
                                    <h5 class="fw-bold text-secondary mb-3">Teacher Information</h5>
                                    <div class="row g-2">
                                        <div class="col-6 col-md-4"><strong>Teacher Code:</strong> <?php echo htmlspecialchars($teacher['teacher_code']); ?></div>
                                        <div class="col-6 col-md-4"><strong>Join Date:</strong> <?php echo $join_date; ?></div>
                                        <div class="col-6 col-md-4"><strong>Account Created:</strong> <?php echo $created_at; ?></div>
                                        <div class="col-6 col-md-4"><strong>Subject:</strong> <?php echo htmlspecialchars($teacher['subject']); ?></div>
                                        <div class="col-6 col-md-4"><strong>Qualification:</strong> <?php echo htmlspecialchars($teacher['qualification']); ?></div>
                                        <div class="col-6 col-md-4"><strong>Monthly Salary:</strong> ₹<?php echo number_format($teacher['salary'], 2); ?></div>
                                        <div class="col-6 col-md-4"><strong>Email:</strong> <?php echo htmlspecialchars($teacher['user_email']); ?></div>
                                        <div class="col-6 col-md-4"><strong>Phone:</strong> <?php echo htmlspecialchars($teacher['user_phone']); ?></div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Assigned Classes/Subjects -->
                            <h5 class="fw-bold text-secondary mb-3">Assigned Classes & Subjects</h5>
                            <div class="row mb-4">
                                <?php if (count($assignments) > 0): ?>
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Class</th>
                                                        <th>Section</th>
                                                        <th>Subject</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($assignments as $index => $assign): ?>
                                                        <tr>
                                                            <td><?php echo $index + 1; ?></td>
                                                            <td><?php echo htmlspecialchars($assign['class_name'] ?? '-'); ?></td>
                                                            <td><?php echo htmlspecialchars($assign['section_name'] ?? '-'); ?></td>
                                                            <td><?php echo htmlspecialchars($assign['subject_name'] ?? '-'); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i> No class assignments found for this teacher.
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <hr>

                            <!-- Login Credentials -->
                            <h5 class="fw-bold text-secondary mb-3">Login Credentials</h5>
                            <div class="row mb-3 g-3 align-items-center">
                                <div class="col-md-5 p-3 border rounded shadow-sm">
                                    <strong>Email (Login ID):</strong><br>
                                    <?php echo htmlspecialchars($teacher['user_email'] ?? '-'); ?>
                                </div>

                                <div class="col-md-5 p-3 border rounded shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Password:</strong><br>
                                            <span id="maskedPwd" style="letter-spacing:3px;">
                                                <?php echo $teacher['plain_password'] ? str_repeat('•', strlen($teacher['plain_password'])) : '-'; ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($teacher['plain_password'])): ?>
                                            <button id="togglePwdBtn" class="btn btn-sm btn-outline-secondary ms-2">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" id="plainPassword" value="<?php echo htmlspecialchars($teacher['plain_password'] ?? ''); ?>">
                                </div>

                                <div class="col-md-2">
                                    <button id="shareBtn" class="btn btn-primary w-100">
                                        <i class="bi bi-share"></i> Share
                                    </button>
                                </div>
                            </div>

                            <hr>

                            <!-- Aadhar Document -->
                            <h5 class="fw-bold text-secondary mb-3">Aadhar Document</h5>
                            <div class="row mb-4">
                                <div class="col-12 col-md-6">
                                    <?php
                                    // Check if aadhar exists in database and file exists on server
                                    $aadhar_display = null;
                                    $aadhar_ext = null;

                                    if (!empty($teacher['aadhar'])) {
                                        // Get the stored path
                                        $aadhar_path_db = $teacher['aadhar'];

                                        // Remove 'public/' if it exists at the beginning
                                        $relative_path = str_replace('public/', '', $aadhar_path_db);

                                        // Check if file exists in multiple possible locations
                                        $possible_paths = [
                                            '../' . $aadhar_path_db,
                                            '../public/uploads/teachers/aadhar/' . basename($aadhar_path_db),
                                            '../public/uploads/teachers/' . basename($aadhar_path_db),
                                            '../' . $relative_path
                                        ];

                                        foreach ($possible_paths as $test_path) {
                                            if (file_exists($test_path)) {
                                                $aadhar_display = $test_path;
                                                break;
                                            }
                                        }

                                        // If still not found, use the database path anyway (for display)
                                        if (!$aadhar_display) {
                                            $aadhar_display = '../' . $aadhar_path_db;
                                        }

                                        $aadhar_ext = strtolower(pathinfo($aadhar_path_db, PATHINFO_EXTENSION));
                                    }
                                    ?>

                                    <?php if (!empty($teacher['aadhar']) && !empty($aadhar_display)): ?>
                                        <?php if (in_array($aadhar_ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                            <div class="text-center">
                                                <img src="<?php echo $aadhar_display; ?>"
                                                    class="img-fluid rounded shadow-sm border"
                                                    style="max-height: 300px; max-width: 100%;"
                                                    alt="Aadhar Document"
                                                    onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<div class=\'alert alert-warning\'><i class=\'bi bi-exclamation-triangle\'></i> Aadhar image could not be loaded. File may be missing or corrupted.</div>';">
                                                <p class="mt-2 small text-muted">Aadhar Card (Image)</p>
                                                <a href="<?php echo $aadhar_display; ?>"
                                                    target="_blank"
                                                    class="btn btn-sm btn-outline-primary mt-2">
                                                    <i class="bi bi-arrows-fullscreen"></i> View Full Size
                                                </a>
                                            </div>
                                        <?php elseif ($aadhar_ext === 'pdf'): ?>
                                            <div class="text-center">
                                                <div class="p-4 border rounded bg-light">
                                                    <i class="bi bi-file-pdf" style="font-size: 3rem; color: #dc3545;"></i>
                                                    <p class="mt-2">PDF Document: <?php echo basename($teacher['aadhar']); ?></p>
                                                    <a href="<?php echo $aadhar_display; ?>"
                                                        target="_blank"
                                                        class="btn btn-danger btn-sm">
                                                        <i class="bi bi-eye"></i> View PDF
                                                    </a>
                                                    <a href="<?php echo $aadhar_display; ?>"
                                                        download
                                                        class="btn btn-outline-danger btn-sm ms-2">
                                                        <i class="bi bi-download"></i> Download
                                                    </a>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info">
                                                <p><strong>Document Type:</strong> <?php echo strtoupper($aadhar_ext); ?></p>
                                                <p><strong>File:</strong> <?php echo basename($teacher['aadhar']); ?></p>
                                                <a href="<?php echo $aadhar_display; ?>"
                                                    target="_blank"
                                                    class="btn btn-info btn-sm">
                                                    <i class="bi bi-download"></i> Download Aadhar
                                                </a>
                                                <button onclick="checkFileExists('<?php echo $aadhar_display; ?>')"
                                                    class="btn btn-warning btn-sm ms-2">
                                                    <i class="bi bi-search"></i> Check File
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle"></i> No Aadhar document uploaded or file not found.
                                            <?php if (!empty($teacher['aadhar'])): ?>
                                                <p class="small mt-2">Database record shows: <?php echo htmlspecialchars($teacher['aadhar']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Additional Info -->
                                <div class="col-12 col-md-6">
                                    <div class="p-3 border rounded shadow-sm bg-light-subtle">
                                        <h6 class="fw-bold mb-3">Additional Information</h6>
                                        <p><strong>Teacher ID:</strong> <?php echo $teacher['id']; ?></p>
                                        <p><strong>User ID:</strong> <?php echo $teacher['user_id']; ?></p>
                                        <p><strong>Aadhar Status:</strong>
                                            <?php if (!empty($teacher['aadhar'])): ?>
                                                <span class="badge bg-success">Uploaded</span>
                                                <small class="text-muted d-block">File: <?php echo basename($teacher['aadhar']); ?></small>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Not Uploaded</span>
                                            <?php endif; ?>
                                        </p>
                                        <p><strong>QR Code:</strong>
                                            <?php if (!empty($teacher['qr_code'])): ?>
                                                <a href="../<?php echo $teacher['qr_code']; ?>"
                                                    target="_blank"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-qr-code"></i> View QR
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Not generated</span>
                                            <?php endif; ?>
                                        </p>
                                        <p><strong>Status:</strong>
                                            <?php echo (isset($teacher['status']) && $teacher['status'] == 1) ?
                                                '<span class="badge bg-success">Active</span>' :
                                                '<span class="badge bg-danger">Inactive</span>'; ?>
                                        </p>
                                    </div>
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
        // Wait for DOM to load
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const toggleBtn = document.getElementById('togglePwdBtn');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const mask = document.getElementById('maskedPwd');
                    const plain = document.getElementById('plainPassword').value;
                    const icon = this.querySelector('i');
                    if (mask.textContent.includes('•')) {
                        mask.textContent = plain;
                        icon.classList.replace('bi-eye', 'bi-eye-slash');
                    } else {
                        mask.textContent = '•'.repeat(plain.length);
                        icon.classList.replace('bi-eye-slash', 'bi-eye');
                    }
                });
            }

            // Share credentials
            const shareBtn = document.getElementById('shareBtn');
            if (shareBtn) {
                shareBtn.addEventListener('click', function() {
                    const teacherName = "<?php echo addslashes($teacher['name']); ?>";
                    const teacherCode = "<?php echo addslashes($teacher['teacher_code']); ?>";
                    const teacherEmail = "<?php echo addslashes($teacher['user_email']); ?>";
                    const teacherPassword = "<?php echo addslashes($teacher['plain_password']); ?>";

                    var msg = `Teacher Login Credentials\n\n` +
                        `Name: ${teacherName}\n` +
                        `Teacher Code: ${teacherCode}\n` +
                        `Email: ${teacherEmail}\n` +
                        `Password: ${teacherPassword}\n\n` +
                        `Login URL: ${window.location.origin}/login.php`;

                    navigator.clipboard.writeText(msg).then(() => {
                        Swal.fire({
                            title: "Copied!",
                            text: "Teacher credentials copied to clipboard",
                            icon: "success",
                            timer: 2000
                        });
                    }).catch(err => {
                        console.error('Copy failed:', err);
                        Swal.fire({
                            title: "Error",
                            text: "Failed to copy credentials. Please try manually.",
                            icon: "error"
                        });
                    });
                });
            }
        });
    </script>
    <script>
        function checkFileExists(fileUrl) {
            fetch(fileUrl, {
                    method: 'HEAD'
                })
                .then(response => {
                    if (response.ok) {
                        Swal.fire({
                            title: 'File Found!',
                            text: 'The file exists on the server.',
                            icon: 'success',
                            timer: 2000
                        });
                    } else {
                        Swal.fire({
                            title: 'File Not Found',
                            text: 'The file does not exist at the specified path.',
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error',
                        text: 'Could not check file: ' + error,
                        icon: 'error'
                    });
                });
        }
    </script>
</body>

</html>