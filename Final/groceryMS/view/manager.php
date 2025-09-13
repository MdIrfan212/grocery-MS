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
// Check if user is manager
if (!function_exists('is_manager') || !is_manager()) {
    header("Location: dashboard.php?page=home");
    exit;
}

// Load any necessary data
$dataDir = dirname(__DIR__) . "/data";
$productsFile = $dataDir . "/products.json";
$ordersFile = $dataDir . "/orders.json";
$pendingProductsFile = $dataDir . "/pending_products.json";
$tasksFile = $dataDir . "/tasks.json";
$discountsFile = $dataDir . "/discounts.json";
$employeesFile = $dataDir . "/employees.json"; // New file for employee data
$reportsFile = $dataDir . "/reports.json"; // New file for reports

// Load data
$products = load_json($productsFile);
$orders = load_json($ordersFile);
$pendingProducts = load_json($pendingProductsFile);
$tasks = load_json($tasksFile);
$discounts = load_json($discountsFile);
$employees = load_json($employeesFile);
$reports = load_json($reportsFile);

// Calculate sales data for reports
$today = date("Y-m-d");
$weekStart = date("Y-m-d", strtotime('monday this week'));
$monthStart = date("Y-m-01");

$todaySales = array_filter($orders, function($order) use ($today) {
    return isset($order['date']) && strpos($order['date'], $today) === 0 && $order['paid'];
});

$weekSales = array_filter($orders, function($order) use ($weekStart) {
    return isset($order['date']) && $order['date'] >= $weekStart && $order['paid'];
});

$monthSales = array_filter($orders, function($order) use ($monthStart) {
    return isset($order['date']) && $order['date'] >= $monthStart && $order['paid'];
});

$todayRevenue = array_sum(array_map(function($order) {
    return $order['total'] ?? 0;
}, $todaySales));

$weekRevenue = array_sum(array_map(function($order) {
    return $order['total'] ?? 0;
}, $weekSales));

$monthRevenue = array_sum(array_map(function($order) {
    return $order['total'] ?? 0;
}, $monthSales));

// Handle form submissions
$notice = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Approve product
    if ($action === 'approve_product') {
        $productId = $_POST['product_id'] ?? '';
        // Find product in pending products
        $productIndex = null;
        foreach ($pendingProducts as $index => $p) {
            if (($p['id'] ?? '') === $productId) {
                $productIndex = $index;
                break;
            }
        }
        
        if ($productIndex !== null) {
            $product = $pendingProducts[$productIndex];
            
            // Add to main products
            $allProducts = load_json($productsFile);
            
            // Generate a proper ID
            $maxId = 0;
            foreach ($allProducts as $item) {
                $idVal = $item['id'];
                if (is_numeric($idVal) && $idVal > $maxId) {
                    $maxId = $idVal;
                }
            }
            $newId = $maxId + 1;
            
            $product['id'] = $newId;
            $allProducts[] = $product;
            save_json($productsFile, $allProducts);
            
            // Remove from pending
            array_splice($pendingProducts, $productIndex, 1);
            save_json($pendingProductsFile, $pendingProducts);
            
            $notice = "✅ Product approved and added to inventory.";
        } else {
            $notice = "❌ Product not found in pending list.";
        }
    }
    
    // Reject product
    if ($action === 'reject_product') {
        $productId = $_POST['product_id'] ?? '';
        $reason = $_POST['reason'] ?? '';
        
        // Find product in pending products
        $productIndex = null;
        foreach ($pendingProducts as $index => $p) {
            if (($p['id'] ?? '') === $productId) {
                $productIndex = $index;
                break;
            }
        }
        
        if ($productIndex !== null) {
            // Remove from pending
            array_splice($pendingProducts, $productIndex, 1);
            save_json($pendingProductsFile, $pendingProducts);
            
            $notice = "✅ Product rejected and removed from queue.";
        } else {
            $notice = "❌ Product not found in pending list.";
        }
    }
    
    // Create task
    if ($action === 'create_task') {
        $taskData = [
            'id' => 't' . substr(md5(uniqid()), 0, 8),
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'assigned_to' => $_POST['assigned_to'] ?? '',
            'due_date' => $_POST['due_date'] ?? '',
            'status' => 'pending',
            'created_by' => $_SESSION['user'] ?? 'Manager',
            'created_at' => date("Y-m-d H:i:s")
        ];
        $tasks[] = $taskData;
        save_json($tasksFile, $tasks);
        $notice = "✅ Task created successfully.";
    }
    
    // Update task status
    if ($action === 'update_task_status') {
        $taskId = $_POST['task_id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        foreach ($tasks as &$task) {
            if ($task['id'] === $taskId) {
                $task['status'] = $status;
                if ($status === 'completed') {
                    $task['completed_at'] = date("Y-m-d H:i:s");
                }
                break;
            }
        }
        save_json($tasksFile, $tasks);
        $notice = "✅ Task status updated.";
    }
    
    // Create discount
    if ($action === 'create_discount') {
        $discountData = [
            'code' => $_POST['code'] ?? '',
            'type' => $_POST['type'] ?? 'percentage',
            'value' => (float)($_POST['value'] ?? 0),
            'min_order' => (float)($_POST['min_order'] ?? 0),
            'max_discount' => (float)($_POST['max_discount'] ?? 0),
            'usage_limit' => (int)($_POST['usage_limit'] ?? 0),
            'valid_until' => $_POST['valid_until'] ?? '',
            'created_by' => $_SESSION['user'] ?? 'Manager',
            'created_at' => date("Y-m-d H:i:s")
        ];
        $discounts[] = $discountData;
        save_json($discountsFile, $discounts);
        $notice = "✅ Discount code created successfully.";
    }
    
    // Set reorder levels
    if ($action === 'set_reorder_level') {
        $productId = $_POST['product_id'] ?? '';
        $reorderLevel = (int)($_POST['reorder_level'] ?? 0);
        
        foreach ($products as &$product) {
            if ($product['id'] == $productId) {
                $product['reorder_level'] = $reorderLevel;
                break;
            }
        }
        save_json($productsFile, $products);
        $notice = "✅ Reorder level set for product.";
    }
    
    // Generate sales report
    if ($action === 'generate_sales_report') {
        $reportType = $_POST['report_type'] ?? 'daily';
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        
        $reportData = [
            'id' => 'r' . substr(md5(uniqid()), 0, 8),
            'type' => $reportType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'generated_by' => $_SESSION['user'] ?? 'Manager',
            'generated_at' => date("Y-m-d H:i:s"),
            'data' => []
        ];
        
        // Filter orders based on report type
        $filteredOrders = [];
        if ($reportType === 'daily') {
            $filteredOrders = array_filter($orders, function($order) use ($startDate) {
                return isset($order['date']) && strpos($order['date'], $startDate) === 0 && $order['paid'];
            });
        } elseif ($reportType === 'weekly') {
            $filteredOrders = array_filter($orders, function($order) use ($startDate, $endDate) {
                return isset($order['date']) && $order['date'] >= $startDate && $order['date'] <= $endDate && $order['paid'];
            });
        } elseif ($reportType === 'monthly') {
            $monthStart = date("Y-m-01", strtotime($startDate));
            $monthEnd = date("Y-m-t", strtotime($startDate));
            $filteredOrders = array_filter($orders, function($order) use ($monthStart, $monthEnd) {
                return isset($order['date']) && $order['date'] >= $monthStart && $order['date'] <= $monthEnd && $order['paid'];
            });
        }
        
        // Calculate report data
        $totalRevenue = array_sum(array_map(function($order) {
            return $order['total'] ?? 0;
        }, $filteredOrders));
        
        $totalOrders = count($filteredOrders);
        
        // Top products
        $productSales = [];
        foreach ($filteredOrders as $order) {
            $productId = $order['product_id'] ?? '';
            if (!isset($productSales[$productId])) {
                $productSales[$productId] = [
                    'name' => $order['product_name'] ?? 'Unknown',
                    'quantity' => 0,
                    'revenue' => 0
                ];
            }
            $productSales[$productId]['quantity'] += $order['qty'] ?? 0;
            $productSales[$productId]['revenue'] += $order['total'] ?? 0;
        }
        
        // Sort by revenue
        uasort($productSales, function($a, $b) {
            return $b['revenue'] - $a['revenue'];
        });
        
        $topProducts = array_slice($productSales, 0, 5);
        
        $reportData['data'] = [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'top_products' => $topProducts
        ];
        
        $reports[] = $reportData;
        save_json($reportsFile, $reports);
        
        $notice = "✅ Sales report generated successfully.";
    }
}
?>

<div class="panel">
    <h3>Manager Dashboard</h3>
    <p class="muted">Approve products, manage tasks, and create discounts.</p>
    
    <?php if ($notice): ?>
        <div class="notice"><?= htmlspecialchars($notice) ?></div>
    <?php endif; ?>
    
    <!-- Sales Overview Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #e8f5e9; padding: 16px; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; color: #2e7d32;">৳<?= number_format($todayRevenue, 2) ?></h3>
            <p style="margin: 8px 0 0; color: #388e3c;">Today's Sales</p>
        </div>
        <div style="background: #e3f2fd; padding: 16px; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; color: #1565c0;">৳<?= number_format($weekRevenue, 2) ?></h3>
            <p style="margin: 8px 0 0; color: #1976d2;">This Week's Sales</p>
        </div>
        <div style="background: #f3e5f5; padding: 16px; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; color: #7b1fa2;">৳<?= number_format($monthRevenue, 2) ?></h3>
            <p style="margin: 8px 0 0; color: #8e24aa;">This Month's Sales</p>
        </div>
        <div style="background: #fff3e0; padding: 16px; border-radius: 8px; text-align: center;">
            <h3 style="margin: 0; color: #ef6c00;"><?= count($pendingProducts) ?></h3>
            <p style="margin: 8px 0 0; color: #f57c00;">Pending Products</p>
        </div>
    </div>
    
    <div class="admin-panel">
        <!-- Product Approval Card -->
        <div class="admin-card">
            <h3>Product Approval Queue</h3>
            <?php if (empty($pendingProducts)): ?>
                <p class="muted">No products pending approval.</p>
            <?php else: ?>
                <?php foreach ($pendingProducts as $product): ?>
                <div style="padding: 10px; border: 1px solid #eee; border-radius: 8px; margin-bottom: 10px;">
                    <h4><?= htmlspecialchars($product['name'] ?? 'Unknown') ?></h4>
                    <p>Category: <?= htmlspecialchars($product['category'] ?? 'Unknown') ?></p>
                    <p>Price: ৳<?= htmlspecialchars($product['price'] ?? '0') ?></p>
                    <p>Submitted by: <?= htmlspecialchars($product['submitted_by'] ?? 'Unknown') ?></p>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="approve_product">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id'] ?? '') ?>">
                            <button type="submit" class="btn small">Approve</button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="reject_product">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id'] ?? '') ?>">
                            <input type="text" name="reason" placeholder="Rejection reason" required style="padding: 6px; border-radius: 4px; border: 1px solid #ddd;">
                            <button type="submit" class="btn ghost small">Reject</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Task Management Card -->
        <div class="admin-card">
            <h3>Task Management</h3>
            <form method="POST">
                <input type="hidden" name="action" value="create_task">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Task Title</label>
                    <input type="text" name="title" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Description</label>
                    <textarea name="description" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; height: 80px;"></textarea>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Assign To</label>
                    <input type="text" name="assigned_to" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Due Date</label>
                    <input type="date" name="due_date" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                </div>
                <button type="submit" class="btn">Create Task</button>
            </form>
            
            <h4 style="margin-top: 20px;">Active Tasks</h4>
            <?php if (empty($tasks)): ?>
                <p class="muted">No active tasks.</p>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                <div style="padding: 10px; border: 1px solid #eee; border-radius: 8px; margin-bottom: 10px;">
                    <h5><?= htmlspecialchars($task['title'] ?? 'Untitled') ?></h5>
                    <p><?= htmlspecialchars($task['description'] ?? 'No description') ?></p>
                    <div style="display: flex; justify-content: space-between; color: #666; font-size: 0.9em;">
                        <span>Assigned to: <?= htmlspecialchars($task['assigned_to'] ?? 'Unassigned') ?></span>
                        <span>Due: <?= htmlspecialchars($task['due_date'] ?? 'No date') ?></span>
                        <span>Status: <?= htmlspecialchars($task['status'] ?? 'pending') ?></span>
                    </div>
                    <form method="POST" style="margin-top: 10px;">
                        <input type="hidden" name="action" value="update_task_status">
                        <input type="hidden" name="task_id" value="<?= htmlspecialchars($task['id'] ?? '') ?>">
                        <select name="status" style="padding: 6px; border-radius: 4px; border: 1px solid #ddd; margin-right: 8px;">
                            <option value="pending" <?= ($task['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="in_progress" <?= ($task['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed" <?= ($task['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                        <button type="submit" class="btn small">Update</button>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Discount Management Card -->
        <div class="admin-card">
            <h3>Discount Management</h3>
            <form method="POST">
                <input type="hidden" name="action" value="create_discount">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Discount Code</label>
                    <input type="text" name="code" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Discount Type</label>
                    <select name="type" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                        <option value="percentage">Percentage</option>
                        <option value="fixed">Fixed Amount</option>
                    </select>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Value</label>
                    <input type="number" step="0.01" name="value" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Minimum Order Amount</label>
                    <input type="number" step="0.01" name="min_order" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Maximum Discount</label>
                    <input type="number" step="0.01" name="max_discount" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Usage Limit</label>
                    <input type="number" name="usage_limit" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Valid Until</label>
                    <input type="date" name="valid_until" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                </div>
                <button type="submit" class="btn">Create Discount</button>
            </form>
            
            <h4 style="margin-top: 20px;">Active Discounts</h4>
            <?php if (empty($discounts)): ?>
                <p class="muted">No active discounts.</p>
            <?php else: ?>
                <?php foreach ($discounts as $discount): ?>
                <div style="padding: 10px; border: 1px solid #eee; border-radius: 8px; margin-bottom: 10px;">
                    <h5>Code: <?= htmlspecialchars($discount['code'] ?? 'N/A') ?></h5>
                    <p>Value: <?= htmlspecialchars($discount['value'] ?? '0') ?> 
                    <?= ($discount['type'] ?? 'percentage') === 'percentage' ? '%' : '৳' ?></p>
                    <p>Valid until: <?= htmlspecialchars($discount['valid_until'] ?? 'No expiry') ?></p>
                    <p>Created by: <?= htmlspecialchars($discount['created_by'] ?? 'Unknown') ?></p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Stock Management Card -->
        <div class="admin-card">
            <h3>Stock Management</h3>
            <h4>Low Stock Items (≤ 5 units)</h4>
            <?php
            $lowStockItems = array_filter($products, function($product) {
                return ($product['stock'] ?? 0) <= 5;
            });
            ?>
            
            <?php if (empty($lowStockItems)): ?>
                <p class="muted">No low stock items.</p>
            <?php else: ?>
                <?php foreach ($lowStockItems as $product): ?>
                <div style="padding: 10px; border: 1px solid #eee; border-radius: 8px; margin-bottom: 10px;">
                    <h5><?= htmlspecialchars($product['name'] ?? 'Unknown') ?></h5>
                    <p>Current Stock: <?= htmlspecialchars($product['stock'] ?? 0) ?></p>
                    <p>Category: <?= htmlspecialchars($product['category'] ?? 'Unknown') ?></p>
                    
                    <form method="POST" style="margin-top: 10px;">
                        <input type="hidden" name="action" value="set_reorder_level">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id'] ?? '') ?>">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <label style="font-weight: 500;">Reorder Level:</label>
                            <input type="number" name="reorder_level" value="<?= htmlspecialchars($product['reorder_level'] ?? 10) ?>" min="1" style="padding: 6px; border-radius: 4px; border: 1px solid #ddd; width: 80px;">
                            <button type="submit" class="btn small">Set</button>
                        </div>
                    </form>
                    
                    <form method="POST" style="margin-top: 10px;">
                        <input type="hidden" name="action" value="restock">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($product['id'] ?? '') ?>">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <label style="font-weight: 500;">Restock Qty:</label>
                            <input type="number" name="qty" value="10" min="1" style="padding: 6px; border-radius: 4px; border: 1px solid #ddd; width: 80px;">
                            <button type="submit" class="btn small">Restock</button>
                        </div>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Sales Reports Card -->
        <div class="admin-card">
            <h3>Sales Reports</h3>
            <form method="POST">
                <input type="hidden" name="action" value="generate_sales_report">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Report Type</label>
                    <select name="report_type" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Start Date</label>
                    <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">End Date (for weekly reports)</label>
                    <input type="date" name="end_date" value="<?= date('Y-m-d', strtotime('+6 days')) ?>" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                </div>
                <button type="submit" class="btn">Generate Report</button>
            </form>
            
            <h4 style="margin-top: 20px;">Recent Reports</h4>
            <?php if (empty($reports)): ?>
                <p class="muted">No reports generated yet.</p>
            <?php else: ?>
                <?php $recentReports = array_slice(array_reverse($reports), 0, 5); ?>
                <?php foreach ($recentReports as $report): ?>
                <div style="padding: 10px; border: 1px solid #eee; border-radius: 8px; margin-bottom: 10px;">
                    <h5><?= htmlspecialchars(ucfirst($report['type'] ?? 'Unknown')) ?> Report</h5>
                    <p>Period: <?= htmlspecialchars($report['start_date'] ?? '') ?> 
                    <?= !empty($report['end_date']) ? ' to ' . htmlspecialchars($report['end_date']) : '' ?></p>
                    <p>Generated: <?= htmlspecialchars($report['generated_at'] ?? '') ?></p>
                    <p>Revenue: ৳<?= number_format($report['data']['total_revenue'] ?? 0, 2) ?></p>
                    <p>Orders: <?= $report['data']['total_orders'] ?? 0 ?></p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>