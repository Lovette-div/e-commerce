<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../settings/core.php');
require_once('../controllers/product_controller.php');
require_once('../controllers/category_controller.php');
require_once('../controllers/brand_controller.php');
require_once('../classes/product_class.php');

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id == 0) {
    header('Location: all_product.php');
    exit;
}

// Fetch product details
$productController = new ProductController();
$product = $productController->get_product_by_id_ctr($product_id);

if (!$product) {
    header('Location: all_product.php');
    exit;
}

// Fetch product images
$productClass = new Product();
$images = $productClass->getImages($product_id);

// Get category and brand details
$categories = get_all_categories_ctr();
$brandController = new BrandController();
$brands = $brandController->fetch_all_ctr();

// Find category and brand names
$cat_name = 'Unknown';
foreach($categories as $cat) {
    if($cat['cat_id'] == $product['cat_id']) {
        $cat_name = $cat['cat_name'];
        break;
    }
}

$brand_name = 'Unknown';
foreach($brands as $brand) {
    if($brand['brand_id'] == $product['brand_id']) {
        $brand_name = $brand['brand_name'];
        break;
    }
}

$customer_name = $_SESSION['customer_name'] ?? 'Customer';
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
        :root {
            --emerald: #50c8aeff;
            --emerald-dark: #3a9682ff;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f7fa;
        }
        .sidebar {
            min-height: 100vh;
            background: var(--emerald);
            color: white;
            padding-top: 20px;
            position: fixed;
            width: 16.666667%;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background: var(--emerald-dark);
        }
        .main-content {
            margin-left: 16.666667%;
        }
        .product-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .product-image-main {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border 0.3s;
        }
        .thumbnail:hover, .thumbnail.active {
            border-color: var(--emerald);
        }
        .price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--emerald);
        }
        .badge-custom {
            background: var(--emerald);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .btn-add-cart {
            background: var(--emerald);
            border: none;
            color: white;
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 8px;
            transition: background 0.3s;
        }
        .btn-add-cart:hover {
            background: var(--emerald-dark);
            color: white;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2 sidebar d-flex flex-column">
            <h4 class="text-center mb-4"><i class="fas fa-shopping-bag"></i> Launder</h4>
            <a href="../index.php"><i class="fas fa-home"></i> Home</a>
            <a href="all_product.php"><i class="fas fa-box-open"></i> All Services</a>
            <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
            <a href="checkout.php"><i class="fas fa-clipboard-list"></i> Orders</a>
            <a href="#"><i class="fas fa-truck"></i> Track Orders</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Main content -->
        <div class="col-lg-10 main-content p-4">
            <div class="mb-3">
                <a href="all_product.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>

            <div class="product-container">
                <div class="row">
                    <!-- Product Images -->
                    <div class="col-md-6">
                        <?php if($images && count($images) > 0): ?>
                            <img id="mainImage" src="/~lovette.philips/<?php echo htmlspecialchars($images[0]['file_path']); ?>" class="product-image-main" alt="<?php echo htmlspecialchars($product['title']); ?>" onerror="this.src='https://placehold.co/500x500?text=No+Image'">
                            
                            <?php if(count($images) > 1): ?>
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php foreach($images as $index => $img): ?>
                                        <img src="/~lovette.philips/<?php echo htmlspecialchars($img['file_path']); ?>" 
                                             class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                             onclick="changeImage(this)" 
                                             alt="Thumbnail"
                                             onerror="this.style.display='none'">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <img src="https://placehold.co/500x500?text=No+Image" class="product-image-main" alt="No image">
                        <?php endif; ?>
                    </div>

                    <!-- Product Details -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <span class="badge-custom me-2"><?php echo htmlspecialchars($cat_name); ?></span>
                            <span class="badge-custom"><?php echo htmlspecialchars($brand_name); ?></span>
                        </div>

                        <h2 class="mb-3"><?php echo htmlspecialchars($product['title']); ?></h2>
                        
                        <div class="price mb-4">GHS<?php echo number_format($product['price'], 2); ?></div>

                        <div class="mb-4">
                            <h5>Description</h5>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>

                        <?php if(!empty($product['keywords'])): ?>
                            <div class="mb-4">
                                <h5>Keywords</h5>
                                <p class="text-muted">
                                    <?php 
                                    $keywords = explode(',', $product['keywords']);
                                    foreach($keywords as $keyword) {
                                        echo '<span class="badge bg-secondary me-1">' . htmlspecialchars(trim($keyword)) . '</span>';
                                    }
                                    ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <p class="text-muted small mb-0">Product ID: #<?php echo $product['product_id']; ?></p>
                        </div>

                        <button class="btn btn-add-cart btn-lg w-100 mb-3" onclick="addToCartQuick(<?php echo $product['product_id']; ?>)">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>

                        <!-- <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Cart functionality coming soon!
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function changeImage(thumbnail) {
        const mainImage = document.getElementById('mainImage');
        mainImage.src = thumbnail.src;
        
        // Update active thumbnail
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.classList.remove('active');
        });
        thumbnail.classList.add('active');
    }
 // Add to cart functionality
    async function addToCartQuick(productId) {
        try {
            const response = await fetch('../actions/functions/add_to_cart_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    product_id: productId,
                    qty: 1
                })
            });

            const data = await response.json();

            if (data.status) {
                // Show success notification
                showNotification('success', data.message);
                
                // Update cart badge
                if (data.cart_count) {
                    updateCartBadge(data.cart_count);
                }
            } else {
                showNotification('error', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('error', 'Failed to add to cart. Please try again.');
        }
    }


     // Update cart badge
    function updateCartBadge(count) {
        let badge = document.getElementById('cartBadge');
        
        if (count > 0) {
            if (!badge) {
                // Create badge if it doesn't exist
                const cartLink = document.querySelector('a[href="cart.php"]');
                badge = document.createElement('span');
                badge.id = 'cartBadge';
                badge.className = 'cart-badge';
                cartLink.appendChild(badge);
            }
            badge.textContent = count;
            badge.style.display = 'inline-block';
        } else if (badge) {
            badge.style.display = 'none';
        }
    }

    // Show notification
    function showNotification(type, message) {
        // Remove existing notifications
        const existing = document.querySelectorAll('.notification');
        existing.forEach(n => n.remove());

        // Create notification
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} notification position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

</script>
</body>
</html>