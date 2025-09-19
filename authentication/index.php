<?php
// Start session to check if customer is logged in
session_start();

// Check if customer is logged in (for future implementation)
$isLoggedIn = isset($_SESSION['customer_id']) && !empty($_SESSION['customer_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShoppN - Online Shopping Platform</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .btn-custom {
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-light:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.3);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .feature-card {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .feature-icon {
            font-size: 3rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .welcome-message {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        footer {
            background: #343a40;
            color: white;
            padding: 20px 0;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-shopping-bag me-2 text-primary"></i>
                ShoppN
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    
                    
                    <?php if (!$isLoggedIn): ?>
                        <!-- Show when customer is NOT logged in -->
                        <li class="nav-item">
                            <a class="nav-link" href="login/register.php">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login/login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- Show when customer IS logged in (for future implementation) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                Welcome, <?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'Customer'); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle me-1"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-shopping-cart me-1"></i>My Cart</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-box me-1"></i>My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt me-1"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-shopping-bag me-3"></i>
                        Welcome to ShoppN
                    </h1>
                    <p class="lead mb-5">Your ultimate online shopping destination. Discover amazing products, great deals, and exceptional service.</p>
                    
                    
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container my-5">
        <?php if (!$isLoggedIn): ?>
           
        <?php endif; ?>

        <!-- Features Section -->
        <div class="row mb-5">
            <div class="col-12 text-center mb-5">
                <h2 class="fw-bold">Why Choose ShoppN?</h2>
                <p class="text-muted">Discover what makes us the preferred choice for online shopping</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-truck feature-icon mb-3"></i>
                        <h5 class="card-title">Fast Delivery</h5>
                        <p class="card-text text-muted">Get your orders delivered quickly with our reliable shipping partners. Track your package every step of the way.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-shield-alt feature-icon mb-3"></i>
                        <h5 class="card-title">Secure Shopping</h5>
                        <p class="card-text text-muted">Shop with confidence knowing your personal and payment information is protected by industry-standard security.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-headset feature-icon mb-3"></i>
                        <h5 class="card-title">24/7 Support</h5>
                        <p class="card-text text-muted">Our dedicated customer support team is always ready to help you with any questions or concerns.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Getting Started Section (only show when not logged in) -->
        <?php if (!$isLoggedIn): ?>
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card bg-light border-0">
                        <div class="card-body text-center p-5">
                            <h3 class="mb-4">Ready to Start Shopping?</h3>
                            <p class="text-muted mb-4">Join our community of satisfied customers and experience the best in online shopping.</p>
                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                <a href="login/register.php" class="btn btn-primary btn-lg btn-custom">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Create Account
                                </a>
                                <a href="login/login.php" class="btn btn-outline-primary btn-lg btn-custom">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <p class="mb-0">
                        <i class="fas fa-shopping-bag me-2"></i>
                        &copy; 2024 ShoppN. All rights reserved. | Built with Bootstrap & PHP
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>