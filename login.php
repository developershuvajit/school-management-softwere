<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School Management Software</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0a5cff;
            --primary-light: #3b82f6;
            --accent: #10b981;
            --text: #1e2937;
            --muted: #64748b;
            --bg: #f8fafc;
            --card: #ffffff;
            --border: #e2e8f0;
            --radius: 16px;
            --shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f1f5f9 0%, #e0e7ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 460px;
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid var(--border);
        }

        /* Header */
        .login-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .school-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .logo-icon {
            background: rgba(255,255,255,0.95);
            color: var(--primary);
            width: 58px;
            height: 58px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        .logo-text {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .logo-text span {
            color: #fff;
        }

        .login-header h1 {
            font-size: 1.85rem;
            margin-bottom: 8px;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 1rem;
        }

        /* Body */
        .login-body {
            padding: 40px 35px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text);
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(10, 92, 255, 0.12);
            outline: none;
        }

        .input-group {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 1.1rem;
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            padding: 16px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(10, 92, 255, 0.25);
        }

        .login-footer {
            text-align: center;
            margin-top: 28px;
            font-size: 0.95rem;
            color: var(--muted);
        }

        .login-footer a {
            color: var(--primary);
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .additional-info {
            margin-top: 32px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 0.92rem;
        }

        .additional-info h3 {
            color: var(--primary);
            margin-bottom: 12px;
            font-size: 1.1rem;
        }

        .additional-info ul {
            padding-left: 22px;
            color: var(--muted);
        }

        .additional-info li {
            margin-bottom: 6px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-body {
                padding: 30px 25px;
            }
            .login-header {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <div class="school-logo">
                <div class="logo-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="logo-text">School <span>India</span></div>
            </div>
            <h1>Welcome Back</h1>
            <p>Sign in to access your school dashboard</p>
        </div>

        <!-- Body -->
        <div class="login-body">
            <form method="post" action="actions/login_script.php" id="loginForm">
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-envelope" style="margin-right:8px;"></i> Email Address
                    </label>
                    <input type="email" name="email" class="form-control" required autofocus placeholder="Enter your registered email">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock" style="margin-right:8px;"></i> Password
                    </label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" required placeholder="Enter your password">
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    Sign In
                </button>

                <div class="login-footer">
                    <a href="#">Forgot Password?</a> &nbsp;•&nbsp; 
                    <a href="#">Contact Support</a>
                </div>
            </form>

            <!-- Login Instructions -->
            <div class="additional-info">
                <h3>Login Guidelines</h3>
                <ul>
                    <li><strong>Students:</strong> Use your school-issued email</li>
                    <li><strong>Parents:</strong> Use the email registered during admission</li>
                    <li><strong>Teachers & Staff:</strong> Use your official school email</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Password Toggle Functionality
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Optional: Disable button on submit to prevent double submission
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.innerHTML = `
                <span style="display:flex;align-items:center;justify-content:center;gap:8px;">
                    <i class="fas fa-spinner fa-spin"></i> Signing In...
                </span>`;
            btn.disabled = true;
        });
    </script>

</body>
</html>