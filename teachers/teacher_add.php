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
                </div>

                <!-- Teacher Add Form -->
                <div class="row">
                    <div class="col-xl-12 col-xxl-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h4 class="card-title mb-2 mb-sm-0">Add New Teacher</h4>
                                <a href="teacher_list.php" class="btn btn-secondary">
                                    <i class="fa fa-list"></i> View All Teachers
                                </a>
                            </div>

                            <div class="card-body">
                                <form method="POST" action="../actions/teacher_add_action.php" enctype="multipart/form-data">
                                    <div class="row">
                                        <!-- Name -->
                                        <div class="col-md-6 mb-3">
                                            <label>Full Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="name" placeholder="Enter full name" required>
                                        </div>

                                        <!-- Email -->
                                        <div class="col-md-6 mb-3">
                                            <label>Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email" placeholder="Enter email" required>
                                        </div>

                                        <!-- Phone -->
                                        <div class="col-md-6 mb-3">
                                            <label>Phone</label>
                                            <input type="text" class="form-control" name="phone" placeholder="Enter phone number">
                                        </div>

                                        <!-- Qualification -->
                                        <div class="col-md-6 mb-3">
                                            <label>Qualification</label>
                                            <input type="text" class="form-control" name="qualification" placeholder="Enter qualification">
                                        </div>

                                        <!-- Subject -->
                                        <div class="col-md-6 mb-3">
                                            <label>Subject</label>
                                            <input type="text" class="form-control" name="subject" placeholder="Enter subject specialization" required>
                                        </div>

                                        <!-- Salary -->
                                        <div class="col-md-6 mb-3">
                                            <label>Salary (₹)</label>
                                            <input type="number" step="0.01" class="form-control" name="salary" placeholder="Enter monthly salary" required>
                                        </div>

                                        <!-- Join Date -->
                                        <div class="col-md-6 mb-3">
                                            <label>Join Date</label>
                                            <input type="date" class="form-control" name="join_date" required>
                                        </div>

                                        <!-- Photo -->
                                        <div class="col-md-6 mb-3">
                                            <label>Photo</label>
                                            <input type="file" class="form-control" name="photo" accept="image/*">
                                        </div>

                                        <!-- Aadhar -->
                                        <div class="col-md-6 mb-3">
                                            <label>Aadhar (Upload PDF/Image)</label>
                                            <input type="file" class="form-control" name="aadhar" accept="image/*,.pdf">
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="text-center mt-3">
                                        <button type="submit" name="add_teacher" class="btn btn-primary px-4">
                                            <i class="fa fa-save"></i> Save Teacher
                                        </button>
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
