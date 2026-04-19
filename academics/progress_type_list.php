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
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <?php include('../includes/welcome_text.php'); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h4 class="card-title mb-2 mb-sm-0">All Classes</h4>
                                <a href="progress_item.php" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Add New Item
                                </a>
                            </div>

                            <div class="card-body">
                                <?php require_once __DIR__ . '/../includes/alert_helper.php'; ?>

                                <div class="table-responsive">
                                    <div class="table-responsive">
 <?php
include('../config/database.php');
?>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Type Name</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        <?php
        $res = $conn->query("SELECT * FROM progress_types ORDER BY id DESC");
        $i = 1;

        while($row = $res->fetch_assoc()):
        ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= $row['name'] ?></td>
            <td>

                <!-- EDIT -->
                <button class="btn btn-sm btn-info"
                    onclick="editType('<?= $row['id'] ?>','<?= $row['name'] ?>')">
                    Edit
                </button>

                <!-- DELETE -->
                <a href="../actions/progress_type_actions.php?delete=<?= $row['id'] ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete?')">
                   Delete
                </a>

            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

 
                                    </div>

                                </div>
                            </div>
                            <!-- EDIT MODAL -->
<div class="modal fade" id="editModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <form method="POST" action="../actions/progress_type_actions.php">

        <div class="modal-header">
          <h5 class="modal-title">Edit Progress Type</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <input type="hidden" name="edit_id" id="modal_edit_id">

          <div class="mb-3">
            <label>Type Name</label>
            <input type="text" name="name" id="modal_name" class="form-control" required>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            Update
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cancel
          </button>
        </div>

      </form>

    </div>
  </div>
</div>
                        </div>
                    </div>
                </div>
            </div>

          

            <!-- ✅ jQuery -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <!-- ✅ SweetAlert -->
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function editType(id, name){

    document.getElementById("modal_edit_id").value = id;
    document.getElementById("modal_name").value = name;

    var myModal = new bootstrap.Modal(document.getElementById('editModal'));
    myModal.show();
}
</script>
            <script>
                function confirmDelete(id) {
                    Swal.fire({
                        title: "Are you sure?",
                        text: "This class will be permanently deleted!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, delete it!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "../actions/class_actions.php?delete=" + id;
                        }
                    });
                }

                // ✅ Auto-Fill Edit Modal
                $(document).on("click", ".editBtn", function() {
                    $("#edit_id").val($(this).data('id'));
                    $("#edit_class_name").val($(this).data('name'));
                    $("#edit_description").val($(this).data('desc'));
                    $("#editModal").modal("show");
                });
            </script>
        </div>

        <?php include "../includes/footer.php" ?>
    </div>
    <?php include "../includes/js_links.php" ?>