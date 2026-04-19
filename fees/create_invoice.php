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
    <?php include "../includes/preloader.php"; ?>
    <div id="main-wrapper">
        <?php include "../includes/navbar.php"; ?>
        <?php include('../includes/sidebar_logic.php'); ?>

        <div class="content-body">
            <div class="container-fluid">

                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <?php include('../includes/welcome_text.php'); ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between flex-wrap">
                        <h4 class="card-title">Select Student to Create Invoice</h4>
                        <a href="view_invoices.php" class="btn btn-secondary">
                            <i class="fa fa-list"></i> View All Invoices
                        </a>
                    </div>

                    <div class="card-body">
                        <!-- FILTER BAR -->
                        <div class="row mb-3">
                            <div class="col-md-4 mb-2">
                                <input type="text" id="searchName" class="form-control" placeholder="Search student name...">
                            </div>
                            <div class="col-md-4 mb-2">
                                <select id="filterClass" class="form-control">
                                    <option value="">-- Filter Class --</option>
                                    <?php
                                    $classQ = $conn->query("SELECT id, class_name FROM classes ORDER BY class_name ASC");
                                    while ($c = $classQ->fetch_assoc()) {
                                        echo "<option value='{$c['id']}'>{$c['class_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <select id="filterSection" class="form-control">
                                    <option value="">-- Filter Section --</option>
                                </select>
                            </div>
                        </div>
                        <!-- STUDENTS TABLE -->
                        <div class="table-responsive ">
                            <table class="table table-bordered" id="studentTable">
                                <thead class="text-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Transport</th>
                                        <th>Phone</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody class="text-dark">
                                    <?php
                                    $sql = "SELECT 
                                    s.id, s.first_name, s.last_name, s.photo, s.parent_phone, s.has_transport,
                                    c.id AS class_id, c.class_name,
                                    sec.id AS section_id, sec.section_name
                                FROM students s
                                LEFT JOIN classes c ON s.class_id = c.id
                                LEFT JOIN sections sec ON s.section_id = sec.id
                                ORDER BY c.class_name, sec.section_name, s.first_name ASC";

                                    $res = $conn->query($sql);
                                    $i = 1;

                                    while ($s = $res->fetch_assoc()) {

                                        $photo = !empty($s['photo']) ? "../" . $s['photo'] : "../assets/img/default-student.png";
                                        $transport = $s['has_transport'] == 1 ? "Yes" : "No";

                                        echo "
                                <tr 
                                    data-class='{$s['class_id']}' 
                                    data-section='{$s['section_id']}'
                                >
                                    <td>{$i}</td>
                                    <td><img src='{$photo}' width='40' height='40' class='rounded-circle'></td>
                                    <td>{$s['first_name']} {$s['last_name']}</td>
                                    <td>{$s['class_name']}</td>
                                    <td>{$s['section_name']}</td>
                                    <td>{$transport}</td>
                                    <td>{$s['parent_phone']}</td>
                                    <td class='text-center'>
                                        <a href='create_invoice_form.php?student_id={$s['id']}' class='btn btn-sm btn-primary'>
                                            <i class='fa fa-plus'></i> Invoice
                                        </a>
                                    </td>
                                </tr>";
                                        $i++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {

                const searchInput = document.getElementById("searchName");
                const classFilter = document.getElementById("filterClass");
                const sectionFilter = document.getElementById("filterSection");
                const rows = document.querySelectorAll("#studentTable tbody tr");

                // ✅ Load section list when class changes
                classFilter.addEventListener("change", function() {
                    let classId = this.value;

                    // Reset section dropdown
                    sectionFilter.innerHTML = '<option value="">-- Filter Section --</option>';

                    // Show all if no class selected
                    if (classId === "") {
                        filterStudents();
                        return;
                    }

                    // Fetch sections via AJAX
                    fetch("../ajax/get_sections_invoice.php?class_id=" + classId)
                        .then(res => res.json())
                        .then(data => {
                            data.forEach(sec => {
                                sectionFilter.innerHTML += `<option value="${sec.id}">${sec.section_name}</option>`;
                            });
                            filterStudents();
                        });
                });

                // ✅ Filter when section changes
                sectionFilter.addEventListener("change", filterStudents);

                // ✅ Search filter
                searchInput.addEventListener("keyup", filterStudents);

                // ✅ Filter function
                function filterStudents() {
                    let searchText = searchInput.value.toLowerCase();
                    let classValue = classFilter.value;
                    let sectionValue = sectionFilter.value;

                    rows.forEach(row => {
                        let name = row.cells[2].textContent.toLowerCase();
                        let rowClass = row.getAttribute("data-class");
                        let rowSection = row.getAttribute("data-section");

                        let matchSearch = name.includes(searchText);
                        let matchClass = (classValue === "" || rowClass === classValue);
                        let matchSection = (sectionValue === "" || rowSection === sectionValue);

                        if (matchSearch && matchClass && matchSection) {
                            row.style.display = "";
                        } else {
                            row.style.display = "none";
                        }
                    });
                }

            });
        </script>


        <?php include "../includes/footer.php"; ?>
    </div>

    <?php include "../includes/js_links.php"; ?>



</body>

</html>