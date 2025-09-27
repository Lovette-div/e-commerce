
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Launber - Online Laundry Platform</title>
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
                Launder
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php session_start(); ?>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login/register.php">Register</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login/login.php">Login</a>
                        </li>
                    <?php elseif ($_SESSION['role'] === 1): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/category.php">Category</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
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
                        Welcome to Launder
                    </h1>
                    <p class="lead mb-5">Your ultimate online laundry platform. Discover amazing laundry agents, great deals, and exceptional service.</p>
                    
                    
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container my-5">
        <!-- Features Section -->
        <div class="row mb-5">
            <div class="col-12 text-center mb-5">
                <h2 class="fw-bold">Why Choose Launder?</h2>
                <p class="text-muted">Discover what makes us the preferred choice for online laundry platform</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-truck feature-icon mb-3"></i>
                        <h5 class="card-title">Fast and Reliable Service</h5>
                        <p class="card-text text-muted">Get your clothes delivered quickly with our reliable partners.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-shield-alt feature-icon mb-3"></i>
                        <h5 class="card-title">Quick Service</h5>
                        <p class="card-text text-muted">Wash with confidence</p>
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
       
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card bg-light border-0">
                        <div class="card-body text-center p-5">
                            <h3 class="mb-4">Ready to Start Washing?</h3>
                            <p class="text-muted mb-4">Join our community of satisfied customers.</p>
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
       

    
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <p class="mb-0">
                        <i class="fas fa-shopping-bag me-2"></i>
                        &copy; 2024 Launder. All rights reserved. 
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>