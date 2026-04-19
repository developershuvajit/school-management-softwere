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
    <title>School India Junior</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        label {
            color: black !important;
        }
    </style>
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
                            <div class="card-header">
                                <h4 class="card-title">Student Admission</h4>

                            </div>
                            <div class="card-body">
                                <div class="container-fluid">
                                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

                                    <form method="POST" action="" enctype="multipart/form-data" id="admissionForm">
                                        <!-- ADMISSION DETAILS -->
                                        <div class="section-card mb-4">
                                            <div class="section-header mb-3">
                                                <h5 class="mb-2">
                                                    <i class="fas fa-user-graduate mr-2 text-primary"></i>Admission Details
                                                </h5>
                                                <p class="text-muted mb-3">Basic admission information</p>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Admission No <span class="text-danger">*</span></label>
                                                        <?php
                                                        $result = $conn->query("SELECT admission_no FROM students ORDER BY id DESC LIMIT 1");
                                                        if ($result->num_rows > 0) {
                                                            $row = $result->fetch_assoc();
                                                            $lastNumber = (int)substr($row['admission_no'], 3);
                                                            $newNumber = $lastNumber + 1;
                                                        } else {
                                                            $newNumber = 1;
                                                        }
                                                        $admissionNo = 'ADM' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
                                                        ?>
                                                        <input type="text" class="form-control bg-light" name="admission_no" value="<?php echo $admissionNo; ?>" readonly>
                                                        <small class="form-text text-muted">Auto-generated</small>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Roll Number</label>
                                                        <input type="text" class="form-control" name="roll_number" placeholder="Enter roll number">
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                                                        <select class="form-control" name="academic_year" id="academic_year" required>
                                                            <option value="" disabled selected>Select Academic Year</option>
                                                            <?php
                                                            $ay = $conn->query("SELECT * FROM academic_years ORDER BY academic_year DESC");
                                                            while ($y = $ay->fetch_assoc()) {
                                                                echo "<option value='{$y['id']}' data-year='{$y['academic_year']}'>{$y['academic_year']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Class <span class="text-danger">*</span></label>
                                                        <select class="form-control" name="class_id" id="class_id" required>
                                                            <option value="" disabled selected>Select Class</option>
                                                            <?php
                                                            $class = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");
                                                            while ($c = $class->fetch_assoc()) {
                                                                echo "<option value='{$c['id']}'>{$c['class_name']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Section <span class="text-danger">*</span></label>
                                                        <select class="form-control" name="section_id" id="section_id" required>
                                                            <option value="" selected>Select Class First</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Admission Month <span class="text-danger">*</span></label>
                                                        <select class="form-control" name="admission_month" id="admission_month" required>
                                                            <option value="" disabled selected>Select Academic Year First</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- PERSONAL INFORMATION -->
                                        <div class="section-card mb-4">
                                            <div class="section-header mb-3">
                                                <h5 class="mb-2">
                                                    <i class="fas fa-user mr-2 text-primary"></i>Personal Information
                                                </h5>
                                                <p class="text-muted mb-3">Student's personal details</p>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="first_name" required placeholder="Enter first name">
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Last Name</label>
                                                        <input type="text" class="form-control" name="last_name" placeholder="Enter last name">
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                                                        <select class="form-control" name="gender" required>
                                                            <option value="" disabled selected>Select Gender</option>
                                                            <option value="Male">Male</option>
                                                            <option value="Female">Female</option>
                                                            <option value="Other">Other</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control" name="dob" required>
                                                        <small class="form-text text-muted">Student's birth date</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- DOCUMENTS -->
                                        <div class="section-card mb-4">
                                            <div class="section-header mb-3">
                                                <h5 class="mb-2">
                                                    <i class="fas fa-file-alt mr-2 text-primary"></i>Documents
                                                </h5>
                                                <p class="text-muted mb-3">Upload required documents</p>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Student Photo</label>
                                                        <input type="file" class="form-control" name="photo" accept="image/*">
                                                        <small class="form-text text-muted">Max size: 2MB, Formats: JPG, PNG</small>
                                                    </div>
                                                </div>

                                                <div class="col-md-4 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Aadhar Card</label>
                                                        <input type="file" class="form-control" name="aadhar" accept=".pdf,image/*">
                                                        <small class="form-text text-muted">PDF or Image, Max size: 5MB</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- TRANSPORT DETAILS -->
                                        <div class="section-card mb-4">
                                            <div class="section-header mb-3">
                                                <h5 class="mb-2">
                                                    <i class="fas fa-bus mr-2 text-primary"></i>Transport Details
                                                </h5>
                                                <p class="text-muted mb-3">Optional transport information</p>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label d-block">Has Transport?</label>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="has_transport" id="transportYes" value="1">
                                                            <label class="form-check-label" for="transportYes">Yes</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="has_transport" id="transportNo" value="0" checked>
                                                            <label class="form-check-label" for="transportNo">No</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3 transport-field d-none">
                                                    <div class="form-group">
                                                        <label class="form-label">Vehicle Number</label>
                                                        <input type="text" class="form-control" name="vehicle_no" placeholder="Enter vehicle number">
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3 transport-field d-none">
                                                    <div class="form-group">
                                                        <label class="form-label">Pickup Point</label>
                                                        <input type="text" class="form-control" name="pickup_point" placeholder="Enter pickup location">
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3 transport-field d-none">
                                                    <div class="form-group">
                                                        <label class="form-label">Transport Fee (₹)</label>
                                                        <input type="number" class="form-control" name="transport_fee" placeholder="0.00" min="0" step="0.01">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- PARENT / GUARDIAN DETAILS -->
                                        <div class="section-card mb-4">
                                            <div class="section-header mb-3">
                                                <h5 class="mb-2">
                                                    <i class="fas fa-users mr-2 text-primary"></i>Parent / Guardian Details
                                                </h5>
                                                <p class="text-muted mb-3">Parent or guardian information</p>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Father's Name</label>
                                                        <input type="text" class="form-control" name="father_name" placeholder="Enter father's name">
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Mother's Name</label>
                                                        <input type="text" class="form-control" name="mother_name" placeholder="Enter mother's name">
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Parent Phone</label>
                                                        <input type="text" class="form-control" name="parent_phone" placeholder="Enter phone number">
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Parent Email</label>
                                                        <input type="email" class="form-control" name="parent_email" placeholder="Enter email address">
                                                        <small class="form-text text-muted">Will be used for login</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ADDRESS -->
                                        <div class="section-card mb-4">
                                            <div class="section-header mb-3">
                                                <h5 class="mb-2">
                                                    <i class="fas fa-home mr-2 text-primary"></i>Address
                                                </h5>
                                                <p class="text-muted mb-3">Student's residential address</p>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Current Address</label>
                                                        <textarea class="form-control" name="current_address" rows="3" placeholder="Enter current address"></textarea>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Permanent Address</label>
                                                        <textarea class="form-control" name="permanent_address" rows="3" placeholder="Enter permanent address"></textarea>
                                                        <div class="form-check mt-2">
                                                            <input class="form-check-input" type="checkbox" id="sameAsCurrent">
                                                            <label class="form-check-label" for="sameAsCurrent">Same as current address</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ADDITIONAL INFORMATION -->
                                        <div class="section-card mb-4">
                                            <div class="section-header mb-3">
                                                <h5 class="mb-2">
                                                    <i class="fas fa-info-circle mr-2 text-primary"></i>Additional Information
                                                </h5>
                                                <p class="text-muted mb-3">Other relevant details</p>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Previous School</label>
                                                        <textarea class="form-control" name="previous_school" rows="3" placeholder="Enter previous school details"></textarea>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label">Blood Group</label>
                                                        <select class="form-control" name="blood_group">
                                                            <option value="" selected>Select Blood Group</option>
                                                            <option value="A+">A+</option>
                                                            <option value="A-">A-</option>
                                                            <option value="B+">B+</option>
                                                            <option value="B-">B-</option>
                                                            <option value="AB+">AB+</option>
                                                            <option value="AB-">AB-</option>
                                                            <option value="O+">O+</option>
                                                            <option value="O-">O-</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- FORM ACTIONS -->
                                        <div class="border-top pt-4 mt-4">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                                    <i class="fas fa-redo mr-2"></i>Reset Form
                                                </button>
                                                <div>
                                                    <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                                                        <i class="fas fa-save mr-2"></i>Save Student
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- JavaScript -->
                                    <script>
                                        // Academic Year change handler
                                        document.getElementById("academic_year").addEventListener("change", function() {
                                            const selectedOption = this.options[this.selectedIndex];
                                            const selectedYear = parseInt(selectedOption.dataset.year);
                                            const monthSelect = document.getElementById("admission_month");

                                            monthSelect.innerHTML = "";

                                            const now = new Date();
                                            const currentYear = now.getFullYear();
                                            const currentMonth = now.getMonth();

                                            const months = [
                                                "January", "February", "March", "April", "May", "June",
                                                "July", "August", "September", "October", "November", "December"
                                            ];

                                            if (selectedYear < currentYear) {
                                                monthSelect.innerHTML = `<option value="" disabled selected>Invalid Academic Year</option>`;
                                                return;
                                            }

                                            let startMonth = (selectedYear === currentYear) ? currentMonth : 0;

                                            for (let i = startMonth; i < 12; i++) {
                                                const opt = document.createElement("option");
                                                opt.value = `${selectedYear}-${String(i + 1).padStart(2, "0")}`;
                                                opt.textContent = `${months[i]} ${selectedYear}`;
                                                monthSelect.appendChild(opt);
                                            }

                                            // Select current month by default
                                            if (selectedYear === currentYear) {
                                                monthSelect.value = `${selectedYear}-${String(currentMonth + 1).padStart(2, "0")}`;
                                            }
                                        });

                                        // Class change handler for sections
                                        $(document).on("change", "#class_id", function() {
                                            let classId = $(this).val();
                                            $("#section_id").html("<option value=''>Loading sections...</option>");

                                            $.post("../ajax/get_sections.php", {
                                                class_id: classId
                                            }, function(data) {
                                                $("#section_id").html(data);
                                            });
                                        });

                                        // Transport field toggle
                                        $("input[name='has_transport']").change(function() {
                                            if ($(this).val() == "1") {
                                                $(".transport-field").removeClass("d-none");
                                            } else {
                                                $(".transport-field").addClass("d-none");
                                            }
                                        });

                                        // Same as current address
                                        document.getElementById('sameAsCurrent').addEventListener('change', function() {
                                            if (this.checked) {
                                                const currentAddress = document.querySelector('textarea[name="current_address"]').value;
                                                document.querySelector('textarea[name="permanent_address"]').value = currentAddress;
                                            } else {
                                                document.querySelector('textarea[name="permanent_address"]').value = '';
                                            }
                                        });

                                        // Form validation
                                        document.getElementById('admissionForm').addEventListener('submit', function(e) {
                                            const requiredFields = this.querySelectorAll('[required]');
                                            let isValid = true;
                                            let firstInvalidField = null;

                                            requiredFields.forEach(field => {
                                                if (!field.value.trim()) {
                                                    isValid = false;
                                                    if (!firstInvalidField) firstInvalidField = field;
                                                    field.classList.add('is-invalid');
                                                } else {
                                                    field.classList.remove('is-invalid');
                                                }
                                            });

                                            if (!isValid) {
                                                e.preventDefault();
                                                firstInvalidField.scrollIntoView({
                                                    behavior: 'smooth',
                                                    block: 'center'
                                                });
                                                firstInvalidField.focus();

                                                // Show error message
                                                Swal.fire({
                                                    icon: 'warning',
                                                    title: 'Required Fields Missing',
                                                    text: 'Please fill all required fields marked with *',
                                                    confirmButtonColor: '#3085d6'
                                                });
                                            } else {
                                                // Show loading on submit
                                                document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
                                                document.getElementById('submitBtn').disabled = true;
                                            }
                                        });

                                        // Reset form function
                                        function resetForm() {
                                            Swal.fire({
                                                title: 'Reset Form?',
                                                text: 'All entered data will be cleared.',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#d33',
                                                cancelButtonColor: '#6c757d',
                                                confirmButtonText: 'Yes, Reset',
                                                cancelButtonText: 'Cancel'
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    document.getElementById('admissionForm').reset();
                                                    $(".transport-field").addClass("d-none");
                                                    window.scrollTo({
                                                        top: 0,
                                                        behavior: 'smooth'
                                                    });
                                                }
                                            });
                                        }
                                    </script>
                                </div>
                            </div>

                            <style>
                                /* Professional Form Styling */
                                .section-card {
                                    background: #fff;
                                    border-radius: 8px;
                                    padding: 1.25rem;
                                    border: 1px solid #e9ecef;
                                    margin-bottom: 1.5rem;
                                }

                                .section-header {
                                    padding-bottom: 0.75rem;
                                    border-bottom: 1px solid #e9ecef;
                                    margin-bottom: 1rem;
                                }

                                .section-header h5 {
                                    font-weight: 600;
                                    color: #2c3e50;
                                    font-size: 1.1rem;
                                }

                                .section-header p {
                                    font-size: 0.9rem;
                                    margin-bottom: 0;
                                }

                                .form-label {
                                    font-weight: 500;
                                    color: #495057;
                                    margin-bottom: 0.4rem;
                                    font-size: 0.9rem;
                                    display: block;
                                }

                                .form-control {
                                    border: 1px solid #ced4da;
                                    border-radius: 4px;
                                    padding: 0.5rem 0.75rem;
                                    font-size: 0.9rem;
                                    transition: all 0.2s;
                                    height: calc(1.5em + 1rem);
                                }

                                .form-control:focus {
                                    border-color: #80bdff;
                                    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
                                }

                                .form-control.bg-light {
                                    background-color: #f8f9fa !important;
                                    color: #495057;
                                }

                                textarea.form-control {
                                    height: auto;
                                    min-height: 100px;
                                }

                                .form-text {
                                    font-size: 0.8rem;
                                    color: #6c757d;
                                    margin-top: 0.25rem;
                                    display: block;
                                }

                                .form-check-input:checked {
                                    background-color: #007bff;
                                    border-color: #007bff;
                                }

                                .form-check-input:focus {
                                    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
                                }

                                /* Button styling */
                                .btn {
                                    border-radius: 4px;
                                    padding: 0.5rem 1.25rem;
                                    font-weight: 500;
                                    transition: all 0.2s;
                                    font-size: 0.9rem;
                                }

                                .btn-primary {
                                    background-color: #007bff;
                                    border-color: #007bff;
                                }

                                .btn-primary:hover {
                                    background-color: #0069d9;
                                    border-color: #0062cc;
                                    transform: translateY(-1px);
                                }

                                .btn-outline-secondary {
                                    color: #6c757d;
                                    border-color: #6c757d;
                                }

                                .btn-outline-secondary:hover {
                                    background-color: #6c757d;
                                    border-color: #6c757d;
                                    color: #fff;
                                }

                                /* Invalid field styling */
                                .is-invalid {
                                    border-color: #dc3545 !important;
                                }

                                .is-invalid:focus {
                                    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
                                }

                                /* Responsive adjustments */
                                @media (max-width: 768px) {
                                    .section-card {
                                        padding: 1rem;
                                    }

                                    .col-md-3,
                                    .col-md-4,
                                    .col-md-6 {
                                        margin-bottom: 0.75rem;
                                    }

                                    .d-flex.justify-content-between {
                                        flex-direction: column;
                                        gap: 1rem;
                                    }

                                    .btn {
                                        width: 100%;
                                        margin-bottom: 0.5rem;
                                    }
                                }

                                /* File upload styling */
                                input[type="file"] {
                                    padding: 0.375rem 0.75rem;
                                }

                                /* Checkbox and radio alignment */
                                .form-check-inline {
                                    margin-right: 1rem;
                                    margin-top: 0.25rem;
                                }

                                .form-check-label {
                                    margin-bottom: 0;
                                }

                                /* Grid spacing */
                                .row {
                                    margin-right: -0.75rem;
                                    margin-left: -0.75rem;
                                }

                                .row>[class*="col-"] {
                                    padding-right: 0.75rem;
                                    padding-left: 0.75rem;
                                }

                                /* Card body padding adjustment */
                                .card-body {
                                    padding: 1.5rem;
                                }

                                @media (max-width: 576px) {
                                    .card-body {
                                        padding: 1rem;
                                    }
                                }
                            </style>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include "../includes/footer.php" ?>
    </div>
    <?php include "../includes/js_links.php" ?>



    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    include('../config/database.php');
    include('../includes/alert_helper.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // ===== Sanitize Inputs =====
        $admission_no      = $conn->real_escape_string($_POST['admission_no']);
        $roll_number       = $conn->real_escape_string($_POST['roll_number']);
        $class_id          = (int)$_POST['class_id'];
        $section_id        = (int)$_POST['section_id'];
        $academic_year_id  = (int)$_POST['academic_year'];
        $first_name        = $conn->real_escape_string($_POST['first_name']);
        $last_name         = $conn->real_escape_string($_POST['last_name']);
        $gender            = $conn->real_escape_string($_POST['gender']);

        // DOB Validate
        $admission_month = trim($_POST['admission_month'] ?? '');

        // Validate format YYYY-MM
        if (!preg_match('/^\d{4}-\d{2}$/', $admission_month)) {
            sweetAlert("Error", "Invalid Admission Month", "error");
            exit;
        }

        // Force day = 01
        $admission_date = $admission_month . '-01'; // YYYY-MM-01
        $dob_raw = trim($_POST['dob']);
        $dob = date('Y-m-d', strtotime($dob_raw));
        if (!strtotime($dob_raw)) {
            sweetAlert("Error", "❌ Invalid Date of Birth!", "error");
            exit;
        }

        $has_transport = isset($_POST['has_transport']) && $_POST['has_transport'] == "1" ? 1 : 0;
        $vehicle_no        = $conn->real_escape_string($_POST['vehicle_no']);
        $pickup_point      = $conn->real_escape_string($_POST['pickup_point']);
        $transport_fee     = isset($_POST['transport_fee']) ? floatval($_POST['transport_fee']) : 0.00;
        $transport_fees    = isset($_POST['transport_fees']) ? intval($_POST['transport_fees']) : 0;

        $father_name       = $conn->real_escape_string($_POST['father_name']);
        $mother_name       = $conn->real_escape_string($_POST['mother_name']);
        $parent_phone      = $conn->real_escape_string($_POST['parent_phone']);
        $parent_email      = $conn->real_escape_string($_POST['parent_email']);
        $current_address   = $conn->real_escape_string($_POST['current_address']);
        $permanent_address = $conn->real_escape_string($_POST['permanent_address']);
        $previous_school   = $conn->real_escape_string($_POST['previous_school'] ?? '');
        $blood_group       = $conn->real_escape_string($_POST['blood_group'] ?? '');

        if (!$class_id || !$section_id || !$academic_year_id) {
            sweetAlert("Error", "Class / Section / Academic Year Required!", "error");
            exit;
        }

        // File Upload Handler
        function uploadFile($fileInput, $folder)
        {
            if (!empty($_FILES[$fileInput]['name'])) {
                $ext = pathinfo($_FILES[$fileInput]['name'], PATHINFO_EXTENSION);
                $filePath = $folder . uniqid() . "." . $ext;
                if (!is_dir("../" . $folder)) mkdir("../" . $folder, 0777, true);
                move_uploaded_file($_FILES[$fileInput]['tmp_name'], "../" . $filePath);
                return $filePath;
            }
            return '';
        }

        $photo  = uploadFile('photo', 'uploads/academics/photos/');
        $aadhar = uploadFile('aadhar', 'uploads/academics/aadhar/');

        // Generate Student ID
        $year = date('Y');
        $q = $conn->query("SELECT student_id FROM students WHERE student_id LIKE '$year/%' ORDER BY id DESC LIMIT 1");

        if ($q->num_rows > 0) {
            $last = $q->fetch_assoc();
            $newNumber = ((int)substr($last['student_id'], 5)) + 1;
        } else {
            $newNumber = 1;
        }
        $student_id = $year . "/" . $newNumber;

       // ===== Handle Guardian User (FIXED) =====
$guardian_user_id = NULL;
$password = "12345";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

if (!empty($parent_email)) {

    $stmt = $conn->prepare(
        "SELECT id FROM users WHERE email = ? AND role = 'parent' LIMIT 1"
    );
    $stmt->bind_param("s", $parent_email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $guardian_user_id = $res->fetch_assoc()['id'];
    } else {
        $password = "12345";
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $parent_name = trim($_POST['father_name'] . " " . $_POST['mother_name']);

        $stmtInsert = $conn->prepare(
            "INSERT INTO users (name, phone, email, password, plain_password, role)
             VALUES (?, ?, ?, ?, ?, 'parent')"
        );
        $stmtInsert->bind_param(
            "sssss",
            $parent_name,
            $parent_phone,
            $parent_email,
            $hashed_password,
            $password
        );

        $stmtInsert->execute();
        $guardian_user_id = $stmtInsert->insert_id;
    }
}

echo "$guardian_user_id";
        // Prepare guardian_user_id for SQL query
        $guardian_user_id_sql = is_numeric($guardian_user_id) ? $guardian_user_id : "NULL";

        // ================= Insert Student Normally =================
        $sql = "INSERT INTO students (
        student_id, admission_no, roll_number, class_id, section_id, academic_year_id,
        first_name, last_name, gender, dob,
        has_transport, vehicle_no, pickup_point, transport_fee, transport_fees,
        father_name, mother_name, parent_phone, parent_email,
        current_address, permanent_address, previous_school, blood_group,
        photo, aadhar, guardian_user_id, admission_date, created_at
    ) VALUES (
        '$student_id', '$admission_no', '$roll_number', $class_id, $section_id, $academic_year_id,
        '$first_name', '$last_name', '$gender', '$dob',
        $has_transport, '$vehicle_no', '$pickup_point', $transport_fee, $transport_fees,
        '$father_name', '$mother_name', '$parent_phone', '$parent_email',
        '$current_address', '$permanent_address', '$previous_school', '$blood_group',
        '$photo', '$aadhar', $guardian_user_id_sql, Now(), '$admission_date'
    )";

        if ($conn->query($sql)) {
            // SUCCESS ALERT

            sweetAlert("Success", "Student added!", "success", "add_student.php");

            exit;
        } else {
            // ERROR ALERT
            $error = addslashes($conn->error);
            echo "
    <script>
    Swal.fire({
        title: 'Error',
        text: 'Server Error: $error',
        icon: 'error'
    });
    </script>
    ";
            exit;
        }
    } ?>