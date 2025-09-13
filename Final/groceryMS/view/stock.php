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
// show all products with restock controls

/**
 * Resolve an image by base name with common extensions.
 * Looks in ../images/products/<basename>.(png|jpg|jpeg|webp|gif)
 * Returns relative path for <img src="...">, or empty string if not found.
 */
function resolve_image($basename) {
    $dir = __DIR__ . '/../images/products/';
    $rel = '../images/products/';
    $exts = ['png','jpg','jpeg','webp','gif'];
    foreach ($exts as $ext) {
        $full = $dir . $basename . '.' . $ext;
        if (file_exists($full)) return $rel . $basename . '.' . $ext;
    }
    return '';
}

// Function to get category color (same as in products.php)
function get_category_color($category) {
    $colors = [
        'Fruits' => '#FF9AA2',
        'Vegetables' => '#C5E99B',
        'Dairy' => '#FFFD98',
        'Bakery' => '#D4A76A',
        'Beverages' => '#8FB8ED',
        'Snacks' => '#FFB347',
        'Frozen' => '#AEC6CF',
        'Meat' => '#FF6961',
        'Seafood' => '#77DD77',
        'Grains' => '#FDFD96',
        'Spices' => '#B39EB5',
        'Condiments' => '#CFCFC4',
        'Personal Care' => '#FFB6C1',
        'Household' => '#E6E6FA',
        'Baby Care' => '#87CEFA'
    ];
    
    return $colors[$category] ?? '#DDDDDD';
}
?>
<div class="panel">
  <h3>Stock Management</h3>
  
  <?php if (empty($products)): ?>
    <div class="muted">No products found in inventory.</div>
  <?php else: ?>
    <table class="table">
      <tr>
        <th>Image</th>
        <th>Product</th>
        <th>Category › Type</th>
        <th>Stock</th>
        <th>Price</th>
        <th>Action</th>
      </tr>
      <?php foreach ($products as $p): ?>
        <tr>
          <td style="width:80px">
            <?php $prodImg = resolve_image($p['name']); ?>
            <?php if (!empty($prodImg)): ?>
              <img src="<?= $prodImg ?>" style="height:48px;width:48px;object-fit:cover;border-radius:6px" alt="<?= htmlspecialchars($p['name']) ?>">
            <?php else: ?>
              <div style="height:48px;width:48px;background:<?= get_category_color($p['category'] ?? 'Other') ?>;display:flex;align-items:center;justify-content:center;border-radius:6px">
                <span style="font-size:14px;font-weight:bold;color:white;"><?= substr($p['name'], 0, 2) ?></span>
              </div>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($p['name']) ?></td>
          <td><?= htmlspecialchars($p['category']) ?> › <?= htmlspecialchars($p['subcategory']) ?></td>
          <td>
            <span class="badge <?= ((int)$p['stock'] <= 5) ? 'danger' : 'ok' ?>">
              <?= (int)$p['stock'] ?>
            </span>
          </td>
          <td>৳ <?= htmlspecialchars($p['price']) ?></td>
          <td>
            <?php if (can_manage()): ?>
              <div style="display: flex; gap: 8px; align-items: center;">
                <!-- Quick stock adjustment buttons -->
                <form method="post" style="display:inline-block">
                  <input type="hidden" name="action" value="adjust_stock">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                  <input type="hidden" name="delta" value="-1">
                  <button class="btn ghost small" title="Reduce stock by 1">-1</button>
                </form>
                
                <form method="post" style="display:inline-block">
                  <input type="hidden" name="action" value="adjust_stock">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                  <input type="hidden" name="delta" value="1">
                  <button class="btn ghost small" title="Increase stock by 1">+1</button>
                </form>
                
                <!-- Restock form -->
                <form method="post" style="display:inline-block">
                  <input type="hidden" name="action" value="restock">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                  <input type="number" name="qty" value="10" min="1" max="1000" style="width:80px;padding:6px;border-radius:6px;border:1px solid #ddd" title="Quantity to add">
                  <button class="btn small">Restock</button>
                </form>
              </div>
            <?php else: ?>
              <?php if ((int)$p['stock'] <= 0): ?>
                <em>Out of stock — notify manager</em>
              <?php elseif ((int)$p['stock'] <= 5): ?>
                <em>Low stock — notify manager</em>
              <?php else: ?>
                <em>Stock level OK</em>
              <?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
    
    <!-- Stock summary -->
    <div style="margin-top: 20px; padding: 12px; background: #f8f8f8; border-radius: 6px;">
      <strong>Stock Summary:</strong>
      <?php
      $totalProducts = count($products);
      $outOfStock = count(array_filter($products, fn($p) => (int)$p['stock'] <= 0));
      $lowStock = count(array_filter($products, fn($p) => (int)$p['stock'] > 0 && (int)$p['stock'] <= 5));
      $inStock = $totalProducts - $outOfStock - $lowStock;
      ?>
      <span style="margin-left: 15px;">Total Products: <?= $totalProducts ?></span>
      <span style="margin-left: 15px; color: #4caf50;">In Stock: <?= $inStock ?></span>
      <span style="margin-left: 15px; color: #ff9800;">Low Stock: <?= $lowStock ?></span>
      <span style="margin-left: 15px; color: #f44336;">Out of Stock: <?= $outOfStock ?></span>
    </div>
  <?php endif; ?>
</div>