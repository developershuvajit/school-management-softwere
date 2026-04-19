<?php include "includes/head.php" ?>label


<?php include "includes/preloader.php" ?>


<div id="main-wrapper">

    <?php include "includes/navbar.php" ?>

    <?php include "includes/sidebar.php" ?>

    <div class="content-body">
        <div class="container-fluid">

            <!-- row -->
            <div class="row">
                <div class="col-xl-12 col-xxl-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Student Admission</h4>
                            <button class="btn btn-secondary float-right">
                                <i class="fa fa-upload"></i> Import Student
                            </button>
                        </div>
                        <div class="card-body">
                            <form id="studentAdmissionForm" method="POST" action="submit_admission.php"
                                enctype="multipart/form-data">
                                <!-- Admission Info -->
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label>Admission No <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="admission_no" required>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Roll Number</label>
                                        <input type="text" class="form-control" name="roll_number">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Class <span class="text-danger">*</span></label>
                                        <select class="form-control" name="class" required>
                                            <option selected disabled>Select</option>
                                            <option>Nursery</option>
                                            <option>KG</option>
                                            <option>1</option>
                                            <option>2</option>
                                            <!-- Add all classes -->
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Section <span class="text-danger">*</span></label>
                                        <select class="form-control" name="section" required>
                                            <option selected disabled>Select</option>
                                            <option>A</option>
                                            <option>B</option>
                                            <option>C</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Personal Info -->
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label>First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="first_name" required>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Last Name</label>
                                        <input type="text" class="form-control" name="last_name">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Gender <span class="text-danger">*</span></label>
                                        <select class="form-control" name="gender" required>
                                            <option selected disabled>Select</option>
                                            <option>Male</option>
                                            <option>Female</option>
                                            <option>Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Date Of Birth <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="dob" required>
                                    </div>
                                </div>

                                <!-- Additional Info -->
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label>Category</label>
                                        <select class="form-control" name="category">
                                            <option selected disabled>Select</option>
                                            <option>General</option>
                                            <option>OBC</option>
                                            <option>SC</option>
                                            <option>ST</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Religion</label>
                                        <input type="text" class="form-control" name="religion">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Caste</label>
                                        <input type="text" class="form-control" name="caste">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Mobile Number</label>
                                        <input type="tel" class="form-control" name="mobile">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label>Email</label>
                                        <input type="email" class="form-control" name="email">
                                    </div>

                                    <div class="form-group col-md-3">
                                        <label>Admission Date</label>
                                        <input type="date" name="datepicker" class=" form-control"
                                            id="">

                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Student Photo (100x100px)</label>
                                        <input type="file" class="form-control-file" name="student_photo">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Blood Group</label>
                                        <select class="form-control" name="blood_group">
                                            <option selected disabled>Select</option>
                                            <option>A+</option>
                                            <option>B+</option>
                                            <option>O+</option>
                                            <option>AB+</option>
                                            <!-- Add other groups -->
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">

                                    <div class="form-group col-md-3">
                                        <label>Height</label>
                                        <input type="text" class="form-control" name="height">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Weight</label>
                                        <input type="text" class="form-control" name="weight">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Measurement Date</label>
                                        <input type="date" class="form-control" name="measurement_date"
                                            value="<?= date('Y-m-d'); ?>">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Medical History</label>
                                    <textarea class="form-control" name="medical_history" rows="2"></textarea>
                                </div>

                                <!-- Transport Details -->
                                <h5>Transport Details</h5>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Route List</label>
                                        <select class="form-control" name="route">
                                            <option selected disabled>Select</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Pickup Point</label>
                                        <select class="form-control" name="pickup_point">
                                            <option selected disabled>Select</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Fees Month</label>
                                        <select class="form-control" name="fees_month">
                                            <option selected disabled>Select Month</option>
                                            <option>January</option>
                                            <option>February</option>
                                            <option>March</option>
                                            <!-- Add all months -->
                                        </select>
                                    </div>
                                </div>


                                <!-- ============ Parent / Guardian Details ============ -->
                                <h5 class="mt-4">Parent / Guardian Details</h5>
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label>Father Name</label>
                                        <input type="text" class="form-control" name="father_name">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Father Phone</label>
                                        <input type="tel" class="form-control" name="father_phone">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Father Occupation</label>
                                        <input type="text" class="form-control" name="father_occupation">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Father Photo (100x100px)</label>
                                        <input type="file" class="form-control-file" name="father_photo">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label>Mother Name</label>
                                        <input type="text" class="form-control" name="mother_name">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Mother Phone</label>
                                        <input type="tel" class="form-control" name="mother_phone">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Mother Occupation</label>
                                        <input type="text" class="form-control" name="mother_occupation">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Mother Photo (100x100px)</label>
                                        <input type="file" class="form-control-file" name="mother_photo">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label>If Guardian Is</label>
                                        <select class="form-control" name="guardian_is">
                                            <option selected disabled>Select</option>
                                            <option>Father</option>
                                            <option>Mother</option>
                                            <option>Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Guardian Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="guardian_name" required>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Guardian Relation</label>
                                        <input type="text" class="form-control" name="guardian_relation">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Guardian Email</label>
                                        <input type="email" class="form-control" name="guardian_email">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label>Guardian Phone</label>
                                        <input type="tel" class="form-control" name="guardian_phone">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Guardian Occupation</label>
                                        <input type="text" class="form-control" name="guardian_occupation">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Guardian Address</label>
                                        <input type="text" class="form-control" name="guardian_address">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Guardian Photo (100x100px)</label>
                                        <input type="file" class="form-control-file" name="guardian_photo">
                                    </div>
                                </div>

                                <!-- ============ Student Address Details ============ -->
                                <h5 class="mt-4">Student Address Details</h5>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="same_as_guardian"
                                                id="sameAsGuardian">
                                            <label class="form-check-label" for="sameAsGuardian">
                                                If Guardian Address is Current Address
                                            </label>
                                        </div>
                                        <label>Current Address</label>
                                        <textarea class="form-control" name="current_address" rows="2"></textarea>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="same_as_current"
                                                id="sameAsCurrent">
                                            <label class="form-check-label" for="sameAsCurrent">
                                                If Permanent Address is Current Address
                                            </label>
                                        </div>
                                        <label>Permanent Address</label>
                                        <textarea class="form-control" name="permanent_address" rows="2"></textarea>
                                    </div>
                                </div>

                                <!-- ============ Miscellaneous Details ============ -->
                                <h5 class="mt-4">Miscellaneous Details</h5>
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label>Bank Account Number</label>
                                        <input type="text" class="form-control" name="bank_account_number">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Bank Name</label>
                                        <input type="text" class="form-control" name="bank_name">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>IFSC Code</label>
                                        <input type="text" class="form-control" name="ifsc_code">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>RTE</label><br>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="rte" value="Yes"> Yes
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="rte" value="No"> No
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>National Identification Number</label>
                                        <input type="text" class="form-control" name="national_id">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Local Identification Number</label>
                                        <input type="text" class="form-control" name="local_id">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Previous School Detail</label>
                                        <textarea class="form-control" name="previous_school" rows="1"></textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Note</label>
                                    <textarea class="form-control" name="note" rows="2"></textarea>
                                </div>

                                <!-- ============ Upload Documents ============ -->
                                <h5 class="mt-4">Upload Documents</h5>
                                <div class="form-row">
                                    <div class="form-group col-md-6 d-flex align-items-center">
                                        <input type="text" class="form-control mr-2" name="doc_title_1"
                                            placeholder="Title 1">
                                        <input type="file" class="form-control-file" name="doc_file_1">
                                    </div>
                                    <div class="form-group col-md-6 d-flex align-items-center">
                                        <input type="text" class="form-control mr-2" name="doc_title_2"
                                            placeholder="Title 2">
                                        <input type="file" class="form-control-file" name="doc_file_2">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6 d-flex align-items-center">
                                        <input type="text" class="form-control mr-2" name="doc_title_3"
                                            placeholder="Title 3">
                                        <input type="file" class="form-control-file" name="doc_file_3">
                                    </div>
                                    <div class="form-group col-md-6 d-flex align-items-center">
                                        <input type="text" class="form-control mr-2" name="doc_title_4"
                                            placeholder="Title 4">
                                        <input type="file" class="form-control-file" name="doc_file_4">
                                    </div>
                                </div>

                                <!-- ============ Final Submit ============ -->
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>



                                <button type="submit" class="btn btn-primary">Submit Admission</button>
                            </form>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <?php include "includes/footer.php" ?>


</div>
<?php include "includes/js_links.php" ?>