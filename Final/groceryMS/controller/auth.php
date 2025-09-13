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
// controller/auth.php
require_once __DIR__ . '/../model/user.php';
session_start();

function handle_login() {
    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';
    $u = user_verify_login($email, $pass);
    if ($u) {
        $_SESSION['uid'] = $u['id'];
        $_SESSION['role'] = $u['role'];
        header("Location: /groceryMS/view/dashboard.php");
        exit;
    } else {
        $_SESSION['login_error'] = "Invalid email or password";
        header("Location: /groceryMS/view/login.php");
        exit;
    }
}

function handle_register() {
    $name = trim($_POST['name'] ?? '');
    $email= trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'customer';

    if (!$name || !$email || !$pass) {
        $_SESSION['register_error'] = "All fields are required.";
        header("Location: /groceryMS/view/register.php");
        exit;
    }

    if (user_find_by_email($email)) {
        $_SESSION['register_error'] = "Email already exists.";
        header("Location: /groceryMS/view/register.php");
        exit;
    }

    user_create($name, $email, $pass, $role, 'active');
    $_SESSION['register_success'] = "Registration successful. Please log in.";
    header("Location: /groceryMS/view/login.php");
    exit;
}

function handle_logout() {
    session_destroy();
    header("Location: /groceryMS/view/login.php");
    exit;
}
?>
