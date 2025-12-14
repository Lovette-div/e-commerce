<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');
error_reporting(E_ALL);

require_once '../settings/core.php';

// Ensure customer is logged in
if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit;
}

// Get customer ID
if (function_exists('get_user_id')) {
    $customer_id = get_user_id();
} else {
    $customer_id = isset($_SESSION['customer_id']) ? (int) $_SESSION['customer_id'] : null;
}

if (empty($customer_id)) {
    header('Location: ../login/login.php');
    exit;
}

// Load order controller
require_once '../controllers/order_controller.php';

// Get all orders for this customer
$orders = get_user_orders_ctr($customer_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Launder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fbfd; }
        
        .navbar { background: linear-gradient(135deg, #ffffff 0%, #fafafa 100%); padding: 20px 0; box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05); }
        .nav-container { max-width: 1400px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 40px; }
        .logo { font-family: 'Cormorant Garamond', serif; font-size: 28px; background: linear-gradient(135deg, #3a9682ff 0%, #50c8aeff 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-decoration: none; }
        
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        
        .page-header { background: linear-gradient(135deg, #ffffff 0%, #fafafa 100%); padding: 50px 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06); margin-bottom: 30px; position: relative; overflow: hidden; }
        .page-header::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #50c8aeff 0%, #50c8aeff 50%, #50c8aeff 100%); }
        .page-title { font-family: 'Cormorant Garamond', serif; font-size: 42px; background: linear-gradient(135deg, #50c8aeff 0%, #3a9682ff 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        .order-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06); margin-bottom: 20px; border-left: 5px solid #50c8aeff; }
        .order-card:hover { box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); }
        
        .order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #f0f0f0; padding-bottom: 15px; }
        .order-id { font-weight: 600; color: #1a1a1a; font-size: 18px; }
        .order-date { color: #6b7280; font-size: 14px; }
        
        .status-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-processing { background: #dbeafe; color: #1e40af; }
        .status-completed { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        
        .order-items { margin: 15px 0; }
        .order-item { display: flex; justify-content: space-between; padding: 10px 0; font-size: 14px; border-bottom: 1px solid #f5f5f5; }
        .order-item:last-child { border-bottom: none; }
        .item-name { color: #1a1a1a; font-weight: 500; }
        .item-qty { color: #6b7280; }
        .item-price { color: #50c8aeff; font-weight: 600; }
        
        .order-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #f0f0f0; }
        .order-total { font-size: 18px; font-weight: 700; color: #3a9682ff; }
        .order-actions { display: flex; gap: 10px; }
        
        .btn-view { background: #50c8aeff; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.3s; }
        .btn-view:hover { background: #3a9682ff; transform: translateY(-2px); }
        
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-icon { font-size: 64px; color: #d1d5db; margin-bottom: 20px; }
        .empty-text { color: #6b7280; font-size: 18px; margin-bottom: 20px; }
        .empty-link { color: #50c8aeff; text-decoration: none; font-weight: 600; }
        
        footer { background: linear-gradient(135deg, #3a9682ff 0%, #50c8aeff 100%); color: white; padding: 30px 0; margin-top: 60px; text-align: center; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">Launder</a>
            <div>
                <a href="shop.php" style="margin-right: 20px; color: #1a1a1a; text-decoration: none; font-weight: 500;">Shop</a>
                <a href="order.php" style="margin-right: 20px; color: #50c8aeff; text-decoration: none; font-weight: 600;">My Orders</a>
                <a href="cart.php" style="margin-right: 20px; color: #1a1a1a; text-decoration: none; font-weight: 500;">Cart</a>
                <a href="../actions/logout_action.php" style="color: #1a1a1a; text-decoration: none; font-weight: 500;">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">My Orders</h1>
            <p style="color: #6b7280; margin-top: 10px;">Track and manage your orders</p>
        </div>

        <!-- Orders List -->
        <div id="ordersContainer">
            <?php if (!$orders || count($orders) === 0): ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                    <p class="empty-text">You haven't placed any orders yet.</p>
                    <a href="shop.php" class="empty-link">Start Shopping →</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <!-- Order Header -->
                        <div class="order-header">
                            <div>
                                <div class="order-id">Order #<?php echo htmlspecialchars($order['order_id']); ?></div>
                                <div class="order-date"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></div>
                            </div>
                            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span>
                        </div>

                        <!-- Order Items -->
                        <div class="order-items">
                            <?php 
                            // Fetch order items (if method exists)
                            $orderItems = [];
                            if (method_exists($orderController, 'getOrderItemsCtr')) {
                                $orderItems = $orderController->getOrderItemsCtr($order['order_id']);
                            }
                            
                            if ($orderItems && count($orderItems) > 0): 
                                foreach ($orderItems as $item):
                            ?>
                                <div class="order-item">
                                    <div>
                                        <span class="item-name"><?php echo htmlspecialchars($item['p_name'] ?? 'Product'); ?></span>
                                        <span class="item-qty"> × <?php echo (int)($item['qty'] ?? 1); ?></span>
                                    </div>
                                    <span class="item-price">GHS <?php echo number_format((float)($item['price'] ?? 0), 2); ?></span>
                                </div>
                            <?php 
                                endforeach;
                            else:
                                echo '<div class="order-item"><span class="item-name">Order Details</span></div>';
                            endif;
                            ?>
                        </div>

                        <!-- Order Footer -->
                        <div class="order-footer">
                            <div class="order-total">
                                Total: GHS <?php echo number_format((float)($order['total_amount'] ?? 0), 2); ?>
                            </div>
                            <div class="order-actions">
                                <button class="btn-view" onclick="viewOrderDetails(<?php echo (int)$order['order_id']; ?>)">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="detailsModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="background: white; border-radius: 12px; max-width: 600px; margin: 50px auto; padding: 30px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="font-size: 24px; font-weight: 700; color: #1a1a1a;">Order Details</h2>
                <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">×</button>
            </div>
            <div id="modalContent" style="max-height: 400px; overflow-y: auto;"></div>
            <div style="margin-top: 20px; text-align: right;">
                <button onclick="closeModal()" style="padding: 10px 20px; background: #50c8aeff; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Close</button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 Launder. All rights reserved.</p>
    </footer>

    <script>
        function viewOrderDetails(orderId) {
            // Fetch order details via AJAX
            fetch('../actions/get_order_details_action.php?order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        let html = `
                            <p><strong>Order ID:</strong> ${data.order.order_id}</p>
                            <p><strong>Order Date:</strong> ${new Date(data.order.order_date).toLocaleDateString()}</p>
                            <p><strong>Status:</strong> <span class="status-badge status-${data.order.status.toLowerCase()}">${data.order.status}</span></p>
                            <p><strong>Total Amount:</strong> GHS ${parseFloat(data.order.total_amount).toFixed(2)}</p>
                            <h4 style="margin-top: 20px; margin-bottom: 10px;">Items:</h4>
                            <ul style="list-style: none; padding: 0;">
                        `;
                        data.items.forEach(item => {
                            html += `<li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0;">
                                ${item.p_name} × ${item.qty} = GHS ${(parseFloat(item.price) * item.qty).toFixed(2)}
                            </li>`;
                        });
                        html += `</ul>`;
                        document.getElementById('modalContent').innerHTML = html;
                        document.getElementById('detailsModal').style.display = 'block';
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            let modal = document.getElementById('detailsModal');
            if (e.target === modal) modal.style.display = 'none';
        });
    </script>
</body>
</html>