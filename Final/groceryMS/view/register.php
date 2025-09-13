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
session_start();

$errors = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    $role  = trim($_POST['role'] ?? '');

    // server-side validation
    if (strlen($name) < 3) $errors[] = "Name must be at least 3 characters.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Enter a valid email.";
    if (strlen($pass) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($role === '') $errors[] = "Please select a role.";

    $file = __DIR__ . '/../users.txt';
    if (!file_exists($file)) { @touch($file); }

    // check duplicate email
    if (empty($errors) && is_readable($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $parts = explode("|", $line);
            $e = $parts[1] ?? '';
            if (strcasecmp($e, $email) === 0) {
                $errors[] = "This email is already registered. Please login.";
                break;
            }
        }
    }

    if (empty($errors)) {
        $line = $name . '|' . $email . '|' . $pass . '|' . $role . PHP_EOL;
        $ok = file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
        if ($ok === false) {
            $errors[] = "Could not save user. Check file permissions.";
        } else {
            // Success: redirect to login with JS alert
            echo "<script>alert('✅ Registration successful! Please login.'); window.location='login.php';</script>";
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GroceryMS - Register</title>
  <link rel="stylesheet" href="../css/main.css">
</head>
<body>
  <div class="auth-box">
    <img src="../images/register_logo.webp" alt="Register Logo" class="logo">
    <h1>Grocery Management</h1>
    <h2>Register</h2>

    <?php if (!empty($errors)): ?>
      <div style="background:#ffe8e8;border:1px solid #ffbdbd;padding:10px;border-radius:6px;margin-bottom:10px;text-align:left;">
        <?php foreach($errors as $err) echo "<div style='color:#b00;margin:4px 0;'>• " . htmlspecialchars($err) . "</div>"; ?>
      </div>
    <?php endif; ?>

    <form method="POST" id="registerForm">
      <label>Full Name</label>
      <input type="text" name="name" id="regName" placeholder="Enter your name" required>
      <small id="regNameError" class="error"></small>

      <label>Email</label>
      <input type="email" name="email" id="regEmail" placeholder="Enter your email" required>
      <small id="regEmailError" class="error"></small>

      <label>Password</label>
      <input type="password" name="password" id="regPassword" placeholder="Enter a password" required>
      <small id="regPassError" class="error"></small>

      <label>Role</label>
      <select name="role" id="regRole" required>
        <option value="">-- Select Role --</option>
        <option value="customer">Customer</option>
        <option value="employee">Employee</option>
        <option value="manager">Manager</option>
        <option value="admin">Admin</option>
      </select>
      <small id="regRoleError" class="error"></small>

      <button type="submit">Register</button>
    </form>

    <p class="switch">Already have an account? <a href="login.php">Login here</a></p>
  </div>

  <script src="../js/script.js"></script>
</body>
</html>
