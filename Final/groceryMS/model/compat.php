<?php
// model/compat.php
// Drop-in compatibility to keep existing pages working while moving to MySQL.

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/user.php';
require_once __DIR__ . '/product.php';

// Back-compat for previous function names seen in logs.
if (!function_exists('db_verify_login')) {
    function db_verify_login($email, $password) {
        return user_verify_login($email, $password);
    }
}

if (!function_exists('db_products_all')) {
    function db_products_all() {
        return products_all(500, 0);
    }
}

// If code used a generic load_json(), serve DB-backed arrays instead.
if (!function_exists('load_json')) {
    function load_json($path) {
        $base = strtolower(basename((string)$path));
        if (strpos($base, 'product') !== false) {
            return products_all(1000, 0);
        }
        if (strpos($base, 'user') !== false) {
            return user_all(1000, 0);
        }
        if (strpos($base, 'categor') !== false) {
            return categories_all();
        }
        // Unknown -> empty (JSON removed)
        return [];
    }
}
?>
