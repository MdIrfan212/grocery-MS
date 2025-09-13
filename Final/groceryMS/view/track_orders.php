<?php
// Auto-added DB bootstrap (keeps your design, replaces JSON with MySQL)
$__candidates = [
    __DIR__ . '/../model/compat.php',
    __DIR__ . '/model/compat.php',
    __DIR__ . '/../../model/compat.php',
];
foreach ($__candidates as $__p) { if (file_exists($__p)) { require_once $__p; break; } }
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GroceryMS - Track Orders</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            padding: 0;
        }
        
        .topbar {
            background: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: bold;
            font-size: 20px;
            color: #2e7d32;
        }
        
        .nav {
            display: flex;
            list-style: none;
            gap: 20px;
        }
        
        .nav a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.3s;
        }
        
        .nav a:hover, .nav a.active {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .userbox {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logout-btn {
            background: #f44336;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
        }
        
        .container-page {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .panel {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .panel h2 {
            color: #2e7d32;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e8f5e9;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .order-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            background: white;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .order-id {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            background: #4caf50;
            color: white;
        }
        
        .product-details {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        
        .product-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #666;
        }
        
        .product-info {
            flex-grow: 1;
        }
        
        .product-name {
            font-weight: bold;
            font-size: 17px;
            margin-bottom: 5px;
        }
        
        .order-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #555;
        }
        
        .tracking-section {
            margin: 25px 0;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .progress-container {
            margin: 30px 0;
            position: relative;
        }
        
        .progress-bar {
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            width: 100%;
            background: linear-gradient(90deg, #4caf50, #2e7d32);
            border-radius: 4px;
            transition: width 1.5s ease;
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            position: relative;
        }
        
        .step {
            text-align: center;
            position: relative;
            width: 20%;
        }
        
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 18px;
            position: relative;
            z-index: 2;
            transition: all 0.3s;
        }
        
        .step.active .step-icon {
            background: #4caf50;
            color: white;
            border-color: #4caf50;
            transform: scale(1.1);
        }
        
        .step.completed .step-icon {
            background: #4caf50;
            color: white;
            border-color: #4caf50;
        }
        
        .step-label {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
            font-weight: 500;
        }
        
        .step.active .step-label {
            color: #4caf50;
            font-weight: bold;
        }
        
        .step.completed .step-label {
            color: #4caf50;
        }
        
        .delivery-info {
            background: #e8f5e9;
            padding: 18px;
            border-radius: 8px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }
        
        .payment-info {
            background: #f9f9f9;
            padding: 18px;
            border-radius: 8px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 14px 24px;
            border-radius: 8px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 15px;
        }
        
        .btn-primary {
            background: #4caf50;
            color: white;
        }
        
        .btn-primary:hover {
            background: #3d8b40;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #f1f1f1;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .btn-secondary:hover {
            background: #e5e5e5;
            transform: translateY(-2px);
        }
        
        .btn-support {
            background: #ff9800;
            color: white;
        }
        
        .btn-support:hover {
            background: #f57c00;
            transform: translateY(-2px);
        }
        
        .help-section {
            margin-top: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            border-left: 4px solid #ff9800;
        }
        
        .help-title {
            font-weight: bold;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #2c3e50;
        }
        
        .help-content {
            color: #666;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .support-info {
            margin-top: 15px;
            padding: 15px;
            background: #fff8e1;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @media (max-width: 768px) {
            .nav {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .topbar {
                flex-direction: column;
                gap: 15px;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .progress-steps {
                flex-wrap: wrap;
                gap: 20px;
            }
            
            .step {
                width: 45%;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        .no-orders {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .no-orders i {
            font-size: 60px;
            margin-bottom: 20px;
            color: #ccc;
        }
    </style>
</head>
<body>
<?php
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once 'dashboard.php';

$dataDir = dirname(__DIR__) . "/data";
$ordersFile = $dataDir . "/orders.json";
$orders = load_json($ordersFile);
$orderssFile = $dataDir . "/orderss.json";

if (file_exists($orderssFile)) {
    $extraOrders = load_json($orderssFile);
    $orders = array_merge($orders, $extraOrders);
}

$productsFile = $dataDir . "/products.json";
$products = load_json($productsFile);

$current_user = $_SESSION['user'];
$user_orders = array_filter($orders, function($order) use ($current_user) {
    return isset($order['by']) && $order['by'] === $current_user;
});

usort($user_orders, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Order status progression
$statusSteps = [
    'Processing' => 1,
    'Confirmed' => 2,
    'Packing' => 3,
    'Shipped' => 4,
    'Out for Delivery' => 5,
    'Delivered' => 6,
    'Completed' => 6,
    'Cancelled' => 0
];

// Function to get product image
function get_product_image($product_id, $products) {
    foreach ($products as $product) {
        if ($product['id'] == $product_id) {
            $imagePath = "../images/products/" . ($product['image'] ?? 'placeholder.png');
            if (file_exists(dirname(__DIR__) . "/images/products/" . $product['image'])) {
                return $imagePath;
            }
            return "../images/products/placeholder.png";
        }
    }
    return "../images/products/placeholder.png";
}
?>

<!-- Main Content -->
<div class="container-page">
    <div class="panel">
        <h2><i class="fas fa-map-marker-alt"></i> Track My Orders</h2>
        <p style="color: #666; margin-bottom: 25px;">Real-time tracking for your grocery orders</p>
        
        <?php if (empty($user_orders)): ?>
            <div class="no-orders">
                <i class="fas fa-box-open"></i>
                <h3>No orders found</h3>
                <p>You haven't placed any orders yet.</p>
                <a href="dashboard.php?page=products" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Start Shopping
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($user_orders as $order): ?>
                <!-- Order Card -->
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">
                            <i class="fas fa-receipt"></i> Order #<?= htmlspecialchars($order['id']) ?>
                        </div>
                        <div class="status-badge" style="background: 
                            <?= ($order['status'] === 'Delivered' || $order['status'] === 'Completed') ? '#4caf50' : 
                               (($order['status'] === 'Processing') ? '#ff9800' : '#2196f3') ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </div>
                    </div>
                    
                    <div class="product-details">
                        <?php if ($order['product_id'] && $order['product_name']): ?>
                            <div class="product-image">
                                <img src="<?= get_product_image($order['product_id'], $products) ?>" 
                                     alt="<?= htmlspecialchars($order['product_name']) ?>" 
                                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?= htmlspecialchars($order['product_name']) ?></div>
                                <div>Quantity: <?= htmlspecialchars($order['qty'] ?? 1) ?></div>
                                <div>Unit Price: ৳<?= htmlspecialchars($order['price'] ?? 0) ?></div>
                            </div>
                        <?php else: ?>
                            <div class="product-image">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div class="product-info">
                                <div class="product-name">Product information not available</div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-meta">
                        <div class="meta-item">
                            <i class="far fa-calendar-alt"></i>
                            <span>Placed on: <?= htmlspecialchars($order['date']) ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-tag"></i>
                            <span>Total: ৳<?= htmlspecialchars($order['total'] ?? 0) ?></span>
                        </div>
                        <?php if ($order['paid']): ?>
                            <div class="meta-item">
                                <i class="fas fa-credit-card"></i>
                                <span>Paid via: <?= htmlspecialchars($order['method'] ?? 'Unknown') ?></span>
                            </div>
                            <?php if ($order['paid_date']): ?>
                                <div class="meta-item">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Paid on: <?= htmlspecialchars($order['paid_date']) ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="tracking-section">
                        <div class="section-title">
                            <i class="fas fa-truck"></i> Tracking Your Order
                        </div>
                        
                        <div class="progress-container">
                            <div class="progress-bar">
                                <?php
                                $currentStatus = $order['status'];
                                $progress = isset($statusSteps[$currentStatus]) ? 
                                    ($statusSteps[$currentStatus] / 6 * 100) : 0;
                                ?>
                                <div class="progress-fill" style="width: <?= $progress ?>%;"></div>
                            </div>
                            
                            <div class="progress-steps">
                                <?php
                                $steps = [
                                    ['icon' => 'fa-shopping-cart', 'label' => 'Order Placed', 'status' => 'Processing'],
                                    ['icon' => 'fa-check-circle', 'label' => 'Confirmed', 'status' => 'Confirmed'],
                                    ['icon' => 'fa-box', 'label' => 'Packing', 'status' => 'Packing'],
                                    ['icon' => 'fa-shipping-fast', 'label' => 'Shipped', 'status' => 'Shipped'],
                                    ['icon' => 'fa-truck', 'label' => 'Out for Delivery', 'status' => 'Out for Delivery'],
                                    ['icon' => 'fa-home', 'label' => 'Delivered', 'status' => 'Delivered']
                                ];
                                
                                foreach ($steps as $index => $step):
                                    $stepNumber = $index + 1;
                                    $isCompleted = isset($statusSteps[$currentStatus]) && 
                                                 $statusSteps[$currentStatus] >= $stepNumber;
                                    $isActive = isset($statusSteps[$currentStatus]) && 
                                               $statusSteps[$currentStatus] == $stepNumber;
                                ?>
                                <div class="step <?= $isCompleted ? 'completed' : '' ?> <?= $isActive ? 'active' : '' ?>">
                                    <div class="step-icon">
                                        <i class="fas <?= $step['icon'] ?>"></i>
                                    </div>
                                    <div class="step-label"><?= $step['label'] ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <?php if ($order['status'] === 'Delivered' || $order['status'] === 'Completed'): ?>
                            <div class="delivery-info">
                                <i class="fas fa-calendar-check"></i>
                                <?php if ($order['delivery_date'] ?? false): ?>
                                    Delivered on <?= htmlspecialchars($order['delivery_date']) ?>
                                <?php else: ?>
                                    Order delivered successfully
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($order['paid']): ?>
                            <div class="payment-info">
                                <i class="fas fa-check-circle"></i>
                                Paid via <?= htmlspecialchars($order['method'] ?? 'Unknown method') ?>
                            </div>
                        <?php else: ?>
                            <div class="payment-info" style="background: #ffebee;">
                                <i class="fas fa-exclamation-circle"></i>
                                Payment pending
                            </div>
                        <?php endif; ?>
                        
                        <div class="action-buttons">
                            <a href="dashboard.php?page=my_orders" class="btn btn-primary">
                                <i class="fas fa-info-circle"></i> View Details
                            </a>
                            
                            <?php if ($order['product_id'] && $order['product_name']): ?>
                                <form method="post" action="dashboard.php?page=products" style="display:inline;">
                                    <input type="hidden" name="action" value="create_order">
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($order['product_id']) ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="fas fa-redo"></i> Order Again
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <a href="#" class="btn btn-support">
                                <i class="fas fa-headset"></i> Support
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="help-section">
            <div class="help-title">
                <i class="fas fa-question-circle"></i> Need Help?
            </div>
            <div class="help-content">
                If you have any questions about your order, please contact our customer support team. We're here to help you!
            </div>
            <div class="support-info">
                <i class="fas fa-phone"></i>
                <div>
                    <div><strong>Call us:</strong> +880 1712 345 678</div>
                    <div><strong>Email:</strong> support@groceryms.com</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.progress-fill').forEach(progressFill => {
            progressFill.style.width = '0';
            
            setTimeout(() => {
                const targetWidth = progressFill.style.width;
                progressFill.style.transition = 'width 1.5s ease';
                progressFill.style.width = targetWidth;
            }, 300);
        });
        
        
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (this.getAttribute('href') === '#') {
                    e.preventDefault();
                    alert('Support action would be performed in a real application');
                }
            });
        });
    });
</script>
</body>
</html>
