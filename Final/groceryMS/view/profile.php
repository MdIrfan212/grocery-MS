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
if ($role !== 'customer') {
    header("Location: dashboard.php?page=home");
    exit;
}

// Load customer data
$customersFile = $dataDir . "/customers.json";
if (!file_exists($customersFile)) {
    save_json($customersFile, []);
}
$customers = load_json($customersFile);

// Find current customer
$currentCustomer = null;
foreach ($customers as $c) {
    if ($c['email'] === $_SESSION['user'] || $c['name'] === $_SESSION['user']) {
        $currentCustomer = $c;
        break;
    }
}

// Handle profile updates
$profileNotice = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    // Validate phone number (11 digits)
    if (!empty($_POST['phone']) && !preg_match('/^01[3-9]\d{8}$/', $_POST['phone'])) {
        $profileNotice = "invalid phone number.";
    } else {
        // Check if customer exists, if not create new
        if (!$currentCustomer) {
            $currentCustomer = [
                'name' => $_SESSION['user'],
                'email' => $_SESSION['user'],
                'phone' => '',
                'address' => '',
                'password' => '' // Will be set from login data
            ];
        }
        
        $updatedData = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'address' => $_POST['address'],
            'password' => $currentCustomer['password'] ?? '' // Keep existing password
        ];
        
        // Update or add customer in array
        $found = false;
        foreach ($customers as &$c) {
            if ($c['email'] === $currentCustomer['email'] || $c['name'] === $currentCustomer['name']) {
                $c = $updatedData;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $customers[] = $updatedData;
        }
        
        save_json($customersFile, $customers);
        $profileNotice = "✅ Profile updated successfully.";
        $currentCustomer = $updatedData;
        
        // Update session if name changed
        if ($_SESSION['user'] !== $updatedData['name']) {
            $_SESSION['user'] = $updatedData['name'];
        }
    }
}
?>

<div class="profile-container">
    <div class="profile-header">
        <h2><i class="icon-user"></i> My Profile</h2>
        <p>View and update your personal information and order history</p>
    </div>
    
    <?php if ($profileNotice): ?>
    <div class="notice <?= strpos($profileNotice, '❌') !== false ? 'error' : 'success' ?>"><?= $profileNotice ?></div>
    <?php endif; ?>
    
    <div class="profile-content">
        <div class="profile-form-section">
            <h3>Personal Information</h3>
            <form method="POST" class="profile-form">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-row">
                    <label for="name"><i class="icon-id-card"></i> Full Name:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($currentCustomer['name'] ?? $name) ?>" required>
                </div>
                
                <div class="form-row">
                    <label for="email"><i class="icon-email"></i> Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($currentCustomer['email'] ?? '') ?>" required>
                </div>
                
                <div class="form-row">
                    <label for="phone"><i class="icon-phone"></i> Phone Number:</label>
                    <input type="tel" id="phone" name="phone" pattern="01[3-9]\d{8}" 
                           title=" 11-digit Bangladeshi number   " 
                           value="<?= htmlspecialchars($currentCustomer['phone'] ?? '') ?>"
                           placeholder=<"">
                    <small class="input-hint"></small>
                </div>
                
                <div class="form-row">
                    <label for="address"><i class="icon-address"></i> Address:</label>
                    <textarea id="address" name="address" rows="3" placeholder="Enter your complete address"><?= htmlspecialchars($currentCustomer['address'] ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="icon-save"></i> Update Profile</button>
            </form>
        </div>
        
        <div class="profile-stats-section">
            <h3>Order Statistics</h3>
            <div class="stats-card">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="icon-orders"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Total Orders</span>
                        <span class="stat-value">
                            <?php
                            $customerOrders = array_filter($orders, function($order) {
                                return $order['by'] === $_SESSION['user'];
                            });
                            $totalOrders = count($customerOrders);
                            echo $totalOrders;
                            ?>
                        </span>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="icon-completed"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Completed Orders</span>
                        <span class="stat-value">
                            <?php
                            $paidOrders = count(array_filter($customerOrders, function($order) {
                                return !empty($order['paid']);
                            }));
                            echo $paidOrders;
                            ?>
                        </span>
                    </div>
                </div>
                
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="icon-money"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Total Spent</span>
                        <span class="stat-value">
                            ৳<?php
                            $totalSpent = array_sum(array_map(function($order) {
                                return (float)($order['total'] ?? 0);
                            }, $customerOrders));
                            echo number_format($totalSpent, 2);
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.profile-header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e0e0e0;
}

.profile-header h2 {
    color: #fff;
    margin-bottom: 10px;
    font-size: 28px;
}

.profile-header p {
    color: #fff;
    font-size: 16px;
}

.profile-content {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
}

.profile-form-section {
    flex: 1;
    min-width: 300px;
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.profile-form-section h3 {
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
}

.profile-form .form-row {
    margin-bottom: 20px;
}

.profile-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #34495e;
}

.profile-form input,
.profile-form textarea {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    transition: border-color 0.3s;
}

.profile-form input:focus,
.profile-form textarea:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.input-hint {
    display: block;
    margin-top: 5px;
    color: #7f8c8d;
    font-size: 12px;
}

.btn-primary {
    background: #3498db;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-primary:hover {
    background: #2980b9;
}

.profile-stats-section {
    flex: 1;
    min-width: 300px;
}

.profile-stats-section h3 {
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
}

.stats-card {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.stat-item {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f1f1;
}

.stat-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 20px;
    color: #3498db;
}

.stat-info {
    flex: 1;
}

.stat-label {
    display: block;
    color: #7f8c8d;
    font-size: 14px;
}

.stat-value {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
}

.notice.success {
    background: #d4edda;
    color: #155724;
    padding: 12px 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    border-left: 4px solid #28a745;
}

.notice.error {
    background: #f8d7da;
    color: #721c24;
    padding: 12px 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    border-left: 4px solid #dc3545;
}

/* Icon font classes */
.icon-user:before { content: "👤 "; }
.icon-id-card:before { content: "🪪 "; }
.icon-email:before { content: "📧 "; }
.icon-phone:before { content: "📱 "; }
.icon-address:before { content: "🏠 "; }
.icon-save:before { content: "💾 "; }
.icon-orders:before { content: "📦 "; }
.icon-completed:before { content: "✅ "; }
.icon-money:before { content: "💰 "; }

@media (max-width: 768px) {
    .profile-content {
        flex-direction: column;
    }
}
</style>