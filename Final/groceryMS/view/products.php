<?php
// Auto-added DB bootstrap (keeps your design, replaces JSON with MySQL)
$__candidates = [
    __DIR__ . '/../model/compat.php',
    __DIR__ . '/model/compat.php',
    __DIR__ . '/../../model/compat.php',
];
<?php 
$cat = $_GET['cat'] ?? '';

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

// Function to get category color
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
        'Condiments' => '#CFCFC4'
    ];
    
    return $colors[$category] ?? '#DDDDDD';
}

function adjust_brightness($hex, $steps) {
    $steps = max(-255, min(255, $steps));
    $hex = str_replace('#', '', $hex);
    
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2).str_repeat(substr($hex, 1, 1), 2).str_repeat(substr($hex, 2, 1), 2);
    }
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    $r_hex = str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
    $g_hex = str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
    $b_hex = str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    
    return '#'.$r_hex.$g_hex.$b_hex;
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return (isset($_SESSION) && ($_SESSION['role'] ?? '') === 'admin');
    }
}
?>

<div class="panel">
  <h3>Products</h3>

<?php
  
    $categoriesFile = dirname(__DIR__) . "/data/categories.json";
    if (file_exists($categoriesFile)) {
        $cats = json_decode(file_get_contents($categoriesFile), true);
    } else {
      
        $cats = [];
        foreach ($products as $pp) {
            $c = $pp['category'] ?? 'Other';
            if (!in_array($c, $cats)) $cats[] = $c;
            if (count($cats) >= 15) break;
        }
        
        if (empty($cats)) $cats = [
            'Fruits','Vegetables','Dairy','Bakery','Beverages','Snacks','Frozen',
            'Meat','Seafood','Grains','Spices','Condiments','Personal Care','Household','Baby Care'
        ];
    }
?>

  <?php if ($cat === ''): ?>
    <!-- Show categories -->
    <div class="category-grid">
      <?php foreach ($cats as $c): ?>
        <div style="position:relative;">
          <a href="dashboard.php?page=products&cat=<?= urlencode($c) ?>" class="category-card" 
             style="background: linear-gradient(135deg, <?= get_category_color($c) ?>, <?= adjust_brightness(get_category_color($c), -20) ?>);">
            <?php $catImg = resolve_image($c); ?>
            <?php if (!empty($catImg)): ?>
              <img src="<?= $catImg ?>" alt="<?= htmlspecialchars($c) ?>" style="height: 80px; object-fit: contain;">
            <?php else: ?>
              <div style="height: 80px; display: flex; align-items: center; justify-content: center;">
                <span style="font-size: 24px; font-weight: bold; color: white;"><?= substr($c, 0, 2) ?></span>
              </div>
            <?php endif; ?>
            <div class="label" style="font-weight: bold; margin-top: 10px; color: white;"><?= htmlspecialchars($c) ?></div>
            <div style="color: rgba(255,255,255,0.8); font-size: 13px;">
              <?= count(array_filter($products, fn($p)=> ($p['category'] ?? '') === $c)) ?> items
            </div>
          </a>

          <!-- Admin: Delete entire category (removes all products in that category) -->
          <?php if (function_exists('is_admin') && is_admin()): ?>
            <form method="post" style="position:absolute; top:6px; right:6px;">
              <input type="hidden" name="action" value="delete_category">
              <input type="hidden" name="category" value="<?= htmlspecialchars($c) ?>">
              <button type="submit" class="btn ghost small" onclick="return confirm('Delete entire category <?= htmlspecialchars($c) ?> and all its products? This cannot be undone.');">Delete</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Add product (only for manager/admin) on ROOT -->
    <?php if (function_exists('can_manage') && can_manage()): ?>
      <div class="panel" style="margin-top:20px">
        <h4>Add Product</h4>
        <form method="post">
          <input type="hidden" name="action" value="add_product">
          <input type="hidden" name="image" id="root_image_field" value="">

          <div class="form-row">
            <label>Name:</label>
            <input type="text" name="name" id="root_name" required>
          </div>
          <div class="form-row">
            <label>Category:</label>
            <input type="text" name="category" id="root_category" required>
          </div>
          <div class="form-row">
            <label>Subcategory:</label>
            <input type="text" name="subcategory" id="root_subcategory" required>
          </div>
          <div class="form-row">
            <label>Price:</label>
            <input type="number" step="0.01" name="price" required>
          </div>
          <div class="form-row">
            <label>Stock:</label>
            <input type="number" name="stock" required>
          </div>

          <div class="muted" style="margin:6px 0 10px">
            Tip: Put <code>ProductName.png</code> (e.g., <code>Apple.png</code>) or parent image
            <code>Fruits.png</code> in <code>images/products/</code>.
          </div>

          <button class="btn">Add</button>
        </form>
      </div>
    <?php endif; ?>

  <?php else: ?>
    <!-- Inside a category -->
    <div style="margin-bottom:20px; display:flex; align-items:center; gap:12px; padding: 12px; background: linear-gradient(135deg, <?= get_category_color($cat) ?>, <?= adjust_brightness(get_category_color($cat), -20) ?>); border-radius: 10px;">
      <?php $catImg = resolve_image($cat); ?>
      <?php if (!empty($catImg)): ?>
        <img src="<?= $catImg ?>" alt="<?= htmlspecialchars($cat) ?>" style="height:56px; object-fit:contain; border-radius:8px; background: white; padding: 4px;">
      <?php else: ?>
        <div style="width:56px; height:56px; background:rgba(255,255,255,0.2); border-radius:8px; display:flex; align-items:center; justify-content:center; font-weight:bold; color: white;">
          <?= substr($cat, 0, 2) ?>
        </div>
      <?php endif; ?>
      <div style="color: white;">
        <strong>Category:</strong> <?= htmlspecialchars($cat) ?>
      </div>
      <div style="margin-left:auto">
        <a href="dashboard.php?page=products" class="btn ghost" style="background: rgba(255,255,255,0.2); color: white; border-color: white;">Back</a>
      </div>
    </div>

    <?php
      $list = array_values(array_filter($products, fn($p)=> ($p['category'] ?? '') === $cat));
      if (empty($list)) {
        echo "<div class='muted'>No products found in this category.</div>";
      }
    ?>

    <!-- Add product (only for manager/admin) INSIDE CATEGORY -->
    <?php if (function_exists('can_manage') && can_manage()): ?>
      <div class="panel" style="margin:10px 0 16px">
        <h4>Add Product to <?= htmlspecialchars($cat) ?></h4>
        <form method="post">
          <input type="hidden" name="action" value="add_product">
          <input type="hidden" name="image" id="cat_image_field" value="">

          <div class="form-row">
            <label>Name:</label>
            <input type="text" name="name" id="cat_name" required>
          </div>
          <div class="form-row">
            <label>Category:</label>
            <input type="text" name="category" value="<?= htmlspecialchars($cat) ?>" readonly style="background: #f7f7f7;">
          </div>
          <div class="form-row">
            <label>Subcategory:</label>
            <input type="text" name="subcategory" id="cat_subcategory" required>
          </div>
          <div class="form-row">
            <label>Price:</label>
            <input type="number" step="0.01" name="price" required>
          </div>
          <div class="form-row">
            <label>Stock:</label>
            <input type="number" name="stock" required>
          </div>

          <div class="muted" style="margin:6px 0 10px">
            Put <code><?= htmlspecialchars($cat) ?></code> and product images into <code>images/products/</code>.
          </div>

          <button class="btn">Add</button>
        </form>
      </div>
    <?php endif; ?>

    <?php if (!empty($list)): ?>
      <div class="product-grid">
        <?php foreach ($list as $p): ?>
          <div class="product-card">
            <?php $prodImg = resolve_image($p['name']); ?>
            <?php if (!empty($prodImg)): ?>
              <img src="<?= $prodImg ?>" alt="<?= htmlspecialchars($p['name']) ?>">
            <?php else: ?>
              <div style="height: 140px; background: <?= get_category_color($p['category'] ?? 'Other') ?>; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                <span style="font-size: 20px; font-weight: bold; color: white;"><?= substr($p['name'], 0, 2) ?></span>
              </div>
            <?php endif; ?>

            <div class="name"><?= htmlspecialchars($p['name']) ?></div>
            <div class="meta">
              <div>৳ <?= htmlspecialchars($p['price']) ?></div>
              <div><?= htmlspecialchars($p['subcategory'] ?? '') ?></div>
            </div>

            <div class="stock <?= ((int)$p['stock'] <= 5) ? 'low' : '' ?>">
              Stock: <?= (int)$p['stock'] ?>
            </div>

        
<?php
$avgRating = get_average_rating($p['id']);
$ratingCount = get_rating_count($p['id']);
?>
<div style="display: flex; align-items: center; gap: 4px; margin: 8px 0;">
    <?php for ($i = 1; $i <= 5; $i++): ?>
        <span style="color: <?= $i <= round($avgRating) ? '#ff9800' : '#ddd' ?>; font-size: 0.9em;">★</span>
    <?php endfor; ?>
    <?php if ($ratingCount > 0): ?>
        <span style="font-size: 0.8em; color: #666;">(<?= $avgRating ?>)</span>
    <?php endif; ?>
</div>

<div class="actions">

  <form method="post" style="display:inline-block;">
    <input type="hidden" name="action" value="create_order">
    <input type="hidden" name="product_id" value="<?= htmlspecialchars($p['id']) ?>">
    <input type="number" name="qty" value="1" min="1" class="qty">
    <button type="submit" class="btn">
         <?= ($role === 'customer') ? 'Add to Cart' : 'Sell' ?>
    </button>
  </form>
  
  <?php if ($role === 'customer'): ?>
    <form method="post" style="display:inline-block; margin-left:6px">
      <input type="hidden" name="action" value="add_to_wishlist">
      <input type="hidden" name="product_id" value="<?= htmlspecialchars($p['id']) ?>">
      <button type="submit" class="btn small" style="background: #ff4081; color: white;">
        Wishlist
      </button>
    </form>
  <?php endif; ?>
  
  <!-- View Reviews Link -->
  <a href="dashboard.php?page=product_reviews&id=<?= htmlspecialchars($p['id']) ?>" class="btn small ghost" style="margin-left: 6px;">
    Reviews (<?= $ratingCount ?>)
  </a>
  
  <!-- Manager/Admin: Edit product (opens modal) -->
  <?php if (function_exists('can_manage') && can_manage()): ?>
    <button class="btn ghost small edit-btn"
      data-id="<?= htmlspecialchars($p['id']) ?>"
      data-name="<?= htmlspecialchars($p['name']) ?>"
      data-price="<?= htmlspecialchars($p['price']) ?>"
      data-stock="<?= htmlspecialchars($p['stock']) ?>"
      data-category="<?= htmlspecialchars($p['category']) ?>"
      data-subcategory="<?= htmlspecialchars($p['subcategory']) ?>"
      data-image="<?= htmlspecialchars($p['image'] ?? '') ?>"
    >Edit</button>

    <!-- Delete product (manager allowed to delete products) -->
    <form method="post" style="display:inline-block;margin-left:6px">
      <input type="hidden" name="action" value="delete_product">
      <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
      <button type="submit" class="btn ghost small" onclick="return confirm('Delete product <?= htmlspecialchars(addslashes($p['name'])) ?>?');">Delete</button>
    </form>
  <?php endif; ?>

  <!-- Stock Adjust (manager only) -->
  <?php if (function_exists('can_manage') && can_manage()): ?>
    <form method="post" style="display:inline-block;margin-left:4px">
      <input type="hidden" name="action" value="adjust_stock">
      <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
      <input type="hidden" name="delta" value="-1">
      <button class="btn ghost small">-1</button>
    </form>
    <form method="post" style="display:inline-block;margin-left:4px">
      <input type="hidden" name="action" value="adjust_stock">
      <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
      <input type="hidden" name="delta" value="1">
      <button class="btn ghost small">+1</button>
    </form>
  <?php endif; ?>
</div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <?php endif; ?>
</div>

<!-- Edit product modal (hidden by default) -->
<div id="editModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:9999;">
  <div style="background:#fff; padding:24px; border-radius:12px; width:440px; max-width:95%; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
    <h4 style="color: #2c5282; margin-top: 0;">Edit product</h4>
    <form method="post" id="editForm">
      <input type="hidden" name="action" value="update_product">
      <input type="hidden" name="id" id="edit_id">

      <div class="form-row">
        <label>Name:</label>
        <input type="text" name="name" id="edit_name" required>
      </div>
      <div class="form-row">
        <label>Category:</label>
        <input type="text" name="category" id="edit_category" required>
      </div>
      <div class="form-row">
        <label>Subcategory:</label>
        <input type="text" name="subcategory" id="edit_subcategory" required>
      </div>
      <div class="form-row">
        <label>Price:</label>
        <input type="number" step="0.01" name="price" id="edit_price" required>
      </div>
      <div class="form-row">
        <label>Stock:</label>
        <input type="number" name="stock" id="edit_stock" required>
      </div>
      <div class="form-row">
        <label>Image filename (optional):</label>
        <input type="text" name="image" id="edit_image" placeholder="apple.png">
      </div>

      <div style="display:flex; gap:8px; margin-top:12px;">
        <button type="submit" class="btn">Save</button>
        <button type="button" class="btn ghost" id="editCancel">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('click', function(e){
    
    if (e.target && e.target.classList.contains('edit-btn')) {
      const b = e.target;
      document.getElementById('edit_id').value = b.dataset.id || '';
      document.getElementById('edit_name').value = b.dataset.name || '';
      document.getElementById('edit_price').value = b.dataset.price || '';
      document.getElementById('edit_stock').value = b.dataset.stock || '';
      document.getElementById('edit_category').value = b.dataset.category || '';
      document.getElementById('edit_subcategory').value = b.dataset.subcategory || '';
      document.getElementById('edit_image').value = b.dataset.image || '';
      document.getElementById('editModal').style.display = 'flex';
    }
    // Cancel edit
    if (e.target && e.target.id === 'editCancel') {
      document.getElementById('editModal').style.display = 'none';
    }
  });

  // Close modal on outside click
  document.getElementById('editModal').addEventListener('click', function(ev){
    if (ev.target === this) this.style.display = 'none';
  });
</script>
