<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

include('../config/database.php');

/* =====================
   MAIN QUERY (TEACHERS)
===================== */
$sql = "SELECT *
        FROM teachers
        WHERE status = 1
        ORDER BY name ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>School Management Softwere</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/css/style.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
<div id="main-wrapper">

<?php include('../includes/navbar.php'); ?>
<?php include('../includes/sidebar_logic.php'); ?>

<div class="content-body">
<div class="container-fluid">

    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <?php include('../includes/welcome_text.php'); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="card-title mb-2 mb-sm-0">Teacher ID Card Generator</h4>

                    <div class="input-group my-2 py-2">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control"
                               placeholder="Search by teacher name or code...">
                    </div>

                    <div>
                        <button type="button" id="generateChecked" class="btn btn-warning me-2">
                            <i class="fa fa-id-card"></i> Generate Selected IDs
                        </button>

                        <button type="button" id="generateAll" class="btn btn-primary">
                            <i class="fa fa-id-card"></i> Generate All IDs
                        </button>
                    </div>
                </div>

                <div class="card-body">

                    <!-- TABLE -->
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered table-striped align-middle text-dark">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Photo</th>
                                    <th>Teacher Code</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            <?php
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {

                                    $photoPath = "../assets/images/default-avatar.png";
                                    if (!empty($row['photo'])) {
                                        $p = "../" . $row['photo'];
                                        if (file_exists($p)) $photoPath = $p;
                                    }
                            ?>

                                <tr class="text-center">
                                    <td>
                                        <input type="checkbox"
                                               class="teacher-check"
                                               value="<?= $row['teacher_code']; ?>">
                                    </td>

                                    <td>
                                        <img src="<?= $photoPath; ?>"
                                             style="width:45px;height:45px;border-radius:50%;object-fit:cover;">
                                    </td>

                                    <td><?= $row['teacher_code']; ?></td>
                                    <td><?= $row['name']; ?></td>
                                    <td><?= $row['email']; ?></td>
                                    <td><?= $row['subject']; ?></td>

                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-primary generateSingle"
                                                data-id="<?= $row['teacher_code']; ?>">
                                            <i class="fa fa-id-card"></i> Generate
                                        </button>
                                    </td>
                                </tr>

                            <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="7" class="text-center text-danger">
                                        No teachers found.
                                    </td>
                                </tr>
                            <?php } ?>

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
</div>
</div>

<?php include('../includes/footer.php'); ?>
<?php include('../includes/js_links.php'); ?>

<script>
$(document).ready(function () {

    /* Generate All */
    $('#generateAll').on('click', function () {

        let form = $('<form>', {
            method: 'POST',
            action: '../actions/generate_teacher_idcard_action.php',
            target: '_blank'
        });

        form.append($('<input>', {
            type: 'hidden',
            name: 'generate_all',
            value: 1
        }));

        $('body').append(form);
        form[0].submit();
    });

    /* Generate Single */
    $('.generateSingle').on('click', function () {

        let teacherCode = $(this).data('id');

        let form = $('<form>', {
            method: 'POST',
            action: '../actions/generate_teacher_idcard_action.php',
            target: '_blank'
        });

        form.append($('<input>', {
            type: 'hidden',
            name: 'teacher_code',
            value: teacherCode
        }));

        form.append($('<input>', {
            type: 'hidden',
            name: 'generate_single',
            value: 1
        }));

        $('body').append(form);
        form[0].submit();
    });

    /* Generate Selected */
    $('#generateChecked').on('click', function () {

        let selectedTeachers = [];

        $('.teacher-check:checked').each(function () {
            selectedTeachers.push($(this).val());
        });

        if (selectedTeachers.length === 0) {
            Swal.fire('Warning', 'Please select at least one teacher', 'warning');
            return;
        }

        let form = $('<form>', {
            method: 'POST',
            action: '../actions/generate_teacher_idcard_action.php',
            target: '_blank'
        });

        selectedTeachers.forEach(function (code) {
            form.append(
                $('<input>', {
                    type: 'hidden',
                    name: 'teacher_codes[]',
                    value: code
                })
            );
        });

        form.append(
            $('<input>', {
                type: 'hidden',
                name: 'generate_checked',
                value: 1
            })
        );

        $('body').append(form);
        form[0].submit();
    });

    /* Search */
    $('#searchInput').on('keyup', function () {
        let value = $(this).val().toLowerCase();

        $('table tbody tr').filter(function () {
            $(this).toggle(
                $(this).text().toLowerCase().indexOf(value) > -1
            );
        });
    });

});
</script>

</body>
</html>
