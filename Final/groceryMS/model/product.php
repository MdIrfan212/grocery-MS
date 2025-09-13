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
// model/product.php
require_once __DIR__ . '/db.php';

function categories_all() {
    $stmt = db_q("SELECT id, name FROM categories ORDER BY name ASC");
    return $stmt->fetchAll();
}

function category_create($name) {
    db_q("INSERT INTO categories (name) VALUES (?)", [$name]);
    return db()->lastInsertId();
}

function products_all($limit = 200, $offset = 0) {
    $sql = "SELECT p.id, p.name, p.sku, p.price, p.stock, c.name AS category
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            ORDER BY p.id DESC
            LIMIT ? OFFSET ?";
    $stmt = db()->prepare($sql);
    $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function product_find($id) {
    $stmt = db_q("SELECT * FROM products WHERE id = ?", [$id]);
    return $stmt->fetch();
}

function product_create($name, $sku, $price, $stock, $category_id=null) {
    db_q("INSERT INTO products (name, sku, price, stock, category_id) VALUES (?, ?, ?, ?, ?)", [
        $name, $sku, $price, $stock, $category_id
    ]);
    return db()->lastInsertId();
}

function product_update($id, $fields) {
    $allowed = ['name','sku','price','stock','category_id','description','image_url'];
    $sets = [];
    $vals = [];
    foreach ($fields as $k=>$v) {
        if (!in_array($k, $allowed, true)) continue;
        $sets[] = "$k = ?";
        $vals[] = $v;
    }
    if (!$sets) return false;
    $vals[] = $id;
    db_q("UPDATE products SET " . implode(', ', $sets) . " WHERE id = ?", $vals);
    return true;
}

function product_delete($id) {
    db_q("DELETE FROM products WHERE id = ?", [$id]);
    return true;
}

// Dashboard helpers often used in JSON-based code:
function dashboard_counts() {
    $users = (int)db_q("SELECT COUNT(*) AS n FROM users")->fetch()['n'];
    $products = (int)db_q("SELECT COUNT(*) AS n FROM products")->fetch()['n'];
    $low_stock = (int)db_q("SELECT COUNT(*) AS n FROM products WHERE stock <= 5")->fetch()['n'];
    return ['users'=>$users, 'products'=>$products, 'low_stock'=>$low_stock];
}
?>
