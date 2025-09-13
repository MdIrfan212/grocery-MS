<?php
// Auto-added DB bootstrap (keeps your design, replaces JSON with MySQL)
$__candidates = [
    __DIR__ . '/../model/compat.php',
    __DIR__ . '/model/compat.php',
    __DIR__ . '/../../model/compat.php',
];
foreach ($__candidates as $__p) { if (file_exists($__p)) { require_once $__p; break; } }
?>
<?php
// Check if user is employee
if (!function_exists('is_employee') || !is_employee()) {
    header("Location: dashboard.php?page=home");
    exit;
}

// Load data files
$dataDir = dirname(__DIR__) . "/data";
$productsFile = $dataDir . "/products.json";
$ordersFile = $dataDir . "/orders.json";

// Load data
$products = load_json($productsFile);
$orders = load_json($ordersFile);

// Filter orders for current employee only
$employeeOrders = array_filter($orders, function($order) {
    return ($order['by'] ?? '') === ($_SESSION['user'] ?? '');
});

// Handle form submissions
$notice = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Create order
    if ($action === 'create_order') {
        $pid = $_POST['product_id'] ?? '';
        $qty = max(1, (int) ($_POST['qty'] ?? 1));
        $products = load_json($productsFile);
        $foundIndex = null;

        // Find product
        foreach ($products as $i => $p) {
            if ($p['id'] == $pid) {
                $foundIndex = $i;
                break;
            }
        }

        if ($foundIndex === null) {
            $notice = "❌ Product not found.";
        } else {
            if ((int) $products[$foundIndex]['stock'] < $qty) {
                $notice = "❌ Not enough stock.";
            } else {
                $products[$foundIndex]['stock'] -= $qty;
                $orders = load_json($ordersFile);
                $oid = 'o' . substr(md5(uniqid('', true)), 0, 8);
                $orders[] = [
                    'id' => $oid,
                    'product_id' => $products[$foundIndex]['id'],
                    'product_name' => $products[$foundIndex]['name'],
                    'price' => (int) $products[$foundIndex]['price'],
                    'qty' => $qty,
                    'total' => (int) $products[$foundIndex]['price'] * $qty,
                    'by' => $_SESSION['user'] ?? 'Employee',
                    'paid' => false,
                    'method' => '',
                    'status' => 'Processing',
                    'date' => date("Y-m-d H:i:s")
                ];
                save_json($ordersFile, $orders);
                save_json($productsFile, $products);
                $notice = "🧾 Order placed (Processing).";
            }
        }
    }
    
    // Mark order as paid
    if ($action === 'mark_paid') {
        $oid = $_POST['order_id'] ?? '';
        $method = $_POST['method'] ?? 'Cash';
        $orders = load_json($ordersFile);
        
        foreach ($orders as &$o) {
            if ($o['id'] === $oid && $o['by'] === $_SESSION['user']) {
                $o['paid'] = true;
                $o['method'] = $method;
                $o['status'] = 'Completed';
                $o['paid_date'] = date("Y-m-d H:i:s");
                $notice = "💳 Order marked paid (" . htmlspecialchars($method) . ").";
                break;
            }
        }
        unset($o);
        
        save_json($ordersFile, $orders);
    }
}
?>

<div class="panel">
    <h3>Employee Dashboard</h3>
    <p class="muted">Welcome, <?= htmlspecialchars($_SESSION['user'] ?? 'Employee') ?>! Manage your orders and sales.</p>
    
    <?php if ($notice): ?>
        <div class="notice"><?= htmlspecialchars($notice) ?></div>
    <?php endif; ?>
    
    <div class="admin-panel">
        <!-- Quick Order Card -->
        <div class="admin-card">
            <h3>Quick Order</h3>
            <?php if (empty($products)): ?>
                <p class="muted">No products available.</p>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="action" value="create_order">
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">Product</label>
                        <select name="product_id" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                            <?php foreach ($products as $product): ?>
                                <option value="<?= htmlspecialchars($product['id']) ?>">
                                    <?= htmlspecialchars($product['name']) ?> - ৳<?= htmlspecialchars($product['price']) ?> (Stock: <?= htmlspecialchars($product['stock']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">Quantity</label>
                        <input type="number" name="qty" value="1" min="1" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    
                    <button type="submit" class="btn">Create Order</button>
                </form>
            <?php endif; ?>
        </div>
        
        <!-- My Orders Card -->
        <div class="admin-card">
            <h3>My Orders</h3>
            <?php if (empty($employeeOrders)): ?>
                <p class="muted">You haven't placed any orders yet.</p>
            <?php else: ?>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach (array_reverse($employeeOrders) as $order): ?>
                        <div style="padding: 10px; border: 1px solid #eee; border-radius: 8px; margin-bottom: 10px;">
                            <div style="display: flex; justify-content: space-between;">
                                <div>
                                    <strong><?= htmlspecialchars($order['product_name']) ?></strong>
                                    <div>Qty: <?= htmlspecialchars($order['qty']) ?></div>
                                    <div>Total: ৳<?= htmlspecialchars($order['total']) ?></div>
                                    <div>Date: <?= htmlspecialchars(date("M j, H:i", strtotime($order['date']))) ?></div>
                                </div>
                                <div>
                                    <span class="badge <?= $order['paid'] ? 'ok' : 'danger' ?>" style="margin-bottom: 8px;">
                                        <?= $order['paid'] ? 'Paid' : 'Unpaid' ?>
                                    </span>
                                    <?php if (!$order['paid']): ?>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="mark_paid">
                                            <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                                            <select name="method" style="padding: 4px; border-radius: 4px; border: 1px solid #ddd; margin-bottom: 5px;">
                                                <option>Cash</option>
                                                <option>Card</option>
                                                <option>Mobile</option>
                                            </select>
                                            <button type="submit" class="btn small">Mark Paid</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sales Summary Card -->
        <div class="admin-card">
            <h3>My Sales Summary</h3>
            <?php
            $totalSales = count($employeeOrders);
            $paidSales = count(array_filter($employeeOrders, function($order) {
                return $order['paid'];
            }));
            $totalRevenue = array_sum(array_map(function($order) {
                return $order['total'];
            }, $employeeOrders));
            $paidRevenue = array_sum(array_map(function($order) {
                return $order['paid'] ? $order['total'] : 0;
            }, $employeeOrders));
            ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div style="padding: 10px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.5em; font-weight: bold; color: #2196f3;"><?= $totalSales ?></div>
                    <div class="muted">Total Orders</div>
                </div>
                <div style="padding: 10px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.5em; font-weight: bold; color: #4caf50;"><?= $paidSales ?></div>
                    <div class="muted">Paid Orders</div>
                </div>
                <div style="padding: 10px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.5em; font-weight: bold; color: #ff9800;">৳<?= number_format($totalRevenue, 2) ?></div>
                    <div class="muted">Total Revenue</div>
                </div>
                <div style="padding: 10px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.5em; font-weight: bold; color: #9c27b0;">৳<?= number_format($paidRevenue, 2) ?></div>
                    <div class="muted">Paid Revenue</div>
                </div>
            </div>
        </div>
    </div>
</div>