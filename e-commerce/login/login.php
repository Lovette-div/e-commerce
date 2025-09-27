<?php
// Start session to check if user is already logged in
session_start();

// If user is already logged in, redirect to homepage
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - Launder</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .card-header h2 {
            margin: 0;
            font-size: 2rem;
            font-weight: 300;
        }
        
        .card-header p {
            margin: 0.5rem 0 0;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-login:disabled {
            transform: none;
            box-shadow: none;
            background: #6c757d;
        }
        
        .register-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
        }
        
        .password-toggle:hover {
            color: #667eea;
        }
        
        .password-input-container {
            position: relative;
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
        
        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
        
        .back-link a {
            color: white;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 15px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        
        .back-link a:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Back to Home Link -->
    <div class="back-link">
        <a href="../index.php">
            <i class="fas fa-arrow-left me-2"></i>Back to Home
        </a>
    </div>

    <div class="login-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="login-card">
                        <div class="card-header">
                            <h2><i class="fas fa-sign-in-alt me-2"></i>Welcome Back</h2>
                            <p>Sign in to your ShoppN account</p>
                        </div>
                        <div class="card-body">
                            <!-- Success Message Container -->
                            <div id="successMessage"></div>
                            
                            <!-- Error Message Container -->
                            <div id="errorMessages"></div>
                            
                            <!-- Login Form -->
                            <form id="loginForm" novalidate>
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required maxlength="50">
                                    <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                                    <div id="emailFeedback"></div>
                                </div>
                                
                                <div class="password-input-container">
                                    <div class="form-floating">
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                        <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                                        <button type="button" class="password-toggle" id="togglePassword">
                                            <i class="fas fa-eye-slash"></i>
                                        </button>
                                    </div>
                                    <div id="passwordFeedback"></div>
                                </div>
                                
                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-secondary btn-lg btn-login" id="loginBtn">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login
                                    </button>
                                </div>
                            </form>
                            
                            <div class="register-link">
                                <p>Don't have an account? <a href="register.php"><i class="fas fa-user-plus me-1"></i>Register here</a></p>
                                <!-- <p><a href="index.php"><i class="fas fa-home me-1"></i>Back to Homepage</a></p> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Login JS -->
    <script src="/e-commerce/js/login.js"></script>
</body>
</html>