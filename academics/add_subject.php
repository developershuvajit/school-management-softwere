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
<?php include('../config/database.php'); ?>

<body>
    <?php include "../includes/preloader.php" ?>
    <div id="main-wrapper">
        <?php include "../includes/navbar.php" ?>
        <?php include('../includes/sidebar_logic.php') ?>
        <div class="content-body">
            <div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <?php include('../includes/welcome_text.php'); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12 col-xxl-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h4 class="card-title mb-2 mb-sm-0">Add New Subject</h4>
                                <a href="subject_list.php" class="btn btn-secondary">
                                    <i class="fa fa-list"></i> View All Subjects
                                </a>
                            </div>

                            <div class="card-body">
                                <form method="POST" action="../actions/subject_actions.php">
                                    <div class="form-row">

                                        <!-- Select Class -->
                                        <div class="form-group col-md-6 col-12">
                                            <label>Select Class <span class="text-danger">*</span></label>
                                            <select class="form-control" name="class_id" required>
                                                <option value="">-- Select Class --</option>
                                                <?php
                                                $classes = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");
                                                while ($row = $classes->fetch_assoc()) {
                                                    echo "<option value='{$row['id']}'>{$row['class_name']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>


                                        <!-- Subject Name -->
                                        <div class="form-group col-md-6 col-12">
                                            <label>Subject Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="subject_name" placeholder="Enter subject name" required>
                                        </div>

                                        <!-- Subject Code -->
                                        <div class="form-group col-md-6 col-12">
                                            <label>Subject Code</label>
                                            <input type="text" class="form-control" name="subject_code" placeholder="Enter subject code">
                                        </div>

                                        <!-- Description -->
                                        <div class="form-group col-md-6 col-12">
                                            <label>Description</label>
                                            <textarea class="form-control" name="description" rows="2" placeholder="Write short description"></textarea>
                                        </div>

                                    </div>

                                    <!-- Submit -->
                                    <div class="text-center mt-3">
                                        <button type="submit" name="add_subject" class="btn btn-primary px-4">
                                            <i class="fa fa-save"></i> Save Subject
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