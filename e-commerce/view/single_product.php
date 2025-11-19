<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../settings/core.php');
require_once __DIR__ . '/../controllers/product_controller.php';
require_once __DIR__ . '/../classes/product_class.php';

if (!function_exists('view_single_product_ctr')) {
    die('view_single_product_ctr() is not loaded!');
}

if(!isset($_GET['id'])){
    die('Product ID not specified');
}

$product_id = intval($_GET['id']);
$product = view_single_product_ctr($product_id);
if(!$product) die('Product not found');

$customer_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Customer';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($product['title']); ?> - Launder</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
:root { --emerald:#50c8aeff; --emerald-dark:#246d5dff; }
body { font-family: 'Segoe UI', sans-serif; background:#f5f7fa; }
.sidebar { min-height:100vh; background:var(--emerald); color:white; padding-top:20px;}
.sidebar a { color:white; display:block; padding:12px 20px; border-radius:8px; margin-bottom:5px; text-decoration:none; transition:background 0.3s;}
.sidebar a:hover, .sidebar a.active { background:var(--emerald-dark);}
.product-image { width:100%; max-height:400px; object-fit:cover; border-radius:12px; }
.product-info { background:white; padding:20px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1);}
.product-price { font-weight:700; color: var(--emerald-dark); font-size:1.5rem; }
</style>
</head>
<body>
<div class="container-fluid">
<div class="row">
    <!-- Sidebar -->
    <div class="col-lg-2 sidebar d-flex flex-column">
        <h4 class="text-center mb-4"><i class="fas fa-shopping-bag"></i> ShopNow</h4>
        <a href="../index.php"><i class="fas fa-home"></i> Home</a>
        <a href="all_product.php"><i class="fas fa-box-open"></i> All Services</a>
        <a href="#"><i class="fas fa-shopping-cart"></i> Cart</a>
        <a href="#"><i class="fas fa-clipboard-list"></i> Orders</a>
        <a href="#"><i class="fas fa-truck"></i> Track Orders</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main -->
    <div class="col-lg-10 p-4">
        <div class="product-info mb-4">
            <h2><?php echo htmlspecialchars($product['product_title']); ?></h2>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($product['cat_name']); ?> | 
               <strong>Brand:</strong> <?php echo htmlspecialchars($product['brand_name']); ?></p>
            <p class="product-price">$<?php echo number_format($product['price'],2); ?></p>
            <?php if(!empty($product['product_image'])): ?>
                <img src="../<?php echo htmlspecialchars($product_images['product_image']); ?>" class="product-image mb-3">
            <?php else: ?>
                <img src="https://via.placeholder.com/400x400?text=No+Image" class="product-image mb-3">
            <?php endif; ?>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            <p><strong>Keywords:</strong> <?php echo htmlspecialchars($product['keyword']); ?></p>
            <button class="btn btn-success"><i class="fas fa-cart-plus"></i> Add to Cart</button>
        </div>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
