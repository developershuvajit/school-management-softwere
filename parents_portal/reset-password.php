<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit();
}


$success_message = '';
$error_message = '';
$user_id = $_SESSION['user_id'];


include('../config/database.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $new_password     = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Basic validation
    if (empty($new_password) || empty($confirm_password)) {
        $error_message = "Both fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } else {

        // 1️⃣ Fetch existing password hash
        $stmt = $conn->prepare("SELECT password, plain_password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            $error_message = "User not found.";
            $stmt->close();
        } else {
            $user = $result->fetch_assoc();
            $stmt->close();

            // 2️⃣ Prevent reusing old password
            if (password_verify($new_password, $user['password'])) {
                $error_message = "New password cannot be the same as the old password.";
            } else {

                // 3️⃣ Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                $update_stmt = $conn->prepare(
                    "UPDATE users SET password = ?, plain_password = ? WHERE id = ?"
                );
                $update_stmt->bind_param("ssi", $hashed_password, $new_password, $user_id);

                if ($update_stmt->execute()) {

                    // 5️⃣ Regenerate session ID for security
                    session_regenerate_id(true);

                    $success_message = "Password has been reset successfully!";
                    $_POST = []; // clear form values

                } else {
                    $error_message = "Failed to update password. Please try again.";
                }

                $update_stmt->close();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Parent Portal - Set New Password</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../public/images/favicon.png">
    <link href="../public/vendor/pg-calendar/css/pignose.calendar.min.css" rel="stylesheet">
    <link href="../public/vendor/chartist/css/chartist.min.css" rel="stylesheet">
    <link href="../public/css/style.css" rel="stylesheet">
    <style>
        .reset-card {
            max-width: 500px;
            margin: 0 auto;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .reset-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #007bff, #6610f2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .reset-icon i {
            font-size: 36px;
            color: white;
        }

        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .strength-weak {
            background-color: #dc3545;
            width: 25%;
        }

        .strength-fair {
            background-color: #ffc107;
            width: 50%;
        }

        .strength-good {
            background-color: #28a745;
            width: 75%;
        }

        .strength-strong {
            background-color: #007bff;
            width: 100%;
        }

        .toggle-password {
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .form-group {
            position: relative;
        }

        .form-control {
            padding-right: 40px;
        }
    </style>
</head>

<body>
    <div id="main-wrapper">
        <?php include "../includes/navbar.php" ?>
        <?php include('../includes/sidebar_logic.php') ?>

        <div class="content-body text-dark">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="row page-titles mx-0">
                    <div class="col-sm-12 p-md-0">
                        <div class="welcome-text">
                            <h4>Set New Password</h4>
                            <p class="mb-0">Set a new password for your account</p>
                        </div>
                    </div>
                </div>

                <!-- Password Reset Card -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card reset-card border-0">
                            <div class="card-body p-5">
                                <!-- Reset Icon -->
                                <div class="reset-icon">
                                    <i class="ti-key"></i>
                                </div>

                                <!-- Messages -->
                                <?php if ($success_message): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="ti-check mr-2"></i>
                                        <?php echo $success_message; ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <?php if ($error_message): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="ti-alert mr-2"></i>
                                        <?php echo $error_message; ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <!-- Password Reset Form -->
                                <form method="POST" action="" id="resetPasswordForm">
                                    <div class="form-group">
                                        <label for="new_password" class="font-weight-bold">New Password</label>
                                        <div class="position-relative">
                                            <input type="password"
                                                class="form-control"
                                                id="new_password"
                                                name="new_password"
                                                placeholder="Enter new password"
                                                required
                                                minlength="6">
                                            <span class="toggle-password" onclick="togglePassword('new_password')">
                                                <i class="ti-eye"></i>
                                            </span>
                                        </div>
                                        <div id="passwordStrength" class="password-strength"></div>
                                        <small class="form-text text-muted">Minimum 6 characters</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="confirm_password" class="font-weight-bold">Confirm Password</label>
                                        <div class="position-relative">
                                            <input type="password"
                                                class="form-control"
                                                id="confirm_password"
                                                name="confirm_password"
                                                placeholder="Confirm new password"
                                                required
                                                minlength="6">
                                            <span class="toggle-password" onclick="togglePassword('confirm_password')">
                                                <i class="ti-eye"></i>
                                            </span>
                                        </div>
                                        <div id="passwordMatch" class="mt-2 small"></div>
                                    </div>

                                    <div class="form-group mt-4">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                                            <i class="ti-save mr-2"></i> Set New Password
                                        </button>
                                    </div>

                                    <div class="text-center mt-3">
                                        <a href="dashboard.php" class="text-primary">
                                            <i class="ti-arrow-left mr-1"></i> Back to Dashboard
                                        </a>
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

    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling.querySelector('i');

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('ti-eye');
                icon.classList.add('ti-eye-off');
            } else {
                field.type = 'password';
                icon.classList.remove('ti-eye-off');
                icon.classList.add('ti-eye');
            }
        }

        // Check password strength
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            const confirmField = document.getElementById('confirm_password');
            const matchIndicator = document.getElementById('passwordMatch');

            let strength = 0;

            // Length check
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;

            // Complexity checks
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            // Update strength bar
            strengthBar.className = 'password-strength';
            if (password.length === 0) {
                strengthBar.style.width = '0%';
            } else if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength === 3) {
                strengthBar.classList.add('strength-fair');
            } else if (strength === 4) {
                strengthBar.classList.add('strength-good');
            } else {
                strengthBar.classList.add('strength-strong');
            }

            // Check password match if confirm field has value
            if (confirmField.value) {
                checkPasswordMatch();
            }
        });

        // Check password match
        document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);

        function checkPasswordMatch() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchIndicator = document.getElementById('passwordMatch');

            if (confirmPassword.length === 0) {
                matchIndicator.innerHTML = '';
                matchIndicator.className = '';
            } else if (password === confirmPassword) {
                matchIndicator.innerHTML = '<i class="ti-check text-success mr-1"></i> Passwords match';
                matchIndicator.className = 'text-success';
            } else {
                matchIndicator.innerHTML = '<i class="ti-close text-danger mr-1"></i> Passwords do not match';
                matchIndicator.className = 'text-danger';
            }
        }

        // Form validation
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            const password = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                return false;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match. Please confirm your password.');
                return false;
            }
        });

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bootstrapAlert = new bootstrap.Alert(alert);
                bootstrapAlert.close();
            });
        }, 5000);
    </script>
</body>

</html>