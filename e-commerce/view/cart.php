<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('../settings/core.php');
require_once('../controllers/cart_controller.php');

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'] ?? 'Customer';

// Get cart items
$cartController = new CartController();
$cart_items = $cartController->get_user_cart_ctr($customer_id);
$cart_total = $cartController->get_cart_total_ctr($customer_id);
$cart_count = $cartController->get_cart_count_ctr($customer_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Launder</title>
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
        }
        .sidebar a:hover {
            background: var(--emerald-dark);
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .cart-header {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .cart-item {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .qty-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .qty-btn {
            width: 35px;
            height: 35px;
            border: none;
            background: var(--emerald);
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .qty-btn:hover {
            background: var(--emerald-dark);
        }
        .qty-input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
        }
        .cart-summary {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
        }
        .btn-checkout {
            background: var(--emerald);
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: background 0.3s;
        }
        .btn-checkout:hover {
            background: var(--emerald-dark);
            color: white;
        }
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-cart i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 20px;
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
            <a href="all_product.php"><i class="fas fa-box-open"></i> All Services</a>
            <a href="cart.php" class="active"><i class="fas fa-shopping-cart"></i> Cart <span class="badge bg-light text-dark" id="cartBadge"><?php echo $cart_count; ?></span></a>
            <a href="checkout.php"><i class="fas fa-clipboard-list"></i> Checkout</a>
            <a href="order.php"><i class="fas fa-truck"></i> My Orders</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Main content -->
        <div class="main-content">
            <div class="cart-header">
                <h2><i class="fas fa-shopping-cart"></i> Bookings </h2>
                <p class="text-muted mb-0">Review your items before checkout</p>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div id="cartItemsContainer">
                        <?php if(count($cart_items) > 0): ?>
                            <?php foreach($cart_items as $item): ?>
                                <div class="cart-item" data-cart-id="<?php echo $item['cart_id']; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <?php 
                                            $image_path = $item['product_image'] ?? null;
                                            if($image_path && !empty($image_path)): 
                                            ?>
                                                <img src="/~lovette.philips/<?php echo htmlspecialchars($image_path); ?>" 
                                                     class="product-image" 
                                                     alt="Product" 
                                                     onerror="this.src='https://placehold.co/100x100?text=No+Image'">
                                            <?php else: ?>
                                                <img src="https://placehold.co/100x100?text=No+Image" class="product-image" alt="No image">
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-4">
                                            <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                            <p class="text-muted mb-0">GHS<?php echo number_format($item['price'], 2); ?> each</p>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="qty-control">
                                                <button class="qty-btn" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['qty'] - 1; ?>)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" class="qty-input" value="<?php echo $item['qty']; ?>" min="1" readonly>
                                                <button class="qty-btn" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['qty'] + 1; ?>)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <h5 class="text-success">GHS<?php echo number_format($item['subtotal'], 2); ?></h5>
                                        </div>
                                        <div class="col-md-1 text-end">
                                            <button class="btn btn-sm btn-danger" onclick="removeItem(<?php echo $item['cart_id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-cart">
                                <i class="fas fa-shopping-cart"></i>
                                <h3>Your cart is empty</h3>
                                <p class="text-muted">Add some products to get started!</p>
                                <a href="all_product.php" class="btn btn-success btn-lg">Browse Products</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if(count($cart_items) > 0): ?>
                        <div class="mt-3">
                            <button class="btn btn-outline-danger" onclick="emptyCart()">
                                <i class="fas fa-trash"></i> Empty Cart
                            </button>
                            <a href="all_product.php" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-arrow-left"></i> Continue Shopping
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if(count($cart_items) > 0): ?>
                    <div class="col-lg-4">
                        <div class="cart-summary">
                            <h4 class="mb-4">Order Summary</h4>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Subtotal:</span>
                                <strong id="subtotalAmount">GHS<?php echo number_format($cart_total, 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Shipping:</span>
                                <strong class="text-success">FREE</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <h5>Total:</h5>
                                <h5 class="text-success" id="totalAmount">$<?php echo number_format($cart_total, 2); ?></h5>
                            </div>
                            <a href="checkout.php" class="btn btn-checkout">
                                <i class="fas fa-lock"></i> Proceed to Checkout
                            </a>
                            <p class="text-center text-muted small mt-3 mb-0">
                                <i class="fas fa-shield-alt"></i> Secure checkout
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/cart.js"></script>
</body>
</html>