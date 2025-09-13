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

// Load customer's wishlist
$wishlistProductIds = get_wishlist($name);
$wishlistProducts = [];

foreach ($products as $product) {
    if (isset($product['id']) && in_array($product['id'], $wishlistProductIds)) {
        $wishlistProducts[] = $product;
    }
}
?>

<div class="panel">
    <h3>My Wishlist</h3>
    
    <?php if (empty($wishlistProducts)): ?>
        <div class="muted" style="padding: 20px; text-align: center;">
            Your wishlist is empty. Start adding products you love!
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($wishlistProducts as $p): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?= imgPath($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                    </div>
                    <div class="product-info">
                        <h4><?= htmlspecialchars($p['name']) ?></h4>
                        <div class="product-category"><?= htmlspecialchars($p['category']) ?> → <?= htmlspecialchars($p['subcategory']) ?></div>
                        <div class="product-price">৳<?= (int)$p['price'] ?></div>
                        <div class="product-stock">Stock: <?= (int)$p['stock'] ?></div>
                        
                        <div style="display: flex; gap: 8px; margin-top: 12px;">
                            <form method="post">
                                <input type="hidden" name="action" value="create_order">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($p['id']) ?>">
                                <input type="hidden" name="qty" value="1">
                                <button class="btn small">Order Now</button>
                            </form>
                            
                            <form method="post">
                                <input type="hidden" name="action" value="remove_from_wishlist">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($p['id']) ?>">
                                <button class="btn small danger">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>