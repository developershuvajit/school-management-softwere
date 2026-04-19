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
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h4 class="card-title mb-2 mb-sm-0">All Academic Years</h4>
                                <a href="add_academic_year.php" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Add Academic Year
                                </a>
                            </div>

                            <div class="card-body">
                                <?php require_once __DIR__ . '/../includes/alert_helper.php'; ?>

                                <div class="table-responsive">
                                    <table id="example2" class="display table table-striped table-bordered text-center" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Academic Year</th>
                                                <th>Description</th>
                                                <th>Created On</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT * FROM academic_years ORDER BY id DESC";
                                            $res = $conn->query($sql);

                                            if ($res && $res->num_rows > 0) {
                                                $i = 1;
                                                while ($row = $res->fetch_assoc()) {
                                                    $id = (int)$row['id'];
                                                    $academic_year = htmlspecialchars($row['academic_year'], ENT_QUOTES);
                                                    $description = htmlspecialchars($row['description'], ENT_QUOTES);
                                                    $created = date('d M Y', strtotime($row['created_at']));

                                                    echo "
                                                        <tr>
                                                            <td>{$i}</td>
                                                            <td>{$academic_year}</td>
                                                            <td>{$description}</td>
                                                            <td>{$created}</td>
                                                            <td class='text-center'>
                                                                <button type='button' class='btn btn-sm btn-primary editBtn'
                                                                    data-id='{$id}'
                                                                    data-year='{$academic_year}'
                                                                    data-desc='{$description}'>
                                                                    <i class='fa fa-edit'></i> Edit
                                                                </button>
                                                                <button class='btn btn-sm btn-danger' onclick='confirmDelete({$id})'>
                                                                    <i class='fa fa-trash'></i> Delete
                                                                </button>
                                                            </td>
                                                        </tr>";
                                                    $i++;
                                                }
                                            } else {
                                                echo "<tr><td colspan='5' class='text-center text-muted'>No academic years found.</td></tr>";
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

            <!-- ✅ Edit Modal -->
            <div class="modal fade" id="editModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="../actions/academic_year_actions.php">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Academic Year</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="edit_id" id="edit_id">

                                <div class="form-group">
                                    <label>Academic Year</label>
                                    <input type="text" name="academic_year" id="edit_academic_year" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" id="edit_description" class="form-control"></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <!-- ✅ JS -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

            <script>
                function confirmDelete(id) {
                    Swal.fire({
                        title: "Are you sure?",
                        text: "This academic year will be permanently deleted!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "../actions/academic_year_actions.php?delete=" + id;
                        }
                    });
                }

                // ✅ Fill Edit Modal
                $(document).on("click", ".editBtn", function() {
                    $("#edit_id").val($(this).data('id'));
                    $("#edit_academic_year").val($(this).data('year'));
                    $("#edit_description").val($(this).data('desc'));
                    $("#editModal").modal("show");
                });
            </script>
        </div>

        <?php include "../includes/footer.php" ?>
    </div>
    <?php include "../includes/js_links.php" ?>