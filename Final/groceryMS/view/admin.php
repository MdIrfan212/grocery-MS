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
// Check if user is admin (redundant but safe)
if (!function_exists('is_admin') || !is_admin()) {
    header("Location: dashboard.php?page=home");
    exit;
}

// Load any necessary data
$dataDir = dirname(__DIR__) . "/data";
$productsFile = $dataDir . "/products.json";
$ordersFile = $dataDir . "/orders.json";
$logsFile = $dataDir . "/activity_logs.json";



// Load data
$products = load_json($productsFile);
$orders = load_json($ordersFile);
$activityLogs = load_json($logsFile);

// Load categories from categories.json if it exists, otherwise from products
$categoriesFile = $dataDir . "/categories.json";
if (file_exists($categoriesFile)) {
    $categories = json_decode(file_get_contents($categoriesFile), true);
} else {
    // Fallback: get unique categories from products
    $categories = array_values(array_unique(array_map(fn($p) => $p['category'], $products)));
    sort($categories);
}

// Handle form submissions
$notice = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Add new category
    if ($action === 'add_category') {
        $newCategory = trim($_POST['new_category'] ?? '');
        if (!empty($newCategory)) {
            // Load current categories from file
            $categoriesFile = $dataDir . "/categories.json";
            $currentCategories = file_exists($categoriesFile) ? json_decode(file_get_contents($categoriesFile), true) : [];
            
            if (!in_array($newCategory, $currentCategories)) {
                $currentCategories[] = $newCategory;
                sort($currentCategories);
                
                // Save updated categories
                file_put_contents($categoriesFile, json_encode($currentCategories, JSON_PRETTY_PRINT));
                
                // Update local variable for display
                $categories = $currentCategories;
                
                $notice = "✅ Category '$newCategory' added successfully.";
            } else {
                $notice = "❌ Category '$newCategory' already exists.";
            }
        }
    }
    
      // Delete category
    if ($action === 'delete_category') {
        $categoryToDelete = trim($_POST['category_to_delete'] ?? '');
        if (!empty($categoryToDelete)) {
            // Filter products to remove those in the deleted category
            $updatedProducts = array_filter($products, fn($p) => $p['category'] !== $categoryToDelete);
            
            if (count($updatedProducts) !== count($products)) {
                // Save updated products
                file_put_contents($productsFile, json_encode(array_values($updatedProducts), JSON_PRETTY_PRINT));
                $products = $updatedProducts;
                
                // Update categories list in file
                $categoriesFile = $dataDir . "/categories.json";
                $currentCategories = file_exists($categoriesFile) ? json_decode(file_get_contents($categoriesFile), true) : [];
                $updatedCategories = array_values(array_filter($currentCategories, fn($c) => $c !== $categoryToDelete));
                
                file_put_contents($categoriesFile, json_encode($updatedCategories, JSON_PRETTY_PRINT));
                
                // Update local variable for display
                $categories = $updatedCategories;
                
                $notice = "✅ Category '$categoryToDelete' and all its products deleted successfully.";
            } else {
                $notice = "❌ Category '$categoryToDelete' not found or already empty.";
            }
        }
    }
    
    // Generate report
if ($action === 'generate_report') {
    $reportType = $_POST['report_type'] ?? 'sales_summary';
    $startDate = $_POST['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
    $endDate = $_POST['end_date'] ?? date('Y-m-d');
    $format = $_POST['format'] ?? 'web';
    
    // Report types with descriptions
    $reportTypes = [
        'sales_summary' => 'Sales Summary Report',
        'inventory_report' => 'Inventory Status Report', 
        'customer_activity' => 'Customer Activity Report',
        'financial_report' => 'Financial Overview Report',
        'low_stock' => 'Low Stock Alert Report'
    ];
    
    $reportName = $reportTypes[$reportType] ?? 'Sales Summary Report';
    
    $notice = "✅ Generated '$reportName' for $startDate to $endDate in $format format.";
    
    // Log this activity
    $newLog = [
        'date' => date("Y-m-d H:i:s"),
        'user' => $_SESSION['user'] ?? 'Admin',
        'action' => "Generated report: $reportName ($startDate to $endDate)"
    ];
    $activityLogs[] = $newLog;
    file_put_contents($logsFile, json_encode($activityLogs, JSON_PRETTY_PRINT));
}

// Handle quick reports
$quickReport = $_GET['quick_report'] ?? '';
if ($quickReport) {
    $quickReports = [
        'today_sales' => "Today's Sales Report",
        'low_stock' => 'Low Stock Report',
        'top_products' => 'Top Products Report',
        'unpaid_orders' => 'Unpaid Orders Report'
    ];
    
    $reportName = $quickReports[$quickReport] ?? 'Quick Report';
    $notice = "✅ Generated $reportName for quick review.";
    
    // Log this activity
    $newLog = [
        'date' => date("Y-m-d H:i:s"),
        'user' => $_SESSION['user'] ?? 'Admin',
        'action' => "Generated quick report: $reportName"
    ];
    $activityLogs[] = $newLog;
    file_put_contents($logsFile, json_encode($activityLogs, JSON_PRETTY_PRINT));
}
}
?>

<div class="panel">
    <h3>Admin Panel</h3>
    <p class="muted">Manage categories, generate reports, and monitor system activity.</p>
    
    <?php if ($notice): ?>
        <div class="notice"><?= htmlspecialchars($notice) ?></div>
    <?php endif; ?>
    
    <div class="admin-panel">
        <!-- Manage Categories Card -->
        <div class="admin-card">
            <h3>Manage Product Categories</h3>
            
            <h4>Add New Category</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_category">
                <div class="form-row">
                    <input type="text" name="new_category" placeholder="Enter new category name" required style="width: 100%;">
                </div>
                <button type="submit" class="btn">Add Category</button>
            </form>
            
            <h4 style="margin-top: 20px;">Existing Categories</h4>
            <ul class="category-list">
                <?php if (empty($categories)): ?>
                    <li class="muted">No categories found.</li>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <span><?= htmlspecialchars($category) ?></span>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete_category">
                                <input type="hidden" name="category_to_delete" value="<?= htmlspecialchars($category) ?>">
                                <button type="submit" class="btn ghost small" onclick="return confirm('Are you sure? This will delete ALL products in this category!')">Delete</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        
       <!-- Generate Reports Card -->
<div class="admin-card">
    <h3>Generate Reports</h3>
    <p class="muted">Select a report type and date range to generate detailed reports:</p>
    
    <form method="POST">
        <input type="hidden" name="action" value="generate_report">
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Report Type</label>
            <select name="report_type" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                <option value="sales_summary">Sales Summary Report</option>
                <option value="inventory_report">Inventory Status Report</option>
                <option value="customer_activity">Customer Activity Report</option>
                <option value="financial_report">Financial Overview Report</option>
                <option value="low_stock">Low Stock Alert Report</option>
            </select>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Start Date</label>
                <input type="date" name="start_date" value="<?= date('Y-m-d', strtotime('-7 days')) ?>" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">End Date</label>
                <input type="date" name="end_date" value="<?= date('Y-m-d') ?>" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
            </div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Format</label>
            <div style="display: flex; gap: 10px;">
                <label style="display: flex; align-items: center; gap: 5px;">
                    <input type="radio" name="format" value="web" checked> Web View
                </label>
                <label style="display: flex; align-items: center; gap: 5px;">
                    <input type="radio" name="format" value="pdf"> PDF
                </label>
                <label style="display: flex; align-items: center; gap: 5px;">
                    <input type="radio" name="format" value="csv"> CSV/Excel
                </label>
            </div>
        </div>
        
        <button type="submit" class="btn" style="width: 100%;">
            <span style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                📊 Generate Report
            </span>
        </button>
    </form>
    
    <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee;">
        <h4>Quick Reports</h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <a href="?quick_report=today_sales" class="btn ghost small" style="text-align: center; padding: 8px;">
                Today's Sales
            </a>
            <a href="?quick_report=low_stock" class="btn ghost small" style="text-align: center; padding: 8px;">
                Low Stock
            </a>
            <a href="?quick_report=top_products" class="btn ghost small" style="text-align: center; padding: 8px;">
                Top Products
            </a>
            <a href="?quick_report=unpaid_orders" class="btn ghost small" style="text-align: center; padding: 8px;">
                Unpaid Orders
            </a>
        </div>
    </div>
    
    <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee;">
        <h4>Report Summary</h4>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <div style="padding: 10px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                <div style="font-size: 1.5em; font-weight: bold; color: #4caf50;">৳<?= number_format(array_sum(array_map(fn($o) => $o['total'], $orders)), 2) ?></div>
                <div class="muted">Total Revenue</div>
            </div>
            <div style="padding: 10px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                <div style="font-size: 1.5em; font-weight: bold; color: #2196f3;"><?= count($orders) ?></div>
                <div class="muted">Total Orders</div>
            </div>
        </div>
    </div>
</div>
        
        <!-- System Activity Card -->
        <div class="admin-card">
            <h3>System Activity Logs</h3>
            <p class="muted">Recent system activities and events:</p>
            
            <div style="max-height: 400px; overflow-y: auto; margin-top: 15px;">
                <?php if (empty($activityLogs)): ?>
                    <div class="muted" style="padding: 20px; text-align: center;">No activity logs found.</div>
                <?php else: ?>
                    <?php 
                    $recentLogs = array_slice(array_reverse($activityLogs), 0, 10);
                    foreach ($recentLogs as $log): 
                    ?>
                        <div class="log-entry">
                            <div class="log-action"><?= htmlspecialchars($log['action'] ?? 'N/A') ?></div>
                            <div class="log-date">By <?= htmlspecialchars($log['user'] ?? 'Unknown') ?> on <?= htmlspecialchars($log['date'] ?? 'Unknown date') ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                <form method="POST">
                    <input type="hidden" name="action" value="clear_logs">
                    <button type="submit" class="btn ghost small" onclick="return confirm('Clear all activity logs?')">Clear Logs</button>
                    <button type="button" class="btn small" onclick="alert('This would export logs in a real application')">Export Logs</button>
                </form>
            </div>
        </div>
    </div>
</div>