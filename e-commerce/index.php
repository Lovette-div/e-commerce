<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Launder - Online Laundry Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0a0a0a;
            color: #ffffff;
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar {
            background: rgba(10, 10, 10, 0.95) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.2rem 0;
            position: fixed;
            width: 100%;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar-scrolled {
            padding: 0.8rem 0;
            box-shadow: 0 10px 40px rgba(0, 188, 212, 0.1);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.8rem;
            background: linear-gradient(135deg, #00e5ff 0%, #00bcd4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: #00e5ff !important;
            transform: translateY(-2px);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%) scaleX(0);
            width: 80%;
            height: 2px;
            background: linear-gradient(90deg, #00e5ff, #00bcd4);
            transition: transform 0.3s ease;
        }

        .nav-link:hover::after {
            transform: translateX(-50%) scaleX(1);
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding-top: 80px;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #0a0a0a 100%);
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(0, 229, 255, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(0, 188, 212, 0.15) 0%, transparent 50%);
            animation: gradientShift 10s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: clamp(3rem, 8vw, 6rem);
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #ffffff 0%, #00e5ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: fadeInUp 1s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-subtitle {
            font-size: 1.4rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 3rem;
            line-height: 1.6;
            animation: fadeInUp 1s ease 0.2s backwards;
        }

        .hero-image {
            position: relative;
            animation: fadeInUp 1s ease 0.4s backwards;
        }

        .hero-image img {
            width: 100%;
            max-width: 600px;
            border-radius: 30px;
            box-shadow: 0 30px 60px rgba(0, 229, 255, 0.3);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        /* Decorative elements */
        .floating-icon {
            position: absolute;
            animation: floatRandom 8s ease-in-out infinite;
            opacity: 0.1;
            font-size: 4rem;
            color: #00e5ff;
        }

        @keyframes floatRandom {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }

        .icon-1 { top: 15%; left: 10%; animation-delay: 0s; }
        .icon-2 { top: 60%; right: 15%; animation-delay: 2s; }
        .icon-3 { bottom: 20%; left: 20%; animation-delay: 4s; }

        /* Buttons */
        .btn-gradient {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            border: none;
            background: linear-gradient(135deg, #00e5ff 0%, #00bcd4 100%);
            color: #0a0a0a;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 229, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-gradient:hover::before {
            left: 100%;
        }

        .btn-gradient:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 40px rgba(0, 229, 255, 0.5);
        }

        .btn-outline-custom {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            border: 2px solid #00e5ff;
            background: transparent;
            color: #00e5ff;
            transition: all 0.3s ease;
        }

        .btn-outline-custom:hover {
            background: rgba(0, 229, 255, 0.1);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 229, 255, 0.2);
            color: #00e5ff;
        }

        /* Features Section */
        .features-section {
            padding: 120px 0;
            background: linear-gradient(180deg, #0a0a0a 0%, #1a1a2e 100%);
            position: relative;
        }

        .section-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            text-align: center;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ffffff 0%, #00e5ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 1.2rem;
            margin-bottom: 5rem;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 3rem 2rem;
            height: 100%;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 229, 255, 0.1) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            border-color: rgba(0, 229, 255, 0.5);
            box-shadow: 0 20px 50px rgba(0, 229, 255, 0.2);
        }

        .feature-icon {
            font-size: 3.5rem;
            background: linear-gradient(135deg, #00e5ff 0%, #00bcd4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
            display: inline-block;
            transition: transform 0.4s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .feature-text {
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.6;
        }

        /* CTA Section */
        .cta-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #1a1a2e 0%, #0a0a0a 100%);
            position: relative;
        }

        .cta-card {
            background: linear-gradient(135deg, rgba(0, 229, 255, 0.1) 0%, rgba(0, 188, 212, 0.1) 100%);
            border: 1px solid rgba(0, 229, 255, 0.3);
            border-radius: 30px;
            padding: 5rem 3rem;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            color: #ffffff;
        }

        /* Footer */
        footer {
            background: #0a0a0a;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2.5rem 0;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }

            .btn-gradient, .btn-outline-custom {
                padding: 0.9rem 2rem;
                font-size: 1rem;
            }

            .feature-card {
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-tint me-2"></i>Launder
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php session_start(); ?>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../e-commerce/login/register.php">Sign Up</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../e-commerce/login/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../e-commerce/view/all_product.php">Services</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../e-commerce/view/all_product.php">Services</a>
                        </li>
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
        <i class="fas fa-tint floating-icon icon-1"></i>
        <i class="fas fa-shirt floating-icon icon-2"></i>
        <i class="fas fa-sync-alt floating-icon icon-3"></i>
        
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="hero-title">
                        Fresh Clothes,<br>
                        Delivered Fast
                    </h1>
                    <p class="hero-subtitle">
                        Experience premium laundry service at your fingertips. Professional cleaning, hassle-free pickup, and lightning-fast delivery.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="../e-commerce/login/register.php" class="btn btn-gradient">
                            Get Started <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                        <a href="../e-commerce/login/login.php" class="btn btn-outline-custom">
                            Sign In
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 hero-image mt-5 mt-lg-0">
                    <img src="https://images.unsplash.com/photo-1517677208171-0bc6725a3e60?w=800&q=80" alt="Folded Laundry" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Why Choose Launder?</h2>
            <p class="section-subtitle">Premium service that makes laundry effortless</p>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-rocket feature-icon"></i>
                        <h3 class="feature-title">Lightning Fast</h3>
                        <p class="feature-text">Same-day pickup and next-day delivery. Your clothes returned fresh and folded in record time.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-shield-alt feature-icon"></i>
                        <h3 class="feature-title">100% Safe</h3>
                        <p class="feature-text">Premium detergents and eco-friendly processes. Your garments treated with professional care.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-headset feature-icon"></i>
                        <h3 class="feature-title">24/7 Support</h3>
                        <p class="feature-text">Round-the-clock customer service ready to help with any questions or special requests.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-card">
                <h2 class="cta-title">Ready to Experience the Future?</h2>
                <p class="hero-subtitle mb-4">Join thousands of satisfied customers who've made the switch</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="../e-commerce/login/register.php" class="btn btn-gradient btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </a>
                    <a href="../e-commerce/login/login.php" class="btn btn-outline-custom btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p class="mb-0">
                <i class="fas fa-tint me-2"></i>
                &copy; 2025 Launder. All rights reserved.
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    </script>
</body>
</html>