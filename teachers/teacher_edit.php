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
    header('Location: teacher_list.php');
    exit;
}

$teacher_id = intval($_GET['id']);

// Fetch teacher data from database with user info
$query = "SELECT t.*, u.email as user_email, u.phone as user_phone 
          FROM teachers t 
          JOIN users u ON t.user_id = u.id 
          WHERE t.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Teacher not found
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

// Process base paths for display
$photo_display_path = !empty($teacher['photo']) ? '../' . $teacher['photo'] : '';
$aadhar_display_path = !empty($teacher['aadhar']) ? '../' . $teacher['aadhar'] : '';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>School Management Softwere - Edit Teacher</title>
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
                            <li class="breadcrumb-item"><a href="teacher_list.php">Teachers</a></li>
                            <li class="breadcrumb-item active">Edit Teacher</li>
                        </ol>
                    </div>
                </div>

                <!-- Teacher Edit Form -->
                <div class="row">
                    <div class="col-xl-12 col-xxl-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h4 class="card-title mb-2 mb-sm-0">
                                    Edit Teacher: <?php echo htmlspecialchars($teacher['name']); ?>
                                    <small class="text-muted d-block mt-1">Code: <?php echo htmlspecialchars($teacher['teacher_code']); ?></small>
                                </h4>
                                <a href="teacher_list.php" class="btn btn-secondary">
                                    <i class="fa fa-list"></i> View All Teachers
                                </a>
                            </div>

                            <div class="card-body">
                                <!-- Display current photo and aadhar -->
                                <div class="row mb-4">
                                    <?php if (!empty($teacher['photo'])): ?>
                                        <div class="col-md-6 text-center">
                                            <p class="mb-1"><strong>Current Photo:</strong></p>
                                            <img src="<?php echo htmlspecialchars($photo_display_path); ?>"
                                                alt="Teacher Photo"
                                                style="max-width: 150px; max-height: 150px; border-radius: 5px; object-fit: cover;">
                                            <p class="small text-muted mt-1"><?php echo basename($teacher['photo']); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($teacher['aadhar'])): ?>
                                        <div class="col-md-6 text-center">
                                            <p class="mb-1"><strong>Current Aadhar:</strong></p>
                                            <?php
                                            $aadhar_file = $teacher['aadhar'];
                                            $aadhar_ext = strtolower(pathinfo($aadhar_file, PATHINFO_EXTENSION));
                                            ?>
                                            <?php if (in_array($aadhar_ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                                <img src="<?php echo htmlspecialchars($aadhar_display_path); ?>"
                                                    alt="Aadhar Document"
                                                    style="max-width: 150px; max-height: 150px; border-radius: 5px; object-fit: cover;">
                                            <?php elseif ($aadhar_ext === 'pdf'): ?>
                                                <a href="<?php echo htmlspecialchars($aadhar_display_path); ?>"
                                                    target="_blank"
                                                    class="btn btn-info btn-sm">
                                                    <i class="fa fa-file-pdf"></i> View PDF
                                                </a>
                                            <?php endif; ?>
                                            <p class="small text-muted mt-1"><?php echo basename($teacher['aadhar']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <form method="POST" action="../actions/teacher_update_action.php" enctype="multipart/form-data">
                                    <input type="hidden" name="teacher_id" value="<?php echo $teacher_id; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($teacher['user_id']); ?>">
                                    <input type="hidden" name="current_photo" value="<?php echo htmlspecialchars($teacher['photo']); ?>">
                                    <input type="hidden" name="current_aadhar" value="<?= htmlspecialchars($teacher['aadhar'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                                    <div class="row">
                                        <!-- Name -->
                                        <div class="col-md-6 mb-3">
                                            <label>Full Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="name"
                                                value="<?php echo htmlspecialchars($teacher['name']); ?>"
                                                placeholder="Enter full name" required>
                                        </div>

                                        <!-- Email -->
                                        <div class="col-md-6 mb-3">
                                            <label>Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email"
                                                value="<?php echo htmlspecialchars($teacher['user_email']); ?>"
                                                placeholder="Enter email" required>
                                        </div>

                                        <!-- Phone -->
                                        <div class="col-md-6 mb-3">
                                            <label>Phone</label>
                                            <input type="text" class="form-control" name="phone"
                                                value="<?php echo htmlspecialchars($teacher['user_phone']); ?>"
                                                placeholder="Enter phone number">
                                        </div>

                                        <!-- Qualification -->
                                        <div class="col-md-6 mb-3">
                                            <label>Qualification</label>
                                            <input type="text" class="form-control" name="qualification"
                                                value="<?php echo htmlspecialchars($teacher['qualification']); ?>"
                                                placeholder="Enter qualification">
                                        </div>

                                        <!-- Subject -->
                                        <div class="col-md-6 mb-3">
                                            <label>Subject <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="subject"
                                                value="<?php echo htmlspecialchars($teacher['subject']); ?>"
                                                placeholder="Enter subject specialization" required>
                                        </div>

                                        <!-- Salary -->
                                        <div class="col-md-6 mb-3">
                                            <label>Salary (₹) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="salary"
                                                value="<?php echo htmlspecialchars($teacher['salary']); ?>"
                                                placeholder="Enter monthly salary" required>
                                        </div>

                                        <!-- Join Date -->
                                        <div class="col-md-6 mb-3">
                                            <label>Join Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="join_date"
                                                value="<?php echo htmlspecialchars($teacher['join_date']); ?>" required>
                                        </div>

                                        <!-- Photo -->
                                        <div class="col-md-6 mb-3">
                                            <label>Photo (Leave empty to keep current)</label>
                                            <input type="file" class="form-control" name="photo" accept="image/*">
                                            <small class="form-text text-muted">Accepted: JPG, PNG, GIF. Max 2MB</small>
                                        </div>

                                        <!-- Aadhar -->
                                        <div class="col-md-6 mb-3">
                                            <label>Aadhar (Upload PDF/Image - Leave empty to keep current)</label>
                                            <input type="file" class="form-control" name="aadhar" accept="image/*,.pdf">
                                            <small class="form-text text-muted">Accepted: JPG, PNG, GIF, PDF. Max 5MB</small>
                                        </div>
                                    </div>

                                    <!-- Submit and Cancel Buttons -->
                                    <div class="text-center mt-3">
                                        <button type="submit" name="update_teacher" class="btn btn-primary px-4">
                                            <i class="fa fa-save"></i> Update Teacher
                                        </button>
                                        <a href="teacher_list.php" class="btn btn-light px-4 ml-2">
                                            <i class="fa fa-times"></i> Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php include "../includes/footer.php" ?>
    </div>
    <?php include "../includes/js_links.php" ?>
</body>

</html>