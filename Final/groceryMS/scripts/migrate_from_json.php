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
// scripts/migrate_from_json.php
// Optional one-time converter. Point $jsonDir to the folder that had your old JSON files.
// It will read products.json / users.json if they exist and insert into DB.
// Run from CLI: php scripts/migrate_from_json.php

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../model/product.php';

$jsonDir = realpath(__DIR__ . '/../json'); // adjust if needed

if (!$jsonDir || !is_dir($jsonDir)) {
    echo "No JSON directory found (expected ../json). Skipping.\n";
    exit(0);
}

function read_json($path) {
    if (!file_exists($path)) return null;
    $txt = file_get_contents($path);
    $data = json_decode($txt, true);
    return is_array($data) ? $data : null;
}

$users = read_json($jsonDir . '/users.json');
if ($users) {
    foreach ($users as $u) {
        $name = $u['name'] ?? ($u['username'] ?? 'User');
        $email = $u['email'] ?? null;
        $pass = $u['password'] ?? 'changeme123';
        $role = $u['role'] ?? 'customer';
        $status = $u['status'] ?? 'active';
        if ($email && !user_find_by_email($email)) {
            user_create($name, $email, $pass, $role, $status);
            echo "Imported user: {$email}\n";
        }
    }
}

$products = read_json($jsonDir . '/products.json');
if ($products) {
    foreach ($products as $p) {
        $name = $p['name'] ?? 'Product';
        $sku = $p['sku'] ?? null;
        $price = isset($p['price']) ? (float)$p['price'] : 0;
        $stock = isset($p['stock']) ? (int)$p['stock'] : 0;
        $catName = $p['category'] ?? null;
        $catId = null;
        if ($catName) {
            $found = db_q("SELECT id FROM categories WHERE name = ?", [$catName])->fetch();
            if ($found) $catId = (int)$found['id']; else $catId = category_create($catName);
        }
        product_create($name, $sku, $price, $stock, $catId);
        echo "Imported product: {$name}\n";
    }
}

echo "Done.\n";
?>
