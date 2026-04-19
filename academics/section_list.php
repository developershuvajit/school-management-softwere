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
                                <h4 class="card-title mb-2 mb-sm-0">All Sections</h4>
                                <a href="add_section.php" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Add New Section
                                </a>
                            </div>

                            <div class="card-body">
                                <?php require_once __DIR__ . '/../includes/alert_helper.php'; ?>

                                <div class="table-responsive">
                                    <table id="example2" class="display table table-bordered text-center" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Class</th>
                                                <th>Section</th>
                                                <th>Description</th>
                                                <th>Created On</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT s.*, c.class_name 
                                            FROM sections s 
                                            JOIN classes c ON s.class_id = c.id 
                                            ORDER BY s.id DESC";

                                            $res = $conn->query($sql);
                                            if ($res && $res->num_rows > 0) {
                                                $i = 1;
                                                while ($row = $res->fetch_assoc()) {
                                                    $id = (int)$row['id'];
                                                    $section_name = htmlspecialchars($row['section_name']);
                                                    $class_name = htmlspecialchars($row['class_name']);
                                                    $description = htmlspecialchars($row['description']);
                                                    $created = date('d M Y', strtotime($row['created_at']));

                                                    echo "
                                            <tr>
                                                <td>{$i}</td>
                                                <td>{$class_name}</td>
                                                <td>{$section_name}</td>
                                                <td>{$description}</td>
                                                <td>{$created}</td>
                                                <td class='text-center'>
                                                     <button type='button' 
                                                        class='btn btn-sm btn-primary editBtn'
                                                        data-id='{$id}'
                                                        data-class-id='{$row['class_id']}'
                                                        data-section='{$section_name}'
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
                                                echo "<tr><td colspan='6' class='text-center text-muted'>No sections found.</td></tr>";
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

            <!-- ✅ Edit Modal (Same style as Class Page) -->
            <div class="modal fade" id="editModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="../actions/section_actions.php">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Section</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>

                            <div class="modal-body">
                                <input type="hidden" name="edit_section_id" id="edit_id">

                                <div class="form-group">
                                    <label>Class</label>
                                    <select name="class_id" id="edit_class" class="form-control" required>
                                        <?php
                                        $classes = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");
                                        while ($c = $classes->fetch_assoc()) {
                                            echo "<option value='{$c['id']}'>{$c['class_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Section Name</label>
                                    <input type="text" name="section_name" id="edit_section" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" id="edit_desc" class="form-control"></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" name="update_section" class="btn btn-primary">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ✅ jQuery -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <!-- ✅ SweetAlert -->
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

            <script>
                function confirmDelete(id) {
                    Swal.fire({
                        title: "Are you sure?",
                        text: "This section will be permanently deleted!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "../actions/section_actions.php?delete=" + id;
                        }
                    });
                }

                // ✅ Auto-Fill Edit Modal (Same logic as class page)
                // ✅ Auto-Fill Edit Modal (Correct Class Dropdown Select)
                $(document).on("click", ".editBtn", function() {
                    $("#edit_id").val($(this).data('id'));

                    let classId = $(this).data('class-id');

                    $("#edit_class").val(classId); // ✅ Select class correctly

                    $("#edit_section").val($(this).data('section'));
                    $("#edit_desc").val($(this).data('desc'));

                    $("#editModal").modal("show");
                });
            </script>
        </div>


        <?php include "../includes/footer.php" ?>
    </div>
    <?php include "../includes/js_links.php" ?>