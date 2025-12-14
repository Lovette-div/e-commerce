<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../settings/core.php');
require_once('../controllers/product_controller.php');
require_once('../controllers/category_controller.php');
require_once('../controllers/brand_controller.php');
require_once('../classes/product_class.php'); 

// Check if user is logged in for cart functionality
$isLoggedIn = isLoggedIn();
$cart_count = 0;

if ($isLoggedIn) {
    require_once('../controllers/cart_controller.php');
    $cartController = new CartController();
    $cart_count = $cartController->get_cart_count_ctr($_SESSION['customer_id']);
}

// Instantiate controller
$productController = new ProductController();

// Fetch all products
$products = $productController->fetch_all_ctr();

// Fetch product images for all products
$productClass = new Product();
$product_images_map = []; // Map product_id => image_path

foreach($products as $product) {
    $images = $productClass->getImages($product['product_id']);
    if($images && count($images) > 0) {
        // Store the first image for each product
        $product_images_map[$product['product_id']] = $images[0]['file_path'];
    }
}

// Get categories and brands
$categories = get_all_categories_ctr();
$brandController = new BrandController();
$brands = $brandController->fetch_all_ctr();

// Pagination
$products_per_page = 12;
$total_products = count($products);
$total_pages = ceil($total_products / $products_per_page);
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $products_per_page;
$paginated_products = array_slice($products, $offset, $products_per_page);

// Get logged-in user name 
$customer_name = $_SESSION['customer_name'] ?? 'Customer';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - Launder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --emerald: #50c8aeff;
            --emerald-dark: #3a9682ff;
            --emerald-gradient: linear-gradient(135deg, #50c8aeff, #246d5dff);
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f7fa;
            overflow-x: auto;
        }
        /* Sidebar */
        .sidebar {
            min-height: 100vh;
            background: var(--emerald);
            color: white;
            padding-top: 20px;
            position: fixed;
            width: 250px;
            left: 0;
            top: 0;
            z-index: 1000;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 5px;
            text-decoration: none;
            transition: background 0.3s;
            position: relative;
        }
        .sidebar a:hover, .sidebar a.active {
            background: var(--emerald-dark);
        }
        .cart-badge {
            position: absolute;
            top: 8px;
            right: 15px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: bold;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        /* Welcome container */
        .welcome-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        /* Product card */
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .product-image {
            height: 250px;
            object-fit: cover;
            width: 100%;
        }
        .product-price {
            font-weight: 700;
            color: var(--emerald-dark);
            font-size: 1.2rem;
        }
        .btn-add-cart {
            background: var(--emerald);
            border: none;
            color: white;
            transition: background 0.3s;
        }
        .btn-add-cart:hover {
            background: var(--emerald-dark);
            color: white;
        }
        .filter-section select, .filter-section input {
            margin-bottom: 15px;
        }
        
        /* Notification styles */
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
        .notification {
            animation: slideIn 0.3s ease-out;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="sidebar d-flex flex-column">
            <h4 class="text-center mb-4"><i class="fas fa-shopping-bag"></i> Launder</h4>
            <a href="../index.php"><i class="fas fa-home"></i> Home</a>
            <a href="all_product.php" class="active"><i class="fas fa-box-open"></i> All Services</a>
            <a href="cart.php">
                <i class="fas fa-shopping-cart"></i> Cart
                <?php if($isLoggedIn && $cart_count > 0): ?>
                    <span class="cart-badge" id="cartBadge"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="checkout.php"><i class="fas fa-clipboard-list"></i> Checkout</a>
            <a href="order.php"><i class="fas fa-truck"></i> My Orders</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Main content -->
        <div class="main-content">
            <!-- Welcome container -->
            <div class="welcome-container">
                <h3>Welcome, <?php echo htmlspecialchars($customer_name); ?>!</h3>
                <p>Explore our wide range of services below. Use the filters or search box to find exactly what you want.</p>
            </div>

            <div class="row">
                <!-- Filters -->
                <div class="col-md-3">
                    <div class="card p-3 mb-4">
                        <h5 class="mb-3"><i class="fas fa-filter"></i> Filters</h5>
                        <input type="text" id="searchInput" class="form-control mb-2" placeholder="Search services...">
                        <select id="categoryFilter" class="form-select mb-2">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['cat_id']; ?>"><?php echo htmlspecialchars($cat['cat_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="brandFilter" class="form-select mb-2">
                            <option value="">All Brands</option>
                            <?php foreach($brands as $brand): ?>
                                <option value="<?php echo $brand['brand_id']; ?>"><?php echo htmlspecialchars($brand['brand_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" id="minPrice" class="form-control mb-2" placeholder="Min Price">
                        <input type="number" id="maxPrice" class="form-control mb-2" placeholder="Max Price">
                        <button id="applyFilters" class="btn btn-success w-100">Apply Filters</button>
                        <button id="clearFilters" class="btn btn-outline-secondary w-100 mt-2">Clear All</button>
                    </div>
                </div>

                <!-- Products -->
                <div class="col-md-9">
                    <div class="row" id="productsContainer">
                        <?php if($paginated_products && count($paginated_products) > 0): ?>
                            <?php foreach($paginated_products as $product): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="product-card">
                                        <?php 
                                        $product_id = $product['product_id'];
                                        $image_path = $product_images_map[$product_id] ?? null;
                                        ?>
                                        
                                        <?php if($image_path): ?>
                                            <img src="/~lovette.philips/<?php echo htmlspecialchars($image_path); ?>" class="product-image" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                        <?php else: ?>
                                            <img src="https://placehold.co/250x250?text=No+Image" class="product-image" alt="No image">
                                        <?php endif; ?>
                                        
                                        <div class="p-3">
                                            <h6><?php echo htmlspecialchars($product['title']); ?></h6>
                                            <p class="product-price">GHS<?php echo number_format($product['price'], 2); ?></p>
                                            <p>
                                                <small>
                                                    <?php 
                                                    // Find category name
                                                    $cat_name = 'Unknown';
                                                    foreach($categories as $cat) {
                                                        if($cat['cat_id'] == $product['cat_id']) {
                                                            $cat_name = $cat['cat_name'];
                                                            break;
                                                        }
                                                    }
                                                    
                                                    // Find brand name
                                                    $brand_name = 'Unknown';
                                                    foreach($brands as $brand) {
                                                        if($brand['brand_id'] == $product['brand_id']) {
                                                            $brand_name = $brand['brand_name'];
                                                            break;
                                                        }
                                                    }
                                                    
                                                    echo htmlspecialchars($cat_name) . ' | ' . htmlspecialchars($brand_name);
                                                    ?>
                                                </small>
                                            </p>
                                            
                                            <!-- Updated buttons with cart functionality -->
                                            <div class="d-grid gap-2">
                                                <a href="single_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                                
                                                <?php if($isLoggedIn): ?>
                                                    <button class="btn btn-sm btn-add-cart" onclick="addToCartQuick(<?php echo $product['product_id']; ?>)">
                                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                                    </button>
                                                <?php else: ?>
                                                    <a href="../login/login.php" class="btn btn-sm btn-success">
                                                        <i class="fas fa-sign-in-alt"></i> Login to Buy
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-muted">No products found.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if($total_pages > 1): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php if($current_page > 1): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?php echo $current_page-1; ?>">&laquo;</a></li>
                                <?php endif; ?>
                                <?php for($i=1;$i<=$total_pages;$i++): ?>
                                    <li class="page-item <?php echo $i==$current_page?'active':''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <?php if($current_page < $total_pages): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?php echo $current_page+1; ?>">&raquo;</a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Filter functionality
    const applyFiltersBtn = document.getElementById('applyFilters');
    const clearFiltersBtn = document.getElementById('clearFilters');

    applyFiltersBtn.addEventListener('click', ()=>{
        const query = document.getElementById('searchInput').value.trim();
        const category = document.getElementById('categoryFilter').value;
        const brand = document.getElementById('brandFilter').value;
        const minPrice = document.getElementById('minPrice').value;
        const maxPrice = document.getElementById('maxPrice').value;

        const params = new URLSearchParams();
        if(query) params.append('query', query);
        if(category) params.append('category', category);
        if(brand) params.append('brand', brand);
        if(minPrice) params.append('min_price', minPrice);
        if(maxPrice) params.append('max_price', maxPrice);

        window.location.href = `product_search_result.php?${params.toString()}`;
    });

    clearFiltersBtn.addEventListener('click', ()=>{
        window.location.href = 'all_product.php';
    });

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