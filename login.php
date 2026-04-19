<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School India Junior - Login Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        :root {
            --primary: #1a4b8c;
            --secondary: #f9a826;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
        }
        
        body {
            background: linear-gradient(135deg, #1a4b8c, #2a5da8);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .login-header {
            background-color: var(--primary);
            color: white;
            padding: 30px 25px;
            text-align: center;
        }
        
        .school-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .logo-icon {
            background-color: white;
            color: var(--primary);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 24px;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: 700;
        }
        
        .logo-text span {
            color: var(--secondary);
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary);
        }
        
        .form-label i {
            margin-right: 8px;
            width: 20px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 75, 140, 0.2);
        }
        
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 1rem;
        }
        
        .mb-4 {
            margin-bottom: 1.5rem;
        }
        
        .d-grid {
            display: grid;
        }
        
        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #153a6b;
        }
        
        .btn-primary {
            background-color: var(--primary);
        }
        
        .btn-lg {
            padding: 14px;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        .login-footer a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .additional-info {
            margin-top: 25px;
            padding: 15px;
            background-color: #f0f5ff;
            border-radius: 5px;
            font-size: 0.9rem;
            color: var(--dark);
        }
        
        .additional-info h3 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .additional-info ul {
            padding-left: 20px;
        }
        
        .additional-info li {
            margin-bottom: 5px;
        }
        
        @media (max-width: 480px) {
            .login-container {
                margin: 0 10px;
            }
            
            .login-header {
                padding: 20px 15px;
            }
            
            .login-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="school-logo">
                <div class="logo-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="logo-text">School <span>India Junior</span></div>
            </div>
            <h1>Login Portal</h1>
            <p>Access your academic information and resources</p>
        </div>
        
        <div class="login-body">
            <form method="post" action="actions/login_script.php" id="loginForm">
                <div class="mb-4">
                    <label class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" name="email" class="form-control" required autofocus placeholder="Enter your email">
                </div>
                
                <div class="mb-4">
                    <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" required placeholder="Enter your password">
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="d-grid mb-3">
                    <button class="btn btn-primary btn-lg" type="submit" id="loginBtn">
                        <span id="btnText">Login</span>
                    </button>
                </div>
                
                <div class="login-footer">
                    <a href="#">Forgot your password?</a> • <a href="#">Contact Support</a>
                </div>
            </form>
            
            <div class="additional-info">
                <h3>Login Instructions</h3>
                <ul>
                    <li>Students: Use your school-issued email address</li>
                    <li>Parents: Use the email registered with the school</li>
                    <li>Faculty: Use your official school email</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
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
        
        
    </script>
</body>
</html>