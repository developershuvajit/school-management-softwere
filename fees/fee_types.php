<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

include('../config/database.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>School Management Softwere</title>

    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/css/style.css" rel="stylesheet">
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

                <!-- Add Fee Type Form -->
                <div class="row">
                    <div class="col-xl-12 col-xxl-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Add / Manage Fee Types</h4>
                            </div>

                            <div class="card-body text-dark">
                                <form method="POST" action="../actions/fee_types_action.php" class="row">
                                    <input type="hidden" name="id" id="fee_id">

                                    <div class="form-group col-md-3">
                                        <label>Fee Type Name *</label>
                                        <input type="text" name="name" id="name" class="form-control" required>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label>Category</label>
                                        <select name="category" id="category" class="form-control" required>
                                            <option value="fee">Fee</option>
                                            <option value="product">Product</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label>Frequency</label>
                                        <select name="frequency" id="frequency" class="form-control" required>
                                            <option value="one_time">One Time</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="annual">Annual</option>
                                            <option value="ad_hoc">Ad Hoc</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label>Academic Year *</label>
                                        <select name="academic_year_id" id="academic_year_id" class="form-control" required>
                                            <option value="">Select Academic Year</option>
                                            <?php
                                            $sql_ay = "SELECT id, academic_year FROM academic_years ORDER BY id DESC";
                                            $result_ay = mysqli_query($conn, $sql_ay);

                                            while ($row_ay = mysqli_fetch_assoc($result_ay)) {
                                                echo "<option value='{$row_ay['id']}'>{$row_ay['academic_year']}</option>";
                                            }
                                            ?>
                                        </select>

                                    </div>


                                    <div class="form-group col-md-2">
                                        <label>Class *</label>
                                        <select name="class_id" id="class_id" class="form-control" required>
                                            <?php
                                            $classList = $conn->query("SELECT id, class_name FROM classes ORDER BY id ASC");
                                            while ($c = $classList->fetch_assoc()) {
                                                echo "<option value='{$c['id']}'>{$c['class_name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-1">
                                        <label>Amount *</label>
                                        <input type="number" name="amount" id="amount" class="form-control" required min="0">
                                    </div>

                                    <div class="col-12 text-center mt-3">
                                        <button type="submit" name="save_fee" class="btn btn-primary px-4">
                                            <i class="fa fa-save"></i> Save
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fee Type List -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">All Fee Types</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <?php
                                    require_once '../includes/alert_helper.php';

                                    $res = $conn->query("SELECT f.*, c.class_name, ay.academic_year AS academic_year_name 
                                                        FROM fee_types f
                                                        JOIN classes c ON f.class_id = c.id
                                                        JOIN academic_years ay ON f.academic_year_id = ay.id
                                                        ORDER BY f.id DESC");
                                    ?>
                                    <style>
                                        table.display tbody tr {
                                            border-bottom: 1px solid #ccc;
                                        }
                                    </style>
                                    <table class="display text-dark" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Year</th>
                                                <th>Class</th>
                                                <th>Category</th>
                                                <th>Frequency</th>
                                                <th>Amount</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $i = 1;
                                            while ($row = $res->fetch_assoc()) {
                                                $id = $row['id'];

                                                echo "<tr>
                                                        <td>{$i}</td>
                                                        <td>{$row['name']}</td>
                                                        <td>{$row['academic_year_name']}</td>
                                                        <td>{$row['class_name']}</td>
                                                        <td>{$row['category']}</td>
                                                        <td>{$row['frequency']}</td>
                                                        <td>₹{$row['amount']}</td>
                                                        <td class='text-center'>
                                                            <button onclick='editFee(" . json_encode($row) . ")' class='btn btn-sm btn-warning'>
                                                                <i class='fa fa-edit'></i>
                                                            </button>
                                                            <button onclick='deleteFee($id)' class='btn btn-sm btn-danger'>
                                                                <i class='fa fa-trash'></i>
                                                            </button>
                                                        </td>
                                                    </tr>";

                                                $i++;
                                            }
                                            ?>
                                        </tbody>
                                    </table>


                                    <script>
                                        function deleteFee(id) {
                                            Swal.fire({
                                                title: "Are you sure?",
                                                icon: "warning",
                                                showCancelButton: true,
                                                confirmButtonColor: "#d33"
                                            }).then((res) => {
                                                if (res.isConfirmed) {
                                                    window.location.href = "../actions/fee_types_action.php?delete=" + id;
                                                }
                                            });
                                        }

                                        function editFee(f) {
                                            document.getElementById("fee_id").value = f.id;
                                            document.getElementById("name").value = f.name;
                                            document.getElementById("category").value = f.category;
                                            document.getElementById("frequency").value = f.frequency;
                                            document.getElementById("academic_year").value = f.academic_year;
                                            document.getElementById("class_id").value = f.class_id;
                                            document.getElementById("amount").value = f.amount;
                                            window.scrollTo({
                                                top: 0,
                                                behavior: "smooth"
                                            });
                                        }
                                    </script>

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