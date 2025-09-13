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

// Get customer's orders
$customerOrders = array_filter($orders, function($order) use ($name) {
    return $order['by'] === $name;
});

// Reverse to show newest first
$customerOrders = array_reverse($customerOrders);

// Order status with better visual representation
function getStatusBadge($status) {
    $statusStyles = [
        'Processing' => ['color' => '#ff9800', 'icon' => '⏳', 'bg' => '#fff3cd'],
        'Confirmed' => ['color' => '#2196f3', 'icon' => '✅', 'bg' => '#d1ecf1'],
        'Packing' => ['color' => '#673ab7', 'icon' => '📦', 'bg' => '#e2e3e5'],
        'Shipped' => ['color' => '#3f51b5', 'icon' => '🚚', 'bg' => '#d4edda'],
        'Out for Delivery' => ['color' => '#ff5722', 'icon' => '📦', 'bg' => '#ffe5d6'],
        'Delivered' => ['color' => '#4caf50', 'icon' => '🏠', 'bg' => '#d4edda'],
        'Cancelled' => ['color' => '#f44336', 'icon' => '❌', 'bg' => '#f8d7da']
    ];
    
    $style = $statusStyles[$status] ?? ['color' => '#9e9e9e', 'icon' => '❓', 'bg' => '#f8f9fa'];
    
    return '<span style="background: '.$style['bg'].'; color: '.$style['color'].'; 
            padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: 500;
            display: inline-flex; align-items: center; gap: 4px;">
            '.$style['icon'].' '.$status.'</span>';
}
?>
<style>
/* Order Card Styles */
.order-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid #eaeaea;
}

.order-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.12);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    flex-wrap: wrap;
    gap: 10px;
}

.order-id {
    font-weight: 700;
    color: #2c3e50;
    font-size: 1.1em;
}

.order-date {
    color: #6c757d;
    font-size: 0.9em;
}

.order-body {
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.product-info {
    flex: 1;
    min-width: 250px;
}

.product-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 12px;
    font-size: 1.1em;
}

.order-details {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 8px 16px;
}

.detail-label {
    color: #6c757d;
    font-size: 0.9em;
}

.detail-value {
    color: #2c3e50;
    font-weight: 500;
}

.total-price {
    font-weight: 700;
    color: #28a745;
    font-size: 1.2em;
}

.payment-status {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.paid-status {
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 0.9em;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.paid-status.paid { 
    background: #d4edda; 
    color: #155724; 
}

.paid-status.unpaid { 
    background: #f8d7da; 
    color: #721c24; 
}

.order-footer {
    padding: 16px 20px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    flex-wrap: wrap;
}

/* Button Styles */
.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    font-size: 0.9em;
    gap: 6px;
}

.btn-outline {
    background: transparent;
    border: 1px solid #007bff;
    color: #007bff;
}

.btn-outline:hover {
    background: #007bff;
    color: white;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
    transform: translateY(-2px);
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #1e7e34;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background-color: white;
    margin: 8% auto;
    padding: 0;
    border-radius: 14px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.25);
    animation: slideIn 0.3s ease;
    overflow: hidden;
}

@keyframes slideIn {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-header {
    padding: 18px 24px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
}

.modal-header h3 {
    margin: 0;
    color: #2c3e50;
    font-weight: 600;
}

.close {
    color: #6c757d;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: color 0.2s ease;
}

.close:hover {
    color: #2c3e50;
}

.modal-body {
    padding: 24px;
}

.payment-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 24px;
}

.payment-option {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 20px 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.payment-option:hover {
    border-color: #007bff;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,123,255,0.1);
}

.payment-option.active {
    border-color: #007bff;
    background-color: #f0f7ff;
}

.payment-icon {
    font-size: 2.5em;
    margin-bottom: 12px;
    display: block;
}

.payment-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.95em;
}

.form-group {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 20px;
}

/* Status History */
.status-history {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
}

.status-history-title {
    font-size: 1em;
    font-weight: 600;
    margin-bottom: 12px;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-timeline {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.status-item {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #007bff;
    margin-top: 6px;
    flex-shrink: 0;
}

.status-content {
    flex: 1;
}

.status-text {
    font-weight: 500;
    color: #2c3e50;
    margin-bottom: 4px;
}

.status-date {
    font-size: 0.85em;
    color: #6c757d;
}

/* Responsive Design */
@media (max-width: 768px) {
    .order-body {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .payment-status {
        align-items: flex-start;
        width: 100%;
    }
    
    .payment-options {
        grid-template-columns: 1fr;
    }
    
    .order-footer {
        flex-direction: column;
    }
    
    .order-footer .btn {
        width: 100%;
        justify-content: center;
    }
    
    .modal-content {
        margin: 10% auto;
        width: 95%;
    }
    
    .form-group {
        flex-direction: column;
    }
    
    .form-group .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .order-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .order-details {
        grid-template-columns: 1fr;
        gap: 4px;
    }
}
</style>

<div class="panel">
    <h2 style="margin-bottom: 24px; color: #2c3e50; display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 1.4em;">📦</span> My Orders
    </h2>
    
    <?php if (empty($customerOrders)): ?>
        <div class="muted" style="padding: 40px 20px; text-align: center; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="font-size: 4em; margin-bottom: 20px;">🛒</div>
            <h3 style="color: #6c757d; margin-bottom: 12px;">No orders yet</h3>
            <p style="color: #6c757d; margin-bottom: 24px;">You haven't placed any orders yet. Start shopping to see your orders here.</p>
            <a href="dashboard.php?page=products" class="btn" style="background: #28a745; color: white; padding: 12px 24px;">
                Browse Products
            </a>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <?php foreach ($customerOrders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div class="order-id">Order #<?= htmlspecialchars($order['id']) ?></div>
                    <div class="order-date">Placed on: <?= date("F j, Y, g:i a", strtotime($order['date'])) ?></div>
                    <div><?= getStatusBadge($order['status']) ?></div>
                </div>
                
                <div class="order-body">
                    <div class="product-info">
                        <div class="product-name"><?= htmlspecialchars($order['product_name']) ?></div>
                        
                        <div class="order-details">
                            <div class="detail-label">Quantity:</div>
                            <div class="detail-value"><?= (int)$order['qty'] ?></div>
                            
                            <div class="detail-label">Unit Price:</div>
                            <div class="detail-value">৳<?= (int)$order['price'] ?></div>
                            
                            <div class="detail-label">Order Date:</div>
                            <div class="detail-value"><?= date("M j, Y", strtotime($order['date'])) ?></div>
                        </div>
                    </div>
                    
                    <div class="total-price">৳<?= (int)$order['total'] ?></div>
                    
                    <div class="payment-status">
                        <?php if (!empty($order['paid']) && $order['paid']): ?>
                            <div class="paid-status paid">
                                <span>✅</span> Paid with <?= htmlspecialchars($order['method'] ?? 'Unknown') ?>
                            </div>
                            <?php if (!empty($order['paid_date'])): ?>
                                <div style="font-size: 0.85em; color: #6c757d;">
                                    on <?= date("M j, Y", strtotime($order['paid_date'])) ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="paid-status unpaid">
                                <span></span> Unpaid
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="order-footer">
                    <a href="dashboard.php?page=order_details&id=<?= htmlspecialchars($order['id']) ?>" class="btn btn-outline">
                        <span>📋</span> View Details
                    </a>
                    
                    <?php if (empty($order['paid']) || $order['paid'] == false): ?>
                    <button type="button" onclick="showPaymentOptions('<?= htmlspecialchars($order['id']) ?>')" class="btn btn-primary">
                        <span>💳</span> Pay Now
                    </button>
                    <?php endif; ?>
                </div>
                
                <!-- Status History -->
                <?php if (!empty($order['status_history'])): ?>
                <div class="status-history">
                    <div class="status-history-title">
                        <span>📊</span> Order Journey
                    </div>
                    <div class="status-timeline">
                        <?php foreach ($order['status_history'] as $history): ?>
                        <div class="status-item">
                            <div class="status-dot"></div>
                            <div class="status-content">
                                <div class="status-text"><?= htmlspecialchars($history['status']) ?></div>
                                <div class="status-date"><?= date("M j, Y g:i a", strtotime($history['date'])) ?> • By <?= htmlspecialchars($history['by']) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Complete Payment</h3>
            <span class="close" onclick="closePaymentModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="paymentForm" method="post">
                <input type="hidden" name="action" value="mark_paid">
                <input type="hidden" name="order_id" id="paymentOrderId">
                
                <div class="payment-options">
                    <div class="payment-option" onclick="selectPaymentMethod('Cash')">
                        <div class="payment-icon">💵</div>
                        <div class="payment-name">Cash on Delivery</div>
                    </div>
                    
                    <div class="payment-option" onclick="selectPaymentMethod('bKash')">
                        <div class="payment-icon">📱</div>
                        <div class="payment-name">bKash</div>
                    </div>
                    
                    <div class="payment-option" onclick="selectPaymentMethod('Nagad')">
                        <div class="payment-icon">📱</div>
                        <div class="payment-name">Nagad</div>
                    </div>
                    
                    <div class="payment-option" onclick="selectPaymentMethod('Card')">
                        <div class="payment-icon">💳</div>
                        <div class="payment-name">Credit/Debit Card</div>
                    </div>
                </div>
                
                <input type="hidden" name="method" id="selectedPaymentMethod">
                
                <div class="form-group">
                    <button type="submit" class="btn btn-success">
                        <span></span> Confirm Payment
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">
                        <span>Cancel</span> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showPaymentOptions(orderId) {
    document.getElementById('paymentOrderId').value = orderId;
    document.getElementById('selectedPaymentMethod').value = '';
    document.getElementById('paymentModal').style.display = 'block';
    
    // Remove active class from all options
    document.querySelectorAll('.payment-option').forEach(option => {
        option.classList.remove('active');
    });
}

function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
}

function selectPaymentMethod(method) {
    document.getElementById('selectedPaymentMethod').value = method;
    
    // Remove active class from all options
    document.querySelectorAll('.payment-option').forEach(option => {
        option.classList.remove('active');
    });
    
    // Add active class to selected option
    event.currentTarget.classList.add('active');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('paymentModal');
    if (event.target == modal) {
        closePaymentModal();
    }
}

// Handle form submission
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const method = document.getElementById('selectedPaymentMethod').value;
    if (!method) {
        e.preventDefault();
        alert('Please select a payment method');
    }
});
</script>