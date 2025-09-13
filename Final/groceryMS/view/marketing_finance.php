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
// marketing_finance.php - Simplified version
if (!function_exists('is_admin') || !is_admin()) {
    header("Location: dashboard.php?page=home");
    exit;
}


// Load necessary data
$dataDir = dirname(__DIR__) . "/data";
$productsFile = $dataDir . "/products.json";
$ordersFile = $dataDir . "/orders.json";
$discountsFile = $dataDir . "/discounts.json";

// Initialize files if they don't exist
if (!file_exists($discountsFile)) {
    save_json($discountsFile, []);
}

// Load data
$products = load_json($productsFile);
$orders = load_json($ordersFile);
$discounts = load_json($discountsFile);

// Handle form submissions
$notice = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Create discount/coupon
    if ($action === 'create_discount') {
        $discountData = [
            'id' => 'd' . substr(md5(uniqid()), 0, 8),
            'code' => $_POST['code'] ?? '',
            'type' => $_POST['type'] ?? 'percentage',
            'value' => (float)($_POST['value'] ?? 0),
            'min_order' => (float)($_POST['min_order'] ?? 0),
            'valid_until' => $_POST['valid_until'] ?? '',
            'created_by' => $_SESSION['user'] ?? 'Admin',
            'created_at' => date("Y-m-d H:i:s"),
            'usage_count' => 0
        ];
        $discounts[] = $discountData;
        save_json($discountsFile, $discounts);
        $notice = "✅ Discount created successfully.";
    }
    
    // Delete discount
    if ($action === 'delete_discount') {
        $id = $_POST['id'] ?? '';
        $newDiscounts = array_filter($discounts, function($d) use ($id) {
            return ($d['id'] ?? '') !== $id;
        });
        if (count($newDiscounts) !== count($discounts)) {
            save_json($discountsFile, array_values($newDiscounts));
            $discounts = array_values($newDiscounts);
            $notice = "✅ Discount deleted successfully.";
        } else {
            $notice = "❌ Discount not found.";
        }
    }
    
    // Bulk price adjustment
    if ($action === 'bulk_price_adjustment') {
        $adjustmentType = $_POST['adjustment_type'] ?? 'percentage';
        $adjustmentValue = (float)($_POST['adjustment_value'] ?? 0);
        $categoryFilter = $_POST['category_filter'] ?? 'all';
        
        $updatedCount = 0;
        foreach ($products as &$product) {
            // Apply category filter
            if ($categoryFilter !== 'all' && $product['category'] !== $categoryFilter) {
                continue;
            }
            
            $currentPrice = (float)$product['price'];
            
            if ($adjustmentType === 'percentage') {
                $newPrice = $currentPrice * (1 + ($adjustmentValue / 100));
            } else {
                $newPrice = $currentPrice + $adjustmentValue;
            }
            
            // Ensure price doesn't go below 0
            $product['price'] = max(0.01, round($newPrice, 2));
            $updatedCount++;
        }
        unset($product);
        
        save_json($productsFile, $products);
        $notice = "✅ Prices updated for {$updatedCount} products.";
    }
    
    // Generate financial report
    if ($action === 'generate_report') {
        $startDate = $_POST['start_date'] ?? date('Y-m-01');
        $endDate = $_POST['end_date'] ?? date('Y-m-t');
        
        // Filter orders by date
        $filteredOrders = array_filter($orders, function($order) use ($startDate, $endDate) {
            $orderDate = isset($order['date']) ? substr($order['date'], 0, 10) : '';
            return $orderDate >= $startDate && $orderDate <= $endDate;
        });
        
        // Calculate financial metrics
        $totalRevenue = array_sum(array_map(function($order) {
            return (float)($order['total'] ?? 0);
        }, $filteredOrders));
        
        $paidOrders = array_filter($filteredOrders, function($order) {
            return !empty($order['paid']);
        });
        
        $unpaidOrders = array_filter($filteredOrders, function($order) {
            return empty($order['paid']);
        });
        
        $paidAmount = array_sum(array_map(function($order) {
            return (float)($order['total'] ?? 0);
        }, $paidOrders));
        
        $unpaidAmount = array_sum(array_map(function($order) {
            return (float)($order['total'] ?? 0);
        }, $unpaidOrders));
        
        // Count orders by status
        $statusCounts = [];
        foreach ($filteredOrders as $order) {
            $status = $order['status'] ?? 'Processing';
            if (!isset($statusCounts[$status])) {
                $statusCounts[$status] = 0;
            } 
            $statusCounts[$status]++;
        }
         
        // Store report data in session to display
        $_SESSION['financial_report'] = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_orders' => count($filteredOrders),
            'total_revenue' => $totalRevenue,
            'paid_amount' => $paidAmount,
            'unpaid_amount' => $unpaidAmount,
            'status_counts' => $statusCounts,
            'generated_at' => date("Y-m-d H:i:s")
        ];
        
        $notice = "✅ Report generated for {$startDate} to {$endDate}.";
    }
}

// Get active discounts (for display)
$activeDiscounts = array_filter($discounts, function($discount) {
    if (isset($discount['valid_until']) && !empty($discount['valid_until'])) {
        return strtotime($discount['valid_until']) >= time();
    }
    return true;
});

// Get categories for filters
$categories = array_values(array_unique(array_map(fn($p) => $p['category'], $products)));
sort($categories);
?>

<div class="page">
    <h2>Marketing & Finance</h2>
    
    <?php if ($notice): ?>
        <div class="notice"><?= htmlspecialchars($notice) ?></div>
    <?php endif; ?>
    
    <div class="panel">
        <h3>Discount Management</h3>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <h4>Create New Discount</h4>
                <form method="POST">
                    <input type="hidden" name="action" value="create_discount">
                    
                    <div style="margin-bottom: 15px;">
                        <label>Discount Code</label>
                        <input type="text" name="code" required>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                        <div>
                            <label>Discount Type</label>
                            <select name="type">
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        <div>
                            <label>Value</label>
                            <input type="number" step="0.01" name="value" required>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label>Minimum Order (৳)</label>
                        <input type="number" step="0.01" name="min_order">
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label>Valid Until</label>
                        <input type="date" name="valid_until">
                    </div>
                    
                    <button type="submit" class="btn">Create Discount</button>
                </form>
            </div>
            
            <div>
                <h4>Active Discounts</h4>
                <?php if (empty($activeDiscounts)): ?>
                    <p class="muted">No active discounts.</p>
                <?php else: ?>
                    <?php foreach ($activeDiscounts as $discount): ?>
                        <div class="card" style="margin-bottom: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong><?= htmlspecialchars($discount['code'] ?? 'N/A') ?></strong>
                                    <div class="muted">
                                        <?= htmlspecialchars($discount['value'] ?? 0) ?>
                                        <?= ($discount['type'] ?? 'percentage') === 'percentage' ? '% off' : '৳ off' ?>
                                        <?php if (!empty($discount['min_order'])): ?>
                                            • Min order: ৳<?= number_format($discount['min_order'] ?? 0, 2) ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($discount['valid_until'])): ?>
                                        <div class="muted">
                                            Valid until: <?= htmlspecialchars($discount['valid_until']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="action" value="delete_discount">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($discount['id'] ?? '') ?>">
                                    <button type="submit" class="btn small" style="background: #f44336;">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="panel">
        <h3>Bulk Price Adjustment</h3>
        <p class="muted">Apply price changes to multiple products at once.</p>
        
        <form method="POST">
            <input type="hidden" name="action" value="bulk_price_adjustment">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label>Adjustment Type</label>
                    <select name="adjustment_type">
                        <option value="percentage">Percentage</option>
                        <option value="fixed">Fixed Amount</option>
                    </select>
                </div>
                
                <div>
                    <label>Adjustment Value</label>
                    <input type="number" step="0.01" name="adjustment_value" required>
                    <small class="muted">
                        For percentage: enter positive to increase, negative to decrease
                    </small>
                </div>
                
                <div>
                    <label>Category Filter</label>
                    <select name="category_filter">
                        <option value="all">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn" onclick="return confirm('This will update prices for all selected products. Continue?')">Apply Price Changes</button>
        </form>
    </div>
    
    <div class="panel">
        <h3>Financial Reports</h3>
        
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
            <div>
                <h4>Generate Report</h4>
                <form method="POST">
                    <input type="hidden" name="action" value="generate_report">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                        <div>
                            <label>Start Date</label>
                            <input type="date" name="start_date" value="<?= date('Y-m-01') ?>">
                        </div>
                        <div>
                            <label>End Date</label>
                            <input type="date" name="end_date" value="<?= date('Y-m-t') ?>">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">Generate Report</button>
                </form>
            </div>
            
            <div>
                <h4>Report Results</h4>
                <?php if (isset($_SESSION['financial_report'])): 
                    $report = $_SESSION['financial_report'];
                ?>
                    <div class="card">
                        <h5>Financial Summary (<?= $report['start_date'] ?> to <?= $report['end_date'] ?>)</h5>
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 15px;">
                            <div class="stat-card">
                                <div class="stat-number"><?= $report['total_orders'] ?></div>
                                <div class="stat-label">Total Orders</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number">৳<?= number_format($report['total_revenue'], 2) ?></div>
                                <div class="stat-label">Total Revenue</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number">৳<?= number_format($report['paid_amount'], 2) ?></div>
                                <div class="stat-label">Paid Amount</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number">৳<?= number_format($report['unpaid_amount'], 2) ?></div>
                                <div class="stat-label">Unpaid Amount</div>
                            </div>
                        </div>
                        
                        <h5>Order Status Breakdown</h5>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                            <?php foreach ($report['status_counts'] as $status => $count): ?>
                                <div class="status-item">
                                    <span><?= htmlspecialchars($status) ?></span>
                                    <strong><?= $count ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="muted" style="margin-top: 15px;">
                            Generated on <?= $report['generated_at'] ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="muted">Generate a report to see results here.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    padding: 10px;
    background: #f7f7f7;
    border-radius: 8px;
    text-align: center;
}

.stat-number {
    font-size: 1.5em;
    font-weight: bold;
    color: #4caf50;
}

.stat-label {
    font-size: 0.9em;
    color: #666;
}

.status-item {
    padding: 8px;
    background: #f5f5f5;
    border-radius: 6px;
    display: flex;
    justify-content: space-between;
}
</style>