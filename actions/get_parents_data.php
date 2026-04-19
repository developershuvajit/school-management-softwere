<?php
session_start();
require_once('../config/database.php');

$search = $_POST['search'] ?? '';
$class_id = $_POST['class_id'] ?? 0;
$academic_year = $_POST['academic_year'] ?? '';

$whereConditions = [];

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $whereConditions[] = "(
        s.first_name LIKE '%$search%' OR 
        s.last_name LIKE '%$search%' OR 
        s.father_name LIKE '%$search%' OR 
        s.mother_name LIKE '%$search%' OR 
        s.parent_phone LIKE '%$search%'
    )";
}

if ($class_id > 0) {
    $whereConditions[] = "s.class_id = $class_id";
}

if (!empty($academic_year)) {
    $academic_year = $conn->real_escape_string($academic_year);
    $whereConditions[] = "ay.academic_year = '$academic_year'";
}

$where = !empty($whereConditions)
    ? 'WHERE ' . implode(' AND ', $whereConditions)
    : '';

if ($class_id > 0) {
    $whereConditions[] = "s.class_id = $class_id";
}
if (!empty($academic_year)) {
    $academic_year = $conn->real_escape_string($academic_year);
    $whereConditions[] = "ay.academic_year = '$academic_year'";
}

$where = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

$sql = "SELECT 
            s.*, 
            c.class_name, 
            sec.section_name,
            ay.academic_year
        FROM students s 
        LEFT JOIN classes c ON s.class_id = c.id 
        LEFT JOIN sections sec ON s.section_id = sec.id 
        LEFT JOIN academic_years ay ON ay.id = s.academic_year_id
        $where
        ORDER BY ay.academic_year DESC, s.first_name, s.last_name";

$result = $conn->query($sql);
$i = 1;

if ($result && $result->num_rows > 0):
?>
    <div class="table-responsive">
        <table class="display table table-hover align-middle" style="width:100%">
            <thead class="text-center bg-light text-dark">
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Parent Details</th>
                    <th>Contact Information</th>
                    <th>Portal Credentials</th>
                    <th>Class/Section</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()):
                    $id = (int)$row['id'];
                    $studentName = htmlspecialchars(trim($row['first_name'] . ' ' . $row['last_name']));
                    $rollNumber = htmlspecialchars($row['roll_number'] ?? 'N/A');
                    $admissionNo = htmlspecialchars($row['admission_no'] ?? 'N/A');
                    $fatherName = htmlspecialchars($row['father_name'] ?? 'Not Provided');
                    $motherName = htmlspecialchars($row['mother_name'] ?? 'Not Provided');
                    $parentPhone = htmlspecialchars($row['parent_phone'] ?? 'Not Provided');
                    $parentEmail = htmlspecialchars($row['parent_email'] ?? 'Not Provided');
                    $guardianUserId = htmlspecialchars($row['guardian_user_id'] ?? 'N/A');
                    $portalUsername = !empty($parentEmail) ? $parentEmail : ($parentPhone ? $parentPhone . '@schoolportal.com' : 'N/A');

                    // Get password
                    $password = "Not Set";
                    if (!empty($parentEmail)) {
                        $pass_sql = "SELECT plain_password FROM users WHERE email = '$parentEmail' LIMIT 1";
                        $pass_results = $conn->query($pass_sql);
                        if ($pass_results && $pass_results->num_rows > 0) {
                            $pass_row = $pass_results->fetch_assoc();
                            $password = $pass_row['plain_password'];
                        }
                    }
                    $portalPassword = !empty($password) ? $password : 'Not Set';

                    $className = htmlspecialchars($row['class_name'] ?? 'N/A');
                    $sectionName = htmlspecialchars($row['section_name'] ?? 'N/A');
                    $hasTransport = $row['has_transport'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>';
                ?>
                    <tr class='parent-row' data-class='<?= $row['class_id'] ?>'>
                        <td class='text-center text-dark'><input type="checkbox" class="student-check mr-2" value="<?= $row['student_id']; ?>"><?= $i;  ?></td>
                        <td>
                            <div class='d-flex align-items-center'>
                                <div>
                                    <strong class='text-dark'><?= $studentName ?></strong><br>
                                    <small class='text-muted'>Roll: <?= $rollNumber ?></small><br>
                                    <small class='text-muted'>Admission: <?= $admissionNo ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class='mb-2'>
                                <span class='parent-info-label'>Father:</span>
                                <span class='text-dark'><?= $fatherName ?></span>
                            </div>
                            <div>
                                <span class='parent-info-label'>Mother:</span>
                                <span class='text-dark'><?= $motherName ?></span>
                            </div>
                            <div class='mt-2'>
                                <small class='text-muted'>Guardian ID: <?= $guardianUserId ?></small>
                            </div>
                        </td>
                        <td>
                            <div class='mb-2'>
                                <i class='fas fa-phone text-success me-2'></i>
                                <span class='text-dark'><?= $parentPhone ?></span>
                            </div>
                            <div>
                                <i class='fas fa-envelope text-primary me-2'></i>
                                <span class='text-dark'><?= $parentEmail ?></span>
                            </div>
                            <div class='mt-2 text-white'>
                                <?= $hasTransport ?>
                                <?= $row['has_transport'] ? "<br><small class='text-dark'>Vehicle: " . htmlspecialchars($row['vehicle_no'] ?? 'N/A') . "</small>" : "" ?>
                            </div>
                        </td>
                        <td>
                            <div class='credentials-box'>
                                <div class='mb-2'>
                                    <div class='credential-label'>Username/Email:</div>
                                    <div class='d-flex justify-content-between align-items-center'>
                                        <code id='username-<?= $id ?>'><?= $portalUsername ?></code>
                                        <i class='fas fa-copy copy-btn text-info' onclick='copyToClipboard("username-<?= $id ?>")' title='Copy Username'></i>
                                    </div>
                                </div>
                                <div>
                                    <div class='credential-label'>Password:</div>
                                    <div class='d-flex justify-content-between align-items-center'>
                                        <code id='password-<?= $id ?>' data-password='<?= $portalPassword ?>'><?= $portalPassword ?></code>
                                        <div>
                                            <i class='fas fa-copy copy-btn text-info me-2' onclick='copyToClipboard("password-<?= $id ?>")' title='Copy Password'></i>
                                            <i class='fas fa-eye show-password' onclick='togglePassword(<?= $id ?>)' title='Show Password'></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class='text-center'>
                            <div class='fw-bold text-dark'><?= $className ?></div>
                            <div class='text-muted'><?= $sectionName ?></div>
                            <div class='mt-2'>
                                <small class='text-muted'>DOB: <?= date('d-m-Y', strtotime($row['dob'])) ?></small>
                            </div>
                        </td>
                        <td class='text-center'>
                            <div class='action-buttons'>
                                <button type='button' class='btn btn-sm btn-primary generateSingleParent' data-student-id='<?= $row['student_id'] ?>'>
                                    <i class='fa fa-id-card'></i> Parent ID Card
                                </button>
                               
                            </div>
                        </td>
                    </tr>
                <?php $i++;
                endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="text-center py-5">
        <i class="fas fa-users fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">No parents found</h4>
        <p class="text-muted">Try adjusting your search or filter criteria</p>
    </div>
<?php endif; ?>