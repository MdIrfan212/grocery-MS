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
// model/user.php
require_once __DIR__ . '/db.php';

function user_find_by_email($email) {
    $stmt = db_q("SELECT * FROM users WHERE email = ?", [$email]);
    return $stmt->fetch(); // returns assoc or false
}

function user_find_by_id($id) {
    $stmt = db_q("SELECT * FROM users WHERE id = ?", [$id]);
    return $stmt->fetch();
}

function user_create($name, $email, $password_plain, $role = 'customer', $status='active') {
    $hash = password_hash($password_plain, PASSWORD_BCRYPT);
    db_q("INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?)", [$name, $email, $hash, $role, $status]);
    return db()->lastInsertId();
}

function user_verify_login($email, $password_plain) {
    $u = user_find_by_email($email);
    if (!$u) return false;
    if (!isset($u['password_hash'])) return false;
    if (password_verify($password_plain, $u['password_hash'])) {
        return $u;
    }
    return false;
}

function user_all($limit = 100, $offset = 0) {
    $stmt = db_q("SELECT id, name, email, role, status, created_at FROM users ORDER BY id DESC LIMIT ? OFFSET ?", [(int)$limit, (int)$offset]);
    return $stmt->fetchAll();
}

function user_update($id, $fields) {
    $allowed = ['name','email','role','status','password_hash'];
    $sets = [];
    $vals = [];
    foreach ($fields as $k=>$v) {
        if (!in_array($k, $allowed, true)) continue;
        $sets[] = "$k = ?";
        $vals[] = $v;
    }
    if (!$sets) return false;
    $vals[] = $id;
    db_q("UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?", $vals);
    return true;
}

function user_change_password($id, $new_plain) {
    $hash = password_hash($new_plain, PASSWORD_BCRYPT);
    return user_update($id, ['password_hash'=>$hash]);
}

function user_delete($id) {
    db_q("DELETE FROM users WHERE id = ?", [$id]);
    return true;
}
?>
