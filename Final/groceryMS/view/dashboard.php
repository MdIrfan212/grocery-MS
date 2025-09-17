<?php

$__candidates = [
    __DIR__ . '/../model/compat.php',
    __DIR__ . '/model/compat.php',
    __DIR__ . '/../../model/compat.php',
];
foreach ($__candidates as $__p) { if (file_exists($__p)) { require_once $__p; break; } }
?>
<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}
$name = $_SESSION['user'];
$role = $_SESSION['role'] ?? 'customer';

// data files
$dataDir = dirname(__DIR__) . "/data";
if (!is_dir($dataDir))
  @mkdir($dataDir, 0777, true);
$productsFile = $dataDir . "/products.json";
$ordersFile = $dataDir . "/orders.json";

function load_json($f)
{
  if (!file_exists($f))
    return [];
  $s = file_get_contents($f);
  $a = json_decode($s, true);
  return is_array($a) ? $a : [];
}
function save_json($f, $d)
{
  file_put_contents($f, json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
}
function can_manage()
{
  $r = $_SESSION['role'] ?? 'customer';
  return in_array($r, ['admin', 'manager']);
}
function is_admin()
{
  return (isset($_SESSION) && ($_SESSION['role'] ?? '') === 'admin');
}

function is_manager() {
    return (isset($_SESSION) && ($_SESSION['role'] ?? '') === 'manager');
}
function is_employee() {
    return (isset($_SESSION) && ($_SESSION['role'] ?? '') === 'employee');
}

if (!file_exists($productsFile)) {
  // Initialize with default products if file doesn't exist
  $defaultProducts = [
    ["id" => 1, "name" => "Apple", "category" => "Fruits", "subcategory" => "Fresh", "price" => 2.5, "stock" => 100, "image" => "Apple.png"],
    ["id" => 2, "name" => "Banana", "category" => "Fruits", "subcategory" => "Fresh", "price" => 1.2, "stock" => 120, "image" => "Banana.png"],
    // ... (other default products)
  ];
  save_json($productsFile, $defaultProducts);
}
if (!file_exists($ordersFile))
  save_json($ordersFile, []);

// central POST action handler (keeps pages small)
$notice = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  // Add product (manager/admin)
  if ($action === 'add_product' && can_manage()) {
    // Handle image upload
    $img = "placeholder.png"; // default

    if (!empty($_FILES['image']['name'])) {
      $uploadDir = dirname(__DIR__) . "/images/products/";
      if (!is_dir($uploadDir))
        @mkdir($uploadDir, 0777, true);

      $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
      $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

      if (in_array($fileExtension, $allowedExtensions)) {
        // Generate unique filename
        $newFilename = 'product_' . uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFilename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
          $img = $newFilename;
        } else {
          $notice = " Error uploading image.";
        }
      } else {
        $notice = " Invalid file type. Please upload JPG, PNG, or GIF images.";
      }
    } else {
      // Use the hidden field value if no file was uploaded
      $img = $_POST['image_filename'] ?? 'placeholder.png';
    }

    // Get other form data
    $nameP = trim($_POST['name'] ?? '');
    $cat = trim($_POST['category'] ?? '');
    $sub = trim($_POST['subcategory'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $replace = trim($_POST['replace_id'] ?? '');
    $all = load_json($productsFile);

    // Generate a proper numeric ID for new products
    if ($replace !== '') {
      // find and replace
      $found = false;
      foreach ($all as $i => $item) {
        if (($item['id'] ?? '') === $replace) {
          $all[$i] = ['id' => $replace, 'category' => $cat, 'subcategory' => $sub, 'name' => $nameP, 'price' => $price, 'stock' => $stock, 'image' => $img];
          $found = true;
          break;
        }
      }
      if (!$found) {
        // fallback: add new with proper ID
        $maxId = 0;
        foreach ($all as $item) {
          $idVal = $item['id'];
          // Handle both numeric and string IDs
          if (is_numeric($idVal) && $idVal > $maxId) {
            $maxId = $idVal;
          }
        }
        $id = $maxId + 1;
        $all[] = ['id' => $id, 'category' => $cat, 'subcategory' => $sub, 'name' => $nameP, 'price' => $price, 'stock' => $stock, 'image' => $img];
      }
    } else {
      // Add new product with proper ID
      $maxId = 0;
      foreach ($all as $item) {
        $idVal = $item['id'];
        // Handle both numeric and string IDs
        if (is_numeric($idVal) && $idVal > $maxId) {
          $maxId = $idVal;
        }
      }
      $id = $maxId + 1;
      $all[] = ['id' => $id, 'category' => $cat, 'subcategory' => $sub, 'name' => $nameP, 'price' => $price, 'stock' => $stock, 'image' => $img];
    }
    save_json($productsFile, $all);
    $notice = " Product saved.";
  }

  // Update product (manager & admin)
  if ($action === 'update_product' && can_manage()) {
    $id = trim($_POST['id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $cat = trim($_POST['category'] ?? '');
    $sub = trim($_POST['subcategory'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $img = trim($_POST['image'] ?? 'placeholder.png');

    $all = load_json($productsFile);
    $found = false;
    foreach ($all as &$pp) {
      if (($pp['id'] ?? '') == $id) {  // Use loose comparison to handle string vs numeric
        $pp['name'] = $name;
        $pp['category'] = $cat;
        $pp['subcategory'] = $sub;
        $pp['price'] = $price;
        $pp['stock'] = $stock;
        $pp['image'] = $img;
        $found = true;
        break;
      }
    }
    unset($pp);

    if ($found) {
      save_json($productsFile, $all);
      $notice = " Product updated.";
    } else {
      $notice = " Product not found.";
    }
  }

  // Delete product (manager & admin)
  if ($action === 'delete_product' && can_manage()) {
    $id = trim($_POST['id'] ?? '');
    $all = load_json($productsFile);
    $new = array_values(array_filter($all, fn($p) => ($p['id'] ?? '') != $id));  // Use loose comparison
    if (count($new) === count($all)) {
      $notice = " Product not found.";
    } else {
      save_json($productsFile, $new);
      $notice = " Product deleted.";
    }
  }

  // Delete category (admin only) — removes all products in that category
  if ($action === 'delete_category' && function_exists('is_admin') && is_admin()) {
    $cat = trim($_POST['category'] ?? '');
    $all = load_json($productsFile);
    $new = array_values(array_filter($all, fn($p) => ($p['category'] ?? '') !== $cat));
    save_json($productsFile, $new);
    $notice = " Category '{$cat}' removed (products deleted).";
  }

  // Ensure images directory exists
  $imagesDir = dirname(__DIR__) . "/images/products/";
  if (!is_dir($imagesDir)) {
    @mkdir($imagesDir, 0777, true);
    // Create an index.html to prevent directory listing
    file_put_contents($imagesDir . "index.html", "<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><p>Directory access is forbidden.</p></body></html>");
  }

  // Create order (any user)
  if ($action === 'create_order') {
    $pid = $_POST['product_id'] ?? '';
    $qty = max(1, (int) ($_POST['qty'] ?? 1));
    $products = load_json($productsFile);
    $foundIndex = null;

    // Use loose comparison to handle string vs numeric IDs
    foreach ($products as $i => $p) {
      if ($p['id'] == $pid) {  // Loose comparison
        $foundIndex = $i;
        break;
      }
    }

    if ($foundIndex === null) {
      $notice = " Product not found.";
    } else {
      if ((int) $products[$foundIndex]['stock'] < $qty) {
        $notice = " Not enough stock.";
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
          'by' => $name,
          'paid' => false,
          'method' => '',
          'status' => 'Processing',
          'date' => date("Y-m-d H:i:s")
        ];
        save_json($ordersFile, $orders);
        save_json($productsFile, $products);
        $notice = " Order placed (Processing).";
      }
    }
  }

  // Adjust stock (manager)
  if ($action === 'adjust_stock' && can_manage()) {
    $id = $_POST['id'] ?? '';
    $delta = (int) ($_POST['delta'] ?? 0);
    $prods = load_json($productsFile);
    foreach ($prods as &$pp) {
      // Use loose comparison for ID matching
      if ($pp['id'] == $id) {
        $pp['stock'] = max(0, (int) $pp['stock'] + $delta);
        $notice = " Stock updated.";
        break;
      }
    }
    unset($pp);
    save_json($productsFile, $prods);
  }

  // Restock (manager) with qty
  if ($action === 'restock' && can_manage()) {
    $id = $_POST['id'] ?? '';
    $qty = max(0, (int) ($_POST['qty'] ?? 0));
    if ($qty <= 0) {
      $notice = " Invalid restock qty.";
    } else {
      $prods = load_json($productsFile);
      foreach ($prods as &$pp) {
        // Use loose comparison for ID matching
        if ($pp['id'] == $id) {
          $pp['stock'] = (int) $pp['stock'] + $qty;
          $notice = " Restocked +{$qty}.";
          break;
        }
      }
      unset($pp);
      save_json($productsFile, $prods);
    }
  }

  // Mark paid (user or manager) - ENHANCED VERSION
  if ($action === 'mark_paid') {
    $oid = $_POST['order_id'] ?? '';
    $method = $_POST['method'] ?? 'Cash';
    $orders = load_json($ordersFile);
    $found = false;

    foreach ($orders as &$o) {
      if ($o['id'] === $oid) {
        if (can_manage() || $o['by'] === $name) {
          $o['paid'] = true;
          $o['method'] = $method;
          $o['status'] = 'Completed';
          $o['paid_date'] = date("Y-m-d H:i:s");
          $notice = "💳 Order marked paid (" . htmlspecialchars($method) . ").";
          $found = true;
        } else {
          $notice = "❌ Not authorized to pay this order.";
        }
        break;
      }
    }
    unset($o);

    if ($found) {
      save_json($ordersFile, $orders);
    }
  }

  // Delete order (admin/manager only)
  if ($action === 'delete_order' && can_manage()) {
    $oid = $_POST['order_id'] ?? '';
    $orders = load_json($ordersFile);
    $newOrders = [];
    $found = false;

    foreach ($orders as $o) {
      if ($o['id'] === $oid) {
        $found = true;
        // Optional: Restore stock if needed
        if (isset($o['product_id']) && isset($o['qty'])) {
          $products = load_json($productsFile);
          foreach ($products as &$p) {
            if ($p['id'] == $o['product_id']) {
              $p['stock'] = (int) $p['stock'] + (int) $o['qty'];
              break;
            }
          }
          unset($p);
          save_json($productsFile, $products);
        }
      } else {
        $newOrders[] = $o;
      }
    }

    if ($found) {
      save_json($ordersFile, $newOrders);
      $notice = " Order deleted successfully.";
    } else {
      $notice = " Order not found.";
    }
  }

  // Partial payment system (optional enhancement)
  if ($action === 'partial_payment' && can_manage()) {
    $oid = $_POST['order_id'] ?? '';
    $amount = (float) ($_POST['amount'] ?? 0);
    $method = $_POST['method'] ?? 'Cash';
    $orders = load_json($ordersFile);

    foreach ($orders as &$o) {
      if ($o['id'] === $oid) {
        if (!isset($o['payments'])) {
          $o['payments'] = [];
        }

        $o['payments'][] = [
          'amount' => $amount,
          'method' => $method,
          'date' => date("Y-m-d H:i:s"),
          'by' => $name
        ];

        $totalPaid = array_sum(array_map(fn($p) => $p['amount'], $o['payments']));

        if ($totalPaid >= $o['total']) {
          $o['paid'] = true;
          $o['status'] = 'Completed';
          $notice = " Full payment received (" . htmlspecialchars($method) . ").";
        } else {
          $o['paid'] = false;
          $o['status'] = 'Partial Payment';
          $notice = " Partial payment of ৳" . $amount . " received. Remaining: ৳" . ($o['total'] - $totalPaid);
        }

        break;
      }
    }
    unset($o);

    save_json($ordersFile, $orders);
  }
}

// Load data for pages
$products = load_json($productsFile);
$orders = load_json($ordersFile);
$lowStock = array_filter($products, fn($p) => (int) $p['stock'] <= 5);
$categories = array_values(array_unique(array_map(fn($p) => $p['category'], $products)));
sort($categories);

// safe include page
$allowed = ['home', 'products', 'stock', 'orders', 'about', 'info', 'admin', 'manager', 'marketing_finance', 'employee'];
$page = $_GET['page'] ?? 'home';
if (!in_array($page, $allowed))
  $page = 'home';

function imgPath($file)
{
  $base = "../images/products/";
  $full = dirname(__DIR__) . "/images/products/" . $file;
  if ($file && file_exists($full))
    return $base . $file;
  return $base . "placeholder.png";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>GroceryMS - Dashboard</title>
  <link rel="stylesheet" href="../css/main.css">
  <style>
    /* small avatar styles — minimal & raw */
    .userbox {
      display: flex;
      align-items: center;
      gap: 10px
    }

    .userbox .avatar {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #e8f5e9
    }

    .userbox .userinfo {
      display: flex;
      flex-direction: column;
      align-items: flex-start
    }

    .userbox .userinfo .uname {
      font-weight: 700
    }

    .userbox .userinfo .urole {
      font-size: 12px;
      color: #4caf50
    }

    @media(max-width:420px) {
      .userbox .userinfo {
        display: none
      }
    }
  </style>
</head>

<body>
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:16px">
      <div class="brand">
        <img src="../images/grocery_logo1.png" alt="logo">
        <div>GroceryMS</div>
      </div>

      <ul class="nav">
        <li><a href="dashboard.php?page=home" class="<?= $page === 'home' ? 'active' : '' ?>">Home</a></li>
        <li><a href="dashboard.php?page=products" class="<?= $page === 'products' ? 'active' : '' ?>">Products</a></li>
        <li><a href="dashboard.php?page=orders" class="<?= $page === 'orders' ? 'active' : '' ?>">Orders</a></li>
        <li><a href="dashboard.php?page=stock" class="<?= $page === 'stock' ? 'active' : '' ?>">Stock</a></li>

        <!-- NEW: Add Admin link -->
        <?php if (is_admin()): ?>
          <li><a href="dashboard.php?page=admin" class="<?= $page === 'admin' ? 'active' : '' ?>">Admin</a></li>
        <?php endif; ?>
        <!-- End New -->
         <?php if (is_manager()): ?>
<li><a href="dashboard.php?page=manager" class="<?= $page==='manager'?'active':'' ?>">Manager</a></li>
<?php endif; ?>

        <!-- NEW: Add Employee link -->
        <?php if (is_employee()): ?>
        <li><a href="dashboard.php?page=employee" class="<?= $page==='employee'?'active':'' ?>">Employee</a></li>
        <?php endif; ?>
        <!-- End New -->

       <!-- NEW: Add Marketing & Finance link -->
<?php if (is_admin()): ?>
    <li><a href="dashboard.php?page=marketing_finance" class="<?= $page === 'marketing_finance' ? 'active' : '' ?>">Marketing & Finance</a></li>
<?php endif; ?>
<!-- End New -->

        <li><a href="dashboard.php?page=about" class="<?= $page === 'about' ? 'active' : '' ?>">About</a></li>
        <li><a href="dashboard.php?page=info" class="<?= $page === 'info' ? 'active' : '' ?>">Info</a></li>
      </ul>
    </div>

    <div class="userbox">

      <div class="userinfo">
        <div class="uname"><?= htmlspecialchars($name) ?></div>
        <div class="urole"><?= htmlspecialchars(ucfirst($role)) ?></div>
      </div>
      <a class="logout-btn" href="logout.php">Logout</a>
    </div>
  </div>

  <?php if ($notice): ?>
    <div class="container-page">
      <div class="notice"><?= htmlspecialchars($notice) ?></div>
    </div>
  <?php endif; ?>

  <div class="container-page">
    <?php include __DIR__ . "/{$page}.php"; ?>
  </div>

  <footer style="margin-top:40px;text-align:center;font-size:13px;color:#666;padding:12px">
    Grocery Management System © <?= date("Y") ?>
  </footer>

  <script src="../js/script.js"></script>
</body>

</html>
